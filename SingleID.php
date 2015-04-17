<?php
header("Access-Control-Allow-Origin: *");


/*
 * SingleID WEB PLUGIN -> https://github.com/SingleID/web-plugin/
 * 
 * To use the plugin on your site follow these step:
 * go to folder that contains your registration / identification form ( example form.php )
 * exec:
 * 
 * git clone https://github.com/SingleID/web-plugin.git
 * cd web-plugin
 * chmod 0777 userdata -R  // maybe 755 could be enough ? to do to check
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
 * <iframe src="web-plugin/SingleID.php?op=init" width="220" height="80" frameborder="0"></iframe>
 *
 * 
 *
 *
 */


if (file_exists( 'personal.conf.php')) {
	require('personal.conf.php'); 		// the only file that you can edit and that will be no replaced on your next git pull
	require('lib/password.php'); 		// needed for php =< 5.5 but >= 5.3.3
	require('MysqliDb.php');			
	require('SingleID_functions.php');
} else {
	die('<p style="font-family:arial;">Config file missing</p>');
}



// configuration version


// System checks

if (STORAGE == 'file') {
    if (!is_writable(PATH)) {
        error_log('no permission for '.PATH.'/ folder. TRY -> sudo chmod 0777 ' . PATH . ' -R ');
        die('<p style="font-family:verdana;">no write permission!</p>');
    }
}






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
			$securitydata = '<html><h1>Silence is gold</h1></html>';  	// absolutely prevent directory browsing!
			$fp           = fopen(PATH . 'index.html', 'w');
			fwrite($fp, $securitydata);
			fclose($fp);
		}
		
		if (!file_exists( PATH . '.htaccess')) {
			$securitydata = 'Options -Indexes';  						// absolutely prevent directory browsing!
			$fp           = fopen(PATH . '.htaccess', 'w');
			fwrite($fp, $securitydata);
			fclose($fp);
		}
		
		if (!file_exists( PATH . 'garbage.txt')) {
			$garbagedata = '
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quaesita enim virtus est, non quae relinqueret naturam, sed quae tueretur. Quare attende, quaeso. Ergo ita: non posse honeste vivi, nisi honeste vivatur? Itaque hic ipse iam pridem est reiectus;

Audax negotium, dicerem impudens, nisi hoc institutum postea translatum ad philosophos nostros esset. Duo Reges: constructio interrete. Facillimum id quidem est, inquam. At enim, qua in vita est aliquid mali, ea beata esse non potest. Atque ab his initiis profecti omnium virtutum et originem et progressionem persecuti sunt. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Sed plane dicit quod intellegit. Illa argumenta propria videamus, cur omnia sint paria peccata.

Immo videri fortasse. Philosophi autem in suis lectulis plerumque moriuntur. Qui autem esse poteris, nisi te amor ipse ceperit? Si mala non sunt, iacet omnis ratio Peripateticorum.

Habent enim et bene longam et satis litigiosam disputationem. Etenim semper illud extra est, quod arte comprehenditur. Prioris generis est docilitas, memoria; Hoc loco tenere se Triarius non potuit. Non est igitur voluptas bonum. Beatus sibi videtur esse moriens. Vitae autem degendae ratio maxime quidem illis placuit quieta.

Huius, Lyco, oratione locuples, rebus ipsis ielunior. Quid ergo aliud intellegetur nisi uti ne quae pars naturae neglegatur? Quarum ambarum rerum cum medicinam pollicetur, luxuriae licentiam pollicetur. Nemo igitur esse beatus potest. Quid ergo attinet gloriose loqui, nisi constanter loquare? Quod iam a me expectare noli.
';
			// when we need to rewrite a file... we try to overwrite it with garbage before delete it. could be useless.... it depends from OS and FS used
			$fp           = fopen('./'. PATH .'/garbage.txt', 'w');
			fwrite($fp, base64_encode(uniqid().$garbagedata));
			fclose($fp);
		}
		
		


		
		
	}
    
    
    if(function_exists('openssl_random_pseudo_bytes')) {
		$Bytes = openssl_random_pseudo_bytes(16, $strong);
	}
	
    if ($strong !== true) {
		die('<p style="font-family:verdana;">Use PHP >= 5.3 or Mcrypt extension</p>');
    }
	
	$_SESSION['SingleID']['hash'] = bin2hex($Bytes);
    
        if (!is_SingleID($_POST['single_id'])) {
			die('<p style="font-family:verdana;">Internal miscofinguration</p>');
		}
        
	$_SESSION['SingleID']['who'] = $_POST['single_id'];
        
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])){						// needed for cloudflare flexible ssl
			$root .= $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'; 				
		} else {															
			$root .= !empty($_SERVER['HTTPS']) ? "https://" : "http://";
		}
		
		if ($root == 'https://'){
			$ssl = 1;
		} else {
			$ssl = 0;
		}
        
	$protocol[1] = 'https';
	$protocol[0] = 'http';
        
        if (($ssl == 0) and (requested_data <> '1')){ 	// { is blocked ALSO server side }
            error_log('SSL needed! ' . $ssl); 			
            die('<p style="font-family:verdana;">SSL Misconfiguration</p>'); 			
        }
	
        
		if (requested_data == '1,4,6') {  	// Sensitive account
			
			$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
			
			if ($_POST['optionalAuth'] <> '[]') {
			
				require('GibberishAES.php');
				
					$options = Array(
					'cost' => 11,
					);
					
				$hashed_check = password_hash(md5($_POST['optionalAuth']), PASSWORD_BCRYPT, $options);
	
				$data = Array(
					'UTID' => $_SESSION['SingleID']['hash'],
					'bcrypted' => $hashed_check 
				);
				
				$db->insert($TABLE_LOG, $data);
    
				GibberishAES::size(256);
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
            'requested_data' 	=> requested_data,
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
            error_log('Request failed: ' . $ServerReply['PopupTitle'] . ' ' . $ServerReply['Popup']);
            die($ServerReply['PopupTitle']);
        }
        
        $_SESSION['SingleID']['counter'] = 0;
        
        die('100'); // js will use this code for refresh
        

} elseif (isset($_POST['gimmedetails'])) {
	
    // a Device is asking details about action to authorize
    
    if (!is_md5($_POST['UTID'])) {
        die('<p style="font-family:verdana;">Wrong data received!</p>');
    }
    
    
    
    // open output
	if (STORAGE == 'file') {
		
		require('GibberishAES.php');
		
		// decrypt the data with the server key
		$filetarget = './'. PATH . $_POST[UTID].'.auth.SingleID.txt';
		$fh = fopen($filetarget, 'r');
		$encdata = fread($fh, filesize($filetarget));
		fclose($fh);
		
		safe_delete($filetarget);
		
		$decrypted_data = GibberishAES::dec($encdata, $PWD_TEMP_FILES);
		
		$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
		$db->where ("SingleID", $_POST['SingleID']);
		$hashed_token = $db->getValue ($TABLE_TOKENS, 'hashedThirdFactor');
		
		// here we re-encrypt the data with the client key if the hash is correct !
		 	
			if (password_verify($_POST['SharedSecret'], $hashed_token)) {
				
				GibberishAES::size(256);    // Also 192, 128
				
				$encrypted_secret_string = GibberishAES::enc($decrypted_data, $_POST['SharedSecret']);
			
				die($encrypted_secret_string); // the device has the password to decrypt this
			
			} else {
				
				die('ko');

			}

	}
    
        
    
} elseif (isset($_POST['UTID'])) { // *MUST BE* after gimmedetails
	
	// an app has sent something
	
    $_POST=array_map("strip_tags",$_POST);
    
    if (!is_md5($_POST['UTID'])) {
        die('Wrong data received!');
    }
    
    if (requested_data == '1,4,6') {	// is an authorization confirmation?
		
		$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
		$db->where ('UTID', $_POST['UTID']);
		$hashed_token = $db->getValue ($TABLE_LOG, 'bcrypted');
			
			if (!password_verify($_POST['unc_hash'], $hashed_token)) {
				
				die('ko'); // the App hasn't decrypted the data, so WTF can authorize?
				error_log('plain text hash mismatch! :-/');
				
			}
			// Surely the user has correctly decrypted the data retrieved few moments ago
			// because the user has returned the md5 hash of the plain text
	}
    
    
    
    // we need remove from this folder all the files older than 3 minutes for security reason !!!
    if (STORAGE == 'file') {
        
        if ($handle = opendir(PATH)) {
            while (false !== ($file = readdir($handle))) {
                if ((time() - filectime(PATH . $file)) >= 180) {
                    if (preg_match('/\.SingleID.txt$/i', $file)) {
                        safe_delete( PATH . $file );
                    }
                }
            }
        }
       
        
        // we store the received data so the javascript could read them later from the browser
        $data = base64_encode(gzcompress(serialize($_POST), 9)); // we compress only to avoid some research with grep by script kiddies like you :-P
        $fp   = fopen(PATH . $_POST['UTID'] . '.SingleID.txt', 'w');
        fwrite($fp, $data);
        fclose($fp);
    }
    
    
    // This step overwrite any previous value founded into DB
    if (requested_data == '1,4,5') {


		if (is_SingleID($_POST['SingleID'])) {
			
			$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
			// we need to create a token because this is the first handshake for a sensitive account
			$hex_secret = create_and_store_random_password($_POST['SingleID']);
			
			die($hex_secret);

		}else{
			error_log('No SingleID detected');
			die('ko');
		}
	}
    
    die('ok');	// if not died before this is a good place for RIP ;-)
    

} elseif ($_REQUEST['op'] == 'getdata') {	// the js from desktop browser is checking for data (if the device has already replied)
    
    if (!is_md5($_SESSION['SingleID']['hash'])) {
        unset($_SESSION['SingleID']); // leave the system clean
        die('Wrong data to recover');
    }
    
    if (STORAGE == 'file') {
		$filename 			= $_SESSION['SingleID']['hash'] . '.SingleID.txt';
        $filetarget			= PATH . $filename;
        $data				= unserialize(gzuncompress(base64_decode(file_get_contents($filetarget))));
    }
    // if this value is set to 1
    // The javascript knows that is not a form filling example but the data has been processed from PHP
    
    // parse the received array and add some data if needed
    $data = singleid_parse_profile($data, ACCEPT);
    
    
    if (isset($data['ALREADY_REGISTERED'])){
		// how is possible that the value is already SET? Someone hopes to be lucky?
		unset($_SESSION['SingleID']);
		safe_delete($filename); // just to be sure
        die('Wrong 318');
	}
	
	
   
				if (requested_data == '1,4,6') { // we rely on the previous md5 check

					$data['ALREADY_REGISTERED'] = 1; // force the refresh via js
					$data['Refresh_Page']		= 0; // remove refresh
					$data['Bypass_Auth']		= 0; // do not exec code for auth
					$_SESSION['good'] 			= true; // temp code for form #6
					print json_encode($data);
					die();
				
				}else{

					$data['ALREADY_REGISTERED'] = 0; // if set to 1 the JS will not try to populate a form
					$data['Bypass_Auth']        = 1; // if set to 1 the php code with the query will not be executed
				}
    
    
    if ($data['Bypass_Auth'] == 1) { // do sometimes we do not want to exec this block
		
			// here is the crucial point
			// a device has sent the data to the iframe so we can do a lot of thing
			// the flow should be the following
			// if you want to hack this system read this carefully :-P
	
		if (!is_SingleID($_SESSION['SingleID']['who'])) {
			die('Internal miscofinguration :: '.$_SESSION['SingleID']['who']);
		}else{
			$db = new Mysqlidb ($HOST, $USER, $PASS, $DB);
		}
			
			require('SingleID_auth.php');	// this code is specific for each user
			
			if (Is_this_SingleID_already_present($db, $_SESSION['SingleID']['who'], $TABLE_USER) === true){
					
				if (Is_this_user_enabled($db, $who, $TABLE_USER) === true){
					
					update_the_user_data($db, $_SESSION['SingleID']['who'],$data);
					
					// give the data array to this function that give back the modified values
					$data = user_is_logged($db, $_SESSION['SingleID']['who'], $TABLE_USER, $data);
					
				} else {
					
					display_error_mex($data);
				
				}
				
			}else{
			
				die('user not found');
			
			}
			
        
    }
    // Printing the data (received) the js/plugin.js will fill the form.
    
    print json_encode($data); // redirect to
    
    
    
    // See if it exists before attempting deletion on it
    if (file_exists($filetarget)) {
		
        safe_delete($filename); // Delete now
        
        unset($_SESSION['SingleID']); // leave the system clean for better security
        
    }
    
    
    die('ok');
    
    
    
} elseif ($_REQUEST['op'] == 'refresh') {
    
    // request made from javascript that is refreshing the iframe
    
    $_SESSION['SingleID']['counter']++;
    
    $file = PATH . $_SESSION['SingleID']['hash'] . '.SingleID.txt';
    
    if ($_SESSION['SingleID']['counter'] > 120) {
        die('400'); // too much time is passed
        // the output number code are inspired from the http status code and are read by the .js
    } else if (is_file($file)) { // the file exist !
        die('200'); // the post data has been received from the device so we launch the JS to populate the fields
    } else {
        die('100');	// continue... misuse as refresh
        
    }

}


// YOU SHOULD NOT BE HERE!
echo '<p>Ops: This script must be embedded into a page to work correctly</p>';





function is_SingleID($val) {
    return (bool) preg_match("/[0-9a-f]{8}$/i", $val);
}

function is_md5($val) {
    return (bool) preg_match("/[0-9a-f]{32}$/i", $val);
}


?>
