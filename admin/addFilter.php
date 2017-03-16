<?php
  include '../connection.php';
  
  include 'auth.php';
  $params = ["name","cid","loop"];
  
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
  validID('view','id',$view);

  // check if name is available
  $sqlt = "SELECT * FROM `filter` WHERE `name` = '$name' AND `view_id` = '$view'";
  
  $rest = $m->query($sqlt);
  if ($rest->num_rows > 0){
    $response['content'] = "filter Name already exists.";
    log_and_respond($response);
    exit;
  }
  $sql = "SELECT max(`order`) as 'max' FROM `filter` WHERE `view_id` = '$view'";
  $res = $m->query($sql);
  if ($res->num_rows == 0){
    $response['content'] = "Query error. getting max order - add filter.";
    log_and_respond($response);
  }
  $max = $res->fetch_assoc()['max'];
  $max = $max+1;
  
  $sql = "INSERT INTO `filter` (`name`,`view_id`,`published`,`created_at`,`order`,`loop`) VALUES ('$name','$view',0, CURRENT_TIMESTAMP,'$max','$loop')";
    
  if (!$m->query($sql)){
    $response['content'] = 'Query error adding filter.';
    log_and_respond($response);
    exit;
  }
  $id = $m->insert_id;

  queueAPN($cid,'client');
	
  $response['content'] = $id;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>