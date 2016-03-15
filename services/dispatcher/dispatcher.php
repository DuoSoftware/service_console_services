<?php
use google\appengine\api\taskqueue\PushTask;

class test{
	public $aa;
	public $bb;
}

class dispatcher {
		private function About(){
			$arr = array('Name' => "Service Console Dispatcher Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function dispatchToQueueMgr($namespace, $class){
			$body = Flight::request()->getBody();
			$data = json_decode($body);

			if (isset($data)){
				$RefID = $data->RefID;
				$OperationCode = $data->OperationCode;
				$Parameters = $data->Parameters;
				$Body = $data->Body;

				$request = $this->getRequest($RefID, $namespace.".".$class, $OperationCode, $Parameters, $Body);
				$this->pushToQueue($request);

			}else{
				echo json_encode("Error in request JSON!");
			}
		}

		private function pushToQueue($request){
			ConsoleLog("Starting pushing requests to Queue!");
			$task = new PushTask('/queuemanager/enqueue', $request);
			$task_name = $task->add("requestQueue");
		}

		private function getRequest($RefId, $RefType, $OperationCode, $parameters, $body){
			$date = new DateTime();
			$time = $date->format('Y-m-d H:i:s');
			$configdata = GetGlobalConfigurations();
			$request = array("RefID" => $RefId,
							 "RefType" => $RefType,
							 "OperationCode" => $OperationCode,
							 "TimeStamp" => $time,
							 "ControlParameters" => $configdata["data"]["data"],
							 "Parameters" => $parameters,
							 "Body" => $body);
			return $request;
		}

		function __construct(){
			Flight::route("GET /dispatcher", function (){$this->About();});
			Flight::route("POST /dispatcher/@namespace/@class", function ($namespace, $class){
				$this->dispatchToQueueMgr($namespace, $class);
			});
		}
	}
?>