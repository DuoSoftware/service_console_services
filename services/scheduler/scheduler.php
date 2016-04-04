<?php
use google\appengine\api\taskqueue\PushTask;
class scheduler {
		private function About(){
			$arr = array('Name' => "Service Console Scheduler Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
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
						echo json_encode("Completed Request!");
					}else{
						echo json_encode("Operation Aborted! Error pushing Request to ObjectStore!");
					}
				}else{
					echo json_encode("TimeStamp Not included in Request!");
				}
				
			}else{
				echo json_encode("Error in request JSON!");
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
			$headers = array('securityToken: asdf');
			$status = CurlPost(SVC_OS_URL."/"."completed.console.data/scheduleobjects", $data, $headers);
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
		}
	}
?>