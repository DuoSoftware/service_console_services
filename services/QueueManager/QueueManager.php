<?php

class huehue{
	private $one;
	private $two;
}

class QueueManager {
		private function About(){
			$arr = array('Name' => "Service Console Queue Manager Service", 'Version' => "1.0.2-a", 'Change Log' => "Added SMS!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function enqueue(){
			
			$data = $_POST;
			$requestObject = $this->getRequestObject($data);

			WriteLog(("QueueAdd".$requestObject->RefId), "Starting adding a new Queue Instance!");

			WriteLog(("QueueAdd".$requestObject->RefId), $requestObject);

			WriteLog("QueueAdd", $requestObject->RefId);

			$response;

			switch ($requestObject->OperationCode) {
			    case "SmoothFlow":
			    	WriteLog(("QueueAdd".$requestObject->RefId), "Sending to SmoothFlow!");
			        $response = $this->PostToSmoothFlow($requestObject);
			        break;
			    case "Email Campaign":
			    	WriteLog(("QueueAdd".$requestObject->RefId), "Sending to CEB Mail Service!");
			        $response = $this->SendEmail($requestObject);
			        break;
			    case "SMS Campaign":
			    	WriteLog(("QueueAdd".$requestObject->RefId), "Sending to CEB SMS Service!");
			        $response = $this->SendSMS($requestObject);
			     	break;
			    default:
			        $response = $this->PostToSmoothFlow($requestObject);
			}

			return $response;

		}

		private function PostToSmoothFlow($requestObject){
			$namespaceAndClass = explode(".", $requestObject->RefType);
			$namespace = $namespaceAndClass[0];
			$class = $namespaceAndClass[1];

			if (!isset($namespace)){
				$namespace = "ignorenamespace";
			}

			if (!isset($class)){
				$class = "ignoreclass";
			}
			$headers = array('securityToken: ignore');
			$result = CurlPost(SVC_WORKER_URL.$namespace."/".$class, $requestObject, $headers);
			return $result;
		}

		private function SendEmail($object){
			WriteLog(("QueueAdd".$object->RefId), "Starting Send Mail Function for CEB Posting....");

			$GroupNamespace = $object->Parameters["JSONData"]["Group"]["Namespace"];
			$GroupID = $object->Parameters["JSONData"]["Group"]["GroupID"];
  		 	$subject = $object->Parameters["JSONData"]["Subject"];
  		 	//$from = $object->Parameters["JSONData"]["GatewaySettings"]["Email"]["From"];
  		 	$TemplateID = $object->Parameters["JSONData"]["Template"]["TemplateID"];
  		 	$TemplateNamespace = $object->Parameters["JSONData"]["Template"]["Namespace"];

  		 	$emailNamespace = $object->Parameters["JSONData"]["GatewaySettings"]["Namespace"];
  		 	$emailClass = $object->Parameters["JSONData"]["GatewaySettings"]["Class"];
  		 	$emailID = $object->Parameters["JSONData"]["GatewaySettings"]["SettingsID"];

  		 	$clientObjEmail = ObjectStoreClient::WithNamespace($emailNamespace,$emailClass,"ignore");
  		 	$resultEmailSettingsArray = $clientObjEmail->get()->byKey($emailID);

  	 		$from = $resultEmailSettingsArray->FromAddress;

  		 	$from = str_replace("u003c","<",$from);
  		 	$from = str_replace("u003e",">",$from);
  		 	$from = str_replace("\u003c","<",$from);
  		 	$from = str_replace("\u003e",">",$from);

  		 	//WriteLog(("QueueAdd".$object->RefId), "From Address : ");
  		 	//WriteLog(("QueueAdd".$object->RefId), string($from));

			$client = ObjectStoreClient::WithNamespace($GroupNamespace,$GroupID,"ignore");
  		 	$resultArray = $client->get()->all();

  		 	for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 		if (!empty($resultArray[$x]["Email"]) && $resultArray[$x]["Email"] != "") {
  		 			WriteLog(("QueueAdd".$object->RefId), "Sending an Email... ");
  		 			//WriteLog(("QueueAdd".$object->RefId), string($resultArray[$x]["Email"]));
	    			$requestBody = $this->createCEBEmailRequest($resultArray[$x]["Email"], $subject, $from, $TemplateNamespace, $TemplateID);
	    			$headers = array('securityToken: ignore');
	    			//$status = CurlPost("http://localhost:6000/aa/bb", $requestBody, $headers);
	    			$status = CurlPost(SVC_CEB_URL."command/notification", $requestBody, $headers);
	    			//WriteLog(("QueueAdd".$object->RefId), "Sending Result : ");
	    			//WriteLog(("QueueAdd".$object->RefId), string($status));
    			}
			}
		}

		private function SendSMS($object){
			WriteLog(("QueueAdd".$object->RefId), "Starting Send SMS Function for CEB Posting....");

			$GroupNamespace = $object->Parameters["JSONData"]["Group"]["Namespace"];
			$GroupID = $object->Parameters["JSONData"]["Group"]["GroupID"];
  		 	//$subject = $object->Parameters["JSONData"]["Subject"];
  		 	//$from = $object->Parameters["JSONData"]["GatewaySettings"]["Email"]["From"];
  		 	$TemplateID = $object->Parameters["JSONData"]["Template"]["TemplateID"];
  		 	$TemplateNamespace = $object->Parameters["JSONData"]["Template"]["Namespace"];

  		 	//$emailNamespace = $object->Parameters["JSONData"]["GatewaySettings"]["Namespace"];
  		 	//$emailClass = $object->Parameters["JSONData"]["GatewaySettings"]["Class"];
  		 	//$emailID = $object->Parameters["JSONData"]["GatewaySettings"]["SettingsID"];

  		 	//$clientObjEmail = ObjectStoreClient::WithNamespace($emailNamespace,$emailClass,"ignore");
  		 	//$resultEmailSettingsArray = $clientObjEmail->get()->byKey($emailID);

  	 		//$from = $resultEmailSettingsArray->FromAddress;

  		 	//$from = str_replace("u003c","<",$from);
  		 	//$from = str_replace("u003e",">",$from);
  		 	//$from = str_replace("\u003c","<",$from);
  		 	//$from = str_replace("\u003e",">",$from);

  		 	//WriteLog(("QueueAdd".$object->RefId), "From Address : ");
  		 	//WriteLog(("QueueAdd".$object->RefId), string($from));

			$client = ObjectStoreClient::WithNamespace($GroupNamespace,$GroupID,"ignore");
  		 	$resultArray = $client->get()->all();

  		 	for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 		if (!empty($resultArray[$x]["PhoneNumber"]) && $resultArray[$x]["PhoneNumber"] != "") {
  		 			WriteLog(("QueueAdd".$object->RefId), "Sending an SMS... ");
  		 			//WriteLog(("QueueAdd".$object->RefId), string($resultArray[$x]["Email"]));
	    			$requestBody = $this->createCEBSmsRequest($resultArray[$x]["PhoneNumber"], $TemplateNamespace, $TemplateID);
	    			$headers = array('securityToken: ignore');
	    			//$status = CurlPost("http://localhost:6000/aa/bb", $requestBody, $headers);
	    			$status = CurlPost(SVC_CEB_URL."command/notification", $requestBody, $headers);
	    			//WriteLog(("QueueAdd".$object->RefId), "Sending Result : ");
	    			//WriteLog(("QueueAdd".$object->RefId), string($status));
    			}
			}
		}

		private function getQueueAddList(){
			$data = ReadLog("QueueAdd");
			echo json_encode($data);
		}

		private function getQueueAddLog($refid){
			$data = ReadLog("QueueAdd".$refid);
			echo json_encode($data);
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

		private function createCEBSmsRequest($number, $namespace, $TemplateID){
			$request = array("type" => "sms",
							 "number" => $number,
							 "Namespace" => $namespace,
							 "TemplateID" => $TemplateID);
			return $request;
		}


		private function getRequestObject($arr){
			$object = new ScheduleRequest();
			$object->RefId = $arr["RefId"];
			$object->RefType = $arr["RefType"];
			$object->OperationCode = $arr["OperationCode"];
			$object->TimeStamp = $arr["TimeStamp"];
			$object->TimeStampReadable = $arr["TimeStampReadable"];
			$object->ControlParameters = $arr["ControlParameters"];
			$object->Parameters = $arr["Parameters"];
			$object->ScheduleParameters = $arr["ScheduleParameters"];
			return $object;
		}

		function __construct(){
			Flight::route("GET /queuemanager", function (){$this->About();});
			Flight::route("POST /queuemanager/enqueue", function (){$this->enqueue();});
			Flight::route("GET /queuemanager/enqueue/loglist", function (){$this->getQueueAddList();});
			Flight::route("GET /queuemanager/enqueue/log/@refid", function ($refid){
				$this->getQueueAddLog($refid);
            });
		}
	}
?>