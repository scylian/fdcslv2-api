<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['files'])){
    $response['content'] = 'No content array set.';
    log_and_respond($response);
    exit;
  }
  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No view id set.';
    log_and_respond($response);
    exit;
  }

  extract($_REQUEST);

  validID('view','id',$id);

  if (!$foiAdmin){    
    if (intval($client_id) != intval($clientID)){ // pulls clientID from auth.php
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  if ($files != '[]'){
    $files = json_decode($files,1);
    if (!$files){
      $response['content'] = "Invalid files array.";
      log_and_respond($response);
    }    
  }

  $sql = "DELETE FROM `view_content` WHERE `view_id` = '$id'";
  if (!$m->query($sql)){
    $response['content'] = "Query error. Deleting view_content.";
    log_and_respond($response);
  }
  if ($files != '[]'){
    for($i=0;$i<sizeof($files);$i++){
      $e = $files[$i];
      $cid = $e['id'];
      validID('content','id',$cid);
      $order = $e['order'];
      if ($order == -1){
        $sql = "SELECT max(`order`) as 'max' FROM `view_content` WHERE `view_id` = '$id'";
        $res = $m->query($sql);
        $max = $res->fetch_assoc()['max'];
        if ($max == null){
          $max = 1;
        } else {
          $max++;
        }
        $order = $max;
      }
      $pub = $e['published'];
          
      $sql = "INSERT INTO `view_content` (`view_id`,`content_id`,`published`,`created_at`,`order`) VALUES ('$id','$cid','$pub',CURRENT_TIMESTAMP,'$order')";
      if (!$m->query($sql)){
        $response['content'] = "Query error. inserting view content.";
        log_and_respond($response);
      }
    }    
  }

  if (isset($users)){    
    if ($users != '[]'){
      $users = json_decode($users,1);
      if (!$users){
        $response['content'] = "Invalid users object/array.";
        log_and_respond($response);
      }      
    }
    $sql = "DELETE FROM `view_user` WHERE `view_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. Deleting view_users.";
      log_and_respond($response);
    }
    if ($users != '[]'){
      for ($i=0;$i<sizeof($users);$i++){
        $uid = $users[$i]['id'];
        validID('user','id',$uid);
      
        // delete all prior associations
        $sql = "DELETE FROM `view_user` WHERE `user_id` = '$uid'";
        if (!$m->query($sql)){
          $response['content'] = 'Query error deleting view/user associations.';
          log_and_respond($response);        
        }

        $sql = "INSERT INTO `view_user` (`view_id`,`user_id`,`created_at`) VALUES ('$id','$uid',CURRENT_TIMESTAMP)";
        if (!$m->query($sql)){
          $response['content'] = 'Query error inserting: View Users.';
          log_and_respond($response);        
        }
      }      
    }
  }

  if (isset($name)){
    $sql = "UPDATE `view` SET `name` = '$name' WHERE `id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. Updating view name.";
      log_and_respond($response);
    }
  }

  queueAPN($client_id,'client');
  
  $response['content'] = "view content updated successfully.";
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>