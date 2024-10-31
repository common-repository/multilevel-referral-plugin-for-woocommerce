<div class="store_credit_notice">
	<h3><?php echo sprintf( __('You have %s store credits.', 'wmc'), $data['store_credit'] ); ?> 
	<a href="#"><?php echo __( 'Redeem now.', 'wmc' ); ?></a></h3>
	<form method="post" action="<?php echo get_the_permalink(); ?>">
		<input type="text" value="<?php echo round( $data['appied_credit_amount'], 2 );?>" name="appied_credit_amount" />
		<input type="hidden" name="action" value="apply_store_credit" />
		<input type="hidden" name="_nonce" value="<?php echo $data['nonce']?>" />
		<input type="submit" value="<?php echo __('Apply','wmc');?>" /><br />
		<div class="notice"><small><?php echo sprintf( __('You can use max %s as store credit.', 'wmc'), wc_price( $data['max_use_credit'] ) ). ' '.$data['notice'];?></small></div>
	</form>
</div>