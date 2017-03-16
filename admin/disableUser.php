<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No user id set.';
    log_and_respond($response);
    exit;
  }
  
  extract($_REQUEST);

  if (!$foiAdmin){
    $sql = "SELECT `client_id` FROM `user` WHERE `id` = '$id'";
    $res = $m->query($sql);
    $client = $res->fetch_assoc()['client_id'];
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  // check if valid user ID
  $sql = "SELECT * FROM `user` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "Invalid User ID";
    log_and_respond($response);
    exit;
  }
  $uname = $res->fetch_assoc()['name'];
  $uts = date('U');
  $newUser = $uts.'-'.$uname;

  $sql = "UPDATE `user` SET `disabled` = 1,`name` = '$newUser' WHERE `id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: User.';
    log_and_respond($response);
    exit;
  }
  $sql = "DELETE FROM `view_user` WHERE `user_id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: view_user.';
    log_and_respond($response);
    exit;
  }

  queueAPN($client,'client');
	
  $response['content'] = 'Delete/Disable Successful: User.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>