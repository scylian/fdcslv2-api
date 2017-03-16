<?php
	include 'connection.php';	

	$certs = array();
	$sql = "SELECT `apn_cert_file`,`apn_pass`,`id` FROM `client`";
	$r = $m->query($sql);
	while ($e = $r->fetch_assoc()){
		$certs[$e['id']] = array(
				'id'=>$e['id'],
				'file'=>$e['apn_cert_file'],
				'pass'=>$e['apn_pass'],
			);
	}
	$apnCertDir = 'files/certs/';
		
	// Create the payload body
	$body['aps'] = array(			
			// 'sound' => 'default',
			'content-available'=>1,
			'badge'=>1
		);

	// Encode the payload as JSON
	$payload = json_encode($body);

	//get device tokens
	$sql = "SELECT `token`,`user_id`,`client_id` FROM `apn_token` LEFT JOIN `user` ON (`apn_token`.`user_id` = `user`.`id`) WHERE `queued` = 1";
	$res = $m->query($sql);

	while($e = $res->fetch_assoc()){
		$deviceToken = $e['token'];
		$uid = $e['user_id'];
		$client_id = $e['client_id'];
		if (sizeof($certs[$client_id])==0){
			continue;
		}
		if ($certs[$client_id]['file'] == '' || $certs[$client_id]['pass'] == ''){
			continue;
		}
		$apnCert = $apnCertDir.$certs[$client_id]['file'];
		$apnPassphrase = $certs[$client_id]['pass'];
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $apnCert);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $apnPassphrase);

		$fp = stream_socket_client(
		$apnUrl, $err,
		$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if(!$fp){
			$response['content'] = "Failed to connect: $err $errstr";
			log_and_respond($response);		
		}
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		// close socket connection
		fclose($fp);
		
		if(!$result){
			$response['content'] = 'Message not delivered.';
			log_and_respond($response);
		}
		// update the queue tinyint for apn_token
		$sql2 = "UPDATE `apn_token` SET `queued` = 0 WHERE `token` = '$deviceToken'";
		if (!$m->query($sql2)){
			$response['content'] = "Query error unsetting queue";
			log_and_respond($response);
		}
	}


	$response['content'] = $res->num_rows;
	$response['status'] = 'OK';
	log_and_respond($response);

?>