<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No Content ID sent.";
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);
  
  $content = array();

  validID('content','id',$id);
 
  // get all associated groups
  $sql = "SELECT `group`.`id`,`group`.`name`,`group_content`.`order` FROM `group_content` LEFT JOIN `group` ON (`group_content`.`group_id` = `group`.`id`) WHERE `group_content`.`content_id` = '$id' ORDER BY `order`";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $content[] = $e;    
  }

  $response['content'] = $content;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>