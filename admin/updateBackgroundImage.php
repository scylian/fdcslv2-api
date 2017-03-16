<?php
	include '../connection.php';
	include 'auth.php';

	//check for required params
  $params = ["view","type"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

	extract($_REQUEST);

	// add new color to db
	$sql = "UPDATE `view_instance` SET `background_img_id` = -1 WHERE `view_id` = '$view' AND `view_type` = '$type'";
	if (!$m->query($sql)){
		$response['content'] = "Query error. updating background image.";
		echo json_encode($response);
		exit;
	}

	queueAPN($client_id,'client');

	$response['content'] = "background updated successfully.";
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;