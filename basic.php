<?php
define("APPKEY", "167abb701f1d34b021dd1fc2098a82e0");
define("APPSECRET", "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");
define("DB_server", "localhost");
define("DB_name", "brianflo_qtybreak");
define("DB_user", "getcreat_qtybreak_fox");
define("DB_pass", "J!}SsF57C=C4");
define("STORES_TABLE", "RegisteredStores");
define("SCHEMES_TABLE", "schemes");
define("TIERES_TABLE", "tieres");
define("PRODUCTS_TABLE", "Products");
define("ROOT_PATH", "/qtybreakapp-no-oop/");
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