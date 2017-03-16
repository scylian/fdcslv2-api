<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No user id set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["groups","name"];
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
    

  validID('user','id',$id);

  if (isset($groups)){
    // delete all prior associations
    $sql = "DELETE FROM `group_user` WHERE `user_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error deleting group/user associations.';
      log_and_respond($response);
      exit;
    }
    $a = json_decode($groups,1);    
    for ($i=0;$i<sizeof($a);$i++){
      $cid = $a[$i];     
      validID('group','id',$cid);

      $sql = "INSERT INTO `group_user` (`group_id`,`user_id`,`created_at`) VALUES ('$cid','$id',CURRENT_TIMESTAMP)";
      if (!$m->query($sql)){
        $response['content'] = 'Query error inserting: Group Users.'.$sql;
        log_and_respond($response);
        exit;
      }
    }
  }
  if (isset($name)){
    $sql2 = "SELECT COUNT(*) as 'count' FROM `user` WHERE `name` = '$name' AND `id` != '$id'";
    $res = $m->query($sql2);
    $count = $res->fetch_assoc()['count'];
    if ($count > 0){
      $response['content'] = "Name already exists for another user. try another.";
      log_and_respond($response);
    }
    $sql = "UPDATE `user` SET `name` = '$name' WHERE `id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error updating: Group Users name.';
      log_and_respond($response);
      exit;
    }
  }

  queueAPN($id,'user');
	
  $response['content'] = 'Update Successful: Group Users.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>