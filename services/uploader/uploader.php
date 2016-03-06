<?php
class uploader {

		private function About(){
			//This is a temporary method. This should be removed and an UI page should come to front :)
			$arr = array('Name' => "Duo World Application Interface", 'Version' => "1.0.0-a", 'Change Log' => "Nothing so far just testing. Move along!", 'Author' => "Duo Software", 'Website' => "http://www.duoworld.com/", 'Status' => "Running");
			echo json_encode($arr);
		}

		private function status(){
			$arr = array('Name' => "Service Console Uploader Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function upload(){
			$status = $this->uploadToObjectstore();
			if ($status){
				ConsoleLog("Successfully pushed file to ObjectStore!");
				$this->pushToQueue();
			}else{
				ConsoleLog("Operation Aborted! Error pushing file to ObjectStore!");
			}
		}

		private function pushToQueue(){
			ConsoleLog("Starting pushing to Queue!");
		}

		private function uploadToObjectstore(){
			ConsoleLog("Starting pushing file to ObjectStore at : ".SVC_OS_URL.SVC_UPLOAD_PATH);

			$url = SVC_OS_URL.SVC_UPLOAD_PATH.$_FILES['file']['name'];
		
			$files['file'] = $_FILES['file'];

			$data = ""; 
		    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10); 

		    $data .= "--$boundary\n"; 

		    //Collect Filedata 
		    foreach($files as $key => $file) 
		    { 
		        $fileContents = file_get_contents($file['tmp_name']); 

		        $data .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$file['name']}\"\n"; 
		        $data .= "Content-Type: multipart/form-data\n";
		        $data .= "Content-Transfer-Encoding: binary\n\n"; 
		        $data .= $fileContents."\n"; 
		        $data .= "--$boundary--\n"; 
		    } 

		    $params = array('http' => array( 
		           'method' => 'POST', 
		           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary, 
		           'content' => $data 
		        )); 

		   $ctx = stream_context_create($params); 
		   $fp = fopen($url, 'rb', false, $ctx); 

		   if (!$fp) { 
		     ConsoleLog("Invalid ObjectStore URL. Check AppEngine Configurations!"); 
		   } 

		   $status = TRUE;
		   $response = @stream_get_contents($fp); 

		   if ($response === false) { 
		   	  $status = FALSE;
		      ConsoleLog("Problem reading data from url"); 
		   }
		   return $status;

		} 

		
		
		function __construct(){
			Flight::route("GET /", function (){$this->About();});
			Flight::route("GET /uploader", function (){$this->status();});
		    Flight::route("POST /uploader/upload", function (){$this->upload();});
		}
	}
?>