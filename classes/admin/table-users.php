<?php



if( ! class_exists( 'WP_List_Table' ) ) {

    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}

if( !class_exists('WMR_User_Table') ):

class WMR_User_Table extends WP_List_Table {



    function __construct(){

		global $status, $page, $obj_referal_program;



        

		parent::__construct( [

			'singular' => __( 'User', 'wmc' ), //singular name of the listed records

			'plural'   => __( 'Users', 'wmc' ), //plural name of the listed records

			'ajax'     => false //should this table support ajax?



		] );



		$obj_referal_program = new Referal_Program();



		add_action( 'admin_head', array( &$this, 'admin_header' ) );            



    }



  function admin_header() {

    $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;

    if( 'wc_referral' != $page )

    return;

    echo '<style type="text/css">';

	echo '.search_email{ width:42%}';

    echo '</style>';

  }



  function no_items() {

    echo __( 'No users found, dude.','wmc' );

  }



  function column_default( $item, $column_name ) {

	

	switch ( $column_name ) {

		case 'display_name':

		case 'email':

		case 'join_date':

        case 'referrer_name':

		case 'referral_code':

		case 'no_of_followers':

		case 'total_credits':

		case 'view_hierarchie':

			return $item[ $column_name ];

	  default:

			return print_r( $item, true ); //Show the whole array for troubleshooting purposes

	}

	

  }



  function bulk_actions($which = ''){

	if ( is_null( $this->_actions ) ) {

		$no_new_actions = $this->_actions = $this->get_bulk_actions();

		$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );

		$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );

		$two = '1';

	} else {

		$two = '2';

	}

	if($two == 1){

		if ( isset( $_GET['user_status'] ) && '0' == $_GET['user_status'] ) {

			echo '<input type="hidden" name="user_status" value="0" />';

		}

		echo '<input type="text" name="search_by_name" placeholder="'.__('Search By Name','wmc').'" value="'.(isset($_REQUEST['search_by_name'])?$_REQUEST['search_by_name']:'').'" class="wmc_search_by_name"/>';

		echo '<input type="text" name="search_by_email" class="search_email" placeholder="'.__('Search By Email','wmc').'" value="'.(isset($_REQUEST['search_by_email'])?$_REQUEST['search_by_email']:'').'"/>';

		echo '<lable>'.__('Date Range','wmc').' :</lable>';

		echo '<input type="text" name="search_by_join_sdate" placeholder="YYYY/MM/DD" value="'.(isset($_REQUEST['search_by_join_sdate'])?$_REQUEST['search_by_join_sdate']:'').'" class="wmc_search_by_date"/>';

		echo '<input type="text" name="search_by_join_edate" placeholder="YYYY/MM/DD" value="'.(isset($_REQUEST['search_by_join_edate'])?$_REQUEST['search_by_join_edate']:'').'" class="wmc_search_by_date" />';

		submit_button( __( 'Apply','wmc'), 'action', '', false, array( 'id' => "doaction" ) );

		echo '<input type="button" value="'.__('Reset','wmc').'" class="button action" id="reset_button"><br />';		

		if(!isset($_GET['tab'])  && !isset($_GET['user_status'])){ 

        	echo '<div class="in_active_user_panel"><a class="button-secondary" href="'.admin_url('admin.php?page=wc_referral&user_status=0').'">'.__('Deleted Referrals','wmc').'</a></div>';
	    } 

	}

  }

  

function get_sortable_columns() {

  $sortable_columns = array(

    'join_date'  => array('join_date',false),

    'display_name' => array('display_name',true),

    'email'   => array('email',false),

    'referrer_name'   => array('referrer_name',false),

    'referral_code'   => array('referral_code',false),

    'no_of_followers'   => array('no_of_followers',false),

    'total_credits'   => array('total_credits',false),

	'view_hierarchie'	=>	array( 'view_hierarchie' , false ) ,

	'deactivate_date'	=>	array( 'deactivate_date' , false ) 

  );

  return $sortable_columns;

}



function get_columns(){

    $c = array(

			//'cb'       => '<input type="checkbox" />',

			'display_name'    => __( 'Name', 'wmc' ),

			'email' => __( 'Email', 'wmc' ),

            'referrer_name'    => __( 'Referrer', 'wmc' ),

			'referral_code'    => __( 'Referral Code', 'wmc' ),

			'join_date'    => __( 'Join Date', 'wmc' ),

			'no_of_followers' => __('Total Referrals', 'wmc' ),

			'total_credits'    => __( 'Total Credits', 'wmc' ),

			'referral_level'    => __( 'Referral Level', 'wmc' ),

			'view_hierarchie'    => __( 'Hierarchy', 'wmc' )

		);



		if ( isset( $_GET['user_status'] ) && '0' == $_GET['user_status'] ) {

			unset( $c['no_of_followers'] );	

			unset( $c['total_credits'] );	

			unset( $c['view_hierarchie'] );

			unset( $c['referral_level'] );

			$c['deactivate_date'] = __( 'Deactive Date', 'wmc' );

			$c['view_inaciver_user'] = '';

		}



		return $c;

		

    }



function usort_reorder( $a, $b ) {

  // If no sort, default to title

  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'username';

  // If no order, default to asc

  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

  // Determine sort order

  $result = strcmp( $a[$orderby], $b[$orderby] );

  // Send final sort direction to usort

  return ( $order === 'asc' ) ? $result : -$result;

}



function get_bulk_actions() {

  $actions = array(

    //'delete'    => 'Delete'

  );

  return $actions;

}



function column_cb($item) {

        return sprintf(

            '<input type="checkbox" name="user[]" value="%s" />', $item['ID']

        );    

    }



public function prepare_items() {

		global $role, $usersearch;        

		$usersearch = isset( $_REQUEST['search_by_email'] ) ? wp_unslash( trim( $_REQUEST['search_by_email'] ) ) : '';

		

		//$role = 'ninjas';



		$per_page = ( $this->is_site_users ) ? 'site_users_network_per_page' : 'users_per_page';

		$users_per_page = $this->get_items_per_page( $per_page );



		$paged = $this->get_pagenum();



		/*$meta_query_args[] = array(

						'key'     => 'join_date',

						'value'   => '',

						'compare' => '!='

					);*/

        $meta_query_args[] = array();

        $args = array(

            'number' => $users_per_page,

            'offset' => ( $paged-1 ) * $users_per_page,

            'search' => $usersearch,

            'fields' => 'all_with_meta',

			'meta_query' => $meta_query_args

        );



		if ( '' !== $args['search'] )

			$args['search'] = '*' . $args['search'] . '*';



		if ( $this->is_site_users )

			$args['blog_id'] = $this->site_id;



		if ( isset( $_REQUEST['orderby'] ) )

			$args['orderby'] = $_REQUEST['orderby'];



		if ( isset( $_REQUEST['order'] ) )

			$args['order'] = $_REQUEST['order'];



		if(isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] == 'no_of_followers' ){

			$args['orderby'] = 'meta_value_num';

			$args['meta_key'] = 'total_referrals';

		}

		if(isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] == 'total_credits' ){

			$args['orderby'] = 'meta_value_num';

			$args['meta_key'] = 'total_credits';

		}

		if(isset( $_REQUEST['orderby'] ) && $_REQUEST['orderby'] == 'join_date' ){

			$args['orderby'] = 'id';

//			$args['meta_key'] = 'join_date';

		}

		if(isset( $_REQUEST['search_by_name'] )  && $_REQUEST['search_by_name'] != ''  ){

			$name_list = explode( ' ' , $_REQUEST['search_by_name'] );
			$data = [];
			foreach( $name_list as $name ){
				$data[] = array(
						'key'     => 'first_name',
						'value'   => $name,
						'compare' => 'LIKE'
					);
				$data['relation'] = 'OR';
				$data[] = array(
						'key'     => 'last_name',
						'value'   => $name,
						'compare' => 'LIKE'
					);
			}


			$meta_query_args[] = array(
					$data
				);

			$args['meta_query'] = $meta_query_args;

		}

		

		/**

		 * Filter the query arguments used to retrieve users for the current users list table.

		 *

		 * @since 4.4.0

		 *

		 * @param array $args Arguments passed to WP_User_Query to retrieve items for the current

		 *                    users list table.

		 */

		$args = apply_filters( 'users_list_table_query_args', $args );



		// Query the user IDs for this page

      

      

		$wp_user_search = new WP_User_Query( $args );

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(

			'total_items' => $wp_user_search->get_total(),

			'per_page' => $users_per_page,

		) );

	}

    

    public function display_rows() {

		global $obj_referral_users;

		// Query the post counts for this page

		if ( ! $this->is_site_users )

			$post_counts = count_many_users_posts( array_keys( $this->items ) );

//print_r($this->items);



		$obj_referral_users = new Referal_Users();

		

		foreach ( $this->items as $userid => $user_object ) {

            

			if ( is_multisite() && empty( $user_object->allcaps ) )

				continue;

		

			echo "\n\t" . $this->single_row( $user_object, '', '', isset( $post_counts ) ? $post_counts[ $userid ] : 0 );

		}

	}

    

    public function single_row( $user_object, $style = '', $role = '', $numposts = 0 ) {

		

		global $obj_referal_program, $obj_referral_users;

		

		if ( ! ( $user_object instanceof WP_User ) ) {

			$user_object = get_userdata( (int) $user_object );

		}

		$user_object->filter = 'display';

		$email = $user_object->user_email;



		if ( $this->is_site_users )

			$url = "site-users.php?id={$this->site_id}&amp;";

		else

			$url = 'users.php?';



		// Set up the hover actions for this user

		$actions = array();

		$checkbox = '';

		// Check if the user for this row is editable

		if ( current_user_can( 'list_users' ) ) {

			// Set up the user editing link

			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) );



			if ( current_user_can( 'edit_user',  $user_object->ID ) ) {

				$edit = "<strong><a href=\"$edit_link\">$user_object->user_login</a></strong><br />";

				$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';

			} else {

				$edit = "<strong>$user_object->user_login</strong><br />";

			}

			/*

			if ( !is_multisite() && get_current_user_id() != $user_object->ID && current_user_can( 'delete_user', $user_object->ID ) )

				$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$user_object->ID", 'bulk-users' ) . "'>" . __( 'Delete' ) . "</a>";

			if ( is_multisite() && get_current_user_id() != $user_object->ID && current_user_can( 'remove_user', $user_object->ID ) )

				$actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( $url."action=remove&amp;user=$user_object->ID", 'bulk-users' ) . "'>" . __( 'Remove' ) . "</a>";



			/**

			 * Filter the action links displayed under each user in the Users list table.

			 *

			 * @since 2.8.0

			 *

			 * @param array   $actions     An array of action links to be displayed.

			 *                             Default 'Edit', 'Delete' for single site, and

			 *                             'Edit', 'Remove' for Multisite.

			 * @param WP_User $user_object WP_User object for the currently-listed user.

			 */

			$actions = apply_filters( 'user_row_actions', $actions, $user_object );



			// Set up the checkbox ( because the user is editable, otherwise it's empty )

			$checkbox = '<label class="screen-reader-text" for="user_' . $user_object->ID . '">' . sprintf( __( 'Select %s' ), $user_object->user_login ) . '</label>'

						. "<input type='checkbox' name='users[]' id='user_{$user_object->ID}' value='{$user_object->ID}' />";



		} else {

			$edit = '<strong>' . $user_object->user_login . '</strong>';

		}

		$avatar = get_avatar( $user_object->ID, 32 );



		$r = "<tr id='user-$user_object->ID'>";



		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();



		$available_credits = $obj_referal_program->available_credits( $user_object->ID );

        $parent_user= $obj_referral_users->referral_user( 'referral_parent', 'user_id', $user_object->ID );

        $referrerName='';

        if($parent_user!=0){

            $referrerInfo=get_user_meta($parent_user);

            if(isset($referrerInfo['first_name'][0]) && $referrerInfo['first_name'][0]!=''){

                $referrerName.=$referrerInfo['first_name'][0];

            }

            if(isset($referrerInfo['last_name'][0]) && $referrerInfo['last_name'][0]!=''){

                $referrerName.=' '.$referrerInfo['last_name'][0];

            }

            if($referrerName==''){

                $referrerName.=$referrerInfo['nickname'][0];

            }

                

            $referrerName = '<a href="'.get_edit_user_link( $parent_user ).'">'. $referrerName.'</a>';            

        }else{

            $referrerName='-';

        }

        

		//$no_of_followers = $obj_referal_program->no_of_followers( $user_object->ID );

		$referral_user_info = $obj_referral_users->get_referral_user( $user_object->ID );

		//$no_of_followers = $referral_user_info['followers'];

		$referral_link	=	get_the_permalink( get_option('woocommerce_myaccount_page_id') );

		

		//update_user_meta(  $user_object->ID , 'total_referrals', $no_of_followers);	

		update_user_meta(  $user_object->ID , 'total_credits', $available_credits);

		

		

			$deactive_date = '';

			

			if ( isset( $_GET['user_status'] ) && '0' == $_GET['user_status'] ) {

				$deactive_date = $obj_referral_users->referral_user( 'update_date', 'user_id', $user_object->ID );

			}

			

		foreach ( $columns as $column_name => $column_display_name ) {

			$classes = "$column_name column-$column_name";

			if ( $primary === $column_name ) {

				$classes .= ' has-row-actions column-primary';

			}

			if ( 'posts' === $column_name ) {

				$classes .= ' num'; // Special case for that column

			}



			if ( in_array( $column_name, $hidden ) ) {

				$classes .= ' hidden';

			}



			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';



			$attributes = "class='$classes' $data";



			if ( 'cb' === $column_name ) {

				$r .= "<th scope='row' class='check-column'>$checkbox</th>";

			} else {

				

				$r .= "<td $attributes>";

				switch ( $column_name ) {

					case 'username':

						$r .= "$avatar $edit";

						break;

					case 'display_name':

						$r .= apply_filters( 'referral_display_name', ucwords($user_object->first_name .' '. $user_object->last_name), $user_object );

						break;

					case 'email':

                        $r .= "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";

                        break;

                    case 'referrer_name':

						$r .= $referrerName;

						break;

					case 'referral_code':

						$link = add_query_arg('ru', $referral_user_info['referral_code'], $referral_link );

						$r .= '<div><a target="_blank" href="'.$link.'">'. $referral_user_info['referral_code'] .'</a></div>';

						break;

					case 'join_date':

						$r .= $referral_user_info['join_date'];

						break;

					case 'deactivate_date':

						$r .= $deactive_date;

						break;

					case 'no_of_followers':

                        //$r .= $no_of_followers;

						$r .= '<div class="wmcReferralsCount" data-name="'.ucwords($user_object->first_name .' '. $user_object->last_name).'" data-id="'.$user_object->ID.'" area-label="'.__('Calculating Referral Count','wmc').'"><img title="'.__('Calculating Referral Count','wmc').'" src="'.WMC_URL.'images/9a7c10bc-b17b-467e-b42d-4f2faf643445.svg" alt="'.__('Loader Image','wmc').'"/></div>';

						break;

					case 'total_credits':

						$r .= wc_price( $available_credits ? $available_credits : 0 );

						break;

					case 'referrer_name':

						$r .= wc_price( $available_credits ? $available_credits : 0 );

						break;

					case 'view_hierarchie':

                        //$r .= $no_of_followers ? '<a href="#" data-name="'.ucwords($user_object->first_name .' '. $user_object->last_name).'" class="view_hierarchie" data-total="'.$no_of_followers.'" data-id="'.$user_object->ID.'">'.__('View hierarchy').'</a>' : '';

						$r .= '<div class="wmcView"><img title="'.__('Calculating Referral Count','wmc').'" src="'.WMC_URL.'images/9a7c10bc-b17b-467e-b42d-4f2faf643445.svg" alt="'.__('Loader Image','wmc').'"/></div>';

						break;

					case 'referral_level';
						global $obj_referral_users;
						$referral_level = $obj_referral_users->get_referral_user_level( $user_object->ID );   
					    $r .=  __( "Level", 'wmc' ). sprintf( " <span class='referral_level_count'>%d</span>", $referral_level );
					    if( $referral_level > 1 ){
					    	$_GET['changeReferralsLevel'] = $user_object->ID;
					    	$url = add_query_arg( $_GET, admin_url('admin.php') );
						    $r .= ' <a href="'. $url .'" class="move_level_up" title="'. __( "Move to up level", 'wmc' ) .'" data-ID="'. $user_object->ID .'"></a>';
						}
						break;

					case 'view_inaciver_user':

						$r .= '<a href="#" class="active_referral_user" data-id="'.$user_object->ID.'">'.__('Add back to referrals', 'wmc').'</a>';	

						break;

					default:

						/**

						 * Filter the display output of custom columns in the Users list table.

						 *

						 * @since 2.8.0

						 *

						 * @param string $output      Custom column output. Default empty.

						 * @param string $column_name Column name.

						 * @param int    $user_id     ID of the currently-listed user.

						 */

						$r .= apply_filters( 'manage_users_custom_column', '', $column_name, $user_object->ID );

				}



				if ( $primary === $column_name ) {

					$r .= $this->row_actions( $actions );

				}

				$r .= "</td>";

			}

		}

		$r .= '</tr>';



		return $r;

	}



	



	

} //class
endif;