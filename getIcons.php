<?php  
  include 'connection.php';

  include 'auth.php';
   
  $icons = array();

  $sql = "SELECT `id`,`location`,`disabled`,`file_id` FROM `icon` WHERE `disabled` = 0 AND `client_id` = '$client_id'";
  if (isset($_REQUEST['timestamp'])){
    $timestamp = strtotime($_REQUEST['timestamp']);
    $timestamp = date("Y-m-d H:i:s",$timestamp);
    $sql .= " AND `icon`.`updated_at` > '$timestamp'";
  }
  $res = $m->query($sql);
 
  while ($e = $res->fetch_assoc()){
    $e['location'] = $baseURL.'icons/'.$e['location'];
    $icons[] = $e;
  }
  
  $response['content'] = $icons;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
