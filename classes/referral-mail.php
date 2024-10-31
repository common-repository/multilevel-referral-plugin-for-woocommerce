<?php

if ( ! class_exists( 'Referral_Mail' ) ) {

	/**
	 * Mail controller class
	 *
	 */
	class Referral_Mail extends WC_Email {
	
		public $email;
		public $user_id;
		public $first_name;
		public $last_name;
		public $referral_code;
		public $template;
		
		public $available_credits;
		public $expire_date;
		public $validity_period;
		public $today_date;
		public $expire_month;
		public $expire_credits;
		
		public function __construct() {

			// set ID, this simply needs to be a unique name
			$this->id = 'wc_referral_program';
	
			// this is the title in WooCommerce Email settings
			$this->title = 'Referral Program';
	
			// this is the description in WooCommerce email settings
			$this->description = 'Sent email notification on joining Referral Program.';
	
			// these are the default heading and subject lines that can be overridden using the settings
			$this->heading = 'Referral Program Team';
			$this->subject = 'Referral Program Team';
	
			// Call parent constructor to load any other defaults not explicity defined here
			parent::__construct();
	
		}
		
		public function trigger($email, $first_name, $last_name, $referral_code, $template,  $user_id) {
	
			$this->recipient 	= 	$email;
			$this->user_id		=	$user_id;
			$this->first_name	=	ucfirst($first_name);
			$this->last_name	=	ucfirst($last_name);
			$this->template		=	$template;
			$this->referral_code=	$referral_code;
			
			if ( ! $this->get_recipient() )
				return;
	            
			// woohoo, send the email!
			$this->send( $email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
		
		public function reminder($email, $first_name, $last_name, $available_credits, $expire_date,  $validity_period, $today_date, $expire_month, $expire_credits ) {
	
	
			$this->available_credits	=	$available_credits;
			$this->expire_date			=	$expire_date;
			$this->validity_period		=	$validity_period;
			$this->today_date			=	$today_date;
			$this->expire_month			=	$expire_month;
			$this->expire_credits		=	$expire_credits;
		
			$this->recipient 	= 	$email;
			$this->first_name	=	ucfirst($first_name);
			$this->last_name	=	ucfirst($last_name);
			$this->template		=	'expire_notification';
			
			if ( ! $this->get_recipient() )
				return;
	
			// woohoo, send the email!
			$this->send( $email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		public function get_content_html() {           
			ob_start();
			$email_heading	=	$this->get_heading();
			$email			=	$this;
			do_action( 'woocommerce_email_header', $email_heading, $email );
			echo $this->get_template_content( $this->template );
			do_action( 'woocommerce_email_footer', $email );
			return ob_get_clean();
		}
		public function get_heading(){
            global $customer_id;
            $customer_id    =    $this->user_id;
            $arg             =     array(
                                      '{first_name}',
                                      '{last_name}',
                                      '{referral_code}',
                                      '{available_credits}',
                                      '{expire_date}',
                                      '{validity_period}',
                                      '{today_date}',
                                      '{expire_month}',
                                      '{expire_credits}'
                                      );
            $replace_with    =    array(
                                      $this->first_name,
                                      $this->last_name,
                                      $this->referral_code,
                                      $this->available_credits,
                                      $this->expire_date,
                                      $this->validity_period,
                                      $this->today_date,
                                      $this->expire_month,
                                      $this->expire_credits
                                      );
            return do_shortcode(stripslashes(str_replace( $arg, $replace_with, get_option( $this->template.'_heading',  __('Referral Program Team','wmc') ) ) ));
        }
        public function get_subject(){
            global $customer_id;
            $customer_id    =    $this->user_id;
            $arg             =     array(
                                      '{first_name}',
                                      '{last_name}',
                                      '{referral_code}',
                                      '{available_credits}',
                                      '{expire_date}',
                                      '{validity_period}',
                                      '{today_date}',
                                      '{expire_month}',
                                      '{expire_credits}'
                                      );
            $replace_with    =    array(
                                      $this->first_name,
                                      $this->last_name,
                                      $this->referral_code,
                                      $this->available_credits,
                                      $this->expire_date,
                                      $this->validity_period,
                                      $this->today_date,
                                      $this->expire_month,
                                      $this->expire_credits
                                      );
            return do_shortcode(stripslashes(str_replace( $arg, $replace_with, get_option( $this->template.'_subject',  __('Referral Program Team','wmc') ) ) ));
        }
		public function get_template_content( $template ){
			global $customer_id;
			$customer_id	=	$this->user_id;
			$arg 			= 	array(
									  '{first_name}',
									  '{last_name}',
									  '{referral_code}',
									  '{available_credits}',
									  '{expire_date}',
									  '{validity_period}',
									  '{today_date}',
									  '{expire_month}',
									  '{expire_credits}'
									  );
			$replace_with	=	array(
									  $this->first_name,
									  $this->last_name,
									  $this->referral_code,
									  $this->available_credits,
									  $this->expire_date,
									  $this->validity_period,
									  $this->today_date,
									  $this->expire_month,
									  $this->expire_credits
									  );
			return wpautop(do_shortcode(stripslashes(str_replace( $arg, $replace_with, get_option( $template.'_template', '' ) ) )));
		}
		public function get_content_plain() {
			ob_start();
			$email_heading	=	$this->get_heading();
			$email			=	$this->get_recipient();
			echo $this->get_template_content( $this->template );
			return ob_get_clean();
		}
		public function init_form_fields() {
	
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => 'Enable/Disable',
					'type'    => 'checkbox',
					'label'   => 'Enable this email notification',
					'default' => 'yes'
				),
				'subject'    => array(
					'title'       => 'Subject',
					'type'        => 'text',
					'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
					'placeholder' => '',
					'default'     => ''
				),
				'heading'    => array(
					'title'       => 'Email Heading',
					'type'        => 'text',
					'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
					'placeholder' => '',
					'default'     => ''
				),
				'email_type' => array(
					'title'       => 'Email type',
					'type'        => 'select',
					'description' => 'Choose which format of email to send.',
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'	    => __( 'Plain text', 'woocommerce' ),
						'html' 	    => __( 'HTML', 'woocommerce' ),
						'multipart' => __( 'Multipart', 'woocommerce' ),
					)
				)
			);
		}

	} // end Referral_Mail
	
}