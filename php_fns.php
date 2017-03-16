<?php

	
	// log and respond function
	function log_and_respond($response){
		global $m;
		global $ip;
		global $user_agent;
		
		$uri = $_SERVER['REQUEST_URI'];

		if(isset($_REQUEST['user'])){
			$uname = $_REQUEST['user'];
		}else{
			$uname = 'N/A';
		}

		$resp = json_encode($response);

		$sql = "INSERT INTO `log` (`uri`,`user`,`ip_address`,`user_agent`,`response`) VALUES ('$uri','$uname','$ip','$user_agent','$resp')";

		$m->query($sql);

		echo $resp;
		exit;
	}

	// queue apn
	function queueAPN($uid,$type){
		global $m;
		if ($type == 'user'){
			$sql = "UPDATE `apn_token` SET `queued` = 1 WHERE `user_id` = '$uid'";
			$m->query($sql);			
		} else if ($type == 'group'){
			// notify all users under that group
			$sql = "SELECT `user_id` FROM `group_user` WHERE `group_id` = '$uid'";
			$res = $m->query($sql);
			while ($e = $res->fetch_assoc()){
				$eid = $e['user_id'];
				$sql2 = "UPDATE `apn_token` SET `queued` = 1 WHERE `user_id` = '$eid'";
				$m->query($sql2);
			}
		} else if ($type == 'client'){
			$sql = "SELECT `id` FROM `user` WHERE `client_id` = '$uid'";
			$res = $m->query($sql);
			while ($e = $res->fetch_assoc()){
				$eid = $e['id'];
				$sql2 = "UPDATE `apn_token` SET `queued` = 1 WHERE `user_id` = '$eid'";
				$m->query($sql2);
			}
		}
	}

	// check if valid
	function validID($table,$col,$id){
		global $m;
		global $response;

		$sql = "SELECT COUNT(*) as 'count' FROM `$table` WHERE `$col` = '$id'";
		$res = $m->query($sql);
		$count = $res->fetch_assoc()['count'];
		if ($count == 0){				
			$response['content'] = $col." Not found. Invalid ".$table." ".$col;
			log_and_respond($response);
		}
		return true;
	}

	// email fetch gen
	function fetch_gen($item){
		global $m;

		$sql = "SELECT `value` FROM `gen` WHERE `item`='$item'";

		$res = $m->query($sql);

		if($res->num_rows==0){
			return false;
		}

		$m->kill($m->thread_id);
		$m->close();

		return base64_decode($res->fetch_assoc()['value']);
	}

	function sendMessage($cid){		
		global $m;
		global $response;
		global $apnUrl;

		$apnCertDir = '../files/certs/';
		$sql = "SELECT `apn_cert_file`, `apn_pass` FROM `client` WHERE `id` = '$cid'";
		$res = $m->query($sql);
		if ($res->num_rows == 0){
			$response['content'] = "Invalid client id. no apn cert found.";
			log_and_respond($response);
		}
		$er = $res->fetch_assoc();
		$apnCert = $apnCertDir.$er['apn_cert_file'];
		$apnPassphrase = $er['apn_pass'];

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $apnCert);
		stream_context_set_option($ctx, 'ssl', 'passphrase', $apnPassphrase);
		
		$sql = "SELECT `apn_message_id`,`sent`,`message`,`user_id` FROM `apn_rel` LEFT JOIN `apn_message` ON (`apn_rel`.`apn_message_id` = `apn_message`.`id`) WHERE `sent` = 0";
		$r = $m->query($sql);
		while ($f = $r->fetch_assoc()){
			$uid = $f['user_id'];
			$apnmid = $f['apn_message_id'];
			// Create the payload body
			$body['aps'] = array(
					'alert' => $f['message'],
					'sound' => 'default'
				);
			// Encode the payload as JSON
			$payload = json_encode($body);
			$sql2 = "SELECT `token` FROM `apn_token` WHERE `user_id` = '$uid'";
			$res = $m->query($sql2);
			if ($res->num_rows == 0){
				continue;
			}
			while ($erf = $res->fetch_assoc()){
				$deviceToken = $erf['token'];
				// Build the binary notification
				$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

				$fp = stream_socket_client(
				$apnUrl, $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

				if(!$fp){
					$response['content'] = "Failed to connect: $err $errstr";
					log_and_respond($response);		
				}
				
				// Send it to the server
				$result = fwrite($fp, $msg, strlen($msg));
				// close socket connection
				fclose($fp);
				if(!$result){
					$response['content'] = 'Message not delivered.';
					log_and_respond($response);
				}				
			}
			
			$sql2 = "UPDATE `apn_message` SET `sent` = 1 WHERE `id` = '$apnmid'";
			if (!$m->query($sql2)){
				$response['content'] = "Query error. updating apn_message to sent.";
				log_and_respond($response);
			}
		}		
	}

	function linkCheck($location){
		// check for http/https
	  if (strlen($location)>4){
	    $http = substr($location, 0,4);
	    $https = substr($location, 0,5);
	    if ($http == 'http'||$https == 'https'){
	      $location = $location;
	    } else {
	      $location = 'http://'.$location;
	    }    
	  } else {
	    $location = 'http://'.$location;
	  }
	  return $location;		
	}