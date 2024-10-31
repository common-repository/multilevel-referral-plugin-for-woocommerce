<?php

require_once '../../../wp-load.php';
if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}
if ( !class_exists( 'wmc_cron' ) ) {
    class wmc_cron {
        public function __construct() {
        }

    }

    new wmc_cron();
}