<?php

include_once realpath( __DIR__ . '/config.php' );

// needed for php < 8.0
if ( !function_exists( 'str_ends_with' ) ) {
  function str_ends_with( $haystack, $needle ) {
    return substr_compare( $haystack, $needle, -strlen( $needle ) ) === 0;
  }
}

/**
 * Verifies the hmac
 *
 * @param string $hmac - the hmac
 * @param string $message - the message to be verified
 * @param string $client_id - (optional, -1) the client's id, if != -1 generated the $message from the client's nonce
 *
 * @return boolean valid or not hmac
 */
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
        throw new Exception( 'Client doesn\'t exist or isn\'t active.' );

    } catch ( PDOException | Exception $err ) {
      die( 'Unable to process request. ' . $err->getMessage() );
    } finally {
      $pdo = null;
    }

  }

  $check = hash_hmac( 'sha256', $message, APP_SECRET );

  return ( $check === $hmac );

}

/**
 * Connects to the database via PDO
 *
 * @return PDO - the pdo instance
 */
function connect_db() {

  $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
  $opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];

  return new PDO( $dsn, DB_USER, DB_PASSWORD, $opt );

}

/**
 * Generates a nonce
 *
 * @param int $client_id - the client's id
 *
 * @return string the nonce generated
 */
function generateNonce( int $client_id ) {

  $nonce = hash( 'sha256', makeRandomString() );
  storeNonce( $client_id, $nonce );

  return $nonce;

}

/**
 * Generates a random string
 *
 * @param int $bits - (optional, 256) string length in bits
 *
 * @return string a random string
 */
function makeRandomString( int $bits = 256 ) {

  $bytes  = ceil( $bits / 8 );
  $return = '';

  for ( $i = 0; $i < $bytes; $i++ )
    $return .= chr( random_int( 0, 255 ) );

  return $return;

}

/**
 * Stores the nonce in the database
 *
 * @param int $client_id - the client's id
 * @param string $nonce - the nonce to be stored
 */
function storeNonce( int $client_id, string $nonce ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET nonce = ? WHERE client_id = ?' );
    $stm->execute([ $nonce, $client_id ]);

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }

}

/**
 * Retireves the client id in the database
 *
 * @param string $shop - the shop name (test.myshopify.com)
 *
 * @return int client id or -1
 */
function getClientId( string $shop ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );
    $stm->execute([ str_replace( '.myshopify.com', '', $shop ) ]);

    $result = $stm->fetchAll();

    if ( count( $result ) )
      return $result[0]['client_id'];
    else
      return -1;

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }

}