<?php

/*
THIS PRELIMINARY PLUGIN IS IN FLUX AND IS SUBJECT TO CHANGE AT ANY TIME
At this time it is being published for internal use only. Please DO NOT RELY
upon it until this notice has been removed. (Which should be soon!)
*/



if (!is_SingleID($_SESSION['SingleID']['who'])) {
    die('500'); // we prevent injection in this simple way
				// 500 is interpreted from the JS
}




// what about PDO or filter var ?
foreach ($data as $key => $val) { // prevention is better than cure ! #DV :-)
    $datasafe[$key] = mysqli_real_escape_string($mysqli, $val);
}



function Is_this_SingleID_already_present() {
	
}
function Is_this_user_enabled() {
	
}


function update_the_user_data() {
	
}

function user_is_logged() {
	
}

function display_error_mex() {
	
}

function Is_this_a_really_new_user_for_my_db() {
	
}

function create_the_user() {
	
}	



die();


// Is This SingleID already present in the DB ?


// Possibilities

// User not present in DB with SingleID and not present with the same Email !

// we try to auto register if the profile contains the minimum of data


// User not present in DB with SingleID BUT with email already present

// The system should send a confirmation link to the email already present to enable the association from the user !


// User present in DB with SingleID but account disabled/banned/removed/deleted

// display error

// User present in DB with SingleID 

// which profile we accept ? (personal/companies) ?

// We need to launch an update query of the user data ( if met the minimum profile data request )

// Brainstorming on... if the user now has just logged with a personal profile and then with a company profile ?



if ((is_numeric($l[0])) and ($l[3] == 1)) {
    // yeah, the user is present and is enabled
    error_log('user is present ' . $l[0] . ' and enabled');
    
    $sql_update = '';
    $res        = mysqli_query($mysqli, $sql_update);
    
    // BE CAREFUL, the EMAIL MUST BE AN UNIQUE KEY ON THE DB IN ORDER TO AVOID IDENTITY THIEF
    
    if (mysqli_errno() == 1062) {
        
        // we need to display a mex to the user !!
        // 'You cannot use the email '.$_POST[email].' sent with SingleID because already exist');	
    }
    
    // Ehy Bro' auth the user right now including your code
    
    
    // user is present but disabled
    
} elseif ((is_numeric($l[0])) and ($l[3] == 0)) {
    
    $data['Refresh_Page'] = 0; // remove refresh
    $data['Bypass_Auth']  = 1; // do not exec code for auth
    $data['Mex']          = 'Your user is disabled on this site';
    $data['Show_Error']   = 1; // shows a javascript error
    
} elseif (!is_numeric($l[0])) { // the search with the singleid has returned nothing.
    // So we need to check if the email sent from the SingleId Device is already stored in this DB
    
    // the SingleID of the user is not present
    // so we need to autoregister the user if a same email is not present
    
    
    
    
    
    if (is_numeric($v[0])) {
        
        $data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'The email of your SingleID profile is already registered. Log in in the old way and add your SingleID in your profile';
        $data['Show_Error']   = 1; // shows a javascript error
        
    } else { // the user has not a SingleID and the email sent is not present in the DB. So is a new User !
        
        // finally registration of a user !!!! YEEEEEEEAAAAHHH 
        $sql              = ''; // MUST BE ENABLED BY DEFAULT
        $res              = mysqli_query($mysqli, $sql);
        $just_inserted_id = mysqli_insert_id($mysqli);
        
        
        
    }
    
    
    
    
}


?>
