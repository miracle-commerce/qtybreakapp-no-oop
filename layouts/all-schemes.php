<?php
    // This layout must be rendered in index.php. 
    // index.php should be include 'header.php', so can use all functions called in this file.
        $shop_url = $_GET['shop']; 
        // Get values for paginate.
        $limit = 20; 
        if(isset($_GET['page'])){
            $start_index = $limit * ($_GET['page'] - 1);
        } else{
            $start_index = 0;
        }
        // Get All Schemes
        $all_schemes = getSchemes($shop_url);
        $total_count = count($all_schemes);
        if($total_count > $limit){
            $num_pages = ceil($total_count/$limit);
        }
        $all_schemes = array_slice($all_schemes, $start_index, $limit);
?>
    <div class="all-schemes-wrapper">
        <div class="add-scheme-button-wrapper">
            <a href="<?php echo ROOT_URL.'layouts/create-scheme.php'?>" class="add-tier-button bcapp-btn__primary bc-app-links">Add New Pricing Tier</a>
        </div>
        <?php if($all_schemes){?>
        <div class="scheme-list_container">
            <div class="scheme-row list-header">
                <div class="scheme-cell title">List of Tiered Pricing</div>
                <div class="scheme-cell applies_to">Applies To</div>
                <div class="scheme-cell act-buttons">Actions</div>
            </div>
            <?php foreach($all_schemes as $i=>$scheme){ 
            $sid = $scheme['id'];
            $editSchemeUrl = ROOT_PATH."layouts/update-scheme.php?sid=".$sid;
            $removeSchemeUrl = ROOT_PATH."basic-modules/scheme/remove?sid=".$sid;
            ?>
            <div class="scheme-row">
                <div class="scheme-cell title">
                    <a href="<?php echo $editSchemeUrl?>"class="bc-app-links"><?php echo $scheme['title']?></a>   
                </div>
                <div class="scheme-cell applies_to">
                    <?php
                        if($scheme['all_products']){
                            echo 'All Products';
                        }else{
                            echo 'Specific Products';
                        }
                    ?>
                </div>
                <div class="scheme-cell act-buttons">
                    <a href="<?php echo $editSchemeUrl ?>" class="bc-app-links bcapp-btn__primary edit-scheme">Edit</a>
                    <a href="javascript:void(0)" class="bc-app-links bcapp-btn__primary remove-scheme" data-scheme="<?php echo $scheme['id'] ?>">Remove</a>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php }
            if(isset($num_pages)){
        ?>
        <div class="pagination-wrapper">
            <?php if($_GET['page'] > 1){ ?>
            <a href="<?php echo $_SERVER['SCRIPT_URL']."?page=".($_GET['page'] - 1) ?>" class="bc-app-links pagenate-btn arrow"><span class="page-control-next fa fa-angle-left"></span></a>
            <?php }?>
            <?php for ($i=1; $i<=$num_pages; $i++){ ?>
                <a href="<?php echo $_SERVER['SCRIPT_URL']."?page=".$i ?>" class="bc-app-links pagenate-btn <?php if($_GET['page']==$i || !isset($_GET['page']) && $i==1){ ?> active <?php }; ?>"><span class="page-control page-num <?php if($_GET['page']==$i){?>active<?php } ?>"><?php echo $i; ?></span></a>
            <?php }
                if($_GET['page'] < $num_pages){
            ?>
            <a href="<?php echo $_SERVER['SCRIPT_URL']."?page=".($_GET['page'] + 1) ?>" class="bc-app-links pagenate-btn arrow"><span class="page-control-next fa fa-angle-right"></span></a>
                <?php } ?>
        </div>
        <?php
            }
        ?>
    </div>
