<?
/**
* Get js script from $MODUILE_DIR/script/script.js to add on page with clid
*/

require_once(__DIR__."/CLeadhitUtils.php");

class CLeadhitScript {
	
	public static function getScript() {
		$filename = $_SERVER["DOCUMENT_ROOT"].CLeadhitUtils::$MODULE_DIR."/script/script.js";
		$handle = fopen($filename, "r");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		$CLID = COption::GetOptionString(CLeadhitUtils::$MODULE_ID, "CLID");
		if(strlen($contents) && $CLID){
			return str_replace("{{clid}}", $CLID, $contents);
		}else{
			return false;
		}
	}

	public static function includeScriptState(){
		return COption::GetOptionString(CLeadhitUtils::$MODULE_ID, "INCLUDE_SCRIPT");
	}

	public static function getSendRequestScript($params) {
		return "<script type=\"text/javascript\" language=\"javascript\">
		var data = {
			'id': ".$params["ID"]."
        };
		BX.ready(function(){
			BX.ajax.post(
	        '/bitrix/components/leadhit/leadhit.sender/sender.php',
	        data,  
	        function(res){ console.log(res);}
		);
	});
</script>";
	}
}
?>