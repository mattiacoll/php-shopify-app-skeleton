<?php

//AS - App Setup

include_once realpath( __DIR__ . '/utils.php' );

$query = [];
parse_str( $_SERVER['QUERY_STRING'], $query );
$shop  = $_GET['shop'];
$code  = $_GET['code'];
$hmac  = $_GET['hmac'];
$nonce = $_GET['state'];

$query_no_hmac = $query;
unset( $query_no_hmac['hmac']);

$message = http_build_query( $query_no_hmac );

if ( !function_exists( 'str_ends_with' ) ) {
  function str_ends_with( $haystack, $needle ) {
    return substr_compare( $haystack, $needle, -strlen( $needle ) ) === 0;
  }
}

if ( verifyHMAC( $hmac, $message ) ) {

  $client_id = getClientId( $shop );

  if ( $client_id === -1 )
    die( 'Unable to process request. ERROR: PO-R-1' );

  if ( !verifyNonce( $client_id, $nonce ) )
    die( 'Unable to process request. ERROR: PO-R-3' );

  if ( !verifyHost( $shop ) )
    die( 'Unable to process request. ERROR: PO-R-2' );


  $query = [
    'client_id'     => APP_KEY,
    'client_secret' => APP_SECRET,
    'code'          => $code,
  ];

  $access_token_url = 'https://' . $shop . '/admin/oauth/access_token';

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
  curl_setopt( $ch, CURLOPT_URL, $access_token_url );
  curl_setopt( $ch, CURLOPT_POST, count( $query ) );
  curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $query ) );

  $result = curl_exec( $ch );
  curl_close( $ch );

  $result = json_decode( $result, true );
  $access_token = $result['access_token'];

  storeToken( $client_id, $access_token );

  $hmac = generateHMAC( $client_id );

  header( 'Location: '.APP_REDIRECT.'?shop='.$shop.'&hmac='.$hmac );

}

function verifyNonce( $client_id, $nonce ) {

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

    if ( $pdo )
      $pdo = null;

  }
}

function verifyHost( $shop ) {

  if ( !str_ends_with( $shop, '.myshopify.com' ) )
    return false;

  $shop = str_replace( '.myshopify.com', '', $shop );
  return ( preg_match( '/[a-z\.\-0-9]/i', $shop ) );

}

function generateHMAC( $client_id ) {

  $nonce = generateNonce( $client_id );
  $hmac  = hash_hmac( 'sha256', $nonce, APP_SECRET );

  storeHMAC( $client_id, $hmac );

  return $hmac;

}


function storeHMAC( $client_id, $hmac ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET hmac = ?, last_activity = NOW() WHERE client_id = ?' );
    $stm->execute([ $hmac, $client_id ]);

  } catch ( PDOException $err ) {

    die( 'Unable to process request. ' . $err->getMessage() );

  } finally {

    if ( $pdo )
      $pdo = null;

  }
}

function storeToken( $client_id, $token ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'UPDATE client_stores SET token = ? WHERE client_id = ?' );
    $stm->execute([ $token, $client_id ]);

  } catch ( PDOException $err ) {

    die( 'Unable to process request. ' . $err->getMessage() );

  } finally {

    if ( $pdo )
      $pdo = null;

  }
}