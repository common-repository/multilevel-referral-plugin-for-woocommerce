<?php

//$period = esc_attr( get_the_author_meta( 'credit_points_expiry_period', $user->ID ) );

//$periodData = array('day' => 'Days', 'month' => 'Months', 'year' => 'Years');
global $wpdb;
$tablename = $wpdb->prefix . 'referal_users';
$sql = 'SELECT RU.user_id FROM '.$tablename.' AS RU ';
$result = $wpdb->get_results( $sql );  		

?>

<h3><?php echo __('Referral Program Statistics', 'wmc'); ?></h3>



<table class="form-table">

	<?php  
		if(isset( $user['join_date'] )){

		}
		else{
			?>
			<tr>

				<td>

					<input type="checkbox" name="add_user_to_referal" id="add_user_to_referal"  />

					<span class="description"><?php echo __('Add user to referal user?', 'wmc');?></span>

				</td>
			</tr>	
			<tr>
				<th><label for="join_date"><?php echo __('Select Parent User', 'wmc')?></label></th>
				<td>

					<select name="parent_userid" id="parent_userid" >
						<option value="0">Select parent user</option>
						<?php
							foreach($result as $val){
								$uid = $val->user_id;
								$user_info = get_userdata($uid);
								
								?>
									<option value="<?php echo $uid; ?>"><?php echo $user_info->user_login; ?></option>
								<?php
							}
							
						?>
					</select>					

				</td>


			</tr>
			<?php
		}
	?>

	<tr>

		<th><label for="join_date"><?php echo __('Join Date', 'wmc')?></label></th>

		<td>

			<input type="text" name="join_date" id="join_date" disabled value="<?php echo isset( $user['join_date'] ) ? $user['join_date'] : ''; ?>" class="regular-text" /><br />

			<span class="description"><?php echo __('Joining date of referral user', 'wmc');?>.</span>

		</td>

	</tr>

	<tr>

		<th><label for="referal_benefits"><?php echo __('Referral Discount', 'wmc')?></label></th>

		<td>

			<input type="checkbox" name="referal_benefits" disabled id="referal_benefits" <?php echo isset( $user['referal_benefits'] ) ? esc_attr($user['referal_benefits'] ) ? 'checked' : '' : '' ; ?> /><br />

			<span class="description"><?php echo __('Status of referral user for discount that taken or not?', 'wmc');?>.</span>

		</td>

	</tr>

	<tr>

		<th><label for="referal_code"><?php echo __('Referral code', 'wmc')?></label></th>

		<td>

			<input type="text" name="referal_code" id="referal_code" disabled value="<?php echo isset( $user['referral_code'] ) ? esc_attr( $user['referral_code'] ) : ''; ?>" class="regular-text" /><br />

			<span class="description"><?php echo __('Auto generated referral code for referral users', 'wmc');?>.</span>

		</td>

	</tr>

	<?php /*

	<tr>

		<th><label for="credit_points_expiry"><?php echo __('Credit points expiry', 'wmc')?></label></th>

		<td>

			<input type="text" name="credit_points_expiry_number" id="credit_points_expiry_number" value="<?php echo esc_attr( get_the_author_meta( 'credit_points_expiry_number', $user->ID ) ); ?>" class="regular-text" />

			<select name="credit_points_expiry_period">

				<?php

				echo '<option value="">'. __('Period', 'wmc') .'</option>';

				foreach($periodData as $key => $value):

					echo "<option ".($period == $key ? 'selected' : '')." value='$key'>". __($value, 'wmc')."</option>";

				endforeach;

				?>

			</select>

			<br />

			<span class="description"><?php echo __('Expire periods of earn credits.', 'wmc');?>.</span>

		</td>

	</tr>

<?php */?>

</table>

<h3><?php echo __('Distribution of commission/Credit for each level.', 'wmc'); ?></h3>

<?php
	global $woocommerce;
	$userID = $_GET['user_id'];
	$credit_type = get_option( 'wmc_credit_type', 'percentage' );
	$type_html = '';
	if( $credit_type == 'percentage' ){
		$type_html = ' (%)';
	}
	$isLevelBaseCredit= get_option('wmc-levelbase-credit',0);
	if($isLevelBaseCredit){
		$maxLevels=get_option('wmc-max-level',1);
		$maxLevelCredits=get_option('wmc-level-credit',array());
		$customerCredits=get_option('wmc-level-c',0);
		$maxUserLevelCredits=get_user_meta($userID,'wmc-level-credit',true);
		$UserLevelEnable=get_user_meta($userID,'wmc-user-level-enable',true);
		$CCredits=get_user_meta($userID,'wmc-level-c',true);
		$addattr = '';
		if($UserLevelEnable == 'on'){
			$addattr = 'checked';
		}
		echo '<input name="wmc_enable_user_level" type="checkbox" '.$addattr.' id="wmc-enable-user-level" ><label for="wmc-enable-user-level">'.__('Enable User Credit','wmc').'</label>';
		echo '<p class="form-field wmc-level-c_field">
		<label for="wmc-level-c">'.__('Customer','wmc').$type_html.'</label><input type="number" step="0.01" class="short" style="width:50px;text-align:right;" name="wmc-level-c" id="wmc-level-c" value="'.$CCredits.'" placeholder="'.$customerCredits.'"> </p>';
		for($i=0;$i<$maxLevels;$i++){
			$levelValue=(isset($maxUserLevelCredits[$i]) && $maxUserLevelCredits[$i]!='')?$maxUserLevelCredits[$i]:'';
			woocommerce_wp_text_input( 
			array( 
				'id'          => 'wmc-level-credit', 
				'name'          => 'wmc-level-credit[]', 
				'type'          => 'number', 
				'style'          => 'width:50px;text-align:right;', 
				'label'       => __( 'Referrer Level ', 'wmc' ).($i+1).' '.$type_html, 
				'placeholder' => $maxLevelCredits[$i],
				'desc_tip'    => false,
				'value' => $levelValue,
				'custom_attributes' => array( 'step' => '0.01' )
			)
		);
		}
		echo '</div>';
	}
?>
