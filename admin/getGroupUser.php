<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No group ID sent.";
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);
  
  $users = array();
  
  // check if user id is valid
  validID('group','id',$id);
  
  // get users associated to this group
  $sql = "SELECT `user_id` FROM `group_user` WHERE `group_id` = '$id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    if (!in_array($e['user_id'], $users)){
      $users[] = $e['user_id'];
    }
  }
  
    

  $response['content'] = $users;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
