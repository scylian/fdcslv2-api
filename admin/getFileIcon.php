<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No file ID sent.";
    log_and_respond($response);
    exit;
  }
  
  $id = $_REQUEST['id'];
   
  $icons = array();

  $sql = "SELECT `id`,`location` FROM `icon` WHERE `file_id` = '$id'";
  $res = $m->query($sql);

  while ($e = $res->fetch_assoc()){
    $icons[] = array(
        'id'=>$e['id'],
        'location'=>$baseURL.'icons/'.$e['location'],                    
      );
  }
	
  $response['content'] = $icons;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>