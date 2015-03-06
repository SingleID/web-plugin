<?php

function print_login_button($language = 'en',$requested_data){
	
	
$label['en']['1'] 			= 'Login with your SingleID';
$label['en']['1,2,3'] 		= 'Login with your SingleID';
$label['en']['1,2,3,4'] 	= 'Login with your SingleID';
$label['en']['1,-2,3'] 		= 'Login with your SingleID';
$label['en']['1,-2,3,4'] 	= 'Login with your SingleID';
$label['en']['5'] 			= 'Identify with SingleID';
$label['en']['6'] 			= 'Confirm with SingleID';
	
	
	return '
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
				<div class="single_text_single_id">'.$label[$language][$requested_data].'</div>
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
					  $(".white_back_single_id").fadeIn(\'fast\');
					  $(".icon_box_go").show(\'fast\');
					  $(".singleid_styled_input").focus();

					  $(".singleid_styled_input").keypress(function(event) {
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




function singleid_parse_profile($data, $accepted)
{
    
    // before anything we need to check if the profile contains a minimum of data !
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
        
        
        
        
        
    }else if (($data['which_set'] == 'business') and (($accepted == 'business') or ($accepted == 'both'))) { // ho ricevuto un set di dati di tipo Business
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
    }else{
		
		
		$data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'Sorry. At the moment we do not accept '.$data['which_set'].' profiles from SingleID';
        $data['Show_Error']   = 1; // shows a javascript error
		
	}
    
    return $data;
}




?>
