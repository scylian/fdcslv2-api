<?php  
  include '../connection.php';

  include 'auth.php';
   
  if(!isset($_REQUEST['id'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  extract($_REQUEST);
  
  $path = -1;
  
  $sql = "SELECT `path` FROM `background_logo` WHERE `client_id` = '$id' AND `active` = 1 ORDER BY `updated_at` ASC LIMIT 1";  
  $res = $m->query($sql);
  
  while ($e = $res->fetch_assoc()){
    $path = $baseURL.'logo/'.$e['path'];
  }

  $link = '';
  $sql = "SELECT `analytics_link` FROM `client` WHERE `id` = '$id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $link = $e['analytics_link'];
  }

  $features = array();
  $sql = "SELECT `feature_id`,`name` FROM `feature_rel` LEFT JOIN `feature` ON (`feature_rel`.`feature_id` = `feature`.`id`) WHERE `feature_rel`.`client_id` = '$id'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $features[] = $e['name'];
  }
  
  $response['content'] = array(
    'path'=>$path,
    'link'=>$link,
    'features'=>$features,
  );
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>
