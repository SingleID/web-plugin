<?php
header("Access-Control-Allow-Origin: *");

if (is_md5($_GET[UTID])){
// open output

	$filetarget = './'.$_GET[UTID].'.auth.SingleID.txt';
	$fh = fopen($filetarget, 'r');
	$theData = fread($fh, filesize($filetarget));
	fclose($fh);
	
	echo $theData;
					
		if (PHP_OS == 'Linux'){
			$size = filesize($filetarget);
			$src = fopen('/dev/zero', 'rb');	// if you prefer you could use urandom also
			$dest = fopen($filetarget, 'wb');

			stream_copy_to_stream($src, $dest, $size);

			fclose($src);
			fclose($dest);
		}
	unlink($filetarget); // Deleted

}



function is_md5($val){
	return (bool) preg_match("/[0-9a-f]{32}$/i", $val);
}


?>
