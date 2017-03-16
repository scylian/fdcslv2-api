<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No user id set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["groups","name","password","pin"];
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
  // update the user view
  if (isset($view)){
    validID('view','id',$view);
    
    // delete all prior associations
    $sql = "DELETE FROM `view_user` WHERE `user_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error deleting view/user associations.';
      log_and_respond($response);
      exit;
    }

    $sql = "INSERT INTO `view_user` (`view_id`,`user_id`,`created_at`) VALUES ('$view','$id',CURRENT_TIMESTAMP)";
    if (!$m->query($sql)){
      $response['content'] = 'Query error inserting: View Users.';
      log_and_respond($response);
      exit;
    }    
  }
  // update the name
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

  if (isset($password)||isset($pin)){
    $sql = "UPDATE `user` SET";
    if (isset($password)){
      $password = md5($password);
      $sql .= " `password` ='$password',";
    }
    if (isset($pin)){
      if (!is_numeric($pin)){
        $response['content'] = "Pin must only be numbers.";
        log_and_respond($response);
      }
      if (strlen($pin) < 4){
        $response['content'] = "Pin must be 4 #'s long.";
        log_and_respond($response);
      }
      $sql .= " `pin` = '$pin',";
    }
    $sql = rtrim($sql,',');
    $sql .= " WHERE `id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = 'Query error updating: UserPassword/Pin';
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