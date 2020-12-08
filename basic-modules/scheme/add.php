<?php
require_once("../../functions.php");
$shop_url = $_POST['shop_url'];
$schemeTitle = addslashes($_POST["title"]);
$schemeMessage = addslashes($_POST['message']); 
$tier_template = addslashes($_POST['tier_template']); 
$tiers = $_POST["tiers"];
$selectAll = $_POST["all_products"];
$storeschemesql = "INSERT INTO ".SCHEMES_TABLE." (shop_url, title, message, tier_template,  all_products, updatedate) VALUES ('$shop_url','$schemeTitle', '$schemeMessage', '$tier_template', $selectAll, CURRENT_TIMESTAMP)";
$schemeResult = $bcapp_db_connection->query($storeschemesql);
if($schemeResult){
	 $newSchemeId = $bcapp_db_connection->insert_id;
	 // Add New tieres
	 function createTierValuesSql($tier){
		  global $shop_url; 
		  global $newSchemeId;
		  $singleTierParam = "('".$shop_url."',". $newSchemeId.",'".$tier['type']."',".$tier['amount'].",".$tier['quantity'].", CURRENT_TIMESTAMP)";
		  return $singleTierParam;
	 };

	 $tiersValuesSql = implode(",", array_map("createTierValuesSql", $tiers));
	 $storetierSql = "INSERT INTO ".TIERES_TABLE."(shop_url, schemeid, type, amount, quantity, update_date) VALUES ".$tiersValuesSql;
	 $tiersResult = $bcapp_db_connection->query($storetierSql);
	 if($tiersResult){
		  $lasttierid = $bcapp_db_connection->insert_id;
	 }else{
		  echo $bcapp_db_connection->error;
	 }

	//Add products to products table with schemeid
	$products = $_POST["products"];
	function productSql($product){
		 global $shop_url, $newSchemeId;
		 $productQuery = "('".$shop_url."',".$newSchemeId.",".$product['id'].",'".addslashes($product['title'])."','".$product['image']."',".$product['price'].")";
		  return $productQuery;
	}

	$addProductsParams = implode(",", array_map("productSql", $products));
	$addProductsSql = "INSERT INTO Products (shopurl, sid, pid, title, image_url, price) VALUES ".$addProductsParams;

	// remove products which are applied another scheme
	$productIds = array();
	foreach($products as $product){
		array_push($productIds, $product['id']);
	};
	$exist_products_another_scheme = false;

	if(count($productIds) > 0){
		$exist_products_another_scheme = removeProducts($shop_url, $productIds, $bcapp_db_connection);
	} else{
		$exist_products_another_scheme = true;
	}
	// add Products to new Schemes
	if($exist_products_another_scheme){
		  	$productsResult = $bcapp_db_connection->query($addProductsSql);
		  	if($productsResult){
				$lastProductId = $bcapp_db_connection->insert_id;
		  	}    
	}
	
} else{
	 var_dump($bcapp_db_connection->error);
}