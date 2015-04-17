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
/*
foreach ($data as $key => $val) { 
    $datasafe[$key] = mysqli_real_escape_string($mysqli, $val);
}
*/

function Is_this_SingleID_already_present($db, $who, $TABLE_USER) {
		
		// EXAMPLE code
		//  SingleID of course is an index in your TABLE
		$db->where ("SingleID", $who);
				
		$numeric_id = $db->getOne ($TABLE_USER);
		
		if (is_numeric($numeric_id['id'])){
			return true;
		}else{
			return false;
		}
		
}

function Is_this_user_enabled($db, $who, $TABLE_USER) {
	
	
	$db->where ("SingleID", $who);
				
		$response = $db->getOne ($TABLE_USER);
		
		if ($response['enabled'] == 1){
			
			return true;		// mmmm
			
		}else{
			// NO
			$data['Refresh_Page'] = 0; // remove refresh
			$data['Bypass_Auth']  = 1; // do not exec code for auth
			$data['Mex']          = 'Your user is disabled on this site';
			$data['Show_Error']   = 1; // shows a javascript error
			
			return $data;
		}
	
	
}


function update_the_user_data($db, $who, $data) {
	
}

function user_is_logged($db, $who) {
	
}

function display_error_mex($data) {
	
		$data['Refresh_Page'] = 0; // remove refresh
        $data['Bypass_Auth']  = 1; // do not exec code for auth
        $data['Mex']          = 'SingleID not allowed to login';
        $data['Show_Error']   = 1; // shows a javascript error
        
        return $data;
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





?>
