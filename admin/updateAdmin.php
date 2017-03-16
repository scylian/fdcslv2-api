<?php  
  include '../connection.php';

  include 'auth.php';
  
  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No content id set.';
    log_and_respond($response);
    exit;
  }
  
  $params = ["name","email","password","active"];

  $isset = 0;
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){      
      $isset = 1;
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);
    }
  }
  if ($isset == 0){
    $response['content'] = 'No params set to update.';
    log_and_respond($response);
    exit;
  }
    
  extract($_REQUEST);

  validID('admin_user','id',$id);

  $sql = "SELECT `client_id`,`user` FROM `admin_user` WHERE `id` = '$id'";
  $res = $m->query($sql);
  $c = $res->fetch_assoc();
  $client = $c['client_id'];
  $username = $c['user'];
  
  if (!$foiAdmin){    
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }


  $sql = "UPDATE `admin_user` SET";

  if (isset($name)){    
    // check if username is avialable
    $sql2 = "SELECT COUNT(*) as 'count' FROM `admin_user` WHERE `user` = '$name' AND `id` != '$id'";
    $res = $m->query($sql2);
    $count = $res->fetch_assoc()['count'];
    if ($count > 0){
      $response['content'] = "username already exists for another user. try another.";
      log_and_respond($response);
    }
    $sql .= " `user` = '$name',";
  }

  if (isset($email)){    
    $sql .= " `email` = '$email',";
  }

  if (isset($active)){    
    // cannot disable self    
    if ($user == $username){
      $response['content'] = "Cannot disable currently logged in user.";
      log_and_respond($response);
    }
    $sql .= " `active` = '$active',";
  }

  if (isset($password)){
    $password = md5($password);
    $sql .= " `password` = '$password', `ftl` = 1,";
  }

  $sql = rtrim($sql,',');

  $sql .= " WHERE `id` = '$id'"; 

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: Admin user.'.$sql;
    log_and_respond($response);
    exit;
  }
	
  $response['content'] = 'Update Successful: Admin User.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>