<?php
	include '../connection.php';
	include 'auth.php';

	//check for required params
  $params = ["client_id","text"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

	extract($_REQUEST);

	// add new color to db
	$sql = "UPDATE `client` SET `text_color` = '$text' WHERE `id` = '$client_id'";
	if (!$m->query($sql)){
		$response['content'] = "Query error. updating text color.";
		echo json_encode($response);
		exit;
	}

	queueAPN($client_id,'client');
	
	$response['content'] = "text updated successfully.";
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;