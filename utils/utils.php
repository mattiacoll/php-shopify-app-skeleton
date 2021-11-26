<?php

function verifyHMAC(){

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