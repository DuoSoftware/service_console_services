<?php

class dispatcher {
		private function About(){
			$arr = array('Name' => "Service Console Dispatcher Service", 'Version' => "1.0.0-a", 'Change Log' => "Refactored Project!", 'Author' => "Duo Software", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		function __construct(){
			Flight::route("GET /dispatcher", function (){$this->About();});
		}
	}
?>