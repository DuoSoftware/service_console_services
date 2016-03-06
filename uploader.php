<?php
class uploader {
		private function status(){
			$arr = array('Name' => "Service Console Uploader Service", 'Version' => "1.0.0-a", 'Change Log' => "Nothing So Far! Testng!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/ServiceConsole/");
			echo json_encode($arr);
		}

		private function upload(){
			ConsoleLog("uploadeeeeeeee");
			$status = $this->uploadToObjectstore();
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
		    ConsoleLog("0.0"); 
	    	$file = new CURLFile(realpath($_FILES['file']['tmp_name']));
ConsoleLog("0.1");
	        $post = array (
	                  'file' => $file
	                  );    
ConsoleLog("0.5");
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
		    ConsoleLog("1");
		    $result = curl_exec ($ch);
			curl_close ($ch);
ConsoleLog("2");
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

		private function addToQueue(){
			ConsoleLog("Starting Adding to Queue!");
		}
		
		function __construct(){
			ConsoleLog("executing constructor!");
			Flight::route("GET /uploader", function (){$this->status();});
		    Flight::route("POST /uploader/upload", function (){$this->upload();});
		}
	}


?>