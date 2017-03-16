<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No group id set.';
    log_and_respond($response);
    exit;
  }
 
  $id = $_REQUEST['id'];

  // check if valid user ID
  $sql = "SELECT `client_id`,`name` FROM `view` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "Invalid view ID";
    log_and_respond($response);
    exit;
  }  
  $e = $res->fetch_assoc();
  $client = $e['client_id'];
  $gname = $e['name'];
  if (!$foiAdmin){    
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }
    
  $sql = "DELETE FROM `view` WHERE `id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: view.';
    log_and_respond($response);
    exit;
  }
  $sql = "DELETE FROM `view_instance` WHERE `view_id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: view_instance.';
    log_and_respond($response);
    exit;
  }
	
  queueAPN($client,'client');

  $response['content'] = 'Delete/Disable Successful: view.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>