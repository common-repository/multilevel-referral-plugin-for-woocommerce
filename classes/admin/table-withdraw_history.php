<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if(! class_exists('WMR_Withdraw_history'))
{

class WMR_Withdraw_history extends WP_List_Table 
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->process_bulk_action();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    function get_columns(){
    $c = array(
            'cb'       => '<input type="checkbox" />',
            'display_name'    => __( 'Name', 'wmc' ),
            'mobile_number' => __( 'Mobile Number', 'wmc' ),
            'merchant_order_id'    => __( 'Merchant Order id', 'wmc' ),
            'transaction_id' => __( 'Transaction id', 'wmc' ),
            'redeems'    => __( 'Amount', 'wmc' ),
            'date'    => __( 'date', 'wmc' ),
            'status'    => __( 'Status', 'wmc' ),
            'message'    => __( 'Message', 'wmc' ),
            'payment_method'    => __( 'Payment Method', 'wmc' ),
        );

        return $c;
        
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    function get_sortable_columns() {
      $sortable_columns = array(
        
        'display_name' => array('display_name',true),
        'mobile_number'   => array('mobile_number',false),
        'merchant_order_id'   => array('merchant_order_id',false),
        'transaction_id'   => array('transaction_id',false),
        'redeems'   => array('redeems',false),
        'date'  => array('date',false),
        'status'   => array('status',false),
        'message'   =>  array( 'message' , false ) ,
        'payment_method'    =>  array( 'payment_method' , false ) 
      );
      return $sortable_columns;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        global $wpdb;

         $data = array();

        $table_redeem_history = $wpdb->prefix."redeem_history";
        $table_ref_program = $wpdb->prefix."referal_program";

        $sql  = "select * from $table_redeem_history where transaction_id != '' ";
        
        if(isset($_GET['search_by_name']) && !empty($_GET['search_by_name']) )
        {
            $str = $_GET['search_by_name'];
            $wp_user_query2 = new WP_User_Query(
            array(
                    'meta_query' => array(
                    'relation' => 'OR',
                        array(
                            'key' => 'first_name',
                            'value' => $str,
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'last_name',
                            'value' => $str,
                            'compare' => 'LIKE'
                          )
                    )
                )
            );

            $users2 = $wp_user_query2->get_results();

            foreach ($users2 as $key => $value) {
                $arr_ids[] = $value->ID;
            }

            $arr_ids = implode(",",$arr_ids);
            
            $sql .= " AND user_id in (".$arr_ids.") ";
        }

        if(isset($_GET['search_by_mobile']) && !empty($_GET['search_by_mobile']) )
        {
            
            //$sql .= " WHERE mobile_number = '" .$_GET['search_by_mobile'] ."'";
            $sql .= " AND mobile_number = '" .$_GET['search_by_mobile'] ."'";
        }

        if(isset($_GET['search_start_date']) && !empty($_GET['search_start_date']) && isset($_GET['search_end_date']) && !empty($_GET['search_end_date']) )
        {
            
            $sql .= " AND (date BETWEEN '".$_GET['search_start_date']."' AND '".$_GET['search_end_date']."')";
        }
        
        $results = $wpdb->get_results($sql);
        
        foreach ($results as $key => $value) {
            if(!$value->mobile_number)
            {
                $value->mobile_number = '-';
            }
            $data[] = array(
                    'id' =>$value->id,
                    'display_name'  => $value->user_id,
                    'mobile_number'  => $value->mobile_number,
                    'merchant_order_id' => $value->merchant_order_id,
                    'transaction_id' => $value->transaction_id,
                    'redeems' =>  number_format($value->amount , 2 ),
                    'date' => $value->date,
                    'status' =>$value->status,
                    'message'=>$value->message,
                    'payment_method'=>$value->payment_method,
                    );    
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    function column_display_name($item){
        $user_id = $item['display_name'];
        $user_info = get_userdata($user_id);
        $username = $user_info->first_name ." ".$user_info->last_name;
    
        $actions = array(
               'delete' => sprintf('<a href="?page=%s&tab=withdraw_history&action=%s&delete_cb=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
            );
        return sprintf('%1$s %2$s', $username, $this->row_actions($actions));

    }
    function column_redeems($item){

            
            $set_redeem_str = get_woocommerce_currency_symbol().' '.$item['redeems'];
            return $set_redeem_str; 
    }
    function column_cb($item)
    {
        $cb_box =  "<input type='checkbox' name='delete_cb[]' value='".$item['id']."' />"; 
        
        return $cb_box; 
    }
    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'display_name':
            case 'mobile_number':
            case 'merchant_order_id':
            case 'transaction_id':
            case 'redeems':
            case 'date':
            case 'status':
            case 'message':
            case 'payment_method':

                return $item[ $column_name ];
          default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes

            }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'date';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
    function get_bulk_actions() {
      $actions = array(

            'delete'    => 'Delete',
      
      );

      return $actions;
    }
    function no_items() {
    echo __( 'No records found.','wmc' );
  }

    public function process_bulk_action() {
        global $wpdb;
            // security check!
            if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

                $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
                $action = 'bulk-' . $this->_args['plural'];

                
                if ( ! wp_verify_nonce( $nonce, $action ) )
                    wp_die( 'Nope! Security check failed!' );

            }
            
            $action = $this->current_action();

            switch ( $action ) {

                case 'delete':

                    if(isset($_GET['delete_cb']))
                    {
                        $delete_items = array();
                        if(is_array($_GET['delete_cb']))
                        {
                            $delete_items = implode(',', $_GET['delete_cb']);    
                        }else{
                            $delete_items = $_GET['delete_cb'];    
                        }
        
                        $check = $wpdb->query("delete from ".$wpdb->prefix ."redeem_history WHERE id in (".$delete_items.")");

                        add_action( 'admin_notices', array($this, 'mef_delete_admin_notice__success' ) );
                    }

                    break;

                case 'save':
                    wp_die( 'Save something' );
                    break;

                default:
                    // do nothing or something else
                    return;
                    break;
            }

            return;
        }
        function mef_delete_admin_notice__success() {
         ?>
             <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Withdraw transaction deleted', 'woocommerce-extention' ); ?></p>
            </div>
            <?php
        }

    }
    
}
?>