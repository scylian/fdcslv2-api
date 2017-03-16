<?php
	include 'connection.php';
	include 'auth.php';
	ignore_user_abort(true);

	extract($_REQUEST);
	
	if ($type == 'general'){
		$sql = "UPDATE `apn_token` SET `queued` = 1 WHERE `user_id` = '$userID'";
		if (!$m->query($sql)){
			$response['content'] = "Query error, adding user to APN queue";
			log_and_respond($response);
		}		
	}
	
	$response['content'] = "APN Queue updated";
	$response['status'] = 'OK';
	log_and_respond($response);

?>