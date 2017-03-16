<?php
	if(!isset($_REQUEST['token'])){
		$response['content'] = "No token supplied.";
		log_and_respond($response);
		exit;
	}

	$token = $_REQUEST['token'];

	$now = date("U");
	$offset = date("Z");

	$utcNow = $now-$offset;

	$nowToken = md5('hamptoncreek'.date("Y-m-d",$utcNow));

	if($token!=$nowToken){
		$response['content'] = 'Invalid token supplied.';
		log_and_respond($response);
		exit;
	}

	
?>