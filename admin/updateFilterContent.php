<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No filter id set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["files","name"];
  $isset = 0;
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){      
      $isset = 1;
    }
  }
  if ($isset == 0){
    $response['content'] = 'No params set to update.';
    log_and_respond($response);
    exit;
  }

  extract($_REQUEST);  
    

  validID('filter','id',$id);

  if (!$foiAdmin){
    $sql = "SELECT `client_id` FROM `filter` WHERE `id` = '$id'";
    $res = $m->query($sql);
    $client = $res->fetch_assoc()['client_id'];
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  if (isset($files)){
    // delete all prior associations
    $sql = "DELETE FROM `filter_content` WHERE `filter_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error deleting filter_content associations.';
      log_and_respond($response);
      exit;
    }
    $a = json_decode($files,1);    
    for ($i=0;$i<sizeof($a);$i++){
      $cid = $a[$i]['id'];
      $order = $a[$i]['order'];
      validID('content','id',$cid);

      $sql = "INSERT INTO `filter_content` (`filter_id`,`content_id`,`created_at`,`order`) VALUES ('$id','$cid',CURRENT_TIMESTAMP,'$order')";
      if (!$m->query($sql)){
        $response['content'] = 'Query error inserting: filter Content.';
        log_and_respond($response);
        exit;
      }
    }
  }


  if (isset($name)){
    $name = stripslashes($name);
    $name = $m->real_escape_string($name);
    $name = trim($name);
    // check if name is available
    $sqlt = "SELECT * FROM `filter` WHERE `name` = '$name' AND `client_id` = '$client' AND `id` != '$id'";
    
    $rest = $m->query($sqlt);
    if ($rest->num_rows > 0){
      $response['content'] = "Filter Name already exists. Try another.";
      log_and_respond($response);
      exit;
    }

    $sql = "UPDATE `filter` SET `name` = '$name' WHERE `id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = "query error updating filter name";
      log_and_respond($response);
    }
  }
  
  queueAPN($client,'client');
	
  $response['content'] = 'Update Successful: filter User/Content.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>