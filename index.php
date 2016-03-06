<?php
  require_once ('./include/flight/Flight.php');
  require_once ('./include/common.php');
  require_once ('./include/config.php');
  require_once ("./uploader.php");
  new uploader();
	
  Flight::start();

  header('Access-Control-Allow-Headers: Content-Type');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST');  
?>