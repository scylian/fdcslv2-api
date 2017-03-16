<?php  
  include 'connection.php';
  include 'auth.php';  

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No User ID sent.";
    log_and_respond($response);
    exit;
  }  
  extract($_REQUEST);
  
  // check if user id is valid
  validID('user','id',$id);
  
  $vi = array(); // main array
  $views = array(); // all views
  $files = array(); // all files
  $vc = array(); // view content
  $fids = array(); // all file ids

  // get groups associated to this user
  $sql = "SELECT `view_id`,`name` FROM `view_user` LEFT JOIN `view` ON (`view_user`.`view_id` = `view`.`id`) WHERE `user_id` = '$id'";
  if (isset($_REQUEST['timestamp'])){
    $timestamp = strtotime($_REQUEST['timestamp']);
    $timestamp = date("Y-m-d H:i:s",$timestamp);
    $sql .= " AND `view_user`.`updated_at` > '$timestamp'";
  }
  $res = $m->query($sql);
  if ($res->num_rows == 0){
    $response['content'] = "No view for this user.";
    log_and_respond($response);
  }
  $r = $res->fetch_assoc();
  $vi['view_id'] = $r['view_id'];
  $vid = $r['view_id'];
  $vi['view_name'] = $r['name'];    

  // get each view instance
  $instances = array();
  $sql = "SELECT `view_type`,`background_img_id`,`background_logo_id`,`bg_color`,`padding`,`opacity`,`text_color`,`filter_color`,`logo_size`,`updated_at` FROM `view_instance` WHERE `view_id` = '$vid'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $e['background'] = '';
    $e['logo'] = '';
    $bgid = $e['background_img_id'];
    $logoid = $e['background_logo_id'];
    $e['padding'] = $e['padding']*2;
    $ls = floatval($e['logo_size'])+2;
    if ($e['view_type'] == 'ipad'){
      $dw = 364;
      $nw = 768;
    } else if ($e['view_type'] == 'iphone'){
      $dw = 262;
      $nw = 375;
    } else { // appletv
      $dw = 611;
      $nw = 1920;
    }
    $p = $ls/$dw;
    $e['logo_width'] = $p*$nw;
    $e['logo_size'] = $ls/$dw;

    $sql1 = "SELECT `path`,`active` FROM `background_image` WHERE `id` = '$bgid'";
    $res1 = $m->query($sql1);
    while ($f = $res1->fetch_assoc()){
      $e['background'] = $baseURL.'bg/'.$f['path'];     
    }
    $sql = "SELECT `path`,`active` FROM `background_logo` WHERE `id` = '$logoid'";
    $res1 = $m->query($sql);
    while ($f = $res1->fetch_assoc()){
      $e['logo'] = $baseURL.'logo/'.$f['path'];   
    }
    list($w,$h) = getimagesize($e['logo']);
    $ratio = $w/$h;
    $e['logo_height'] = $e['logo_width']/$ratio;
    $instances[] = $e;
  }
  $vi['instances'] = $instances;

  // get the filters for that view
  $filters = array();
  $sql = "SELECT `id`,`name`,`order`,`published`,`updated_at`,`loop` FROM `filter` WHERE `view_id` = '$vid' AND `disabled` = 0 ORDER BY `order`";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $fid = $e['id'];
    $content = array();
    $sql1 = "SELECT `content_id`,`order` FROM `filter_content` LEFT JOIN `content` ON (`filter_content`.`content_id` = `content`.`id`) WHERE `filter_id` = '$fid' AND `content`.`disabled` = 0 ORDER BY `order`";
    $ras = $m->query($sql1);
    while ($f = $ras->fetch_assoc()){      
      if (!in_array($f['content_id'], $fids)){
        $fids[] = $f['content_id'];
      }
      $content[] = $f;
    }
    $e['content'] = $content;
    $filters[] = $e;
  }
  $vi['filters'] = $filters;

  // get content for each view
  $sql = "SELECT `vc`.`content_id`,`vc`.`order`,`vc`.`share` as 'view_content_share',`loop` as 'loop_duration',`refresh` FROM `view_content` vc LEFT JOIN `content` c ON (`vc`.`content_id` = `c`.`id`) WHERE `vc`.`view_id` = '$vid' AND `c`.`disabled` = 0 AND `vc`.`published` = 1 ORDER BY `order`";  
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){    
    if (!in_array($e['content_id'], $fids)){      
      $fids[] = $e['content_id'];
    }    
    $vc[] = $e;
  }
  $vi['view_content'] = $vc;  

  // get file info with icon info
  for ($i=0;$i<sizeof($fids);$i++){
    $fid = $fids[$i];

    $sql = "SELECT `content`.`id`,`content`.`name`, `content`.`display`,`content`.`location`,`content`.`icon_id`,`type`,`share` FROM `content` WHERE `content`.`id` = '$fid' AND `disabled` = 0";
    $res = $m->query($sql);
    if ($res->num_rows == 0){
      continue;
    }
    $e = $res->fetch_assoc();
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
      $e['location'] = linkCheck($e['location']);        
      $e['content_type'] = 'link';
    }
    $e['icon_location'] = '';
    $sql = "SELECT `location` FROM `icon` WHERE `id` = '$icon_id' AND `disabled` = 0";
    $r = $m->query($sql);
    while ($ef = $r->fetch_assoc()){
      $e['icon_location'] = $baseURL.'icons/'.$ef['location'];
    }

    $e['loop_duration'] = 0;
    $e['view_content_share'] = -1;
    $e['refresh'] = 0;
    $e['order'] = -1;
    $sql2 = "SELECT `loop` AS 'loop_duration', `refresh`,`order`,`share` AS 'view_content_share' FROM `view_content` WHERE `content_id` = '$fid' AND `view_id` = '$vid'";
    $res2 = $m->query($sql2);
    if ($res2->num_rows > 0){
      $r = $res2->fetch_assoc();
      foreach ($r as $key => $value) {
        $e[$key] = $value;
      }
    }
    $files[] = $e;    
  }
  $vi['content'] = $files;

  $response['content'] = $vi;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
