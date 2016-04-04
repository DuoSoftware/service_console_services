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
					//var_dump($data);
					// var_dump("--------------");
					// var_dump($data->RefType);
					// var_dump($data->Parameters->JSONData->GatewaySettings->user);
					// var_dump($data->Parameters->JSONData->subject);
					// var_dump($data->Parameters->JSONData->->Template->TemplateID);
					// var_dump("--------------");

					$request = $this->getScheduleRequest($data);
					$status = $this->pushRecordToObjectstore($data, "RefId");
					if ($status){
						//$this->SendEmail($data);
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

		private function SendEmail($object){
			$GroupNamespace = $object->Parameters->JSONData->Group->Namespace;
			$GroupID = $object->Parameters->JSONData->Group->GroupID;
  		 	$subject = $object->Parameters->JSONData->Subject;
  		 	$from = $object->Parameters->JSONData->GatewaySettings->Email->From;
  		 	$TemplateID = $object->Parameters->JSONData->Template->TemplateID;
  		 	$TemplateNamespace = $object->Parameters->JSONData->Template->Namespace;

  		 	var_dump("-----------");
  		 	var_dump($GroupNamespace);
  		 	var_dump($GroupID);
  		 	var_dump($subject);
  		 	var_dump($from);
			var_dump($TemplateID);
  		 	var_dump($TemplateNamespace);
  		 	var_dump("-----------");

			$client = ObjectStoreClient::WithNamespace($GroupNamespace,$GroupID,"ignore");
  		 	$resultArray = $client->get()->all();

  		 	
  		 	for ($x = 0; $x < sizeof($resultArray); $x++) {
    			$requestBody = $this->createCEBEmailRequest($resultArray[$x]["Email"], $subject, $from, $TemplateNamespace, $TemplateID);
    			//var_dump($requestBody);
    			$headers = array('securityToken: asdf');
    			$status = CurlPost(SVC_CEB_URL."command/notification", $requestBody, $headers);
    			var_dump($status);
			}
		}

		private function createCEBEmailRequest($email, $subject, $from, $namespace, $TemplateID){
			$request = array("type" => "email",
							 "to" => $email,
							 "subject" => $subject,
							 "from" => $from,
							 "Namespace" => $namespace,
							 "TemplateID" => $TemplateID);
			return $request;
		}

		private function getScheduleRequest($request){
			$request = array("RefId" => $request->RefId,
							 "RefType" => $request->RefType,
							 "OperationCode" => $request->OperationCode,
							 "TimeStamp" => $request->TimeStamp,
							 "TimeStampReadable" => $request->TimeStampReadable,
							 "ControlParameters" => $request->ControlParameters,
							 "Parameters" => $request->Parameters,
							 "ScheduleParameters" => $request->ScheduleParameters);
			return $request;
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