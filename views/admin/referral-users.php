<?php

$title = __('Referral users', 'wmc');

$user_list = new WMR_User_Table();



if(isset( $_GET['uid']) && $_GET['uid'] != '' ){

    $client_name = get_user_meta( $_GET['uid'], 'first_name', true );

    $last_name = get_user_meta( $_GET['uid'], 'last_name', true );

    if( !empty( $last_name ) ){

        $client_name .= ' '. $last_name;    

    }

    

     echo '<div class="updated"><p>'.$client_name.' '.__('is successfully activated','wmc').'</p></div>';   

}

?>


    <form method="get" action="admin.php" id="referral_user_form">

        <input type="hidden" name="page" value="wc_referral" />

        <?php

        $user_list->prepare_items();

        $user_list->display(); ?>

    </form>



</div>

<div id="dialog_referral_user" title="List of referral users"></div>