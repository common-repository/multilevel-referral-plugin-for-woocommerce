=== Multilevel Referral Affiliate Plugin for WooCommerce ===
Plugin Name: Multilevel Referral Affiliate Plugin for WooCommerce
Plugin URI: http://referral.staging.prismitsystems.com
Contributors: prismitsystems, miteshsolanki, freemius
Donate link: http://www.prismitsystems.com/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: woocommerce, referral, multilevel users, credit points, redeemption, redeem Points, Invite, invite friends, earning, earn credit points, commision, join referral program 
Requires at least: 4.0
Requires PHP: 5.6
Tested up to: 6.6
Stable tag: 2.27

The most advanced referral Plugin for woocommerce users who wants to increase their sale within few days.

== Description ==

**Attract new customers, grow and market your business for free using a social referral program. Made especially for WooCommerce store owners, WooCommerce Multilevel Referral Plugin rewards your clients for sharing your website with their friends, family, and colleagues.**

One of the most recommended referral marketing programs on WooCommerce, it's really easy to configure and use. You can track the referrers, their sales, their total credits as well - along with redeemed credits to know your clientele well.

Moreover, you can even send emails to referral users reminding them of their referral points by that, appealing them into increasing their purchase. We thereby help you boost your sales by word-of-mouth as well as enticing the already registered to make them purchase more.

The referral plugin is compatible with the latest WordPress as well as WooCommerce versions.

= How does the Multilevel Referral Plugin work? =

* Your client will register on the website and join referral program receive a referral code.
* He/She will then share the code with his/her relatives and friends.
* Once his/her connection register on the website, join referral program using his/her code and purchases a product, he/she will be entitled to receive a referral reward.
* The more number of people are referred to in his/her hierarchy, the more his/her commission would be.

== Features == 

* Increase sales through a referral chain.
* User can set Store credit percentage globally.
* Users can earn credit points whilst their followers buy products from the existing online store.
* Redeem your points at the time of personal purchase.
* Customised email templates.
* Set Auto Join or Manual join option.
* See your earned point in account area.
* Invite friends through social media.
* Referrers can share predefined banners on social media for increasing their network.
* Admin can see a complete list of registered users who have joined their referral program with their earned credit points.
* The log is maintained for earned credits as well as redeemed points for each order. Admin can see this log within the Admin panel.

== Premium Features ==

* **Level Based Credit System** - Store owners can manage levelwise credit percentage globally (Applicable for all products), Category Wise and even product specific. 
* **Binary MLM System** - Added support for Binary MLM system, so site owner can choose regular or Binary MLM system base on their requirement.
* **Manage number of levels** - Store owners can define the number of referrer levels which the referrers would receive the credit points for.
From the admin panel, store owners can manage levels and their credit percentage.
* Ability to set validation period for credit points. After the validation period the credit points will expire.
* Admin can set number of days to send notification emails before the point expire. This will enable the user to have enough time for him/her to take appropriate action before the credits expire.
* Products can be excluded from the referral program.
* Admin can set monthly credit limit, and also there is an ability to set monthly redemption limit. 
* The multi level user list view is provided so; admin can see hierarchy of users.
* Added total volume of referees limitation.
* Managed the referral code required functionality for the auto-join referral program.
* Allowed welcome credits on customer registration.

== Add-on Plugins ==

**[Referral Payment Withdrawal Add-On](https://www.prismitworks.com/referral-payment-withdrawal-add-on-plugin)**

With this plugin, one can withdraw or encash his/her earned credit points (Accumulated by [The WooCommerce Multilevel Referral Affiliate Plugin](https://codecanyon.net/item/woocommerce-multilevel-referral-plugin/16993804)) by transferring them to their subsequent Paypal Accounts (globally).

**[Referral Credits Manager Add-On](https://www.prismitworks.com/woocommerce-multilevel-referral-credits-manager-add-on-plugin)**

With this plugin, one can only distribute a fixed percentage of credit points to their customers or can even assign different percentages for different referrers from each different level/s. This add-on will allow store owners to manually manage credit points of referral users registered with their subsequent e-store/s.


Interested in the premium features? You can read all about it [here](https://referral.staging.prismitsystems.com/documentation.html).

[Demo Plugin](http://referral.staging.prismitsystems.com/) 

Now credit point information and invite friend form can be show anywhere on wordpress pages. These shortcodes will show information of users who are logged in.

[wmc_invite_friends] =  this shortcode will show invite friend form.

[wmc_show_credit_info] = this will show logged in user credit point information.

== Screenshots ==
1. Listing of all referral users.
2. Referral plugin settings in woocommerce setting panel.
3. Point logs of all referral users.
4. You can set email content for various email templates.
5. Upload promotional banner from here.
6. User can see their credit points from user account. They can invite their friends by email address or sharing on social media.
7. Check this option to enable referral options on registration form.
8. Referral options on registration form.
9. Level Wise Credit System. (Premium Feature)
10. Multilevel Recursive Credit System. (Premium Feature)

== Installation ==
1. Upload the 'Multilevel Referral Affiliate Plugin for WooCommerce' plugin to the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.

== Changelog ==

= 2.27 =
* Feature - admin can add users to the referral program from the backend.
* Update - added pagination to the credit log points listing on the affiliates page.

= 2.26 =
* Fix - set default credit for fixed advance setting calculation.
* Update - add support for high-performance order storage.
* Update - add support to set custom affiliate percentages for specific users.

= 2.25 =
* Fix - fix JS issue on registration page
* Update - Update plugin license system

= 2.24 =
* Fix - Redeem credits issue

= 2.23 =
* Update - Update license system

= 2.22 =
* Update - Change plugin license system
* Fix - Blank value issue

= 2.21 =
* Update - Save templates.

= 2.20 =
* Update - Save referral code in a cookies for future registration.
* Update - Remove credit section in a cart/checkout page on max redeem credit to zero.
* Update - Allow credits up to 10,000 for fixed commission type.
* Update - Visual issue.
* Fix - Credit issue for fixed credit value type.
* Fix - Facebook sharing issue
* Fix - Validate wrong referral code from user dashboard
* Fix - Calculation of credit and redeem blocks.
* Fix - Allow float value in the category credit.

= 2.19 =
* Feature - Added total volume of referees limitation.
* Feature - Managed the referral code required functionality for the auto-join referral program.
* Feature - Added the ability for choosing credits distribution between percentage & fixed value.
* Feature - Allowed welcome credits on customer registration.
* New hook wmc_referral_tab_block - The user can add new block after referral statistics block on the front end.
* New hook wmc_registation_referral_fields - To hide referral program fields from a registered form
* New hook wmc_withdraw_credited - That adds support for The Referral Withdrawal and The Referral Credit Manager Add-on plugins.
* New hook wmc_withdraw_earned - That adds support for The Referral Withdrawal and The Referral Credit Manager Add-on plugins.
* New hook wmc_additional_commission_settings - The user can add additional setting for their requirements.
* Update - Removed commented code and regenerated language file for localisation.
* Update - Allowed NEGATIVE Total Credits.
* Fix - Addressed a Referral link issue on a user dashboard
* Fix - LinkedIn sharing issue.
* Fix - Difference of �Total Available Credits� in cart/checkout and referral page.
* Fix - Removed duplicate queries.
* Fix - Addressed a Visual issue
* Fix - Changed image resolution to resolve social sharing issue.
* Fix - Addressed Image cache issue for social sharing.

= 2.18 =
* Added hooks for add additional mail templates
* Added referral code filter in order listing page
* Changed referral registration flow in check out page
* Fixed auto join referral user issue
* Fixed display credits with points 
* Fixed warnings

= 2.17 =
* Added a copy referraral code from dashboard
* Added customer based bonus offers
* Added total volume of referees
* Added search by customer name/email to referral users panel
* Added search by customer name/order id/referral code to orderwise user credits panel
* Fixed multiple store credits for same order
* Fixed typo mistake and warnings

= 2.16 =
* Added a setting for referral users where a user will not gain any reward point without a purchase.
* Added a setting to select products to gain reward points.
* Fixed a mail class issue.
* Fixed a search based issue from the Referral user tab.
* Improved the UI layout on the front as well as on the admin panel.

= 2.15 =
* Fixed typo mistake and warnings.
* Added support for Binary MLM System.

= 2.14 =
* Fixed language translation issue.
* Changed minor backend layout.

= 2.13 =
* Improved code for the social media sharing banners. 
* Removed some issues. 

= 2.12 = 
* Remove bug from the admin referral listing. 
* Increase the length of the database fields to accept large values. 

= 2.11 =

* We added ability to add Referral code and link dynamically on Custom Banners. Prevously this feature was available for pre-defined banners. 

* The size of Social media sharing banners has been changed to 1200px*630px. 

* Store owners can design their own banners and upload. The referral code and link will be added dynamically on the banners on top and bottom respectively. Keep the 100px space on top and bottom for referral code and link.

= 2.10 =

* Added shortcode support for front end my account dashboard tabs "Referral" and "My Affiliates".

[wmc_my_referral_tab] : It will provide ability to invite peoples by email and social media sharing. 

[wmc_my_affiliate_tab] : It will show the list of referral's of logged in user. 

* Provided High resolution pre-designed banners.    

= 2.9 =

* Added option to control the distribution of credit points in Recursive Credit method. Admin can set number of levels which are entitled to receive the credit points from the settings.  

= 2.8 =

* Implemented AJAX technique to get followers/referral's count in the admin panel to improve the page performance.

= 2.7 =

* Fixed issues related to followers count. 

= 2.6 =

* Fixed Issue related to Envato API. 

* Upgraded to new Envato API to validate plugin. 

= 2.5.1 =

* Fixed bug in cart and checkout page related to redemption of credit point feature.

= 2.5 =

* Fixed bug permalink and social media share.

= 2.4 =

* Fixed bug with Auto join feature in checkout page.

= 2.3 =

* Fixed bug related to "My Affiliate" tab in Dashboard.

= 2.2 = 

* Fixed some minor bugs from the plugin.

= 2.1 =

* Fixed Issue in checkout form.

* Change the order of referral program fields on checkout page. 

= 2.0 =

* Level Based credit system

*# Previously, the credit percentile was fixed and was applicate globally. With this new version, we are now introducing and adding a completely new credit system as. Henceforth, the store owners will be able to assign variable percentile for each level of referrers individually, as well.

Store owners can manage levelwise credit percentage globally (Applicable for all products), Category Wise and even product specific. 

This version does not replace the old credit system and that too will be in working status. Apparently, now, we have 2 systems to choose from, Depending upon a subsequent criteria, store owners will now have the choice to choose either from - Level Based Credit System (LCS) or Recursive Credit System (RCS - old one). 

* Manage number of levels.

*# Store owners now can define the number of referrer levels which the referrers would receive the credit points for. 

From the admin panel, store owners can now manage levels and their credit percentage. 

* Social Media Sharing

*# We are happy to be introducing a social media sharing option as well for the referrers. 

Referrers can share predefined banners on social media for increasing their network. 

Visitors who follow or click a socially shared link, will join under the referrer�s account. The previous version only had an option to invite people to join referral program, only via email. But now, social media sharing is available, apparently opening one�s business up to a wider audience.


= 1.6 =
* Fixed - Fixed minor issues.

= 1.5 =
* Update - WooCommerce Templates.

= 1.4 =
* Fixed - Fixed minor issues.

= 1.3 = 

* New: Added the ability for choosing credits distribution between percentage & fixed value.
* New: Search by Customer name, Order ID and Referral code on Orderwise user credits page
* New: Search by Customer name, Order ID and Referral code on Point logs page
* Fix: Total Credits issue

= 1.2 =
* Fixed - Fixed minor issues.

= 1.1 =
* Fixed - Fixed type mistakes and wannings.
* New - Add Binay MLM support for pro version.

= 1.0 =
* Initial release.