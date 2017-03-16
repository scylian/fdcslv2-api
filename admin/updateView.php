<?php
	include '../connection.php';
	include 'auth.php';

	if(!isset($_REQUEST['id'])){
    $response['content'] = "No view ID sent.";
    log_and_respond($response);
    exit;
  }  

  $params = ["type","bg","text","filter","offset","logo_width","background_logo_id","background_img_id","padding","filter_rgb","opacity"];
  $isset = 0;

  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){      
      $isset = 1;
    }
  }
  if ($isset == 0){
  	if (!isset($_REQUEST['name'])){
	    $response['content'] = 'No params set to update.';
	    log_and_respond($response);
	    exit;  		
  	}
  }
  extract($_REQUEST);
  if (!$foiAdmin){
    if (intval($client_id) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }  
  }
  
  // check if view is valid
  validID('view','id',$id);

  if (isset($name)){
  	$sql = "UPDATE `view` SET `name` = '$name' WHERE `id` = '$id'";
  	if (!$m->query($sql)){
  		$response['content'] = "Query error. Updating view name.";
  		log_and_respond($response);
  	}
  }

	if ($isset == 1){
	  $sql = "UPDATE `view_instance` SET";
	  if (isset($background_img_id)){
	  	$sql .= " `background_img_id` = '$background_img_id',";
	  }
	  if (isset($background_logo_id)){
	  	$sql .= " `background_logo_id` = '$background_logo_id',";
	  }
	  if (isset($bg)){
	  	$sql .= " `bg_color` = '$bg',";
	  }
	  if (isset($text)){
	  	$sql .= " `text_color` = '$text',";
	  }
	  if (isset($filter)){
	  	$sql .= " `filter_color` = '$filter',";
	  }
	  if (isset($filter_rgb)){
	  	$sql .= " `filter_rgb` = '$filter_rgb',";
	  }
	  if (isset($opacity)){
	  	$sql .= " `opacity` = '$opacity',";
	  }
	  if (isset($padding)){
	  	$sql .= " `padding` = '$padding',";
	  }
	  if (isset($offset)){
	  	$sql .= " `logo_offset` = '$offset',";
	  }
	  if (isset($logo_width)){	  	
	  	$sql .= " `logo_size` = '$logo_width',";
	  }

	  $sql = rtrim($sql,',');

	  $sql .= " WHERE `view_id` = '$id' AND `view_type` = '$type'";

	  if (!$m->query($sql)){
	  	$response['content'] = "Query error. Updating view/instance.";
	  	log_and_respond($response);
		}  		
	}

	// update filters
	if (isset($filters)){
		$filters = json_decode($filters,1);
		for($i=0;$i<sizeof($filters);$i++){
	    $e = $filters[$i];	    
	    $fid = $e['id'];
	    $forder = $e['order'];
	    $sql = "UPDATE `filter` SET `order` = '$forder' WHERE `id` = '$fid'";
	    if (!$m->query($sql)){
	      $response['content'] = "Query error. updating filter order.";
	      log_and_respond($response);
	    }
	  }
	}

	// update content
	if (isset($content)){
    $a = json_decode($content,1);
    $sql = "UPDATE `view_content` SET `order` = -1,`published` = 0 WHERE `view_id` = '$id'";
    if (!$m->query($sql)){
    	$response['content'] = "Query error. removing view_content order.";
    	log_and_respond($response);
    }			
    if (isset($curFilter)){ // VIEW SPECIFIC
			$sql = "DELETE FROM `filter_content` WHERE `filter_id` = '$curFilter'";
			if (!$m->query($sql)){
				$response['content'] = "Query error. removing filter_content.";
				log_and_respond($response);
			}
		}
    for ($i=0;$i<sizeof($a);$i++){
      $cid = $a[$i]['id'];
      $order = $a[$i]['order'];
      validID('content','id',$cid);
    	$sql = "UPDATE `view_content` SET `published` = 1,`order` = '$order' WHERE `content_id` = '$cid'";
      if (!$m->query($sql)){
        $response['content'] = 'Query error inserting: view Content published.';
        log_and_respond($response);
        exit;
      }
      if (isset($curFilter)){
      	$sql = "INSERT INTO `filter_content` (`filter_id`,`content_id`,`order`,`created_at`) VALUES ('$curFilter','$cid','$order',CURRENT_TIMESTAMP)";
	      if (!$m->query($sql)){
	        $response['content'] = 'Query error inserting: filter_content published.';
	        log_and_respond($response);
	        exit;
	      }
      }
    }		
  }
  queueAPN($client_id,'client');

	$response['status'] = "OK";
	$response['content'] = "View updated successfully.";
	log_and_respond($response);

?>