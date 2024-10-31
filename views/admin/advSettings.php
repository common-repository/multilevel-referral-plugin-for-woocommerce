<?php
if(isset($_POST['save'])){
    if(isset($_POST['wmc-levelbase-credit'])){
        update_option('wmc-levelbase-credit',sanitize_text_field($_POST['wmc-levelbase-credit']));
    }
    if(isset($_POST['wmc-max-level'])){
        update_option('wmc-max-level',sanitize_text_field($_POST['wmc-max-level']));
    }
    if(isset($_POST['wmc-customer-based-bonus'])){
        update_option( 'wmc_customer_based_bonus', 1 );
    }else{
        update_option( 'wmc_customer_based_bonus', 0 );
    }
    /*if(isset($_POST['wmc-max-referrals'])){
        update_option('wmc-max-referrals',sanitize_text_field($_POST['wmc-max-referrals']));
    }*/
    if(isset($_POST['wmc-level-c'])){
        update_option('wmc-level-c',$_POST['wmc-level-c']);    
    }
    if(isset($_POST['wmc-level-credit'])){
        update_option('wmc-level-credit',$_POST['wmc-level-credit']);    
    }
    if(isset($_POST['wmc-earning-method'])){
        update_option('wmc-earning-method',sanitize_text_field($_POST['wmc-earning-method']));
    }
    if(isset($_POST['wmc-customer-based-bonus']) && isset($_POST['wmc-level-c-without-link'])){
        update_option('wmc-level-c-new',$_POST['wmc-level-c-without-link']);    
    }
    if(isset($_POST['wmc-customer-based-bonus']) && isset($_POST['wmc-level-c-with-link'])){
        update_option('wmc-level-c',sanitize_text_field($_POST['wmc-level-c-with-link']));
    }

    if(isset($_POST['wmc_customer_order_based_bonus'])){
        update_option( 'wmc_customer_order_based_bonus', 1 );
    }else{
        update_option( 'wmc_customer_order_based_bonus', 0 );
    }
    if( isset( $_POST['wmc_order_level_credit'] )){
        update_option('wmc_order_level_credit',$_POST['wmc_order_level_credit']);
    }
    if( isset( $_POST['wmc_bouns_offere_type'] )){
        update_option('wmc_bouns_offere_type',$_POST['wmc_bouns_offere_type']);
    }
}

$getcustorder = get_option('wmc_customer_order_based_bonus', 0);
$getorderlevel = get_option('wmc_order_level_credit', array());
$getbounsoffer = get_option('wmc_bouns_offere_type');

$isLevelBaseCredit= get_option('wmc-levelbase-credit',0);
$earningMethod= get_option('wmc-earning-method','product');
$maxLevels=get_option('wmc-max-level',1);
//$maxReferrals=get_option('wmc-max-referrals',0);
$maxLevelCredits=get_option('wmc-level-credit',array());
$customerCredits=get_option('wmc-level-c',0);
$customerCreditsNew=get_option('wmc-level-c-new',0);
$globalStoreCredit=get_option('wmc_store_credit',0);
$customerBonus = get_option( 'wmc_customer_based_bonus', 0 );
$credit_type = get_option( 'wmc_credit_type', 'percentage' );
$class="wmc-hide";
if($isLevelBaseCredit){
    $class="";
}
if( !$customerCredits ){
    $customerCredits = 0;
}
if( !$customerCreditsNew ){
    $customerCreditsNew = 0;
}
if( !$globalStoreCredit ){
    $globalStoreCredit = 10;
}
$credit_type_class = '';
$max_input_value = 100;
if( $credit_type == 'fixed' ){
    $credit_type_class = "wmc-hide";
    $max_input_value = 10000;
}
?>
<div class="wmc-advSettings">
    <form method="post" action="">
        <h2><?php echo __('Level based credit system','wmc');?></h2>
        <p><?php echo __('The level based credit percentage will be applied on product price for each level affiliate users.','wmc');?></p>
        <table class="form-table wmc-level-table">
            <tbody>
                <tr valign="top">
                    <th scope="row" class="titledesc"><label><?php echo __('Enable','wmc');?> / <?php echo __('Disable','wmc');?></label></th>
                    <td class="forminp">
                        <fieldset>
                            <label for="wmc-levelbase-credit-disable">
                            <?php 
                            $Dchecked='';
                            $Echecked='';
                            if($isLevelBaseCredit){
                                $Echecked='checked="checked"';
                            }else{
                                $Dchecked='checked="checked"';
                            }
                            ?>
                                <input type="radio" id="wmc-levelbase-credit-disable" name="wmc-levelbase-credit" value="0" <?php echo $Dchecked;?>><?php echo __('Disable level based credit system.','wmc')?> 
                            </label>
                        </fieldset>
                        <fieldset>
                            <label for="wmc-levelbase-credit-enable">
                                <input type="radio" <?php echo $Echecked;?> id="wmc-levelbase-credit-enable" name="wmc-levelbase-credit" value="1"><?php echo __('Enable level based credit system.','wmc')?> 
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top" class="wmc-optional-bouns">
                     <th scope="row" class="" ><label for="wmc-max-level"><?php echo __('Select Bonus Offers Type','wmc'); ?> </label></th> 
                     <td class="forminp">
                         <select class="wmc-bouns-offere-type" name="wmc_bouns_offere_type">
                             <option value="wmc_user" <?php if($getbounsoffer == 'wmc_user') echo 'selected'; ?>><?php _e('User','wmc'); ?></option>
                             <option value="wmc_order" <?php if($getbounsoffer == 'wmc_order') echo 'selected'; ?>><?php _e('Order', 'wmc'); ?></option>
                         </select>
                     </td>      
                </tr>                
                <tr valign="top" class="wmc-optional <?php echo $class;?>">
                     <th scope="row" class="titledesc" ><label for="wmc-max-level"><?php echo __('Maximum number of levels','wmc'); ?> </label></th> 
                     <td class="forminp">
                        <input type="number" readonly="readonly" name="wmc-max-level" id="wmc-max-level" class="form-field" min="1" value="<?php echo $maxLevels;?>"> 
                        
                     </td>      
                </tr>
                <!--tr valign="top" class="wmc-optional <?php echo $class;?>">
                     <th scope="row" class="titledesc" ><label for="wmc-max-referrals"><?php echo __('Maximum number of referrals on each level','wmc'); ?> </label></th> 
                     <td class="forminp">
                        <input type="number"  name="wmc-max-referrals" id="wmc-max-referrals" class="form-field" placeholder="0" min="0" value="<?php echo $maxReferrals;?>"> 
                        <p class="description"><?php echo __('Input "0 (ZERO)" for no limitations, 2 for Binary and 3 for turnary Tree','wmc'); ?></p>
                     </td>      
                </tr-->
                <tr valign="top" class="wmc-optional wmc_advsetting_credit  <?php echo "$credit_type_class $class"; ?>">
                    <th scope="row" class="titledesc"><label><?php echo __('Credit / Commission Earning Method options','wmc');?> </label></th>
                    <td class="forminp">
                        <fieldset>
                            <label for="wmc-product-base">
                            <?php 
                            $Pchecked='';
                            $Cchecked='';
                            $discountAmount = 0;
                            if($earningMethod=='product'){
                                $Pchecked='checked="checked"';
                            }else if($earningMethod=='commission'){
                                $Cchecked='checked="checked"';
                            }
                            $productPrice=3900;
                            ?>
                                <input type="radio" id="wmc-product-base" name="wmc-earning-method" value="product" <?php echo $Pchecked;?>><?php echo __('Product Price.','wmc')?> 
                                <p class="description"><?php echo __('With this method, The Referral Plugin will make use of the direct product price to calculate commission for each level.','wmc').'<br>'. __('e.g. Suppose Product "A" is priced at ','wmc').wc_price($productPrice).__(' then, referrals of each level will receive commission/credits as summarised below:','wmc') .'</p><ul>';  
                                echo '<li>'.__('Customer','wmc').' -  : '.$productPrice.' * '.$customerCredits.'% = '.wc_price(($productPrice*$customerCredits)/100).'</li>';
                                if( !$discountAmount && $Pchecked ){
                                    $discountAmount = wc_price(($productPrice*$customerCredits)/100);
                                }
                                for($i=0;$i<$maxLevels;$i++){
                                    if( isset( $maxLevelCredits[$i] ) && !empty( $maxLevelCredits[$i] ) ):
                                    echo '<li>'.__('Referrer Level','wmc').' - '.($i+1).' : '.$productPrice.' * '.$maxLevelCredits[$i].'% = '.wc_price(($productPrice*$maxLevelCredits[$i])/100).'</li>';
                                    endif;
                                }    
                                ?></ul>
                            </label>
                        </fieldset>
                        <fieldset>
                            <label for="wmc-commission-base">
                                <input type="radio" <?php echo $Cchecked;?> id="wmc-commission-base" name="wmc-earning-method" value="commission"><?php echo __('Commission/Credit','wmc')?> 
                                <p class="description"><?php echo __('This method is more lucid and is widely used and supported. Here The Referral Plugin will first calculate the commission/credit in accordance to the globally set percentage, and, that percentage will then be used to calculate the commission/credit for each of the levels.','wmc').'<br>'. __('e.g. Suppose Product "A" is priced at ','wmc').wc_price($productPrice).__(' and the global credit/commission percentage is set to ','wmc').$globalStoreCredit.__('% then, the total commission/credit on this product would sum up to ','wmc').wc_price(($productPrice*$globalStoreCredit)/100).__('. Referrals on each levels will receive the commission/credits as summarised below','wmc') .'</p><ul>'; 
                                $commission=(($productPrice*$globalStoreCredit)/100); 
                                echo '<li>'.__('Customer','wmc').' -  : '.$commission.' * '.$customerCredits.'% = '.wc_price(($commission*$customerCredits)/100).'</li>';
                                if( !$discountAmount && $Cchecked ){
                                    $discountAmount = wc_price(($commission*$customerCredits)/100);
                                }
                                for($i=0;$i<$maxLevels;$i++){
                                    if( isset( $maxLevelCredits[$i] ) && !empty($maxLevelCredits[$i]) ):
                                    echo '<li>'.__('Referrer Level','wmc').' - '.($i+1).' : '.$commission.' * '.$maxLevelCredits[$i].'% = '.wc_price(($commission*$maxLevelCredits[$i])/100).'</li>';
                                    endif;
                                }    
                                ?></ul>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top" class="wmc-optional <?php echo $class;?>">
                    <th scope="row" class="titledesc" ><label for="wmc-customer-based-bonus"><?php echo __('Customer Based Bonus Offers','wmc'); ?></label></th> 
                    <td>
                        <input type="checkbox" name="wmc-customer-based-bonus" id="wmc-customer-based-bonus" <?php echo $customerBonus ? 'checked' : ''; ?> />
                    </td>
                </tr>
                <tr valign="top" class="wmc-optional <?php echo $class;?>">
                         <th scope="row" class="titledesc" ><label for="wmc-level-c"><?php echo __('Customer','wmc'); ?></label></th> 
                         <td class="forminp">
                            <div class="bonus_for_all <?php echo $customerBonus ? 'wmc-hide' : ''; ?>">
                                <input type="number" max="<?php echo $max_input_value; ?>" step="0.01" min="0" name="wmc-level-c" id="wmc-level-c" class="form-field" value="<?php echo $customerCredits;?>"><span class="<?php echo $credit_type_class; ?>"> % </span>
                            </div>
                            <table class="form-table wmc-customer-bonus-table bonus_for_customer <?php echo $customerBonus ? '' : 'wmc-hide'; ?>">
                                <tr>
                                    <th>
                                        <label><?php _e( 'Bonus for Customer with Ref-link', 'wmc' ); ?></label>
                                    </th>
                                    <th>
                                        <label><?php _e( 'Bonus for Customer without Ref-link', 'wmc' ); ?></label>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="number" max="<?php echo $max_input_value; ?>" step="0.01" min="0" name="wmc-level-c-with-link" id="wmc-level-c-with-link" class="form-field" value="<?php echo $customerCredits;?>"><span class="<?php echo $credit_type_class; ?>"> % </span>
                                    </td>
                                    <td>
                                        <input type="number" max="<?php echo $max_input_value; ?>" step="0.01" min="0" name="wmc-level-c-without-link" id="wmc-level-c-without-link" class="form-field" value="<?php echo $customerCreditsNew;?>"><span class="<?php echo $credit_type_class; ?>"> % </span>
                                    </td>
                                </tr>
                            </table>
                            <?php do_action( 'wmc_customer_announcement', $discountAmount, $productPrice ); ?>
                         </td>      
                    </tr>
                <?php for($i=0;$i<$maxLevels;$i++){?>
                    <tr valign="top" data-level="<?php echo $i+1;?>" class="wmc-optional wmc-level <?php echo $class;?>">
                         <th scope="row" class="titledesc" ><label for="wmc-level-<?php echo $i+1;?>"><?php echo __('Referrer Level - ','wmc'); ?><span><?php echo $i+1;?></span></label></th> 
                         <td class="forminp">
                            <input type="number" max="<?php echo $max_input_value; ?>" step="0.01" min="0" name="wmc-level-credit[]" id="wmc-level-<?php echo $i+1;?>" class="form-field" value="<?php echo $maxLevelCredits[$i];?>"><span class="<?php echo $credit_type_class; ?>"> % </span>
                         </td>      
                    </tr>
                <?php }?>
            </tbody>
        </table>
        
        <div class="wmc-buttons">
			<span class="wmc-optional">
				<button id="wmc-add-more" type="button" class="wmc-optional button-primary <?php echo $class;?>"><?php echo __('Add Level','wmc'); ?></button>
			</span>
			<span class="wmc-optional">
			<?php 
				if($maxLevels>1){
					echo '<button id="wmc-delete-last" type="button" class="wmc-optional button-primary '.$class.'">'. __('Delete Last Level','wmc').'</button>';
				}else{
					echo '<button style="display:none;" id="wmc-delete-last" type="button" class="wmc-optional button-primary ">'. __('Delete Last Level','wmc').'</button>';
				}
			?>
			</span>
        </div>
        <div class="wmc-user-order-main">
            <div class="wmc-user-order-wrap">
                <table class="form-table wmc-order-level-table">
                    <tbody>
                        <?php 
                        if( is_array($getorderlevel) && !empty($getorderlevel)){
                            $count = 1;
                            foreach ($getorderlevel as $key => $level) {
                                ?>
                                <tr valign="top" data-order_level="<?php echo $key + 1; ?>" class=" wmc-level ">
                                    <th scope="row" class="titledesc">
                                        <label for="wmc-level-<?php echo $key; ?>">
                                            <?php _e('Order', 'wmc'); ?> <span><?php echo $count++; ?></span>
                                        </label>
                                    </th>
                                    <td class="forminp">
                                        <input type="number" max="10000" step="0.01" min="0" name="wmc_order_level_credit[]" id="wmc-level-1" class="form-field" value="<?php echo $level; ?>"><span class="wmc-hide"> % </span>
                                    </td>
                                </tr>
                                <?php 
                            }
                        }else{
                            ?>
                                <tr valign="top" data-order_level="1" class=" wmc-level ">
                                    <th scope="row" class="titledesc">
                                        <label for="wmc-level-1">
                                            <?php _e(' - Order', 'wmc'); ?> <span>1</span>
                                        </label>
                                    </th>
                                    <td class="forminp">
                                        <input type="number" max="10000" step="0.01" min="0" name="wmc_order_level_credit[]" id="wmc-level-1" class="form-field" value="0"><span class="wmc-hide"> % </span>
                                    </td>
                                </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <div class="wmc-buttons">
                    <span>
                        <button id="wmc-order-add-more" type="button" class="button-primary "><?php echo _e('Add Level','wmc'); ?></button>
                    </span>
                    <span>
                        <button id="wmc-order-delete-last" type="button" class="button-primary"><?php _e('Delete Last Level','wmc'); ?></button>
                    </span>
                </div>
            </div>
        </div>
        <?php
            do_action('wmc_additional_commission_settings');
        ?>
        <div class="wmc-buttons">
			<span>
				<button name="save" class="button-primary" type="submit" value="<?php echo __('Save changes','wmc'); ?>"><?php echo __('Save changes','wmc'); ?></button>
			</span>
		</div>

    </form>
</div>
