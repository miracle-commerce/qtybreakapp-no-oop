<?php 
include_once('../header.php');
if(verifyRequest()){
    $shopURL = $_GET['shop'];
    $sid = $_GET['sid'];
    $shop = getShop($shopURL);
    $shopId = $shop['ShopId'];
    $accessToken = $shop['access_token'];
    $products = requestProducts($shopURL, $accessToken, $_POST);
    $scheme = getSingleScheme($sid, $bcapp_db_connection);
    $tiers = getTieres($sid);
    $schemeProducts = getProductsBySid($sid, $bcapp_db_connection);
    $render_params = ["shop_url"=>$shopURL, "action"=>"update", "stitle"=>$scheme["title"], "message"=>$scheme["message"], "tier_template"=>$scheme["tier_template"], "sid"=>$sid,"tieres"=>$tiers, "all_products"=>$scheme['all_products']];
    // render layout
    if($scheme){
        include_once("../layout-snippets/single-scheme.php");    
    }
};
include('../footer.php');