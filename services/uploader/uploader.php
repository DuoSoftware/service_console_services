<?php
use google\appengine\api\taskqueue\PushTask;

class uploader {

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

		private function uploadTest(){
			$this->pushToQueue();
		}



		private function postObjectstore(){ 
			$data = $_POST;                                                                 
			$data_string = json_encode($data);                                                                                   
			                                                                                                                     
			$ch = curl_init("http://localhost:3000/aa/bb");                                                                      
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			    'securityToken: securityToken',
			    'Content-Type: application/json',                                                                                
			    'Content-Length: ' . strlen($data_string))                                                                       
			);                                                                                                                   
			                                                                                                                     
			$result = curl_exec($ch);

		}

		private function uploadTest1(){
			$this->postObjectstore();
		}

		private function pushToQueue(){
			ConsoleLog("Starting pushing to Queue!");
			$data = array("Object" => array("Id" => "-888", "Name" => "PRASAD!"), "Parameters" => array("KeyProperty" => "Id"));                                                                 
			$task = new PushTask('/uploader/upload2', $data);
			$task_name = $task->add("jay");
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
			Flight::route("GET /uploader", function (){$this->status();});
			Flight::route("GET /uploader/upload1", function (){$this->uploadTest();});
			Flight::route("POST /uploader/upload2", function (){$this->uploadTest1();});
		    Flight::route("POST /uploader/upload", function (){$this->upload();});
		}
	}
?>