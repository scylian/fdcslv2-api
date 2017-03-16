<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['filters'])){
    $response['content'] = 'No filter array set.';
    log_and_respond($response);
    exit;
  }
  if (!isset($_REQUEST['client'])){
    $response['content'] = 'No filter array set.';
    log_and_respond($response);
    exit;
  }

  extract($_REQUEST);
  
  $filters = json_decode($filters,1);
  if (!$filters){
    $response['content'] = "Invalid filters array.";
    log_and_respond($response);
  }

  if (!$foiAdmin){    
    if (intval($client) != intval($clientID)){ // pulls clientID from auth.php
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  for($i=0;$i<sizeof($filters);$i++){
    $e = $filters[$i];
    $fid = $e['id'];
    $forder = $e['order'];
    $sql = "UPDATE `filter` SET `order` = '$forder' WHERE `id` = '$fid'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. updating filter order.";
      log_and_respond($response);
    }
  }
  
  // $sql = "SELECT `id`,`order` FROM `filter` WHERE `client_id` = '$client' AND `disabled` = 0 AND `published` = 1 ORDER BY `order`";
  // $res = $m->query($sql);
  // $order = 1;
  // while ($e = $res->fetch_assoc()){
  //   $fid = $e['id'];
  //   $ord = $e['order'];
  //   $sql2 = "UPDATE `filter` SET `order` = '$order' WHERE `id` = '$fid'";
  //   $m->query($sql2);
  //   $order++;
  // }

  queueAPN($client,'client');
  
  $response['content'] = "FIlter order updated successfully.";
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>