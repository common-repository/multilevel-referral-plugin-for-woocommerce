<?php

/**
 * WooCommerce Multilevel Referral
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}
if ( !class_exists( 'WMR_Referal_Settings' ) ) {
    /**
     * WMR_Referal_Settings.
     */
    class WMR_Referal_Settings extends WMC_Module {
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
            add_action( 'admin_menu', __CLASS__ . '::_add_referal_menu_callback', 99 );
            add_action( 'all_admin_notices', __CLASS__ . '::_add_referal_header_callback', 99 );
            add_action( 'pre_get_posts', __CLASS__ . '::_change_banner_display_order' );
            add_filter( 'admin_body_class', array($this, 'wmc_add_banner_body_class') );
            add_action( 'admin_footer', array($this, 'close_banner_div') );
        }

        public function wmc_add_banner_body_class( $classes ) {
            global $post;
            if ( isset( $_GET['post_type'] ) && sanitize_text_field( $_GET['post_type'] ) == 'wmc-banner' || isset( $post->post_type ) && $post->post_type == 'wmc-banner' ) {
                $classes = ' toplevel_page_wc_referral ';
            }
            $class_name = 'mrapfw_free_plan';
            $classes .= " {$class_name}";
            return $classes;
        }

        public static function _change_banner_display_order( $query ) {
            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wmc-banner' && $query->is_main_query() ) {
                $args = array(
                    'title' => 'ASC',
                );
                $query->set( 'orderby', $args );
            }
        }

        public static function _add_referal_menu_callback() {
            $icon = WMC_URL . 'images/mrapfw_icon.png';
            add_menu_page(
                __( 'Referral', 'wmc' ),
                __( 'Referral', 'wmc' ),
                'manage_woocommerce',
                'wc_referral',
                __CLASS__ . '::referal_program',
                $icon,
                55.6
            );
        }

        public static function _add_referal_header_callback() {
            if ( !isset( $_GET['post_type'] ) || $_GET['post_type'] != 'wmc-banner' ) {
                return;
            }
            $obj_referal_users = new Referal_Users();
            $obj_referal_program = new Referal_Program();
            $users = count_users();
            $total_referrals = $obj_referal_users->record_count();
            $total_credits = $obj_referal_program->total_statistic( 'credits' );
            $total_redeems = $obj_referal_program->total_statistic( 'redeems' );
            $data = array(
                'total_users'     => $users['total_users'],
                'total_referrals' => $total_referrals,
                'total_credites'  => $total_credits,
                'total_redeems'   => $total_redeems,
            );
            $_GET['tab'] = 'banners';
            echo self::render_template( 'admin/referral-header.php', array(
                'data' => $data,
            ) );
            print '<div class="wmc_referral_table_shadow wmc_banner_section">';
            return;
        }

        function close_banner_div() {
            if ( !isset( $_GET['post_type'] ) || sanitize_text_field( $_GET['post_type'] ) != 'wmc-banner' ) {
                return;
            }
            print '</div>';
        }

        public static function referal_program() {
            $template = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'referral-users' );
            $is_pro = false;
            if ( ($template == 'advSettings' || $template == 'addons') && !$is_pro ) {
                return;
            }
            $option = 'per_page';
            $args = [
                'label'   => 'Orders',
                'default' => 5,
                'option'  => 'orders_per_page',
            ];
            add_screen_option( $option, $args );
            WMR_Referal_Settings::_save_referal_templates_callback();
            $obj_referal_users = new Referal_Users();
            $obj_referal_program = new Referal_Program();
            $users = count_users();
            $total_referrals = $obj_referal_users->record_count();
            $total_credits = $obj_referal_program->total_statistic( 'credits' );
            $total_redeems = $obj_referal_program->total_statistic( 'redeems' );
            $data = array(
                'total_users'          => $users['total_users'],
                'total_referrals'      => $total_referrals,
                'total_credites'       => $total_credits,
                'total_redeems'        => $total_redeems,
                'advance_setting_link' => '',
                'addons_link'          => '',
            );
            echo self::render_template( 'admin/referral-header.php', array(
                'data' => $data,
            ) );
            print '<div class="wmc_referral_table_shadow">';
            echo self::render_template( 'admin/' . $template . '.php' );
            print '</div>';
        }

        /*
         *	Save email templates
         */
        public static function _save_referal_templates_callback() {
            if ( isset( $_POST['save_template'] ) ) {
                update_option( 'joining_mail_template', $_POST['joining_mail_template'] );
                update_option( 'joining_mail_subject', $_POST['joining_mail_subject'] );
                update_option( 'joining_mail_heading', $_POST['joining_mail_heading'] );
                update_option( 'referral_user_template', $_POST['referral_user_template'] );
                update_option( 'referral_user_subject', $_POST['referral_user_subject'] );
                update_option( 'referral_user_heading', $_POST['referral_user_heading'] );
                update_option( 'expire_notification_template', $_POST['expire_notification_template'] );
                update_option( 'expire_notification_subject', $_POST['expire_notification_subject'] );
                update_option( 'expire_notification_heading', $_POST['expire_notification_heading'] );
                do_action( 'wmc_save_email_templates' );
            }
        }

        public function activate( $network_wide ) {
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() {
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
        }

        /**
         * Checks if the plugin was recently updated and upgrades if necessary
         *
         * @mvc Controller
         *
         * @param string $db_version
         */
        public function upgrade( $db_version = 0 ) {
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         * @return bool
         */
        public function is_valid( $valid = "all" ) {
            return true;
        }

    }

    new WMR_Referal_Settings();
}