<?php
// Set variables for request. 
// Example of install URL: https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&scope={scopes}&redirect_uri={redirect_uri}&state={nonce}&grant_options[]={access_mode}
$shop = $_GET['shop'];
$api_key = '167abb701f1d34b021dd1fc2098a82e0';
$scopes = "write_themes,read_products,write_draft_orders,write_script_tags,read_discounts,read_price_rules";
$redirect_url = "https://beyondcommerce.co/customapp/qtybreak-discount/index.php";

// Build install/approval URL to redirect to
$install_url = "https://".$shop."/admin/oauth/authorize?client_id=".$api_key."&scope=".$scopes."&redirect_uri=".urlencode($redirect_url);
header("Location:".$install_url);
die();