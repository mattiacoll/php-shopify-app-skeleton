<?php

#Database configuration
define( 'DB_HOST', '' );
define( 'DB_USER', '' );
define( 'DB_PASSWORD', '' );
define( 'DB_NAME', '' );

#APP configuration
define( 'APP_KEY', '' );
define( 'APP_SECRET', '' );

define( 'APP_URL', 'https://yourapplocation' );

// Redirect URL for after handshake
define( 'APP_REDIRECT', APP_URL . '/index.php' );

// Refer to https://shopify.dev/api/usage/access-scopes
define( 'APP_PERMISSION', implode( ',', [
  'read_orders',
  'read_script_tags',
  'write_script_tags',
]) );