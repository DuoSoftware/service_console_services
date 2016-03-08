<?php
// Use this class to expose public API running stats and API references
class info {
		private function About(){
			$arr = array('Name' => "Duo World Application Interface", 'Version' => "1.0.0-a", 'Change Log' => "Nothing so far just testing. Move along!", 'Author' => "Duo Software", 'Website' => "http://www.duoworld.com/", 'Status' => "Running");
			echo json_encode($arr);
		}

		function __construct(){
			Flight::route("GET /", function (){$this->About();});
		}
	}
?>