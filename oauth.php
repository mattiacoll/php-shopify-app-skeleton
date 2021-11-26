<?php

//UA - User Authentication

include 'config.php';
include_once 'utils/utils.php';

$query = [];
parse_str( $_SERVER['QUERY_STRING'], $query );

$shop = str_replace( '.myshopify.com', '', $_GET['shop']);
$hmac = $_GET['hmac'];

$query_no_hmac = $query;
unset( $query_no_hmac['hmac']);

$message = http_build_query( $query_no_hmac );

if ( verifyHMAC() ) {

  $client_id = processClient( $shop );
  $nonce     = generateNonce( $client_id );

  if ( $client_id === -1 )
    die( 'Unable to process request. ERROR: O-R-1' );

  header( 'Location: https://'.$shop.'.myshopify.com/admin/oauth/authorize?client_id='.$k.'&scope='.implode( ',', $permissions ).'&redirect_uri='.$app_url.'/postoauth.php&state='.$nonce );

}

function processClient( $shop ) {

  $client_id = -1;

  try {

    $pdo;
    connect_db( $pdo );

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );

    $stm->execute([ $shop ]);
    $stm->fetchAll();

    if ( count( $result ) )
      $client_id = $result[0]['client_id'];
    else
      $client_id = createClient( $shop );

    if ( $client_id === -1 )
      die( 'Unable to process request. ERROR: O-PC-4' );

    $stm = $pdo->prepare( 'SELECT store_id FROM client_stores WHERE client_id = ?' );
    $stm->execute([ $client_id ]);

    $result = $stm->fetchAll();

    if ( !count( $result ) ) {

      $stm = $pdo->prepare( 'INSERT INTO client_stores (client_id, store_name, url) VALUES (?, ?, ?)' );
      $stm->execute([ $client_id, $shop, 'https://'.$shop.'.myshopify.com/' ]);

    }

    $pdo = null;

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  }

  return $client_id;

}

function createClient( $shop ) {

  try {

    $pdo;
    connect_db( $pdo );

    $stm = $pdo->prepare( 'INSERT INTO clients (client_name) VALUES (?)' );
    $stm->execute([ $shop ]);

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );
    $stm->execute([ $shop ]);

    $result = $stm->fetchAll();

    $pdo = null;

    if ( count( $result ) )
      return $result[0]['client_id'];
    else
      return -1;

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  }

}