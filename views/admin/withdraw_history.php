<?php

$title = __('Withdrawn History', 'wmc');

$order_list = new WMR_Withdraw_history();

?>

<?php if(isset($_GET['action']) && $_GET['action'] == 'delete') {?>

<div class="notice notice-success is-dismissible"> 

	<p><strong>Withdraw transaction deleted.</strong></p>

</div>

<?php } ?>

	<form method="get" id="form_widthdraw_filter">

		<div class="tablenav top">

    		<div class="alignleft actions">

    			<input type="hidden" name="page" value="wc_referral">

    			<input type="hidden" name="tab" value="withdraw_history">

	        <?php

	        echo '<input type="text" name="search_by_name" placeholder="'.__('Search By Name','wmc').'" value="'.(isset($_REQUEST['search_by_name'])?$_REQUEST['search_by_name']:'').'" />';

	        echo '<input type="text" name="search_by_mobile" class="search_email" placeholder="'.__('Search By Mobile Number','wmc').'" value="'.(isset($_REQUEST['search_by_mobile'])?$_REQUEST['search_by_mobile']:'').'" />';

	        echo '<lable>'.__('Date Range','wmc').' :</lable>';

	        echo '<input type="text" name="search_start_date" placeholder="YYYY/MM/DD" value="'.(isset($_REQUEST['search_start_date'])?$_REQUEST['search_start_date']:'').'" />';

	        echo '<input type="text" name="search_end_date" placeholder="YYYY/MM/DD" value="'.(isset($_REQUEST['search_end_date'])?$_REQUEST['search_end_date']:'').'" />';

	        submit_button( __( 'Apply','wmc'), 'action', '', false, array( 'id' => "doaction" ) );

	        echo '<input type="button" value="'.__('Reset','wmc').'" class="button action" id="reset_button_withdraw"><br />';       

	        ?>

	    	</div>

    	</div>

	</form>

    <form method="get" id="form_widthdraw_table">

    	<input type="hidden" name="page" value="wc_referral">

    	<input type="hidden" name="tab" value="withdraw_history">

        <?php 



        $order_list->prepare_items();

        $order_list->display(); ?>

    </form>

</div>

