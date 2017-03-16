<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No letter id set.';
    log_and_respond($response);
    exit;
  }
 
  $id = $_REQUEST['id'];

  // check if valid content ID
  $sql = "SELECT `client_id` FROM `content` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "Invalid Content ID";
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

  $sql = "UPDATE `content` SET `disabled` = 1 WHERE `id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: Content.';
    log_and_respond($response);
    exit;
  }

  queueAPN($client,'client');
	
  $response['content'] = 'Delete/Disable Successful: Content.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>