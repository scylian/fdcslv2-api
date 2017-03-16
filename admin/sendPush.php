<?php
	include '../connection.php';
	include 'auth.php';

	//check for required params
  $params = ["object","message","type"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

	extract($_REQUEST);
  
  // add message to apn_message
  $message = stripslashes($message);
  $message = $m->real_escape_string($message);
  $message = trim($message);
  $sql = "INSERT INTO `apn_message` (`sender_id`,`message`,`type`,`created_at`) VALUES ('$userID','$message','$type',CURRENT_TIMESTAMP)";
  if (!$m->query($sql)){
    $response['content'] = "Query error. adding apn_message";
    log_and_respond($response);
  }
  $apn_message_id = $m->insert_id;

  // loop through object
  $object = json_decode($object,1);
  if (!$object){
    $response['content'] = "Invalid object structure. sendPush";
    log_and_respond($response);
  } 
  for ($i=0;$i<sizeof($object);$i++){
    $e = $object[$i];
    // check if user or group ID is valid
    validID($type,'id',$e);
    
    if ($type == 'user'){
      // rel the message to that apn push
      $sql = "INSERT INTO `apn_rel` (`apn_message_id`,`user_id`) VALUES ('$apn_message_id','$e')";
      if (!$m->query($sql)){
        $response['content'] = "Query error. apn_rel";
        log_and_respond($response);
      }
    } else if ($type == 'view'){
      // rel the message to that apn push
      $sql = "SELECT `user_id` FROM `view_user` WHERE `view_id` = '$e'";
      $res = $m->query($sql);
      while ($e = $res->fetch_assoc()){
        $eid = $e['user_id'];
        $sql = "INSERT INTO `apn_rel` (`apn_message_id`,`user_id`) VALUES ('$apn_message_id','$eid')";
        if (!$m->query($sql)){
          $response['content'] = "Query error. apn_rel";
          log_and_respond($response);
        } 
      }
    }    
  }
  // send messages
  sendMessage($client);

  $response['content'] = 'apn pushed successfully.';
  $response['status'] = "OK";
  log_and_respond($response);
