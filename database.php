<?php

include_once realpath( __DIR__ . '/utils.php' );

try {

  $pdo = connect_db();

  $sql  = 'CREATE TABLE IF NOT EXISTS `clients` ( `client_id` INT(11) NOT NULL AUTO_INCREMENT , `client_name` VARCHAR(255) NOT NULL , PRIMARY KEY (`client_id`)) ENGINE = InnoDB;';
  $sql .= 'CREATE TABLE IF NOT EXISTS `client_stores` ( `store_id` INT(11) NOT NULL AUTO_INCREMENT , `client_id` INT(11) NOT NULL , `store_name` VARCHAR(255) NOT NULL , `token` VARCHAR(255) NOT NULL , `hmac` VARCHAR(255) NULL DEFAULT NULL , `nonce` VARCHAR(255) NULL DEFAULT NULL , `url` VARCHAR(255) NOT NULL , `last_activity` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `active` TINYINT(4) NOT NULL DEFAULT "1" , PRIMARY KEY (`store_id`)) ENGINE = InnoDB;';

  $pdo->exec( $sql );

  echo 'Created tables';

} catch ( PDOException $err ) {

  die( 'Unable to process request. ' . $err->getMessage() );

} finally {

  if ( $pdo )
    $pdo = null;

}