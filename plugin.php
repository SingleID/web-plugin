<?php
header("Access-Control-Allow-Origin: *");


/*
 * SINGLEID WEB PLUGIN
 * Date: 2015-02 from SingleID Inc.
 * 
 * To use the plugin on your site please upload this file to your web root directory.
 * You must have jQuery on your site. You can install jquery by adding these line to your head:
 * <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
 *
 * The next step is to insert the SingleId Button. Place this line of code to the place of your site where you want to place the button:
 * <iframe src="http://www.example.com/plugin.php?op=init" width="200" height="80" frameborder="0"></iframe>
 * where www.example.com is your domain
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
 
 
 
define("LOGO_URL", "http://www.singleid.com/img/logonew.png");
define("SITE_NAME", "Basic test");
define("requested_data", "1,2,3,4");
define("billing_key", ""); 	// You have to request this key from www.singleid.com if requested_data is different from "1"
define("admin_contact", "xxxxxx@singleid.com");


function is_SingleID($val){

if (strlen($val) == '8'){  
	return (bool)preg_match("/[0-9a-f]{8}/i", $val);
}else if (strlen($val) == '7'){  	// crockford mode base 32
	return (bool)preg_match("/[0-9a-z]{7}/i", $val);
}else{
	return false;
}

}



// first check !

if(!is_writable('userdata/')){
	print '<p>no write permission!</p>';
	error_log('no permission for userdata/ folder TRY -> sudo chmod 0777 userdata/ -R ');
}


// create some fake file in userdata 


	session_start();




		if(isset($_POST['UTID'])) {
			$op = 'response'; // When some device is sending data !
		} else {
			$op = $_REQUEST['op'];
		}





if($op == 'init'){ // Where all begin ( from browser user )

		$_SESSION['SingleID']['hash'] = md5( microtime().md5($_SERVER['HTTP_USER_AGENT'].mt_rand(1, mt_getrandmax())).$_SERVER['REMOTE_ADDR'].$_SERVER['SCRIPT_FILENAME'].mt_rand(1, mt_getrandmax()) );
		
		
		$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['has_response'] = 0;
		$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] = 0;

		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>SingleID iframed button</title>
				<meta charset="utf-8">
				<link rel="stylesheet" href="https://app.singleid.com/button/plugin/css/main_sheet.css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>				
				<script src="js/plugin.js"></script>
			</head>
			<body>
		   		<div class="singleid_button_wrap singleid_pointer">
	        	    <div class="single_text_single_id">SingleID</div>
	                <div class="icon_box_single_id"><img src="https://app.singleid.com/button/plugin/img/key2.jpg" alt="" /></div>

	            	<div class="white_back_single_id singleid_invisible">
	                	<input class="singleid_styled_input" name="SingleID" type="text" value="" maxlength="8" />
	                	<button type="button" class="icon_box_go" onClick="sid_sendData();">go</button>
	            	</div>
	                <div class="singleid_waiting singleid_invisible">waiting for data</div>
				    <a href="https://www.singleid.com" title="SingleID is available for Android, iPhone and Windows Phone"><div class="free_text_single_id">Get SingleID now!</div>
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
			

			//open connection
			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
			//execute post
			$result = curl_exec($ch);
			error_log(curl_error($ch));
			$responseInfo = curl_getinfo($ch);

			$ServerReply = json_decode($result, true);
			


			//close connection
			curl_close($ch);

			$_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'] = time();
		}
		
			if ($ServerReply['Reply'] <> 'ok'){
			error_log($ServerReply['PopupTitle'].' '. $ServerReply['Popup']);
			die($ServerReply['PopupTitle']);
			}

		die('500'); // if numeric is ok
	}else{
		die('not valid singleid'); // TODO TO CHECK 
	}
	
	
} elseif($op == 'response') {
	// This happen when a Device has sent something ( $_POST[UTID]) 
	
	
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
	   
	   
	   

	if ($_POST['Ecom_payment_mode'] != 'paypal')	{
		$_POST['Ecom_payment_mode'] = $_POST['Ecom_payment_card_type'];
	}
	
	
	// so we need the write the received data
	$data = gzcompress(serialize($_POST), 9);	 // we compress only to avoid some research with grep from script kiddies:-/
	$fp = fopen('userdata/'.$_POST['UTID'].'.SingleID.txt', 'w');
	fwrite($fp, $data);
	fclose($fp);
	
	

	
	
	
} elseif($op == 'getdata') {
	error_log('getdata========');
	$filetarget = 'userdata/'.$_SESSION['SingleID']['hash'].'.SingleID.txt';
	$data = unserialize(gzuncompress(file_get_contents($filetarget)));
	$data['ALREADY_REGISTERED'] = 1; // to understand security of this...

				// TODO TOCHECK 
				// THIS IS THE MOST DELICATE POINT !

	
	// but if I am already a registered user ?
	
	// TODO CHECK SINGLEID !
	
	
	
	 // CODE FOR WEBSITE OWNER  START =======
	
	 

			// the SingleID is already present in the profile ?
			
				// if yes we need to merge this profile but how ???
				
				
			// if the email is not present we need to register the user
	
				// IN DEVELOPMENT
	
	
	
		// so i need to update the allowed value and then mark it as logged in
	
	// CODE FOR WEBSITE OWNER END =====
	
	
	
		// if i print the data received the js/plugin.js will fill a form.
	
	print json_encode($data); // qua redirect to

	
	
	
	// See if it exists before attempting deletion on it
	if (file_exists($filetarget)) {
    unlink($filetarget); // Delete now
    unset($_SESSION['SingleID']); // we love to clean the system after use 

	} 
	// See if it exists again to be sure it was removed
	if (file_exists($filetarget)) {
		error_log('Problem deleting sensitive SingleID files. Check your permission ! ' . $filetarget);
	}
	die;
	
	
	
} elseif($op == 'refresh') {
	
	// request made from browser !
	
		$file = 'userdata/'.$_SESSION['SingleID']['hash'].'.SingleID.txt';
		//$file300 = 'userdata/'.$_SESSION['SingleID']['hash'].'.300.SingleID.txt';
		//$dif = time() - $_SESSION['SingleID'][$_SESSION['SingleID']['hash']]['is_sended'];

		if($dif > 60){
			error_log('400-0000'); // TODO 
			print 400; // too much time is passed
			
		}else if( is_file($file) ){ // the file exist !
			 
				error_log('200-0000');
				print '200'; // the post data has been received from the device so we launch the JS to populate the fields
			//}
		}
		else
		{
			error_log('waiting for-1-0000');
			print '1';
		}
	die;
}




  
  
?>
