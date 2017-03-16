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

	// Make sure file is not cached (as it happens for example on iOS devices)
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// 5 minutes execution time
	@set_time_limit(5 * 60);

	// Uncomment this one to fake upload time
	// usleep(5000);

	// Settings
	$cleanupTargetDir = false; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds
	$targetDir = $baseFileDir;
	$iconDir = '../icons/';

	// Create target dir
	if (!file_exists($targetDir)) {
		@mkdir($targetDir);
	}

	// Get a file name
	if (isset($_REQUEST["name"])) {	
		$fileName = $_REQUEST["name"];
	} elseif (!empty($_FILES)) {
		$fileName = $_FILES["file"]["name"];
	} else {
		$fileName = uniqid("file_");
	}
	if(!isset($_FILES['file'])){
		$response['content'] = 'No $_FILES sent.';
		log_and_respond($response);
		exit;
	}
	$name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
	$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

	$i = 0;
	while (file_exists($targetDir . $name .'.'. $ext)){
		$name = $name.'_'.$i;
		$i++;
	}

	$fileName = $name .'.'. $ext;

	$filePath = $targetDir . $fileName;

	// Chunking might be enabled
	$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

	// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);	
	$fileName = $fileName;

	// Make sure the fileName is unique but only if chunking is disabled
	if ($chunks < 2 && file_exists($targetDir . $fileName)) {
		$ext = strrpos($fileName, '.');
		$fileName_a = substr($fileName, 0, $ext);
		$fileName_b = substr($fileName, $ext);
		$count = 1;
		while (file_exists($targetDir . $fileName_a . '_' . $count . $fileName_b)){
			$count++;
		}
		$fileName = $fileName_a . '_' . $count . $fileName_b;
	}

	$filePath = $targetDir . $fileName;

	// Open temp file
	if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {		
		$response['content'] = 'Failed to open output stream.';
		log_and_respond($response);
		exit;
	}

	if (!empty($_FILES)) {
		if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {			
			$response['content'] = 'Failed to move uploaded file.';
			log_and_respond($response);
			exit;
		}

		// Read binary input stream and append it to temp file
		if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {			
			$response['content'] = 'Failed to open input stream.';
			log_and_respond($response);
			exit;
		}
	} else {	
		if (!$in = @fopen("php://input", "rb")) {			
			$response['content'] = 'Failed to open input stream.';
			log_and_respond($response);
			exit;
		}
	}

	while ($buff = fread($in, 4096)) {
		fwrite($out, $buff);
	}

	@fclose($out);
	@fclose($in);

	// Check if file has been uploaded
	if (!$chunks || $chunk == $chunks - 1) {
		// Strip the temp .part suffix off 
		if (file_exists($targetDir . $fileName)){
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
			$count = 1;
			while (file_exists($targetDir . $fileName_a . '_' . $count . $fileName_b)){
				$count++;
			}
			$fileName = $fileName_a.'_'.$count.$fileName_b;			
		}
		$newFilePath = $targetDir.$fileName;
		rename("{$filePath}.part", $newFilePath);

		//get extension
		$ext = explode('.', $fileName);
		$ext = $ext[sizeof($ext)-1];
		
		
		//generate thumb
		if(strtolower($ext)=='mp4'||strtolower($ext)=='m4v'){
			$video = escapeshellcmd($newFilePath);
			$cmd = "ffmpeg -i $video 2>&1";
			$second = 5;			
			$image  = '../icons/icon_'.$fileName.'.png';
			$icon_name = 'icon_'.$fileName.'.png';
			$cmd = "ffmpeg -i $video -s 260x260 -deinterlace -an -ss $second -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $image 2>&1";
			$do = `$cmd`;
			$image = ltrim($image,"\.\./");
		}else if(strtolower($ext)=='pdf'){
			$image = '../icons/icon_'.$fileName.'.png';

			$icon_name = 'icon_'.$fileName.'.png';
			exec('convert "'.$newFilePath.'[0]" -alpha off -colorspace RGB -geometry 260 -quality 200 "'.$image.'"');			
			$image = ltrim($image,"\.\./");
		}else{ // need to pull thumb from any other types -- like images.
			$makeIcon = resize($fileName);
			$icon_name = $makeIcon;
		}
		
		// get tmp file display name		
		$cleanFile = explode('.', $fileName);
		$so = (sizeof($cleanFile)-1);
		unset($cleanFile[$so]);
		$cleanFile = implode($cleanFile);

		//add to main videos table
		$now = date("Y-m-d H:i:s");
		$fileDir = $targetDir.$fileName;
		$fileDir = ltrim($fileDir,"\.\./");		

		$sql = "INSERT INTO `content` (`location`,`created_at`,`type`,`name`,`display`,`client_id`) VALUES ('$fileName',CURRENT_TIMESTAMP,'file','$cleanFile','$cleanFile','$client')";
		if(!$m->query($sql)){
			$response['content'] = 'Query error adding file content.';
			log_and_respond($response);
			exit;
		}

		//get file id
		$fid = $m->insert_id;

		$sql = "INSERT INTO `icon` (`file_id`,`location`,`created_at`,`client_id`) VALUES ('$fid','$icon_name',CURRENT_TIMESTAMP,'$client')";		
    if (!$m->query($sql)){
      $response['content'] = "Query error adding new icon. addContent.";
      log_and_respond($response);
    }

    $icon_id = $m->insert_id;

    $sql = "UPDATE `content` SET `icon_id` = '$icon_id' WHERE `id` = '$fid'";
    if (!$m->query($sql)){
    	$response['content'] = 'Query error updating content icon_id.';
    	log_and_respond($response);
    }
		
		$response['status'] = 'OK';
		$response['content'] = array(
				"id" => $fid,				
				"location" =>$fileName,
				"display"=>$cleanFile,
				'icon_id'=>$icon_id,
				'icon_location'=>$baseURL.'icons/'.$icon_name,
			);
		log_and_respond($response);
		exit;		
	}

function resize($file){
	global $baseFileDir;
	$width = 260;
  list($w,$h) = getimagesize($baseFileDir.$file);
  $height = ($width*$h)/$w;

  $ratio = max($width/$w,$height/$h);
  $h = ceil($height/$ratio);
  $x = ($w-$width/$ratio)/2;
  $w = ceil($width/$ratio);

  // get tmp file display name        
  $cleanFile = explode('.', $file);
  $so = (sizeof($cleanFile)-1);
  unset($cleanFile[$so]);
  $cleanFile = implode($cleanFile);
  $fname = 'icon_'.$cleanFile.'.png';
  $path = '../icons/icon_'.$cleanFile.'.png';
  $imgString = file_get_contents($baseFileDir.$file);
  $imagez = imagecreatefromstring($imgString);
  $tmp = imagecreatetruecolor(260, 260);
  imagecopyresampled($tmp, $imagez, 0, 0, 0, 0,260,260,$w,$h);
  imagepng($tmp,$path,0);
  
  return $fname;
  imagedestroy($imagez);
  imagedestroy($tmp);
}