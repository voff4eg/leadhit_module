<?
/**
* 
*/
class CLeadhitBasketUserHash
{
	public function getBasketUserHash($user = 0){

		$user = intval($user);

		$strSql = 
		"SELECT `HASH` ".
				"FROM `leadhit_basket_user_hash` ".
				"WHERE `FUSER_ID` = ";

		if($user){
			$strSql .= $user;
		}else{
			$strSql .= CSaleBasket::GetBasketUserID();
		}

		global $DB;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch()){
			return $res;
		}else{
			return array();
		}
	}

	public function addBasketUserHash(){

		global $DB;

		$strSql =
			"INSERT INTO `leadhit_basket_user_hash` (`FUSER_ID`,`HASH`) ".
			"VALUES(".CSaleBasket::GetBasketUserID().",'".self::generateBasketUserHash()."')";

		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public function generateBasketUserHash(){
		return hash("haval128,3", uniqid());
	}

	public function checkBasketUserHash($user,$hash){

		$user = intval($user);
		$hash = substr($hash,0,32);

		$strSql = 
		"SELECT `ID` ".
				"FROM `leadhit_basket_user_hash` ".
				"WHERE `FUSER_ID` = ".$user." AND ".
				"`HASH` = '".$hash."'";

		global $DB;

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch()){
			return $res["ID"];
		}else{
			return false;
		}
	}
}