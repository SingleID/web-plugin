<?php
header("Access-Control-Allow-Origin: *");


/*
 * SingleID WEB PLUGIN -> https://github.com/SingleID/web-plugin/
 * Date: 2015-02 from SingleID Inc.
 * 
 * To use the plugin on your site please upload this file to your web root directory.
 * You must have jQuery on your site. You can install jquery by adding these line to your head:
 * <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
 *
 * The next step is to insert the SingleId Button. Place this line of code to the place of your site where you want to place the button:
 * <iframe src="SingleID.php?op=init" width="270" height="80" frameborder="0"></iframe>
 *
 * On line 26 and 27 you must use your site logo and site name.
 * On line 28 you can change the data that you want to receive
 * <option value="1">Personal data only</option>
	<option value="1,2,3">Personal, Billing and Shipping data</option>
	<option value="1,-2,3">Personal, Billing and Shipping data ( Without credit card ) </option>
	<option value="1,2,3,4">Personal, Billing, Shipping and Identification data</option>
	<option value="1,-2,3,4">Personal, Billing ( Without credit card ), Shipping and Identification data</option>
 *
 *
 * In the directory where you place plugin.php you must make a dir called "userdata" and make it writeable.
 *
 *
 */
 
 
 
define("LOGO_URL", "http://avatars0.githubusercontent.com/u/10206030?v=3&s=40");
define("SITE_NAME", "GITHUB");
define("requested_data", "1");
define("billing_key", ""); 						// You have to request this key from www.singleid.com if requested_data is different from "1"
define("admin_contact", "");					// You have to set this field only if requested_data is different from "1"



// first of all

if (!is_writable('userdata/')) {
	error_log('no permission for userdata/ folder TRY -> sudo chmod 0777 userdata/ -R ');
	die('<p>no write permission!</p>');
}


// create some fake file in userdata 


	session_start();

	if (isset($_POST['UTID'])) { // to change !
		$op = 'response'; // When some device is sending data !
	} else {
		$op = $_REQUEST['op'];
	}


if ($op == 'init') { // Where all begin ( from browser user )
		error_log('button is displayed');
		

		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>SingleID iframed button</title>
				<meta charset="utf-8">
				<link rel="stylesheet" href="css/SingleID/SingleID.css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>				
				<script src="js/plugin.js"></script>
			</head>
			<body>
		   		<div class="singleid_button_wrap singleid_pointer">
	        	    <div class="single_text_single_id">login with your SingleID</div>
	                <div class="icon_box_single_id"><img src="css/SingleID/SingleID_logo_key.jpg" alt="No more form filling with SingleID" /></div>

	            	<div class="white_back_single_id singleid_invisible">
	                	<input class="singleid_styled_input" name="SingleID" type="text" value="" maxlength="8" />
	                	<button type="button" class="icon_box_go" onClick="sid_sendData();">go</button>
	            	</div>
	                <div class="singleid_waiting singleid_invisible">waiting for data</div>
				    <a href="https://www.singleid.com" target="_top" title="SingleID is available for Android, iPhone and Windows Phone"><div class="free_text_single_id">Get SingleID now!</div>
					</a>
			    </div>
			   </body>
			   <script>
			   $(function() {

			        $(".singleid_button_wrap").bind("click", function() {
			              $(".icon_box_single_id, .icon_box_single_id img").fadeOut(50);
			              $(".icon_box_single_id").queue(function(next){
			                 $(this).addClass("singleid_invisible");
			              });
			              $(".single_text_single_id").queue(function(next){
			                 $(this).addClass("singleid_invisible");
			              });
			              $(".white_back_single_id").fadeIn('fast');
			              $(".icon_box_go").show('fast');
			              $(".singleid_styled_input").focus();

			              $('.singleid_styled_input').keypress(function(event) {
			                  if (event.keyCode == 13) {
			                	  sid_sendData();
			                  }
			              });
			        });


			    });
		        </script>
		</html>
		<?php
	
	die();
	
	
}elseif($op == 'send'){ 	// From browser 
							// here start the request to forward 
							
	$securitydata = '<html><h1>Silence is gold</h1></html>';	 // just to be extra sure that nobody could browse this folder that form some minutes is full of sensitive data
	$fp = fopen('userdata/index.html', 'w');
	fwrite($fp, $securitydata);
	fclose($fp);
							
							
	error_log('user has clicked go');
	
	$_SESSION['SingleID']['hash'] = md5( microtime().md5($_SERVER['HTTP_USER_AGENT'].mt_rand(1, mt_getrandmax())).$_SERVER['REMOTE_ADDR'].$_SERVER['SCRIPT_FILENAME'].mt_rand(1, mt_getrandmax()) );
	$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['has_response'] = 0;
	$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] = 0;
		
		
		

	if (is_SingleID($_POST['single_id'])) {
		
		$single_id = $_POST['single_id'];
		$_SESSION['SingleID']['who'] = $single_id;

		if ($single_id) { // redundant
			
			$ssl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1:0;
			$protocol[1] = 'https';
			$protocol[0] = 'http';
			
				// fix 2015-01-27
				if ($ssl == 0 and requested_data <> '1'){
					error_log('send 2 '.$ssl); // in questo caso devo bloccare il pulsante !!!! [TODO]
				}
			
			//error_log('AFTERIF requested_data='.requested_data.'||| ssl'.$ssl);	
			//set POST variables
			$url = 'https://app.singleid.com/';

			$fields = array(
				'SingleID' 			=> urlencode($single_id), // the value typed in the button ( 8 hex char string )
				'UTID' 				=> urlencode($_SESSION['SingleID']['hash']), // MUST BE AN MD5 HASH or a 32 hex char string
				'logo_url' 			=> LOGO_URL, // the img that will be displayed on the user device
				'name' 				=> SITE_NAME, // website name
				'requested_data' 	=> requested_data, // see note 1 below
				'ssl' 				=> $ssl,
				'url_waiting_data'	=> $protocol[$ssl].'://'. $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"],
				'ACTION_ID' 		=> "askfordata"
			);

				//url-ify the data for the POST
				foreach($fields as $key=>$value) {
					$fields_string .= $key.'='.$value.'&'; // TODO TO CHECK 
				}
				rtrim($fields_string, '&');
			
		$ip = $_SERVER['REMOTE_ADDR'];



			$ip = filter_var($ip, FILTER_VALIDATE_IP);
			$ip = ($ip === false) ? '0.0.0.0' : $ip;


			$headers = array(
            		'Authorization: key=' . billing_key,
            		'Browser_ip: '. $ip
			);
			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
			
			//execute post
			$result = curl_exec($ch);
			error_log(curl_error($ch));
			$responseInfo = curl_getinfo($ch);

			$ServerReply = json_decode($result, true);


				// for debug purposes only
						/*
						if (empty($result)) {
							// some kind of an error happened
							die(curl_error($ch));
							curl_close($ch); // close cURL handler
						} else {
							$info = curl_getinfo($ch);
							curl_close($ch); // close cURL handler

							if (empty($info['http_code'])) {
									die("No HTTP code was returned");
							} else {
								// load the HTTP codes
								$http_codes = parse_ini_file("path/to/the/ini/file/I/pasted/above");
							   
								// echo results
								echo "The server responded: <br />";
								echo $info['http_code'] . " " . $http_codes[$info['http_code']];
							}

						}
						*/


			
			curl_close($ch); //close connection because we are brave 

			$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] = time();
		}
		
			if ($ServerReply['Reply'] <> 'ok') {
			error_log($ServerReply['PopupTitle'].' '. $ServerReply['Popup']);
			die($ServerReply['PopupTitle']);
			}

		die('500'); // if numeric is ok
	}else{
		die('not valid singleid'); // TODO TO CHECK 
	}
	
	
} elseif($op == 'response') {
	// This happen when a Device has sent something ( $_POST[UTID]) 
	if (!is_md5($_POST['UTID'])){
		die('Wrong data received!');
		unset($_SESSION['SingleID']); // leave the system clean for better security
	}
	
		// TODO we need also to remove from this folder all the files older than 5 minutes for security reason !!!
		$path = 'userdata/';
		  if ($handle = opendir($path)) {
			 while (false !== ($file = readdir($handle))) {
				if ((time()-filectime($path.$file)) >= 300) {
				   if (preg_match('/\.SingleID.txt$/i', $file)) {
					  unlink($path.$file);
				   }
				}
			 }
		   }
	   
	   
	   
	error_log('DEVICE_RESP'.serialize($_POST));

	if ($_POST['Ecom_payment_mode'] != 'paypal')	{
		$_POST['Ecom_payment_mode'] = $_POST['Ecom_payment_card_type'];
	}
	
	
	// so we need the write the received data
	$data = gzcompress(serialize($_POST), 9);	 // we compress only to avoid some research with grep from script kiddies:-/
	$fp = fopen('userdata/'.$_POST['UTID'].'.SingleID.txt', 'w');
	fwrite($fp, $data);
	fclose($fp);
	
	

	
	
	
} elseif($op == 'getdata') {
	
	if (!is_md5($_SESSION['SingleID']['hash'])){
		die('Wrong data to recover');
		unset($_SESSION['SingleID']); // leave the system clean for better security
	}
	
	error_log('getdata========'.$_SESSION['SingleID']['hash']);
	$filetarget = 'userdata/'.$_SESSION['SingleID']['hash'].'.SingleID.txt';
	$data = unserialize(gzuncompress(file_get_contents($filetarget)));
	$data['ALREADY_REGISTERED'] = 1; // to understand security of this...
	
									 // if this value is set to 1
									 // The javascript know that is not a form filling example but the data has been processed from PHP
	// start from here we need
	
	// but if I am already a registered user ?
	
	// TODO CHECK SINGLEID !
	
	
	
	 // CODE FOR WEBSITE OWNER  START =======

	
	/* data received with a personal profile 
			"Pers_title",
			"Pers_first_name",
			"Pers_middle_name",
			"Pers_last_name",
			"Pers_birthdate",
			"Pers_gender",
			"Pers_postal_street_line_1",
			"Pers_postal_street_line_2",
			"Pers_postal_street_line_3",
			"Pers_postal_city",
			"Pers_postal_postalcode",
			"Pers_postal_stateprov",
			"Pers_postal_countrycode",
			"Pers_telecom_fixed_phone",
			"Pers_telecom_mobile_phone",
			"Pers_first_email",
			"Pers_skype",
			"Pers_first_language",
			"Pers_second_language",
			"Pers_contact_preferred_mode",
			"Pers_newsletter_agree"
      */
      
      
                            
	// Possibilities
	
	// User not present in DB with SingleID and not present with the same Email !
		
		// we try to auto register if the profile contains a minimum of data
		
	
	// User not present in DB with SingleID BUT with email already present
	
		// The system should send a confirmation link to the email already present to enable the association from the user !
		
		
	// User present in DB with SingleID but account disabled
		
		// display error
	
	// User present in DB with SingleID 
	
		// which profile we accept ? (personal/companies) ?
		
		// We need to launch an update query of the user data ( if met the minimum profile data request )
			
			// warning we had not to replace the data that we do not the user could replace ! ( name / surname )
			
			// Warning... if the user now has just logged with a personal profile and then with a company profile ?
	
	


	// CODE FOR WEBSITE OWNER END =====
	
	
	
		// if i print the data received the js/plugin.js will fill a form.
	
	print json_encode($data); // qua redirect to

	
	
	
	// See if it exists before attempting deletion on it
	if (file_exists($filetarget)) {
    unlink($filetarget); // Delete now
    unset($_SESSION['SingleID']); // leave the system clean for better security

	} 
	// See if it exists again to be sure it was removed
	if (file_exists($filetarget)) {
		error_log('Problem deleting. Check your permission ! ' . $filetarget);
	}
	die;
	
	
	
} elseif($op == 'refresh') {
	
	// request made from browser !
	
	error_log('refresh');

	
	//if($_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] > 0)
	//{
		$file = 'userdata/'.$_SESSION['SingleID']['hash'].'.SingleID.txt';
		//$file300 = 'userdata/'.$_SESSION['SingleID']['hash'].'.300.SingleID.txt';
		//$dif = time() - $_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'];

		if($dif > 60){
			error_log('400-0000');
			print 400; // too much time is passed
			
		}else if( is_file($file) ){ // the file exist !

				error_log('DEBUG: DATA RECEIVED FROM DEVICE');
				print '200'; // the post data has been received from the device so we launch the JS to populate the fields
		}
		else
		{
			error_log('waiting for-1-0000');
			print '1';
		}
	die;
}


// debug info
echo '<p>This script must be embedded into a page to work correctly</p>';






function is_SingleID($val){
	if (strlen($val) == '8'){  
		return (bool)preg_match("/[0-9a-f]{8}/i", $val);
		}else if (strlen($val) == '7'){  	// crockford mode base 32
		return (bool)preg_match("/[0-9a-z]{7}/i", $val);
		}else{
		return false;
	}
}

function is_md5($val) {
	return (bool)preg_match("/[0-9a-f]{32}/i", $val);
}
  
  
?>
