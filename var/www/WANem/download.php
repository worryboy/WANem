<?php
	$path = "/tmp/"; 
	$download_file="netemstate.txt";
	$fullPath = $path.$download_file;
	if (file_exists($fullPath)) {
		$fd = fopen ($fullPath, "r") or die($php_errormsg);
		$fsize = filesize($fullPath);
		$path_parts = pathinfo($fullPath);
		$ext = strtolower($path_parts["extension"]); 
		
		switch ($ext) {
			case "txt":
				//add here more headers for diff. extensions
				header("Content-type: application/txt"); 
				//use 'attachement' to force a download
				header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); 
        			break;
        	
			default;
        			header("Content-type: application/octet-stream");
        			header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");		
    		}
		
		header("Content-length: $fsize");
		//use this to open files directly
		header("Cache-control: private"); 
		while (!feof($fd)) {

			$buffer = fread($fd, 2048);
			echo $buffer;
		}
		fclose ($fd);
		echo "File saved to client machine";
		//exit;
	}
	else {
		//echo '<script language="javascript">alert("File does not exist")</script>';
		echo "File does not exist";
		//exit;
	}
	// For next file_exist() call, clearstatcache.
	clearstatcache();
?>
