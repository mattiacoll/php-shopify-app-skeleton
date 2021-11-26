<?php

include_once 'utils/utils.php';

$shop      = str_replace( '.myshopify.com', '', $_GET['shop']);
$hmac      = $_GET['hmac'];
$client_id = getClientId( $shop );

if ( !verifyHMACClient( $hmac, $client_id ) )
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