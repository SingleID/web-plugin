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
 

define("LOGO_URL", 'http://www.singleid.com/img/logonew.png');	// must not be on HTTPS!
																// setup.php will change this for you. Trust me.
define("SITE_NAME", 'basic install');							// setup.php will change this for you. Trust me.
define("requested_data", '1');									// setup.php will change this for you. Trust me.
define("admin_contact", ''); 		// You have to set this field only if requested_data is different from "1"
define("STORAGE",'file');			// use files for temporary storage ( memcache or mysql will be included soon )
define("ACCEPT",'both');			// which profile we accept (allowed value are personal, business, both )
define("LANGUAGE",'en');

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


// ------------------------------ SENSITIVE ACCOUNT SECTION END ------------------------------





// YOU SHOULD NOT CHANGE THE VALUES BELOW 
$PWD_TEMP_FILES = 'CHANGE_THIS_WITH_RANDOM_(FIXED)_CHARS AT SETUP!'; // setup.php will change this for you. Trust me.

define("PATH",'userdata/');				// setup.php will change this for you. Trust me.

define("CONFIG_VERSION",'20150418'); 	// ABSOLUTELY DO NOT CHANGE THIS. It's used to help you to know when a git pull requires you to do some work.

?>
