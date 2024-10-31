<?php $addons = simplexml_load_file( WMC_DIR . '/addons.xml' ); ?>
<div class="wmc-addons-panel">
	<h1><?php echo esc_html__('Implementing Inputs with Add-Ons for an ever evolving experience', 'wmc');?></h1>
	<p><?php echo esc_html__('Recognising various popular requests, we steadily aim for new advancements at your disposal', 'wmc');?></p>
	<?php foreach( $addons as $addon ): ?>
	<?php $plugin_file = $addon->slug.'/'.$addon->slug.'.php'; ?>
	<?php $licence = get_option( $addon->slug.'_activated' ); ?>	
	<div class="wmc-addons-banner-block-item wmc-blue-gradient">
		<div class="wmc-addons-banner-block-item-icon">
			<div class="wmc-addons-banner-inner">
				<img class="wmc-addons-img" src="<?php echo WMC_URL .'/images/add-ons/'. $addon->image_url; ?>">
			</div>
			<?php if( $addon->video_url != '' ): ?>
			<div>
				<a class="wmc-addons-button wmc-addons-watch" href=""><?php echo esc_html('Watch', 'wmc')?></a>
			</div>
			<?php endif; ?>
		</div>
		<div class="wmc-addons-banner-block-item-content">
			<h3><?php echo esc_html__( $addon->name, 'wmc' );?></h3>
			<p><?php echo esc_html__( $addon->description, 'wmc'); ?></p>
		</div>
		<div class="wmc-addon-inner">
			<div class="wmc-addon-price">
				<?php echo $addon->price; ?>
			</div>
			<div>
				<?php 
				$filename = ABSPATH . 'wp-content/plugins/'.$plugin_file;
				if (file_exists($filename) && !is_plugin_active( $plugin_file ) ) {
					$plugin_slug = trim($addon->slug);
					$plugin_active_link = add_query_arg(
						array(
							'page'	=> 'wc_referral',
							'tab'	=> 'addons',
							'plugin'=> $plugin_file,
							'plugin_slug' => $plugin_slug,
							'wmc_addons_active_nonce' => wp_create_nonce( $plugin_slug )
						),
						admin_url( 'admin.php' )
					);
					?>
					<a class="wmc-addons-button wmc-addons-button-activate"  href="<?php echo $plugin_active_link; ?>"><?php _e('Activate', 'wmc');?></a>
					<?php
				}else if ( is_plugin_active( $plugin_file ) ) {
					/*
					$licence_validation_remaining has added for future purpose. Once we complete licence validation functionality, we can remove this. 
					*/ 
    				?>
    				<?php if ( !$licence && isset( $licence_validation_remaining ) ): ?>
    				<form name="wmc-addons-form" method="post">
						<?php wp_nonce_field( $addon->slug, 'wmc_addons_nonce' ); ?>
						<h2><?php _e( 'Licence Key', 'wmc' ); ?>:</h2>
						<input type="text" name="wmc-addon-license-key">
						<input type="button" class="wmc-addons-button-activate" value="<?php _e( 'Verify', 'wmc' ); ?>">
					</form>
					<?php else: ?>
						<input type="button" class="wmc-addons-button-activate" value="<?php _e( 'Activated', 'wmc' ); ?>">
    				<?php
    				endif;	
				}else{	 
				?>
				<a class="wmc-addons-button addon-buy-now" target="_blank" href="<?php echo $addon->plugin_link; ?>"><?php echo esc_html__('Buy Now', 'wmc')?></a><br />
				<a class="wmc-addons-button addon-demo" target="_blank" href="<?php echo $addon->demo_link; ?>"><?php echo esc_html__('Demo', 'wmc')?></a>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>