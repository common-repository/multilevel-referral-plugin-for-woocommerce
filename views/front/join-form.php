<div class="wmc_join_form">
<h2><?php echo __( 'Join Referral Program', 'wmc' );?></h2>
    <form action="" method="post">
        <p class="form-row form-row-wide">
            <label for="option_1"><input type="radio" id="option_1" name="join_referral_program" <?php echo $data['join_referral_program'] == "1" ? 'checked' : ''; ?> value="1" /> <?php echo __( 'I have the referral code and want to join referral program.', 'wmc' ); ?></label>
            <label for="option_2"><input type="radio" id="option_2" name="join_referral_program" <?php echo $data['join_referral_program'] == "2" ? 'checked' : ''; ?> value="2" /> <?php echo __( 'I don\'t have referral code or I lost it. But I wish to join referral program.', 'wmc' ); ?></label>
        </p>
        <p class="referral_code_panel form-row hide">
            <label for="referral_code"><?php echo apply_filters('wmc_reg_field_referral_field_name_change',__( 'Referral Code', 'wmc' ));?> <span class="required">*</span></label>
            <input type="text"  class="input-text"  name="referral_code" id="referral_code" value="<?php echo $data['referral_code']; ?>" />
            <small><?php echo __( '&nbsp;', 'wmc' );?></small>
        </p>
        <!--p class="referral_email_panel form-row form-row-first hide">
            <label for="referral_email">Referral Email</label>
            <input type="email"  class="input-text"  name="referral_email" id="referral_email" value="<?php echo $data['referral_email']; ?>" />
        </p-->
        <p class="referral_terms_conditions form-row form-row-wide hide">
        <input type="checkbox" <?php echo isset($data['termsandconditions'])&& $data['termsandconditions'] ? 'checked' : ''; ?> name="termsandconditions" id="termsandconditions" value="1" /> <label for="termsandconditions"><?php _e( 'I\'ve read and agree to the referral program', 'wmc' ) ?> <a href="<?php echo esc_url( get_permalink(get_option('wmc_terms_and_conditions',0)) ); ?>" target="_blank">
        <?php echo __( 'terms and conditions', 'wmc' ); ?></a></label>
        </p>
        
        <p class="form-row form-row-wide">
            <input type="submit" class="button" name="add_new_referral_user" value="<?php echo __( 'Join', 'wmc' );?>">
            <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $data['nonce']?>">
            <input type="hidden" name="action" value="join_referreal_program">
        </p>
    </form>
</div>
