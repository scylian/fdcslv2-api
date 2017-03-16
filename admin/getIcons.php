<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  
  $client = $_REQUEST['client'];
   
  $icons = array();

  $sql = "SELECT `id`,`location`,`file_id` FROM `icon` WHERE `disabled` = 0 AND `client_id` = '$client'";
  $res = $m->query($sql);

  while ($e = $res->fetch_assoc()){
    $icons[] = array(
        'id'=>$e['id'],
        'location'=>$baseURL.'icons/'.$e['location'],     
        'file_id'=>$e['file_id'],               
      );
  }
	
  $response['content'] = $icons;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>