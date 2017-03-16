<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No content ID set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["name","type"];
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

  $sql = "SELECT * FROM `content` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "No Content found To Update. Invalid ID.";
    log_and_respond($response);
    exit;
  }  
  
  $sql = "UPDATE `content` SET";
  if (isset($name)){
    $sql .= " `name` = '$name',";
  }
  if (isset($type)){    
    $sql .= " `type` = '$type',";
  }
  $sql = rtrim($sql,",");
  
  $sql .= " WHERE `id` = '$id'";

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: Content.';
    log_and_respond($response);
    exit;
  }

  queueAPN($client,'client');
	
  $response['content'] = 'Update Successful: Content.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>