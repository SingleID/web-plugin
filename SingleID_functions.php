<?php



function create_a_mysql_table() {
    
    /*
    
    CREATE TABLE IF NOT EXISTS `SingleID_Tokens` (
    `SingleID` char(8) NOT NULL,
    `hashedThirdFactor` char(60) NOT NULL COMMENT 'it''s only to prevent reading from the SingleID Server',
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `shared-with` varchar(50) NOT NULL,
    UNIQUE KEY `SingleID` (`SingleID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=ascii;
    
    
    
    CREATE TABLE IF NOT EXISTS `SingleID_log` (
    `UTID` char(32) NOT NULL,
    `bcrypted` varchar(60) NOT NULL,
    `happened_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`UTID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=ascii;
    
    
    
    */
    
}



function create_and_store_random_password($SingleID) {
    
    // we always delete any previous handshake
    global $db;
    $db->where("SingleID", $SingleID);
    $db->delete('SingleID_Tokens');
    
    $ip = gimme_visitor_ip();
    
    if (function_exists('openssl_random_pseudo_bytes')) {
        $Bytes = openssl_random_pseudo_bytes(16, $strong);
    }
    if ($strong !== true) {
        die('Please use PHP >= 5.3 or Mcrypt extension');
    }
    
    $HexPassword = bin2hex($Bytes);
    
    $options             = Array(
        'cost' => 12
    );
    $hashed_third_factor = password_hash($HexPassword, PASSWORD_BCRYPT, $options);
    
    
    
    
    $data = Array(
        'SingleID' => $SingleID,
        'hashedThirdFactor' => $hashed_third_factor,
        'shared-with' => $ip
    );
    $id   = $db->insert('SingleID_Tokens', $data);
    
    
    if ($id == $SingleID) {
        //error_log('successful saved hash ' . $hashed_third_factor . ' for '. $SingleID);
    } else {
        error_log('wtf happened? no handshake saved');
        return 'ko-handshake';
    }
    
    return $HexPassword;
}





function gimme_visitor_ip() {
    
    // we all know that an ip could be spoofed and so ? What do you suggest ?
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // behind amazon load balancing
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    $ip = filter_var($ip, FILTER_VALIDATE_IP);
    $ip = ($ip === false) ? '0.0.0.0' : $ip;
    
    return $ip;
}




function send_request_to_singleid_server($fields, $fields_string) {
    
    $ip = gimme_visitor_ip();
    
    $headers = array(
        'Authorization: ' . billing_key,
        'Browser_ip: ' . $ip,
        'admin_contact: ' . admin_contact
    );
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_URL, SINGLEID_SERVER_URL);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    //execute post
    $result       = curl_exec($ch);
    $responseInfo = curl_getinfo($ch);
    $ServerReply  = json_decode($result, true);
    curl_close($ch); //close connection because we are good guys 
    
    return $ServerReply;
}


function safe_delete($file) {
    // for better privacy we can try overwrite them before deleting...
    // on linux only we try to access /dev/zero
    if (PHP_OS == 'Linux') {
        
        // it also depends on the filesystem type... so doing this step could be almost useless but is not so much resource consuming so for now can be executed
        
        $size = filesize($file);
        
        try {
            $src = fopen('/dev/zero', 'rb'); // Maybe on shared hosting this could not be done...
            if (!$src) {
                throw new Exception("Could not open the file!");
            }
        }
        catch (Exception $e) {
            // echo "Error (File: ".$e->getFile().", line ". $e->getLine()."): ".$e->getMessage();
            $src = fopen('./' . PATH . '/garbage.txt', 'rb');
        }
        
        
    } else { // on different platform....
        
        $src = fopen('./' . PATH . '/garbage.txt', 'rb');
        
    }
    
    
    $dest = fopen($file, 'wb');
    
    stream_copy_to_stream($src, $dest, $size);
    
    fclose($src);
    fclose($dest);
    
    unlink($file);
}




function print_login_button($language = 'en', $requested_data) {
    
    
    $label['en']['1']        = 'Login with';
    $label['en']['1,2,3']    = 'Login with';
    $label['en']['1,2,3,4']  = 'Login with';
    $label['en']['1,-2,3']   = 'Login with';
    $label['en']['1,-2,3,4'] = 'Login with';
    $label['en']['1,4,5']    = 'Identify with';
    $label['en']['1,4,6']    = 'Confirm with';
    
    
    return '
<!DOCTYPE html>
<html>
    <head>
	<title>SingleID: First-class login</title>
        <meta charset="utf-8">
        <meta name="robots" content="none" />
        <link rel="stylesheet" href="css/SingleID/SingleID.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>                
        <script src="js/plugin.js"></script>
    </head>
    <body>
        <div class="singleid_button_wrap singleid_pointer">
            <div class="single_text_single_id">' . $label[$language][$requested_data] . '</div>
            <div class="icon_box_single_id"><img src="css/SingleID/SingleID_logo_key.jpg" alt="No more form filling, no more password" title="SingleID" /></div>

            <div class="white_back_single_id singleid_invisible">
                <input class="singleid_styled_input" name="SingleID" type="text" value="" maxlength="8" />
                <button type="button" class="icon_box_go" onClick="sid_sendData();">go</button>
            </div>
            <div class="singleid_waiting singleid_invisible">waiting for data</div>
            <a href="https://www.singleid.com" target="_top" title="Available for Android, iPhone and Windows Phone"><div class="free_text_single_id">Get SingleID now!</div>
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
                  $(".white_back_single_id").fadeIn(\'fast\');
                  $(".icon_box_go").show(\'fast\');
                  $(".singleid_styled_input").focus();

                  $(".singleid_styled_input").keyup(function(event) {
                      if (event.keyCode == 13) {
                          sid_sendData();
                      }
                  });
            });

        });
        </script>
</html>
        ';
}




function singleid_parse_profile($data, $accepted) {
    
    
    foreach ($data as $key => $value) {
        $data[$key] = strip_tags($value); // redundant
    }
    
    
    //we need to check if the profile contains a minimum of data !
    if (($data['which_set'] == 'personal') and (($accepted == 'personal') or ($accepted == 'both'))) { // ho ricevuto un set di dati di tipo personale
        // the minimum required data are:
        
        
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
        
        
        
        if ((trim($data['Pers_first_name']) == '') or (trim($data['Pers_last_name']) == '') or (trim($data['Pers_postal_street_line_1']) == '') or (trim($data['Pers_postal_city']) == '') or (trim($data['Pers_postal_postalcode']) == '') or (trim($data['Pers_postal_countrycode']) == '') or (trim($data['Pers_first_email']) == '')) {
            
            // in order to use this site with SingleID you need to fill the minimum data required in your profile !
            $data['Refresh_Page'] = 0; // remove refresh
            $data['Bypass_Auth']  = 1; // do not exec code for auth
            $data['Mex']          = 'Your SingleID Profile doesn\'t have all the minimum required fields';
            $data['Show_Error']   = 1; // shows a javascript error
            // we need to display a mex to the user !
            
        }
        
        
        
        
        
    } else if (($data['which_set'] == 'business') and (($accepted == 'business') or ($accepted == 'both'))) { // ho ricevuto un set di dati di tipo Business
        // the minimum required data are:
        // we accept only personal profile
        
        $data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'Sorry. At the moment we accept only personal profiles from SingleID';
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
    } else {
        
        
        $data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'Sorry. At the moment we do not accept ' . $data['which_set'] . ' profiles from SingleID';
        $data['Show_Error']   = 1; // shows a javascript error
        
    }
    
    return $data;
}



function is_SingleID($val) {
    return (bool) preg_match("/[0-9a-f]{8}$/i", $val);
}

function is_md5($val) {
    return (bool) preg_match("/[0-9a-f]{32}$/i", $val);
}


?>
