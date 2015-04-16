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


function Is_this_SingleID_already_present($db, $who) {
	// Sanitize the data and check in your DB
		
		// EXAMPLE CODE
		$db->where ("SingleID", $who);	 
		$numeric_id = $db->getValue ($TABLE_USER, 'id');
		error_log('id founded is numeric' . is_numeric($numeric_id));
		return is_numeric($numeric_id);
}

function Is_this_user_enabled($db, $who ) {
	
	// NO
	$data['Refresh_Page'] = 0; // remove refresh
    $data['Bypass_Auth']  = 1; // do not exec code for auth
    $data['Mex']          = 'Your user is disabled on this site';
    $data['Show_Error']   = 1; // shows a javascript error
    
}


function update_the_user_data($db, $who, $data) {
	
}

function user_is_logged($db, $who) {
	
}

function display_error_mex() {
	
}

function Is_this_a_really_new_user_for_my_db() {
		
		// YES
		
		// NO
		$data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'The email of your SingleID profile is already registered. Log in in the old way and add your SingleID in your profile';
        $data['Show_Error']   = 1; // shows a javascript error
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




?>
