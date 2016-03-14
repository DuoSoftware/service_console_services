<?php
use google\appengine\api\taskqueue\PushTask;

class uploader {

		private function status(){
			$arr = array('Name' => "Service Console Uploader Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function upload($namespace, $class){
			$guid = md5(uniqid(rand(), true));
			$fileName = $guid."-".$_FILES['file']['name'];
			$uploadRequest = $this->getFileUploadRequest($fileName, $namespace.$class, "BulkProcessor", $fileName, NULL);
			$status = $this->pushFileToObjectstore($fileName);
			if ($status){
				ConsoleLog("Successfully pushed file to ObjectStore!");
				$this->pushToQueue($uploadRequest);
			}else{
				ConsoleLog("Operation Aborted! Error pushing file to ObjectStore!");
			}
		}

		private function getFileUploadRequest($RefId, $RefType, $OperationCode, $fileName, $body){
			$time = new DateTime();
			$time->getTimestamp();
			$configdata = GetGlobalConfigurations();
			$request = array("RefID" => $RefId,
							 "RefType" => $RefType,
							 "OperationCode" => $OperationCode,
							 "TimeStamp" => $time,
							 "ControlParameters" => $configdata["data"]["data"],
							 "Parameters" => array("FileName" => $fileName),
							 "Body" => $body);
			return $request;
		}

		private function pushFileToObjectstore($fileName){
			$status = TRUE;
			if (strpos($_FILES['file']['name'], '.xlsx') !== false){
    			$status = CurlUploadFile(SVC_OS_URL.SVC_UPLOAD_PATH.$_FILES['file']['name'], $_FILES['file'], $fileName);
			}else{
				$status = FALSE;
			}
			return $status;
		}

		private function pushToQueue($uploadRequest){
			ConsoleLog("Starting pushing to File Queue!");
			$task = new PushTask('/queuemanager/enqueue', $uploadRequest);
			$task_name = $task->add("fileQueue");
		}

		// private function pushToQueue(){
		// 	ConsoleLog("Starting pushing to File Queue!");
		// 	$data = array("Object" => array("Id" => "-888", "Name" => "PRASAD!"), "Parameters" => array("KeyProperty" => "Id"));                                                                 
		// 	$data = array("RefID" => "", "Name" => "PRASAD!", "Parameters" => array("KeyProperty" => "Id"));
		// 	$task = new PushTask('/queuemanager/enqueue', $data);
		// 	$task_name = $task->add("fileQueue");
		// }


		//	Test Functions don't deletes

		// private function pushToQueue(){
		// 	ConsoleLog("Starting pushing to Queue!");
		// 	$data = array("Object" => array("Id" => "-888", "Name" => "PRASAD!"), "Parameters" => array("KeyProperty" => "Id"));                                                                 
		// 	$task = new PushTask('/uploader/upload2', $data);
		// 	$task_name = $task->add("fileQueue");
		// }

		// private function uploadTest1(){
		// 	$data = $_POST;
		// 	$headers = array('securityToken: asdf');
		// 	CurlPost("http://localhost:3000/aa/bb", $data, $headers);
		// }
	
		function __construct(){
			Flight::route("GET /uploader", function (){$this->status();});
			Flight::route("POST /uploader/@namespace/@class", function ($namespace, $class){
				$this->upload($namespace, $class);
            });
		}
	}
?>