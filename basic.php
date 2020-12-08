<?php
define("APPKEY", "167abb701f1d34b021dd1fc2098a82e0");
define("APPSECRET", "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");
define("DB_server", "localhost");
define("DB_name", "freegro1_becom_qtybreak_app");
define("DB_user", "freegro1_fox0127");
define("DB_pass", "Ga?N!9ZQ]f.v");
define("STORES_TABLE", "RegisteredStores");
define("SCHEMES_TABLE", "schemes");
define("TIERES_TABLE", "tieres");
define("PRODUCTS_TABLE", "Products");
define("ROOT_PATH", "/customapp/qtybreak-discount/");
define("BASICMODULEPATH", ROOT_PATH."/basic-modules");
define("API_VERSION", "2020-10");

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
define("ROOT_URL", $link."/customapp/qtybreak-discount/");
define("ROOT_ASSETS_URL", $link."/customapp/qtybreak-discount/assets");
