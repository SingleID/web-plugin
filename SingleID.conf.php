<?php

/*
THIS PRELIMINARY PLUGIN IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
At this time it is being published for internal use only. Please DO NOT RELY
upon it until this notice has been removed. (Which should be soon!)
*/


define("LOGO_URL", 'http://www.vantax.eu/img_data/graph.png');
define("SITE_NAME", 'Simple Test');
define("requested_data", '1');
define("billing_key", 'aba5399b8d4b172b3abec582743a9b637ac94e5ca19ea4dcb090ad0059598444'); 		// You have to request this key from www.singleid.com if requested_data is different from "1"
define("admin_contact", ''); 	// You have to set this field only if requested_data is different from "1"
define("STORAGE",'file');		// use files for temporary storage ( memcache or mysql will be included soon )
define("ACCEPT",'personal');	// which profile we accept (allowed value are personal, business, both )
define("LANGUAGE",'en');
define("PATH",'userdata/');

// if you use Mysql as storage or if you use set "requested_data" to 5 you need also to set the following var

$HOST = 'localhost';
$USER = 'user';
$PASS = 'pass';
$DB = 'SingleID';




?>
