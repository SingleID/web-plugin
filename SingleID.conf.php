<?php

/*
THIS PRELIMINARY PLUGIN IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
At this time it is being published for internal use only. Please DO NOT RELY
upon it until this notice has been removed. (Which should be soon!)
*/

/*

 <option value="1">Personal data only</option>
 <option value="1,2,3">Personal, Billing and Shipping data</option>
 <option value="1,-2,3">Personal, Billing and Shipping data ( Without credit card ) </option>
 <option value="1,2,3,4">Personal, Billing, Shipping and Identification data</option>
 <option value="1,-2,3,4">Personal, Billing ( Without credit card ), Shipping and Identification data</option>
 <option value="1,4,5">All data with a random password as final handshake</option>
 <option value="1,4,6">All data with the previous exchanged random password</option>

*/
 
 

define("LOGO_URL", 'http://www.singleid.com/img/money-icon.png');
define("SITE_NAME", 'Device Auth');
define("requested_data", '1,4,6');
define("billing_key", 'aba5399b8d4b172b3abec582743a9b637ac94e5ca19ea4dcb090ad0059598444'); 		// You have to request this key from www.singleid.com if requested_data is different from "1"
define("admin_contact", ''); 	// You have to set this field only if requested_data is different from "1"
define("STORAGE",'file');		// use files for temporary storage ( memcache or mysql will be included soon )
define("ACCEPT",'both');	// which profile we accept (allowed value are personal, business, both )
define("LANGUAGE",'en');
define("PATH",'userdata/');
// if you use Mysql as storage or if you use set "requested_data" to 5 you need also to set the following var

$HOST 	= 'localhost';
$USER 	= 'root';
$PASS 	= 'password';
$DB 	= 'SingleID_users';

$PWD_TEMP_FILES = md5($_SERVER['SCRIPT_FILENAME'] . ' <--- CHANGE_THIS_WITH_RANDOM_(FIXED)_CHARS AT SETUP!');




?>
