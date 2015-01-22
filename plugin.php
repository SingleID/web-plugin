<?php
header("Access-Control-Allow-Origin: *");

// alpha release intro for internal developer only

/*
 * SINGLEID WEB PLUGIN
 * Date: 2015-01 from SingleID Inc.
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
define("SITE_NAME", "Full data test");
define("requested_data", "1,2,3,4");
define("billing_key", "internaltest"); 				// you must require this key from www.singleid.com
define("admin_contact", "xxxxxx@singleid.com");







session_start();

$op = $_REQUEST['op'];

if(isset($_POST['UTID']))
	$op = 'response';

if($op == 'init')
{
	if(!is_writable('userdata/'))
	{
		print '<p>no write permission!</p>';
		error_log('no permission for userdata/ folder TRY -> sudo chmod 0777 userdata/ -R ');
	}
	else
	{
		$_SESSION['singleID']['hash'] = md5( microtime().mt_rand(1, mt_getrandmax()).$_SERVER['REMOTE_ADDR'].mt_rand(1, mt_getrandmax()) );
		$_SESSION['singleID'][$_SESSION['singleID']['hash']]['has_response'] = 0;
		$_SESSION['singleID'][$_SESSION['singleID']['hash']]['is_sended'] = 0;

		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title>SingleID iframed button</title>
				<meta charset="utf-8">
				<link rel="stylesheet" href="https://app.singleid.com/button/plugin/css/main_sheet.css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
				<script src="https://app.singleid.com/button/plugin.js"></script>
			</head>
			<body>
		   		<div class="singleid_button_wrap singleid_pointer">
	        	    <div class="single_text_single_id">fill out</div>
	                <div class="icon_box_single_id"><img src="https://app.singleid.com/button/plugin/img/key2.jpg" alt="" /></div>

	            	<div class="white_back_single_id singleid_invisible">
	                	<input class="singleid_styled_input" name="SingleID" type="text" value="" maxlength="8" />
	                	<button type="button" class="icon_box_go" onClick="sid_sendData();">go</button>
	            	</div>
	                <div class="singleid_waiting singleid_invisible">waiting for data</div>
				    <a href="https://play.google.com/store/apps/details?id=com.singleid.wapp" title="SingleID app for Android"><div class="free_text_single_id">Get SingleID now!</div>
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
	}
	die();
}
elseif($op == 'send')
{
error_log('send');
	$single_id = $_POST['single_id'];
	if($single_id)
	{
		//error_log('send 1');
		$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? 1:0;
		$protocol[1] = 'https';
		$protocol[0] = 'http';

		//set POST variables
		$url = 'https://app.singleid.com/';

		$fields = array(
			'SingleID' 			=> urlencode($single_id), // the value typed in the button ( 8 hex char string )
			'UTID' 				=> urlencode($_SESSION['singleID']['hash']), // MUST BE AN MD5 HASH or a 32 hex char string
			'logo_url' 			=> LOGO_URL, // the img that will be displayed on the user device
			'name' 				=> SITE_NAME, // website name
			'requested_data' 	=> requested_data, // see note 1 below
			'ssl' 				=> $ssl,
			'url_waiting_data'	=> $protocol[$ssl].'://'. $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"],
			'ACTION_ID' 		=> "askfordata"
		);

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		
//error_log('fields='.$fields_string.'|||'.$ch);

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$result = curl_exec($ch);
		//error_log(curl_error($ch));
		$reponseInfo = curl_getinfo($ch);
//error_log('result='.$result.'|||'.$ch);
//error_log('reponseInfo='.serialize($reponseInfo));


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


		//close connection
		curl_close($ch);

		$_SESSION['singleID'][$_SESSION['singleID']['hash']]['is_sended'] = time();
	}
	die();
}
elseif($op == 'response')
{
	error_log('response!!!');

	if($_POST['Ecom_payment_mode'] != 'paypal')
	{
		$_POST['Ecom_payment_mode'] = $_POST['Ecom_payment_card_type'];
	}

	$data = serialize($_POST);	
	$fp = fopen('userdata/'.$_POST['UTID'], 'w');
	fwrite($fp, $data);
	fclose($fp);
	//chmod('userdata/'.$_POST['UTID'], 777); // #DV ? WTF ? why 777 ?
	
	/* if($_POST['status'] == 1)
	{
		if($_POST['data']['Ecom_payment_mode'] != 'paypal')
			$_POST['data']['Ecom_payment_mode'] = $_POST['data']['Ecom_payment_card_type'];

		$data = serialize($_POST['data']);

		$fp = fopen('userdata/'.$_POST['UTID'], "w");
		fwrite($fp, $data);
		fclose($fp);
	}
	else
	{
		$fp = fopen('userdata/'.$_POST['UTID'], "w");
		fwrite($fp, '9');
		fclose($fp);
	} */
}
elseif($op == 'getdata')
{
	// error_log('getdata');
	$filetarget = 'userdata/'.$_SESSION['singleID']['hash'];
	$data = unserialize(file_get_contents($filetarget));

	print json_encode($data);
	
	
	// See if it exists before attempting deletion on it
	if (file_exists($filetarget)) {
    unlink($filetarget); // Delete now
	} 
	// See if it exists again to be sure it was removed
	if (file_exists($filetarget)) {
		error_log('Problem deleting. Check your permission ! ' . $filetarget);
	}
	die;
}
elseif($op == 'refresh')
{
	error_log('refresh');

	/* $_SESSION['singleID']['hash'] = '18a7f2cacbe7393a2bcfc012792fd76a';
	$_SESSION['singleID'][$_SESSION['singleID']['hash']]['is_sended'] = time()-5; */
	if($_SESSION['singleID'][$_SESSION['singleID']['hash']]['is_sended'] > 0)
	{
		$file = 'userdata/'.$_SESSION['singleID']['hash'];
		$dif = time() - $_SESSION['singleID'][$_SESSION['singleID']['hash']]['is_sended'];

		if($dif > 60)
		{
			print 10;
		}
		else if( is_file($file) )
		{
			$d = (int)file_get_contents($file);
			if($d == 9)
			{
				print '9';
			}
			else
			{
				print '2';
			}
		}
		else
		{
			print '1';
		}
	}
	die;
}
?>
