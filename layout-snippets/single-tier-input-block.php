<?php 
    function singleTierInputBlock($index, $tier=[]){
        if(array_key_exists('id', $tier)){
            $tid = $tier['id'];
        }
        if(array_key_exists('type', $tier)){
           $ttype = $tier['type']; 
        }
        if(array_key_exists('amount', $tier)){
            $tamount = $tier['amount'];
        }
        if(array_key_exists('quantity', $tier)){
            $tqty = $tier['quantity'];    
        }
        if(!isset($index)){
            die('Index params must be given.');
        }
        $basic_quantity = 2 + $index;
        $basic_amount = 10 * ($index + 1); 
?>
     
    <div class="single-tier-input-wrapper" data-block-index = "<?php echo $index; ?>">
        <div class="single-tier-block">
            <?php
                if(!isset($tid)){
            ?>
            <input type="hidden" name="tiers[<?php echo $index ?>][id]" value="<?php echo $tid; ?>" class="tier-id">
            <?php
                }
            ?>
            <span class="field-text buy">Buy</span>
            <input type="number" name="tiers[<?php echo $index ?>][quantity]" value="<?php if(isset($tqty)){echo $tqty;}else{ echo $basic_quantity; } ?>" class="tier-qty" required>
            <span class="field-text get">, Get</span>
            <input type="text" name="tiers[<?php echo $index ?>][amount]" class="tier-amount" value="<?php if(isset($tqty)){echo $tamount;}else{ echo $basic_amount; } ?>" required>
            <select name="tiers[<?php echo $index ?>][type]" class="tier-type" required>
                <option value="percentage" <?php if(isset($ttype) && $ttype == 'percentage'){?> selected <?php } ?>>%</option>
                <option value="fixed_amount" <?php if(isset($ttype) && $ttype == 'fixed_amount'){?> selected <?php } ?>>USD</option>
            </select>
            <span class="field-text off">off for each</span>
        </div>
        <?php 
        if($index > 0){
        ?>
        <a href="javascript:void(0);" class="remove-tier" data-block-index="<?php echo $index; ?>">remove</a>
        <?php
        }
        ?>
    </div>
<?php
    }
?>