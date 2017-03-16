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
 
  // get all associated filters
  $sql = "SELECT `filter`.`id`,`filter`.`name`,`filter_content`.`order` FROM `filter_content` LEFT JOIN `filter` ON (`filter_content`.`filter_id` = `filter`.`id`) WHERE `filter_content`.`content_id` = '$id' ORDER BY `order`";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $content[] = $e;    
  }

  $response['content'] = $content;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>