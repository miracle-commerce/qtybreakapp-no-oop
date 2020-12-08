<?php
    require_once("../functions.php"); 
    $query = $_POST;
    $shop_url = $_POST["shop_url"];
    if(function_exists("getAccessToken")){
        $token = getAccessToken($shop_url);
        $accessToken = $token['access_token']; 
        $searchedProducts = requestProducts($shop_url, $accessToken, $_POST);
        $searchedProducts = json_encode($searchedProducts);
        echo $searchedProducts;
    }
