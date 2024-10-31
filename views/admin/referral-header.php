<div class="wrap">

	<h1><?php echo __('Multilevel Referral Affiliate Plugin for WooCommerce', 'wmc')?></h1>

	<div id="referral_program_statistics">

		<div class="total_users_panel">

			<div class="icon">

				<span class="dashicons dashicons-groups"></span>	

			</div>

			<div class="number"><?php echo $data['total_users'];?></div>

			<div class="text"><?php echo __('Total Users','wmc');?></div>

		</div>

		<div class="total_referral_panel">

			<div class="icon">

				<span class="dashicons dashicons-networking"></span>	

			</div>

			<div class="number"><?php echo $data['total_referrals'];?></div>

			<div class="text"><?php echo __('Referrals','wmc');?></div>

		</div>

		<div class="total_earn_panel">

			<div class="icon">

				<span class="dashicons dashicons-download"></span>	

			</div>

			<div class="number"><?php echo $data['total_credites'];?></div>

			<div class="text"><?php echo __('Earned Credits','wmc');?></div>

		</div>

		<div class="total_redeem_panel">

			<div class="icon">

				<span class="dashicons dashicons-upload"></span>	

			</div>

			<div class="number"><?php echo $data['total_redeems'];?></div>

			<div class="text"><?php echo __('Redeemed Credits','wmc');?></div>

		</div>

	</div>

	
<div class="wmc_header_tabs" id="wmc_header_tabs">
	<div class="scroller scroller-left"><span class="dashicons dashicons-arrow-left-alt2"></span></div>
  	<div class="scroller scroller-right"><span class="dashicons dashicons-arrow-right-alt2"></span></div>
<h2 class="nav-tab-wrapper">	

    <a href="<?php echo admin_url('admin.php?page=wc_referral'); ?>" title="<?php echo __('Referral users','wmc');?>" class="nav-tab <?php echo !isset($_GET['tab']) ? 'nav-tab-active' : ''; ?>"><?php echo __('Referral users','wmc');?></a>

	<a href="<?php echo admin_url('admin.php?page=wc_referral&tab=orderwise_credits'); ?>" title="<?php echo __('Orderwise user credits','wmc');?>" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'orderwise_credits' ? 'nav-tab-active' : ''; ?>"><?php echo __('Orderwise user credits','wmc');?></a>

	<a href="<?php echo admin_url('admin.php?page=wc_referral&tab=credit_logs'); ?>" title="<?php echo __('Point logs','wmc');?>" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'credit_logs' ? 'nav-tab-active' : ''; ?>"><?php echo __('Point logs','wmc');?></a>

	<!--a href="<?php echo admin_url('admin.php?page=wc_referral&tab=withdraw_history'); ?>" title="<?php echo __('Withdraw History','wmc');?>" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'withdraw_history' ? 'nav-tab-active' : ''; ?>"><?php echo __('Withdraw History','wmc');?></a-->

	<a href="<?php echo admin_url('admin.php?page=wc_referral&tab=email_templates'); ?>" title="<?php echo __('Email templates','wmc');?>" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'email_templates' ? 'nav-tab-active' : ''; ?>"><?php echo __('Email templates','wmc');?></a>

    <a href="<?php echo admin_url('edit.php?post_type=wmc-banner'); ?>" title="<?php echo __('Banners','wmc');?>" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'banners' ? 'nav-tab-active' : ''; ?>"><?php echo __('Banners','wmc');?></a>    

    <?php do_action('wmc_referral_header'); ?>

    <a href="<?php echo isset($data['advance_setting_link']) ? $data['advance_setting_link'] : '#' ; ?>" title="<?php echo __('Advance Settings','wmc') . (!isset($data['advance_setting_link']) ? ' ('.__('Premium Feature','wmc').')' : '' );?>" class="nav-tab advance_setting_link <?php echo isset($_GET['tab']) && $_GET['tab'] == 'advSettings' ? 'nav-tab-active' : ''; ?>"><?php echo __('Advance Settings','wmc');?></a>

    <a href="<?php echo isset($data['addons_link']) ? $data['addons_link'] : '#'; ?>" title="<?php echo __('Add-Ons','wmc') . (!isset($data['addons_link']) ? ' ('.__('Premium Feature','wmc').')' : '' ) ;?>" class="nav-tab addons_link <?php echo isset($_GET['tab']) && $_GET['tab'] == 'addons' ? 'nav-tab-active' : ''; ?>"><?php echo __('Add-Ons','wmc');?></a>
      
</h2>
</div>