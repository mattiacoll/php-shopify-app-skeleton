<?php

include_once realpath( __DIR__ . '/utils.php' );

$shop      = $_GET['shop'];
$hmac      = $_GET['hmac'];
$client_id = getClientId( $shop );

if ( !verifyHMAC( $hmac, '', $client_id ) )
  die( 'Unable to process request.' );

?>

<html>
  <head>
  </head>
  <body>
    <center>
      <h1>Welcome to My App, <?php echo $shop ?>! (ID: <?php echo $client_id ?>)</h1>
    </center>
  </body>
</html>