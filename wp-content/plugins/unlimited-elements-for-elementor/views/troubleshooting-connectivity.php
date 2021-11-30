
<h1>Unlimited Elements - API Access Test</h1>

<br>

<?php

/**
 * check zip file request
 */
function checkZipFile(){
	
	//request single file
	$urlAPI = GlobalsUC::URL_API;
	
	$arrPost = array(
		"action"=>"get_addon_zip",
		"name"=>"team_member_box_overlay",
		"cat"=>"Team Members",
		"type"=>"addons",
		"catalog_date"=>"1563618449",
		"code"=>""
	);
	
	
	dmp("requesting widget zip from API");
	
	$response = UniteFunctionsUC::getUrlContents($urlAPI, $arrPost);
	
	if(empty($response))
		UniteFunctionsUC::throwError("Empty server response");
	
	$len = strlen($response);
		
	dmp("api response OK, recieve string size: $len");
	
}


/**
 * check zip file request
 */
function checkCatalogRequest(){
	
	//request single file
	$urlAPI = GlobalsUC::URL_API;
	
	$arrPost = array(
		"action"=>"check_catalog",
		"catalog_date"=>"1563618449",
		"include_pages"=>false,
		"domain"=>"localhost",
		"platform"=>"wp"	
	);
	
	dmp("requesting catalog check");
	
	try{
		
		$response = UniteFunctionsUC::getUrlContents($urlAPI, $arrPost);
				
		if(empty($response))
			UniteFunctionsUC::throwError("Empty server response");
		
		$len = strlen($response);
		
		dmp("api response OK, recieve string size: $len");
			
	}catch(Exception $e){
		
		$message = $e->getMessage()."\n<br>";
		
		$message .= "The request to the catalog url has failed. \n<br>";
		$message .= "Please contact your hosting provider and request to open firewall access to this address: \n<br>";
		$message .= "http://api.unlimited-elements.com/";

		UniteFunctionsUC::throwError($message);
	}
		
	
}

/**
 * various
 */
function checkVariousOptions(){
	
	dmp("checking file get contents");
	
	$urlAPI = GlobalsUC::URL_API;
	$response = file_get_contents($urlAPI);
	
	$len = strlen($response);
	
	dmp("file get contents OK, recieve string size: $len");
	
}

try{
	
	checkVariousOptions();
	
	echo "<br><br>";
	
	checkCatalogRequest();
	
	echo "<br><br>";
	
	checkZipFile();
	
	
}catch(Exception $e){
	echo $e->getMessage();
}

