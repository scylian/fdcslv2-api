<?php
	include '../connection.php';
	include 'auth.php';

	if(!isset($_REQUEST['client_id'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  //check for required params
  $params = ["view_id","type"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

	extract($_REQUEST);	

	$info = array();
	$views = array();

	validID('view','id',$view_id);
	// ipad reso  2048x1536
	// iphone reso 750 X 1334
	// appletv reso 1920 x 1080
	
	// get view info per instance
  $sql = "SELECT `view_id`,`view_type`,`bg_color`,`padding`,`text_color`,`filter_color`,`logo_size`,`logo_offset`,`background_img_id`,`background_logo_id`,`filter_rgb`,`opacity` FROM `view_instance` WHERE `view_id` = '$view_id' AND `view_type` = '$type'";

	$res = $m->query($sql);
	$instances = array();
	while ($f = $res->fetch_assoc()){
		// get background img info
		$bgid = $f['background_img_id'];
		$f['background'] = '';
		$f['logo'] = '';
		$f['max_ratio'] = 0;
		$f['height'] = 0;
		$f['width'] = 0;
		$f['ratio'] = 0;
		if ($f['filter_rgb'] == '-1'){
			$f['filter_rgb'] = $f['filter_color'];
		}
		if ($f['view_type'] == 'ipad'){
			$wvar = 364;
			$hvar = 488;
		} else if ($f['view_type'] == 'iphone'){
			$wvar = 262;
			$hvar = 467;
		} else if ($f['view_type'] == 'appletv'){
			$wvar = 611;
			$hvar = 343;
		} else {
			$wvar = 529;
			$hvar = 331;
		}
		$sql1 = "SELECT `path`,`active` FROM `background_image` WHERE `id` = '$bgid'";
		$res1 = $m->query($sql1);
		while ($e = $res1->fetch_assoc()){
			$f['background'] = $baseURL.'bg/'.$e['path'];			
		}
		$logoid = $f['background_logo_id'];
		// get logo info
		$sql = "SELECT `path`,`x_offset`,`active` FROM `background_logo` WHERE `id` = '$logoid'";
		$res1 = $m->query($sql);
		while ($e = $res1->fetch_assoc()){
			$f['logo'] = $baseURL.'logo/'.$e['path'];		
			$logo = $f['logo'];

			list($w,$h) = getimagesize($logo);
			$ratio = $w/$h;
			$ratio_w = $w/$wvar;
			$ratio_h = $h/$hvar;
			if ($ratio_w > $ratio_h){
				$max_ratio = $ratio_w;
			} else {
				$max_ratio = $ratio_h;
			}
			$f['max_ratio'] = $max_ratio;
			$f['width'] = $w;
			$f['height'] = $h;
			$f['ratio'] = $ratio;
		}
		$instances = $f;
	}
	$info['info'] = $instances;
	
	// get list of filters
	$sql = "SELECT `id`,`name`,`published`,`order`,`loop` FROM `filter` WHERE `disabled` = 0 AND `view_id` = '$view_id' ORDER BY `order`";
  $res = $m->query($sql);
  $filters = array();
  while ($e = $res->fetch_assoc()){
    $filters[] = array(
        'id'=>$e['id'],
        'name'=>$e['name'],  
        'published'=>$e['published'],
        'order'=>$e['order'],
        'loop'=>$e['loop'],      
      );
  }    
	$info['filters'] = $filters;
	
	$response['content'] = $info;
	
	$response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>