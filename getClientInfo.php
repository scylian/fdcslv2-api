<?php
	include 'connection.php';
	include 'auth.php';

	extract($_REQUEST);

	$bg = '';
	$logo = '';
	$offet = 0;

	$sql = "SELECT `id`,`path`,`active` FROM `background_image` WHERE `client_id` = '$client_id' AND `active` = 1";
	$res = $m->query($sql);

	while ($e = $res->fetch_assoc()){
		$bg = $baseURL.'bg/'.$e['path'];
	}

	$sql = "SELECT `path`,`x_offset`,`active` FROM `background_logo` WHERE `client_id` = '$client_id' AND `active` = 1";
	$res = $m->query($sql);
	while ($e = $res->fetch_assoc()){
		$logo = $baseURL.'logo/'.$e['path'];
		$offset = $e['x_offset'];
	}

	$bgcolor = -1;
	$textcolor = -1;
	$filtercolor = -1;
	$sql = "SELECT `bg_color`,`text_color`,`filter_color` FROM `client` WHERE `id` = '$client_id'";
	$res = $m->query($sql);
	while ($e = $res->fetch_assoc()){
		$bgcolor = $e['bg_color'];
		$textcolor = $e['text_color'];
		$filtercolor = $e['filter_color'];
	}

	$features = array();
  $sql = "SELECT `feature_id`,`name` FROM `feature_rel` LEFT JOIN `feature` ON (`feature_rel`.`feature_id` = `feature`.`id`) WHERE `feature_rel`.`client_id` = '$client_id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $features[] = $e;
  }

	$response['content'] = array(
		'background'=>$bg,
		'background_color'=>$bgcolor,
		'text_color'=>$textcolor,
		'filter_color'=>$filtercolor,
		'logo'=>$logo,
		'logo_x_offset'=>$offset,
		'features'=>$features,
	);
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>