<?php  
  include '../connection.php';

  include 'auth.php';

  if (!isset($_REQUEST['id'])){
    $response['content'] = 'No content id set.';
    log_and_respond($response);
    exit;
  }
        
  $params = ["display","name","icon_id","published","type","share"];
  $isset = 0;
  foreach ($params as $key => $value) {
    if (isset($_REQUEST[$value])){   
      if (gettype($_REQUEST[$value]) == 'string'){
        $_REQUEST[$value] = stripslashes($_REQUEST[$value]);
        $_REQUEST[$value] = $m->real_escape_string($_REQUEST[$value]);
        $_REQUEST[$value] = trim($_REQUEST[$value]);        
      }
      $isset = 1;
    }
  }
  if ($isset == 0){
    $response['content'] = 'No params set to update.';
    log_and_respond($response);
    exit;
  }
  
  extract($_REQUEST);
  
  validID('content','id',$id);

  if (!$foiAdmin){
    $sql = "SELECT `client_id` FROM `content` WHERE `id` = '$id'";
    $res = $m->query($sql);
    $client = $res->fetch_assoc()['client_id'];
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  $curGroups = array();
  // update user content associations
  if (isset($groups)){          
    $a = json_decode($groups,1);
    if (sizeof($a) == 0){
      $sql = "DELETE FROM `group_content` WHERE `content_id` = '$id'";
      if (!$m->query($sql)){
        $response['content'] = 'Query error removing group_content.';
        log_and_respond($response);
      }
    } else {      
      // get currently associated groups for this piece of content
      $sql = "SELECT DISTINCT `group_id` FROM `group_content` WHERE `content_id` = '$id'";
      $res = $m->query($sql);
      while ($ef = $res->fetch_assoc()){
        $curGroups[] = $ef['group_id'];
      }  
      for ($i=0;$i<sizeof($a);$i++){
        $uid = $a[$i];            
        validID('group','id',$uid);

        queueAPN($uid,'group');
        $groupCheck = array_search($uid, $curGroups);
        
        if (gettype($groupCheck) == 'boolean'){
          $sql = "SELECT MAX(`order`) as 'max' FROM `group_content` WHERE `group_id` = '$uid'";
          $res = $m->query($sql);
          $max = $res->fetch_assoc()['max'];
          if ($max == null){
            $max = 1;
          } else {
            $max++;
          }
          $sql = "INSERT INTO `group_content` (`group_id`,`content_id`,`created_at`,`order`) VALUES ('$uid','$id',CURRENT_TIMESTAMP,'$max')";
          if (!$m->query($sql)){
            $response['content'] = 'Query error inserting: Content associations.';
            log_and_respond($response);
            exit;
          }          
        } else if (gettype($groupCheck) == 'integer'){        
          unset($curGroups[$groupCheck]);
        }
      }    
    }
  }
  for ($i=0;$i<sizeof($curGroups);$i++){
    $g = $curGroups[$i];
    $sql = "DELETE FROM `group_content` WHERE `group_id` = '$g'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. group_content clean up.";
      log_and_respond($response);
    }
  }
  $curFilters = array();
  // update user content associations
  if (isset($filters)){
    // get max order for next  
    $a = json_decode($filters,1);
    if (sizeof($a) == 0){
      $sql = "DELETE FROM `filter_content` WHERE `content_id` = '$id'";
      if (!$m->query($sql)){
        $response['content'] = 'Query error removing filter_content.';
        log_and_respond($response);
      }
    } else {      
      // get currently associated groups for this piece of content
      $sql = "SELECT DISTINCT `filter_id` FROM `filter_content` WHERE `content_id` = '$id'";
      $res = $m->query($sql);
      while ($ef = $res->fetch_assoc()){
        $curFilters[] = $ef['filter_id'];
      }
      for ($i=0;$i<sizeof($a);$i++){
        $uid = $a[$i];
        validID('filter','id',$uid);   
        // check if exists in the filter_content table
        $filterCheck = array_search($uid, $curFilters);
        if (gettype($filterCheck) == 'boolean'){
          $sql2 = "SELECT MAX(`order`) as 'max' FROM `filter_content` WHERE `filter_id` = '$uid'";
          $res = $m->query($sql2);
          $max = $res->fetch_assoc()['max'];
          if ($max == null){
            $max = 1;
          } else {
            $max++;          
          }
          $sql = "INSERT INTO `filter_content` (`filter_id`,`content_id`,`created_at`,`order`) VALUES ('$uid','$id',CURRENT_TIMESTAMP,'$max')";
          if (!$m->query($sql)){
            $response['content'] = 'Query error inserting: Content associations. filters';
            log_and_respond($response);
            exit;
          }
        } else if (gettype($filterCheck) == 'integer'){
          unset($curFilters[$filterCheck]);
        }
      }
    }
  }
  for ($i=0;$i<sizeof($curFilters);$i++){
    $g = $curFilters[$i];
    $sql = "DELETE FROM `filter_content` WHERE `filter_id` = '$g'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. filter_content clean up.";
      log_and_respond($response);
    }
  }

  $sql = "UPDATE `content` SET"; 

  // update display if set
  if (isset($display)){
    $sql .= " `display` = '$display',";
  }
  if (isset($name)){
    $sql .= " `name` = '$name',";
  }
  if (isset($icon_id)){
    validID('icon','id',$icon_id);
    $sql .= " `icon_id` = '$icon_id',";
  }
  if (isset($share)){
    $sql .= " `share` = '$share',"; 
  }
  if (isset($type)){
    if (!isset($location)){
      $response['content'] = "Missing link location/address.";
      log_and_respond($response);
    }
    // add http if needed
    $location = linkCheck($location);

    // check if link is valid
    $sqlt = "SELECT * FROM `content` WHERE `location` = '$location' AND `type` = 'link' AND `disabled` = 0 AND `client_id` = '$clientID' AND `id` != '$id'";
    $rest = $m->query($sqlt);
    if ($rest->num_rows > 0){
      $response['content'] = "Link already exists.";
      log_and_respond($response);    
    }
    $sql .= " `location` = '$location',";
  }
  if (isset($published)){
    $sql .= " `published` = '$published',"; 
  }

  $sql = rtrim($sql,',');

  $sql .= " WHERE `id` = '$id'";

  if (!$m->query($sql)){
    $response['content'] = "Query error. Updating content display.";
    log_and_respond($response);    
  }

  if (isset($vid)){
    $sql = "UPDATE `view_content` SET ";
    if (isset($looping)){
      if (!isset($refresh)||!isset($loop)){
        $response['content'] = 'Missing loop duration/refresh duration/viewid';
        log_and_respond($response);
      }
      $sql .= "`loop` = '$loop',`refresh` = '$refresh',";
    }
    if (isset($share)){
      $sql .= "`share` = '$share',";
    }
    $sql = rtrim($sql, ',');
    $sql .= " WHERE `content_id` = '$id' AND `view_id` = '$vid'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. Update Content - update loop/refresh time.";
      log_and_respond($response);
    }
  }

  queueAPN($clientID,'client');
  	
  $response['content'] = 'Update Successful: Content.';
  $response['status'] = 'OK';
  log_and_respond($response);
  exit;

?>