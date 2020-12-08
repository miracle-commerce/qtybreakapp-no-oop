<?php
$products = $products;
$enableNextPage = $products["data"]["products"]["pageInfo"]["hasNextPage"];
$enablePrevPage = $products["data"]["products"]["pageInfo"]["hasPreviousPage"];
$products = $products["data"]["products"]["edges"];
if($enableNextPage){
    $nextPageCursor = $products[array_key_last($products)]["cursor"];
}

if($enablePrevPage){
    $prevPageCursor = $products[array_key_first($products)]["cursor"];
}

if(isset($schemeProducts) && count($schemeProducts) > 0){
    $schemeProductsIds = array();
    foreach($schemeProducts as $schemeProduct){
        $schemeProductId = $schemeProduct['pid']; 
        array_push($schemeProductsIds, $schemeProductId);
    }
}
?>
<div class="product-selector">
    <div class="shop-products">
        <div class="single-products-selector_wrapper">
            <?php
            foreach($products as $product){
                $product = $product[node];
                $product_id = explode("/", $product["id"])[4];
                $product_title = $product["title"];
                $product_price = $product["priceRangeV2"]["minVariantPrice"]["amount"];
                $product_img_url = $product["images"]["edges"][0]["node"]["src"];
                $has_scheme = false;
                if(isset($schemeProductsIds) && count($schemeProductsIds) > 0){
                    $has_scheme = in_array($product_id, $schemeProductsIds);
                }
            ?>
            <div class="single-product-selector_wrapper" 
                data-product-id="<?php echo $product_id; ?>"
                data-img="<?php if(!empty($product_img_url)){echo $product_img_url;}else{ echo ROOT_ASSETS_URL."/placeholder.png";}  ?>"
                data-title="<?php echo $product_title ?>"
                data-price="<?php echo $product_price ?>"
                >
                <div class="single-product-img">
                    <img src="<?php if(!empty($product_img_url)){echo $product_img_url;}else{ echo ROOT_ASSETS_URL."/placeholder.png";}  ?> " alt="" class="single-product-image">
                </div>
                <div class="single-product-title"><?php echo $product_title ?></div>
                <div class="single-product-price">$<?php echo $product_price ?></div>
                <div class="add-product-btn-wrapper">
                    <?php
                        if($has_scheme){
                    ?>
                    <a href="javascript:void(0)" class="product-action-btn" data-product-id="<?php echo $product_id; ?>" data-action="added"><span class="fa fa-check-circle"></span></a>
                    <?php
                        }else{
                    ?>
                    <a href="javascript:void(0)" class="product-action-btn" data-product-id="<?php echo $product_id; ?>" data-action="add">Add</a>
                    <?php
                        }
                    ?>
                   
                </div>
            </div>
            <?php    
            }
            ?>    
        </div>
        <?php
            if($enableNextPage || $enablePrevPage){
        ?>
            <div id="shop-product-pagination-wrapper" class="product-pagination-wrapper">
                <?php 
                if(isset($prevPageCursor)){
                ?>
                <a href="javascript:void(0)" class="pagenate-btn select-product-pagination prev" data-paginate="before" data-point="<?php echo $prevPageCursor; ?>"><span class="fa fa-angle-left"></span></a>
                <?php
                }
                ?>

                <?php
                    if(isset($nextPageCursor)){ 
                ?>
                <a href="javascript:void(0)" class="pagenate-btn select-product-pagination next" data-paginate="after" data-point="<?php echo $nextPageCursor; ?>"><span class="fa fa-angle-right"></span></a>
                <?php
                }
                ?>
            </div>
        <?php
        }
        ?>
    </div>
    <div class="selected-products">
        <div class="single-products-selector_wrapper">
        <?php if(isset($schemeProducts) && count($schemeProducts) > 0){
            foreach($schemeProducts as $key=>$product){
                $product_id = $product['pid'];
                $product_title = $product["title"];
                $product_price = $product["price"];
                $product_img_url = $product["image_url"];
        ?>
            <div class="single-product-selector_wrapper" 
                data-index="<?php echo $key; ?>"
                data-product-id="<?php echo $product_id; ?>" 
                data-img="<?php echo $product_img_url; ?>"
                data-title="<?php echo $product_title; ?>"
                data-price="<?php echo $product_price; ?>">
                <input type="hidden" name="products[<?php echo $key; ?>][id]" value="<?php echo $product_id; ?>">
                <input type="hidden" name="products[<?php echo $key; ?>][title]" value="<?php echo $product_title; ?>">
                <input type="hidden" name="products[<?php echo $key; ?>][image]" value="<?php echo $product_img_url; ?>">
                <input type="hidden" name="products[<?php echo $key; ?>][price]" value="<?php echo $product_price; ?>">
                <div class="single-product-img">
                    <img src="<?php echo $product_img_url; ?>" alt="" class="single-product-image">
                </div>
                <div class="single-product-title"><?php echo $product_title; ?></div>
                <div class="single-product-price"><?php echo $product_price; ?></div>
                <div class="add-product-btn-wrapper"><a href="javascript:void(0)" class="product-action-btn" data-product-id="<?php echo $product_id; ?>" data-action="remove">remove</a></div>
            </div>
        <?php }}?>
        </div>
    </div>
</div>