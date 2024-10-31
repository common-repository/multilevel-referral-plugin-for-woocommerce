<?php

/* 
 * Mobile authantication 
 * checkout page and belling in phone numver validation 
 */
if ( ! class_exists( 'mobile_authanticate' ) ) {

    class mobile_authanticate {
		function __construct() {
		    add_action('wp_authenticate',array($this ,'mobile_authantication_callback'),1,2 ); // authantication use mobile
		    add_action('woocommerce_checkout_process',array($this, 'check_phone_number_exist_callback'),10); // checkout page mobile validation
		    add_filter( 'woocommerce_process_myaccount_field_billing_phone',array($this,'login_user_belling_number_callback'),10); // user my account in validation
			add_filter( 'woocommerce_locate_template',array($this ,'myplugin_woocommerce_locate_template'), 1, 3 );

			// WOOCOMMERCE REGISTRATION FORM IN ADD PHONE MUMBER FILED
 			add_action( 'woocommerce_register_form_start',array($this, 'mrp_wooc_extra_register_fields'),10);
 			
 			// WOOCOMMERCE REGISTRATION VALIDATION FROM IN ADD PHONE MUMBER FILED
 			add_action( 'woocommerce_register_post', array($this , 'mrp_wooc_validate_extra_register_fields'), 10, 3 );
 			add_action( 'woocommerce_save_account_details_errors',array($this, 'mrp_wooc_edit_save_fields'), 10, 1 );
 			// WOOCOMMERCE REGISTRATION UPDATE 
 			add_action( 'woocommerce_created_customer', array($this, 'mrp_wooc_validate_extra_register_fields_update'),10);
		}

		function mrp_wooc_edit_save_fields($args){
			global $wpdb;
			$user_id = get_current_user_ID();
				

			if( isset( $_POST['billing_phone'] ) && $_POST['billing_phone'] == '' ) {
				$args->add( 'billing_phone_name_error', __( 'Mobile number is required.', 'woocommerce' ) );
			}
			if(isset( $_POST['billing_phone'] ) && !empty( $_POST['billing_phone'] )){
				$mobile_num_result = $wpdb->get_var("select B.ID from ". $wpdb->prefix ."usermeta as A join ". $wpdb->prefix ."users as B where meta_key='billing_phone' and meta_value='".$_POST['billing_phone']."' and A.user_id =  b.ID ");
				if(isset($mobile_num_result))
				{
				    if($user_id != $mobile_num_result)
				    {
						wc_add_notice(__( 'Mobile Number is already used.','woocommerce'), 'error' );
						return;	
				    }else{
				    	update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
				    	return $_POST['billing_phone'];
				    }
				} else{
					update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
					return $_POST['billing_phone'];
				} 
			}

		}
		function mrp_wooc_validate_extra_register_fields_update($customer_id){
			if ( isset( $_POST['billing_phone'] ) ) {
		        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
		    }
		}
		function mrp_wooc_extra_register_fields(){
			$num_val = '';
			if(is_user_logged_in())
			{
				$user_id = get_current_user_ID();
				$num_val = get_user_meta($user_id, 'billing_phone',true);
				if(isset($_POST['billing_phone']))
				{
					$num_val = $_POST['billing_phone'];	
				}
			}else{
				if(isset($_POST['billing_phone']))
				{
					$num_val = $_POST['billing_phone'];	
				}
			}
			
			?>

			<p class="form-row form-row-wide">
				<label for="reg_billing_phone">
					<?php _e( 'Mobile Number', 'woocommerce' ); ?> <span class="required">*</span>
				</label>
				<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php echo $num_val; ?>" maxlength="10" />
			</p> 
		 <?php
		}

		function mrp_wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
			global $wpdb;
			if( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
				$validation_errors->add( 'billing_phone_name_error', __( 'Mobile number is required.', 'woocommerce' ) );
			}
			if(isset( $_POST['billing_phone'] ) && !empty( $_POST['billing_phone'] )){
				$mobile_num_result = $wpdb->get_var("select user_id from ". $wpdb->prefix ."usermeta  where meta_key='billing_phone' and meta_value='".$_POST['billing_phone']."' ");
				if(isset($mobile_num_result))
				{
				    if($mobile_num_result)
				    {
				    	$validation_errors->add( 'billing_phone_name_error', __( 'Mobile Number is already registred.', 'woocommerce' ) );
				    }else{
				    	update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
				    	return $_POST['billing_phone'];
				    }
				} else{
					update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
					return $_POST['billing_phone'];
				} 
			}
			return $validation_errors;
		}

		function woo_adon_plugin_template( $template, $template_name, $template_path ) {
		     global $woocommerce;
		     $_template = $template;
		     if ( ! $template_path ) 
		        $template_path = $woocommerce->template_url;
		 
		     $plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/template/woocommerce/';
		 
		    // Look within passed path within the theme - this is priority
		    $template = locate_template(
		    array(
		      $template_path . $template_name,
		      $template_name
		    )
		   );
		 
		   if( ! $template && file_exists( $plugin_path . $template_name ) )
		    $template = $plugin_path . $template_name;
		 
		   if ( ! $template )
		    $template = $_template;

		   return $template;
		}
		//user login my acccount in validation
		function login_user_belling_number_callback( $fieldnumber){
			global $wpdb;
			
			if(is_user_logged_in())
			{
				$user_id = get_current_user_id();
				if (isset($_POST['billing_phone'] ))
			    {
					$mobile_num_result = $wpdb->get_var("select B.ID from ". $wpdb->prefix ."usermeta as A join ". $wpdb->prefix ."users as B where meta_key='billing_phone' and meta_value='".$_POST['billing_phone']."' and A.user_id =  b.ID ");
					if(isset($mobile_num_result))
					{
					    if($user_id != $mobile_num_result)
					    {
							wc_add_notice(__( 'Billing number is already used.','woocommerce'), 'error' );
							return;	
					    }else{
					    	update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
					    	return $_POST['billing_phone'];
					    }
					} else{
						update_user_meta($user_id ,'billing_phone',$_POST['billing_phone']);
						return $_POST['billing_phone'];
					} 
			    }
			}
		}
		//checout page validatiion
		function check_phone_number_exist_callback() {
		    global $wpdb;
		    // Check if set, if its not set add an error.
		    if (isset($_POST['billing_phone'] ) )
		    {
				$mobile_num_result = $wpdb->get_var("select B.ID from ". $wpdb->prefix ."usermeta as A join ". $wpdb->prefix ."users as B where meta_key='billing_phone' and meta_value='".$_POST['billing_phone']."' and A.user_id =  B.ID ");
                
				if(isset($mobile_num_result))
				{
				    if(get_current_user_id() != $mobile_num_result)
				    {
						wc_add_notice(__( 'Billing number is already used.','woocommerce'), 'error' );
				    }
				}   
		    }
		}
		// mobile authantication
		function mobile_authantication_callback($username, $password)
		{
		    global $wpdb;
		    if(isset($username) && isset($password) && !empty($password) && !empty($username))
		    {
				$mobile_num_result = $wpdb->get_var("select B.user_login  from ". $wpdb->prefix ."usermeta as A join ". $wpdb->prefix ."users as B where meta_key='billing_phone' and meta_value='".$username."' and A.user_id =  B.ID ");
				$reminder = isset($_POST['rememberme'])?'true':'false';
				if(isset($mobile_num_result))
				{

				    $creds = array(
					'user_login'=> $mobile_num_result,
					'user_password' => $password,
					'remember'      => $reminder,
				    );
				    remove_action('wp_authenticate',array($this ,'mobile_authantication_callback'),1,2 );
				    $user = wp_signon( $creds, false );
				    add_action('wp_authenticate',array($this ,'mobile_authantication_callback'),1,2 );
				    if ( !is_wp_error( $user ) ) {
						$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
						if ( $myaccount_page ) {
						    $myaccount_page_url = get_permalink( $myaccount_page );
						    wp_redirect($myaccount_page_url);
						    exit();
						}
				    }else{
				    	add_filter( 'login_errors' ,function(){return;},10);
				    	$message = "<strong>".__('ERROR','woocommerce')."</strong>: ".__('The password you entered for the mobile number','woocommerce')." <strong>".$username."</strong> " .__('is incorrect','woocommerce'). ". <a href=".wp_lostpassword_url().">".__('Lost your password?','woocommerce') ."</a>";
				    	wc_add_notice(__($message,'woocommerce'), 'error' );

				    }
				}
		    }
		}
		function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
			  global $woocommerce;

			  $_template = $template;

			  if ( ! $template_path ) $template_path = $woocommerce->template_url;

			  $plugin_path  = WMC_DIR. '/woocommerce/';

			  // Look within passed path within the theme - this is priority
			  $template = locate_template(

			    array(
			      $template_path . $template_name,
			      $template_name
			    )
			  );
			  // Modification: Get the template from this plugin, if it exists
			  if ( ! $template && file_exists( $plugin_path . $template_name ) )
			    $template = $plugin_path . $template_name;

			  // Use default template
			  if ( ! $template )
			    $template = $_template;
			  // Return what we found
			  return $template;
			}
    }
    
    new mobile_authanticate();
}

