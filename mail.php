<?php
	function wmc_referral_join_email( $email_classes ) {
	
		// include our custom email class
		require_once( __DIR__ . '/classes/referral-mail2.php' );
	
		// add the email class to the list of email classes that WooCommerce loads
		$email_classes['WMC_Joining_User'] = new Referral_Mail();
	
		return $email_classes;
	
	}	
	add_filter( 'woocommerce_email_classes', 'wmc_referral_join_email' );
	add_action( 'wmc_joining_user_notification', 'joining_mail', 10 );
	function joining_mail(){
			$obj_mails = new WC_Emails();
			if( get_current_user_id() ){
				$current_user 	=	wp_get_current_user();
				
				$email_id 			=	$current_user->user_email;	 
				$first_name 	= 	$current_user->user_firstname;
				$last_name 		= 	$current_user->user_lastname;
			}else{
				$email_id = sanitize_text_field($_POST['email']);
                $first_name='';
                $last_name='';
                if(isset($_POST['billing_first_name'])){
				    $first_name 	= 	sanitize_text_field($_POST['billing_first_name']);
                }
                if(isset($_POST['billing_last_name'])){
				    $last_name 		= 	sanitize_text_field($_POST['billing_last_name']);
                }
			}
			
			$email = $obj_mails->emails['WMC_Joining_User'];
			//$email->trigger( $email_id, $first_name, $last_name, 'joining_mail', 'xsdsaa', 1 );
			$email->trigger( $email_id, $first_name, $last_name, 'referral_user', 'xsdsaa', 1 );
			
			
	}
	
	//add_filter( 'woocommerce_email_actions', 'filter_woocommerce_email_actions', 10, 1 );
	
	function filter_woocommerce_email_actions( $filter_array ){
		$filter_array[] = 'wmc_joining_user';
		return $filter_array;
	}

	add_filter('the_content', function( $content ){
		do_action( 'wmc_joining_user_notification' );
		return $content;	
	});
	