<?php

include_once realpath( __DIR__ . '/utils.php' );

$hmac  = $_GET['hmac'];
$nonce = $_GET['state'];

$query = [];
parse_str( $_SERVER['QUERY_STRING'], $query );
unset( $query['hmac']);

$message = http_build_query( $query );

if ( verifyHMAC( $hmac, $message ) ) {

  $shop  = $_GET['shop'];
  $code  = $_GET['code'];

  $client_id = getClientId( $shop );

  if ( $client_id === -1 )
    die( 'Unable to process request. Invalid client id.' );

  if ( !verifyNonce( $client_id, $nonce ) )
    die( 'Unable to process request. Invalid nonce.' );

  if ( !verifyHost( $shop ) )
    die( 'Unable to process request. Invalid host.' );


  $curl_query = [
    'client_id'     => APP_KEY,
    'client_secret' => APP_SECRET,
    'code'          => $code,
  ];

  $access_token_url = 'https://' . $shop . '/admin/oauth/access_token';

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
  curl_setopt( $ch, CURLOPT_URL, $access_token_url );
  curl_setopt( $ch, CURLOPT_POST, count( $curl_query ) );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $curl_query ) );

  $result = curl_exec( $ch );
  curl_close( $ch );

  $result = json_decode( $result, true );
  $access_token = $result['access_token'];

  storeToken( $client_id, $access_token );

  $hmac = generateHMAC( $client_id );

  header( 'Location: '.APP_REDIRECT.'?shop='.$shop.'&hmac='.$hmac );

}

/**
 * Verifies the nonce
 *
 * @param int $client_id - the client's id
 * @param string $nonce - the nonce to be verified
 *
 * @return boolean valid or not nonce
 */
function verifyNonce( int $client_id, string $nonce ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'SELECT nonce FROM client_stores WHERE client_id = ?' );
    $stm->execute([ $client_id ]);

    $result = $stm->fetchAll();

    if ( count( $result ) )
      $check = $result[0]['nonce'];

    $stm = $pdo->prepare( 'UPDATE client_stores SET nonce = "" WHERE client_id = ?' );
    $stm->execute([ $client_id ]);

    return ( $nonce === $check );

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }
}

/**
 * Verifies if the host is a shopify url
 *
 * @param string $shop - the shop's host (test.myshopify.com)
 *
 * @return boolean valid or not host
 */
function verifyHost( string $shop ) {

  if ( !str_ends_with( $shop, '.myshopify.com' ) )
    return false;

  $shop = str_replace( '.myshopify.com', '', $shop );
  return ( preg_match( '/[a-z\.\-0-9]/i', $shop ) );

}

/**
 * Generates the hmac
 *
 * @param int - $client_id
 *
 * @return string - the generated hmac
 */
function generateHMAC( int $client_id ) {

  $nonce = generateNonce( $client_id );
  $hmac  = hash_hmac( 'sha256', $nonce, APP_SECRET );

  storeHMAC( $client_id, $hmac );

  return $hmac;

}

/**
 * Stores the generated hmac
 *
 * @param int $client_id - the client's id
 * @param string $hmac - the hmac
 */
function storeHMAC( int $client_id, string $hmac ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET hmac = ?, last_activity = NOW() WHERE client_id = ?' );
    $stm->execute([ $hmac, $client_id ]);

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }
}

/**
 * Stores the shop's token
 *
 * @param int $client_id - the client's id
 * @param string $token - the token
 */
function storeToken( int $client_id, string $token ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET token = ? WHERE client_id = ?' );
    $stm->execute([ $token, $client_id ]);

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }
}