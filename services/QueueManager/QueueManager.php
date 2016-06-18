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

		private function test(){
			$emailStack = array();
			
			$email1 = $this->createCEBEmailRequest("test1@gmail.com", "subject1", "from1", "namespace1", "templateid1");
  		 	$email2 = $this->createCEBEmailRequest("test2@gmail.com", "subject2", "from1", "namespace1", "templateid1");

  		 	array_push($emailStack, $email1, $email2);
  		 	$pushObject = $this->createCEBBulkEmailRequest($emailStack, "localhost620", "MandrillApp");
  		 	$headers = array('securityToken: ignore');
			$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);
			
			$data = new CEBNotifierResponse();
			$data = json_decode($status);
			var_dump($data->data->failList);
			var_dump(sizeof($data->data->failList));
			var_dump(sizeof($data->data->successList));

			$successCount = sizeof($data->data->successList);
			$failCount = sizeof($data->data->failList);
			$failList = $data->data->failList;

			$report = $this->CreateCEBResponseToObjectstoreJson("one", $successCount, $failCount, $failList, "EMAIL");
			var_dump($report);
			$objResult = $this->PushReportToObjectstore($report, "com.prasad.com", "CampaignReports", "RefId");

			var_dump($objResult);
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
			        $response = $this->SendEmailBulk($requestObject);
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

		private function SendEmailBulk($object){
			WriteLog(("QueueAdd".$object->RefId), "Starting Send Mail Function for CEB Posting....");
			
			$MainNamespace = $object->Parameters["Namespace"];

			$GroupNamespace = $object->Parameters["JSONData"]["Group"]["Namespace"];
			$GroupID = $object->Parameters["JSONData"]["Group"]["GroupID"];
  		 	$subject = $object->Parameters["JSONData"]["Subject"];
  		 	//$from = $object->Parameters["JSONData"]["GatewaySettings"]["Email"]["From"];
  		 	$TemplateID = $object->Parameters["JSONData"]["Template"]["TemplateID"];
  		 	$TemplateNamespace = $object->Parameters["JSONData"]["Template"]["Namespace"];

  		 	$emailNamespace = $object->Parameters["JSONData"]["GatewaySettings"]["Namespace"];
  		 	$emailClass = $object->Parameters["JSONData"]["GatewaySettings"]["Class"];
  		 	$from = $object->Parameters["JSONData"]["GatewaySettings"]["From"];

  		 	$from = str_replace("u003c","<",$from);
  		 	$from = str_replace("u003e",">",$from);
  		 	$from = str_replace("\u003c","<",$from);
  		 	$from = str_replace("\u003e",">",$from);

  		 	//WriteLog(("QueueAdd".$object->RefId), "From Address : ");
  		 	//WriteLog(("QueueAdd".$object->RefId), string($from));

			$client = ObjectStoreClient::WithNamespace($GroupNamespace,$GroupID,"ignore");
  		 	$resultArray = $client->get()->all();

  		 	$count = 1;
  		 	$emailStack = array();

  		 	if (sizeof($resultArray) <=100) {
  		 		for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 			if (!empty($resultArray[$x]["Email"]) && $resultArray[$x]["Email"] != "") {
  		 				$requestBody = $this->createCEBEmailRequest($resultArray[$x]["Email"], $subject, $from, $TemplateNamespace, $TemplateID);
  		 				array_push($emailStack, $requestBody);
    				}
				}

				$pushObject = $this->createCEBBulkEmailRequest($emailStack, $emailNamespace, $emailClass);
				
				WriteLog(("QueueAdd".$object->RefId), "Sending an individual Email Stack... ");
		    	$headers = array('securityToken: ignore');
		    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);
		    	

				$cebResponse = new CEBNotifierResponse();
				$cebResponse = json_decode($status);

				WriteLog(("QueueAdd".$object->RefId), $status);

				$id = $object->RefId;
				$successCount = sizeof($cebResponse->data->successList);
				$failCount = sizeof($cebResponse->data->failList);
				$failList = $cebResponse->data->failList;

				$report = $this->CreateCEBResponseToObjectstoreJson($id, $successCount, $failCount, $failList, "EMAIL");
				$objResult = $this->PushReportToObjectstore($report, $MainNamespace, "CampaignReports", "RefId");
				WriteLog(("QueueAdd".$object->RefId), $objResult);
				$objResponse = new ObjectStoreResponse();
				$objResponse = json_decode($objResult);

				if ($objResponse->IsSuccess == TRUE){
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Successful!");
				}else{
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Failed!");
				}

			}else{
				$failCount =0;
				$successCount = 0;
				$failList = array();
				$id = $object->RefId;

				for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 			if (!empty($resultArray[$x]["Email"]) && $resultArray[$x]["Email"] != "") {
  		 				$requestBody = $this->createCEBEmailRequest($resultArray[$x]["Email"], $subject, $from, $TemplateNamespace, $TemplateID);
  		 				array_push($emailStack, $requestBody);

  		 				if (sizeof($emailStack) == 100){
  		 					$pushObject = $this->createCEBBulkEmailRequest($emailStack, $emailNamespace, $emailClass);
							WriteLog(("QueueAdd".$object->RefId), "Sending an Email Stack... ");
					    	$headers = array('securityToken: ignore');
					    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);

					    	$cebResponse = new CEBNotifierResponse();
							$cebResponse = json_decode($status);

							WriteLog(("QueueAdd".$object->RefId), $status);

							$successCount += sizeof($cebResponse->data->successList);
							$failCount += sizeof($cebResponse->data->failList);
							$failList = array_merge($failList, ($cebResponse->data->failList));

  		 					$count = 0;
  		 					$emailStack = array();

  		 				}else if (sizeof($emailStack) < 100 && x==(sizeof($resultArray)-1)){
  		 					$pushObject = $this->createCEBBulkEmailRequest($emailStack, $emailNamespace, $emailClass);
							WriteLog(("QueueAdd".$object->RefId), "Sending Last Email Stack... ");
					    	$headers = array('securityToken: ignore');
					    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);

					    	$cebResponse = new CEBNotifierResponse();
							$cebResponse = json_decode($status);

							WriteLog(("QueueAdd".$object->RefId), $status);

							$successCount += sizeof($cebResponse->data->successList);
							$failCount += sizeof($cebResponse->data->failList);
							$failList = array_merge($failList, ($cebResponse->data->failList));

  		 				}else{
  		 					//Don't do anything :P
  		 					$count += 1;
  		 				}
    				}
				}

				$report = $this->CreateCEBResponseToObjectstoreJson($id, $successCount, $failCount, $failList, "EMAIL");
				$objResult = $this->PushReportToObjectstore($report, $MainNamespace, "CampaignReports", "RefId");
				WriteLog(("QueueAdd".$object->RefId), $objResult);
				$objResponse = new ObjectStoreResponse();
				$objResponse = json_decode($objResult);

				if ($objResponse->IsSuccess == TRUE){
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Successful!");
				}else{
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Failed!");
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

		private function SendSMSBulk($object){
			WriteLog(("QueueAdd".$object->RefId), "Starting Send SMS Function for CEB Posting....");

			$GroupNamespace = $object->Parameters["JSONData"]["Group"]["Namespace"];
			$GroupID = $object->Parameters["JSONData"]["Group"]["GroupID"];

  		 	$TemplateID = $object->Parameters["JSONData"]["Template"]["TemplateID"];
  		 	$TemplateNamespace = $object->Parameters["JSONData"]["Template"]["Namespace"];

  		 	$emailNamespace = $object->Parameters["JSONData"]["GatewaySettings"]["Namespace"];
  		 	$emailClass = $object->Parameters["JSONData"]["GatewaySettings"]["Class"];
 

			$client = ObjectStoreClient::WithNamespace($GroupNamespace,$GroupID,"ignore");
  		 	$resultArray = $client->get()->all();

			$count = 1;
  		 	$smsStack = array();

  		 	if (sizeof($resultArray) <=100) {
  		 		for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 			if (!empty($resultArray[$x]["PhoneNumber"]) && $resultArray[$x]["PhoneNumber"] != "") {
  		 				$requestBody = $this->createCEBSMSRequest($resultArray[$x]["PhoneNumber"], $subject, $from, $TemplateNamespace, $TemplateID);
  		 				array_push($smsStack, $requestBody);
    				}
				}

				$pushObject = $this->createCEBBulkSMSRequest($smsStack, $emailNamespace, $emailClass);
				
				WriteLog(("QueueAdd".$object->RefId), "Sending an individual SMS Stack... ");
		    	$headers = array('securityToken: ignore');
		    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);

		    	$cebResponse = new CEBNotifierResponse();
				$cebResponse = json_decode($status);

				WriteLog(("QueueAdd".$object->RefId), $status);

				$id = $object->RefId;
				$successCount = sizeof($cebResponse->data->successList);
				$failCount = sizeof($cebResponse->data->failList);
				$failList = $cebResponse->data->failList;

				$report = $this->CreateCEBResponseToObjectstoreJson($id, $successCount, $failCount, $failList, "SMS");
				$objResult = $this->PushReportToObjectstore($report, $MainNamespace, "CampaignReports", "RefId");
				WriteLog(("QueueAdd".$object->RefId), $objResult);
				$objResponse = new ObjectStoreResponse();
				$objResponse = json_decode($objResult);

				if ($objResponse->IsSuccess == TRUE){
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Successful!");
				}else{
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Failed!");
				}

			}else{

				$failCount =0;
				$successCount = 0;
				$failList = array();
				$id = $object->RefId;

				for ($x = 0; $x < sizeof($resultArray); $x++) {
  		 			if (!empty($resultArray[$x]["PhoneNumber"]) && $resultArray[$x]["PhoneNumber"] != "") {
  		 				$requestBody = $this->createCEBSMSRequest($resultArray[$x]["PhoneNumber"], $subject, $from, $TemplateNamespace, $TemplateID);
  		 				array_push($smsStack, $requestBody);

  		 				if (sizeof($smsStack) == 100){
  		 					$pushObject = $this->createCEBBulkSMSRequest($smsStack, $emailNamespace, $emailClass);
							WriteLog(("QueueAdd".$object->RefId), "Sending an SMS Stack... ");
					    	$headers = array('securityToken: ignore');
					    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);

					    	$cebResponse = new CEBNotifierResponse();
							$cebResponse = json_decode($status);

							WriteLog(("QueueAdd".$object->RefId), $status);

							$successCount += sizeof($cebResponse->data->successList);
							$failCount += sizeof($cebResponse->data->failList);
							$failList = array_merge($failList, ($cebResponse->data->failList));

  		 					$count = 0;
  		 					$smsStack = array();
  		 				}else if (sizeof($smsStack) < 100 && x==(sizeof($resultArray)-1)){
  		 					$pushObject = $this->createCEBBulkSMSRequest($smsStack, $emailNamespace, $emailClass);
							WriteLog(("QueueAdd".$object->RefId), "Sending Last SMS Stack... ");
					    	$headers = array('securityToken: ignore');
					    	$status = CurlPost(SVC_CEB_URL."command/notification", $pushObject, $headers);

							$cebResponse = new CEBNotifierResponse();
							$cebResponse = json_decode($status);

							WriteLog(("QueueAdd".$object->RefId), $status);

							$successCount += sizeof($cebResponse->data->successList);
							$failCount += sizeof($cebResponse->data->failList);
							$failList = array_merge($failList, ($cebResponse->data->failList));

  		 				}else{
  		 					//Don't do anything :P
  		 					$count += 1;
  		 				}
    				}
				}

				$report = $this->CreateCEBResponseToObjectstoreJson($id, $successCount, $failCount, $failList, "SMS");
				$objResult = $this->PushReportToObjectstore($report, $MainNamespace, "CampaignReports", "RefId");
				WriteLog(("QueueAdd".$object->RefId), $objResult);
				$objResponse = new ObjectStoreResponse();
				$objResponse = json_decode($objResult);

				if ($objResponse->IsSuccess == TRUE){
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Successful!");
				}else{
					WriteLog(("QueueAdd".$object->RefId), "Report Saving to ObjectStore Failed!");
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

		private function CreateCEBResponseToObjectstoreJson($RefId, $successCount, $failCount, $failList, $type){
			$date = new DateTime();
			$dateString = $date->format('YmdHis');
			$readableTimeStamp = $date->format('Y-m-d H:i:s');
			$request = array("RefId" => ($RefId."-".$dateString),
							 "SuccessCount" => $successCount,
							 "FailCount" => $failCount,
							 "TotalCount" => ($successCount+$failCount),
							 "FailList" => $failList,
							 "Type" => $type,
							 "TimeStamp" => $readableTimeStamp);
			return $request;
		}

		private function PushReportToObjectstore($record, $namespace, $class, $primarykey){
			$status = TRUE;
			$data = array("Object" => $record, "Parameters" => array("KeyProperty" => $primarykey));                                                          
			$headers = array('securityToken: ignore');
			$status = CurlPost(SVC_OS_URL."/".$namespace."/".$class, $data, $headers);
			return $status;
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

		private function createCEBBulkEmailRequest($emailArray, $class, $namespace){
			$request = array("configuration" => array("class" => $class, "namespace" => $namespace),
							 "type" => "bulkemail",
							 "recivers" => $emailArray);
			return $request;
		}

		private function createCEBBulkSMSRequest($smsArray, $class, $namespace){
			$request = array("configuration" => array("class" => $class, "namespace" => $namespace),
							 "type" => "bulksms",
							 "recivers" => $smsArray);
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
			Flight::route("POST /queuemanager/test", function (){$this->test();});
			Flight::route("GET /queuemanager/enqueue/loglist", function (){$this->getQueueAddList();});
			Flight::route("GET /queuemanager/enqueue/log/@refid", function ($refid){
				$this->getQueueAddLog($refid);
            });
		}
	}
?>