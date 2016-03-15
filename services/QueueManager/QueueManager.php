<?php

class huehue{
	private $one;
	private $two;
}

class QueueManager {
		private function About(){
			$arr = array('Name' => "Service Console Queue Manager Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function enqueue(){
			ConsoleLog("Executing Enqueue Method");
			$data = $_POST;
			$namespaceAndClass = explode(".", $data["RefType"]);
			CurlPost(SVC_CHECK_URL.$namespaceAndClass[0]."/".$namespaceAndClass[1], $data, $headers);
		}

		function __construct(){
			Flight::route("GET /queuemanager", function (){$this->About();});
			Flight::route("POST /queuemanager/enqueue", function (){$this->enqueue();});
		}
	}
?>