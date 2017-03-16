<?php
	if(!isset($_REQUEST['token'])){
		$response['content'] = "No token supplied.";
		log_and_respond($response);		
	}
	if (!isset($_REQUEST['client_id'])){
		$response['content'] = 'No client id supplied';
		log_and_respond($response);
	}

	$token = $_REQUEST['token'];

	$client_id = $_REQUEST['client_id'];

	$sql = "SELECT `name`,`active` FROM `client` WHERE `id` = '$client_id'";
	$res = $m->query($sql);
	if ($res->num_rows == 0){
		$response['content'] = 'Invalid client id /auth';
		log_and_respond($response);
	}
	$r = $res->fetch_assoc();
	if (intval($r['active']) == 0){
		$response['content'] = 'App not active.';
		log_and_respond($response);	
	}
	$cname = $r['name'];
	$cname = str_replace(' ', '', $cname);
	$cname = strtolower($cname);
	
	$now = date("U");
	$offset = date("Z");

	$utcNow = $now-$offset;
	// check token with dynamic client name
	$nowToken = md5($cname.date("Y-m-d",$utcNow));	
	if($token!=$nowToken){
		$response['content'] = 'Invalid token supplied.';
		log_and_respond($response);
		exit;
	}

	
?>