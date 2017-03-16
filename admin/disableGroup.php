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
  $sql = "SELECT `client_id`,`name` FROM `group` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "Invalid group ID";
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
  $uts = date('U');
  $newGroup = $uts.'-'.$gname;
  $sql = "UPDATE `group` SET `disabled` = 1,`name` = '$newGroup' WHERE `id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: group.';
    log_and_respond($response);
    exit;
  }
	
  queueAPN($client,'client');

  $response['content'] = 'Delete/Disable Successful: group.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>