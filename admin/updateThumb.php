<?php
  include '../connection.php';

  include 'auth.php';

  //check if file id set
  if(!isset($_REQUEST['id'])){
    $response['content'] = 'No file id set.';
    log_event();
    echo json_encode($response);
    exit;
  }

  $fid = $_REQUEST['id'];

  $target_dir = $targetDir.'thumbs/';
  $target_file = $target_dir.basename($_FILES["file"]["name"]);
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["file"]["tmp_name"]);
    if($check !== false) {
      $response['content']['size'] =  "File is an image - " . $check["mime"] . ".";        
    } else {
      $response['content'] =  "File is not an image.";        
      log_event();
      echo json_encode($response);
      exit;
    }
  }
  $uts = date('U');
  // Check if file already exists
  if (file_exists($target_file)) {
    $target_file = $target_dir .$uts.'_'. basename($_FILES["file"]["name"]);
  }

  $_FILES["file"]["name"] = preg_replace('/[^\w\._]+/', '_', $_FILES["file"]["name"]);

  function resize($width,$imageFileType,$target_dir){
    list($w,$h) = getimagesize($_FILES['file']['tmp_name']);
    $height = ($width*$h)/$w;

    $ratio = max($width/$w,$height/$h);
    $h = ceil($height/$ratio);
    $x = ($w-$width/$ratio)/2;
    $w = ceil($width/$ratio);

    // get tmp file display name        
    $cleanFile = explode('.', $_FILES['file']['name']);
    $so = (sizeof($cleanFile)-1);
    unset($cleanFile[$so]);
    $cleanFile = implode($cleanFile);

    $path = $target_dir.$width.'x'.$height.'_'.$cleanFile.'.png';
    $imgString = file_get_contents($_FILES['file']['tmp_name']);
    $image = imagecreatefromstring($imgString);
    $tmp = imagecreatetruecolor($width, $height);
    imagecopyresampled($tmp, $image, 0, 0, $x, 0,$width,$height,$w,$h);
    imagepng($tmp,$path,0);    
    return $path;
    imagedestroy($image);
    imagedestroy($tmp);
  }
  //Allow certain file formats
  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" ) {
    $uploadOk = 0;
    $response['content']= "Sorry, only JPG, JPEG & PNG files are allowed.";
    log_event();
    echo json_encode($response);
    exit;
  }
  $outputFile = resize('400',$imageFileType,$target_dir);
  // check file is actually uploaded
  if (file_exists($outputFile)) {
    $outputFile = ltrim($outputFile,"\.\./");
    //update in db
    $sql = "UPDATE `file` SET `thumb`='$outputFile' WHERE `id`='$fid'";
    if(!$m->query($sql)){
      $response['content'] = 'Query error updating thumb.';
      log_event();
      echo json_encode($response);
      exit;
    }

    /* apn push trigger */

    $pushGroups = array();
    $pushLangs = array();
    //get groups
    $sql = "SELECT `group_id` FROM `file_group` WHERE `file_id`='$fid'";
    $res = $m->query($sql);
    while($e = $res->fetch_assoc()){
      $pushGroups[] = $e['group_id'];
    }

    //get langs
    $sql = "SELECT `language_id` FROM `file_language` WHERE `file_id`='$fid'";
    $res = $m->query($sql);
    while($e = $res->fetch_assoc()){
      $pushLangs[] = $e['language_id'];
    }

    for($i=0;$i<sizeof($pushGroups);$i++){
      $pushGroup = $pushGroups[$i];
      for($j=0;$j<sizeof($pushLangs);$j++){
        $pushLang = $pushLangs[$j];
        curl_post_async($baseUrl.'apnPush.php',array('groupId'=>$pushGroup, 'languageId'=>$pushLang));
      }
    }

    /* end apn push trigger */

    $response['status'] = 'OK';
    $response['content'] = $outputFile;
  } else {
    $response['content']= "Sorry, there was an error uploading your file.";
  }    
  log_event();
  echo json_encode($response);
  exit;
?>