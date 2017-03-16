<?php
	if(!isset($_REQUEST['user'])||!isset($_REQUEST['token'])){
		$response['content'] = "No authentication supplied.";
		log_and_respond($response);
		exit;
	}
	$u = $_REQUEST['user'];
	$t = $_REQUEST['token'];

	$foiAdmin = false;

	$sql = "SELECT `client_id`,`id` FROM `admin_user` WHERE `user` = '$u'";
	$res = $m->query($sql);
	if ($res->num_rows == 0){
		$response['content'] = 'invalid user, cannot find client_id';
		log_and_respond($response);
	}
	$er = $res->fetch_assoc();
	$clientID = $er['client_id'];
	$userID = $er['id'];

	if (intval($clientID) == -1){
		$foiAdmin = true;
	}

	//clear expired tokens
	$now = date("Y-m-d H:i:s");
	$sql = "DELETE FROM `portal_session` WHERE `expiration`<='$now'";
	$m->query($sql);


	$sql = "SELECT * FROM `portal_session` WHERE `user`='$u' AND `token`='$t'";
	$res = $m->query($sql);

	if($res->num_rows==0){
		$response['content'] = 'invalid_session';
		log_and_respond($response);		
	}	
?>