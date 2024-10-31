<?php
/**
 * Credit log table
 *
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WMR_Credit_log' ) ) :

/**
 * WMR_Referal_Settings.
 */
class WMR_Credit_log extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $obj_referal_program;
		
		parent::__construct( [
			'singular' => __( 'Order', 'wmc' ), //singular name of the listed records
			'plural'   => __( 'Orders', 'wmc' ), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		] );

		$obj_referal_program = new Referal_Program();
	}
  
	/** Text displayed when no customer data is available */
  public function no_items() {
	_e( 'No orders avaliable.', 'wmc' );
  }
  
	/**
   * Render a column when no column specific method exists.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default( $item, $column_name ) {
	switch ( $column_name ) {
	  case 'order_id':
		return edit_post_link( '#'.$item[ $column_name ], '', '', $item[ $column_name ]);
	  case 'redeems':
	  case 'credits':
		return wc_price( $item[ $column_name ] );
	  case 'user_id':
		$user_name = ucwords( get_user_meta( $item[ $column_name ], 'first_name', true ). ' '.get_user_meta( $item[ $column_name ], 'last_name', true ) );
		return $user_name ?  $user_name : get_user_meta( $item[ $column_name ], 'nickname', true );
	  default:
		return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	}
  }
  
	/**
	*  Associative array of columns
	*
	* @return array
	*/
   function get_columns() {
	 $columns = [
	   'order_id'    => __( 'Order', 'wmc' ),
	   'user_id'    => __( 'Name', 'wmc' ),
	   'credits'    => __( 'Earned Credits', 'wmc' ),
	   'redeems' => __( 'Redeemed Credits', 'wmc' ),
	 ];
   
	 return $columns;
   }
   
   function column_order_id($item){
   		$order_id = $item['order_id'];
   		if($item['order_id'] == 0)
   		{
   			$order_id = '  -  ';
   		}
   		return $order_id;
   }
   /**
	* Columns to make sortable.
	*
	* @return array
	*/
   public function get_sortable_columns() {
	 $sortable_columns = array(
	   'order_id' => array( 'order_id', false ),
	   'redeems' => array( 'redeems', true ),
	   'credits' => array( 'credits', true ),
	 );
   
	 return $sortable_columns;
   }
   
   /**
	* Handles data query and filter, sorting, and pagination.
	*/
   public function prepare_items() {
		global $obj_referal_program, $wpdb;
   
   
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$order_id = null;
		$user_id = null;
		$search_result = false;
		if( isset( $_GET['s'] ) && !empty($_GET['s']) ){
			$objReferalUsers = new Referal_Users();
			$user_id = $objReferalUsers->referral_user( 'user_id', 'referral_code', $_GET['s'] );
			if( $user_id ){
				$user_id = $objReferalUsers->get_all_referral_user_id( array( $user_id ) );	
			}else{
				$search_result = true;
			}
	 	}
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		if( $search_result && isset( $_GET['s'] ) && !empty($_GET['s']) ){
   			$wp_user_query = new WP_User_Query(
								  		array(
								    		'meta_query' => array(
								    		'relation' => 'OR',
									      	array(
										        'key' => 'first_name',
										        'value' => $_GET['s'],
										        'compare' => 'LIKE'
									      	),
										    array(
										        'key' => 'last_name',
										        'value' => $_GET['s'],
										        'compare' => 'LIKE'
								      		)
								    	)
								  	)
								);
   			$users = $wp_user_query->get_results();
   			if( $users && count( $users ) ){
   				$user_id = [];
   				foreach ( $users as $user ) {
   					$user_id[] = $user->ID;
   					$search_result = false;
   					$all_record = true;
   				}
   			}
   		}
   		if( $search_result && isset( $_GET['s'] ) && !empty($_GET['s']) && is_int( intval( $_GET['s'] ) ) ){
   			$order_id = $objReferalUsers->get_orders_by_id( $_GET['s'] );
		    if( is_array( $order_id ) && count( $order_id ) ) {
		        $search_result = false;
   				$all_record = true;
		    }
   		}

		$post_per_page = get_option('posts_per_page');
		$per_page     = $this->get_items_per_page( 'orders_per_page', $post_per_page );
		$current_page = $this->get_pagenum();

		$total_items  = $search_result ? 0 : $obj_referal_program->record_count(null, true, $user_id, $order_id);

		$this->set_pagination_args( [
		   'total_items' => $total_items, //WE have to calculate the total number of items
		   'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );
	   
	   
		$this->items = $search_result ? 0 : $obj_referal_program->select_all( $per_page, $current_page, $user_id, $order_id );
	}
  
  	public function search_box( $text, $input_id ) { ?>
	    <p class="search-box">
	      <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	      <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
	      <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
	      <br />
	      <span class="description_panel"><?php _e( 'You can search by Customer name, Order ID and Referral code', 'wmc' );?></span>
	  </p>
	<?php }

}

endif;
