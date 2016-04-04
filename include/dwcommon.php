<?php

class DuoWorldCommon {
	
	public static function GetHost(){
		//return "dilshan.sossgrid.com";
		//return "hnb.bank.com";
		return $_SERVER['HTTP_HOST'];
	}

	public function CheckAuth(){
		$currentHost = DuoWorldCommon::GetHost();
		$isLocalHost = $this->startsWith($currentHost, "localhost") || $this->startsWith($currentHost, "127.0.0.1");

		if (!$isLocalHost){
			if(!isset($_COOKIE['securityToken'])){
				header("Location: http://" . $currentHost . "/s.php?r=". $_SERVER['REQUEST_URI']);
				exit();
			}
		}
	}

	public function ValidateToken($token){
	        $ch = curl_init();

	        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:3048/Login/admin@duosoftware.com/admin/duosoftware.com');
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        $data = curl_exec($ch);
	        $authObject = json_decode($data);
	        curl_close($ch);
	        var_dump($data);
	}

	private function startsWith($haystack, $needle) {
	    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	public static function mapToObject($data, $class) {
		foreach ($data as $key => $value) $class->{$key} = $value;
		return $class;
	}
}

class WsInvoker {
	
	private $baseUrl;
	private $conType;
	private $headerArray;


	public function post($relUrl, $body){

//		echo json_encode($body);

		$data_string = json_encode($body);
        
//		echo $this->baseUrl . $relUrl;
//		echo "<br/>";
		
		if (!isset($this->conType))
			$this->conType = 'application/json';

		$ch = curl_init($this->baseUrl . $relUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->addHeader('Content-Type', $this->conType);
		$this->addHeader('Content-Length', strlen($data_string));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headerArray);
		
		$result = curl_exec($ch);
		//var_dump($result);
		return $result;
	}

	public function delete($relUrl, $body){

//		echo json_encode($body);

		$data_string = json_encode($body);
        
//		echo $this->baseUrl . $relUrl;
//		echo "<br/>";
		
		if (!isset($this->conType))
			$this->conType = 'application/json';

		$ch = curl_init($this->baseUrl . $relUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$this->addHeader('Content-Type', $this->conType);
		$this->addHeader('Content-Length', strlen($data_string));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headerArray);
		
		$result = curl_exec($ch);
		//var_dump($result);
		return $result;
	}

	public function addHeader($k, $v){
		array_push($this->headerArray, $k . ": " . $v);
	}

	public function get($relUrl){
		//echo $this->baseUrl . $relUrl;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPGET,true);
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $relUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER,  $this->headerArray);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		return $result;
	}	

	public function map($json, $class) {
		$data = json_decode($json, true);
		return Common::mapToObject($data,$class);
	}

	public function setContentType($ct){
		$this->conType = $ct;
	}

	function __construct($bu){
		$this->baseUrl = $bu;
		$this->headerArray = Array();
	}
}

$DUO_COMMON = new DuoWorldCommon();

//$pageName = basename($_SERVER['PHP_SELF']);
//if ($pageName =="login.php" || $pageName =="")

//$DUO_COMMON->CheckAuth();

?>
