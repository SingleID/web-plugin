<?php

define("SETUP_CONFIG",'20150418');



if (php_sapi_name() == "cli") {
    // In cli-mode
    
    fwrite(STDOUT, PHP_EOL."\033[32mWelcome to SingleID web-plugin setup!".PHP_EOL);
    fwrite(STDOUT, "\033[0m____________________________________________________".PHP_EOL.PHP_EOL);
    
} else {
	
    die('<h1>you can launch this php only from command line</h1>');
    
}




if (file_exists( __DIR__ . '/personal.conf.php')) {
	
	
	fwrite(STDERR, PHP_EOL."\033[31m Configuration File already founded!".PHP_EOL." Do you want to delete it? (y/n) \033[0m".PHP_EOL);
	$answer 	= fgets(STDIN);
	
	
	// TODO check for release version of the founded file!
	
	if ($answer == 'y'){
		
		unlink(__DIR__ . '/personal.conf.php');
		fwrite(STDOUT, PHP_EOL."Previous configuration file deleted succesfully.".PHP_EOL);
		
	}else{

		fwrite(STDOUT, PHP_EOL."Configuration file not modified! Bye".PHP_EOL);
		die();
	
	}
	
} else {
	



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

/*
$name = trim(shell_exec("read -p 'Enter your name: ' name\necho \$name"));
echo "Hello $name this is PHP speaking\n";
exit;
*/
retry_name:
fwrite(STDERR, "Enter Short Site Name:".PHP_EOL);
$sitename 	= fgets(STDIN);

if (trim($sitename) == ''){
	goto retry_name;
}

$create_new  	= str_replace('basic install', substr($sitename,0,-1), $create_new);



retry_url:
fwrite(STDOUT, "Enter logo URL".PHP_EOL);
fwrite(STDOUT, "\033[31mRemember:".PHP_EOL);
fwrite(STDOUT, "\033[0m 1) NO HTTPS".PHP_EOL);
fwrite(STDOUT, "2) PNG only".PHP_EOL);
fwrite(STDERR, "3) On the same domain of your website".PHP_EOL);
$logourl 		= fgets(STDIN);

if (trim($logourl) == ''){
	goto retry_url;
}

$create_new  	= str_replace('http://www.singleid.com/img/logonew.png', substr($logourl,0,-1), $create_new);




$fp           	= fopen( 'personal.conf.php', 'w'); 	// configuration file has been written with some random value. Good job bro'
fwrite($fp, $create_new);
fclose($fp);

}






if (!file_exists( 'personal.auth.php')) {
	
$create_new 	= file_get_contents( __DIR__ . '/lib/official.examples/SingleID.auth.php');
    
$fpp           	= fopen('personal.auth.php', 'w');
fwrite($fpp, $create_new);
fclose($fpp);

}
    fwrite(STDOUT,  PHP_EOL."\033[32mConfiguration done!".PHP_EOL);
    fwrite(STDOUT, "\033[0m".PHP_EOL.PHP_EOL);

?>
