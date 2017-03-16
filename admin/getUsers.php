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

  $sql = "SELECT `u`.`id`,`u`.`name`,`published`,`pin` FROM `user` as u WHERE `disabled` = 0 AND `client_id` = '$client'";
  $res = $m->query($sql);  

  while ($e = $res->fetch_assoc()){
    $users[] = array(
        'id'=>$e['id'],
        'name'=>$e['name'],  
        'published'=>$e['published'],
        'pin'=>$e['pin'],             
      );
  }
	
  $response['content'] = $users;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>