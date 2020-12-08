<?php 
header("Access-Control-Allow-Origin: *");
require_once("../functions.php"); 
if(isset($_POST['shop'])){
    // Check if the shopify has been registered in App
    $shop_url = $_POST['shop'];
    $registeredShop = getShop($_POST['shop']);
    if(count($registeredShop) > 0){
        $shopData = array('schemes'=>'', 'config'=>''); //Grand Parents Object. 
        $shopSchemes = array();
        $schemes = getSchemes($shop_url);
        foreach($schemes as $scheme){
            $schemeId = $scheme['id'];
            $tieres = getTieres($schemeId);
            $schemeProductIds = getProductsIdsBySid($schemeId, $bcapp_db_connection);
            $singleScheme = array(
                'id'=>$schemeId,
                'title'=>$scheme['title'], 
                'message'=>$scheme['message'],
                'tier_template'=>$scheme['tier_template'],
                'tieres'=>$tieres,
                'products'=>$schemeProductIds,
                'all_products'=>''
            );

            if($scheme['all_products']){
                $singleScheme['all_products'] = true;
            }else{
                $singleScheme['all_products'] = false;
            }
            //Add singleScheme to ShopSchemes
            array_push($shopSchemes, $singleScheme);
        };
        
        $shopData['schemes'] = $shopSchemes;
        $shopData = json_encode($shopData);
        echo $shopData;
    }else{
        echo "Sorry, Your Store has not been registered";
    }
}