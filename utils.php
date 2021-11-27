<?php

include_once realpath( __DIR__ . '/config.php' );

function verifyHMAC( string $hmac, string $message, int $client_id = -1 ) {

  // Get nonce if client_id is set
  if ( $client_id !== -1 ) {

    try {

      $pdo = connect_db();

      $stm = $pdo->prepare( 'SELECT nonce FROM client_stores WHERE client_id = ? AND last_activity >= NOW() - INTERVAL 10 SECOND AND active = 1' );
      $stm->execute([ $client_id ]);

      $result = $stm->fetchAll();

      if ( count( $result ) )
        $message = $result[0]['nonce'];
      else
        die( 'Unable to process request.' );

    } catch ( PDOException $err ) {

      die( 'Unable to process request. ' . $err->getMessage() );

    } finally {

      if ( $pdo )
        $pdo = null;

    }

  }

  $check = hash_hmac( 'sha256', $message, APP_SECRET );

  return ( $check === $hmac );

}


function connect_db() {

  $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
  $opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];

  return new PDO( $dsn, DB_USER, DB_PASSWORD, $opt );

}


function generateNonce( $client_id ) {

  $nonce = hash( 'sha256', makeRandomString() );
  storeNonce( $client_id, $nonce );

  return $nonce;

}

function makeRandomString( $bits = 256 ) {

  $bytes  = ceil( $bits / 8 );
  $return = '';

  for ( $i = 0; $i < $bytes; $i++ )
    $return .= chr( random_int( 0, 255 ) );

  return $return;

}

function storeNonce( $client_id, $nonce ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET nonce = ? WHERE client_id = ?' );
    $stm->execute([ $nonce, $client_id ]);

  } catch ( PDOException $err ) {

    die( 'Unable to process request. ' . $err->getMessage() );

  } finally {

    if ( $pdo )
      $pdo = null;

  }

}

//UA - User Authentication
function getClientId( $shop ) {

  $client_id = -1;

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );
    $stm->execute([ str_replace( '.myshopify.com', '', $shop ) ]);

    $result = $stm->fetchAll();

    if ( count( $result ) )
      $client_id = $result[0]['client_id'];

  } catch ( PDOException $err ) {

    die( 'Unable to process request. ' . $err->getMessage() );

  } finally {

    if ( $pdo )
      $pdo = null;

  }

  return $client_id;
}