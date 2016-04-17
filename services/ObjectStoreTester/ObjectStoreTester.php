<?php

class objectstoretester {

		private function upload($namespace, $class){
			$body = Flight::request()->getBody();
			$data = json_decode($body);
			$response = $this->obj($data, $namespace, $class);
			$response1 = json_decode($response);
			if ($response1->IsSuccess == TRUE) {
				exit(header("Status: 200 OK"));
			}else{
				exit(header('HTTP/1.1 500 Internal Server Error')); 	
			}

		}

		private function obj($data, $namespace, $class){                                                        
			$headers = array('securityToken: ignore');
			$status = CurlPost(SVC_OS_URL."/".$namespace."/".$class, $data, $headers);
			return $status;
		}
	
		function __construct(){
			Flight::route("POST /objectstore/@namespace/@class", function ($namespace, $class){
				$this->upload($namespace, $class);
            });
		}
	}
?>