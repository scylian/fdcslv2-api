<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No User ID sent.";
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);
  
  $content = array();

  // check if user id is valid
  validID('user','id',$id);
  
  $groups = array();
  // get groups associated to this user
  $sql = "SELECT `group_id` FROM `group_user` WHERE `user_id` = '$id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    if (!in_array($e['group_id'], $groups)){
      $groups[] = $e['group_id'];
    }
  }
 

  $response['content'] = $groups;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
