<?php

/*
THIS PRELIMINARY PLUGIN IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
At this time it is being published for internal use only. Please DO NOT RELY
upon it until this notice has been removed. (Which should be soon!)
*/

header("Access-Control-Allow-Origin: *");


/*
 * SingleID WEB PLUGIN -> https://github.com/SingleID/web-plugin/
 * Date: 2015-03 from SingleID Inc.
 * 
 * To use the plugin on your site please upload this file to your web root directory.
 * You must have jQuery on your site. You can install jquery by adding these line to your head:
 * <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
 *
 * The next step is to insert the SingleID Button. Place this line of code to the place of your site where you want to place the button:
 * 
 * 
 * git clone https://github.com/SingleID/web-plugin/
 * 
 * 
 * <iframe src="web-plugin/SingleID.php?op=init" width="270" height="80" frameborder="0"></iframe>
 *
 * On 3 you must use your site logo and site name.
 * On line 45 you can change the data that you want to receive
 *  
 <option value="1">Personal data only</option>
 <option value="1,2,3">Personal, Billing and Shipping data</option>
 <option value="1,-2,3">Personal, Billing and Shipping data ( Without credit card ) </option>
 <option value="1,2,3,4">Personal, Billing, Shipping and Identification data</option>
 <option value="1,-2,3,4">Personal, Billing ( Without credit card ), Shipping and Identification data</option>
 <option value="5">All data with a random password as final handshake</option>
 <option value="6">All data with the previous exchanged random password</option>
 *
 *
 * In the directory where you place plugin.php you must make a dir called "userdata" and make it writeable.
 *
 *
 */

require_once('SingleID.conf.php'); // the only file that you can edit and that will be no replaced with git pull

// first check

if (!is_writable('userdata/')) {
				error_log('no permission for userdata/ folder TRY -> sudo chmod 0777 userdata/ -R ');
				die('<p>no write permission!</p>');
}


// create some fake file in userdata 


session_start();



if ($_REQUEST['op'] == 'init') { 	// Where all begin ( here we display the green button )
				
				
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
	        	    <div class="single_text_single_id">Login with your SingleID</div>
	                <div class="icon_box_single_id"><img src="css/SingleID/SingleID_logo_key.jpg" alt="No more form filling, no more password" /></div>

	            	<div class="white_back_single_id singleid_invisible">
	                	<input class="singleid_styled_input" name="SingleID" type="text" value="" maxlength="8" />
	                	<button type="button" class="icon_box_go" onClick="sid_sendData();">go</button>
	            	</div>
	                <div class="singleid_waiting singleid_invisible">waiting for data</div>
				    <a href="http://www.singleid.com" target="_top" title="SingleID is available for Android, iPhone and Windows Phone"><div class="free_text_single_id">Get SingleID now!</div>
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
				
				
} elseif ($_REQUEST['op'] == 'send') {	// From browser (user has clicked go)
							// here start the request from the website to the SingleID Server
				
				// this step is for extra security. If you really know what are you doing you can remove
				// just to be extra sure that nobody could browse this folder that for some minutes could be full of sensitive data
				$securitydata = '<html><h1>Silence is gold</h1></html>'; 
				$fp           = fopen('userdata/index.html', 'w');
				fwrite($fp, $securitydata);
				fclose($fp);
				
				
				$_SESSION['SingleID']['hash'] = md5(microtime() . md5($_SERVER['HTTP_USER_AGENT'] . mt_rand(1, mt_getrandmax())) . $_SERVER['REMOTE_ADDR'] . $_SERVER['SCRIPT_FILENAME'] . mt_rand(1, mt_getrandmax())); // a bit of entropy here
				
				
				
				if (is_SingleID($_POST['single_id'])) {
								
								$single_id                   = $_POST['single_id'];
								$_SESSION['SingleID']['who'] = $single_id;
								
								if ($single_id) { // redundant
												
												$ssl         = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
												$protocol[1] = 'https';
												$protocol[0] = 'http';
												
												// ttofix 2015-01-27
												if ($ssl == 0 and requested_data <> '1') {
																error_log('send 2 ' . $ssl);	// This will be correct very soon
																								// we need to block here this request and we need an alert for the sysadmin
												}
												
												//set POST variables
												$url = 'https://app.singleid.com/';
												
												$fields = array(
																'SingleID' => urlencode($single_id), // the value typed in the button ( 8 hex char string )
																'UTID' => urlencode($_SESSION['SingleID']['hash']), // MUST BE AN MD5 HASH or a 32 hex char string
																'logo_url' => LOGO_URL, // the img that will be displayed on the user device
																'name' => SITE_NAME, // website name
																'requested_data' => requested_data, // see note 1 below
																'ssl' => $ssl,
																'url_waiting_data' => $protocol[$ssl] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"],
																'ACTION_ID' => 'askfordata'
												);
												
												//url-ify the data for the POST
												foreach ($fields as $key => $value) { // todo and if a var contain a & ? DOUBLE CHECK HERE ASAP
																$fields_string .= $key . '=' . $value . '&'; // TODO TO CHECK 
												}
												rtrim($fields_string, '&');
												
												
												
												// we all know that an ip could be spoofed
												
												if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
													$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];  // behind amazon load balancing
												} else {
													$ip = $_SERVER['REMOTE_ADDR'];
												}


												$ip = filter_var($ip, FILTER_VALIDATE_IP);
												$ip = ($ip === false) ? '0.0.0.0' : $ip;
												
												
												$headers = array(
																'Authorization: ' . billing_key,
																'Browser_ip: ' . $ip
												);
												//open connection
												$ch      = curl_init();
												
												//set the url, number of POST vars, POST data
												curl_setopt($ch, CURLOPT_URL, $url);
												curl_setopt($ch, CURLOPT_POST, count($fields));
												curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
												curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
												
												//execute post
												$result = curl_exec($ch);
												error_log(curl_error($ch));
												$responseInfo = curl_getinfo($ch);
												
												$ServerReply = json_decode($result, true);
												
												
												curl_close($ch); //close connection because we are brave 
												
												//$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] = time();
								}
								
								if ($ServerReply['Reply'] <> 'ok') {
												error_log($ServerReply['PopupTitle'] . ' ' . $ServerReply['Popup']);
												die($ServerReply['PopupTitle']);
								}
								
								die('500'); // if numeric is ok
				} else {
								die('not valid SingleID'); // TODO TO CHECK 
				}
				
				
} elseif (isset($_POST['UTID'])) {
				// This happen when a Device has sent something ( $_POST[UTID]) 
				if (!is_md5($_POST['UTID'])) {
								die('Wrong data received!');
								unset($_SESSION['SingleID']); // leave the system clean for better security
				}
				
				// we need remove from this folder all the files older than 5 minutes for security reason !!!
				$path = 'userdata/';
				if ($handle = opendir($path)) {
								while (false !== ($file = readdir($handle))) {
												if ((time() - filectime($path . $file)) >= 300) {
																if (preg_match('/\.SingleID.txt$/i', $file)) {
																				unlink($path . $file);
																}
												}
								}
				}
				
				
				if ($_POST['Ecom_payment_mode'] != 'paypal') {
								$_POST['Ecom_payment_mode'] = $_POST['Ecom_payment_card_type'];
				}
				
				
				// so we need the store the received data
				$data = gzcompress(serialize($_POST), 9); // we compress only to avoid some research with grep from script kiddies like you :-P
				$fp   = fopen('userdata/' . $_POST['UTID'] . '.SingleID.txt', 'w');
				fwrite($fp, $data);
				fclose($fp);
				
				
				
				
				
				
} elseif ($_REQUEST['op'] == 'getdata') {
				
				if (!is_md5($_SESSION['SingleID']['hash'])) {
								die('Wrong data to recover');
								unset($_SESSION['SingleID']); // leave the system clean
				}
				
				//error_log('getdata========'.$_SESSION['SingleID']['hash']);
				$filetarget           = 'userdata/' . $_SESSION['SingleID']['hash'] . '.SingleID.txt';
				$data                 = unserialize(gzuncompress(file_get_contents($filetarget)));
				$data['Refresh_Page'] = 1;
				
				// if this value is set to 1
				// The javascript knows that is not a form filling example but the data has been processed from PHP
				// start from here we need
				error_log($data['which_set']);
				
				// but if I am already a registered user ?
				
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
				
				
				
				
				// before anything we need to check if the profile contains a minimum of data !
				if ($data['which_set'] == 'personal') {	// ho ricevuto un set di dati di tipo personale
								// the minimum required data are:
								
								if ((trim($data['Pers_first_name']) == '') or (trim($data['Pers_last_name']) == '') or (trim($data['Pers_postal_street_line_1']) == '') or (trim($data['Pers_postal_city']) == '') or (trim($data['Pers_postal_postalcode']) == '') or (trim($data['Pers_postal_countrycode']) == '') or (trim($data['Pers_first_email']) == '')) {
												
												// in order to use this site with SingleID you need to fill the minimum data required in your profile !
												$data['Refresh_Page'] = 0; // remove refresh
												$data['Bypass_Auth']  = 1; // do not exec code for auth
												$data['Mex']          = 'Your SingleID Profile doesn\'t have all the minimum required fields';
												$data['Show_Error']   = 1; // shows a javascript error
												// we need to display a mex to the user !
												
								}
				}
				
				
				if ($data['which_set'] == 'business') { // ho ricevuto un set di dati di tipo Business
								// the minimum required data are:
								// we accept only personal profile
								
								$data['Refresh_Page'] = 0; // remove refresh
								$data['Bypass_Auth']  = 1; // do not exec code for auth
								$data['Mex']          = 'Sorry. At the moment we accept only personal profile from SingleID';
								$data['Show_Error']   = 1; // shows a javascript error
								
								/*	
								if (
								(trim($data['Company_name']) == '') or
								(trim($data['Comp_postal_street_line_1']) == '') or
								(trim($data['Comp_postal_city']) == '') or
								(trim($data['Comp_postal_postalcode']) == '') or
								(trim($data['Comp_postal_countrycode']) == '') or
								(trim($data['Comp_contact_language']) == '') or
								(trim($data['Comp_billing_vat_id']) == '')){
								
								// in order to use this site with SingleID you need to fill the minimum data required in your profile !
								$data['Refresh_Page'] = 0; // remove refresh
								$data['Bypass_Auth'] = 1; // do not exec code for auth
								$data['Mex'] = 'Your SingleID Profile doesn\'t have all the minimum required fields';
								$data['Show_Error'] = 1; // shows a javascript error
								// we need to display a mex to the user !
								
								}
								*/
				}
				
				
				
				
				
				if ($data['Bypass_Auth'] <> 1) { // do not exec code for auth
								
								require_once('SingleID_auth.php');
								// userAuth(); // to switch !
								
				}
				// Printing the data (received) the js/plugin.js will fill the form.
				
				print json_encode($data); // redirect to
				
				
				
				
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
				
				
				
} elseif ($_REQUEST['op'] == 'refresh') {
				
				// request made from browser !
				
				//error_log('refresh');
				$_SESSION['SingleID']['counter']++;
				
				//if($_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] > 0)
				//{
				$file = 'userdata/' . $_SESSION['SingleID']['hash'] . '.SingleID.txt';
				
				if ($_SESSION['SingleID']['counter'] > 120) {
								//error_log('400-0000');
								print 400; // too much time is passed
								// the output number code are inspired from the http status code
								// 400 = error
				} else if (is_file($file)) { // the file exist !
								
								// error_log('DEBUG: DATA RECEIVED FROM DEVICE');
								print '200'; // the post data has been received from the device so we launch the JS to populate the fields
				} // 200 = OK
				else {
								//error_log('waiting for-1-0000');
								print '100';
								
								// continue... misuse as refresh
				}
				die;
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
