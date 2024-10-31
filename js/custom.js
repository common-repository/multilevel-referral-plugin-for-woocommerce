
jQuery(document).ready(function () {
    
        if(jQuery('#widraw_filter_id').length > 0){
            jQuery('#widraw_filter_id input[name="search_start_date"]').mask('0000/00/00');
            jQuery('#widraw_filter_id input[name="search_end_date"]').mask('0000/00/00');
        }
    

	jQuery('#widraw_filter_id input[name="search_start_date"]').datepicker({
		dateFormat: 'yy/mm/dd',
		todayHighlight: true,
	});
	jQuery('#widraw_filter_id input[name="search_end_date"]').datepicker({
		dateFormat: 'yy/mm/dd',
	});

    jQuery('#widraw_filter_id #reset_widthraw').click(function(){
    	 jQuery('#widraw_filter_id input[type=text]').val(''); 
       jQuery('#widraw_filter_id').submit(); 
    });
  //called when key is pressed in textbox
  	jQuery('input[name="paytm_id"]').keypress(function (e) {
     //if the letter is not digit then display error and don't type anything
    	if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
        	return false;
    	}
   	});
  	jQuery('input[name="paytm_amount"]').keypress(function (e) {
	

     //if the letter is not digit then display error and don't type anything
     	if (((event.which != 46 || (event.which == 46 && jQuery(this).val() == '')) ||
            jQuery(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        	return false;
    	}
   	});
   	jQuery('#reg_billing_phone').keypress(function (e) {
	
     //if the letter is not digit then display error and don't type anything
     	if (((event.which != 46 || (event.which == 46 && jQuery(this).val() == '')) ||
            jQuery(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
        	return false;
    	}
   	});
  	jQuery('input[name="paytm_amount"]').keyup(function(){
		var rate = jQuery('#conversion_rate_id').val();
		var symbal = jQuery('#conversion_rate_id').attr('data');
		var point = jQuery(this).val();
		jQuery('.show_amount').html(symbal+' '+ point * rate );
	});
});

jQuery(document).on('click','#add_account_detail',function(){
	if(jQuery(this).prop('checked') == true)
	{
		jQuery('.add_bank_details_div .fields_groups').show();
	}else{
		jQuery('.add_bank_details_div .fields_groups').hide();
	}
});
jQuery(document).ready(function(){
	jQuery('.saved_bank_details_info .remove_bank_info').click(function(e){
		e.preventDefault();
		var redirect = jQuery(this).attr('href');
		var input = confirm("Are you sure you want to remove?.");
		if(input)
		{
			window.location.href = redirect;
		}
	});
});
jQuery(document).on('click',"#bank_transfer_radio",function(){
	jQuery('.redeem_point_main').find('.content_payment').hide();
	jQuery(this).parents('.bank_tarnsfer').find('.content_payment').show();
});
jQuery(document).on('click',"#newpurchase_radio",function(){
	jQuery('.redeem_point_main').find('.content_payment').hide();
});
jQuery(document).on('click',"#paytm_radio",function(){
	jQuery('.redeem_point_main').find('.content_payment').hide();
	jQuery(this).parents('.paytm').find('.content_payment').show();
});

jQuery(document).on('change','#my-affilicate_filters' ,function(){
	var vals =  jQuery(this).val();
	var order = jQuery('#order_by_filter').val();
	var url = jQuery(this).attr('data_url');

	if(vals != '')
	{
		url = url + '?filter='+vals+'&orderby='+order;
	}

	// if(vals == 'last_month')
	// {
	// 	url = url + '?filter=month';
	// }else if(vals == 'last_quarter'){
	// 	url = url+ '?filter=3month';
	// }
	// else if(vals == 'last_year'){
	// 	url = url + '?filter=year';
	// }
	window.location.href=url;
});
jQuery(document).on('change','#order_by_filter' ,function(){
	var order =  jQuery(this).val();
	var date = jQuery('#my-affilicate_filters').val();
	var url = jQuery('#my-affilicate_filters').attr('data_url');

	if(order != '')
	{
		url = url + '?filter='+date+'&orderby='+order;
	}

	window.location.href=url;
});