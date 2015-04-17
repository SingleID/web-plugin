<?php

define("SETUP_CONFIG",'20150418');




if (!file_exists( '/personal.conf.php')) {
	
$create_new = file_get_contents('lib/official.examples/SingleID.conf.php');
    			
// create random value for PATH value
$Bytes 			= openssl_random_pseudo_bytes(8, $strong);
$rndval 		= bin2hex($Bytes);
$create_new  	= str_replace('userdata/', $rndval.'/', $create_new);

if (!is_dir($rndval)) {
    mkdir($rndval, 0777, true);
    error_log('Created folder for temp data');
}



// this step is for extra security. If you really know what are you doing you can remove
// just to be extra sure that nobody could browse this folder that for some minutes could be full of sensitive data
if (!file_exists(__DIR__ . '/' . $rndval . '/index.html')) {
	$securitydata = '<html><h1>Silence is gold</h1></html>';  	// absolutely prevent directory browsing!
	$fp           = fopen($rndval . '/index.html', 'w');
	fwrite($fp, $securitydata);
	fclose($fp);
	error_log('Created fake index.html');
}

if (!file_exists(__DIR__ . '/' . $rndval . '/.htaccess')) {
	$securitydata = 'Options -Indexes';  						// absolutely prevent directory browsing!
	$fp           = fopen($rndval . '/.htaccess', 'w');
	fwrite($fp, $securitydata);
	fclose($fp);
	error_log('Created fake .htaccess');
}

if (!file_exists(__DIR__ . '/' .$rndval . '/garbage.txt')) {
$garbagedata = '
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quaesita enim virtus est, non quae relinqueret naturam, sed quae tueretur. Quare attende, quaeso. Ergo ita: non posse honeste vivi, nisi honeste vivatur? Itaque hic ipse iam pridem est reiectus;

Audax negotium, dicerem impudens, nisi hoc institutum postea translatum ad philosophos nostros esset. Duo Reges: constructio interrete. Facillimum id quidem est, inquam. At enim, qua in vita est aliquid mali, ea beata esse non potest. Atque ab his initiis profecti omnium virtutum et originem et progressionem persecuti sunt. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Sed plane dicit quod intellegit. Illa argumenta propria videamus, cur omnia sint paria peccata.

Immo videri fortasse. Philosophi autem in suis lectulis plerumque moriuntur. Qui autem esse poteris, nisi te amor ipse ceperit? Si mala non sunt, iacet omnis ratio Peripateticorum.

Habent enim et bene longam et satis litigiosam disputationem. Etenim semper illud extra est, quod arte comprehenditur. Prioris generis est docilitas, memoria; Hoc loco tenere se Triarius non potuit. Non est igitur voluptas bonum. Beatus sibi videtur esse moriens. Vitae autem degendae ratio maxime quidem illis placuit quieta.

Huius, Lyco, oratione locuples, rebus ipsis ielunior. Quid ergo aliud intellegetur nisi uti ne quae pars naturae neglegatur? Quarum ambarum rerum cum medicinam pollicetur, luxuriae licentiam pollicetur. Nemo igitur esse beatus potest. Quid ergo attinet gloriose loqui, nisi constanter loquare? Quod iam a me expectare noli.
';


// when we need to rewrite a file... we try to overwrite it with garbage before delete it. could be useless.... it depends from OS and FS used
$fp           = fopen($rndval . '/garbage.txt', 'w');
fwrite($fp, base64_encode(uniqid().$garbagedata));
fclose($fp);
			
}	



// create random value for PATH value
$Bytes 			= openssl_random_pseudo_bytes(32, $strong);
$rndval 		= bin2hex($Bytes);
$create_new  	= str_replace('RANDOM_(FIXED)_CHARS AT SETUP!', $rndval, $create_new);


$fp           	= fopen( 'personal.conf.php', 'w'); 	// configuration file has been written with some random value. Good job bro'
fwrite($fp, $create_new);
fclose($fp);

}






if (!file_exists( 'personal.auth.php')) {
	
$create_new = file_get_contents( __DIR__ . '/lib/official.examples/SingleID.auth.php');
    
$fpp           	= fopen('personal.auth.php', 'w');
fwrite($fpp, $create_new);
fclose($fpp);

}

// unlink(__FILE__);
?>
