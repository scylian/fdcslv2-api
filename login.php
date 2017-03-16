<?php	
	include 'connection.php';

	$params = ["token","user","password"];
	
	foreach ($params as $key => $value) {
    if (!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);          
    }
    $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
    $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
    $_REQUEST[$value] = trim($_REQUEST[$value]);
  }
  
  // userid
	$u = $_REQUEST['user'];
	
	// pass
	$p = $_REQUEST['password'];
	$p = md5($p);
	
	// token
	$t = $_REQUEST['token'];
	
	validID('user','id',$u);

	$sql = "SELECT * FROM `user` WHERE `id`='$u' AND `password`='$p'";
	$res = $m->query($sql);
	if($res->num_rows==0){
		$response['content'] = 'Invalid user/password provided.';
		log_and_respond($response);		
	}
	$e = $res->fetch_assoc();
	$client_id = $e['client_id'];
	$dis = $e['disabled'];
	if (intval($dis) == 1){
		$response['content'] = 'User ID not active.';
		log_and_respond($response);
	}
	
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
	if ($t != $nowToken){
		$response['content'] = 'Invalid token supplied.';
		log_and_respond($response);
		exit;
	}

	$response['content'] = 'success';
	$response['status'] = 'OK';

	log_and_respond($response);
?>