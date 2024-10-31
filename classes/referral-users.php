<?php

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( !class_exists( 'Referal_Users' ) ) {
    /**
     * Main / front controller class
     *
     */
    class Referal_Users extends WooCommerce_Multilevel_Referal {
        public $table_name;

        public $arrFollowersCount;

        public $arrUpdateFollowers;

        public function __construct() {
            global $wpdb;
            $this->table_name = $wpdb->prefix . 'referal_users';
            $this->arrFollowersCount = array();
            $this->arrUpdateFollowers = array();
            $this->register_hook_callbacks();
        }

        public function register_hook_callbacks() {
            add_action( 'init', array($this, 'join_referral_program') );
            add_action( 'init', array($this, 'send_invitation') );
            add_action( 'woocommerce_register_form_start', array($this, 'referral_register_start_fields') );
            add_action( 'woocommerce_register_form', array($this, 'referral_register_fields') );
            add_action(
                'woocommerce_register_post',
                array($this, 'referral_registration_validation'),
                1,
                3
            );
            add_action( 'woocommerce_created_customer', array($this, 'referral_customer_save_data'), 10 );
            add_action( 'delete_user', array($this, 'delete_user_callback') );
            add_shortcode( 'referral_link', array($this, 'referral_link_callback') );
            add_shortcode( 'wmc_my_affiliate_tab', array($this, 'wmc_my_affiliates') );
            add_shortcode( 'wmc_my_referral_tab', array($this, 'wmc_my_referrals') );
            add_shortcode( 'wmc_stat_blocks', array($this, 'wmc_referral_stats_blocks') );
            add_shortcode( 'wmc_invite_friends', array($this, 'referral_user_invite_friends') );
            add_shortcode( 'wmc_show_credit_info', array($this, 'referral_user_credit_info') );
            add_shortcode( 'wmc_show_affiliate_info', array($this, 'wmcShowMyAffiliates') );
            add_action( 'init', array($this, 'init_hook') );
            add_action( 'wp', array($this, 'fnChangeShareContent') );
            add_action( 'wp_head', array($this, 'fnShareOnWhatsup') );
            add_action(
                'profile_update',
                array($this, 'wmc_update_user_to_referal'),
                10,
                2
            );
        }

        public function wmc_update_user_to_referal( $user_id, $old_user_data ) {
            if ( isset( $_POST['add_user_to_referal'] ) ) {
                $parent_id = ( isset( $_POST['parent_userid'] ) ? $_POST['parent_userid'] : 0 );
                $benefit = 0;
                $referral_code = $this->referral_code( $user_id );
                $this->insert( array(
                    'user_id'          => $user_id,
                    'referral_parent'  => $parent_id,
                    'active'           => 1,
                    'referral_code'    => $referral_code,
                    'referral_email'   => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( $_POST['referral_email'] ) : '' ),
                    'referal_benefits' => $benefit,
                ) );
                update_user_meta( $user_id, 'total_referrals', 0 );
                $this->fnUpdateFollowersCount( $user_id );
            }
        }

        public function fnShareOnWhatsup() {
            if ( isset( $_GET['share'] ) && $_GET['share'] == md5( 'whatsup' ) ) {
                $my_account_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
                $my_account_link = add_query_arg( 'ru', $_GET['ru'], $my_account_link );
                $output = '<meta property="og:url" content="' . $my_account_link . '" >';
                $output .= '<meta property="og:title" content="' . $_GET['title'] . '" >';
                $output .= '<meta property="og:description" content="' . $_GET['content'] . '" >';
                $output .= '<meta property="og:image" content="' . $_GET['image'] . '" >';
                $output .= '<meta property="og:image:width" content="500" >';
                $output .= '<meta property="og:image:height" content="300" >';
                echo $output;
            }
        }

        /*
         *    Delete user from referral program
         *
         *    @param int Deleted user id
         *
         *    @return void
         */
        public function delete_user_callback( $customer_id ) {
            global $wpdb;
            $this->change_referral_user( $customer_id );
            $this->delete( $customer_id );
            $parent_user_id = get_user_meta( $customer_id, 'meta_value', true );
            $this->fnUpdateFollowersCount( $parent_user_id );
            $query = 'UPDATE ' . $wpdb->usermeta . ' SET meta_value = "' . $parent_user_id . '" WHERE meta_key = "referral_parent" AND user_id IN ( SELECT * from ( SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE `meta_key` LIKE "referral_parent" AND `meta_value` LIKE "' . $customer_id . '" ) as a)';
            $wpdb->query( $query );
        }

        /*
         * Call of referral_link shortcode
         *
         * @param $atts Attributes of shortcode
         *
         * @return string Link of referral program.
         */
        public function referral_link_callback( $atts ) {
            global $customer_id, $referral_code;
            //$text_link = 'Click here';
            $pull_quote_atts = shortcode_atts( array(
                'text' => 'Click here',
            ), $atts );
            $link = add_query_arg( 'ru', $referral_code, get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
            return '<a href="' . $link . '" target="_blank">' . $pull_quote_atts['text'] . '</a>';
        }

        function fnCreateSQLfile( $content ) {
            if ( $content != '' ) {
                $fileName = WMC_DIR . "followers_count.sql";
                $myfile = fopen( WMC_DIR . "followers_count.sql", "w" );
                if ( $myfile !== false ) {
                    fwrite( $myfile, $content );
                }
                fclose( $myfile );
            }
        }

        /*
         * Static methods
         */
        public function create_table() {
            global $wpdb;
            $wpdb->query( 'DROP FUNCTION IF EXISTS `followers_count`' );
            //$sql = " DELIMITER ;;";
            //$wpdb->query( $sql );
            $sql = "CREATE FUNCTION `followers_count`(`parent_id` INT, `return_value` VARCHAR(1024)) \r\n            RETURNS VARCHAR(1024)\r\n            BEGIN\r\n            DECLARE rv,q,queue,queue_children2 VARCHAR(1024);\r\n            DECLARE queue_length,pos INT;\r\n            DECLARE front_id BIGINT;\r\n            DECLARE no_of_followers INT;\r\n\r\n            SET rv = parent_id;\r\n            SET queue = parent_id;\r\n            SET queue_length = 1;\r\n            SET no_of_followers = 0;\r\n\r\n            WHILE queue_length > 0 DO\r\n\r\n            SET front_id = FORMAT(queue,0);\r\n            IF queue_length = 1 THEN\r\n            SET queue = '';\r\n            ELSE\r\n            SET pos = LOCATE(',',queue) + 1;\r\n            SET q = SUBSTR(queue,pos);\r\n            SET queue = q;\r\n            END IF;\r\n            SET queue_length = queue_length - 1;\r\n\r\n            SELECT IFNULL(qc,'') INTO queue_children2\r\n            FROM (SELECT GROUP_CONCAT(user_id) qc\r\n            FROM " . $this->table_name . " WHERE referral_parent IN (front_id)) A;\r\n\r\n            IF LENGTH(queue_children2) = 0 THEN\r\n            IF LENGTH(queue) = 0 THEN\r\n            SET queue_length = 0;\r\n            END IF;\r\n            ELSE\r\n            IF LENGTH(rv) = 0 THEN\r\n            SET rv = queue_children2;\r\n            ELSE\r\n            SET rv = CONCAT(rv,',',queue_children2);\r\n            END IF;\r\n            IF LENGTH(queue) = 0 THEN\r\n            SET queue = queue_children2;\r\n            ELSE\r\n            SET queue = CONCAT(queue,',',queue_children2);\r\n            END IF;\r\n            SET queue_length = LENGTH(queue) - LENGTH(REPLACE(queue,',','')) + 1;\r\n            END IF;\r\n            END WHILE;\r\n\r\n            IF(return_value = 'count') THEN\r\n            SELECT count(*) into no_of_followers  FROM " . $this->table_name . " WHERE active = 1 AND FIND_IN_SET(referral_parent, rv );\r\n\r\n            RETURN no_of_followers;\r\n            ELSE\r\n            RETURN rv;\r\n            END IF;\r\n            END ;;";
            // $this->fnCreateSQLfile($sql);
            //  $wpdb->query( $sql );
            // $sql = " DELIMITER ;";
            //$wpdb->query( $sql );
            $checkSQL = "show tables like '" . $this->table_name . "'";
            if ( $wpdb->get_var( $checkSQL ) != $this->table_name ) {
                $sql = "CREATE TABLE " . $this->table_name . " (\r\n                id int(11) NOT NULL AUTO_INCREMENT,\r\n                user_id int(11)  NOT NULL,\r\n                referral_parent  int(11)  NOT NULL,\r\n                active  TINYINT(1) NOT NULL DEFAULT 1,\r\n                referral_code VARCHAR(5) NOT NULL,\r\n                referal_benefits  TINYINT(1) NOT NULL DEFAULT 0,\r\n                referral_email VARCHAR(50) NOT NULL,\r\n                join_date  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\r\n                update_date  TIMESTAMP NOT NULL DEFAULT 0,\r\n                PRIMARY KEY  (id),\r\n                INDEX `referral_users` (`referral_parent`, `user_id`)\r\n                );";
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
            $wpdb->insert( $this->table_name, $data );
        }

        public function delete( $user_id ) {
            global $wpdb;
            $wpdb->delete( $this->table_name, array(
                'user_id' => $user_id,
            ) );
        }

        public function update( $user_id, $referral_parent, $status = 1 ) {
            global $wpdb;
            $wpdb->update( $this->table_name, array(
                'active'          => $status,
                'update_date'     => date( "Y-m-d H:i:s" ),
                'referral_parent' => $referral_parent,
            ), array(
                'user_id' => $user_id,
            ) );
        }

        public function updateAll( $data, $user_id ) {
            global $wpdb;
            $wpdb->update( $this->table_name, $data, array(
                'user_id' => $user_id,
            ) );
        }

        public function fnUpdateFollowersCount( $customerID ) {
            global $wpdb;
            $sql = 'SELECT referral_parent FROM ' . $this->table_name . ' WHERE user_id=' . $customerID;
            $rows = $wpdb->get_results( $sql );
            if ( is_array( $rows ) && count( $rows ) > 0 ) {
                foreach ( $rows as $row ) {
                    if ( isset( $row->referral_parent ) && $row->referral_parent != '' && $row->referral_parent != 0 ) {
                        $cntFollowers = $this->fnGetFollowersCount( $row->referral_parent );
                        update_user_meta( $row->referral_parent, 'total_referrals', $cntFollowers );
                        $this->fnUpdateFollowersCount( $row->referral_parent );
                    }
                }
            }
        }

        function fnReadWriteContentsOfFile( $mode = 'read' ) {
            if ( $mode == 'read' ) {
                $str = file_get_contents( WMC_DIR . 'includes/referrals.tmp' );
                if ( $str != '' ) {
                    $this->arrFollowersCount = json_decode( $str );
                    unset($str);
                }
            } else {
                file_put_contents( WMC_DIR . 'includes/referrals.tmp', json_encode( $this->arrFollowersCount ) );
            }
        }

        public function fnGetReferralsIdsByLevel( $parentId ) {
            global $wpdb;
            $sql = 'SELECT user_id FROM ' . $this->table_name . ' WHERE referral_parent=' . $parentId;
            $rows = $wpdb->get_results( $sql );
            if ( is_array( $rows ) && count( $rows ) > 0 ) {
                return $rows;
            }
            return 0;
        }

        public function fnGetFollowersCount( $parentId ) {
            global $wpdb, $wmc_cache;
            if ( isset( $wmc_cache['followers_count'] ) && isset( $wmc_cache['followers_count'][$parentId] ) ) {
                return $wmc_cache['followers_count'][$parentId];
            }
            if ( !isset( $wmc_cache['followers_count'] ) ) {
                $wmc_cache['followers_count'] = [];
            }
            if ( isset( $this->arrFollowersCount[$parentId] ) ) {
                $wmc_cache['followers_count'][$parentId] = $this->arrFollowersCount[$parentId];
                return $this->arrFollowersCount[$parentId];
            } else {
                $cntFollowers = 0;
                $sql = 'SELECT user_id FROM ' . $this->table_name . ' WHERE referral_parent=' . $parentId;
                if ( isset( $wmc_cache['referral_user_list'] ) && isset( $wmc_cache['referral_user_list'][$parentId] ) ) {
                    $rows = $wmc_cache['referral_user_list'][$parentId];
                } else {
                    $rows = $wpdb->get_results( $sql );
                    $wmc_cache['referral_user_list'][$parentId] = $rows;
                }
                if ( is_array( $rows ) && count( $rows ) > 0 ) {
                    $cntFollowers += count( $rows );
                    foreach ( $rows as $row ) {
                        $cntFollowers += $this->fnGetFollowersCount( $row->user_id );
                    }
                }
                unset($rows);
                unset($sql);
                update_user_meta( $parentId, 'total_referrals', $cntFollowers );
                $this->arrFollowersCount[$parentId] = $cntFollowers;
                $wmc_cache['followers_count'][$parentId] = $cntFollowers;
                return $cntFollowers;
            }
        }

        function wmc_get_customer_total_spent( $customer_id, $month ) {
            global $wpdb, $wmc_cache;
            if ( !isset( $wmc_cache['customer_total_spent'] ) ) {
                $current_date = date( 'Y-m-d' );
                $end_date_current = date( "Y-{$month}-01" );
                $end_date_current = date( "Y-{$month}-t", strtotime( "{$end_date_current}" ) );
                $end_date_future = date( "Y-{$month}-t", strtotime( "{$end_date_current} +1 year" ) );
                $end_date_past = date( "Y-{$month}-t", strtotime( "{$end_date_current} -1 year" ) );
                if ( $current_date > $end_date_past && $end_date_current >= $current_date ) {
                    $start_date = date( "Y-m-d", strtotime( "{$end_date_past} +1 day" ) );
                    $end_date = $end_date_current;
                }
                if ( $current_date > $end_date_current && $end_date_future >= $current_date ) {
                    $start_date = date( "Y-m-d", strtotime( "{$end_date_current} +1 day" ) );
                    $end_date = $end_date_future;
                }
                $wmc_cache['customer_total_spent']['start_date'] = $start_date;
                $wmc_cache['customer_total_spent']['end_date'] = $end_date;
            }
            $start_date = $wmc_cache['customer_total_spent']['start_date'];
            $end_date = $wmc_cache['customer_total_spent']['end_date'];
            $statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
            $spent = $wpdb->get_var( "SELECT SUM(meta2.meta_value)\r\n                    FROM {$wpdb->posts} as posts\r\n                    LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id\r\n                    LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id\r\n                    WHERE   meta.meta_key       = '_customer_user'\r\n                    AND     meta.meta_value     = '" . esc_sql( $customer_id ) . "'\r\n                    AND     posts.post_type     = 'shop_order'\r\n                    AND     posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )\r\n                    AND     posts.post_date     >= '{$start_date} 23:59:59' \r\n                    AND     posts.post_date     <= '{$end_date} 23:59:59' \r\n                    AND     meta2.meta_key      = '_order_total'" );
            return $spent;
        }

        /*
         *
         */
        public function get_referral_user( $user_id ) {
            global $wpdb;
            if ( !$user_id ) {
                return array(
                    'referral_code'    => '',
                    'join_date'        => '',
                    'referal_benefits' => '',
                );
            }
            $sql = 'SELECT RU.referral_code, RU.join_date, RU.referal_benefits FROM ' . $this->table_name . ' AS RU WHERE RU.user_id =' . $user_id;
            $result = $wpdb->get_row( $sql, ARRAY_A );
            return $result;
        }

        public function referral_user( $user_field, $where, $user_id ) {
            global $wpdb, $wmc_cache;
            if ( isset( $wmc_cache['referral_user_query'] ) && isset( $wmc_cache['referral_user_query'][$where] ) && isset( $wmc_cache['referral_user_query'][$where][$user_field] ) && isset( $wmc_cache['referral_user_query'][$where][$user_field][$user_id] ) ) {
                return $wmc_cache['referral_user_query'][$where][$user_field][$user_id];
            }
            if ( !isset( $wmc_cache['referral_user_query'] ) ) {
                $wmc_cache['referral_user_query'] = [];
            }
            if ( !isset( $wmc_cache['referral_user_query'][$where] ) ) {
                $wmc_cache['referral_user_query'][$where] = [];
            }
            if ( !isset( $wmc_cache['referral_user_query'][$where][$user_field] ) ) {
                $wmc_cache['referral_user_query'][$where][$user_field] = [];
            }
            $user_data = $wpdb->get_var( 'SELECT ' . $user_field . ' FROM ' . $this->table_name . ' WHERE ' . $where . ' = "' . $user_id . '"' );
            $wmc_cache['referral_user_query'][$where][$user_field][$user_id] = $user_data;
            return $user_data;
        }

        public function change_referral_user( $user_id ) {
            global $wpdb;
            $parent_referral_user = $wpdb->get_var( 'SELECT referral_parent FROM ' . $this->table_name . ' WHERE user_id = ' . $user_id );
            if ( $parent_referral_user ) {
                $this->update( $user_id, $parent_referral_user, 0 );
                $query = 'UPDATE ' . $this->table_name . ' SET referral_parent = ' . $parent_referral_user . ' WHERE referral_parent = ' . $user_id;
                $wpdb->query( $query );
            }
            return $parent_referral_user;
        }

        public function active_referral_user( $user_id ) {
            global $wpdb, $inactive_user_array;
            $parent_referral_user = $wpdb->get_var( 'SELECT referral_parent FROM ' . $this->table_name . ' WHERE user_id = ' . $user_id );
            $this->update( $user_id, $parent_referral_user, 1 );
            $query = 'SELECT um.user_id FROM ' . $wpdb->usermeta . ' AS um JOIN ' . $this->table_name . ' AS ru ON ru.user_id = um.user_id WHERE ru.active = 1 AND um.meta_value = "' . $user_id . '" AND um.`meta_key` = "referral_parent"';
            $active_user_list = $wpdb->get_col( $query );
            if ( count( $active_user_list ) ) {
                $query = 'UPDATE ' . $this->table_name . ' SET referral_parent = ' . $user_id . ', update_date = "' . date( "Y-m-d H:i:s" ) . '"  WHERE active = 1 AND user_id IN (' . implode( ',', $active_user_list ) . ')';
                $wpdb->query( $query );
            }
            $this->check_child_deactive_referral_user( $user_id );
            if ( count( $inactive_user_array ) > 0 ) {
                $query = 'UPDATE ' . $this->table_name . ' SET referral_parent = ' . $user_id . ', update_date = "' . date( "Y-m-d H:i:s" ) . '" WHERE active = 0 AND user_id IN (' . implode( ',', $inactive_user_array ) . ')';
                $wpdb->query( $query );
            }
            echo admin_url( 'admin.php?page=wc_referral&user_status=0&uid=' . $user_id );
            die;
        }

        public function check_child_deactive_referral_user( $user_id ) {
            global $wpdb, $inactive_user_array;
            $query = 'SELECT um.user_id FROM ' . $wpdb->usermeta . ' AS um JOIN ' . $this->table_name . ' AS ru ON ru.user_id = um.user_id WHERE ru.active = 0 AND um.meta_value = "' . $user_id . '" AND um.`meta_key` = "referral_parent"';
            $deactive_user_list = $wpdb->get_col( $query );
            if ( count( $deactive_user_list ) ) {
                foreach ( $deactive_user_list as $deactive_user ) {
                    $inactive_user_array[] = $deactive_user;
                    $this->check_child_deactive_referral_user( $deactive_user );
                }
            }
        }

        /**
         * Add new register fields for WooCommerce registration.
         *
         * @return string Register fields HTML.
         */
        public function referral_register_start_fields() {
            if ( isset( $_GET['ru'] ) && !isset( $_POST['referral_code'] ) && $_GET['ru'] != '' ) {
                $referral_email = $this->referral_user( 'referral_email', 'referral_code', sanitize_text_field( $_GET['ru'] ) );
                if ( $referral_email ) {
                    $_POST['email'] = $referral_email;
                }
            }
            echo self::render_template( 'front/register_form_start_fields.php' );
        }

        /*
         *    Add referral program form to register form
         */
        public function referral_register_fields() {
            //print_r($_POST);
            $referral_code = '';
            if ( isset( $_POST['referral_code'] ) ) {
                $referral_code = sanitize_text_field( $_POST['referral_code'] );
            } elseif ( isset( $_GET['ru'] ) ) {
                $referral_code = sanitize_text_field( $_GET['ru'] );
            } elseif ( isset( $_COOKIE['WMC_REFERRAL_CODE'] ) ) {
                $referral_code = sanitize_text_field( $_COOKIE['WMC_REFERRAL_CODE'] );
            }
            $data = array(
                'join_referral_program' => ( isset( $_POST['join_referral_program'] ) ? sanitize_text_field( $_POST['join_referral_program'] ) : (( $referral_code && !isset( $_POST['join_referral_program'] ) ? 0 : 2 )) ),
                'referral_email'        => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( $_POST['referral_email'] ) : '' ),
                'referral_code'         => $referral_code,
                'flag'                  => true,
            );
            $data = apply_filters( 'wmc_registation_referral_fields', $data );
            //print_r($data);
            if ( isset( $data['flag'] ) && $data['flag'] == true ) {
                echo self::render_template( 'front/register_form_end_fields.php', array(
                    'data' => $data,
                ) );
            }
        }

        // Newly added checkout fields 19-01-2018
        function wmc_override_checkout_fields( $wmcFields ) {
            $autoJoin = get_option( 'wmc_auto_register', 'no' );
            $arrOptions = array(
                '1' => __( 'I have the referral code and want to join referral program.', 'wmc' ),
                '2' => __( 'I don\'t have referral code or I lost it. But I wish to join referral program.', 'wmc' ),
                '3' => __( 'No, I don\'t want to be a part of referral program at this time.', 'wmc' ),
            );
            $referral_code = ( isset( $_COOKIE['WMC_REFERRAL_CODE'] ) ? sanitize_text_field( $_COOKIE['WMC_REFERRAL_CODE'] ) : '' );
            $arrReferralCode = array(
                'type'        => 'text',
                'label'       => __( 'Referral Code', 'wmc' ),
                'placeholder' => __( 'Enter referral code', 'wmc' ),
                'class'       => ( $referral_code ? array('form-row-wide') : array('form-row-wide', 'hide') ),
                'default'     => $referral_code,
            );
            if ( $autoJoin == 'yes' ) {
                $wmcFields['account']['join_referral_program'] = array(
                    'type'    => 'hidden',
                    'default' => "2",
                );
                $wmcFields['account']['termsandconditions'] = array(
                    'type'    => 'hidden',
                    'default' => "1",
                );
                $arrReferralCode['class'] = array('form-row-wide');
                $arrReferralCode['placeholder'] = __( 'Enter referral code if you have one', 'wmc' );
                $wmcFields['account']['referral_code'] = $arrReferralCode;
            } else {
                $wmcFields['account']['join_referral_stage_one'] = array(
                    'type'     => 'radio',
                    'required' => true,
                    'label'    => __( 'Do you want to join Referral Program?', 'wmc' ),
                    'class'    => array('form-row-wide'),
                    'options'  => array(
                        '2' => __( 'Yes', 'wmc' ),
                        '3' => __( 'No', 'wmc' ),
                    ),
                    'default'  => ( $referral_code ? 2 : '' ),
                );
                $wmcFields['account']['join_referral_stage_two'] = array(
                    'type'    => 'radio',
                    'label'   => __( 'Do you have Referral code?', 'wmc' ),
                    'class'   => ( $referral_code ? array('form-row-wide') : array('form-row-wide', 'hide') ),
                    'options' => array(
                        '1' => __( 'Yes', 'wmc' ),
                        '2' => __( 'No', 'wmc' ),
                    ),
                    'default' => ( $referral_code ? 1 : '' ),
                );
                $wmcFields['account']['join_referral_program'] = array(
                    'type'  => 'hidden',
                    'value' => 3,
                );
                /*$wmcFields['account']['join_referral_program']=array(
                      'type' => 'select',
                      'label' => __('Join Referral Program', 'wmc'),
                      'placeholder' => _x('Join Referral Program', 'placeholder', 'wmc'),
                      'class' => array('form-row-wide'),
                      'label_class' => array('hidden')
                  );*/
                //$wmcFields['account']['join_referral_program']['options']=$arrOptions;
                $wmcFields['account']['referral_code'] = $arrReferralCode;
                $wmcFields['account']['termsandconditions'] = array(
                    'type'        => 'checkbox',
                    'label'       => __( 'I\'ve read and agree to the referral program', 'wmc' ) . ' <a href="' . esc_url( get_permalink( get_option( 'wmc_terms_and_conditions', 0 ) ) ) . '" target="_blank">' . __( 'terms and conditions', 'wmc' ) . '</a>',
                    'class'       => ( $referral_code ? array('form-row-wide wpmlrp-checkbox') : array('form-row-wide wpmlrp-checkbox hide') ),
                    'label_class' => array(''),
                );
            }
            return $wmcFields;
        }

        function wmc_custom_checkout_field_process() {
            $guestCheckout = get_option( 'woocommerce_enable_guest_checkout' );
            $autoJoin = get_option( 'wmc_auto_register', 'no' );
            $validateReferral = false;
            if ( $guestCheckout == 'yes' && isset( $_POST['createaccount'] ) ) {
                $validateReferral = true;
            }
            if ( $guestCheckout == 'no' ) {
                $validateReferral = true;
            }
            if ( $validateReferral && isset( $_POST['join_referral_program'] ) ) {
                if ( $_POST['join_referral_program'] == 1 ) {
                    if ( isset( $_POST['referral_code'] ) && $_POST['referral_code'] == '' ) {
                        wc_add_notice( __( '<strong>The Referral code</strong> is required field.', 'wmc' ), 'error' );
                        return;
                    }
                    if ( !isset( $_POST['termsandconditions'] ) ) {
                        wc_add_notice( __( 'Please accept <strong>terms and conditions</strong> to join referral program.', 'wmc' ), 'error' );
                    }
                }
                if ( $_POST['join_referral_program'] == 2 ) {
                    if ( !isset( $_POST['termsandconditions'] ) && isset( $_POST['join_referral_stage_two'] ) ) {
                        wc_add_notice( __( 'Please accept <strong>terms and conditions</strong> to join referral program.', 'wmc' ), 'error' );
                    }
                    if ( !isset( $_POST['join_referral_stage_two'] ) && $autoJoin == 'no' ) {
                        wc_add_notice( __( '<strong>Do you have Referral code?</strong> is required field.', 'wmc' ), 'error' );
                    }
                }
            }
            if ( isset( $_POST['referral_code'] ) && $_POST['referral_code'] != '' ) {
                $parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( $_POST['referral_code'] ) );
                if ( !$parent_id ) {
                    wc_add_notice( __( 'There is no such referral code exist<strong>(' . sanitize_text_field( $_POST['referral_code'] ) . ')</strong> exist.', 'wmc' ), 'error' );
                }
            }
        }

        function referral_user_my_affiliate_panel() {
            echo $this->wmc_my_affiliates();
        }

        function referral_user_account_panel() {
            echo do_shortcode( '[wmc_my_referral_tab]' );
        }

        /* Shortcode to display Invite friends form*/
        public function wmc_my_referrals() {
            if ( is_user_logged_in() ) {
                $htmlBlock = '';
                $htmlBlock .= '<div class="referral_program_details">';
                $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
                if ( $check_user ) {
                    $htmlBlock .= $this->wmc_referral_stats_blocks();
                }
                $htmlBlock .= '<div class="referral_program_sections"><div class="referral_program_content">';
                $htmlBlock .= do_shortcode( '[wmc_invite_friends]', true );
                $htmlBlock .= '</div></div></div>';
                echo $htmlBlock;
            }
        }

        public function wmc_my_affiliates() {
            $htmlBlock = '';
            if ( is_user_logged_in() ) {
                $htmlBlock = '<div class="referral_program_details">';
                $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
                if ( $check_user ) {
                    $htmlBlock .= $this->wmc_referral_stats_blocks();
                    $htmlBlock .= '<div class="referral_program_sections" style="padding-top: 30px;"><div class="referral_program_content">';
                    $htmlBlock .= $this->wmcShowMyAffiliates();
                    $htmlBlock .= $this->referral_user_credit_info();
                    $htmlBlock .= '</div></div>';
                } else {
                    $htmlBlock .= '<p>' . __( 'Please join our Referral Program to access this page.', 'wmc' ) . '</p>';
                    $htmlBlock = apply_filters( 'wmc_my_affliates_join_programe_text', $htmlBlock );
                }
                $htmlBlock .= '</div>';
            }
            return $htmlBlock;
        }

        public function wmc_referral_stats_blocks() {
            $htmlBlock = '';
            if ( is_user_logged_in() ) {
                $htmlBlock .= '<div class="referral_program_overview referral_top_section">';
                $current_user_id = get_current_user_ID();
                $obj_referal_program = new Referal_Program();
                $obj_referal_users = new Referal_Users();
                $data = array(
                    'referral_code'    => $obj_referal_users->referral_user( 'referral_code', 'user_id', $current_user_id ),
                    'total_points'     => $obj_referal_program->available_credits( $current_user_id ),
                    'total_followers'  => $obj_referal_users->fnGetFollowersCount( $current_user_id ),
                    'total_withdraw'   => $obj_referal_program->total_withdraw_credit( $current_user_id ),
                    'total_earn_point' => $obj_referal_program->total_earn_credit( $current_user_id ),
                );
                add_filter( 'woocommerce_currency_symbol', 'wmc_remove_wc_currency_symbols', 99 );
                $link = add_query_arg( 'ru', $data['referral_code'], get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
                $htmlBlock .= '   <div class="referral_program_stats">
                                    <span class="referral_icon"></span>
                                    <span>' . __( 'Referral Code', 'wmc' ) . '</span>
                                    <span class="show_output">' . $data['referral_code'] . '</span>
                                    <a class="copy_referral_link" data-content="' . __( 'Copied', 'wmc' ) . '" href="' . $link . '">' . __( 'Copy your Referral Link.', 'wmc' ) . '</a>
                                </div>
                                <div class="referral_program_stats total_avilable_credit">
                                    <span class="total_credit_icon"></span>
                                    <span>' . apply_filters( 'wmc_total_credits_available', __( 'Total Credits Available', 'wmc' ) ) . '</span>
                                    <span class="show_output">' . wc_price( apply_filters( 'wmc_total_credits_amount', $data['total_points'] ) ) . '</span>
                                </div>
                                <div class="referral_program_stats">
                                    <span class="total_referral"></span>
                                    <span>' . __( 'Total Referrals', 'wmc' ) . '</span>
                                    <span class="show_output">' . $data['total_followers'] . '</span>
                                </div>';
                $htmlBlock = apply_filters( 'wmc_referral_tab_block', $htmlBlock, $data );
                $htmlBlock .= '</div>';
                remove_filter( 'woocommerce_currency_symbol', 'wmc_remove_wc_currency_symbols', 99 );
                $htmlBlock = apply_filters( 'wmc_referral_tabs', $htmlBlock, $data );
            }
            return $htmlBlock;
        }

        public function referral_user_invite_friends() {
            if ( is_user_logged_in() ) {
                global $invitation_error;
                $wmc_html = '';
                $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
                if ( $check_user ) {
                    $wmc_html .= '<div class="wmc-invite-friends">';
                    $wmc_html = apply_filters( 'wmc_referral_after_block', $wmc_html, $check_user );
                    $email = ( isset( $_POST['emails'] ) ? sanitize_text_field( $_POST['emails'] ) : '' );
                    $wmc_html .= '<p class="hide">
                    <a href="#" class="button btn-invite-friends">' . __( 'Invite Friends', 'wmc' ) . '</a>
                    </p>
                    <div id="dialog-invitation-form">
                    <h2>' . __( 'Invite your friends', 'wmc' ) . '</h2>       
                    <h4>' . __( 'Send an Invitation to your friend by adding his/her e-mail address. (If you want to invite sevaral at the same time, just add a comma in between.)', 'wmc' ) . '</h4>
                    <form method="post">
                    <table class="shop_table shop_table_responsive">
                    <tr>
                    <td>
                    <input type="text" name="emails"  class="input-text" value="' . $email . '" placeholder="Ex. test@demo.com, test2@demo.com" />
                    </td>
                    <td width="105px">    
                    <input type="submit" class="button btn-send-invitation" value="' . __( 'Invite', 'wmc' ) . '" />
                    <input type="hidden" name="action" value="send_invitations" />
                    </td>
                    </tr>
                    </table>
                    </form>
                    </div>';
                    $wmc_html .= '</div>';
                    $bannars = $this->wmcShowBanners();
                    $wmc_html .= $bannars;
                } else {
                    $referal_code = '';
                    if ( isset( $_POST['referral_code'] ) ) {
                        $referal_code = sanitize_text_field( $_POST['referral_code'] );
                    } elseif ( isset( $_COOKIE['WMC_REFERRAL_CODE'] ) ) {
                        $referal_code = sanitize_text_field( $_COOKIE['WMC_REFERRAL_CODE'] );
                    }
                    $data = array(
                        'join_referral_program' => ( isset( $_POST['join_referral_program'] ) ? sanitize_text_field( $_POST['join_referral_program'] ) : 1 ),
                        'referral_email'        => ( isset( $_POST['referral_email'] ) ? sanitize_email( $_POST['referral_email'] ) : '' ),
                        'referral_code'         => ( isset( $_POST['referral_code'] ) ? sanitize_text_field( $_POST['referral_code'] ) : '' ),
                        'nonce'                 => wp_create_nonce( 'referral_program' ),
                    );
                    $wmc_html .= self::render_template( 'front/join-form.php', array(
                        'data' => $data,
                    ) );
                    $wmc_html = apply_filters( 'wmc_join_form_front', $wmc_html );
                }
                return $wmc_html;
            }
            return;
        }

        function wmcGetTinyUrl( $url ) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt( $ch, CURLOPT_URL, 'https://tinyurl.com/api-create.php?url=' . $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
            $data = curl_exec( $ch );
            curl_close( $ch );
            return $data;
        }

        function wmcShowBanners() {
            global $wp;
            $banners = get_option( 'wmc-default-banners' );
            $allBanners = get_posts( array(
                'post_type'   => 'wmc-banner',
                'numberposts' => -1,
                'post__in'    => $banners,
                'order'       => 'ASC',
                'orderby'     => 'title',
            ) );
            $i = 0;
            $arrBanners = get_option( 'wmc-pre-banners' );
            $firstBanner = array();
            $referralCode = __( 'Referral Code : ', 'wmc' );
            $code = '';
            if ( !function_exists( 'imagecreatefrompng' ) ) {
                return '<div id="wmc-social-media"><h2>' . __( 'Share on Social Media', 'wmc' ) . '</h2><h4><strong>' . __( 'Please contact site administrator for active social media sharing functionality.', 'wmc' ) . '</strong></h4></div>';
            }
            $current_user_id = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
            if ( $current_user_id ) {
                $code = $this->referral_user( 'referral_code', 'user_id', $current_user_id );
                $referralCode .= $code;
            }
            $wmc_html = '<div id="wmc-social-media">
            <h2>' . __( 'Share on Social Media', 'wmc' ) . '</h2>
            <h4>' . __( 'Select a banner, write a title and a description, then click the icon of the social media you want to share on.', 'wmc' ) . '</h4>
            <div class="wmc-banners">
            <div class="wmc-banner-list">
            <label>' . __( 'Select Banner', 'wmc' ) . ' </label>
            <select data-loader="' . WMC_URL . 'images/loadingAnimation.gif">';
            $firstBanner = array(
                'attachId' => '',
                'thumbUrl' => '',
                'path'     => '',
                'title'    => '',
                'desc'     => '',
                'url'      => '',
                'id'       => '',
            );
            foreach ( $allBanners as $banner ) {
                $checked = '';
                $presetBanner = 'no';
                if ( has_post_thumbnail( $banner->ID ) ) {
                    $banner_thumbnail_id = get_post_thumbnail_id( $banner->ID );
                    $banner_thumbnail_url = wp_get_attachment_url( $banner_thumbnail_id );
                    $bannerPath = get_attached_file( $banner_thumbnail_id );
                    //$sharemeURL=$this->wmcGetTinyUrl(site_url().'wmcbanner/shareme'.$current_user_id.'_'.$banner_thumbnail_id);
                    $qURL = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
                    $pageURL = add_query_arg( 'wmcbanner', $code . '-' . $current_user_id . '-' . $banner->ID . '-' . $banner_thumbnail_id, $qURL );
                    $sharemeURL = $this->wmcGetTinyUrl( $pageURL );
                    // $sharemeURL=$pageURL;
                    if ( $i < 1 ) {
                        $firstBanner['attachId'] = $banner_thumbnail_id;
                        $firstBanner['thumbUrl'] = $banner_thumbnail_url;
                        $firstBanner['path'] = $bannerPath;
                        $checked = 'checked="checked"';
                        $firstBanner['title'] = $banner->post_title;
                        $firstBanner['desc'] = $banner->post_excerpt;
                        $firstBanner['url'] = $sharemeURL;
                        //$firstBanner['url']=add_query_arg( array('ru' => $code),$firstBanner['url'] );
                        $firstBanner['id'] = $banner->ID;
                    }
                    $wmc_html .= '<option data-code="' . $code . '" data-url="' . $sharemeURL . '"  data-attachid="' . $banner_thumbnail_id . '" value="' . $banner->ID . '" data-title="' . $banner->post_title . '" data-desc="' . $banner->post_excerpt . '" data-image="' . $banner_thumbnail_url . '">' . $banner->post_title . '</option>';
                    $i++;
                }
            }
            $wmc_html .= '</select></div>
            <div class="wmc-banner-preview">';
            $imageURL = $this->wmcGetTinyUrl( $firstBanner['thumbUrl'] );
            //$imageURL= $firstBanner['thumbUrl'];
            if ( count( $firstBanner ) > 0 && $firstBanner['path'] != '' ) {
                $imageURL = $this->writeTextonImage( $referralCode, $firstBanner['path'], $current_user_id );
                $imageURL = $this->wmcGetTinyUrl( $imageURL );
                //$arrPlaceholders=array('REFERRAL_CODE'=>$code,'ATTACH_ID'=>$firstBanner['attachId'],'BANNER_TITLE'=>$firstBanner['title'],'BANNER_DESC'=>$firstBanner['desc'],'BANNER_IMAGE'=>$imageURL);
                // $this->fnChangeShareContent($arrPlaceholders);
                $wmc_html .= '<img  src="' . $imageURL . '" alt="Promotional Banner">';
            }
            $wmc_html .= '</div>';
            $wmc_html .= '<div><p class="form-row form-row-wide"><label for="wmcBannerTitle" class="">' . __( 'Custom Banner Title', 'wmc' ) . '</label><input type="text" class="input-text " name="wmcBannerTitle" id="wmcBannerTitle" placeholder="' . __( 'Banner Title', 'wmc' ) . '" value="' . $firstBanner['title'] . '"></p><p class="form-row form-row-wide"><label for="wmcBannerDescription" class="">' . __( 'Custom Banner Description', 'wmc' ) . '</label><textarea class="input-text" name="wmcBannerDescription" id="wmcBannerDescription" placeholder="' . __( 'Banner Description', 'wmc' ) . '">' . $firstBanner['desc'] . '</textarea></p></div>
            </div>
            <div class="wmcShareWrapper" data-url="' . $firstBanner['url'] . '" data-title="' . $firstBanner['title'] . '" data-image="' . $imageURL . '" data-description="' . $firstBanner['desc'] . '">
            <span id="share42">
            <a rel="nofollow" class="wmc-button-fb"  href="#" data-count="fb"  title="' . __( 'Share on Facebook', 'wmc' ) . '" target="_blank"></a>
            <!--a rel="nofollow" class="wmc-button-gplus"  href="#" data-count="gplus"  title="' . __( 'Share on Google+', 'wmc' ) . '" target="_blank"></a-->
            <a rel="nofollow" class="wmc-button-lnkd"  href="#" data-count="lnkd"  title="' . __( 'Share on Linkedin', 'wmc' ) . '" target="_blank"></a>
            <a rel="nofollow" class="wmc-button-pin"  href="#" data-count="pin" title="' . __( 'Pin It', 'wmc' ) . '" target="_blank"></a>                
            <a rel="nofollow" class="wmc-button-twi"  href="#" data-count="twi" title="' . __( 'Share on Twitter', 'wmc' ) . '" target="_blank"></a>                
            <a rel="nofollow" class="wmc-button-whatsup" href="#" data-account="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '" data-ru="' . $code . '" data-share="' . md5( 'whatsup' ) . '" data-count="whatsup" title="' . __( 'Share on What\'s up', 'wmc' ) . '"></a>
            </span>
            </div>';
            return $wmc_html .= '</div>';
        }

        function fnBannerMetaInformation() {
            global $wpdb;
            if ( is_single() ) {
                $post = get_post();
                if ( $post->post_type == 'wmc-banner' ) {
                    $post_thumbnail_id = get_post_thumbnail_id( $post->ID );
                    $imageURL = wp_get_attachment_image_src( $post_thumbnail_id, $size );
                    $bannerPath = get_attached_file( $post_thumbnail_id );
                    $arrBanners = get_option( 'wmc-pre-banners' );
                    if ( in_array( $post->ID, $arrBanners ) ) {
                        global $current_user;
                        get_currentuserinfo();
                        if ( $current_user->ID != 0 ) {
                            $current_user_id = $current_user->ID;
                            $referralCode = __( 'Referral Code : ', 'wmc' );
                            $code = $wpdb->get_var( 'SELECT referral_code FROM ' . $this->table_name . ' WHERE user_id = "' . $current_user_id . '"' );
                            $referralCode .= $code;
                            $this->writeTextonImage( $referralCode, $bannerPath, $current_user_id );
                            $imageURL = WMC_URL . 'images/userbanners/banner-' . $current_user_id . '.jpg';
                            $metaInfo = '<script type="text/javascript">
                            var FBAPP_ID = "1696793383871229";
                            </script><meta property="og:type" content="article"><meta property="og:title" content="' . $post->post_title . '"><meta property="fb:app_id" content="1696793383871229" >
                            <meta property="og:url" content="' . get_permalink( $post->ID ) . '" >
                            <meta property="og:description" content="' . $post->post_excerpt . '" >
                            <meta property="og:image" content="' . $imageURL . '" >
                            <meta property="og:image:width" content="500" > 
                            <meta property="og:image:height" content="300" > 
                            <meta name="twitter:card" content="summary_large_image" >
                            <meta name="twitter:title" content="' . $post->post_title . '" >
                            <meta name="twitter:url" content="' . get_permalink( $post->ID ) . '" >
                            <meta name="twitter:description" content="' . $post->post_excerpt . '" >
                            <meta name="twitter:image" content="' . $imageURL . '" >
                            <meta itemprop="name" content="' . $post->post_title . '">
                            <meta itemprop="description" content="' . $post->post_excerpt . '">
                            <meta itemprop="image" content="' . $imageURL . '">';
                            echo $metaInfo;
                        }
                    }
                }
            }
        }

        function fnModifyPostThumbnail(
            $html,
            $post_id,
            $post_thumbnail_id,
            $size,
            $attr
        ) {
            if ( has_post_thumbnail() && is_user_logged_in() ) {
                $postType = get_post_type();
                $current_user_id = get_current_user_id();
                if ( $postType == 'wmc-banner' ) {
                    $imageURL = WMC_URL . 'images/userbanners/banner-' . $current_user_id . '.jpg';
                    $doc = new DOMDocument();
                    $doc->loadHTML( $html );
                    $tags = $doc->getElementsByTagName( 'img' );
                    foreach ( $tags as $tag ) {
                        $old_src = $tag->getAttribute( 'src' );
                        $tag->setAttribute( 'src', $imageURL );
                        $tag->setAttribute( 'srcset', $imageURL );
                    }
                    $html = $doc->saveHTML();
                }
            }
            return $html;
        }

        function fnFilterTheContent( $content ) {
            if ( is_single() && in_the_loop() && is_main_query() ) {
                $link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
                if ( isset( $_GET['ru'] ) && $_GET['ru'] != '' ) {
                    $link = add_query_arg( array(
                        'ru' => $_GET['ru'],
                    ), $link );
                    $content .= '<div class="wmc-account-link"><a href="' . $link . '" title="' . __( 'Login / Register', 'wmc' ) . '">' . __( 'Login / Register', 'wmc' ) . '</a></div>';
                }
            }
            return $content;
        }

        function fnCheckAndGetImageType( $path ) {
            $path_parts = pathinfo( $path );
            $mimeType = $path_parts['extension'];
            $imgObj = '';
            if ( isset( $mimeType ) && $mimeType != '' && function_exists( 'imagecreatefrompng' ) ) {
                switch ( $mimeType ) {
                    case 'png':
                        $imgObj = imagecreatefrompng( $path );
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $imgObj = imagecreatefromjpeg( $path );
                        break;
                    case 'gif':
                        $imgObj = imagecreatefromgif( $path );
                        break;
                    default:
                        die( 'Invalid image type' );
                }
            }
            return array(
                'img'  => $imgObj,
                'type' => $mimeType,
            );
        }

        function fnCreateImageByType( $mimeType, $imgObj, $fileName ) {
            if ( !is_dir( WMC_DIR . 'images/userbanners' ) ) {
                mkdir( WMC_DIR . 'images/userbanners' );
            }
            $imagePath = WMC_DIR . 'images/userbanners/' . $fileName;
            $imageURL = WMC_URL . 'images/userbanners/' . $fileName;
            $extension = '';
            switch ( $mimeType ) {
                case 'png':
                    imagepng( $imgObj, $imagePath . '.png', 9 );
                    $imageURL .= '.png';
                    $extension = '.png';
                    break;
                case 'jpg':
                case 'jpeg':
                    imagejpeg( $imgObj, $imagePath . '.jpeg', 100 );
                    $imageURL .= '.jpeg';
                    $extension = '.jpeg';
                    break;
                case 'gif':
                    imagegif( $imgObj, $imagePath . '.gif', 100 );
                    $imageURL .= '.gif';
                    $extension = '.gif';
                    break;
                default:
                    die( 'Invalid image type' );
            }
            if ( is_resource( $imgObj ) ) {
                imagedestroy( $imgObj );
            }
            if ( $extension && !file_exists( $imagePath . $extension ) ) {
                $image = wp_get_image_editor( $imagePath . $extension );
                if ( !is_wp_error( $image ) ) {
                    $image->resize( NULL, 300, false );
                    $image->save( $imagePath . $extension );
                }
            }
            return $imageURL;
        }

        function writeTextonImage(
            $code,
            $path,
            $userId,
            $attachId = 0
        ) {
            $imgArr = $this->fnCheckAndGetImageType( $path );
            $imgURL = '';
            if ( $imgArr['img'] && $imgArr['img'] != '' ) {
                $color = imagecolorallocate(
                    $imgArr['img'],
                    0xff,
                    0xff,
                    0xff
                );
                $width = imagesx( $imgArr['img'] );
                // it will store width of image
                $height = imagesy( $imgArr['img'] );
                //it will store height of image
                $fontsize = round( 48 * (15.87 * $height) / 100 / 100 );
                // size of font
                $font = WMC_DIR . 'css/roboto-condensed-regular.ttf';
                $txtBoxWidth = $width - 20;
                do {
                    $bbox = imagettfbbox(
                        $fontsize,
                        0,
                        $font,
                        $code
                    );
                    $boxWidth = abs( $bbox[4] - $bbox[0] );
                    $x = ($txtBoxWidth - $boxWidth) / 2;
                    $fontsize--;
                } while ( $boxWidth > $txtBoxWidth );
                $topPos = 100 * $height / 630 - abs( $bbox[5] - $bbox[1] ) / 2;
                imagettftext(
                    $imgArr['img'],
                    $fontsize + 1,
                    0,
                    intval( $x ),
                    intval( $topPos ),
                    $color,
                    $font,
                    $code
                );
                $uRL = site_url();
                //$topPos= 600;
                $boxWidth = $width;
                do {
                    $bbox2 = imagettfbbox(
                        $fontsize,
                        0,
                        $font,
                        $uRL
                    );
                    $boxWidth = abs( $bbox2[4] - $bbox2[0] );
                    $x = ($txtBoxWidth - $boxWidth) / 2;
                    $fontsize--;
                } while ( $boxWidth > $txtBoxWidth );
                $topPos = $height - abs( $bbox2[5] - $bbox2[1] ) / 2;
                imagettftext(
                    $imgArr['img'],
                    $fontsize + 1,
                    0,
                    $x,
                    $topPos,
                    $color,
                    $font,
                    $uRL
                );
                $imgURL = $this->fnCreateImageByType( $imgArr['type'], $imgArr['img'], 'banner-' . $userId . $attachId );
                unset($color);
            }
            return $imgURL;
        }

        function fnChangeShareContent() {
            global $wp;
            $current_url = home_url( add_query_arg( array(), $wp->request ) );
            $queryParam = get_query_var( 'wmcbanner' );
            if ( $queryParam != '' ) {
                $arrParam = explode( '-', $queryParam );
                $siteURL = site_url();
                $link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
                $url = get_permalink( $arrParam[2] );
                $link = add_query_arg( 'ru', $arrParam[0], $link );
                $bannerImage = '';
                $referralURL = ( isset( $_SERVER["HTTP_REFERER"] ) ? $_SERVER["HTTP_REFERER"] : '' );
                if ( $referralURL != "" ) {
                    $arrURL = parse_url( $referralURL );
                    $arrHomeURL = parse_url( $siteURL );
                    if ( $arrURL["host"] !== $arrHomeURL["host"] ) {
                        header( "Location: " . $link );
                        exit;
                    }
                }
                $referralCode = __( 'Referral Code : ', 'wmc' ) . $arrParam[0];
                $userId = $arrParam[1];
                $wmcPost = get_post( $arrParam[2] );
                $wmcTitle = '';
                $wmcDesc = '';
                $arrCustomTitles = get_transient( 'wmc_banner_' . $userId . '_' . $arrParam[3] );
                if ( $arrCustomTitles ) {
                    $wmcTitle = $arrCustomTitles['title'];
                    $wmcDesc = $arrCustomTitles['desc'];
                    $bannerImage = $arrCustomTitles['imageURL'];
                }
                if ( !$bannerImage ) {
                    $bannerPath = get_attached_file( $arrParam[3] );
                    $arrPreBanners = get_option( 'wmc-pre-banners' );
                    $bannerImage = wp_get_attachment_url( $arrParam[3] );
                    $bannerImage = $this->writeTextonImage( $referralCode, $bannerPath, $userId );
                }
                $wmcTitle = ( $wmcTitle == '' ? $wmcPost->post_title : $wmcTitle );
                $wmcDesc = ( $wmcDesc == '' ? $wmcPost->post_excerpt : $wmcDesc );
                $htmlContents = '<!doctype html><html lang="en-US"><head><meta property="og:type" content="article"><meta property="og:title" content="' . $wmcTitle . '"><meta property="fb:app_id" content="1696793383871229" ><meta property="og:description" content="' . $wmcDesc . '" ><meta property="og:image" content="' . $bannerImage . '" ><meta property="og:image:width" content="500" > <meta property="og:image:height" content="300" > <meta name="twitter:card" content="summary" ><meta name="twitter:title" content="' . $wmcTitle . '" ><meta name="twitter:description" content="' . $wmcDesc . '" ><meta name="twitter:image" content="' . $bannerImage . '" ><meta itemprop="name" content="' . $wmcTitle . '"><meta itemprop="description" content="' . $wmcDesc . '"><meta itemprop="image" content="' . $bannerImage . '"><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"><meta name="Description" content="' . $wmcDesc . '"><meta name="title" content="' . $wmcTitle . '">
                <title>' . $wmcTitle . ' &#8211;  ' . get_bloginfo( 'name' ) . '</title></head><body><h1>' . $wmcTitle . '</h1><p><img src="' . $bannerImage . '" alt="' . $wmcTitle . '">' . $wmcDesc . '</p><script type="text/javascript">
                window.fbAsyncInit = function() {
                window.FB.init({
                appId            : \'1696793383871229\',
                autoLogAppEvents : true,
                xfbml            : true,
                version          : \'v2.11\'
                });
                };

                (function(d, s, id){
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
                }(document, \'script\', \'facebook-jssdk\'));
                if(window.location.search.indexOf("facebook_refresh") >= 0)
                {
                //Feature check browsers for support
                if(document.addEventListener && window.XMLHttpRequest && document.querySelector)
                {
                //DOM is ready
                document.addEventListener("DOMContentLoaded", function() {
                window.FB.login(function(response) { 
                var httpRequest = new XMLHttpRequest();
                httpRequest.open("POST", "https://graph.facebook.com?access_token="+response.authResponse.accessToken, true);

                httpRequest.onreadystatechange = function () {
                if (httpRequest.readyState == 4) { console.log("httpRequest.responseText", httpRequest.responseText); }
                };

                //Default URL to send to Facebook
                var url = window.location;

                //og:url element
                var og_url = document.querySelector("meta[property=\'og:url\']");
                //var og_url = window.location.href;

                //Check if og:url element is present on page
                if(og_url != null)
                {
                //Get the content attribute value of og:url
                var og_url_value = og_url.getAttribute("content");

                //If og:url content attribute isn\'t empty
                if(og_url_value != "")
                {
                url = og_url_value;
                } else {
                console.warn(\'<meta property="og:url" content=""> is empty. Falling back to window.location\');
                }               
                } else {
                console.warn(\'<meta property="og:url" content=""> is missing. Falling back to window.location\');
                } 

                //Send AJAX
                httpRequest.send("scrape=true&id=" + encodeURIComponent(url));
                }, {perms:\'read_stream,publish_stream,offline_access\'});


                });
                } else {
                console.warn("Your browser doesn\'t support one of the following: document.addEventListener && window.XMLHttpRequest && document.querySelector");
                }
                }</script></body></html>';
                echo $htmlContents;
                die;
            }
        }

        function wmcCreditLogPagination() {
            $response = array();
            $pageno = ( isset( $_POST['pageno'] ) ? $_POST['pageno'] : '' );
            if ( !empty( $pageno ) ) {
                $current_user_id = get_current_user_id();
                $obj_referal_program = new Referal_Program();
                $data = array(
                    'records' => $obj_referal_program->select_all( 10, $pageno, $current_user_id ),
                );
                $all_items = $obj_referal_program->getall_count( 0, 1, $current_user_id );
                $total_items = $all_items[0]->records_count;
                if ( count( $data['records'] ) > 0 ) {
                    $wmc_html_credit = '';
                    $wmc_html_credit .= '<tr>
                        <!--th>' . __( 'Order', 'wmc' ) . '</th-->
                        <th>' . __( 'Date', 'wmc' ) . '</th>
                        <th>' . __( 'Note', 'wmc' ) . '</th>
                        </tr>';
                    foreach ( $data['records'] as $row ) {
                        $note = '';
                        $order = wc_get_order( $row['order_id'] );
                        if ( !is_bool( $order ) && $row['credits'] > 0 ) {
                            $credits = wc_price( apply_filters( 'wmc_total_credits_amount', $row['credits'] ) );
                            if ( $order->get_user_id() == $row['user_id'] ) {
                                if ( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ) {
                                    $note = sprintf( apply_filters( 'wmc_store_refund_credits', __( '%s Store credit is refund for order %s.', 'wmc' ) ), $credits, '#' . $row['order_id'] );
                                } else {
                                    $note = sprintf( apply_filters( 'wmc_store_earned_credits', __( '%s Store credit is earned from order %s.', 'wmc' ) ), $credits, '#' . $row['order_id'] );
                                }
                            } else {
                                $note = sprintf(
                                    apply_filters( 'wmc_store_earned_credits_by_referral', __( '%s Store credit is earned through referral user ( %s order %s )  ', 'wmc' ) ),
                                    $credits,
                                    get_user_meta( $order->get_user_id(), 'first_name', true ) . ' ' . get_user_meta( $order->get_user_id(), 'last_name', true ),
                                    '#' . $row['order_id']
                                );
                            }
                        }
                        if ( !is_bool( $order ) && $row['redeems'] > 0 ) {
                            $redeems = wc_price( apply_filters( 'wmc_total_redeems_amount', $row['redeems'] ) );
                            if ( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ) {
                                $note = sprintf( apply_filters( 'wmc_store_refund_credits', __( '%s Store credit is refund for order %s.', 'wmc' ) ), $redeems, '#' . $row['order_id'] );
                            } else {
                                if ( $row['order_id'] ) {
                                    $note = sprintf( apply_filters( 'wmc_store_used_credits', __( '%s Store credit is used in order %s.', 'wmc' ) ), $redeems, '#' . $row['order_id'] );
                                } else {
                                    $note = sprintf( apply_filters( 'wmc_store_expired_credits', __( '%s Store credit is expired.', 'wmc' ) ), $redeems );
                                }
                            }
                        }
                        if ( $row['order_id'] == 0 ) {
                            $credits = wc_price( apply_filters( 'wmc_total_credits_amount', $row['credits'] ) );
                            $note = sprintf( __( 'You have %s credits for registration to the site.', 'wmc' ), $credits );
                        }
                        $note = apply_filters( 'wmc_credit_logs_notes', $note, $row );
                        $wmc_html_credit .= '<tr>
                        <!--td><a htref="">#' . $row['order_id'] . '</a></td-->
                        <td data-title="' . __( 'Date', 'wmc' ) . '">' . date_i18n( 'M d, Y', strtotime( $row['date'] ) ) . '</td>
                        <td data-title="' . __( 'Note', 'wmc' ) . '">' . $note . '</td>
                        </tr>';
                    }
                    $page = $pageno;
                    $page = ( $page == 0 ? 1 : $page );
                    $recordsPerPage = 10;
                    $start = ($page - 1) * $recordsPerPage;
                    $adjacents = 2;
                    $prev = $page - 1;
                    $next = $page + 1;
                    $lastpage = ceil( $total_items / $recordsPerPage );
                    $lpm1 = $lastpage - 1;
                    $pagination = "";
                    if ( $lastpage > 1 ) {
                        //$pagination .= "<div class='pagination'>";
                        if ( $page > 1 ) {
                            $pagination .= "<button class='page-link button prev' data-page='" . $prev . "' >&laquo; Previous</button>";
                        } else {
                            $pagination .= "<button class='disabled prev'>&laquo; Previous</button>";
                        }
                        if ( $lastpage < 7 + $adjacents * 2 ) {
                            for ($counter = 1; $counter <= $lastpage; $counter++) {
                                if ( $counter == $page ) {
                                    $pagination .= "<button class='current button'>{$counter}</button>";
                                } else {
                                    $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                }
                            }
                        } elseif ( $lastpage > 5 + $adjacents * 2 ) {
                            if ( $page < 1 + $adjacents * 2 ) {
                                for ($counter = 1; $counter < 2 + $adjacents * 2; $counter++) {
                                    if ( $counter == $page ) {
                                        $pagination .= "<button class='current button'>{$counter}</button>";
                                    } else {
                                        $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                    }
                                }
                                $pagination .= "...";
                                $pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
                                $pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
                            } elseif ( $lastpage - $adjacents * 2 > $page && $page > $adjacents * 2 ) {
                                $pagination .= "<button class='page-link button' data-page='1' >1</button>";
                                $pagination .= "<button class='page-link button' data-page='2' >2</button>";
                                $pagination .= "...";
                                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                                    if ( $counter == $page ) {
                                        $pagination .= "<button class='current button'>{$counter}</button>";
                                    } else {
                                        $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                    }
                                }
                                $pagination .= "..";
                                $pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
                                $pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
                            } else {
                                $pagination .= "<button class='page-link button' data-page='1' >1</button>";
                                $pagination .= "<button class='page-link button' data-page='2' >2</button>";
                                $pagination .= "..";
                                for ($counter = $lastpage - (2 + $adjacents * 2); $counter <= $lastpage; $counter++) {
                                    if ( $counter == $page ) {
                                        $pagination .= "<button class='current button'>{$counter}</button>";
                                    } else {
                                        $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                    }
                                }
                            }
                        }
                        if ( $page < $counter - 1 ) {
                            $pagination .= "<button class='page-link button next' data-page='" . $next . "' >Next &raquo;</button>";
                        } else {
                            $pagination .= "<button class='disabled next'>Next &raquo;</button>";
                        }
                        //$pagination.= "</div>";
                    }
                } else {
                    $wmc_html_credit .= '<p class="help">' . __( 'No records found.', 'wmc' ) . '</p>';
                }
            }
            $response['data'] = $wmc_html_credit;
            $response['pagination'] = $pagination;
            echo json_encode( $response );
            exit;
        }

        function wmcChangeBanner() {
            global $wpdb, $isTransientBanner;
            $code = $wpdb->get_var( 'SELECT referral_code FROM ' . $wpdb->prefix . 'referal_users WHERE user_id = "' . get_current_user_id() . '"' );
            $userId = get_current_user_id();
            $response = array();
            $bTitle = ( isset( $_POST['bTitle'] ) ? $_POST['bTitle'] : '' );
            $bDesc = ( isset( $_POST['bDesc'] ) ? $_POST['bDesc'] : '' );
            $attachId = ( isset( $_POST['attachId'] ) && $_POST['attachId'] != '' ? $_POST['attachId'] : 0 );
            $imgURL = '';
            if ( $attachId ) {
                $bannerPath = get_attached_file( $_POST['attachId'] );
                $referralCode = __( 'Referral Code : ', 'wmc' );
                if ( $code ) {
                    $referralCode .= $code;
                }
                $imgURL = $this->writeTextonImage(
                    $referralCode,
                    $bannerPath,
                    $userId,
                    $attachId
                );
                $response['type'] = 'success';
            } else {
                $response['type'] = 'failed';
            }
            $response['imageURL'] = $this->wmcGetTinyUrl( $imgURL . '?t=' . time() );
            if ( $isTransientBanner ) {
                $isTransientBanner = false;
                return $imgURL;
            }
            echo json_encode( $response );
            exit;
        }

        function wmcSaveTransientBanner() {
            global $isTransientBanner;
            $isTransientBanner = true;
            $userId = get_current_user_id();
            $response = array();
            $bTitle = ( isset( $_POST['bTitle'] ) ? $_POST['bTitle'] : '' );
            $bDesc = ( isset( $_POST['bDesc'] ) ? $_POST['bDesc'] : '' );
            $attachId = ( isset( $_POST['attachId'] ) && $_POST['attachId'] != '' ? $_POST['attachId'] : 0 );
            if ( $attachId ) {
                $imgURL = $this->wmcChangeBanner();
                set_transient( 'wmc_banner_' . $userId . '_' . $attachId, array(
                    'title'    => $bTitle,
                    'desc'     => $bDesc,
                    'imageURL' => $imgURL,
                ), 60 * 60 * 1 );
                $response['type'] = 'success';
            } else {
                $response['type'] = 'failed';
            }
            echo json_encode( $response );
            exit;
        }

        /* Shortcode to display Credit points info */
        /* Show the logged in users affiliate user list */
        function wmcRewrite() {
            add_rewrite_rule( '^wmcbanner$', 'index.php?wmcbanner=$1', 'top' );
            if ( get_transient( 'vpt_flush' ) ) {
                delete_transient( 'vpt_flush' );
                flush_rewrite_rules();
            }
        }

        /* Show the logged in users affiliate user list */
        function wmcShowMyAffiliates() {
            global $wpdb;
            $wmcHtml = '';
            $url_filter = site_url();
            $myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
            if ( is_user_logged_in() && in_the_loop() && is_page( $myaccount_page ) ) {
                $url_filter = get_permalink( $myaccount_page ) . "my-affliates/";
            }
            $active_sel = '';
            if ( isset( $_GET['filter'] ) ) {
                $active_sel = $_GET['filter'];
            }
            $active_order = '';
            if ( isset( $_GET['orderby'] ) ) {
                $active_order = $_GET['orderby'];
            }
            if ( is_user_logged_in() ) {
                $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
                if ( $check_user ) {
                    $myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
                    $current_user_id = get_current_user_id();
                    $obj_referal_program = new Referal_Program();
                    $obj_referal_users = new Referal_Users();
                    $data = array(
                        'referral_code'   => $obj_referal_users->referral_user( 'referral_code', 'user_id', $current_user_id ),
                        'total_points'    => $obj_referal_program->available_credits( $current_user_id ),
                        'total_followers' => $obj_referal_users->fnGetFollowersCount( $current_user_id ),
                    );
                    $active_panel = 'referral-share-invite';
                    if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'referral-affiliates' ) {
                        $active_panel = 'referral-affiliates';
                        $data['content'] = $this->wmcShowMyAffiliates();
                    } else {
                        $data['content'] = do_shortcode( '[wmc_invite_friends]', true );
                    }
                    $data['page_url'] = get_permalink( $myaccount_page );
                    $data['active_panel'] = $active_panel;
                }
            }
            $arrBreadCrumb = array();
            $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
            if ( $check_user ) {
                $get_min_date = $wpdb->get_var( "SELECT MIN(join_date) FROM " . $wpdb->prefix . "referal_users where user_id=" . get_current_user_id() );
                $get_min_date = date( 'Y-m-01 H:i:s', strtotime( $get_min_date ) );
                $date_ranges = $this->dateRange(
                    $get_min_date,
                    date( 'Y-m-d H:i:s' ),
                    '+1 month',
                    'Y-m-d'
                );
                $this_month = date( 'Y-m-d', strtotime( 'first day of this month' ) );
                if ( !in_array( $this_month, $date_ranges ) ) {
                    array_push( $date_ranges, $this_month );
                }
                $wmcHtml .= '<div class="wmc-show-affiliates">';
                $wmcHtml .= '<h2>' . __( 'My Affiliates', 'wmc' ) . '</h2>';
                $wmcHtml .= '<div class="affliate-filter"><div class="filter_date"><label>' . __( 'Filter by', 'wmc' ) . '</label>';
                $wmcHtml .= '<select id="my-affilicate_filters" data_url="' . $url_filter . '"><option value="">' . __( 'All', 'wmc' ) . '</option>';
                foreach ( $date_ranges as $key => $value ) {
                    $val_date_formate = date_format( date_create( $value ), "y-m-d" );
                    $wmcHtml .= '<option value="' . $val_date_formate . '" ' . (( isset( $_GET['filter'] ) && $_GET['filter'] == $val_date_formate ? 'selected' : '' )) . ' >' . date_format( date_create( $value ), "M-Y" ) . '</option>';
                }
                $wmcHtml .= '</select></div>';
                $wmcHtml .= '<div class="filter_order"><label> Order by </label><select name="orderby" id="order_by_filter">';
                $wmcHtml .= '<option value="asc" ' . (( $active_order == 'asc' ? 'selected' : '' )) . ' >' . __( 'Asc', 'wmc' ) . '</option><option value="desc" ' . (( $active_order == 'desc' ? 'selected' : '' )) . '>' . __( 'Desc', 'wmc' ) . '</option>';
                $wmcHtml .= '</select></div></div>';
                $wmcHtml .= '<table class="shop_table shop_table_responsive">';
                $wmcHtml .= '<thead><tr><th align="center">' . __( 'Show/Hide', 'wmc' ) . '</th><th align="center">' . __( 'Referral Code', 'wmc' ) . '</th><th align="center">' . __( 'Name', 'wmc' ) . '</th><th align="right">' . __( 'Referrals', 'wmc' ) . '</th><!--th>' . __( 'Affiliates Credit', 'wmc' ) . '</th--><th align="center">' . __( 'Join Date', 'wmc' ) . '</th></tr></thead>';
                $returnHtml = $this->wmcGetAffliateUsersList( $check_user );
                $wmcHtml .= $returnHtml;
                if ( $returnHtml == '' ) {
                    $wmcHtml .= '<tr class="affliate-note"><td colspan="6"><p class="help">' . __( 'Could not find any affiliate users. Please invite more friends and colleagues to start earning credit points.', 'wmc' ) . '</p></td></tr>';
                } else {
                    $wmcHtml .= '<tr class="affliate-note"><td colspan="6"><p class="help"><Strong>' . __( 'Affiliates : ', 'wmc' ) . '</strong>' . __( 'This particular column shows the number of Affiliates for the corresponding affiliate member.', 'wmc' ) . '</p></td></tr>';
                }
                $wmcHtml .= '</table>';
                $wmcHtml .= '</div>';
            }
            return $wmcHtml;
        }

        function dateRange(
            $first,
            $last,
            $step = '+1 day',
            $format = 'Y/m/d'
        ) {
            $dates = array();
            $current = strtotime( $first );
            $last = strtotime( $last );
            while ( $current <= $last ) {
                $dates[] = date( $format, $current );
                $current = strtotime( $step, $current );
            }
            return $dates;
        }

        function wmcGetAffliateUsersList(
            $parentID,
            $arrClass = array(),
            $backColor = '',
            $rHTML = ''
        ) {
            global $wpdb;
            $obj_referal_program = new Referal_Program();
            $get_filter = ( isset( $_GET['filter'] ) ? $_GET['filter'] : 'none' );
            $referral_users = $obj_referal_program->get_referral_user_list( $parentID, $get_filter );
            if ( is_array( $referral_users ) && count( $referral_users ) > 0 ) {
                foreach ( $referral_users as $key => $affiliate ) {
                    $followers = $this->fnGetFollowersCount( $affiliate->user_id );
                    $className = '';
                    if ( $parentID != get_current_user_id() && strpos( $className, 'wmc-child ' ) === false ) {
                        $className = 'wmc-child';
                    }
                    if ( !in_array( $parentID, $arrClass ) ) {
                        array_push( $arrClass, $parentID );
                    }
                    $opacity = 1 / count( $arrClass );
                    if ( $parentID == get_current_user_id() ) {
                        if ( $key % 2 != 0 ) {
                            $backColor = '230,230,230';
                        } else {
                            $backColor = '178,229,255';
                        }
                        $opacity = 1;
                    }
                    $wmcFinder = implode( '-', $arrClass );
                    $className .= ' wmc-child-' . $wmcFinder;
                    $user_info = get_userdata( $affiliate->user_id );
                    $args = array(
                        'customer_id' => $affiliate->user_id,
                    );
                    $orders = wc_get_orders( $args );
                    $credits = 0;
                    $order_ids = array();
                    $tbl_referal_program = $wpdb->prefix . 'referal_program';
                    foreach ( $orders as $key => $value ) {
                        $order_id = $value->get_id();
                        $order_ids[] = $order_id;
                    }
                    $order_id = implode( ',', $order_ids );
                    if ( !empty( $order_id ) ) {
                        $credits_res = $wpdb->get_var( "select sum(credits) as credit from {$tbl_referal_program} where order_id in ({$order_id}) and user_id = {$affiliate->user_id}" );
                    } else {
                        $credits_res = $wpdb->get_var( "select sum(credits) as credit from {$tbl_referal_program} where user_id = {$affiliate->user_id}" );
                    }
                    if ( $credits_res ) {
                        $credits = $credits_res;
                    }
                    $rHTML .= '<tr class="' . $className . '">';
                    if ( intval( $affiliate->followers ) > 0 ) {
                        $rHTML .= '<td align="center" data-title="' . __( 'Show/Hide', 'wmc' ) . '" class="view_hierarchie"><a href="javascript:void(0)" data-finder="' . $wmcFinder . '-' . $affiliate->user_id . '" class="view_hierarchie">' . __( 'View Hirarchy', 'wmc' ) . '  </a></td>';
                    } else {
                        $rHTML .= '<td align="center" data-title="' . __( 'Show/Hide', 'wmc' ) . '">-</td>';
                    }
                    $rHTML .= '<td  align="center" data-title="' . __( 'Referral Code', 'wmc' ) . '">' . $this->referral_user( 'referral_code', 'user_id', $affiliate->user_id ) . '</td><td data-title="' . __( 'Name', 'wmc' ) . '">' . $affiliate->first_name . '&nbsp;' . $affiliate->last_name . '</td><td align="right" data-title="' . __( 'Affiliates', 'wmc' ) . '">' . $followers . '</td><!--td align="right" data-title="' . __( 'Affiliates Credit', 'wmc' ) . '">' . number_format( $credits, 2 ) . '</td--><td align="right" data-title="' . __( 'Join Date', 'wmc' ) . '">' . $user_info->data->user_registered . '</td>';
                    $rHTML .= '</tr>';
                    if ( intval( $affiliate->followers ) > 0 ) {
                        $rHTML .= $this->wmcGetAffliateUsersList( $affiliate->user_id, $arrClass, $backColor );
                    }
                }
            }
            return $rHTML;
        }

        /* End */
        public function referral_user_credit_info() {
            if ( is_user_logged_in() ) {
                global $invitation_error;
                $check_user = $this->referral_user( 'user_id', 'user_id', get_current_user_id() );
                $wmc_html_credit = '<div class="wmc-show-credits">';
                if ( $check_user ) {
                    $current_user_id = $check_user;
                    $obj_referal_program = new Referal_Program();
                    $all_items = $obj_referal_program->getall_count( 0, 1, $current_user_id );
                    $total_items = $all_items[0]->records_count;
                    $data = array(
                        'referral_code'   => $this->referral_user( 'referral_code', 'user_id', $current_user_id ),
                        'total_points'    => $obj_referal_program->available_credits( $current_user_id ),
                        'total_followers' => $this->fnGetFollowersCount( $current_user_id ),
                        'records'         => $obj_referal_program->select_all( 10, 1, $current_user_id ),
                        'emails'          => ( isset( $_POST['emails'] ) ? sanitize_text_field( $_POST['emails'] ) : '' ),
                    );
                    $wmc_html_credit .= '<h2>' . __( 'Credit Points Log', 'wmc' ) . '</h2>';
                    if ( count( $data['records'] ) > 0 ) {
                        $wmc_html_credit .= '<div class="wmc_main_table_wrap" > <div class="loader_main" ><div id="loader"></div></div>';
                        $wmc_html_credit .= '<table class="shop_table shop_table_responsive my_account_orders">
                        <tr>
                        <!--th>' . __( 'Order', 'wmc' ) . '</th-->
                        <th>' . __( 'Date', 'wmc' ) . '</th>
                        <th>' . __( 'Note', 'wmc' ) . '</th>
                        </tr>';
                        foreach ( $data['records'] as $row ) {
                            $note = '';
                            $order = wc_get_order( $row['order_id'] );
                            if ( !is_bool( $order ) && $row['credits'] > 0 ) {
                                $credits = wc_price( apply_filters( 'wmc_total_credits_amount', $row['credits'] ) );
                                if ( $order->get_user_id() == $row['user_id'] ) {
                                    if ( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ) {
                                        $note = sprintf( apply_filters( 'wmc_store_refund_credits', __( '%s Store credit is refund for order %s.', 'wmc' ) ), $credits, '#' . $row['order_id'] );
                                    } else {
                                        $note = sprintf( apply_filters( 'wmc_store_earned_credits', __( '%s Store credit is earned from order %s.', 'wmc' ) ), $credits, '#' . $row['order_id'] );
                                    }
                                } else {
                                    $note = sprintf(
                                        apply_filters( 'wmc_store_earned_credits_by_referral', __( '%s Store credit is earned through referral user ( %s order %s )  ', 'wmc' ) ),
                                        $credits,
                                        get_user_meta( $order->get_user_id(), 'first_name', true ) . ' ' . get_user_meta( $order->get_user_id(), 'last_name', true ),
                                        '#' . $row['order_id']
                                    );
                                }
                            }
                            if ( !is_bool( $order ) && $row['redeems'] > 0 ) {
                                $redeems = wc_price( apply_filters( 'wmc_total_redeems_amount', $row['redeems'] ) );
                                if ( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ) {
                                    $note = sprintf( apply_filters( 'wmc_store_refund_credits', __( '%s Store credit is refund for order %s.', 'wmc' ) ), $redeems, '#' . $row['order_id'] );
                                } else {
                                    if ( $row['order_id'] ) {
                                        $note = sprintf( apply_filters( 'wmc_store_used_credits', __( '%s Store credit is used in order %s.', 'wmc' ) ), $redeems, '#' . $row['order_id'] );
                                    } else {
                                        $note = sprintf( apply_filters( 'wmc_store_expired_credits', __( '%s Store credit is expired.', 'wmc' ) ), $redeems );
                                    }
                                }
                            }
                            if ( $row['order_id'] == 0 ) {
                                $credits = wc_price( apply_filters( 'wmc_total_credits_amount', $row['credits'] ) );
                                $note = sprintf( __( 'You have %s credits for registration to the site.', 'wmc' ), $credits );
                            }
                            $note = apply_filters( 'wmc_credit_logs_notes', $note, $row );
                            $wmc_html_credit .= '<tr>
                            <!--td><a htref="">#' . $row['order_id'] . '</a></td-->
                            <td data-title="' . __( 'Date', 'wmc' ) . '">' . date_i18n( 'M d, Y', strtotime( $row['date'] ) ) . '</td>
                            <td data-title="' . __( 'Note', 'wmc' ) . '">' . $note . '</td>
                            </tr>';
                        }
                        $wmc_html_credit .= '</table> </div>';
                        $wmc_html_credit .= '</br>';
                        //$total_pages = ceil($total_items / $per_page);
                        $page = 1;
                        $page = ( $page == 0 ? 1 : $page );
                        $recordsPerPage = 10;
                        $start = ($page - 1) * $recordsPerPage;
                        $adjacents = 2;
                        $prev = $page - 1;
                        $next = $page + 1;
                        $lastpage = ceil( $total_items / $recordsPerPage );
                        $lpm1 = $lastpage - 1;
                        $pagination = "";
                        if ( $lastpage > 1 ) {
                            $pagination .= "<div class='pagination'>";
                            if ( $page > 1 ) {
                                $pagination .= "<button class='page-link button prev' data-page='" . $prev . "' >&laquo; Previous</button>";
                            } else {
                                $pagination .= "<button class='disabled prev'>&laquo; Previous</button>";
                            }
                            if ( $lastpage < 7 + $adjacents * 2 ) {
                                for ($counter = 1; $counter <= $lastpage; $counter++) {
                                    if ( $counter == $page ) {
                                        $pagination .= "<button class='current button'>{$counter}</button>";
                                    } else {
                                        $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                    }
                                }
                            } elseif ( $lastpage > 5 + $adjacents * 2 ) {
                                if ( $page < 1 + $adjacents * 2 ) {
                                    for ($counter = 1; $counter < 2 + $adjacents * 2; $counter++) {
                                        if ( $counter == $page ) {
                                            $pagination .= "<button class='current button'>{$counter}</button>";
                                        } else {
                                            $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                        }
                                    }
                                    $pagination .= "...";
                                    $pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
                                    $pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
                                } elseif ( $lastpage - $adjacents * 2 > $page && $page > $adjacents * 2 ) {
                                    $pagination .= "<button class='page-link button' data-page='1' >1</button>";
                                    $pagination .= "<button class='page-link button' data-page='2' >2</button>";
                                    $pagination .= "...";
                                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                                        if ( $counter == $page ) {
                                            $pagination .= "<button class='current button'>{$counter}</button>";
                                        } else {
                                            $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                        }
                                    }
                                    $pagination .= "..";
                                    $pagination .= "<button class='page-link button' data-page='" . $lpm1 . "' >{$lpm1}</button>";
                                    $pagination .= "<button class='page-link button' data-page='" . $lastpage . "' >{$lastpage}</button>";
                                } else {
                                    $pagination .= "<button class='page-link button' data-page='1' >1</button>";
                                    $pagination .= "<button class='page-link button' data-page='2' >2</button>";
                                    $pagination .= "..";
                                    for ($counter = $lastpage - (2 + $adjacents * 2); $counter <= $lastpage; $counter++) {
                                        if ( $counter == $page ) {
                                            $pagination .= "<button class='current button'>{$counter}</button>";
                                        } else {
                                            $pagination .= "<button class='page-link button' data-page='" . $counter . "' >{$counter}</button>";
                                        }
                                    }
                                }
                            }
                            if ( $page < $counter - 1 ) {
                                $pagination .= "<button class='page-link button next' data-page='" . $next . "' >Next &raquo;</button>";
                            } else {
                                $pagination .= "<button class='disabled next'>Next &raquo;</button>";
                            }
                            $pagination .= "</div>";
                        }
                        $wmc_html_credit .= $pagination;
                        /* if($total_pages > 1){
                               $wmc_html_credit.= '<div class="pagination">';
                               $wmc_html_credit.= '<button class="page-link button" disabled >&laquo;</button>';
                               for ($i = ($currentpage - $range); $i < (($currentpage + $range)  + 1); $i++) {
                                   if (($i > 0) && ($i <= $total_pages)) {
                                       if($i == 1){
                                           $wmc_html_credit.= '<button class="page-link button current" data-page="' . $i . '">' . $i . '</button> ';
                                       }else{
                                           $wmc_html_credit.= '<button class="page-link button" data-page="' . $i . '">' . $i . '</button> ';
                                       }
                                   }
                               }
                               $wmc_html_credit.= '<button class="page-link button">...</button>';
                               $wmc_html_credit.= '<button class="page-link button">&raquo;</button>';
                               $wmc_html_credit.= '</div>';
                           }
                           */
                    } else {
                        $wmc_html_credit .= '<p class="help">' . __( 'No records found.', 'wmc' ) . '</p>';
                    }
                }
                $wmc_html_credit .= '</div>';
                $wmc_html_credit = apply_filters(
                    'wmc_store_credits_contents',
                    $wmc_html_credit,
                    $data,
                    $check_user
                );
                return $wmc_html_credit;
            }
            return;
        }

        /**
         *    Send invation to others to join Referral Program
         *
         *    @return string status
         **/
        public function send_invitation() {
            global $customer_id, $referral_code, $invitation_error;
            try {
                // WP Validation
                $validation_errors = new WP_Error();
                $invitation_error = false;
                if ( isset( $_POST['action'] ) && $_POST['action'] == 'send_invitations' ) {
                    unset($_POST['action']);
                    if ( empty( $_POST['emails'] ) ) {
                        throw new Exception(__( 'Please enter a valid E-mail address.', 'wmc' ));
                    }
                    $email_array = explode( ',', sanitize_text_field( $_POST['emails'] ) );
                    $customer_id = get_current_user_id();
                    WC()->mailer();
                    $current_user = wp_get_current_user();
                    $email = $current_user->user_email;
                    $first_name = $current_user->user_firstname;
                    $last_name = $current_user->user_lastname;
                    $referral_code = $this->referral_user( 'referral_code', 'user_id', $customer_id );
                    $invalid_arrray = array();
                    $exist_email_array = array();
                    $success_mail = false;
                    foreach ( $email_array as $email ) {
                        //check exist user join with program
                        // Referral user mail
                        $check_user = $this->user_join_referral_program( $email );
                        if ( $email != '' ) {
                            if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) && email_exists( $email ) && $check_user ) {
                                $exist_email_array[] = $email;
                            } elseif ( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
                                $success_mail = true;
                                do_action(
                                    'wmc_joining_user_notification',
                                    $email,
                                    $first_name,
                                    $last_name,
                                    $referral_code,
                                    'referral_user',
                                    $customer_id
                                );
                            } else {
                                $invalid_arrray[] = $email;
                            }
                        }
                    }
                    if ( count( $exist_email_array ) > 0 ) {
                        $email_list = '<ul><li>' . implode( '</li><li>', $exist_email_array ) . '</li></ul>';
                        $messagewmc1 = __( 'The user is already part of our referral program, please try with different E-mail address.', 'wmc' );
                        throw new Exception($messagewmc1 . $email_list);
                    }
                    if ( !$success_mail ) {
                        $messagewmc2 = __( 'E-mail address is invalid.', 'wmc' );
                        throw new Exception($messagewmc2);
                    }
                    if ( count( $invalid_arrray ) > 0 ) {
                        $email_list = '<ul><li>' . implode( '</li><li>', $invalid_arrray ) . '</li></ul>';
                        $messagewmc3 = __( 'We can not send invitation to below listed E-mail addresses.', 'wmc' );
                        throw new Exception($messagewmc2 . $email_list);
                    }
                    wc_add_notice( __( 'Your invitations are sent succesfully!', 'wmc' ) );
                }
            } catch ( Exception $e ) {
                $invitation_error = true;
                wc_add_notice( '<strong>' . __( 'Error', 'wmc' ) . ':</strong> ' . $e->getMessage(), 'error' );
            }
        }

        /**
         * User join Referral Program
         *
         * @return bool
         **/
        public function user_join_referral_program( $email ) {
            if ( email_exists( $email ) ) {
                global $wpdb;
                $user = get_user_by( 'email', $email );
                if ( $user ) {
                    $user_id = $user->ID;
                    $checkval = $wpdb->get_var( 'SELECT id FROM ' . $this->table_name . ' WHERE user_id = ' . $user_id );
                    if ( $checkval ) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Hander for late join Referral Program
         *
         * @return void
         **/
        public function join_referral_program() {
            try {
                // WP Validation
                $validation_errors = new WP_Error();
                if ( isset( $_POST['join_referral_program'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'referral_program' ) ) {
                    $validation_errors = $this->referral_registration_validation( null, null, $validation_errors );
                    if ( $validation_errors->get_error_code() ) {
                        unset($_POST['_wpnonce']);
                        throw new Exception($validation_errors->get_error_message());
                    }
                    $this->referral_customer_save_data( get_current_user_id() );
                    wc_add_notice( __( 'Thanks for joining the referral program', 'wmc' ) );
                    unset($_POST['_wpnonce']);
                }
            } catch ( Exception $e ) {
                wc_add_notice( '<strong>' . __( 'Error', 'wmc' ) . ':</strong> ' . $e->getMessage(), 'error' );
            }
        }

        /**
         * Validate the extra register fields.
         *
         * @param  string $username          Current username.
         * @param  string $email             Current email.
         * @param  object $validation_errors WP_Error object.
         *
         * @return void
         */
        public function referral_registration_validation( $username, $email, $validation_errors ) {
            $autoJoin = get_option( 'wmc_auto_register', 'no' );
            if ( isset( $_POST['billing_first_name'] ) && $_POST['billing_first_name'] == '' ) {
                $validation_errors->add( 'empty required fields', __( 'Please enter the First name.', 'wmc' ) );
            }
            if ( isset( $_POST['billing_last_name'] ) && $_POST['billing_last_name'] == '' ) {
                $validation_errors->add( 'empty required fields', __( 'Please enter the Last name.', 'wmc' ) );
            }
            if ( isset( $_POST['referral_code'] ) && $_POST['referral_code'] == '' && isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] == 1 ) {
                if ( $autoJoin != 'yes' ) {
                    $validation_errors->add( 'empty required fields', __( 'You must have to add referral code to join referral program.', 'wmc' ) );
                }
            }
            if ( isset( $_POST['email'] ) && !is_email( $_POST['email'] ) ) {
                $validation_errors->add( 'invalid fields', __( 'E-mail address is invalid', 'wmc' ) );
            }
            if ( isset( $_POST['referral_code'] ) && $_POST['referral_code'] != '' && isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] == 1 ) {
                $parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( $_POST['referral_code'] ) );
                if ( !$parent_id ) {
                    $validation_errors->add( 'empty required fields', __( 'There is no such referral code exist<strong>(' . sanitize_text_field( $_POST['referral_code'] ) . ')</strong> exist.', 'wmc' ) );
                    $_POST['wrong_referral_code'] = 'yes';
                }
            }
            if ( isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] == 2 && isset( $_POST['referral_email'] ) && $_POST['referral_email'] == '' ) {
                //$validation_errors->add( 'empty required fields', __( 'You have to add referral email to join referral program.', 'wmc' ) );
            }
            //if ( isset($_POST['join_referral_program']) && $_POST['referral_email'] != '' ) {
            if ( isset( $_POST['join_referral_program'] ) && isset( $_POST['referral_email'] ) && $_POST['join_referral_program'] == 2 && $_POST['referral_email'] != '' ) {
                if ( email_exists( $_POST['referral_email'] ) ) {
                    $validation_errors->add( 'invalid fields', __( 'This referral E-mail <strong>(' . sanitize_text_field( $_POST['referral_email'] ) . ')</strong> is already exist.', 'wmc' ) );
                }
            }
            if ( isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] != 3 ) {
                if ( !isset( $_POST['termsandconditions'] ) || $_POST['termsandconditions'] != 1 ) {
                    $validation_errors->add( 'Error', __( 'Please accept referral Program terms and conditions', 'wmc' ) );
                }
            }
            return $validation_errors;
        }

        /**
         * Save the extra register fields.
         *
         * @param  int  $customer_id Current customer ID.
         *
         * @return void
         */
        public function referral_customer_save_data( $user_id ) {
            global $customer_id, $referral_code;
            $customer_id = $user_id;
            $parent_id = 0;
            $first_name = '';
            $last_name = '';
            $email = '';
            if ( isset( $_POST['billing_first_name'] ) ) {
                // WordPress default first name field.
                update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
                // WooCommerce billing first name.
                update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
                $first_name = $_POST['billing_first_name'];
            }
            if ( isset( $_POST['billing_last_name'] ) ) {
                // WordPress default last name field.
                update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
                // WooCommerce billing last name.
                update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
                $last_name = sanitize_text_field( $_POST['billing_last_name'] );
            }
            $autoJoin = get_option( 'wmc_auto_register', 'no' );
            if ( isset( $_POST['referral_code'] ) && $_POST['referral_code'] != '' ) {
                $parent_id = $this->referral_user( 'user_id', 'referral_code', sanitize_text_field( $_POST['referral_code'] ) );
            } else {
                if ( $autoJoin == 'yes' ) {
                    $_POST['join_referral_program'] = 2;
                }
            }
            if ( isset( $_POST['termsandconditions'] ) && $_POST['termsandconditions'] == 1 ) {
                update_user_meta( $customer_id, 'termsandconditions', sanitize_text_field( $_POST['termsandconditions'] ) );
            }
            if ( isset( $_POST['join_referral_program'] ) && $_POST['join_referral_program'] < 3 || $parent_id ) {
                $referral_code = $this->referral_code( $customer_id );
                $creditFor = get_option( 'wmc_welcome_credit_for', 'new' );
                $benefit = 0;
                if ( isset( $_POST['action'] ) && $_POST['action'] == 'join_referreal_program' ) {
                    if ( $creditFor == 'new' ) {
                        $benefit = 1;
                    }
                }
                if ( !$this->referral_user( 'id', 'user_id', $customer_id ) ) {
                    $plan_type = get_option( 'wmc_plan_type' );
                    $this->insert( array(
                        'user_id'          => $customer_id,
                        'referral_parent'  => ( $parent_id ? $parent_id : 0 ),
                        'active'           => 1,
                        'referral_code'    => $referral_code,
                        'referral_email'   => ( isset( $_POST['referral_email'] ) ? sanitize_text_field( $_POST['referral_email'] ) : '' ),
                        'referal_benefits' => $benefit,
                    ) );
                    update_user_meta( $customer_id, 'total_referrals', 0 );
                    $this->fnUpdateFollowersCount( $customer_id );
                }
                if ( get_current_user_id() ) {
                    $current_user = wp_get_current_user();
                    $email = $current_user->user_email;
                    $first_name = $current_user->user_firstname;
                    $last_name = $current_user->user_lastname;
                } else {
                    $email = ( isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '' );
                }
                WC()->mailer();
                //    Joining mail for new registered user
                do_action(
                    'wmc_joining_user_notification',
                    $email,
                    $first_name,
                    $last_name,
                    $referral_code,
                    'joining_mail',
                    $customer_id
                );
                // Referral user mail
                if ( isset( $_POST['referral_email'] ) && $_POST['referral_email'] != '' ) {
                    do_action(
                        'wmc_joining_user_notification',
                        sanitize_text_field( $_POST['referral_email'] ),
                        $first_name,
                        $last_name,
                        $referral_code,
                        'referral_user',
                        $customer_id
                    );
                }
                //break;
            }
        }

        /**
         * Generate referral code
         *
         * @param int $customer_id Current customer ID.
         *
         * @return Unique Referral Code
         */
        public function referral_code( $customer_id ) {
            global $wpdb;
            $temp_cid = md5( 'R' . $customer_id );
            $referral_code = substr( $temp_cid, 0, 5 );
            $referral_code = apply_filters( 'multilevel_referral_code', $referral_code, $customer_id );
            $exist_referral_code = $wpdb->get_var( 'SELECT id FROM ' . $this->table_name . ' WHERE referral_code = "' . $referral_code . '"' );
            if ( $exist_referral_code ) {
                $this->referral_code( $referral_code );
            }
            return $referral_code;
        }

        /*
         *    Get number of referral users
         */
        public function record_count() {
            global $wpdb;
            $sql = "SELECT count(B.ID)  FROM " . $this->table_name . " AS A LEFT JOIN {$wpdb->users} AS B ON A.user_id = B.ID WHERE A.active = 1";
            return $wpdb->get_var( $sql );
        }

        public function add_my_account_menu( $items ) {
            $key = array_search( 'dashboard', array_keys( $items ) );
            if ( $key !== false ) {
                $items = array_merge( array_splice( $items, 0, $key + 1 ), array(
                    'referral' => __( 'Referral', 'wmc' ),
                ), $items );
                $items = array_merge( array_splice( $items, 0, $key + 2 ), array(
                    'my-affliates' => __( 'My Affiliates', 'wmc' ),
                ), $items );
            } else {
                $items['referral'] = __( 'Referral', 'wmc' );
                $items['my-affliates'] = __( 'My Affiliates', 'wmc' );
            }
            return $items;
        }

        public function add_referral_query_var( $vars ) {
            $vars[] = 'referral';
            $vars[] = 'my-affliates';
            return $vars;
        }

        public function woocommerce_account_referral_endpoint_hook() {
            $this->referral_user_account_panel();
        }

        public function wmc_my_affliates_endpoint_content() {
            $this->referral_user_my_affiliate_panel();
        }

        public function init_hook() {
            add_rewrite_endpoint( 'referral', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'my-affliates', EP_ROOT | EP_PAGES );
            add_rewrite_endpoint( 'wmcbanner', EP_ROOT | EP_PAGES );
            flush_rewrite_rules();
            add_action( 'wp_ajax_wmcChangeBanner', array($this, 'wmcChangeBanner') );
            add_action( 'wp_ajax_wmcSaveTransientBanner', array($this, 'wmcSaveTransientBanner') );
            add_action( 'wp_ajax_wmcCreditLogPagination', array($this, 'wmcCreditLogPagination') );
            if ( isset( $_GET['ru'] ) && $_GET['ru'] != '' ) {
                setcookie(
                    'WMC_REFERRAL_CODE',
                    $_GET['ru'],
                    strtotime( '+30 days' ),
                    '/'
                );
            }
            global $woocommerce;
            if ( isset( $woocommerce ) && version_compare( $woocommerce->version, '2.6.0', ">=" ) ) {
                /* Hooks for myaccount referral endpoint */
                add_filter( 'woocommerce_account_menu_items', array($this, 'add_my_account_menu') );
                add_filter( 'query_vars', array($this, 'add_referral_query_var') );
                add_action( 'woocommerce_account_referral_endpoint', array($this, 'woocommerce_account_referral_endpoint_hook') );
                add_action( 'woocommerce_account_my-affliates_endpoint', array($this, 'wmc_my_affliates_endpoint_content') );
            } else {
                add_action( 'woocommerce_before_my_account', array($this, 'referral_user_account_panel') );
            }
            add_filter( 'woocommerce_checkout_fields', array($this, 'wmc_override_checkout_fields') );
            add_action( 'woocommerce_checkout_process', array($this, 'wmc_custom_checkout_field_process') );
        }

        function get_all_referral_user_id( $user_ids = array(), $first_level = false ) {
            global $wpdb;
            $sql = "SELECT user_id FROM {$wpdb->prefix}referal_users WHERE referral_parent IN (" . implode( ',', $user_ids ) . ")";
            $user_list = $wpdb->get_col( $sql );
            if ( $first_level ) {
                return $user_list;
            }
            if ( count( $user_list ) ) {
                $user_ids = array_merge( $user_ids, $user_list );
                $user_list = $this->get_all_referral_user_id( $user_list );
            }
            return array_unique( array_merge( $user_ids, $user_list ) );
        }

        function get_orders_by_id( $id ) {
            global $wpdb;
            $sql = "SELECT ID FROM {$wpdb->posts} WHERE ID LIKE '%{$id}%' AND `post_type` = 'shop_order'";
            return $wpdb->get_col( $sql );
        }

        function get_referral_user_level( $user_id, $level = 1 ) {
            global $wpdb;
            $parent_id = $wpdb->get_var( 'SELECT `referral_parent` FROM ' . $this->table_name . ' WHERE `active` = 1 AND `user_id` = "' . $user_id . '"' );
            if ( $parent_id ) {
                $level++;
                $level = $this->get_referral_user_level( $parent_id, $level );
            }
            return $level;
        }

        function change_referral_user_level( $user_id ) {
            global $wpdb;
            $parent_id = $wpdb->get_var( 'SELECT B.`referral_parent` FROM ' . $this->table_name . ' AS A LEFT JOIN ' . $this->table_name . ' AS B ON A.referral_parent = B.user_id WHERE A.`active` = 1 AND  B.`active` = 1 AND A.`user_id` = "' . $user_id . '"' );
            $parent_id = ( $parent_id ? $parent_id : 0 );
            $this->update( $user_id, $parent_id );
            $referral_level = $this->get_referral_user_level( $user_id );
            return $referral_level;
        }

    }

    // end Referal_Users
}