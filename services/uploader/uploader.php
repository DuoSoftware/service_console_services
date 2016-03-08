<?php
use google\appengine\api\taskqueue\PushTask;

class uploader {

		private function status(){
			$arr = array('Name' => "Service Console Uploader Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
			$configs = GetGlobalConfigurations();
			var_dump($configs);
		}

		private function upload(){
			$status = $this->pushFileToObjectstore();
			if ($status){
				ConsoleLog("Successfully pushed file to ObjectStore!");
				$this->pushToQueue();
			}else{
				ConsoleLog("Operation Aborted! Error pushing file to ObjectStore!");
			}
		}

		private function pushFileToObjectstore(){
			$headers = array();
			$status = TRUE;
			if (strpos($_FILES['file']['name'], '.xlsx') !== false){
    			$status = CurlUploadFile(SVC_OS_URL.SVC_UPLOAD_PATH.$_FILES['file']['name'], $_FILES, $headers);
			}else{
				$status = FALSE;
			}
			return $status;
		}

		private function pushToQueue(){
			ConsoleLog("Starting pushing to Queue!");
			$data = array("Object" => array("Id" => "-888", "Name" => "PRASAD!"), "Parameters" => array("KeyProperty" => "Id"));                                                                 
			$task = new PushTask('/uploader/upload2', $data);
			$task_name = $task->add("fileQueue");
		}

		private function uploadTest1(){
			$data = $_POST;
			$headers = array('securityToken: asdf');
			CurlPost("http://localhost:3000/aa/bb", $data, $headers);
		}
	
		function __construct(){
			Flight::route("GET /uploader", function (){$this->status();});
			Flight::route("POST /uploader/upload2", function (){$this->uploadTest1();});
		    Flight::route("POST /uploader/upload", function (){$this->upload();});
		}
	}
?>