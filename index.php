<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'include/flight/Flight.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'include/common.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'include/config.php');
require_once ("./uploader.php");

new uploader();
	
Flight::start();

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');  
?>