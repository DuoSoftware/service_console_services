<?php
use google\appengine\api\taskqueue\PushTask;
class scheduler {
		private function About(){
			$arr = array('Name' => "Service Console Scheduler Service", 'Version' => "1.0.1-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function addInfo(){
			echo json_encode("GET version of this method is unavailable! Try POST method with a scheduler request body!");
		}

		private function uploadInfo(){
				echo json_encode("GET version of this method is unavailable! Try POST method with a scheduler request body!");
		}

		private function add(){
			$body = Flight::request()->getBody();
			$data = new ScheduleRequest();
			$data = json_decode($body);
			$configdata = GetGlobalConfigurations();
			$data->ControlParameters = $configdata["data"]["data"];
			$request = $this->getScheduleRequest($data);
			$status = $this->pushNewScheduleObjectToObjectstore($data, "RefId");


			
			if ($status){
					$response = new CommonResponse();
					$response->IsSuccess = TRUE;
					echo json_encode($response);
				}else{
					$response = new CommonResponse();
					$response->CustomMessage = "Operation Aborted! Error pushing Request to ObjectStore!";
					$response->IsSuccess = FALSE;
					echo json_encode($response);
				}
		}

		private function upload(){
			
			$body = Flight::request()->getBody();
			$data = new ScheduleRequest();
			$data = json_decode($body);

			if (isset($data)){
				if (isset($data->TimeStamp)){
					$configdata = GetGlobalConfigurations();
					$data->ControlParameters = $configdata["data"]["data"];
		
					$request = $this->getScheduleRequest($data);
					$status = $this->pushRecordToObjectstore($data, "RefId");
					if ($status){
						$this->pushToQueue($request);
						$response = new CommonResponse();
						$response->IsSuccess = TRUE;
						echo json_encode($response);
					}else{
						$response = new CommonResponse();
						$response->CustomMessage = "Operation Aborted! Error pushing Request to ObjectStore!";
						$response->IsSuccess = FALSE;
						echo json_encode($response);
					}
				}else{
					$response = new CommonResponse();
					$response->CustomMessage = "TimeStamp Not included in Request!";
					$response->IsSuccess = FALSE;
					echo json_encode($response);
				}
				
			}else{
				$response = new CommonResponse();
				$response->CustomMessage = "Error in request JSON!";
				$response->IsSuccess = FALSE;
				echo json_encode($response);
			}
		}

		private function getScheduleRequest($request){
			$obj = array("RefId" => $request->RefId,
							 "RefType" => $request->RefType,
							 "OperationCode" => $request->OperationCode,
							 "TimeStamp" => $request->TimeStamp,
							 "TimeStampReadable" => $request->TimeStampReadable,
							 "ControlParameters" => $request->ControlParameters,
							 "Parameters" => $request->Parameters,
							 "ScheduleParameters" => $request->ScheduleParameters);
			return $obj;
		}	

		private function pushRecordToObjectstore($record, $primarykey){
			$status = TRUE;
			$data = array("Object" => $record, "Parameters" => array("KeyProperty" => $primarykey));                                                          
			$headers = array('securityToken: ignore');
			//var_dump($data);
			$status = CurlPost(SVC_OS_URL."/"."completed.console.data/scheduleobjects", $data, $headers);
			return $status;
		}

		private function pushNewScheduleObjectToObjectstore($record, $primarykey){
			$status = TRUE;
			$data = array("Object" => $record, "Parameters" => array("KeyProperty" => $primarykey));                                                          
			$headers = array('securityToken: ignore');
			//var_dump($data);
			$status = CurlPost(SVC_OS_URL."/"."pending.console.data/scheduleobjects", $data, $headers);
			return $status;
		}

		private function pushToQueue($request){
			ConsoleLog("Starting pushing to File Queue!");
			$task = new PushTask('/queuemanager/enqueue', $request);
			$task_name = $task->add("scheduleQueue");
		}

		function __construct(){
			Flight::route("GET /scheduler", function (){$this->About();});
			Flight::route("POST /scheduler/schedule", function (){$this->upload();});
			Flight::route("POST /scheduler/add", function (){$this->add();});
			Flight::route("GET /scheduler/schedule", function (){$this->uploadInfo();});
			Flight::route("GET /scheduler/add", function (){$this->addInfo();});
		}
	}
?>