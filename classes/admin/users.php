<?php

/**

 * WooCommerce Multilevel Referral Users Meta

 *

 */



if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}



if ( ! class_exists( 'WMR_User' ) ) :



/**

 * WMR_User.

 */

class WMR_User extends WMC_Module {



	/**

	 * Constructor.

	 */

	public function __construct() {

		$this->register_hook_callbacks();

	}



	/**

	 * Register callbacks for actions and filters

	 *

	 * @mvc Controller

	 */

	public function register_hook_callbacks() {

		

		add_action( 'show_user_profile', __CLASS__.'::referal_fields');

		add_action( 'edit_user_profile', __CLASS__.'::referal_fields');

		// update user level credit
        add_action( 'profile_update', __CLASS__. '::wmc_update_user_level_credit_on_profile_update', 10, 2 );

		//add_action( 'personal_options_update', 	__CLASS__.'::save_fields' );

		//add_action( 'edit_user_profile_update', __CLASS__.'::save_fields' );

	}

	

	public static function referal_fields($user){

		$objReferalUsers	=	new Referal_Users();

		$user				=	$objReferalUsers->get_referral_user( $user->ID );

		echo self::render_template( 'admin/users.php', array( 'user' => $user));

	}

	

	public static function save_fields($user_id){

		if ( !current_user_can( 'edit_user', $user_id ) ){

			return false;

		}

		if( isset ($_POST['referal_code']) ){

			update_user_meta( absint( $user_id ), 'referal_code', wp_kses_post( sanitize_text_field($_POST['referal_code']) ) );

		}

		update_user_meta( absint( $user_id ), 'join_date', wp_kses_post( sanitize_text_field($_POST['join_date']) ) );

		update_user_meta( absint( $user_id ), 'referal_benefits', wp_kses_post( isset($_POST['referal_benefits']) ? 1 : 0 ) );

		update_user_meta( absint( $user_id ), 'credit_points_expiry_number', wp_kses_post( sanitize_text_field($_POST['credit_points_expiry_number']) ) );

		update_user_meta( absint( $user_id ), 'credit_points_expiry_period', wp_kses_post( sanitize_text_field($_POST['credit_points_expiry_period'] )) );

	}

	/* 
	* Update user level credit
	*/
	public static function wmc_update_user_level_credit_on_profile_update( $user_id, $old_user_data ) {
        // Check if the user ID is valid
        if ( ! $user_id ) {
            return;
        }

        // Get user data
        //$user_info = get_userdata( $user_id );

        // Update user meta based on the updated user data
		if(isset($_POST['wmc_enable_user_level'])){
			update_user_meta( $user_id, 'wmc-user-level-enable', $_POST['wmc_enable_user_level']); 
		}
		else{
			update_user_meta( $user_id, 'wmc-user-level-enable', 'off');
		}
        if(isset($_POST['wmc-level-c'])){
			update_user_meta( $user_id, 'wmc-level-c', $_POST['wmc-level-c']); 
		}  
		if(isset($_POST['wmc-level-credit'])){
			update_user_meta( $user_id, 'wmc-level-credit', $_POST['wmc-level-credit']); 
		} 
    }

	/*

	 *	Get list of users

	 */

	public function get_users( $per_page = 5, $page_number = 1 ) {

	

		global $wpdb;

	  

		$sql = "SELECT u.ID, u.user_email AS email, CONCAT (um1.meta_value , ' ', um2.meta_value) AS name, um3.meta_value AS join_date, (SUM(rp.credits) - SUM(rp.redeems)) AS total_credits, 0 AS no_of_followers

		FROM $wpdb->users AS u

		INNER JOIN $wpdb->usermeta AS um1 ON u.ID = um1.user_id AND um1.meta_key='first_name'

		INNER JOIN $wpdb->usermeta AS um2 ON u.ID = um2.user_id AND um2.meta_key='last_name'

		INNER JOIN $wpdb->usermeta AS um3 ON u.ID = um3.user_id AND um3.meta_key='join_date'

		LEFT JOIN ".$wpdb->prefix."referal_program AS rp ON rp.user_id = u.ID

		ORDER BY u.ID ";

	  

		//$sql = "SELECT user_id, order_id, redeems FROM ".$this->table_name." GROUP BY order_id ";

	  

		if ( ! empty( $_REQUEST['orderby'] ) ) {

		  $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );

		  $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';

		}

	  

		$sql .= " LIMIT $per_page";

	  

		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

	  	

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

	  

		return $result;

	}

	

	

	/*

	*	Get number of orders

	*/

   public function record_count() {

	   global $wpdb;

	 

	   $sql = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE meta_key = 'join_date' AND meta_value != ''";

	 

	   return $wpdb->get_var( $sql );

	}

	

	public function activate( $network_wide ){

		

	}



	/**

	 * Rolls back activation procedures when de-activating the plugin

	 *

	 * @mvc Controller

	 */

	public function deactivate(){

		

	}



	/**

	 * Initializes variables

	 *

	 * @mvc Controller

	 */

	public function init(){

		

	}



	/**

	 * Checks if the plugin was recently updated and upgrades if necessary

	 *

	 * @mvc Controller

	 *

	 * @param string $db_version

	 */

	public function upgrade( $db_version = 0 ){

		

	}



	/**

	 * Checks that the object is in a correct state

	 *

	 * @mvc Model

	 *

	 * @param string $property An individual property to check, or 'all' to check all of them

	 * @return bool

	 */

	public function is_valid($valid = "all"){

		return true;

	}



}



endif;

