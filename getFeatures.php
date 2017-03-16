<?php	 
  include 'connection.php';
  include 'auth.php';

  $features = array();

  $sql = "SELECT `id`,`name` FROM `feature`";

  $res = $m->query($sql);

  while ($e = $res->fetch_assoc()){
    $features[] = $e;
  }
	
  $response['content'] = $features;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>