<?php

define("SETUP_CONFIG",'20150418');



if (php_sapi_name() == "cli") {
    
    fwrite(STDOUT, PHP_EOL."\033[32mWelcome to SingleID web-plugin setup!
\033[0m____________________________________________________".PHP_EOL.PHP_EOL);
    
} else {
	
    die('<h1>Sorry. Exec this php from command line.</h1>');
    
}







if (file_exists( __DIR__ . '/personal.conf.php')) {
	
	
	fwrite(STDOUT, PHP_EOL."\033[31m Configuration File already founded!".PHP_EOL." Do you want to delete it? (y/n) \033[0m".PHP_EOL);
	$answer 	= fgets(STDIN);
	
	
	// TODO check for release version of the founded file!
	
	if ($answer[0] == 'y'){
		
		unlink(__DIR__ . '/personal.conf.php');
		fwrite(STDOUT, PHP_EOL."Previous configuration file deleted succesfully.".PHP_EOL);
		
	}else{

		fwrite(STDOUT, PHP_EOL."Configuration file not modified! Bye".PHP_EOL);
		die();
	
	}
	
}
	



$create_new = file_get_contents('lib/official.examples/SingleID.conf.php');
    			
// create random value for PATH value
$Bytes 			= openssl_random_pseudo_bytes(8, $strong);
$rndval 		= 'userdata_'.bin2hex($Bytes);
$create_new  	= str_replace('userdata/', $rndval.'/', $create_new);

	if (!is_dir($rndval)) {
		mkdir( $rndval, 0777, true);   
	}



	// this step is for extra security. If you really know what are you doing you can remove
	// just to be extra sure that nobody could browse this folder that for some minutes could be full of sensitive data
	if (!file_exists(__DIR__ . '/' . $rndval . '/index.html')) {
		$securitydata = '<html><h1>Silence is gold</h1></html>';  	// absolutely prevent directory browsing!
		$fp           = fopen($rndval . '/index.html', 'w');
		fwrite($fp, $securitydata);
		fclose($fp);
	}

	if (!file_exists(__DIR__ . '/' . $rndval . '/.htaccess')) {
		$securitydata = 'Options -Indexes';  						// absolutely prevent directory browsing!
		$fp           = fopen($rndval . '/.htaccess', 'w');
		fwrite($fp, $securitydata);
		fclose($fp);
	}


	if (!file_exists(__DIR__ . '/' .$rndval . '/garbage.txt')) {
		
		$garbagedata = bin2hex(openssl_random_pseudo_bytes(1024));

		// We try to overwrite the file with garbage before deleting it. 
		// could be useless because it depends from OS, FS and storage type used but... why not?
		
		$fp           = fopen($rndval . '/garbage.txt', 'w');
		fwrite($fp, base64_encode($garbagedata));
		fclose($fp);
				
	}	



// create random value for PATH value
$Bytes 			= openssl_random_pseudo_bytes(32, $strong);
$rndval 		= bin2hex($Bytes);
$create_new  	= str_replace('CHANGE_THIS_WITH_RANDOM_(FIXED)_CHARS AT SETUP!', $rndval, $create_new);

// params that should be entered during setup!

/*
type of data that you want to require
* 
* 
define("LOGO_URL", 'http://www.img-bahn.de/es/v1504/img/logo-db-bahn.png');	// must not be on HTTPS!
define("SITE_NAME", 'DB Bahn');
define("requested_data", '1,2,3');
define("admin_contact", ''); 		// You have to set this field only if requested_data is different from "1"
define("STORAGE",'file');			// use files for temporary storage ( memcache or mysql will be included soon )
define("ACCEPT",'both');			// which profile we accept (allowed value are personal, business, both )
define("LANGUAGE",'en');

*/


retry_name:
fwrite(STDOUT, "Enter Short Site Name:".PHP_EOL);
$sitename 	= substr(fgets(STDIN),0,-1);

if (trim($sitename) == ''){
	goto retry_name;
}

$create_new  	= str_replace('basic install', $sitename, $create_new);

fwrite(STDOUT, "Enter LOGO URL
\033[31mRemember:

\033[0m1) NO HTTPS
2) PNG only
3) On the same domain of your website
4) No more than 40kb!
5) Resource must be accessible from internet

example ( www.singleid.com/img/logonew.png )
".PHP_EOL);

retry_url:

fwrite(STDOUT, "http://");


$logourl 		= "http://".substr(fgets(STDIN),0,-1);

if (trim($logourl) == ''){
	fwrite(STDOUT, "\033[31mNO URL INSERTED!".PHP_EOL."\033[0m");
	goto retry_url;
}else{
	
	if (filter_var($logourl, FILTER_VALIDATE_URL)){ 
	  // seems ok
	}else{
		fwrite(STDOUT, "\033[31mWRONG URL INSERTED!".PHP_EOL."\033[0m");

		goto retry_url;
	}
}

$create_new  	= str_replace('http://www.singleid.com/img/logonew.png', $logourl, $create_new);




$fp           	= fopen( 'personal.conf.php', 'w'); 	// configuration file has been written with some random value. Good job bro'
fwrite($fp, $create_new);
fclose($fp);




/*
// TODO check if the file has the right IDs into the form
$inputgrep = file_get_contents('../index.html');
die(strip_tags($inputgrep,'<input>'));
*/


if (!file_exists( 'personal.auth.php')) {
	
$create_new 	= file_get_contents( __DIR__ . '/lib/official.examples/SingleID.auth.php');
    
$fpp           	= fopen('personal.auth.php', 'w');
fwrite($fpp, $create_new);
fclose($fpp);

}
    fwrite(STDOUT,  PHP_EOL."\033[32mConfiguration done!
\033[0m____________________________________________________".PHP_EOL.PHP_EOL);
    
    
    fwrite(STDOUT,  PHP_EOL."\033[0m
You must have jQuery on the page that embed this script.
You can install jQuery by adding these line inside the head section

\033[1m<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js\"></script>\033[0m

Finally insert the SingleID Button. 
Place this line of code where you want button:

\033[1m<iframe src=\"web-plugin/SingleID.php?op=init\" width=\"220\" height=\"80\" frameborder=\"0\"></iframe>\033[0m".PHP_EOL);






    fwrite(STDOUT, "\033[0m".PHP_EOL.PHP_EOL);

?>
