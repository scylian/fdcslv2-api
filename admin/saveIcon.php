<?php    
  include '../connection.php';

  include 'auth.php';

  if(!isset($_REQUEST['client'])){
    $response['content'] = "No client ID sent.";
    log_and_respond($response);
    exit;
  }
  
  $client = $_REQUEST['client'];

  if (!$foiAdmin){
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  $target_dir = "../icons/";
  $target_file = $target_dir . basename($_FILES["file"]["name"]);
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if($check !== false) {
      $response['content']['size'] =  "File is an image - " . $check["mime"] . ".";        
    } else {
      $response['content'] =  "File is not an image.";        
      log_and_respond($response);      
    }
  }
  
  list($w,$h) = getimagesize($_FILES['file']['tmp_name']);
  if ($w != '240' && $h != '240'){
    $response['content'] = "Icon images must be 240 x 240";
    log_and_respond($response);
  }
  
    
  $filename = 'icon_'.$_FILES['file']['name'];
  $filename = preg_replace('/[^\w\._]+/', '_', $filename);
  $path = $target_dir.$filename;
  // clean filename
  $uts = date('U');
  // Check if file already exists
  if (file_exists($path)) {
    $path = $target_dir.$uts.'_'.$filename;
    $filename = $uts.'_'.$filename;
  }
  if (move_uploaded_file($_FILES['file']['tmp_name'], $path)){
    $sql = "INSERT INTO `icon` (`location`,`disabled`,`created_at`,`client_id`) VALUES ('$filename',0,CURRENT_TIMESTAMP,'$client')";
    if (!$m->query($sql)){
      $response['content'] = "Query error adding new icon";
      log_and_respond($response);
    }
    $icon_id = $m->insert_id;
    $response['status'] = 'OK';
    $response['content'] = array(
      'id'=>$icon_id,
      'path'=>$baseURL.'icons/'.$filename,
      'fname'=>$filename,
    );
  } else {
    $response['content']= "Sorry, there was an error uploading your icon file.";
  }
  log_and_respond($response);
  
?>