<?php
// Request API
require_once("basic.php");
function requestRestAPI($access_token, $shop, $method, $api_version, $endpoint, $query_params=array()){
    // Params;
    // $shop is the admin url of Shop. We can get this from uri by "GET" method such as "$_GET['shop']"
    // $endpoint is endpoint of Shopify's Rest API. 
    // $query_params is query and it's should be array. 
    $api_ch = curl_init();
    $request_url = "https://$shop/admin/api/$api_version/$endpoint";
    $ch_header = array(
        "X-Shopify-Access-Token: ".$access_token,
        "Content-Type: application/json",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Host: ".$shop
    );

    curl_setopt($api_ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($api_ch, CURLOPT_HTTPHEADER, $ch_header);
    curl_setopt($api_ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($api_ch, CURLOPT_URL, $request_url);
    $apiResponse = curl_exec($api_ch);
    
    $error_num = curl_errno($api_ch);
    $error_message = curl_error($api_ch);
    curl_close($api_ch);
    
    if($error_num){
        echo $error_message;
        die();
    } else{
        $apiResponse = json_decode($apiResponse);
        return $apiResponse;    
    }
};
function requestGraphQl($shop_url, $accessToken, $query){
    $request_url = "https://$shop_url/admin/api/".API_VERSION."/graphql.json";
    $ch_header = array(
        "X-Shopify-Access-Token: $accessToken",
        "Content-Type: application/json",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
    );
    $api_ch = curl_init();
    curl_setopt_array($api_ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>$query,
        CURLOPT_HTTPHEADER => $ch_header
      ));

    $result = curl_exec($api_ch);
    curl_close($api_ch);
    if(curl_errno($api_ch) > 0){
        echo "curl error";
        echo curl_error($api_ch);
    }else{
        $results = json_decode($result, true);
        return $results;    
    }
    
};

// request Products By GraphQL
function requestProducts($shop_url, $access_token, $query){
    $query_variable = "\"variables\":{";
        if(isset($query['search']) && isset($query['cursor'])){
            $searchQuery = $query['search']; 
            $cursor = $query['cursor'];
            $query_variable = $query_variable."\"query\":\"title:'$searchQuery'*\",\"cursor\":\"$cursor\"";
        } elseif(isset($query['search']) && !isset($query['cursor'])){
            $searchQuery = $query['search'];
            $query_variable = $query_variable."\"query\":\"title:'$searchQuery'*\"";
        } elseif(!isset($_POST['search']) && isset($query['cursor'])){
            $cursor = $query['cursor'];
            $query_variable = $query_variable."\"cursor\":\"$cursor\"";
        };
        
        $query_variable = $query_variable."}";
        if(!isset($_POST["pagenationType"]) || $_POST["pagenationType"] == "after"){
            $fetchPoint = "first";
            $fetchType = "after";
        }elseif($_POST["pagenationType"] == "before"){
            $fetchPoint = "last";
            $fetchType = "before";
        }

        $product_query = "{\"query\":\"query(\$query: String, \$cursor: String ){\\r\\n  products($fetchPoint:10, $fetchType:\$cursor, query: \$query) {\\r\\n    edges {\\r\\n      node {\\r\\n        id\\r\\n        title\\r\\n        priceRange {\\r\\n          minVariantPrice {\\r\\n            amount\\r\\n            currencyCode\\r\\n          }\\r\\n        }\\r\\n        priceRangeV2 {\\r\\n          minVariantPrice {\\r\\n            amount\\r\\n            currencyCode\\r\\n          }\\r\\n        }\\r\\n        images(first: 1) {\\r\\n          edges {\\r\\n            node {\\r\\n              originalSrc\\r\\n              src\\r\\n            }\\r\\n          }\\r\\n        }\\r\\n      }\\r\\n      cursor\\r\\n    }\\r\\n    pageInfo {\\r\\n      hasNextPage\\r\\n      hasPreviousPage\\r\\n    }\\r\\n  }\\r\\n}\",";
        
        $product_query = "$product_query $query_variable}";

        return requestGraphQl($shop_url, $access_token, $product_query);

}

// Get Access Token
function requestAccessToken($shop, $query_params){
    $token_request_url = "https://".$shop."/admin/oauth/access_token";
    $token_ch = curl_init();
    curl_setopt_array($token_ch, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $token_request_url,
        CURLOPT_POST => count($query_params), 
        CURLOPT_POSTFIELDS => http_build_query($query_params)
    ));
    
    $token_result = curl_exec($token_ch);
    curl_close($token_ch);
    if(curl_errno($token_ch) > 0){
        echo curl_error($token_ch);
    }else{
        $token_result = json_decode($token_result, true);
        $access_token = $token_result["access_token"];
        return $access_token;    
    }
    
};


//Verify Shop Request
function verifyRequest(){
	$params = $_GET;
	$hmac = $params['hmac']; 
	// Remove hmac from params
	$params = array_diff_key($params, array('hmac' => ''));
	ksort($params); // Sort params lexographically
	$computed_hmac = hash_hmac('sha256', http_build_query($params), APPSECRET);
	if(hash_equals($hmac, $computed_hmac)){
		return true;
	} else{
		return false; 
	}
};
// Get AccessToken from database; 
function getAccessToken($shopUrl){
    $tokenSql = "Select access_token FROM ".STORES_TABLE." WHERE ShopUrl = '$shopUrl'";
    global $bcapp_db_connection; 
    $token_result = $bcapp_db_connection->query($tokenSql);
    if($token_result){
        $token_result = $token_result->fetch_assoc();
        return $token_result;
    }else{
        echo("Error description:".$bcapp_db_connection->error);
    }
}
// Register Shop
function registerShop($access_token, $shop){
    global $bcapp_db_connection;
    $shop_data = requestRestAPI($access_token, $shop, 'GET', API_VERSION, 'shop.json');
    $shop_data = $shop_data->shop;
    $shopId = $shop_data->id;
    $selectShopQuery = "SELECT id FROM ".STORES_TABLE." WHERE access_token='".$access_token."' and ShopUrl='".$shop."'";
    $shop_result = $bcapp_db_connection->query($selectShopQuery);
    if($shop_result){
        $shopresult = $shop_result->fetch_assoc();
        if(!$shopresult){
            $registerShopSql = "INSERT INTO RegisteredStores (ShopId, ShopUrl, access_token) VALUES ($shopId, '$shop','$access_token')";
            if($bcapp_db_connection->query($registerShopSql)){
                echo "Shop has been registered successfully.";
            } else{
                echo("Error description:".$bcapp_db_connection->error);
            }
        }
    } else{
        echo("Error description:".$bcapp_db_connection->error);
    }
};

// Get Shop from database
function getShop($shop_url){
    global $bcapp_db_connection; 
    $getShopSql = "SELECT * FROM ".STORES_TABLE." WHERE ShopUrl='".$shop_url."'AND activate=true";
    $shop_result = $bcapp_db_connection->query($getShopSql);
    if($shop_result){
        return $shop_result->fetch_assoc();
    }else{
        echo("Error description:".$shop_con->error);
        return false; 
    }
};
/*===========================================================
Functions for scheme
===========================================================*/

//Get Schemes from database
function getSchemes($shop_url){
    global $bcapp_db_connection;

    $scheme_sql = "SELECT * FROM ".SCHEMES_TABLE." WHERE shop_url='".$shop_url."' AND activate=1 ORDER BY updatedate DESC";
    $scheme_result = $bcapp_db_connection->query($scheme_sql);
    if($scheme_result){
        return $scheme_result->fetch_all(MYSQLI_ASSOC);    
    } else{
        return false;
    }
};

// Get Single Scheme By sid
function getSingleScheme($sid, $conn){
    $sql = "SELECT * FROM schemes WHERE id = $sid";
    $result = $conn->query($sql);
    if($result){
        $scheme = $result->fetch_all(MYSQLI_ASSOC);
        if(count($scheme)){
            return $scheme[0];
        }else{
            echo "<p class='alert'>There is no schemes that its id is $sid</p>";
        }
    }else{
        echo $bcapp_db_connection->error();
    }
}
// Remove Scheme by Sid
function removeSchemeById($id){
    global $bcapp_db_connection;
    $sql = "DELETE FROM ".SCHEMES_TABLE." WHERE id=$id";
    if($bcapp_db_connection->query($sql)){
        return true; 
    }else{
        $alert=array("Error"=>"$bcapp_db_connection->error");
        echo $alert; 
        return false;
    }
}
/*=============================================
Scripts for Tieres
===============================================*/
// Add Tieres to database 
function addTiers($shop_url, $sid, $tiers, $conn){
    function createSingleTierSql($singleTier){
        global $shop_url, $sid;
        $singleTierParam = "('{$shop_url}', $sid, '{$singleTier['type']}', {$singleTier['amount']}, {$singleTier['quantity']}, CURRENT_TIMESTAMP)";
        return $singleTierParam;
    }
    $tiersValuesSql = implode(",", array_map("createSingleTierSql", $tiers));
    $addTiersQuery = "INSERT INTO ".TIERES_TABLE." (shop_url, schemeid, type, amount, quantity, update_date) VALUES {$tiersValuesSql}";

    if($conn->query($addTiersQuery)){
        return true;
    }else{
        return false; 
    }
}

// Get Tiers from database
function getTieres($sid){
    global $bcapp_db_connection;
    $sql = "SELECT id, quantity, amount, type FROM ".TIERES_TABLE." WHERE schemeid=$sid";
    $tieres_result = $bcapp_db_connection->query($sql);
    if($tieres_result){
        return $tieres_result->fetch_all(MYSQLI_ASSOC);
    }else{
        echo("Error description:".$bcapp_db_connection->error);
    }
}
function getTierById($tid){
    global $bcapp_db_connection; 
    $sql = "SELECT quantity, amount, type FROM ".TIERES_TABLE." WHERE id=$tid";
    $tier_result = $bcapp_db_connection->query($sql); 
    if($tier_result){
        return $tier_result->fetch_all(MYSQLI_ASSOC);
    }else{
        return false; 
    }
}
// Remove Tiere By SchemeId
function removeTieresBySid($sid){
    global $bcapp_db_connection; 
    $sql = "DELETE FROM ".TIERES_TABLE." WHERE schemeid=$sid";
    if($bcapp_db_connection->query($sql)){
        return true; 
    }else{
        $alert=array("Error"=>"$bcapp_db_connection->error");
        echo $alert; 
        return false;
    }
}
/*=============================================================
Scripts for Products
===============================================================*/

// Add Products by sid
function addProducts($shop_url, $sid, $products, $conn){
    function productSql($product){
        global $shop_url, $sid;
        $productQuery = "('".$shop_url."',".$sid.",".$product['id'].",'".addslashes($product['title'])."','".$product['image']."',".$product['price'].")";
         return $productQuery;
   }
   $addProductsParams = implode(",", array_map("productSql", $products));
   $addProductsSql = "INSERT INTO Products (shopurl, sid, pid, title, image_url, price) VALUES ".$addProductsParams;
   if($conn->query($addProductsSql)){
       return true;
   }else{
       return false; 
   }
}
// Remove products by SchemeId
function removeProductsBySid($sid){
    global $bcapp_db_connection; 
    $sql = "DELETE FROM ".PRODUCTS_TABLE." WHERE sid=$sid";
    if($bcapp_db_connection->query($sql)){
        return true;
    }else{
        $alert=array("Error"=>"$bcapp_db_connection->error");
        echo $alert; 
        return false;
    }
}
// Get Product by Schemeid
function getProductsBySid($sid, $conn){
    $sql = "SELECT pid, title, image_url, price FROM Products WHERE sid=$sid";
    $products_result = $conn->query($sql);
    if($products_result){
        $products = $products_result->fetch_all(MYSQLI_ASSOC);
            return $products;
    }else{
        echo ("Error descripion:".$conn->error);
    }
};

// Get Products Ids by Schemeid
function getProductsIdsBySid($sid, $conn){
    $productIds = array();
    $sql = "SELECT pid FROM Products WHERE sid=$sid";
    $products_result = $conn->query($sql);
    if($products_result){
        $products = $products_result->fetch_all(MYSQLI_ASSOC);
        foreach($products as $product){
            array_push($productIds, $product['pid']);
        }
            return $productIds;
    }else{
        echo ("Error descripion:".$conn->error);
    }
}

// Get All Products by Shop_url
function getAllRegisteredProducts($shop, $conn){
    $sql = "SELECT * FROM Products WHERE shopurl='$shop' AND exist_products == true";
    $products_result = $conn->query($sql);
    if($products_result){
        $products = $tieres_result->fetch_all(MYSQLI_ASSOC);
        if($conn->field_count() > 0 ){
            return $products;
        } else{
            return [];
        }
    }else{
        echo ("Error descripion:".$conn->error);
    }
}

//remove products from database based on shop_url, and productid
function removeProducts($shop_url, $pids, $conn){
    $pids = "(".implode(",", $pids).")";
    $rpsql = "DELETE FROM ".PRODUCTS_TABLE." WHERE pid IN $pids";
    $removeProductsResult = $conn->query($rpsql);
    if($removeProductsResult){
        return true; 
    }else{
        return false; 
    }
};
/*======================================================
Script for Orders
=======================================================*/
// Get actived Automatic discounts from Shop
function getDiscountAutomaticBasic($shop_url, $access_token){
    $query = "{\"query\":\"{\\r\\n  automaticDiscountNodes(first: 1, query: \\\"status:active\\\") {\\r\\n    edges {\\r\\n      node {\\r\\n        id\\r\\n        automaticDiscount {\\r\\n          ... on DiscountAutomaticBasic {\\r\\n            title\\r\\n            startsAt\\r\\n            endsAt\\r\\n            customerGets {\\r\\n              items {\\r\\n                ... on AllDiscountItems {\\r\\n                  __typename\\r\\n                  allItems\\r\\n                }\\r\\n              }\\r\\n              value {\\r\\n                ... on DiscountAmount {\\r\\n                  __typename\\r\\n                  amount {\\r\\n                    amount\\r\\n                    currencyCode\\r\\n                  }\\r\\n                  appliesOnEachItem\\r\\n                }\\r\\n                ... on DiscountOnQuantity {\\r\\n                  __typename\\r\\n                  quantity {\\r\\n                    quantity\\r\\n                  }\\r\\n                }\\r\\n                ... on DiscountPercentage {\\r\\n                  __typename\\r\\n                  percentage\\r\\n                }\\r\\n              }\\r\\n            }\\r\\n            minimumRequirement {\\r\\n              ... on DiscountMinimumQuantity {\\r\\n                __typename\\r\\n                greaterThanOrEqualToQuantity\\r\\n              }\\r\\n              ... on DiscountMinimumSubtotal {\\r\\n                __typename\\r\\n                greaterThanOrEqualToSubtotal {\\r\\n                  amount\\r\\n                  currencyCode\\r\\n                }\\r\\n              }\\r\\n            }\\r\\n          }\\r\\n        }\\r\\n      }\\r\\n    }\\r\\n  }\\r\\n}\\r\\n\",\"variables\":{}}";
    return requestGraphQl($shop_url, $access_token, $query);
}

function createDraftOrder($shop_url, $access_token, $order_query){
    $request_url = "https://$shop_url/admin/api/".API_VERSION."/draft_orders.json";
    $ch_header = array(
        "X-Shopify-Access-Token: $access_token",
        "Content-Type: application/json",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
    );
    $api_ch = curl_init();
    curl_setopt_array($api_ch, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>$order_query,
        CURLOPT_HTTPHEADER => $ch_header
    ));

    $result = curl_exec($api_ch);
    curl_close($api_ch);
    if(curl_errno($api_ch)){
        $order_result = curl_errno($api_ch);
    }else{
        $order_result = json_decode($result)->draft_order->invoice_url;
        $order_result = array("invoice_url"=>$order_result);
    }

    return $order_result;
}