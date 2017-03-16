<?php	
	include '../connection.php';

	include 'auth.php';

	if(!isset($_REQUEST['username'])||!isset($_REQUEST['newPass'])){
		$response['content'] = 'username and/or password not supplied.';
		log_and_respond($response);
		exit;
	}

	$u = $_REQUEST['username'];
	$np = $_REQUEST['newPass'];
	
	// check new password length
	$passLength = strlen($np);
	if ($passLength < 6){
		$response['content'] = 'Invalid New Password: Minimum Length is 6 characters.';
		log_and_respond($response);
		exit;
	}

	if($u!=$_REQUEST['user']){
		$response['content'] = 'Not authorized to change password for this user.';
		log_and_respond($response);
		exit;
	}

	$u = stripslashes($u);
	$u = $m->real_escape_string($u);

	$u = strtolower($u);

	$np = md5($np);

	//check admin_user table
	$sql = "SELECT * FROM `admin_user` WHERE `user`='$u' AND `ftl`=1";
	$res = $m->query($sql);
	if($res->num_rows==0){
		$response['content'] = 'Invalid first time login user provided.';
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


?>