<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  
  $client = $_REQUEST['client'];
   
  $users = array();

  $sql = "SELECT `id`,`user` as 'name',`email`,`active` FROM `admin_user` WHERE `client_id` = '$client'";    
  $res = $m->query($sql);
 
  while ($e = $res->fetch_assoc()){
    $users[] = $e;
  }
  
  $response['content'] = $users;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
