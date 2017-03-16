<?php
	include '../connection.php';
	include 'auth.php';

	if(!isset($_REQUEST['client_id'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }

	extract($_REQUEST);	
	$info = array();	
	$views = array();
  $content = array();
	
	// get views
	$sql = "SELECT `id` as 'view_id',`name` FROM `view` WHERE `client_id` = '$client_id'";	
	$res = $m->query($sql);	
	while ($e = $res->fetch_assoc()){
		$vid = $e['view_id'];				
  	
  	// get instances
	  $sql = "SELECT `view_id`,`view_type` FROM `view_instance` WHERE `view_id` = '$vid'";
		$res2 = $m->query($sql);
		$instances = array(); 
		while ($f = $res2->fetch_assoc()){
			$instances[] = $f;
		}
		$e['instances'] = $instances;
		$views[] = $e;
	}
	$info['views'] = $views;

	// get content for client	
  $sql = "SELECT `id`,`name`,`display`,`location`,`icon_id`,`type`,`published` FROM `content` WHERE `content`.`disabled` = 0 AND `client_id` = '$client_id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $icon_id = $e['icon_id'];
    if ($e['type'] != 'link'){
      $e['location'] = $baseFileURL.$e['location'];
      $ext = pathinfo($e['location'],PATHINFO_EXTENSION);
      if ($ext == 'jpg'||$ext=='jpeg'||$ext== 'png'){
        $e['content_type'] = 'image';
      } else if ($ext == 'mp4'||$ext == 'mov'||$ext == 'm4v'){
        $e['content_type'] = 'video';
      } else if ($ext == 'pdf'){
        $e['content_type'] = 'pdf';
      }        
    } else {
      $e['content_type'] = 'link';
    } 
    $e['icon_location'] = '';
    $sql = "SELECT `location` FROM `icon` WHERE `id` = '$icon_id' AND `disabled` = 0";
    $r = $m->query($sql);
    while ($ef = $r->fetch_assoc()){
      $e['icon_location'] = $baseURL.'icons/'.$ef['location'];
    }
    $content[] = $e;
  }
  $info['files'] = $content;

	$response['content'] = $info;
	
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>