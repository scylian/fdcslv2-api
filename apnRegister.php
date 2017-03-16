<?php
	include 'connection.php';

	include 'auth.php';

	//check if params set
	$params = ["udid","apnToken","user_id"];

	foreach ($params as $key => $value) {
		if(!isset($_REQUEST[$value])){
			$response['content'] = 'No '.$value.' set.';
			log_and_respond($response);
		}
	}

	extract($_REQUEST);

	//check if udid set
	$sql = "SELECT `id` FROM `apn_token` WHERE `udid`='$udid' AND `user_id` = '$user_id'";

	$res = $m->query($sql);

	if($res->num_rows==0){
		$action = 'added';
		//add		
		$sql = "INSERT INTO `apn_token` (`udid`,`user_id`,`token`,`created_at`) VALUES ('$udid','$user_id','$apnToken',CURRENT_TIMESTAMP)";
	}else{
		$action = 'updated';
		$id = $res->fetch_assoc()['id'];
		$sql = "UPDATE `apn_token` SET `token`='$apnToken' WHERE `id`='$id'";
	}

	if(!$m->query($sql)){
		$response['content'] = 'Query error.';
		log_and_respond($response);
	}

	$response['content'] = 'Token '.$action.' successfully.';
	$response['status'] = 'OK';

	log_and_respond($response);
	
?>