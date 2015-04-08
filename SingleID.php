<?php

/*
 * THIS CODE IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
 * It is provided for discussion only and may change at any moment. 
 * Don't cite this code other than as work in progress.
 * 
 */

header("Access-Control-Allow-Origin: *");


/*
 * SingleID WEB PLUGIN -> https://github.com/SingleID/web-plugin/
 * 
 * To use the plugin on your site follow these step:
 * go to the folder that contain your registration / identification form ( example form.php )
 * exec:
 * 
 * git clone https://github.com/SingleID/web-plugin/
 * cd web-plugin
 * mkdir userdata
 * chmod 0777 userdata -R
 * 
 * 
 * You must have jQuery on the page that embed this script.
 * You can install jQuery by adding these line to your head
 * 
 * <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
 *
 * 
 * The next step is to insert the SingleID Button. Place this line of code to the place of your site where you want to place the button:
 * 
 * <iframe src="web-plugin/SingleID.php?op=init" width="270" height="80" frameborder="0"></iframe>
 *
 * 
 *
 *
 */




require('SingleID.conf.php'); // the only file that you can edit and that will be no replaced on your next git pull
require('lib/password.php'); // for php < 5.5 but > 5.3.7




// before all
if (STORAGE == 'file') {
    if (!is_writable(PATH)) {
        error_log('no permission for userdata/ folder TRY -> sudo chmod 0777 ' . PATH . ' -R ');
        die('<p>no write permission!</p>');
    }
}


require('MysqliDb.php');
require('SingleID_functions.php');



define('SINGLEID_SERVER_URL','https://app.singleid.com/'); // don't change this

session_start();

if ($_REQUEST['op'] == 'init') { // Where all begin ( display the green button )
    
    die(print_login_button(LANGUAGE, requested_data));
    
} elseif ($_REQUEST['op'] == 'send') { // From browser (user has clicked go)
    // here start the request from the website to the SingleID Server
    
    if (STORAGE == 'file') {
		// this step is for extra security. If you really know what are you doing you can remove
		// just to be extra sure that nobody could browse this folder that for some minutes could be full of sensitive data
		if (!file_exists( PATH . 'index.html')) {
			// prevent directory browsing creating a fake file
			$securitydata = '<html><h1>Silence is gold</h1></html>';
			$fp           = fopen(PATH . 'index.html', 'w');
			fwrite($fp, $securitydata);
			fclose($fp);
		}
	}
    
    
    
    $Bytes = openssl_random_pseudo_bytes(16, $cstrong);
	$_SESSION['SingleID']['hash'] = bin2hex($Bytes);
    
        if (!is_SingleID($_POST['single_id'])) {
			die('Internal miscofinguration');
		}
        
        $_SESSION['SingleID']['who'] = $_POST['single_id'];
        
        
        
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){
			$root .= $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'; // needed for cloudflare flexible ssl
		}else{
			$root .= !empty($_SERVER['HTTPS']) ? "https://" : "http://";
		}
		
		if ($root == 'https://'){
			$ssl = 1;
		}else{
			$ssl = 0;
		}

        
        $protocol[1] = 'https';
        $protocol[0] = 'http';
        
        if (($ssl == 0) and (requested_data <> '1')){ // { TODO -> double check server side }
            error_log('SSL needed! ' . $ssl); // This will be correct very soon
            // die('Misconfiguration:'. $ssl); // TODO TO CHECK 
        }
	
        
        
        
        // sensitive account
        // TODO OVERWRITE ? ALWAYS for DEFAULT
        

		if (requested_data == '1,4,6') {
			$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
			// if requested_data == 1,4,6
			if ($_POST['optionalAuth'] <> '[]') { // TODO -> double check
				// This if will be defined in April
				// all what you need to know is that if you want to use requested_data 5 you need also a Mysql DB
				// $_POST['optionalAuth'] TODO must be encrypted with the third factor key of the user!
				// here we need to recover the password shared in a previous request
			
			//$db->where ("SingleID", $_POST['single_id']);
			//$ClearPassword = $db->getValue ('SingleID_Tokens', 'clearTextPassword');
			//error_log('yuhuuu: '.$ClearPassword);
			
			require('GibberishAES.php');


			// $old_key_size = GibberishAES::size();
			GibberishAES::size(256);    // Also 192, 128
			$encrypted_secret_string = GibberishAES::enc($_POST['optionalAuth'], $PWD_TEMP_FILES);

			$fileoutput = './'. PATH . $_SESSION['SingleID']['hash'] . '.auth.SingleID.txt';
						
			$afp = fopen($fileoutput, 'w');
			fwrite($afp, $encrypted_secret_string);
			fclose($afp);
				
			}
		}
        //set POST variables
        $fields_string = '';
        // url encode ?
        $fields = array(
            'SingleID' 			=> $_POST['single_id'], // the value typed in the button ( 8 hex char string )
            'UTID' 				=> $_SESSION['SingleID']['hash'], // MUST BE AN MD5 HASH or a 32 hex char string
            'logo_url' 			=> LOGO_URL, // the img that will be displayed on the user device
            'name' 				=> SITE_NAME, // website name
            'requested_data' 	=> requested_data, // see note 1 below
            'ssl' 				=> $ssl,
            'url_waiting_data' 	=> $protocol[$ssl] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"],
            'ACTION_ID' 		=> 'askfordata'
        );
        
        //url-ify the data for the POST
        foreach ($fields as $key => $value) { // todo and if a var contain a & ? DOUBLE CHECK HERE ASAP
            $fields_string .= $key . '=' . $value . '&'; // TODO TO CHECK 
        }
        rtrim($fields_string, '&');
        

        $ServerReply = send_request_to_singleid_server($fields,$fields_string);
        
        
        if ($ServerReply['Reply'] <> 'ok') {
            error_log($ServerReply['PopupTitle'] . ' ' . $ServerReply['Popup']);
            die($ServerReply['PopupTitle']);
        }
        
        $_SESSION['SingleID']['counter'] = 0;
        die('100'); // js will use this code for refresh
        

} elseif (isset($_POST['gimmedetails'])) { // TODO in April 2015
	

	
    // a Device is asking info a about the auth prog
    
    if (!is_md5($_POST['UTID'])) {
		unset($_SESSION['SingleID']); // leave the system clean for better security
        die('Wrong data received!');
    }
    
    
    
    // open output
	if (STORAGE == 'file') {
		
		require('GibberishAES.php');
		
		// decrypt the data with the server key
		$filetarget = './'. PATH . $_POST[UTID].'.auth.SingleID.txt';
		$fh = fopen($filetarget, 'r');
		$encdata = fread($fh, filesize($filetarget));
		fclose($fh);
		
		safe_delete($filetarget); // if i delete the files now... how can i check the md5 of the decrypted text ?
		
		$decrypted_data = GibberishAES::dec($encdata, $PWD_TEMP_FILES);
		
		$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
		$db->where ("SingleID", $_POST['SingleID']);
		$hashed_token = $db->getValue ('SingleID_Tokens', 'hashedThirdFactor');
		//re-encrypt the data with the client key if the hash is correct !
		if (password_verify($_POST['SharedSecret'], $hashed_token)) {
			
			// $old_key_size = GibberishAES::size();
			GibberishAES::size(256);    // Also 192, 128
			$encrypted_secret_string = GibberishAES::enc($decrypted_data, $_POST['SharedSecret']);
		
			die($encrypted_secret_string); // the device has the password to decrypt this
		
		} else {
			die('ko');
		}
	}
    
        
    
} elseif (isset($_POST['UTID'])) { // necessariamente dopo gimmedetails
    $_POST=array_map("strip_tags",$_POST);
    // an app has sent something
    if (!is_md5($_POST['UTID'])) {
        unset($_SESSION['SingleID']); // leave the system clean for better security
        die('Wrong data received!');
    }
    
   // if (isset($_POST['unc_hash'])){
		// this is the only proof that the client has decrypted the text correctly....
	// }
    
    
    if ($_POST['Ecom_payment_mode'] != 'paypal') { // WTF ? remove or optimize
        $_POST['Ecom_payment_mode'] = $_POST['Ecom_payment_card_type'];
    }
    
    
    
    // we need remove from this folder all the files older than 3 minutes for security reason !!!
    if (STORAGE == 'file') {
        
        if ($handle = opendir(PATH)) {
            while (false !== ($file = readdir($handle))) {
                if ((time() - filectime(PATH . $file)) >= 180) {
                    if (preg_match('/\.SingleID.txt$/i', $file)) {
                        //error_log('vorrei cancellare: '.$file);
                        safe_delete( PATH . $file );
                    }
                }
            }
        }
       
        
        // so we need the store the received data
        $data = base64_encode(gzcompress(serialize($_POST), 9)); // we compress only to avoid some research with grep from script kiddies like you
        $fp   = fopen(PATH . $_POST['UTID'] . '.SingleID.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);
    }
    
    
    // This step overwrite any previous value founded into DB
    if (requested_data == '1,4,5'){ // TODO in April 2015


		if (is_SingleID($_POST['SingleID'])){
			
			$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
			// we need to create a token because this is the first handshake for a sensitive account
			$hex_secret = create_and_store_random_password($_POST['SingleID']);
			
		
			die($hex_secret);
		}else{
			error_log('ops... missing something important right here');
		}
	}
    
    die('ok');	// if not died with create_and_store_random_password
    

} elseif ($_REQUEST['op'] == 'getdata') {	// the js from desktop browser is checking for data (if the device has already replied)
    
    if (!is_md5($_SESSION['SingleID']['hash'])) {
        unset($_SESSION['SingleID']); // leave the system clean
        die('Wrong data to recover');
    }
    
    if (STORAGE == 'file') {
        $filetarget           = PATH . $_SESSION['SingleID']['hash'] . '.SingleID.txt';
        $data                 = unserialize(gzuncompress(base64_decode(file_get_contents($filetarget))));
    //    $data['Refresh_Page'] = 1;
    }
    // if this value is set to 1
    // The javascript knows that is not a form filling example but the data has been processed from PHP
    // start from here we need
    
    // but if I am already a registered user ?
    
    
    // parse the received array and add some data if needed
    $data = singleid_parse_profile($data, ACCEPT);
    
    
    if (isset($data['ALREADY_REGISTERED'])){
		// how is possible that the value is already SET ?
		unset($_SESSION['SingleID']); // leave the system clean
        die('Wrong 318');
	}
	
	
    // if (isset($data['Refresh_Page'])){
	//	// how is possible ?
	//	unset($_SESSION['SingleID']); // leave the system clean
    //    die('Wrong 260');
	//}
	
	
	/* unuseless because singleid_parse_profile fill it
	 * 
	 * if (isset($data['Bypass_Auth'])){
		// how is possible ?
		unset($_SESSION['SingleID']); // leave the system clean
        die('Wrong 307');
	}*/
    
    
    
				// MANUAL SET
				if (requested_data == '1,4,6'){ // TODO which check ?
					// error_log('debug here 1,4,6');
					$data['ALREADY_REGISTERED'] = 1; // force the refresh via js
					$data['Refresh_Page']		= 0; // remove refresh
					$data['Bypass_Auth']		= 0; // do not exec code for auth
					$_SESSION['good'] 			= true; // temp code for form #6
					print json_encode($data);
					die();
				
				}else{
					
				//error_log('debug here bbb');
					// MANUAL SET
					$data['ALREADY_REGISTERED'] = 0; // if set to 1 the JS will not try to populate a form
					//$data['Refresh_Page']       = 0; // remove refresh
					$data['Bypass_Auth']        = 1; // if set to 1 the php code with the query will not be executed
				}
    
    if ($data['Bypass_Auth'] <> 1) { // do not exec code for auth
			// here is the crucial point
			// a device has sent the data to the iframe so we can do a lot of thing
			// the flow should be the following
			
			// is this SingleID already present in my user table ?
	
	
			require('SingleID_auth.php');
			
			if (Is_this_SingleID_already_present() == true){
				
					error_log('debug here 1,4,6');
					
				if (Is_this_user_enabled() == true){
					
					
					update_the_user_data();
					user_is_logged();
				
				}else{
					display_error_mex();
				}
				
			}else{
				
				if( Is_this_a_really_new_user_for_my_db == false){ // if a user is arelady registered ?
					display_error_mex();
				}else{
					
					// the email sent is not present in the DB. So is a new User !
					// we need to create a record about this new user
					create_the_user();	// according to request 5 and 6
					user_is_logged();	
					
				}
				
			}
			
        
    }
    // Printing the data (received) the js/plugin.js will fill the form.
    
    print json_encode($data); // redirect to
    
    
    
    
    // See if it exists before attempting deletion on it
    if (file_exists($filetarget)) {
        unlink($filetarget); // Delete now
        unset($_SESSION['SingleID']); // leave the system clean for better security
        
    }
    
    // See if it exists again to be sure it was removed // paranoid check that could be removed
    if (file_exists($filetarget)) {
        error_log('Problem deleting. Check your permission ! ' . $filetarget);
    }
    die;
    
    
    
} elseif ($_REQUEST['op'] == 'refresh') {
    
    // request made from javascript that is refreshing the iframe
    
    $_SESSION['SingleID']['counter']++;
    
    $file = PATH . $_SESSION['SingleID']['hash'] . '.SingleID.txt';
    
    if ($_SESSION['SingleID']['counter'] > 120) {
        die('400'); // too much time is passed
        // the output number code are inspired from the http status code
    } else if (is_file($file)) { // the file exist !
        die('200'); // the post data has been received from the device so we launch the JS to populate the fields
    } else {
        die('100');	// continue... misuse as refresh
        
    }

}


// debug info
echo '<p>Ops: This script must be embedded into a page to work correctly</p>';





function is_SingleID($val)
{
    return (bool) preg_match("/[0-9a-f]{8}$/i", $val);
}

function is_md5($val)
{
    return (bool) preg_match("/[0-9a-f]{32}$/i", $val);
}


?>
