<?
/**
* 
*/
class CLeadhitRequest
{
	
	public function Add($PARAMS) {
		global $DB;

		$arInsert = $DB->PrepareInsert("leadhit_request", $PARAMS);

		$strSql =
			"INSERT INTO leadhit_request(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";

		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public function GetByID($ID) {
		global $DB;

		if(self::CheckTable() === true && intval($ID)) {
			$strSql =
				"SELECT * ".
				"FROM leadhit_request ".
				"WHERE `ID` = ".$ID;
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
				return $res;
		}

		return false;
	}

	public function UpdateStatusSuccess($ID) {
		global $DB;
		$strSql = "UPDATE leadhit_request SET SUCCESS_EXEC = 'Y' WHERE ID = '".$DB->ForSql($ID)."'";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $ID;
	}

	public function UpdateRequest($ID, $TIME) {
		global $DB;
		$arUpdate = $DB->PrepareInsert("leadhit_request", array("DATE_LAST_TRY" => $TIME));
		return $DB->Query("UPDATE leadhit_request SET ".$arUpdate[0]." = ".$arUpdate[1]." WHERE ID = '".$DB->ForSQL($ID)."'", true);
	}

	public function CheckTable() {
		if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'leadhit_request'"))==1) 
			return true;
		else return false;
	}

	public function GetLastSentRequest() {
		global $DB;

		if(self::CheckTable() === true) {
			$strSql =
				"SELECT * ".
				"FROM leadhit_request ".
				"ORDER BY DATE_LAST_TRY ASC LIMIT 1";
			$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($res = $db_res->Fetch())
				return $res;
		}

		return false;
	}

	public function DeleteRequest($ID) {
		global $DB;
		return $DB->Query("DELETE FROM leadhit_request WHERE ID = '".$DB->ForSQL($ID)."'", true);
	}

	public function ProcessReturnHeaders($arHeaders){
		$return = false;
		if(!empty($arHeaders)){
			$status = explode(" ", $arHeaders["0"]);
			switch ($status["1"]) {
				case ($status["1"] < 500):
					$return = true;
					break;
				
				default:
					$return = false;
					break;
			}
		}
		return $return;
	}
}
?>