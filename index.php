<?php

include_once realpath( __DIR__ . '/utils.php' );

$shop      = $_GET['shop'];
$hmac      = $_GET['hmac'];
$client_id = getClientId( $shop );

if ( !verifyHMAC( $hmac, '', $client_id ) )
  die( 'Unable to process request. Cannot verify HMAC' );

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <h1>Welcome to My App, <?php echo $shop ?>! (ID: <?php echo $client_id ?>)</h1>
  <p>Happy coding!</p>
</body>
</html>