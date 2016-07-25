<?php
  require_once ('./include/flight/Flight.php');
  require_once ('./include/common.php');
  require_once ('./info.php');
  require_once ('./include/config.php');
  require_once ("./services/uploader/uploader.php");
  require_once ("./services/dispatcher/dispatcher.php");
  require_once ("./services/QueueManager/QueueManager.php");
  require_once ("./services/scheduler/scheduler.php");
  require_once ("./services/structs/requests.php");
  require_once ('./include/dwcommon.php');
  require_once ('./include/objectstoreproxy.php');
  require_once ("./services/TaskDispatcher/TaskDispatcher.php");
  require_once ("./services/ObjectStoreTester/ObjectStoreTester.php");

   require_once ("./services/DuoWorld/CampaignManager/campaignmain.php");
  
  new info();
  new uploader();
  new dispatcher();
  new QueueManager();
  new scheduler();
  new TaskDispatcher();
  new objectstoretester();
  new CampaignManager();
	
  Flight::start();

  header('Access-Control-Allow-Headers: Content-Type');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');  
?>