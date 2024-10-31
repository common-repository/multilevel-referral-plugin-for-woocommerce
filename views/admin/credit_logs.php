<?php

$title = __('Credit logs', 'wmc');

$order_list = new WMR_Credit_log();

?>        

    <form method="get" id="point_logs_id">
    	<input type="hidden" name="page" value="wc_referral" />
    	<input type="hidden" name="tab" value="credit_logs" />
        <?php
        $order_list->search_box('Search', 'search');
        $order_list->prepare_items();
        $order_list->display(); ?>

    </form>

</div>

