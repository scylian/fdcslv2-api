<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No group ID sent.";
    log_and_respond($response);
    exit;
  }
  // $cid = $_REQUEST['id'];
  $content = array();
  extract($_REQUEST);

  validID('group','id',$id);

  // get all associated groups
  $sql = "SELECT `content_id` as 'id' ,`order` FROM `group_content` WHERE `group_content`.`group_id` = '$id' ORDER BY `order` ASC";
  // echo $sql;
  // exit;
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $content[] = $e;
  }

  $response['content'] = $content;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>