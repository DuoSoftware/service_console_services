<?php
class uploader {

		private function About(){
			$arr = array('Name' => "Duo World Application Interface", 'Version' => "1.0.0-a", 'Change Log' => "Nothing so far just testing. Move along!", 'Author' => "Duo Software", 'Website' => "http://www.duoworld.com/", 'Status' => "Running");
			echo json_encode($arr);
		}

		private function status(){
			$arr = array('Name' => "Service Console Uploader Service", 'Version' => "1.0.0-a", 'Change Log' => "AppEngine Compatible uploader redirector.", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function upload(){
			//$status = $this->uploadToObjectstore();
			$status = $this->uploadWithoutCurl();
			if ($status == "true"){
				$this->addToQueue();
			}else{
				ConsoleLog("Operation Aborted!");
			}
		}

		private function uploadToObjectstore(){

			$url = SVC_OS_URL.SVC_UPLOAD_PATH.$_FILES['file']['name'];
			ConsoleLog($url);

		    $fname = $_FILES['file']['name'];

 	    	$file = new CURLFile(realpath($_FILES['file']['tmp_name']));
 			var_dump($file);
	        $post = array (
	                  'file' => $file
	                  );    

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");   
		    curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data','Application: service-console-uploader'));
		    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);   
		    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);  
		    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		    $result = curl_exec($ch);
			curl_close ($ch);
			$status = "false";

		    if ($result === FALSE) {
		       	ConsoleLog("Error sending" . $fname);	
		       	$status = "false";	        
		    }else{
		        ConsoleLog($result);
		        $status = "true";
		    }

		    return $status;
		}

		private function uploadWithoutCurl(){
			ConsoleLog("huehueheu");
			$file=file($_FILES['file']['tmp_name']);
			$url = SVC_OS_URL.SVC_UPLOAD_PATH.$_FILES['file']['name'];
			$files['file'] = $_FILES['file']; 
			ConsoleLog($url);
			$this->aaa($url, $files); 
			return "true";

		}

		private function aaa($url, $files){ 
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
		     ConsoleLog("Problem with url"); 
		   } 

		   $response = @stream_get_contents($fp); 
		   if ($response === false) { 
		      ConsoleLog("Problem reading data from url"); 
		   }else{
		   		ConsoleLog("yay");
		   }  
		} 
		

		private function addToQueue(){
			ConsoleLog("Starting Adding to Queue!");
		}
		
		function __construct(){
			Flight::route("GET /", function (){$this->About();});
			Flight::route("GET /uploader", function (){$this->status();});
		    Flight::route("POST /uploader/upload", function (){$this->upload();});
		}
	}
?>