<?php
header("Access-Control-Allow-Origin: *");
require_once("functions.php");
if(isset($_POST['shop_url'])){
    $shop_url = $_POST['shop_url'];
    $cart = $_POST['cart']; 
    $registeredShop = getShop($shop_url);     
    if(count($registeredShop) > 0){
        $access_token = $registeredShop['access_token'];
        $order_query = array("draft_order"=>array("tags"=>"beyondCommerce"));
        $total_quantity = 0;
        $total_spent_price = 0;
        $line_items = array();
        foreach($cart["line_items"] as $line_item){
            $line_item_quantity = intval($line_item["quantity"]);
            $total_quantity = $total_quantity + $line_item_quantity;
            $line_item_final_price = floatval($line_item["final_price"]) / 100;
            $final_line_price = $line_item_final_price * $line_item_quantity;
            $tid = $line_item["tierId"];

            if(!empty($tid)){
                $tier = getTierById($tid);
                if(count($tier) > 0){
                    $tier = $tier[0];
                    if($tier['type'] == 'fixed_amount'){
                        $itemDiscountTitle = "Buy ".$tier['quantity']." Get $".$tier['amount']." off for each";
                        $itemDiscountValue = floatval($tier['amount']);
                        $itemDiscountAmount = $itemDiscountValue * intval($line_item['quantity']);
                    }else if($tier['type'] == 'percentage'){
                        $itemDiscountTitle = "Buy ".$tier['quantity']." Get ".$tier['amount']."% off";
                        $itemDiscountValue = floatval($tier['amount']);
                        $itemDiscountAmount = $final_line_price * $itemDiscountValue / 100;  
                    }
                    $final_line_price = $final_line_price - $itemDiscountAmount;
                    // Create Item level discount Array;
                    $item_level_discount = array("title"=>$itemDiscountTitle, "value_type"=>$tier['type'], "value"=>number_format($itemDiscountValue, 3, '.', ''), "amount"=>number_format($itemDiscountAmount, 3, '.', ''));
                }
            }

            //Create single line_item object. 
            $single_line_item = array("variant_id"=>intval($line_item["variant_id"]), "quantity"=>$line_item_quantity);
            if(!empty($line_item["properties"])){
                $single_line_item["properties"] = $line_item["properties"];
            }
            if(isset($item_level_discount)){
                $single_line_item["applied_discount"] = $item_level_discount;
            }
            

            // Add single line item to lineitems array
            array_push($line_items, $single_line_item);

            //Add totalSpentPrice
            $total_spent_price = $total_spent_price + $final_line_price; 
        }

        // Add line_items to draft order
        $order_query["draft_order"]["line_items"] = $line_items;
        
        
        // Check if there is a DiscountAutomaticBasic that applies to all items only, then add cart level discount.
        $discountAutomaticBasic = getDiscountAutomaticBasic($shop_url, $access_token);
        $has_discount_autobasic = false; 
        $discountAutomaticBasic = $discountAutomaticBasic["data"]["automaticDiscountNodes"]["edges"][0]["node"]["automaticDiscount"];
        if($discountAutomaticBasic && $discountAutomaticBasic["customerGets"]["items"]["__typename"] == "AllDiscountItems" && $discountAutomaticBasic["customerGets"]["items"]["allItems"]){
            $minRequirement = $discountAutomaticBasic["minimumRequirement"];
            $minRequireType = $minRequirement["__typename"];
            switch($minRequireType){
                case "DiscountMinimumQuantity":
                    $minRequireQty = intval($minRequirement["greaterThanOrEqualToQuantity"]);
                    if($total_quantity >= $minRequireQty){
                        $has_discount_autobasic = true; 
                    }
                break;
                case "DiscountMinimumSubtotal":
                    $minRequirePrice = floatval($minRequirement["greaterThanOrEqualToSubtotal"]["amount"]);
                    if($total_spent_price >= $minRequirePrice){
                        $has_discount_autobasic = true;
                    }
                break;
                default:
                $has_discount_autobasic = false; 
            }
            
            // Check if discount timestamp
            $currentTimeStamp = time();
            $discountStartTimeStamp = strtotime($discountAutomaticBasic["startsAt"]);
            if($discountStartTimeStamp <= $currentTimeStamp && $has_discount_autobasic){
                if(!empty($discountAutomaticBasic["endsAt"])){
                    if($discountAutomaticBasic["endsAt"] >= $currentTimeStamp ){
                        $has_discount_autobasic = true;    
                    }else{
                        $has_discount_autobasic = false; 
                    }
                }else{
                    $has_discount_autobasic = true; 
                }              
            }
            if($has_discount_autobasic){
                $cart_level_discount_title = $discountAutomaticBasic["title"];
                $discountAutomaticValue = $discountAutomaticBasic["customerGets"]["value"];
                $discountAutomaticValueType = $discountAutomaticValue["__typename"];
                switch($discountAutomaticValueType){
                    case "DiscountAmount":
                        $cart_level_discount_type = "fixed_amount";
                        $cart_level_discount_value = floatval($discountAutomaticValue["amount"]["amount"]);
                        $cart_level_discount_amount = $cart_level_discount_value;
                    break;
                    case "DiscountPercentage":
                        $cart_level_discount_type = "percentage";
                        $cart_level_discount_value = floatval($discountAutomaticValue["percentage"]);
                        $cart_level_discount_amount = $total_spent_price * $cart_level_discount_value;
                        $cart_level_discount_value = $cart_level_discount_value * 100;
                    break;
                }

                $cart_level_discount = array("title"=>$cart_level_discount_title, "value"=>$cart_level_discount_value, "value_type"=>$cart_level_discount_type, "amount"=>number_format($cart_level_discount_amount, 3, '.', ''));
                $order_query["draft_order"]["applied_discount"] = $cart_level_discount;
            }
        }

        
        $order_query = json_encode($order_query);
        $draft_order = createDraftOrder($shop_url, $access_token, $order_query);
        //return new order data to Shop
        echo json_encode($draft_order);
    }else{
        $error = array("errors"=>"Shop has been not registered.");
        echo json_encode($error);
    }
}