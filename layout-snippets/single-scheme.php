<?php
// All layout snippets must be rendered in layout files

if(isset($render_params)){
    $params = $render_params;
}
    // $params: associative array
    // ex: ["action"=>"update or create", "stitle"=>"", "sid"=>interger(schemeid), "tieres"=[]]
    $pageAct = $params['action'];
    if($pageAct == "update"){
        $actionUrl = "../basic-modules/scheme/update.php";
    }elseif($pageAct == "create"){
        $actionUrl = "../basic-modules/scheme/add.php";
    }
?>

<div class="edit-schemes-wrapper main-content">
    <div class="page-loader-wrapper">
        <img src="<?php echo ROOT_ASSETS_URL."/page-loader.gif" ?>" alt="page-loader-gif" class="page-loader-image">
    </div>
    <form action="<?php echo $actionUrl ?>" method="POST" class="bc-app-form" id="<?php if($pageAct == 'create'){echo 'create-scheme';}elseif($pageAct == 'update'){echo 'update-scheme';}?>">
    <?php
        if($params['sid']){
    ?>
    <input type="hidden" name="sid" value="<?php echo $params['sid']?>">
    <?php
        }
    ?>
    <input type="hidden" name="shop_url" value="<?php echo $params['shop_url'] ?>">
    <div class="edit-scheme-block edit-tieres">
        <h3 class="block-title">Tiered Pricing</h3>
        <div class="app-block-content scheme-settings-block">
            <div class="input-block scheme-title-block">
                <label for="scheme-title">Name*</label>
                <input type="text" name="title" id="scheme-title" value="<?php if($pageAct == 'update'){ echo $params['stitle']; } ?>" required>
            </div>
            <div class="input-block tiers-settings-block">
                <?php
                    include('single-tier-input-block.php'); 
                    if($pageAct == "update"){
                        $tieres = $params["tieres"];
                        for($i = 0; $i < count($tieres); $i++){
                            $tier = $tieres[$i];
                            singleTierInputBlock($i, $tier);
                        } // endfor
                    }elseif($pageAct == "create"){
                        for($i = 0; $i < 3; $i++){
                            singleTierInputBlock($i);
                        }// endfor
                    }
                ?>
            </div>
            <a href="javascript:void(0)" id="add-tier-btn" class="bcapp-btn__primary">Add Tier</a>
        </div>
    </div>
    <!-- Tiered Pricing Layouts -->
    <div class="edit-scheme-block pricing-table-settings">
        <h3 class="block-title">Tiered Pricing Table</h3>
        <div class="pricing-table-settings-content">
            <div class="table-settings-subblock">
                <label for="scheme_message">Message the user sees about the tiered pricing*</label>
                <input id="scheme_message" type="text" name="message" value="<?php if(isset($params["message"])){echo $params["message"];}else{ ?> Get more. Pay less. <?php } ?>" required>
            </div>
            <div class="table-settings-subblock">
                <label for="tier_template">Tier message template*</label>
                <input id="tier_template" type="text" name="tier_template" value="<?php if(isset($params["tier_template"])){echo $params["tier_template"];}else{ ?> Buy [Q] for [E] each([D]% off!) <?php } ?>" required>
                <div class="tier-variables-descriptions">
                    <span class="description-header">Variable descriptions</span>
                    <div class="description-content">
                        <p>[Q]: tier quantity</p>
                        <p>[E]: price of each product with discount</p>
                        <p>[T]: total price with discount</p>
                        <p>[D]: discount in percent</p>
                    </div>
                </div>
            </div>
            <div class="table-settings-subblock preview-table-block">
                <span class="table-label">Preview:</span>
                <div class="tier-pricing-table-wrapper">
                    <div class="tier-pricing-table">
                        <div class="table-header"><?php if(isset($params["message"])){echo $params["message"];}else{?>Get more. Pay less.<?php } ?></div>
                        <div class="table-body">
                            <?php 
                                $tier_template = 'Buy [Q] for [E] each([D]% off!)';
                                if(isset($params)){
                                    if(isset($params["tier_template"])){
                                        $tier_template = $params["tier_template"];
                                    }
                                }
                                if(isset($params) && count($params["tieres"])){
                                    for($i = 0; $i < count($tieres); $i++){
                                        $tier = $tieres[$i];
                                        $tier_quantity = $tier["quantity"]; 
                                        $tier_discount_type = $tier["type"]; 
                                        $tier_amount = $tier["amount"]; 
                                        if($tier_discount_type === "fixed_amount"){
                                            $tier_discount_percentage = "Y";
                                        }elseif($tier_discount_type == "percentage"){
                                            $tier_discount_percentage = $tier_amount;
                                        }
                                        $eachDiscountedPrice = '\$E';
                                        $totalDiscountedPrice = '\$T';
                                        $tier_template_patterns = array('/\[Q\]/i', '/\[E\]/i', '/\[T\]/i', '/\[D\]/i');
                                        $tier_template_replacements = array($tier_quantity, $eachDiscountedPrice, $totalDiscountedPrice, $tier_discount_percentage); 
                            ?>
                            <p class="tier-content" data-block-index="<?php echo $i;?>"><?php echo preg_replace($tier_template_patterns, $tier_template_replacements, $tier_template);?></p>
                            <?php
                                    } // endfor
                                }else{
                                    for($i = 0; $i < 3; $i++){
                                        $basic_quantity = 2 + $i;
                                        $basic_amount = 10 * ($i + 1);                                 
                            ?>
                            <p class="tier-content" data-block-index="<?php echo $i;?>">Buy <?php echo $basic_quantity; ?> for Y each(<?php echo $basic_amount; ?>% off!)</p>
                            <?php
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="pricing-table-location-settings">
                    <p class="widget-selector-description">
                    The pricing table will be placed bottom of the 'add to cart form' element in product page. If you want to change that location, copy below code then paste it where you want on product page.
                    </p>
                    <div class="widget-container-data">
                        <span class="widget-html-content">
                            <?php echo htmlspecialchars('<div class="bcapp-pricing-table-container"></div>'); ?>    
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- Product Settings -->
    <div class="edit-scheme-block product-settings">
        <h3 class="block-title">Select Products</h3>
        <!-- Include product search widget -->
        <div class="product-settings-wrapper">
            <div class="product-select-options-wrapper">
                <div class="radio-block">
                    <input type="radio" name="all_products" value="true" id="product-select-all" <?php if($params["all_products"]){echo 'checked';}?>>
                    <label for="product-select-all">All Products</label>    
                </div>
                <div class="radio-block">
                    <input type="radio" name="all_products" value="false" id="product-select-specific" <?php if(!$params["all_products"]){echo "checked";}?>>
                    <label for="product-select-specific">Specific Products</label>
                </div>
            </div>
            <div class="product-specific-select-wrapper <?php if($params["all_products"]){echo 'hide'; }?>">
                <div class="product-search-widget">
                    <input type="text" name="search" placeholder="Search products" id="product-search">
                    <a href="javascript:void(0)" id="search_submit" value="Search" class="bcapp-btn__primary">Search</a>
                </div>
                <?php include("product-selector.php");?>
            </div>
        </div>
    </div>
    <input type="submit" value="Save Settings" class="bcapp-btn__primary">
    </form> 
</div>