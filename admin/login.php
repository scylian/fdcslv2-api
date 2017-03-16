<?php
	include '../connection.php';

	if(!isset($_REQUEST['user'])||!isset($_REQUEST['password'])){
		$response['content'] = 'User and/or password not supplied.';
		log_and_respond($response);
		exit;
	}

	$u = $_REQUEST['user'];
	$p = $_REQUEST['password'];

	$u = stripslashes($u);
	$u = $m->real_escape_string($u);

	$u = strtolower($u);

	$p = md5($p);
	
	$sql = "SELECT * FROM `admin_user` WHERE `user`='$u' AND `password`='$p'";
	$res = $m->query($sql);

	if($res->num_rows==0){
		$response['content'] = 'Invalid user/password provided.';
		log_and_respond($response);		
	}
	while ($e = $res->fetch_assoc()){
		$ftl = $e['ftl'];				
		$client_id = $e['client_id'];	
		$active = $e['active'];
		if (intval($active) == 0){
			$response['content'] = 'User is not active. unable to log in.';
			log_and_respond($response);
		}	
	}

	//init session
	$t = md5(md5($u).md5(date("U")));

	$now = date("U");
	$exp = $now+(4*60*60); //4 hours token validity
	$exp = date("Y-m-d H:i:s",$exp);
	$sql = "INSERT INTO `portal_session` (`user`,`token`,`expiration`) VALUES ('$u','$t','$exp')";

	$m->query($sql);

	$response['content'] = array(
			"token" => $t,
			"user" =>$u,
			"ftl" => $ftl,		
			"client_id"=>$client_id,	
		);
	$response['status'] = 'OK';

	log_and_respond($response);
?>