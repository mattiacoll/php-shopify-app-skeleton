<?php

//UA - User Authentication

include_once realpath( __DIR__ . '/utils.php' );

$hmac  = $_GET['hmac'];

parse_str( $_SERVER['QUERY_STRING'], $query );
unset( $query['hmac']);

$message = http_build_query( $query );

if ( verifyHMAC( $hmac, $message ) ) {

  $shop = str_replace( '.myshopify.com', '', $_GET['shop']);

  $client_id = createClientStore( $shop );
  $nonce     = generateNonce( $client_id );

  if ( $client_id === -1 )
    die( 'Unable to process request. Invalid client ID.' );

  header( 'Location: https://'.$shop.'.myshopify.com/admin/oauth/authorize?client_id='.APP_KEY.'&scope='.APP_PERMISSION.'&redirect_uri='.APP_URL.'/postoauth.php&state='.$nonce );

}

/**
 * Creates a client in `client_stores` table
 *
 * @param string $shop - shop's name (test.myshopify.com)
 *
 * @return int the client's id or -1 if no user was found
 */
function createClientStore( string $shop ) {

  $client_id = -1;

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'SELECT client_id FROM clients WHERE client_name = ?' );

    $stm->execute([ $shop ]);
    $result = $stm->fetchAll();

    if ( count( $result ) )
      $client_id = $result[0]['client_id'];
    else
      $client_id = createClient( $shop );

    if ( $client_id === -1 )
      throw new Exception( 'Invalid client ID.' );

    $stm = $pdo->prepare( 'SELECT store_id FROM client_stores WHERE client_id = ?' );
    $stm->execute([ $client_id ]);

    $result = $stm->fetchAll();

    if ( !count( $result ) ) {

      $stm = $pdo->prepare( 'INSERT INTO client_stores (client_id, store_name, url) VALUES (?, ?, ?)' );
      $stm->execute([ $client_id, $shop, 'https://'.$shop.'.myshopify.com/' ]);

    }

  } catch ( PDOException | Exception $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }

  return $client_id;

}

/**
 * Creates a client in `clients` table
 *
 * @param string $shop - shop's name (test.myshopify.com)
 *
 * @return int the client's id or -1 if no user was found
 */
function createClient( string $shop ) {

  try {

    $pdo = connect_db();

    $stm = $pdo->prepare( 'INSERT INTO clients (client_name) VALUES (?)' );
    $stm->execute([ $shop ]);

    return getClientId( $shop );

  } catch ( PDOException $err ) {
    die( 'Unable to process request. ' . $err->getMessage() );
  } finally {
    $pdo = null;
  }

}