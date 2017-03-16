<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No filter id set.';
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);

  // check if valid user ID
  $sql = "SELECT `view_id`,`name` FROM `filter` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows==0){
    $response['content'] = "Invalid filter ID";
    log_and_respond($response);
    exit;
  }  
  $e = $res->fetch_assoc();
  $vid = $e['view_id'];
  $fname = $e['name'];
  if (!$foiAdmin){    
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }
  $uts = date('U');
  $newFilter = $uts.'-'.$fname;

  $sql = "UPDATE `filter` SET `disabled` = 1,`name` = '$newFilter',`order` = -1 WHERE `id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = 'Query error deleting: filter.';
    log_and_respond($response);
    exit;
  }
  // delete from filter_content
  $sql = "DELETE FROM `filter_content` WHERE `filter_id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = "Query error. Deleting filter content.";
    log_and_respond($response);
  }
  $sql = "SELECT `id`,`order` FROM `filter` WHERE `view_id` = '$vid' AND `disabled` = 0 AND `published` = 1 ORDER BY `order`";
  $res = $m->query($sql);
  $order = 1;
  while ($e = $res->fetch_assoc()){
    $fid = $e['id'];
    $ord = $e['order'];
    $sql2 = "UPDATE `filter` SET `order` = '$order' WHERE `id` = '$fid'";
    $m->query($sql2);
    $order++;
  }
  queueAPN($client,'client');
	
  $response['content'] = 'Delete/Disable Successful: filter.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>