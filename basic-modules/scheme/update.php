<?php 
// update Schemes
require_once("../../functions.php");
$sid = $_POST['sid'];
$shop_url = $_POST['shop_url'];
$products = $_POST['products'];
$productIds = array();
foreach($products as $product){
    array_push($productIds, $product['id']);
};
//Create db Connection 
if(isset($bcapp_db_connection)){
    $bcapp_db_connection = $bcapp_db_connection;
}else{
    $bcapp_db_connection = $bcapp_db_connection = new mysqli(DB_server, DB_user, DB_pass, DB_name);
}
// Get current Products by schemeId
$currentProducts = getProductsBySid($sid, $bcapp_db_connection);
if(count($currentProducts)){
    foreach($currentProducts as $currentProduct){
        if(!in_array($currentProduct['pid'], $productIds)){
            array_push($productIds, $currentProduct['pid']);
        }
    }
}

// update Schemes by sid if change
$updateSchemeSql = "UPDATE ".SCHEMES_TABLE." SET title='{$_POST['title']}', message='{$_POST['message']}', tier_template='{$_POST['tier_template']}', all_products={$_POST['all_products']}, updatedate=CURRENT_TIMESTAMP() WHERE id={$sid}";
if($bcapp_db_connection->query($updateSchemeSql)){
    /*==========================================
        Update tiers
    ===========================================*/
    // Remove Old Tiers
    $removeTiersSql = "DELETE FROM ".TIERES_TABLE." WHERE schemeid = $sid"; 
    if($bcapp_db_connection->query($removeTiersSql)){
        //Add New Tiers
        $updateTier = addTiers($shop_url, $sid, $_POST['tiers'], $bcapp_db_connection);
        var_dump($updateTier);
        if($updateTier){
            /*======================================
            UpdateProducts
            =======================================*/
            // removeProducts
            if(removeProducts($shop_url, $productIds, $bcapp_db_connection)){
                // Add Products By Scheme Id;
                addProducts($shop_url, $sid, $products, $bcapp_db_connection);
            }
        }else{
            echo "Fail: Updating tiers has been failed.";
        }
    }else{
        echo "Fail: Removing tiers has been failed";
    }
}else{
    echo 'Fail: updateScheme';
}
