<?php  
  include '../connection.php';

  include 'auth.php';
   
  $clients = array();

  $sql = "SELECT `id`,`name` FROM `client` WHERE `active` = 1";  
  $res = $m->query($sql);
 
  while ($e = $res->fetch_assoc()){
    $clients[] = $e;
  }
  
  $response['content'] = $clients;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
