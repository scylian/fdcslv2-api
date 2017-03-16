<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No view ID sent.";
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);
  
  $content = array();

  // check if user id is valid
  validID('view','id',$id);
  
  $users = array();
  // get groups associated to this user
  $sql = "SELECT `user_id` FROM `view_user` WHERE `view_id` = '$id'";
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
