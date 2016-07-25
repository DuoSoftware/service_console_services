<?php

class CampaignManager {
		private function About(){
			$arr = array('Name' => "Service Console Scheduler Service", 'Version' => "1.0.1-a", 'Change Log' => "Refactored Project!", 'Author' => "THIS IS TESTING FFS!", 'Repository' => "https://github.com/DuoSoftware/service_console_services");
			echo json_encode($arr);
		}

		private function RedFromObjectStore($refid, $namespace, $class, $id){
			$status = TRUE;                    
			$headers = array('securityToken: ignore');
			$status = CurlGet(SVC_OS_URL."/".$namespace."/".$class."/".$id, $headers);
			return $status;
		}

		private function GetFromPendingList($refid){
			$status = TRUE;                    
			$headers = array('securityToken: ignore');

			$pending = $this->RedFromObjectStore($refid, "pending.console.data", "scheduleobjects", $refid);
			$pendingObject = new ObjectStoreResponse();
			$pendingObject = json_decode($pending);

			$completed = $this->RedFromObjectStore($refid, "completed.console.data", "scheduleobjects", $refid);
			$completedObject = new ObjectStoreResponse();
			$completedObject = json_decode($completed);

			$message = "Pending";

			if (empty($pendingObject) && empty($completedObject)){
				$message = "Not Found";
			}else if (empty($pendingObject) && $completedObject->ScheduleParameters->ScheduleOccurence == 0) {
				$message= "Completed";
			}else if (!empty($completedObject) && $completedObject->ScheduleParameters->ScheduleOccurence == 0 ){
					$message = "completed";
			}

			echo json_encode($message);
		}

		function __construct(){
			Flight::route("GET /duoworld/campaignmanager", function (){$this->About();});
			Flight::route("GET /duoworld/campaignmanager/status/@refid", function ($refid){
				$this->GetFromPendingList($refid);
            });

		}
	}

?>