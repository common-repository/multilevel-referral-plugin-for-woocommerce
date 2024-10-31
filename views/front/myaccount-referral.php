  
	<?php 
	if( isset( $data['active'] ) && $data['active'] == 'amount_tab' ){ ?>
	<div class="referral_program_overview second_section"> 
		
		<div class="referral_program_stats total_earn_credit">
			<span class="total_credit_icon"></span>
			<span><?php _e('Total Credits Earned','woocommerce-extention'); ?></span>
			<span class="show_output"><?php echo  floor($data['total_earn_point']) ; ?></span>
		</div>
		<div class="referral_program_stats total_avilable_credit">
			<span class="total_credit_icon"></span>
			<span><?php echo apply_filters( 'wmc_total_credits_available', __('Total Credits Available','wmc') ); ?></span>
			<span class="show_output"><?php echo apply_filters( 'wmc_total_credits_amount', floor($data['total_points']) ); ?></span>
		</div>
		<div class="referral_program_stats">
			<span class="total_credit_icon total_withdraw_credit"></span>
			<span><?php echo _e('Total Withdrawn','woocommerce-extention'); ?></span>
			<span class="show_output"><?php echo  floor($data['total_withdraw']); ?></span>
		</div>
	</div>
	<?php }else{?>
	<div class="referral_program_overview referral_top_section">
		<div class="referral_program_stats">
			<span class="referral_icon"></span>
			<span><?php _e('Referral Code', 'wmc'); ?></span>
			<span class="show_output"><?php echo $data['referral_code']; ?></span>
		</div>
		<div class="referral_program_stats total_avilable_credit">
			<span class="total_credit_icon"></span>
			<span><?php echo apply_filters( 'wmc_total_credits_available', __('Total Credits Available','wmc') ); ?></span>
			<span class="show_output"><?php echo apply_filters( 'wmc_total_credits_amount', floor($data['total_points']) ); ?></span>
		</div>
		<div class="referral_program_stats">
			<span class="total_referral"></span>
			<span><?php _e('Total Referrals', 'wmc'); ?></span>
			<span class="show_output"><?php echo $data['total_followers']; ?></span>
		</div>
	</div>
	<?php } ?>
	<div class="referral_program_sections" style="padding-top: 30px;">
		<!-- <ul>
			<li class="<?php //echo ( $data['active_panel'] == 'referral-share-invite' ? 'active' : '' );?>" ><a href="<?php //echo add_query_arg( 'tab', 'referral-share-invite', $data['page_url'].'referral' ); ?>"><?php //_e('Share / Invite', 'wmc'); ?></a></li>
			<li class="<?php //echo ( $data['active_panel'] == 'referral-affiliates' ? 'active' : '' );?>"><a href="<?php //echo add_query_arg( 'tab', 'referral-affiliates', $data['page_url'].'referral' ); ?>"><?php //_e('My Affiliates', 'wmc'); ?></a></li>
		</ul> -->
		<div class="referral_program_content">
			<?php echo $data['content']; ?>
		</div>
	</div>

