<select name="referral_code[]" multiple="multiple" id="dropdown_referral_code" data-placeholder="<?php esc_attr_e( 'Filter by referral code', 'wmc' ); ?>" data-allow_clear="true" >
	<?php if( is_array( $referral_code_list ) ): ?>
		<?php foreach( $referral_code_list as $referral_code ): ?>
			<option <?php echo ( is_array( $get_referral_code ) && in_array( $referral_code->user_id, $get_referral_code ) ? 'selected' : '' ); ?>  value="<?php echo $referral_code->user_id; ?>"><?php echo $referral_code->referral_code; ?></option>
		<?php endforeach; ?>
	<?php endif; ?>
</select>