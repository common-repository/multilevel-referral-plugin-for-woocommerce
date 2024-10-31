<p class="form-row form-row-first">
	<label for="reg_billing_first_name"><?php _e( 'First name', 'wmc' ); ?> <span class="required">*</span></label>
	<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) sanitize_text_field( $_POST['billing_first_name'] ); ?>" />
</p>

<p class="form-row form-row-last">
	<label for="reg_billing_last_name"><?php _e( 'Last name', 'wmc' ); ?> <span class="required">*</span></label>
	<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) sanitize_text_field( $_POST['billing_last_name'] ); ?>" />
</p>