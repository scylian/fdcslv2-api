<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No group id set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["files","users","name"];
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
    

  validID('group','id',$id);

  if (!$foiAdmin){
    $sql = "SELECT `client_id` FROM `group` WHERE `id` = '$id'";
    $res = $m->query($sql);
    $client = $res->fetch_assoc()['client_id'];
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  if (isset($files)){
    // delete all prior associations
    $sql = "DELETE FROM `group_content` WHERE `group_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error deleting group_content associations.';
      log_and_respond($response);
      exit;
    }
    $a = json_decode($files,1);    
    for ($i=0;$i<sizeof($a);$i++){
      $cid = $a[$i]['id'];
      $order = $a[$i]['order'];
      validID('content','id',$cid);

      $sql = "INSERT INTO `group_content` (`group_id`,`content_id`,`created_at`,`order`) VALUES ('$id','$cid',CURRENT_TIMESTAMP,'$order')";
      if (!$m->query($sql)){
        $response['content'] = 'Query error inserting: Group Content.';
        log_and_respond($response);
        exit;
      }
    }
  }

  if (isset($users)){
    // delete all prior associations
    $sql = "DELETE FROM `group_user` WHERE `group_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error deleting group_user associations.';
      log_and_respond($response);
      exit;
    }
    $a = json_decode($users,1);    
    for ($i=0;$i<sizeof($a);$i++){
      $cid = $a[$i];      
      validID('user','id',$cid);

      $sql = "INSERT INTO `group_user` (`group_id`,`user_id`,`created_at`) VALUES ('$id','$cid',CURRENT_TIMESTAMP)";
      if (!$m->query($sql)){
        $response['content'] = 'Query error inserting: Group Users.';
        log_and_respond($response);        
      }
    }
  }

  if (isset($name)){
    $name = stripslashes($name);
    $name = $m->real_escape_string($name);
    $name = trim($name);
    // check if name is available
    $sqlt = "SELECT * FROM `group` WHERE `name` = '$name' AND `client_id` = '$client' AND `id` != '$id'";
    
    $rest = $m->query($sqlt);
    if ($rest->num_rows > 0){
      $response['content'] = "Group Name already exists.";
      log_and_respond($response);
      exit;
    }
    
    $sql = "UPDATE `group` SET `name` = '$name' WHERE `id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = "query error updating group name";
      log_and_respond($response);
    }
  }
  
  queueAPN($id,'group');
	
  $response['content'] = 'Update Successful: Group User/Content.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>