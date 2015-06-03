<?
/**
* Utils for module leadhit
*/
class CLeadhitUtils {
	
	public static $MODULE_ID = "leadhit.leadhit";
	public static $MODULE_DIR = "/bitrix/modules/leadhit.leadhit";

	public static function getCLID() {
		return COption::GetOptionString(self::$MODULE_ID, "CLID");
	}

	public function getHeaders() {
		if (!function_exists('getallheaders')) 
		{ 
		    function getallheaders() 
		    { 
		           $headers = ''; 
		       foreach ($_SERVER as $name => $value) 
		       { 
		           if (substr($name, 0, 5) == 'HTTP_') 
		           { 
		               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
		           } 
		       } 
		       return $headers; 
		    } 
		}

		//return getallheaders();
		return apache_response_headers();
	}

	public function PrepareParam ($param) {
		if(strlen($param)){
			return $param;
		}else{
			return "''";
		}
	}

	public function GetConvertedString ($string) {
		$encoding = mb_detect_encoding($string, array("UTF-8","CP1251"));
		$string = iconv($encoding, "UTF-8", $string);
		return $string;
	}
}
?>