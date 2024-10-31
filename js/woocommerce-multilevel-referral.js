
function checkReferralProgramValue($val) {
    switch ($val) {
        case "1":
            jQuery('.referral_terms_conditions').removeClass('hide');
            jQuery('.referral_code_panel').removeClass('hide');
            break;
        case "2":
            jQuery('.referral_terms_conditions').removeClass('hide');
            //jQuery('.referral_code_panel').val('');
            jQuery('.referral_code_panel').addClass('hide');
            break;
        case "3":
            jQuery('.referral_terms_conditions').addClass('hide');
            jQuery('.referral_code_panel').addClass('hide');
            break;
    }
}
jQuery(document).ready(function () {
    // Handle store credit limit
    jQuery('.store_credit_notice a').click(function (e) {
        e.preventDefault();
        jQuery(this).parent().siblings('form').toggle('fast');
    });

    if (jQuery('.woocommerce input[name="join_referral_program"]').length > 0) {
        checkReferralProgramValue(jQuery('.woocommerce input[name="join_referral_program"]:checked').val());
    }
    jQuery('body').on('click', 'input[name="join_referral_program"]', function (e) {
        checkReferralProgramValue(jQuery(this).val());
    });
    jQuery('body').on('change', 'input[name="join_referral_stage_one"]', function (e) {
        jQuery('#join_referral_stage_two_field').addClass('hide');
        jQuery('#referral_code_field').addClass('hide');
        jQuery('#termsandconditions_field').addClass('hide');
        jQuery('#join_referral_program').val(jQuery(this).val());
        if (jQuery(this).val() == 2) {
            jQuery('#join_referral_stage_two_field').removeClass('hide');

            if (jQuery('input[name="join_referral_stage_two"]:checked').val() == 2) {
                jQuery('#termsandconditions_field').removeClass('hide');
            }
            if (jQuery('input[name="join_referral_stage_two"]:checked').val() == 1) {
                jQuery('#referral_code_field').removeClass('hide');
                jQuery('#termsandconditions_field').removeClass('hide');
                jQuery('#join_referral_program').val(1);
            }
        }
    });
    jQuery('body').on('change', 'input[name="join_referral_stage_two"]', function (e) {
        jQuery('#referral_code_field').removeClass('hide');
        jQuery('#termsandconditions_field').removeClass('hide');
        jQuery('#join_referral_program').val(jQuery(this).val());
        if (jQuery(this).val() == 2) {
            jQuery('#referral_code_field').addClass('hide');
        }
    });
    jQuery('.btn-invite-friends').click(function (e) {
        e.preventDefault();
        jQuery('#dialog-invitation-form').toggleClass('hide');
    });
    jQuery(document).on('click', '.page-link.button', function (e) {
        e.preventDefault();
        var pageno = jQuery(this).attr("data-page");
        jQuery('.page-link.button').removeClass("current");
        jQuery(this).addClass("current");
        jQuery('.loader_main').show(); // Show the loader
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: wmcAjax.ajaxurl,
            data: { action: "wmcCreditLogPagination", pageno: pageno },
            success: function (response) {
                jQuery('.loader_main').hide(); // Hide the loader
                if (response) {
                    jQuery(".shop_table.my_account_orders tbody").html(response.data);
                    jQuery(".pagination").html(response.pagination);
                }
            }
        })
    });
    jQuery('.referral_program_stats a.copy_referral_link').click(function (e) {
        e.preventDefault();
        var $temp = jQuery("<input>");
        jQuery("body").append($temp);
        $temp.val(jQuery(this).attr('href')).select();
        document.execCommand("copy");
        $temp.remove();
        jQuery(this).html(jQuery(this).data('content'));
    });
    jQuery("#wmc-social-media .wmc-banner-list select").on('change', function () {
        var selectBox = jQuery(this);
        var optionSelected = selectBox.find("option:selected");
        selectBox.attr("disabled", true);
        var image = optionSelected.data('image');
        var attachId = optionSelected.data('attachid');
        var title = optionSelected.data('title');
        var desc = optionSelected.data('desc');
        var url = optionSelected.data('url');
        var fn = wmcAjax.URL + 'images/icons.png';
        jQuery('#wmc-social-media .wmc-banners #wmcBannerTitle').val(title);
        jQuery('#wmc-social-media .wmc-banners #wmcBannerDescription').val(desc);

        jQuery('#wmc-social-media .share42init').attr('data-url', url).attr('data-title', title).attr('data-description', desc);
        jQuery('#wmc-social-media .wmc-banner-preview img').attr("src", selectBox.data('loader'));
        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: wmcAjax.ajaxurl,
            data: { action: "wmcChangeBanner", attachId: attachId, bTitle: title, bDesc: desc },
            success: function (response) {
                if (response.type == "success") {
                    jQuery('#wmc-social-media .wmc-banner-preview').fadeOut(500, function () {
                        var source = response.imageURL;
                        jQuery('#wmc-social-media .share42init').attr('data-image', source);
                        jQuery('#wmc-social-media .wmc-banner-preview img').attr("src", source); jQuery('#wmc-social-media .wmc-banner-preview').fadeIn(500);
                        jQuery('#wmc-social-media .wmc-banner-preview img').attr("src", source);

                    });
                }
                selectBox.removeAttr("disabled");
            }
        })
        //}
    });
    jQuery('#wmc-social-media .wmcShareWrapper a').click(function (e) {
        e.preventDefault();
        var sharedButton = jQuery(this);
        var selectBox = jQuery("#wmc-social-media .wmc-banner-list select");
        var optionSelected = selectBox.find("option:selected");
        var cTitle = jQuery('#wmc-social-media #wmcBannerTitle').val();
        var cDesc = jQuery('#wmc-social-media #wmcBannerDescription').val();
        var image = jQuery('#wmc-social-media .wmc-banner-preview img').attr("src");
        var attachId = optionSelected.data('attachid');
        var title = optionSelected.data('title');
        var desc = optionSelected.data('desc');
        var url = optionSelected.data('url');
        cTitle = cTitle == '' ? title : cTitle;
        cDesc = cDesc == '' ? desc : cDesc;
        var shareURL = newWindow = '';
        if (!sharedButton.hasClass('wmc-button-whatsup')) {
            newWindow = window.open('', '_blank', 'scrollbars=0, resizable=1, menubar=0, left=100, top=100, width=550, height=440, toolbar=0, status=0');
        }
        switch (sharedButton.data('count')) {
            case 'fb':
                shareURL += '//www.facebook.com/sharer/sharer.php?display=popup&u=';
                //shareURL+='//www.facebook.com/dialog/share?app_id=1696793383871229&display=popup&href=';
                break;
            case 'gplus':
                shareURL += '//plus.google.com/share?url=';
                break;
            case 'lnkd':
                shareURL += '//www.linkedin.com/sharing/share-offsite/?url=';
                break;
            case 'pin':
                shareURL += '//pinterest.com/pin/create/button/?media=' + image + '&amp;description=' + cDesc + '&amp;url=';
                break;
            case 'twi':
                shareURL += '//twitter.com/intent/tweet?text=' + cTitle + '&amp;url=';
                break;
            case 'whatsup':
                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    shareURL += 'whatsapp://send?text=';
                } else {
                    shareURL += '//web.whatsapp.com/send?text=';
                }

                break;
        }
        if (sharedButton.hasClass('wmc-button-whatsup')) {
            url = sharedButton.data('account') + '?ru=' + sharedButton.data('ru') + '&title=' + encodeURI(cTitle) + '&content=' + encodeURI(cDesc) + '&image=' + encodeURI(image) + '&share=' + sharedButton.data('share');
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                shareURL += encodeURIComponent(url);
                //shareURL = 'whatsapp://send?text=http%3A%2F%2Fbaszicare.prismitsolutions.com%2Fmy-account%2Freferral%2F%3Fru%3D8c6d2%26title%3Dxxx';
                console.log(shareURL);
                sharedButton.attr('href', shareURL);
                window.location.href = shareURL;
            } else {
                shareURL = 'https://web.whatsapp.com/send?text=';
                shareURL += encodeURIComponent(url);
                jQuery('<a href="' + shareURL + '" target="_blank"></a>')[0].click();
            }
            return true;
        }
        shareURL += encodeURI(url);

        jQuery.ajax({
            type: "post",
            dataType: "json",
            url: wmcAjax.ajaxurl,
            data: { action: "wmcSaveTransientBanner", attachId: attachId, bTitle: cTitle, bDesc: cDesc },
            success: function (response) {
                if (response.type == "success") {
                    if (sharedButton.hasClass('wmc-button-whatsup')) {
                        sharedButton.attr('href', shareURL);
                        newWindow.location.href = shareURL;
                        return true;
                    } else {
                        newWindow.location.href = shareURL;
                        console.log(shareURL);
                        return false;
                    }
                }
            }
        });

    });
    jQuery('.wmc-show-affiliates a.view_hierarchie').on('click', function () {
        var parentID = jQuery(this).data('finder');
        if (jQuery(this).hasClass('wmcOpen')) {
            jQuery(this).removeClass('wmcOpen').addClass('wmcClose');
            jQuery('.wmc-show-affiliates').find('[class*=wmc-child-' + parentID + ']').hide();
            jQuery('.wmc-show-affiliates').find('[class*=wmc-child-' + parentID + '] a.view_hierarchie').removeClass('wmcOpen').addClass('wmcClose');
        } else {
            jQuery(this).removeClass('wmcClose').addClass('wmcOpen');
            jQuery('.wmc-show-affiliates .wmc-child-' + parentID).show();
        }
    });
    jQuery('.woocommerce-checkout select#join_referral_program').on('change', function () {
        var optionSelected = jQuery(this).find("option:selected");
        selectedValue = optionSelected.val();
        console.log(selectedValue);
        referralCode = jQuery('.woocommerce-checkout input#referral_code');
        if (selectedValue == 1) {
            if (referralCode.val() == '') {
                referralCode.closest('p').addClass('woocommerce-invalid');
            } else {
                referralCode.closest('p').removeClass('woocommerce-invalid').addClass('woocommerce-valid');
            }
            jQuery('.woocommerce-checkout #referral_code_field').show();
            jQuery('.woocommerce-checkout #termsandconditions_field').show();
            jQuery('.woocommerce-checkout #termsandconditions_field label.checkbox').removeClass('hidden');
        } else if (selectedValue == 2) {
            jQuery('.woocommerce-checkout #referral_code_field').hide();
            jQuery('.woocommerce-checkout #termsandconditions_field').show();
            jQuery('.woocommerce-checkout #termsandconditions_field label.checkbox').removeClass('hidden');
        } else if (selectedValue == 3) {
            jQuery('.woocommerce-checkout #referral_code_field').hide();
            jQuery('.woocommerce-checkout #termsandconditions_field').hide();
            jQuery('.woocommerce-checkout #termsandconditions_field label.checkbox').addClass('hidden');
        }
    });
    jQuery('.woocommerce-checkout select#join_referral_program').trigger("change");

    jQuery(document).on('change', '#my-affilicate_filters', function () {
        var vals = jQuery(this).val();
        var order = jQuery('#order_by_filter').val();
        var url = jQuery(this).attr('data_url');

        if (vals != '') {
            url = url + '?filter=' + vals + '&orderby=' + order;
        }

        window.location.href = url;
    });
});