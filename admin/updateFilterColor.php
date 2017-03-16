<?php
	include '../connection.php';
	include 'auth.php';

	//check for required params
  $params = ["client_id","filter"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

	extract($_REQUEST);

	// add new color to db
	$sql = "UPDATE `client` SET `filter_color` = '$filter' WHERE `id` = '$client_id'";
	if (!$m->query($sql)){
		$response['content'] = "Query error. updating filter color.";
		echo json_encode($response);
		exit;
	}

	queueAPN($client_id,'client');

	$response['content'] = "filter updated successfully.";
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;