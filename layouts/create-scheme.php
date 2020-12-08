<?php 
include_once('../header.php');
if(verifyRequest()){
    $shopURL = $_GET['shop'];
    $shop = getShop($shopURL);
    $shopId = $shop['ShopId'];
    $accessToken = $shop['access_token'];
    
    $products = requestProducts($shopURL, $accessToken, $_POST);
    $render_params = ["shop_url"=>$shopURL, "action"=>"create", "stitle"=>"", "sid"=>null,"tieres"=>[]];
    
    // render layout
    include_once("../layout-snippets/single-scheme.php");
    
};
include('../footer.php');