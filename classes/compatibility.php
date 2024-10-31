<?php
if ( ! class_exists( 'Referal_compatibility' ) ) {
    /**
    * front controller class
    */
    class Referal_compatibility {

        public function __construct(){
            add_filter( 'wolmart_account_dashboard_items', array($this, 'wolmart_account_add_navigation_referral'));
        }

        /**
         *  17-09-2021
         *
         *  Add referral menu tabs user account ( Wolmart theme conflict my-account/navigation.php ) 
         **/
        function wolmart_account_add_navigation_referral($account_arr){
            $account_arr['referral']     = array('Referral','referral');
            $account_arr['my-affliates'] = array('My Affiliates','my-affliates');
            return $account_arr;
        }
    }
    new Referal_compatibility();
}
?>