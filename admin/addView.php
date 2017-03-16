<?php
  include '../connection.php';  
  include 'auth.php';

  $params = ["name","cid"];
  
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);      
    } else {
      $response['content'] = 'One or more parameters missing.';
      log_and_respond($response);      
    }
  }
  
  extract($_REQUEST);
	if (!$foiAdmin){
    if (intval($cid) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }  
  }
  // check if name is available
  $sqlt = "SELECT * FROM `view` WHERE `name` = '$name' AND `client_id` = '$cid'";
  
  $rest = $m->query($sqlt);
  if ($rest->num_rows > 0){
    $response['content'] = "View Name already exists.";
    log_and_respond($response);
    exit;
  }
      
  $sql = "INSERT INTO `view` (`name`,`client_id`,`created_at`) VALUES ('$name','$cid',CURRENT_TIMESTAMP)";
    
  if (!$m->query($sql)){
    $response['content'] = 'Query error adding view.';
    log_and_respond($response);
    exit;
  }
  $id = $m->insert_id;

  $sql = "INSERT INTO `view_instance` (`view_id`,`view_type`,`created_at`,`padding`) VALUES ('$id','ipad',CURRENT_TIMESTAMP, 19),('$id','iphone',CURRENT_TIMESTAMP, 7.5),('$id','appletv',CURRENT_TIMESTAMP,30.5)";
  if (!$m->query($sql)){
    $response['content'] = "Query error. inserting view instance on addView";
    log_and_respond($response);
  }

  queueAPN($cid,'client');
	
  $response['content'] = $id;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>