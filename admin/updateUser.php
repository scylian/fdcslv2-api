<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No user id set.';
    log_and_respond($response);
    exit;
  }

  $params = ["name","published"];
  $isset = 0;
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);
      $isset = 1;
    }
  }
  if ($isset == 0){
    $response['content'] = 'No params set to update.';
    log_and_respond($response);
    exit;
  }

  extract($_REQUEST);
  
  $id = $_REQUEST['id'];

  $sql = "SELECT `client_id` FROM `user` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "No User Found To Update. Invalid ID.";
    log_and_respond($response);
    exit;
  }
  $client = $res->fetch_assoc()['client_id'];
  if (!$foiAdmin){    
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  $sql = "UPDATE `user` SET";
  if (isset($name)){
    $sql2 = "SELECT COUNT(*) as 'count' FROM `user` WHERE `name` = '$name' AND `id` != '$id' AND `client_id` = '$client'";
    $res = $m->query($sql2);
    $count = $res->fetch_assoc()['count'];
    if ($count > 0){
      $response['content'] = "Name already exists for another user. try another.";
      log_and_respond($response);
    }
    $sql .= " `name` = '$name',";
  }  
  if (isset($published)){    
    $sql .= " `published` = '$published',";
  } 
  $sql = rtrim($sql,",");

  $sql .= " WHERE `id`='$id'";

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: User.';
    log_and_respond($response);
    exit;
  }
	queueAPN($id,'user');
  
  $response['content'] = 'Update Successful: User.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>