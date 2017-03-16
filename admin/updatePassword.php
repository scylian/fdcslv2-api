<?php
	include '../connection.php';

	include 'auth.php';

	if(!isset($_REQUEST['user'])||!isset($_REQUEST['oldPass'])||!isset($_REQUEST['newPass'])){
		$response['content'] = 'user and/or password not supplied.';
		log_and_respond($response);
		exit;
	}

	$u = $_REQUEST['user'];
	$op = $_REQUEST['oldPass'];
	$np = $_REQUEST['newPass'];
	$passLength = strlen($np);
	if ($passLength < 6){
		$response['content'] = 'Invalid New Password: Minimum Length is 6 characters.';
		log_and_respond($response);
		exit;
	}
	$u = stripslashes($u);
	$u = $m->real_escape_string($u);

	$u = strtolower($u);

	$op = md5($op);
	$np = md5($np);

	//check admin_user table
	$sql = "SELECT * FROM `admin_user` WHERE `user`='$u' AND `password`='$op'";
	$res = $m->query($sql);
	if($res->num_rows==0){
		$response['content'] = 'Invalid user/password provided.';
		log_and_respond($response);
		exit;
	}

	$sql = "UPDATE `admin_user` SET `password`='$np', `ftl`=0 WHERE `user`='$u'";
	if(!$m->query($sql)){
		$response['content'] = 'Failed to update password.';
		log_and_respond($response);
		exit;
	}

	$response['status'] = 'OK';
	$response['content'] = 'Password updated successfully.';

	log_and_respond($response);
	exit;
?>