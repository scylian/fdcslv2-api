<?php
  include '../connection.php';
  
  include 'auth.php';
	    
  $params = ["name","location","display","icon_id","client"];
  
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){
      $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
      $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
      $_REQUEST[$value] = trim($_REQUEST[$value]);      
    } else {
      $response['content'] = $value. ' parameter missing.';
      log_and_respond($response);
      exit;
    }
  }
  
  extract($_REQUEST);
  if (!$foiAdmin){
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  // check if icon is valid
  validID('icon','id',$icon_id);
  
  // check for http/https
  $location = linkCheck($location);
  
  // check if link is valid
  $sqlt = "SELECT * FROM `content` WHERE `location` = '$location' AND `type` = 'link' AND `disabled` = 0 AND `client_id` = '$client'";
  $rest = $m->query($sqlt);
  if ($rest->num_rows > 0){
    $response['content'] = "Link already exists.";
    log_and_respond($response);    
  }

  $sql = "INSERT INTO `content` (`name`,`location`,`display`,`created_at`,`type`,`disabled`,`icon_id`,`client_id`) VALUES ('$name','$location','$display',CURRENT_TIMESTAMP,'link',0,'$icon_id','$client')";  
    
  if (!$m->query($sql)){
    $response['content'] = 'Query error adding link.';
    log_and_respond($response);    
  }
  $id = $m->insert_id;

  queueAPN($client,'client');
	
  $response['content'] = $id;
  $response['status'] = 'OK';
  log_and_respond($response);  

?>