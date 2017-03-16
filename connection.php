<?php	
	
	header("Content-Type: application/json");

	$apnUrl = 'ssl://gateway.push.apple.com:2195';
	$apnPassphrase = '';
	$apnCert = 'files/ck.pem';

	$dbHost = 'localhost';
	$dbUser = 'root';
	$dbPass = 'Monster.';
	$dbName = 'fdcslv2';

	$response = array(
		'status'=>'NO',
		'content'=>'',
		);

	$m = new mysqli($dbHost,$dbUser,$dbPass,$dbName);

	if ($m->connect_errno){
		$response['content'] = 'Error connectiong to the database.';
		log_and_respond($response);
		exit;
	}
	
	$baseFileDir = '../files/';

	$baseURL = 'https://bien.fusionofideas.com/dcs-api/';
	$baseFileURL = 'https://bien.fusionofideas.com/dcs-api/files/';

	include 'php_fns.php';

?>
