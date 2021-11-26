<?php

include_once 'config.php';

function verifyHMAC() {

  global $s;
  global $message;
  global $hmac;

  $check = hash_hmac( 'sha256', $message, $s );

  return ( $check === $hmac );

}


function connect_db( &$pdo ) {

  global $sn, $dn, $un, $pw;

  $dsn = 'mysql:host='.$sn.';dbname='.$dn.';charset=utf8';
  $opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];

  $pdo = new PDO( $dsn, $un, $pw, $opt );

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

    $pdo;
    connect_db( $pdo );

    $stm = $pdo->prepare( 'UPDATE client_stores SET nonce = ? WHERE client_id = ?' );
    $stm->execute([ $nonce, $client_id ]);

    $pdo = null;

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  }

}

//UA - User Authentication
function getClientId( $shop ) {

  $client_id = -1;

  try {

    $pdo;
    connect_db( $pdo );

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );
    $stm->execute(array($shop));

    $result = $stm->fetchAll();

    if ( count( $result ) )
      $client_id = $result[0]['client_id'];

    $pdo = null;

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  }

  return $client_id;
}

function verifyHMACClient( $hmac, $client_id ) {

  try {

    $pdo;
    connect_db( $pdo );

    $stm = $pdo->prepare( 'SELECT nonce FROM client_stores WHERE client_id = ? AND last_activity >= NOW() - INTERVAL 10 SECOND AND active = 1' );
    $stm->execute([ $client_id ]);

    $result = $stm->fetchAll();

    if ( count( $result ) )
      $nonce = $result[0]['nonce'];

    $check = hash_hmac( 'sha256', $nonce, $s );

    return ( $check === $hmac );

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  }

}