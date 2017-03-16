<?php
  include '../connection.php';
  
  include 'auth.php';
	    
  $params = ["name","client"];
  
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);      
    } else {
      $response['content'] = 'One or more parameters missing.';
      log_and_respond($response);
      exit;
    }
  }
  $pw = -1;
  $npin = -1;
  extract($_REQUEST);
  if (!$foiAdmin){
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }
  
  // check if name is available
  $sqlt = "SELECT * FROM `user` WHERE `name` = '$name' AND `client_id` = '$client'";
  
  $rest = $m->query($sqlt);
  if ($rest->num_rows > 0){
    $response['content'] = "Name already exists.";
    log_and_respond($response);
    exit;
  }

  if (isset($password)){
    $pw = md5($password);
  }
  if (isset($pin)){
    if (!is_numeric($pin)){
      $response['content'] = "Pin must only be numbers.";
      log_and_respond($response);
    }
    if (strlen($pin)<4){
      $response['content'] = "Pin must be 4 numbers long.";
      log_and_respond($response);
    }
    $npin = $pin;
  }
  
  $sql = "INSERT INTO `user` (`name`,`published`,`created_at`,`client_id`,`password`,`pin`) VALUES ('$name',0, CURRENT_TIMESTAMP,'$client','$pw','$npin')";
    
  if (!$m->query($sql)){
    $response['content'] = 'Query error adding user.';
    log_and_respond($response);
    exit;
  }
  $id = $m->insert_id;

  queueAPN($client,'client');
	
  $response['content'] = $id;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>