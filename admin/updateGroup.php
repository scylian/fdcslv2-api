<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No group id set.';
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

  $sql = "SELECT `client_id` FROM `group` WHERE `id` = '$id'";
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

  $sql = "UPDATE `group` SET";
  if (isset($name)){
    // check if name is available
    $sqlt = "SELECT * FROM `group` WHERE `name` = '$name' AND `client_id` = '$client' AND `id` != '$id'";
    
    $rest = $m->query($sqlt);
    if ($rest->num_rows > 0){
      $response['content'] = "Group Name already exists.";
      log_and_respond($response);
      exit;
    }
    $sql .= " `name` = '$name',";
  }  
  if (isset($published)){    
    $sql .= " `published` = '$published',";
  } 
  $sql = rtrim($sql,",");

  $sql .= " WHERE `id`='$id'";

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: Group.';
    log_and_respond($response);
    exit;
  }

  queueAPN($id,'group');
	
  $response['content'] = 'Update Successful: Group.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>