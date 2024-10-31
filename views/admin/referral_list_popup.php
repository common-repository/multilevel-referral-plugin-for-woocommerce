<?php
if( !$referral_users ){
	return;
}
$count = 1;
echo '<ul>';
foreach( $referral_users as $referral_user ){
	$extend_list = '<li data-id="%d"><div><span class="%s">%s %s</span> (<span class="count">%d</span>)<a href="#" class="remove_referral_user">Remove</a></div></li>';
	if($referral_user->followers){
		$extend_list = '<li data-get="0" data-id="%d"><div><a class="get_referral_user" href="#"><span class="%s">%s %s</span> (<span class="count">%d</span>)</a><a href="#" class="remove_referral_user">Remove</a></div></li>';	
	}
	$inactive_class = '';
	if(!$referral_user->active){
		$inactive_class = 'in_active';
	}
        if(empty($referral_user->first_name) && empty($referral_user->last_name)){
            $number = get_user_meta($referral_user->user_id, 'billing_phone', true);
            echo sprintf($extend_list, $referral_user->user_id, $inactive_class, $number, "", $referral_user->followers );
        }else{
            echo sprintf($extend_list, $referral_user->user_id, $inactive_class, $referral_user->first_name, $referral_user->last_name, $referral_user->followers );
        }
}
echo '</ul>';
?>