<?
define('NO_AGENT_CHECK', true);
define("STOP_STATISTICS", true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if ($_POST["id"] > 0) {

	if(!CModule::IncludeModule("leadhit.leadhit")){
		return;
	}


	if($arParams = CLeadhitRequest::GetByID($_POST["id"])){

		$paramsList = json_decode($arParams["PARAMS"]);
		$paramsList = (array) $paramsList;
		$http_data = "";
		$i = 0;
		foreach ($paramsList as $key => $param) {
			if($key == "cart"){
				$http_data .= "&".$key."=".json_encode($param);
			}else{
				if($i > 0){
					$http_data .= "&".$key."=".urlencode($param);
				}else{
					$http_data .= $key."=".urlencode($param);
				}
			}
			$i++;
		}

		if(strpos($arParams["URL"],"http://") !== false){
	    	$host = str_replace("http://", "", $arParams["URL"]);
	    	$arHost = explode("/", $host);
	    	$host = $arHost[0];
	    	$page = "";
	    	for($i = 1; $i <= (count($arHost)-1); $i++){
	    		$page .= "/".$arHost[$i];
	    	}
	    	
	    }elseif(strpos($arParams["URL"],"https://") !== false){
	    	$host = str_replace("https://", "", $arParams["URL"]);
	    	$arHost = explode("/", $host);
	    	$host = $arHost[0];
	    	$page = "";
	    	for($i = 1; $i <= (count($arHost)-1); $i++){
	    		$page .= "/".$arHost[$i];
	    	}
	    }
	
		/*$fp = fsockopen("ssl://".$host,
	                 443, $errno, $errstr, 5);*/
		
		/*$fp = fsockopen($host,
	                 6789, $errno, $errstr, 5);*/

		$timeout = 8;

		$fp = fsockopen($host,
	                 6789, $errno, $errstr, $timeout);

		if (!$fp) {
			$time = ConvertTimeStamp(time(), "FULL", "ru");
		    CLeadhitRequest::UpdateRequest($arParams["ID"], $time);
		} else {

		    if($arParams["TYPE"] == "POST"){
		    	fwrite($fp, $arParams["TYPE"]." ".$page." HTTP/1.0\r\n");
		    }else{
		    	fwrite($fp, $arParams["TYPE"]." ".$page."?".$http_data." HTTP/1.0\r\n");
		    }
		    
	        fwrite($fp, "Host: ".$host."\r\n");
	        fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	        fwrite($fp, "Content-length: " . strlen($http_data) . "\r\n");
	        fwrite($fp, "Connection: Close\r\n\r\n");
	        if($arParams["TYPE"] == "POST"){
		        fwrite($fp, $http_data."\r\n\r\n");
		    }

	        stream_set_blocking($fp, TRUE); 
	        stream_set_timeout($fp, $timeout); 
	        $info = stream_get_meta_data($fp); 

	        while ((!feof($fp)) && (!$info['timed_out'])) { 
	            $_return .= fgets($fp, 4096); 
	            $info = stream_get_meta_data($fp); 
	            ob_flush; 
	            flush(); 
	        }

	        fclose($fp);

	        if ($info['timed_out']) {
	            echo "Connection Timed Out!";
	            $time = ConvertTimeStamp(time(), "FULL", "ru");
		    	CLeadhitRequest::UpdateRequest($arParams["ID"], $time);
	        }else{

	            $arHeaders = explode("\n", $_return);

			    $status = CLeadhitRequest::ProcessReturnHeaders($arHeaders);
			    if($status) {
			    	CLeadhitRequest::DeleteRequest($arParams["ID"]);
			    }else{
			    	$time = ConvertTimeStamp(time(), "FULL", "ru");
			    	CLeadhitRequest::UpdateRequest($arParams["ID"], $time);
			    }
	        }
		}
	}
}
?>