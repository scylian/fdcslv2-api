<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  $client = $_REQUEST['client'];
  $groups = array();

  $sql = "SELECT `id`,`name`,`published` FROM `group` WHERE `disabled` = 0 AND `client_id` = '$client'";
  $res = $m->query($sql);

  while ($e = $res->fetch_assoc()){
    $groups[] = array(
        'id'=>$e['id'],
        'name'=>$e['name'],  
        'published'=>$e['published'],             
      );
  }
	
  $response['content'] = $groups;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>