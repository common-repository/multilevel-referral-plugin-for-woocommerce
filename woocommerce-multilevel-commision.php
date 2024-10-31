<?php

/**
* Plugin Name: Multilevel Referral Affiliate Plugin for WooCommerce
* Plugin URI: http://referral.staging.prismitsystems.com/
* Description: The WooCommerce Multilevel Plugin is a WooCommerce Add-On Plugin. 
Attract new customers, grow and market your business for free using a social referral program. Made especially for WooCommerce store owners, Multilevel Referral Affiliate Plugin for WooCommerce rewards your clients for sharing your website with their friends, family, and colleagues. 
* Version: 2.27
* WC requires at least: 3.0.0
* WC tested up to: 9.1.2
* Author: Prism I.T. Systems
* Author URI: http://www.prismitsystems.com
* Developer: Prism I.T. Systems
* Developer URI: http://www.prismitsystems.com
* Text Domain: wmc
* Domain Path: /languages
* Copyright: &copy;    2009-2019 PRISM I.T. SYSTEMS.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
if ( !defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}
require_once ABSPATH . 'wp-includes/pluggable.php';
define( 'WMC_NAME', 'Multilevel Referral Affiliate Plugin for WooCommerce' );
define( 'WMC_REQUIRED_PHP_VERSION', '5.3' );
// because of get_called_class()
define( 'WMC_REQUIRED_WP_VERSION', '3.1' );
// because of esc_textarea()
define( 'WMC_VER', 2.27 );
define( 'WMC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WMC_URL', plugin_dir_url( __FILE__ ) );
/* High-performance order storage compatible */
add_action( 'before_woocommerce_init', 'wmc_high_performance_compatible' );
function wmc_high_performance_compatible() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}

if ( function_exists( 'mrapfw_fs' ) ) {
    mrapfw_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'mrapfw_fs' ) ) {
        // Create a helper function for easy SDK access.
        function mrapfw_fs() {
            global $mrapfw_fs;
            if ( !isset( $mrapfw_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $mrapfw_fs = fs_dynamic_init( array(
                    'id'             => '12292',
                    'slug'           => 'multilevel-referral-affiliate-plugin-for-woocommerce',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_76635ea6cec771cc09d9d49823c0d',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => true,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 3,
                        'is_require_payment' => false,
                    ),
                    'menu'           => array(
                        'slug'    => 'wc_referral',
                        'support' => false,
                        'parent'  => array(
                            'slug' => 'wc_referral',
                        ),
                    ),
                    'is_live'        => true,
                ) );
            }
            return $mrapfw_fs;
        }

        // Init Freemius.
        mrapfw_fs();
        // Signal that SDK was initiated.
        do_action( 'mrapfw_fs_loaded' );
    }
    // plugin's main file logic ...
    add_action( 'init', 'wmc_plugin_init' );
    if ( !function_exists( 'wmc_plugin_init' ) ) {
        function wmc_plugin_init() {
            $locale = ( is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale() );
            $locale = apply_filters( 'plugin_locale', $locale, 'wmc' );
            unload_textdomain( 'wmc' );
            load_textdomain( 'wmc', WMC_DIR . 'languages/' . "wmc-" . $locale . '.mo' );
            load_plugin_textdomain( 'wmc', false, WMC_DIR . 'languages' );
        }

    }
    /**
     * Checks if the system requirements are met
     *
     * @return bool True if system requirements are met, false if not
     */
    if ( !function_exists( 'wmc_requirements_check' ) ) {
        function wmc_requirements_check() {
            global $wp_version;
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
            // to get is_plugin_active() early
            if ( version_compare( PHP_VERSION, WMC_REQUIRED_PHP_VERSION, '<' ) ) {
                return false;
            }
            if ( version_compare( $wp_version, WMC_REQUIRED_WP_VERSION, '<' ) ) {
                return false;
            }
            if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                return false;
            }
            return true;
        }

    }
    /**
     * Prints an error that the system requirements weren't met.
     */
    if ( !function_exists( 'wmc_requirements_error' ) ) {
        function wmc_requirements_error() {
            global $wp_version;
            require_once dirname( __FILE__ ) . '/views/requirements-error.php';
        }

    }
    /**
     * Prints an error that the system requirements weren't met.
     */
    if ( !function_exists( 'wmc_requirements_library' ) ) {
        function wmc_requirements_library() {
            global $wp_version;
            require_once dirname( __FILE__ ) . '/views/requirements-lib-error.php';
        }

    }
    /*
     * Check requirements and load main class
     * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
     */
    if ( wmc_requirements_check() ) {
        if ( !function_exists( 'imagecreatefrompng' ) ) {
            add_action( 'admin_notices', 'wmc_requirements_library' );
        }
        require_once __DIR__ . '/classes/wmc-module.php';
        require_once __DIR__ . '/includes/functions.php';
        if ( is_admin() ) {
            require_once __DIR__ . '/classes/admin/table-users.php';
            require_once __DIR__ . '/classes/admin/table-credit_logs.php';
            require_once __DIR__ . '/classes/admin/table-orderwise_credits.php';
            require_once __DIR__ . '/classes/admin/settings-general.php';
            require_once __DIR__ . '/classes/admin/users.php';
            require_once __DIR__ . '/classes/admin/referral.php';
            require_once __DIR__ . '/classes/admin/metabox-product.php';
        }
        require_once __DIR__ . '/classes/woocommerce-multilevel-referral.php';
        require_once __DIR__ . '/classes/referral-program.php';
        require_once __DIR__ . '/classes/referral-users.php';
        require_once __DIR__ . '/classes/woocommerce-order.php';
        require_once __DIR__ . '/classes/compatibility.php';
        if ( class_exists( 'WooCommerce_Multilevel_Referal' ) ) {
            $GLOBALS['wpps'] = WooCommerce_Multilevel_Referal::get_instance();
            register_activation_hook( __FILE__, array($GLOBALS['wpps'], 'activate') );
            register_deactivation_hook( __FILE__, array($GLOBALS['wpps'], 'deactivate') );
        }
    } else {
        add_action( 'admin_notices', 'wmc_requirements_error' );
    }
}