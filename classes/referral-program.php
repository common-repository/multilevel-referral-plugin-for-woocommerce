<?php

if ( !class_exists( 'Referal_Program' ) ) {
    /**
     * Main / front controller class
     *
     */
    class Referal_Program {
        public $table_name;

        public function __construct() {
            global $wpdb;
            $this->table_name = $wpdb->prefix . 'referal_program';
            $this->product_commission = $wpdb->prefix . 'referal_product_commission';
        }

        /*
         * Static methods
         */
        public function create_table() {
            global $wpdb;
            $checkSQL = "show tables like '" . $this->table_name . "'";
            if ( $wpdb->get_var( $checkSQL ) != $this->table_name ) {
                $sql = "CREATE TABLE " . $this->table_name . " (\r\n\t\t\t\tid int(11) NOT NULL AUTO_INCREMENT,\r\n\t\t\t\torder_id  int(11),\r\n\t\t\t\tuser_id  int(11),\r\n\t\t\t\tcredits  decimal(10,4) DEFAULT 0.0000,\r\n\t\t\t\tredeems  decimal(10,4) DEFAULT 0.0000,\r\n\t\t\t\tdate  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\t\tis_transfer BOOLEAN NOT NULL DEFAULT FALSE,\r\n\t\t\t\tPRIMARY KEY  (id)\r\n\t\t\t  );";
                // we do not execute sql directly
                // we are calling dbDelta which cant migrate database
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta( $sql );
            }
            $checkSQL = "show tables like '" . $this->product_commission . "'";
            if ( $wpdb->get_var( $checkSQL ) != $this->product_commission ) {
                $sql = "CREATE TABLE " . $this->product_commission . " (\r\n\t\t\t\tid int(11) NOT NULL AUTO_INCREMENT,\r\n\t\t\t\tuser_id  int(11),\r\n\t\t\t\torder_id  int(11),\r\n\t\t\t\tproduct_id  int(11),\r\n\t\t\t\tcredits  decimal(10,4) DEFAULT 0.0000,\r\n\t\t\t\tdate  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\t\tPRIMARY KEY  (id)\r\n\t\t\t  );";
                // we do not execute sql directly
                // we are calling dbDelta which cant migrate database
                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                dbDelta( $sql );
            }
        }

        /**
         * Insert record
         *
         * @mvc Controller
         */
        public function insert( $data ) {
            global $wpdb;
            $wpdb->insert( $this->table_name, array(
                'order_id' => $data['order_id'],
                'user_id'  => $data['user_id'],
                'credits'  => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
                'redeems'  => ( isset( $data['redeems'] ) ? $data['redeems'] : 0 ),
            ) );
        }

        /**
         * Insert record Product Commition
         *
         * @mvc Controller
         */
        public function insert_product_commission( $data ) {
            global $wpdb;
            $wpdb->insert( $this->product_commission, array(
                'user_id'    => $data['user_id'],
                'order_id'   => $data['order_id'],
                'product_id' => $data['product_id'],
                'credits'    => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
            ) );
        }

        public function insert_redeem( $data ) {
            global $wpdb;
            $wpdb->insert( $wpdb->prefix . 'redeem_history', array(
                'mobile_number'     => $data['mobile_number'],
                'merchant_order_id' => $data['merchant_order_id'],
                'transaction_id'    => $data['transaction_id'],
                'status'            => $data['status'],
                'status'            => $data['status'],
                'payment_method'    => $data['payment_method'],
                'message'           => $data['statusMessage'],
                'user_id'           => $data['user_id'],
                'amount'            => ( isset( $data['amount'] ) ? $data['amount'] : 0 ),
            ) );
            $ins_id = $wpdb->insert_id;
            if ( $data['status'] == 'SUCCESS' ) {
                $wpdb->insert( $this->table_name, array(
                    'order_id'  => ( isset( $data['order_id'] ) ? $data['order_id'] : 0 ),
                    'user_id'   => $data['user_id'],
                    'credits'   => ( isset( $data['credits'] ) ? $data['credits'] : 0 ),
                    'redeems'   => ( isset( $data['redeems'] ) ? $data['redeems'] : 0 ),
                    'type'      => ( isset( $data['type'] ) ? $data['type'] : 0 ),
                    'redeem_id' => ( isset( $ins_id ) ? $ins_id : 0 ),
                ) );
            }
        }

        public function update( $data, $user_id ) {
            global $wpdb;
            $wpdb->update( $this->table_name, $data, array(
                'user_id' => $user_id,
            ) );
        }

        public static function delete( $order_id ) {
            global $wpdb;
            $wpdb->delete( $this->table_name, array(
                'order_id' => $order_id,
            ) );
        }

        /*
         *	Get credit for specific order
         */
        public function get_credits_by_order( $order_id ) {
            global $wpdb;
            $sql = "SELECT user_id, credits FROM " . $this->table_name . " WHERE credits > 0 AND order_id = " . $order_id . " GROUP BY user_id";
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            return $result;
        }

        /*
         *	remove credit for specific order
         */
        public function remove_credits_by_order( $order_id ) {
            global $wpdb;
            $sql = "DELETE FROM " . $this->table_name . " WHERE credits > 0 AND order_id = {$order_id}";
            $result = $wpdb->get_results( $sql );
            return $result;
        }

        /*
         *	Get earn credit list base on order.	
         */
        public function get_credits(
            $per_page = 5,
            $page_number = 1,
            $where = '',
            $order_id = array()
        ) {
            global $wpdb;
            if ( $where ) {
                $where = sprintf( 'AND user_id IN (%s)', implode( ',', $where ) );
            }
            if ( is_array( $order_id ) && count( $order_id ) ) {
                $where = sprintf( 'AND order_id IN (%s)', implode( ',', $order_id ) );
            }
            $sql = "SELECT min({$this->table_name}.id), user_id, order_id, sum(credits) as credits FROM " . $this->table_name . " RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND credits > 0 {$where} GROUP BY order_id ORDER BY ";
            $orderBy = 'order_id';
            if ( isset( $_REQUEST['orderby'] ) && !empty( $_REQUEST['orderby'] ) ) {
                $orderBy = $_REQUEST['orderby'];
            }
            $sql .= $orderBy . ' ';
            $order = 'DESC';
            if ( isset( $_REQUEST['order'] ) && !empty( $_REQUEST['order'] ) ) {
                $order = $_REQUEST['order'];
            }
            $sql .= $order;
            $sql .= " LIMIT {$per_page}";
            $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            return $result;
        }

        /*
         *	Get earn redeem list base on order.	
         */
        public function get_redeems( $per_page = 5, $page_number = 1 ) {
            global $wpdb;
            $sql = "SELECT A.user_id, A.order_id, A.redeems FROM {$this->table_name} AS A RIGHT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' WHERE A.redeems > 0 GROUP BY A.order_id ";
            if ( !empty( $_REQUEST['orderby'] ) ) {
                $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
                $sql .= ( !empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC' );
            }
            $sql .= " LIMIT {$per_page}";
            $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            return $result;
        }

        /*
         *	Get number of orders
         */
        public function record_count(
            $type = 'credits',
            $all_record = false,
            $user_id = array(),
            $order_id = array()
        ) {
            global $wpdb;
            $where = '';
            if ( is_array( $user_id ) && count( $user_id ) ) {
                $where = ' WHERE user_id IN (' . implode( ',', $user_id ) . ')';
            }
            if ( is_array( $order_id ) && count( $order_id ) ) {
                $where = ' WHERE order_id IN (' . implode( ',', $order_id ) . ')';
            }
            if ( $all_record ) {
                $sql = "SELECT count(*) FROM " . $this->table_name . " {$where}";
            } else {
                if ( $user_id ) {
                    $sql = "SELECT COUNT(*) FROM (SELECT count(*) FROM " . $this->table_name . " RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND {$type} > 0 AND user_id IN (" . implode( ',', $user_id ) . ") GROUP BY order_id) AS total ";
                } else {
                    $sql = "SELECT COUNT(*) FROM (SELECT count(*) FROM " . $this->table_name . " RIGHT JOIN {$wpdb->posts} ON {$this->table_name}.order_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status != 'trash' AND {$type} > 0 GROUP BY order_id) AS total ";
                }
            }
            return $wpdb->get_var( $sql );
        }

        /*
         *	Get total of earning credits
         */
        public function total_statistic( $type ) {
            global $wpdb;
            $creditFor = get_option( 'wmc_welcome_credit_for', 'new' );
            $sql = "SELECT sum(A.{$type}) FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash'";
            $n = apply_filters(
                'wmc_total_' . $type . '_statistic',
                $wpdb->get_var( $sql ),
                $type,
                $creditFor
            );
            if ( $n != '' ) {
                return $this->make_nice_number( $n );
            }
            return 0;
        }

        public function make_nice_number( $n ) {
            // first strip any formatting;
            $n = 0 + str_replace( ",", "", $n );
            // is this a number?
            if ( !is_numeric( $n ) ) {
                return 0;
            }
            // now filter it;
            if ( $n > 1000000000000 ) {
                return round( $n / 1000000000000, 1 ) . ' trillion';
            } else {
                if ( $n > 1000000000 ) {
                    return round( $n / 1000000000, 1 ) . ' billion';
                } else {
                    if ( $n > 1000000 ) {
                        return round( $n / 1000000, 1 ) . ' million';
                    } else {
                        if ( $n > 1000 ) {
                            return round( $n / 1000, 1 ) . 'k';
                        }
                    }
                }
            }
            return number_format( $n, 2 );
        }

        /*
         *	Get all records count
         */
        public function getall_count(
            $per_page = 5,
            $page_number = 1,
            $where = null,
            $order_id = array()
        ) {
            global $wpdb;
            $sql = "SELECT COUNT(A.id) AS records_count FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE ( B.post_status != 'trash' OR A.order_id = 0 ) ";
            if ( $where ) {
                if ( is_array( $where ) ) {
                    $sql .= ' AND A.user_id IN (' . implode( ',', $where ) . ')';
                } else {
                    $sql .= ' AND A.user_id = ' . $where;
                }
            }
            if ( is_array( $order_id ) && count( $order_id ) ) {
                $sql .= ' AND A.order_id IN (' . implode( ',', $order_id ) . ')';
            }
            if ( !empty( $_REQUEST['orderby'] ) ) {
                $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
                $sql .= ( !empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC' );
            } else {
                $sql .= ' ORDER BY A.id DESC, A.order_id DESC';
            }
            if ( $per_page > 0 ) {
                $sql .= " LIMIT {$per_page}";
                $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
            }
            $result = $wpdb->get_results( $sql );
            return $result;
        }

        /*
         *	Get all records
         */
        public function select_all(
            $per_page = 5,
            $page_number = 1,
            $where = null,
            $order_id = array()
        ) {
            global $wpdb;
            $sql = "SELECT A.* FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE ( B.post_status != 'trash' OR A.order_id = 0 ) ";
            if ( $where ) {
                if ( is_array( $where ) ) {
                    $sql .= ' AND A.user_id IN (' . implode( ',', $where ) . ')';
                } else {
                    $sql .= ' AND A.user_id = ' . $where;
                }
            }
            if ( is_array( $order_id ) && count( $order_id ) ) {
                $sql .= ' AND A.order_id IN (' . implode( ',', $order_id ) . ')';
            }
            if ( !empty( $_REQUEST['orderby'] ) ) {
                $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
                $sql .= ( !empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC' );
            } else {
                $sql .= ' ORDER BY A.id DESC, A.order_id DESC';
            }
            if ( $per_page > 0 ) {
                $sql .= " LIMIT {$per_page}";
                $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
            }
            $result = $wpdb->get_results( $sql, 'ARRAY_A' );
            return $result;
        }

        /*
         *	Availabel Credits of user
         */
        public function available_credits( $user_id ) {
            global $wpdb, $referral_cache;
            if ( isset( $referral_cache['available_credits'] ) && isset( $referral_cache['available_credits'][$user_id] ) ) {
                return $referral_cache['available_credits'][$user_id];
            }
            $creditFor = get_option( 'wmc_welcome_credit_for', 'new' );
            $user_credits = floatval( get_user_meta( $user_id, 'wmc_store_credit', 0 ) );
            $welcome_credit = floatval( get_option( 'wmc_welcome_credit', 0 ) );
            $sql = "SELECT IF ( sum(A.credits) - sum(A.redeems) , sum(A.credits) - sum(A.redeems), 0)  AS total FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND A.user_id = {$user_id} ";
            $available_credits = apply_filters( 'wmc_available_credit', $wpdb->get_var( $sql ), $user_id );
            $available_credits = apply_filters( 'wmc_total_credits_amount', $available_credits );
            $available_credits = ( $available_credits ? $available_credits : 0 );
            $referral_cache['available_credits'][$user_id] = $available_credits;
            return $available_credits;
        }

        /*
         *	Get product comittion total
         */
        public function product_wise_total( $user_id ) {
            global $wpdb;
            $sql = "SELECT IF ( sum(A.credits), sum(A.credits), 0)  AS total FROM {$this->product_commission} AS A RIGHT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND A.user_id = {$user_id} ";
            return apply_filters( 'wmc_product_wise_total', $wpdb->get_var( $sql ), $user_id );
        }

        public function total_withdraw_credit( $user_id ) {
            global $wpdb;
            $sql = "SELECT sum(A.redeems) AS total FROM {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND user_id = {$user_id} ";
            $total_withdraw_credit = $wpdb->get_var( $sql );
            return apply_filters(
                'wmc_withdraw_credited',
                $total_withdraw_credit,
                $user_id,
                $total_withdraw_credit
            );
        }

        public function total_earn_credit( $user_id ) {
            global $wpdb;
            $sql = "SELECT sum(A.credits) AS total FROM  {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND user_id = {$user_id} ";
            return apply_filters( 'wmc_withdraw_earned', $wpdb->get_var( $sql ), $user_id );
        }

        /*
         * Retrieve total number of followers
         */
        function no_of_followers( $user_id ) {
            global $wpdb;
            //return 0;
            $followers = $wpdb->get_var( 'SELECT followers_count(' . $user_id . ', \'count\' )' );
            $followers = ( $followers == '' || empty( $followers ) ? 0 : $followers );
            return $followers;
        }

        /*
         * Get current user's referal details
         */
        function get_referral_user_list( $user_id, $get_filter = null ) {
            global $wpdb;
            /*$sql = 'SELECT a.user_id, a.meta_value as first_name, b.meta_value as last_name, followers_count(a.user_id, \'count\') as followers, c.active
            		FROM '.$wpdb->usermeta.' AS a
            		JOIN '.$wpdb->usermeta.' AS b on a.user_id = b.user_id
            		JOIN '.$wpdb->prefix . 'referal_users AS c on a.user_id = c.user_id
            		WHERE a.meta_key = "first_name" AND b.meta_key = "last_name" AND c.active = 1 AND c.referral_parent = '.$user_id;*/
            $sql = 'SELECT a.user_id, a.meta_value as first_name, b.meta_value as last_name, UM.meta_value as followers, c.active, c.join_date
            FROM ' . $wpdb->usermeta . ' AS a
            JOIN ' . $wpdb->usermeta . ' AS b on a.user_id = b.user_id
            JOIN ' . $wpdb->usermeta . ' AS UM on a.user_id = UM.user_id
            JOIN ' . $wpdb->prefix . 'referal_users AS c on a.user_id = c.user_id
            WHERE a.meta_key = "first_name" AND b.meta_key = "last_name" AND UM.meta_key="total_referrals" AND c.active = 1 AND c.referral_parent = ' . $user_id;
            if ( isset( $get_filter ) && $get_filter != 'none' && $get_filter != null ) {
                $get_filter_date = $get_filter;
                $month_start_date = date( 'y-m-d', strtotime( "{$get_filter_date} first day of this month" ) );
                $month_last_date = date( 'y-m-d', strtotime( "{$get_filter_date} last day of this month" ) );
                $sql .= ' AND c.join_date BETWEEN STR_TO_DATE("' . $month_start_date . '","%Y-%m-%d") AND STR_TO_DATE("' . $month_last_date . '","%Y-%m-%d")';
            }
            if ( isset( $_GET['orderby'] ) && $_GET['orderby'] == 'desc' ) {
                $sql .= ' order by c.join_date DESC';
            } else {
                $sql .= ' order by c.join_date ASC';
            }
            $referral_result = $wpdb->get_results( $sql );
            return $referral_result;
        }

        /*
         *	Remove referral user
         */
        function remove_referral_user( $user_id ) {
            global $wpdb;
            $obj_referal_users = new Referal_Users();
            return $obj_referal_users->change_referral_user( $user_id );
        }

        /*
         *	Distrubute credits to user by order
         */
        function distribute_credit_by_order( $credit_amount ) {
            global $wpdb;
        }

        /**
         *	Get current month earning.
         *
         *	@param int $userId Requested user id
         *
         *	@return int Return total earning of current month
         */
        public function get_current_month_earning( $userId ) {
            global $wpdb;
            return $wpdb->get_var( "SELECT if ( sum(A.credits) , sum(A.credits) , 0) AS earning from  {$this->table_name} AS A LEFT JOIN {$wpdb->posts} AS B ON A.order_id = B.ID WHERE B.post_status != 'trash' AND MONTH(CURDATE())=MONTH(date) AND A.user_id = {$userId}" );
        }

        /**
         * Get referral code list
         */
        public function get_referral_code_list() {
            global $wpdb;
            $sql = "SELECT ru.user_id, ru.referral_code FROM {$wpdb->users} JOIN {$wpdb->prefix}referal_users AS ru ON ru.user_id = {$wpdb->users}.ID WHERE 1=1 AND ru.active = 1 ORDER BY ru.referral_code ASC";
            return $wpdb->get_results( $sql );
        }

    }

    // end Referal_Program
}