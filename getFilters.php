<?php
  echo 'DEPRECATED';
  exit;
  include 'connection.php';
  include 'auth.php';

  $groups = array();

  $sql = "SELECT `id`,`name`,`published`,`order`,`loop` FROM `filter` WHERE `disabled` = 0 AND `client_id` = '$client_id'";
  $res = $m->query($sql);

  // optional user id being passed
  if(isset($_REQUEST['id'])){
    $id = $_REQUEST['id'];
  }

  while ($e = $res->fetch_assoc()){
    $fid = $e['id'];
    // if user id is passed -- get all published non disabled content for that users groups, only if its apart of this filter id (e['id'])
    if(isset($id)){
      $sql2 = "SELECT COUNT(*) as 'count' FROM `group_content` LEFT JOIN `group_user` ON (`group_user`.`group_id`=`group_content`.`group_id`) LEFT JOIN `filter_content` ON (`filter_content`.`content_id`=`group_content`.`content_id`) LEFT JOIN `content` ON (`content`.`id`=`group_content`.`content_id`) WHERE `group_user`.`user_id`='$id' AND `filter_content`.`filter_id`='$fid' AND `content`.`published`=1 AND `content`.`disabled`=0";
    }else{
      // if no user id passed -- get count of content for this filter that is published and not disabled
      $sql2 = "SELECT COUNT(*) as 'count' FROM `filter_content` LEFT JOIN `content` ON (`content`.`id`=`filter_content`.`content_id`) WHERE `filter_content`.`filter_id` = '$fid' AND `content`.`published`=1 AND `content`.`disabled`=0";
    }
    $r = $m->query($sql2);
    $count = $r->fetch_assoc()['count'];
    if ($count == 0){ // if no content in that set; do not pass to app
      continue;
    }

    $groups[] = array(
        'id'=>$e['id'],
        'name'=>$e['name'],  
        'published'=>$e['published'],
        'order'=>$e['order'],
      );
  }
	
  $response['content'] = $groups;
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>