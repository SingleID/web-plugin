<?php


/*

Requirements for Throw-Away accounts if you are interested only to form filling
PHP >= 5.3.7
a writable folder


Additional requirements:
for Throw-Away accounts if you are interested to recognize a previous user
for Sensitive Accounts


Mysql with two table

Possible values for requested_data

1 			= The App replies with personal/company data only. This is the only way to use SingleID without SSL.
1,2,3		= The App replies with Personal, Billing and Shipping data
1,-2,3		= The App replies with Personal, Billing and Shipping data (without credit card infos; useful if payment are processed by third party)
1,2,3,4		= The App replies with Personal, Billing, Shipping and Identification data
1,-2,3,4	= The App replies with Personal, Billing ( Without credit card ), Shipping and Identification data
1,4,5		= The App replies with Personal and Identification data. A password will be shared between device and your website
1,4,6		= The App replies with Personal and Identification data. All data with the previous exchanged random password

*/
 

define("LOGO_URL", 'http://www.singleid.com/img/money-icon.png');	// must not be on HTTPS!
define("SITE_NAME", 'Device Auth');
define("requested_data", '1,4,6');
define("admin_contact", ''); 		// You have to set this field only if requested_data is different from "1"
define("STORAGE",'file');			// use files for temporary storage ( memcache or mysql will be included soon )
define("ACCEPT",'both');			// which profile we accept (allowed value are personal, business, both )
define("LANGUAGE",'en');
define("PATH",'userdata/');			// Please change the default folder for storing temporary files

// Mysql section (if needed) (2FA?)
$HOST 			= 'localhost';
$USER 			= 'root';
$PASS 			= 'password';
$DB 			= 'SingleID_users';
$TABLE_USER		= 'changeme';



// ------------------------------ SENSITIVE ACCOUNT SECTION START ------------------------------
// if you set requested_data to 1,4,5 OR 1,4,6 you must to fill the following values 

define("billing_key", 'aba5399b8d4b172b3abec582743a9b637ac94e5ca19ea4dcb090ad0059598444'); 		// You have to request this key from www.singleid.com if requested_data is different from "1"

// have you filled Mysql Section above? Good!

$TABLE_TOKENS  	= 'SingleID_Tokens';
$TABLE_LOG  	= 'SingleID_log';

$PWD_TEMP_FILES = md5($_SERVER['SCRIPT_FILENAME'] . ' -> CHANGE_THIS_WITH_RANDOM_(FIXED)_CHARS AT SETUP!');

// ------------------------------ SENSITIVE ACCOUNT SECTION END ------------------------------



?>
