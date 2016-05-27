<?php

function ConsoleLog( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Log : " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Log : " . $data . "' );</script>";
	//echo($output);
}

function AuthLogin($authURL, $username, $password, $domain){
	ConsoleLog("Sign in Attempt as : ".$username);
	$emptyHeaders  = array();
	$result = CurlGet($authURL."Login/".$username."/".$password."/".$domain, $emptyHeaders);
	return $result;
}

function GetGlobalConfigurations(){
	$config = getCachedCEB_Config("CEB_Config");
	return $config;
}

function ReadFromCEB($securityToken){
	$url = SVC_CEB_URL."command/getglobalconfig/";
	$data = array("class" => "ConsoleConfig");
	$headers = array('securitytoken: '.$securityToken, 'Content-Type: application/json');
	$config = CurlPost($url, $data, $headers);
	return json_decode($config, true);
}

function GetSecurityToken(){
	$authdata = json_decode(AuthLogin(SVC_AUTH_URL,SVC_AUTH_USERNAME,SVC_AUTH_PASSWORD,SVC_AUTH_DOMAIN), true);
	$token = "INVALID";
	if (sizeof($authdata)>0){
		$token =  $authdata['SecurityToken'];
	}
	return $token;
}

function CurlGet($url, $headers){
	
	// $headerArray = array(                                                                          
	// 		    'Content-Type: application/json',                                                                                
	// 		    'Content-Length: ' . strlen($data_string));

	$headerArray = array(                                                                          
			    'Content-Type: application/json');

	if(!empty($headers)){
		$headerArray=array_merge($headers, $headerArray);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);          
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray); 
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function CurlPost($url, $data, $headers){                                                                
	$data_string = json_encode($data);

	$headerArray = array(                                                                          
			    'Content-Type: application/json',                                                                                
			    'Content-Length: ' . strlen($data_string));

	if(!empty($headers)){
		$headerArray=array_merge($headers, $headerArray);
	}
			        	                                                                                                             
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);                                                                                                                                                                                                                                        
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function CurlUploadFile($url, $file, $fileName){
	ConsoleLog("Start uploading file to : ".SVC_OS_URL."/".SVC_UPLOAD_PATH);
			
	$data = ""; 
	$boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10); 

	$data .= "--$boundary\n"; 
	
	$fileContents = file_get_contents($file['tmp_name']); 
	$data .= "Content-Disposition: form-data; name=\"file\"; filename=\"".$fileName."\"\n"; 
	$data .= "Content-Type: multipart/form-data\n";
	$data .= "Content-Transfer-Encoding: binary\n\n"; 
	$data .= $fileContents."\n"; 
	$data .= "--$boundary--\n"; 

	$params = array('http' => array( 
		           'method' => 'POST', 
		           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
		           'content' => $data 
	)); 

	$ctx = stream_context_create($params); 
	$fp = fopen($url, 'rb', false, $ctx); 

	if (!$fp) { 
		ConsoleLog("Invalid URL. Check AppEngine Configurations!"); 
	} 

	$status = TRUE;
	$response = @stream_get_contents($fp); 

	if ($response === false) { 
		$status = FALSE;
		ConsoleLog("Problem reading data from url"); 
	}

	return $status;
}

function getCachedSecurityToken() {
  $memcache = new Memcache;
  $data = $memcache->get("securityToken");
  if ($data === false) {
  	ConsoleLog("SecurityToken Not Found in Cache!");
    $data = GetSecurityToken();
    $memcache->set("securityToken", $data);
  }else{
  	ConsoleLog("SecurityToken Found in Cache");
  }
  return $data;
}

function getCachedCEB_Config() {
  $memcache = new Memcache;
  $data = $memcache->get("CEB_Config");
  if ($data === false) {
  	ConsoleLog("CEB_Config Not Found in Cache!");
    $data = ReadFromCEB(getCachedSecurityToken());
    $memcache->set("CEB_Config", $data);
  }else{
  	ConsoleLog("CEB_Config Found in Cache");
  }
  return $data;
}

?>