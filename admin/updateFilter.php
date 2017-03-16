<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No filter id set.';
    log_and_respond($response);
    exit;
  }

  $params = ["name","published","loop"];
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

  $sql = "SELECT `client_id` FROM `user` WHERE `id` = '$userID'";
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

  $sql = "UPDATE `filter` SET";
  if (isset($name)){    
    $sql .= " `name` = '$name',";
  }
  if (isset($loop)){
    $sql .= " `loop` = '$loop',"; 
  }
  $sql = rtrim($sql,",");

  $sql .= " WHERE `id`='$id'";

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: filter.';
    log_and_respond($response);
    exit;
  }	

  queueAPN($client,'client');
  
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>