<?php  
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['id'])){
    $response['content'] = "No filter ID sent.";
    log_and_respond($response);
    exit;
  }
  // $cid = $_REQUEST['id'];
  $content = array();
  extract($_REQUEST);

  validID('filter','id',$id);
  $vid = 0;
  $sql = "SELECT `view_id` FROM `filter` WHERE `id` = '$id'";
  $res = $m->query($sql);
  if ($res->num_rows > 0){
    $vid = $res->fetch_assoc()['view_id'];
  }

  // get all associated filters
  $sql = "SELECT `name`,`display`,`content`.`id`,`order`,`icon_id`,`type`,`content`.`id`,`content`.`location` FROM `filter_content` LEFT JOIN `content` ON (`content`.`id` = `filter_content`.`content_id`) WHERE `filter_content`.`filter_id` = '$id' AND `content`.`disabled` = 0 ORDER BY `order` ASC";
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
    $cid = $e['id'];
    
    $e['published'] = 0;
    $sql2 = "SELECT `published`,`share`,`loop`,`refresh` FROM `view_content` WHERE `content_id` = '$cid' AND `view_id` = '$vid'";
    $res2 = $m->query($sql2);
    if ($res2->num_rows > 0){
      $r = $res2->fetch_assoc();
      $e['published'] = $r['published'];
      $e['loop'] = $r['loop'];
      $e['refresh'] = $r['refresh'];
      $e['share'] = $r['share'];
    }
    $content[] = $e;
  }

  $response['content'] = $content;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>