<?php
include("header.php");
// when the user clicks the install button in the app prompt, they're redirected to the client server(app server). 
/* With this code, we will get access_token to make calls the Shopify REST APIs. 
Shopify custom apps and public apps use to oAuth2.0's authorization code grant flow to get access token. 
The Access token should be stored in database. */

// Before you continue, The app must performs the following security checks. If any of the checks fails, your app must reject the request with an error, and must not continue.
$params = $_GET; //Retrive all request parameters. 
if(verifyRequest($params)){
    $registeredShop = getShop($params['shop']);
    if(!$registeredShop){
        $token_request_params = array(
            "client_id" => APPKEY, 
            "client_secret" => APPSECRET,
            "code" => $params['code']
        );
        $shop = $params['shop'];
        $access_token = requestAccessToken($shop, $token_request_params);
        if($access_token){
            registerShop($access_token, $shop);
        }    
    }else{
        include("layouts/all-schemes.php");
    }
    
}
include("footer.php");

