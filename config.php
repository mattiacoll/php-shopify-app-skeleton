<?php

#Database configuration
$sn = '';
$un = '';
$pw = '';
$dn = '';

#Shopify configuration
$k = '';		#App Key
$s = '';		#App Secret
$app_url      = 'https://yourapplocation';
$redirect_url = $app_url . '/index.php';	#Redirect URL for after handshake
$permissions = [
  'read_orders',										#List what ever permissions your app will need here
  'read_script_tags',
  'write_script_tags'
];