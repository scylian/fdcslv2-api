<?php	
	include 'connection.php';
	include 'auth.php';
	  
  // userid
	$u = $_REQUEST['user'];

	validID('user','id',$u);

	$sql = "SELECT `pin` FROM `user` WHERE `id`='$u'";
	$res = $m->query($sql);
	if($res->num_rows==0){
		$response['content'] = 'Invalid user provided.';
		log_and_respond($response);		
	}
	$e = $res->fetch_assoc();
	$pin = $e['pin'];
	$response['content'] = $pin;
	$response['status'] = "OK";	

	log_and_respond($response);
?>