<?php  
  include 'connection.php';

  include 'auth.php';
   
  $users = array();

  $sql = "SELECT `id`,`name`,`pin` FROM `user` WHERE `published` = 1 AND `disabled` = 0 AND `client_id` = '$client_id'";
  if (isset($_REQUEST['timestamp'])){
    $timestamp = strtotime($_REQUEST['timestamp']);
    $timestamp = date("Y-m-d H:i:s",$timestamp);
    $sql .= " AND `user`.`updated_at` > '$timestamp'";
  }
  $res = $m->query($sql);
 
  while ($e = $res->fetch_assoc()){
    $users[] = array(
        'id'=>$e['id'],
        'name'=>$e['name'],
        "pin"=>$e['pin'],
      );
  }
  
  $response['content'] = $users;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
