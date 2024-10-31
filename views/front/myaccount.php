<?php
//$referral_link	=	get_the_permalink( get_option('woocommerce_myaccount_page_id') );
//$referral_link 	=	add_query_arg('ru', $data['referral_code'], $referral_link );
?>
<div class="referral_program_details">
	<p class="hide">
	 <a href="#" class="button btn-invite-friends"><?php echo __('Invite Friends','wmc');?></a>
	 </p>
	<div id="dialog-invitation-form" class="<?php //echo $data['invitation_status']?>">
		<h2><?php __( 'Invite your friends', 'wmc' ); ?></h2>
		<!--center>
			<p><!--Enter email id for send invitation.<br/>
			<small>You can send request more than one using comma.</small>
			</p>
		</center-->
		
		<span><small><?php echo __('You can earn more credits by inviting more people to join this referral program. You can add comma separated list of emails below ...','wmc');?></small></span>
		<form method="post">
			<table class="shop_table shop_table_responsive">
				<tr>
					<td>
						<input type="text" name="emails"  class="input-text" value="<?php echo $data['emails']?>" placeholder="Ex. test@demo.com, test2@demo.com" />
					</td>
					<td width="30%">    
						<input type="submit" class="button btn-send-invitation" value="<?php echo __('Send Invitations','wmc');?>" />
						<input type="hidden" name="action" value="send_invitations" />
					</td>
				</tr>
			</table>
		</form>
	</div>
	<h2><?php __( 'Referral Program Details', 'wmc' ); ?></h2>
	<table class="shop_table shop_table_responsive my_account_orders">
		<tr>
			<th><?php __('Your Referral Code', 'wmc'); ?></th>	
			<th><?php __('Store Credits', 'wmc'); ?></th>	
			<th><?php __('Total Followers', 'wmc'); ?></th>	
		</tr>
		<tr>
			<td><?php echo $data['referral_code']; ?></td>
			<td><?php echo wc_price( $data['total_points'] ); ?></td>
			<td><?php __($data['total_followers']); ?></td>
		</tr>
	</table>
	<?php
		if( count($data['records']) > 0 ){
	?>
	<table class="shop_table shop_table_responsive my_account_orders">
		<tr>
			<!--th><?php esc_attr_e( 'Order', 'wmc' ); ?></th-->
			<th><?php __( 'Date', 'wmc' ); ?></th>
			<th><?php __( 'Note', 'wmc' ); ?></th>
		</tr>
		<?php
			foreach( $data['records'] as $row ){
				$note = '';
				$order = new WC_Order( $row['order_id'] );
				
				if( $row['credits'] > 0 ){
					$credits = wc_price( $row['credits'] );
					
					if( $order->user_id == $row['user_id'] ){
						if( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ){
							$note =  sprintf( __( '%s Store credit is refund for order %s.', 'wmc' ) ,$credits, '#'.$row['order_id'] );
						}else{
							$note =  sprintf( __( '%s Store credit is earned from order %s.', 'wmc' ) ,$credits, '#'.$row['order_id'] );
						}
					}else{
						$note = sprintf( __( '%s Store credit is earned through referral user ( %s order %s )  ', 'wmc' ) ,$credits, get_user_meta( $order->user_id, 'first_name', true) .' '. get_user_meta( $order->user_id, 'last_name', true), '#'.$row['order_id'] );	
					}
				}
				if( $row['redeems'] > 0 ){
					$redeems = wc_price( $row['redeems'] );
					
					if( $order->get_status() == 'cancelled' || $order->get_status() == 'refunded' || $order->get_status() == 'failed' ){
						$note =  sprintf( __( '%s Store credit is refund for order %s.', 'wmc' ) ,$redeems, '#'.$row['order_id'] );
					}else{
						if( $row['order_id'] ){
							$note = sprintf( __( '%s Store credit is used in order %s.', 'wmc' ), $redeems, '#'.$row['order_id'] ); 
						}else{
							$note = sprintf( __( '%s Store credit is expired.', 'wmc' ), $redeems ); 
						}
					}
				}
				echo '
						<tr>
							<!--td><a htref="">#'.$row['order_id'].'</a></td-->
							<td>'. date_i18n( 'M d, Y', strtotime( $row['date'] ) ) .'</td>
							<td>'.$note.'</td>
						</tr>';
			}
		?>
	</table>
	<?php
		}
	?>
</div>
