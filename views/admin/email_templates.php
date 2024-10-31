<?php

$title = __('Email templates', 'wmc');

$joining_mail_template = stripcslashes( get_option('joining_mail_template', '') );

$joining_mail_subject = stripcslashes( get_option('joining_mail_subject', __('Referral Program Team','wmc')) );

$joining_mail_heading = stripcslashes( get_option('joining_mail_heading', __('Referral Program Team','wmc')) );

$referral_user_template = stripcslashes( get_option('referral_user_template', '') );

$referral_user_subject = stripcslashes( get_option('referral_user_subject', __('Referral Program Team','wmc')) );

$referral_user_heading = stripcslashes( get_option('referral_user_heading', __('Referral Program Team','wmc')) );

$expire_notification_template = stripcslashes( get_option('expire_notification_template', '') );

$expire_notification_subject = stripcslashes( get_option('expire_notification_subject',__('Referral Program Team','wmc')) );

$expire_notification_heading = stripcslashes( get_option('expire_notification_heading', __('Referral Program Team','wmc')) );

$settings  = array('editor_height' => 425,
                  'textarea_rows' => 20);

?>

<form action="" method="post">
   <div class="wmc-email-template">
      <div class="mdl-tabs vertical-mdl-tabs mdl-js-tabs mdl-js-ripple-effect">
         <div class="mdl-grid mdl-grid--no-spacing">
            <div class="mdl-cell mdl-cell--2-col">
               <div class="mdl-tabs__tab-bar"> <a href="#tab1-panel" class="mdl-tabs__tab is-active"> <span class="hollow-circle"></span> <?php _e('Joining mail for referral program', 'wmc');?> </a> <a href="#tab2-panel" class="mdl-tabs__tab"> <span class="hollow-circle"></span> <?php _e('Invitation mail for Referral users', 'wmc');?> </a> <a href="#tab3-panel" class="mdl-tabs__tab"> <span class="hollow-circle"></span> <?php _e('Expire credit notification', 'wmc');?> </a> 
                  <?php do_action( 'wmc_mail_menu_header' ); ?>
               </div>
            </div>
            <div class="mdl-cell mdl-cell--10-col">
               <div class="mdl-tabs__panel is-active" id="tab1-panel">
                  <div class="cell-50 cell-0">
                     <label for="joining_mail_subject"><?php _e('Joining mail Subject', 'wmc');?></label> 
                     <div><input placeholder="<?php _e('Joining mail Subject', 'wmc');?>" type="text" class="form-field" name="joining_mail_subject" id="joining_mail_subject" value="<?php echo $joining_mail_subject;?>"></div>
                  </div>
                  <div class="cell-50 cell-1">
                     <label for="joining_mail_heading"><?php _e('Joining mail Heading', 'wmc');?></label> 
                     <div><input placeholder="<?php _e('Joining mail Heading', 'wmc');?>" type="text" class="form-field" name="joining_mail_heading" id="joining_mail_heading" value="<?php echo $joining_mail_heading;?>"></div>
                  </div>
                  <?php echo wp_editor($joining_mail_template, 'joining_mail_template', $settings)?> <small><?php _e('You can use{referral_code}to replace respective referral code.', 'wmc');?></small><br/> <small><?php _e('You can use{first_name}to replace respective user name.', 'wmc');?></small><br/> <small><?php _e('You can use{last_name}to replace respective user name.', 'wmc');?></small>
                  <p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php _e('Save template', 'wmc')?>"/> </p>
               </div>
               <div class="mdl-tabs__panel" id="tab2-panel">
                  <div class="cell-50 cell-0">
                     <label for="referral_user_subject"><?php _e('Referral User E-mail Subject', 'wmc');?></label> 
                     <div><input placeholder="<?php _e('Referral User E-mail Subject', 'wmc');?>" type="text" class="form-field" name="referral_user_subject" id="referral_user_subject" value="<?php echo $referral_user_subject;?>"></div>
                  </div>
                  <div class="cell-50 cell-1">
                     <label for="referral_user_heading"><?php _e('Referral User E-mail Heading', 'wmc');?></label> 
                     <div><input placeholder="<?php _e('Referral User E-mail Heading', 'wmc');?>" type="text" class="form-field" name="referral_user_heading" id="referral_user_heading" value="<?php echo $referral_user_heading;?>"></div>
                  </div>
                  <?php echo wp_editor($referral_user_template, 'referral_user_template', $settings)?> <small><?php _e('You can use{referral_code}to replace respective referral code.', 'wmc');?></small><br/> <small><?php _e('You can use{first_name}to replace respective user name.', 'wmc');?></small><br/> <small><?php _e('You can use{last_name}to replace respective user name.', 'wmc');?></small><br/> <small><?php _e('You can use [referral_link text="Click here"] to replace respective user referral link.', 'wmc');?></small> 
                  <p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php _e('Save template', 'wmc')?>"/> </p>
               </div>
               <div class="mdl-tabs__panel" id="tab3-panel">
                  <div class="cell-50 cell-0">
                     <label for="expire_notification_subject"><?php _e('Expire Notification E-mail Subject', 'wmc');?></label> 
                     <div><input placeholder="<?php _e(' Notification E-mail Subject', 'wmc');?>" type="text" class="form-field" name="expire_notification_subject" id="expire_notification_subject" value="<?php echo $expire_notification_subject;?>"></div>
                  </div>
                  <div class="cell-50 cell-1">
                     <label for="expire_notification_heading"><?php _e('Expire Notification E-mail Heading', 'wmc');?></label> 
                     <div><input placeholder="<?php _e(' Notification E-mail Heading', 'wmc');?>" type="text" class="form-field" name="expire_notification_heading" id="expire_notification_heading" value="<?php echo $expire_notification_heading;?>"></div>
                  </div>
                  <?php echo wp_editor($expire_notification_template, 'expire_notification_template', $settings)?> <small><?php _e('{available_credits}- Replace respective user credits.', 'wmc');?></small><br/> <small><?php _e('{first_name}- Replace respective user name.', 'wmc');?></small><br/> <small><?php _e('{last_name}- Replace respective user name.', 'wmc');?></small><br/> <small><?php _e('{expire_date}- Replace respective expiry date of user credits.', 'wmc');?></small><br/> <small><?php _e('{validity_period}- Replace respective store credit validity.', 'wmc');?></small><br/> <small><?php _e('{today_date}- Replace respective current date.', 'wmc');?></small><br/> <small><?php _e('{expire_month}- Replace respective credit expired month.', 'wmc');?></small><br/> <small><?php _e('{expire_credits}- Replace respective expired credits.', 'wmc');?></small>
                  <p> <input type="submit" class="button button-primary button-large" name="save_template" value="<?php _e('Save template', 'wmc')?>"/> </p>
               </div>
               <?php do_action( 'wmc_mail_menu_content', $settings ); ?>
            </div>
         </div>
      </div>
   </div>
</form>

