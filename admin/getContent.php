<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  
  $client = $_REQUEST['client'];
  
  $content = array();
  
  // get all content
  $sql = "SELECT `id`,`name`,`display`,`location`,`icon_id`,`type`,`published`,`share` FROM `content` WHERE `content`.`disabled` = 0 AND `client_id` = '$client'";
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

  $response['content'] = $content;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>