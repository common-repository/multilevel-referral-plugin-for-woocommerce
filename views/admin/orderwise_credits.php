<?php

$title = __('Orderwise credits', 'wmc');

$order_list = new WMR_Orcer_Credit_List();

?>


    <form method="get" id="otherwise_credits">
    	<input type="hidden" name="page" value="wc_referral" />
    	<input type="hidden" name="tab" value="orderwise_credits" />

        <?php
        $order_list->search_box('Search', 'search');
        $order_list->prepare_items();

        $order_list->display(); ?>

    </form>

</div>

