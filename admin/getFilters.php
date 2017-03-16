<?php	 
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  $client = $_REQUEST['client'];
  $groups = array();
  $views = array();

  $sql = "SELECT `id`,`name` FROM `view` WHERE `client_id` = '$client'";
  $res = $m->query($sql);
  while ($e = $res->fetch_assoc()){
    $vid = $e['id'];
    
    // get all filters for each view
    $sql = "SELECT `id`,`name`,`published`,`order` FROM `filter` WHERE `disabled` = 0 AND `view_id` = '$vid' ORDER BY `order`";
    $res = $m->query($sql);

    while ($er = $res->fetch_assoc()){
      if (!in_array($er['id'], $views)){
        $groups[] = array(
            'view_id'=>$vid,
            'view_name'=>$e['name'],
            'id'=>$er['id'],
            'name'=>$er['name'],  
            'published'=>$er['published'],
            'order'=>$er['order'],  
          );
        $views[] = $er['id'];        
      }
    }
  }

	
  $response['content'] = $groups;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>