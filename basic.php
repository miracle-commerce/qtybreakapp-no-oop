<?php
define("APPKEY", "*****");
define("APPSECRET", "*****");
define("DB_server", "****");
define("DB_name", "******");
define("DB_user", "******");
define("DB_pass", "*****");
define("STORES_TABLE", "******");
define("SCHEMES_TABLE", "******");
define("TIERES_TABLE", "*****");
define("PRODUCTS_TABLE", "******");
define("ROOT_PATH", "**************");
define("BASICMODULEPATH", ROOT_PATH."/basic-modules");
define("API_VERSION", "2022-04");

$bcapp_db_connection = new mysqli(DB_server, DB_user, DB_pass, DB_name);
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    $link = "https"; 
else
    $link = "http"; 
  
// Here append the common URL characters. 
$link .= "://"; 
  
// Append the host(domain name, ip) to the URL. 
$link .= $_SERVER['HTTP_HOST']; 
  
// Append the requested resource location to the URL 
// Print the link 
define("ROOT_URL", $link."//qtybreakapp-no-oop/");
define("ROOT_ASSETS_URL", $link."//qtybreakapp-no-oop/assets");
