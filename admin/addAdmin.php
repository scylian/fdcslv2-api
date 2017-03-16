<?php
  include '../connection.php';
  
  include 'auth.php';
	    
  $params = ["name","client","email","password"];
  
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);      
    } else {
      $response['content'] = $value.' parameters missing.';
      log_and_respond($response);
      exit;
    }
  }
  
  extract($_REQUEST);
  if (!$foiAdmin){
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }
  
  // check if name is available
  $sqlt = "SELECT * FROM `admin_user` WHERE `user` = '$name'";
  
  $rest = $m->query($sqlt);
  if ($rest->num_rows > 0){
    $response['content'] = "username already exists.";
    log_and_respond($response);
    exit;
  }
  $password = md5($password);
  $sql = "INSERT INTO `admin_user` (`user`,`active`,`created_at`,`client_id`,`password`,`email`,`ftl`) VALUES ('$name',0, CURRENT_TIMESTAMP,'$client','$password','$email',1)";
    
  if (!$m->query($sql)){
    $response['content'] = 'Query error adding admin userr.';
    log_and_respond($response);
    exit;
  }
  $id = $m->insert_id;
	
  $response['content'] = $id;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>