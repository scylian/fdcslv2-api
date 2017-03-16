<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['client_id'])){
    $response['content'] = 'No client_id set.';
    log_and_respond($response);    
  }

  $params = ["logo","x_offset"];
  
  foreach ($params as $key => $value) {
    if (!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set to update.';
      log_and_respond($response);          
    }
    $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
    $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
    $_REQUEST[$value] = trim($_REQUEST[$value]);
  }
  
  extract($_REQUEST);

  if (!$foiAdmin){    
    if (intval($client_id) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  validID('background_logo','id',$logo);

  $sql = "UPDATE `background_logo` SET `x_offset` = '$x_offset' WHERE `id` = '$logo'"; 

  if (!$m->query($sql)){
    $response['content'] = 'Query error updating: Client Info.';
    log_and_respond($response);
    exit;
  }

  queueAPN($client_id,'client');
	
  $response['content'] = 'Update Successful: Client Info.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>