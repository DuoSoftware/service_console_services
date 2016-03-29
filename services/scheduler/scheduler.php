<?php
use google\appengine\api\taskqueue\PushTask;
class scheduler {
		private function About(){
			$arr = array('Name' => "Service Console Scheduler Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function upload($namespace, $class){
			
			$body = Flight::request()->getBody();
			$data = json_decode($body);

			if (isset($data)){
				$RefID = $data->RefID;
				$OperationCode = $data->OperationCode;
				$Parameters = $data->Parameters;
				$ScheduleParameters = $data->ScheduleParameters;

				if (isset($data->Body)){
					$Body = $data->Body;
				}else{
					$Body = "Empty Body Field.. Use this feild to store related informations.";
				}

				if (isset($data->TimeStamp)){
					$TimeStamp = $data->TimeStamp;

					$request = $this->getScheduleRequest($RefID, $namespace.".".$class, $OperationCode, $Parameters, $ScheduleParameters, $TimeStamp, $Body);

					$status = $this->pushRecordToObjectstore($request, "RefID");
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

		private function getScheduleRequest($RefId, $RefType, $OperationCode, $parameters, $ScheduleParameters, $TimeStamp, $body){
			//$date = new DateTime();
			//$time = $date->format('Y-m-d H:i:s');

			// if ($TimeStamp != "nowtime"){
			// 	$to_time = strtotime($TimeStamp);
			// 	$from_time = strtotime($time);
			// 	$time =  round(($to_time - $from_time) ,2);
			// }

			$configdata = GetGlobalConfigurations();
			$request = array("RefID" => $RefId,
							 "RefType" => $RefType,
							 "OperationCode" => $OperationCode,
							 "TimeStamp" => $TimeStamp,
							 "ControlParameters" => $configdata["data"]["data"],
							 "Parameters" => $parameters,
							 "ScheduleParameters" => $ScheduleParameters,
							 "Body" => $body);
			return $request;
		}	

		private function pushRecordToObjectstore($record, $primarykey){
			$status = TRUE;
			$data = array("Object" => $record, "Parameters" => array("KeyProperty" => $primarykey));                                                                 
			$headers = array('securityToken: asdf');
			$status = CurlPost(SVC_OS_URL."service.console.data/scheduleobjects", $data, $headers);
			return $status;
		}

		private function pushToQueue($request){
			ConsoleLog("Starting pushing to File Queue!");
			$task = new PushTask('/queuemanager/enqueue', $request);
			$task_name = $task->add("scheduleQueue");
		}

		function __construct(){
			Flight::route("GET /scheduler", function (){$this->About();});
			Flight::route("POST /scheduler/@namespace/@class", function ($namespace, $class){
				$this->upload($namespace, $class);
            });
		}
	}
?>