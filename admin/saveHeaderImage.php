<?php    
  include '../connection.php';

  include 'auth.php';

  //check for required params
  $params = ["id","client"];

  foreach ($params as $key => $value) {
    if(!isset($_REQUEST[$value])){
      $response['content'] = 'No '.$value.' set.';
      log_and_respond($response);      
    }
  }

  extract($_REQUEST);
  if (!$foiAdmin){
    if (intval($client) != intval($clientID)){
      $response['content'] = "Not authorized. Client ID does not match.";
      log_and_respond($response);
    }
  }

  validID('client','id',$id);
  
  $target_dir = "../logo/";
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
    
  // clean filename
  $filename = 'logo_'.$_FILES['file']['name'];
  $filename = preg_replace('/[^\w\._]+/', '_', $filename);  
  $path = $target_dir.$filename;

  $uts = date('U');
  // Check if file already exists
  if (file_exists($path)) {
    $path = $target_dir.$uts.'_'.$filename;
    $filename = $uts.'_'.$filename;
  }  
  
  if (move_uploaded_file($_FILES['file']['tmp_name'], $path)){
    // unset all other backgrounds for this client
    $sql = "UPDATE `background_logo` SET `active` = 0 WHERE `client_id` = '$id'";
    if (!$m->query($sql)){
      $response['content'] = "Query error. unset background_Logo";
      log_and_respond($response);
    }

    $sql = "INSERT INTO `background_logo` (`client_id`,`path`,`x_offset`,`active`,`created_at`) VALUES ('$id','$filename',0,1,CURRENT_TIMESTAMP)";
    if (!$m->query($sql)){
      $response['content'] = "Query error inserting new header logo image";
      log_and_respond($response);
    }
    $file_id = $m->insert_id;    

    $response['status'] = 'OK';
    $response['content'] = array(
      'id'=>$file_id,
      'path'=>$baseURL.'logo/'.$filename,
      'fname'=>$filename,            
    );
  } else {
    $response['content']= "Sorry, there was an error uploading your file.";
  }
  log_and_respond($response);  
?>
