<?php    
  include '../connection.php';

  include 'auth.php';

  //check for required params
  $params = ["id","type","client","view","viewType"];

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

  if ($type == 'logo'){
    $target_dir = "../logo/";
  } else if ($type == 'bg'){
    $target_dir = "../bg/";
  }
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
  if ($type == 'bg'){
    if ($viewType == 'appletv'){
      if (($h != '1080' || $w != '1920')){
        $response['content'] = "Background images must be 1080p. (1920px x 1080px)";
        log_and_respond($response);
      }  
    } else{
      if (($w != '2048' || $h != '2048')){
        $response['content'] = "Background images must be 2048px x 2048px";
        log_and_respond($response);
      }
    } 
  }
    
  $filename = $type.'_'.$_FILES['file']['name'];
  $filename = preg_replace('/[^\w\._]+/', '_', $filename);  
  $path = $target_dir.$filename;
  // clean filename

  $uts = date('U');
  // Check if file already exists
  if (file_exists($path)) {
    $path = $target_dir.$uts.'_'.$filename;
    $filename = $uts.'_'.$filename;
  }
  $max = getMaxRatio($viewType,$view,$w,$h);
  
  if (move_uploaded_file($_FILES['file']['tmp_name'], $path)){
    if ($type == 'bg'){      
      $sql = "INSERT INTO `background_image` (`client_id`,`path`,`active`,`created_at`) VALUES ('$id','$filename',0,CURRENT_TIMESTAMP)";
    } else {
      $sql = "INSERT INTO `background_logo` (`client_id`,`path`,`x_offset`,`active`,`created_at`) VALUES ('$id','$filename',0,0,CURRENT_TIMESTAMP)";
    }
    if (!$m->query($sql)){
      $response['content'] = "Query error inserting new image ".$type;
      log_and_respond($response);
    }
    $file_id = $m->insert_id;
    $response['status'] = 'OK';
    $response['content'] = array(
      'id'=>$file_id,
      'path'=>$baseURL.$type.'/'.$filename,
      'fname'=>$filename,
      "width"=>$w,
      "height"=>$h,
      "ratio"=>$w/$h,
      'logo_width'=>$max,
    );
  } else {
    $response['content']= "Sorry, there was an error uploading your file.";
  }
  log_and_respond($response);

function getMaxRatio($vtype,$v,$w,$h){
  if ($vtype == 'ipad'){
    $wvar = 324;
    $hvar = 488;
  } else if ($vtype == 'iphone'){
    $wvar = 247;
    $hvar = 467;
  } else {
    $wvar = 550;
    $hvar = 343;
  }
  $ratio_w = $w/$wvar;
  $ratio_h = $h/$hvar;
  if ($ratio_w > $ratio_h){
    $max_ratio = $ratio_w;
  } else {
    $max_ratio = $ratio_h;
  }
  $newW = $w/$max_ratio;

  return $newW;
}
  
?>
