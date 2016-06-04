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

			WriteLog(("ScheduleAdd".$data->RefId), "Starting adding a new scheduler");

			WriteLog(("ScheduleAdd".$data->RefId), $body);

			WriteLog(("ScheduleAdd".$data->RefId), $data);

			WriteLog("ScheduleAddRequests", $data->RefId);
			
			if ($status){
					$response = new CommonResponse();
					$response->IsSuccess = TRUE;
					WriteLog(("ScheduleAdd".$data->RefId), "Successfully added schedule to ObjectStore!");
					echo json_encode($response);
				}else{
					$response = new CommonResponse();
					$response->CustomMessage = "Operation Aborted! Error pushing Request to ObjectStore!";
					$response->IsSuccess = FALSE;
					WriteLog(("ScheduleAdd".$data->RefId), "Operation Aborted! Error pushing Request to ObjectStore!");
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

					WriteLog("Schedule", $data->RefId);

					WriteLog(("Schedule".$data->RefId), "Staring new Scheduler Task!");
					WriteLog(("Schedule".$data->RefId), $data);

					if ($status){
						$this->pushToQueue($request);
						$response = new CommonResponse();
						$response->IsSuccess = TRUE;
						WriteLog(("Schedule".$data->RefId), "Successfully added to Task Queue!");
						echo json_encode($response);
					}else{
						$response = new CommonResponse();
						$response->CustomMessage = "Operation Aborted! Error pushing Request to ObjectStore!";
						WriteLog(("Schedule".$data->RefId), "Error adding to Task Queue because ObjectStore returned an Error!");
						$response->IsSuccess = FALSE;
						echo json_encode($response);
					}
				}else{
					$response = new CommonResponse();
					$response->CustomMessage = "TimeStamp Not included in Request!";
					WriteLog(("Schedule".$data->RefId), "TimeStamp Not included in Request!");
					$response->IsSuccess = FALSE;
					echo json_encode($response);
				}
				
			}else{
				$response = new CommonResponse();
				$response->CustomMessage = "Error in request JSON!";
				WriteLog(("Schedule".$data->RefId), "Error in request JSON!");
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

		private function getAddLogList(){
			$data = ReadLog("ScheduleAddRequests");
			echo json_encode($data);
		}

		private function getAddLog($refid){
			$data = ReadLog("ScheduleAdd".$refid);
			echo json_encode($data);
		}

		private function getScheduleLogList(){
			$data = ReadLog("Schedule");
			echo json_encode($data);
		}

		private function getScheduleLog($refid){
			$data = ReadLog("Schedule".$refid);
			echo json_encode($data);
		}

		function __construct(){
			Flight::route("GET /scheduler", function (){$this->About();});
			Flight::route("POST /scheduler/schedule", function (){$this->upload();});
			Flight::route("POST /scheduler/add", function (){$this->add();});
			Flight::route("GET /scheduler/schedule", function (){$this->uploadInfo();});
			Flight::route("GET /scheduler/add", function (){$this->addInfo();});
			Flight::route("GET /scheduler/add/loglist", function (){$this->getAddLogList();});
			Flight::route("GET /scheduler/add/log/@refid", function ($refid){
				$this->getAddLog($refid);
            });
            Flight::route("GET /scheduler/schedule/loglist", function (){$this->getScheduleLogList();});
			Flight::route("GET /scheduler/schedule/log/@refid", function ($refid){
				$this->getScheduleLog($refid);
            });
		}
	}
?>