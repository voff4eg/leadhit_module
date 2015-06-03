<?
CModule::AddAutoloadClasses(
	"leadhit.leadhit",
	array(
	    "CLeadhitUtils" => "classes/CLeadhitUtils.php",
	    "CLeadhitScript" => "classes/CLeadhitScript.php",
	    "CLeadhitRequest" => "classes/CLeadhitRequest.php",
	    "CLeadhitBasket" => "classes/CLeadhitBasket.php",
	    "CLeadhitBasketUserHash" => "classes/CLeadhitBasketUserHash.php",
	)
);

Class CLeadhitLeadhit {
	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu) {
		if($GLOBALS['APPLICATION']->GetGroupRight("main") < "R")
			return;

		$MODULE_ID = CLeadhitUtils::$MODULE_ID;
		$aMenu = array(
			//"parent_menu" => "global_menu_services",
			"parent_menu" => "global_menu_settings",
			"section" => $MODULE_ID,
			"sort" => 50,
			"text" => $MODULE_ID,
			"title" => '',
			"url" => "leadhit.leadhit_settings.php",
			"icon" => "",
			"page_icon" => "",
			"items_id" => $MODULE_ID."_items",
			"more_url" => array(),
			"items" => array()
		);

		if (file_exists($path = dirname(__FILE__).'/admin'))
		{
			if ($dir = opendir($path))
			{
				$arFiles = array();

				while(false !== $item = readdir($dir))
				{
					if (in_array($item,array('.','..','menu.php')))
						continue;

					if (!file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$MODULE_ID.'_'.$item))
						file_put_contents($file,'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.$MODULE_ID.'/admin/'.$item.'");?'.'>');

					$arFiles[] = $item;
				}

				sort($arFiles);

				foreach($arFiles as $item)
					$aMenu['items'][] = array(
						'text' => $item,
						'url' => $MODULE_ID.'_'.$item,
						'module_id' => $MODULE_ID,
						"title" => "",
					);
			}
		}
		$aModuleMenu[] = $aMenu;
	}

	function ChangeBufferContent(&$content) {
		$reqHeaders = CLeadhitUtils::getHeaders();
		if(/*$reqHeaders["Bx-Ajax"] !== true && *//*empty($_SERVER['HTTP_X_REQUESTED_WITH']) && */strlen($content) > 0 && strpos(strtolower($content), "<body>") !== false) {
			if(CLeadhitScript::includeScriptState() == "on"){
				if($script = CLeadhitScript::getScript()){
					$content .= "<script type=\"text/javascript\" language=\"javascript\">".$script."</script>";
				}
			}

			$reqParams = CLeadhitRequest::GetLastSentRequest();
			if(!empty($reqParams)){
				$script = CLeadhitScript::getSendRequestScript($reqParams);
				$content .= $script;
			}
		}
	}

	function GetOrderStatus($ID) {
		switch ($ID) {
			case 'N':
				$status = "confirmed";
				break;

			case 'P':
				$status = "paid";
				break;

			case 'F':
				$status = "delivered";
				break;

			default:
				$status = "";
				break;
		}
		return $status;
	}

	function GetSendToCartParams($ID = 0, $arOrder = array()) {
		$arBasket = CLeadhitBasket::getUserBasket($ID);
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		$arParams = false;$arSendParams = array();
		$arLHTM_R = explode("|", $_COOKIE["_lhtm_r"]);
		if(!empty($arBasket)){
			if(intval($ID) <= 0){
				$arSendParams = array(
					"url"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"uid"  => CLeadhitUtils::PrepareParam($_COOKIE["_lhtm_u"]),
					"vid"  => CLeadhitUtils::PrepareParam($arLHTM_R["1"]),
					"ref"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"clid" => CLeadhitUtils::getCLID(),
					"cart" => array(
							"fuser_hash" => CLeadhitUtils::PrepareParam($arHash["HASH"]),
							"name" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetFullName())) : "''"),
							"email" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetEmail())) : "''"),
							"order_id" => "''",
							"phone" => "''",
							"address" => "''",
							"sum" => "''",
							"status" => "unready",
							"contents" => json_encode($arBasket)
						),

				);
			}else{

				if(empty($arOrder)){
					$arOrder = CSaleOrder::GetByID($ID);
				}
				$dbOrderProps = CSaleOrderPropsValue::GetList(
			        array("SORT" => "ASC"),
			        array("ORDER_ID" => $ID)
			    );
			    while ($arOrderProps = $dbOrderProps->GetNext()):
			        $arOrderPropList[ $arOrderProps["CODE"] ] = $arOrderProps;
			    endwhile;

				$arSendParams = array(
					"url"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"uid"  => CLeadhitUtils::PrepareParam($_COOKIE["_lhtm_u"]),
					"vid"  => CLeadhitUtils::PrepareParam($arLHTM_R["1"]),
					"ref"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"clid" => CLeadhitUtils::getCLID(),
					"cart" => array(
							"fuser_hash" => CLeadhitUtils::PrepareParam($arHash["HASH"]),
							"name" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetFullName())) : CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString( $arOrderPropList["FIO"]["VALUE"]))),
							"email" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetEmail())) : CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString( $arOrderPropList["EMAIL"]["VALUE"]))),
							"order_id" => $arOrder["ID"],
							"phone" => CLeadhitUtils::PrepareParam($arOrderPropList["PHONE"]["VALUE"]),
							"address" => CLeadhitUtils::PrepareParam(trim(CLeadhitUtils::GetConvertedString($arOrderPropList["CITY"]["VALUE"]." ".$arOrderPropList["LOCATION"]["VALUE"]." ".$arOrderPropList["ADDRESS"]["VALUE"]))),
							"sum" => CLeadhitUtils::PrepareParam($arOrder["PRICE"]),
							"status" => ($arOrder["CANCELED"] == "N" ? self::GetOrderStatus($arOrder["STATUS_ID"]) : "stub"),
							"contents" => json_encode($arBasket)
						),

				);
			}

			$time = ConvertTimeStamp(time(), "FULL", "ru");

			$arParams = array(
				"PARAMS" => json_encode($arSendParams),
				"TYPE" => "POST",
				"DATE_INSERT" => $time,
				"DATE_LAST_TRY" => $time,
				//"URL" => "http://dev.leadhit.ru:6789/stat/cart",
				"URL" => "http://dev.leadhit.ru/stat/cart",
				"SUCCESS_EXEC" => "N"
			);
		}else{
			if($ID){
				$arOrder = CSaleOrder::GetByID($ID);
				$dbOrderProps = CSaleOrderPropsValue::GetList(
			        array("SORT" => "ASC"),
			        array("ORDER_ID" => $ID)
			    );
			    while ($arOrderProps = $dbOrderProps->GetNext()):
			        $arOrderPropList[ $arOrderProps["CODE"] ] = $arOrderProps;
			    endwhile;
			    
				$arSendParams = array(
					"url"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"uid"  => CLeadhitUtils::PrepareParam($_COOKIE["_lhtm_u"]),
					"vid"  => CLeadhitUtils::PrepareParam($arLHTM_R["1"]),
					"ref"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"clid" => CLeadhitUtils::getCLID(),
					"cart" => array(
							"fuser_hash" => CLeadhitUtils::PrepareParam($arHash["HASH"]),
							"name" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetFullName())) : CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($arOrderPropList["FIO"]["VALUE"]))),
							"email" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam($GLOBALS['USER']->GetEmail()) : CLeadhitUtils::PrepareParam($arOrderPropList["EMAIL"]["VALUE"])),
							"order_id" => CLeadhitUtils::PrepareParam($arOrder["ID"]),
							"phone" => CLeadhitUtils::PrepareParam($arOrderPropList["PHONE"]["VALUE"]),
							"address" => CLeadhitUtils::PrepareParam(trim(CLeadhitUtils::GetConvertedString($arOrderPropList["CITY"]["VALUE"]." ".$arOrderPropList["LOCATION"]["VALUE"]." ".$arOrderPropList["ADDRESS"]["VALUE"]))),
							"sum" => CLeadhitUtils::PrepareParam($arOrder["PRICE"]),
							"status" => ($arOrder["CANCELED"] == "N" ? self::GetOrderStatus($arOrder["STATUS_ID"]) : "stub"),
							"contents" => json_encode($arBasket)
						),

				);
				$time = ConvertTimeStamp(time(), "FULL", "ru");
				$arParams = array(
					"PARAMS" => json_encode($arSendParams),
					"TYPE" => "POST",
					"DATE_INSERT" => $time,
					"DATE_LAST_TRY" => $time,
					//"URL" => "http://dev.leadhit.ru:6789/stat/cart",
					"URL" => "http://dev.leadhit.ru/stat/cart",
					"SUCCESS_EXEC" => "N"
				);
			}else{
				//cart deleted
				$arSendParams = array(
					"url"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"uid"  => CLeadhitUtils::PrepareParam($_COOKIE["_lhtm_u"]),
					"vid"  => CLeadhitUtils::PrepareParam($arLHTM_R["1"]),
					"ref"  => CLeadhitUtils::PrepareParam($_SERVER["HTTP_REFERER"]),
					"clid" => CLeadhitUtils::getCLID(),
					"cart" => array(
							"fuser_hash" => CLeadhitUtils::PrepareParam($arHash["HASH"]),
							"name" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetFullName())) : "''"),
							"email" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString($GLOBALS['USER']->GetEmail())) : "''"),
							"order_id" => "''",
							"phone" => "''",
							"address" => "''",
							"sum" => "''",
							"status" => "stub",
							"contents" => json_encode(array())
						),

				);
				$time = ConvertTimeStamp(time(), "FULL", "ru");
				$arParams = array(
					"PARAMS" => json_encode($arSendParams),
					"TYPE" => "POST",
					"DATE_INSERT" => $time,
					"DATE_LAST_TRY" => $time,
					//"URL" => "http://dev.leadhit.ru:6789/stat/cart",
					"URL" => "http://dev.leadhit.ru/stat/cart",
					"SUCCESS_EXEC" => "N"
				);
			}
		}
		
		return $arParams;
	}

	function GetSendToLeadFormParams($ID, $arOrder) {
		$arParams = false;$arSendParams = array();
		if(empty($arOrder)){
			return false;
		}

		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());

		$arLHTM_R = explode("|", $_COOKIE["_lhtm_r"]);

		$dbOrderProps = CSaleOrderPropsValue::GetList(
	        array("SORT" => "ASC"),
	        array("ORDER_ID" => $ID)
	    );
	    while ($arOrderProps = $dbOrderProps->GetNext()):
	        $arOrderPropList[ $arOrderProps["CODE"] ] = $arOrderProps;
	    endwhile;

		$arSendParams = array(
			"action" => "lh_bitrix_cart",
			"url" => $_SERVER["HTTP_REFERER"],
			"uid" => $_COOKIE["_lhtm_u"],
			"vid" => $arLHTM_R["1"],
			"ref" => $_SERVER["HTTP_REFERER"],
			"clid" => CLeadhitUtils::getCLID(),
			"fuser_hash" => CLeadhitUtils::PrepareParam($arHash["HASH"]),
			"f_name" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::GetConvertedString( $GLOBALS['USER']->GetFullName()) : "''"),
			"f_email" => ($GLOBALS['USER']->IsAuthorized() > 0 ? CLeadhitUtils::GetConvertedString( $GLOBALS['USER']->GetEmail()) : "''"),
			"f_order_id" => $arOrder["ID"],
			"f_phone" => $arOrderPropList["PHONE"]["VALUE"],
			"f_address" => trim(CLeadhitUtils::GetConvertedString($arOrderPropList["CITY"]["VALUE"]." ".$arOrderPropList["LOCATION"]["VALUE"]." ".$arOrderPropList["ADDRESS"]["VALUE"])),
			"f_cart_sum" => $arOrder["PRICE"]
		);
		$time = ConvertTimeStamp(time(), "FULL", "ru");
		$arParams = array(
			"PARAMS" => json_encode($arSendParams),
			"TYPE" => "GET",
			"DATE_INSERT" => $time,
			"DATE_LAST_TRY" => $time,
			"URL" => "http://dev.leadhit.ru/stat/lead_form",
			"SUCCESS_EXEC" => "N"
		);

		return $arParams;
	}

	function OnBasketAdd($ID, $arFields) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams(0, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function OnBasketUpdate($ID, $arFields) {
		/*echo "ID = ".$ID."@";
		echo "<pre>";print_r($arFields);echo "</pre>";die;*/
	}

	function OnBasketDelete($ID) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams(0, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function OnSaleComponentOrderOneStepFinal($ID, $arOrder, $Params) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams($ID, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
		if($arParams = self::GetSendToLeadFormParams($ID, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function OnSaleComponentOrderComplete($ID, $arOrder, $Params) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams($ID, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
		if($arParams = self::GetSendToLeadFormParams($ID, $arOrder)) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function OnSaleCancelOrder($orderId, $value, $description) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams($orderId, array())) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function OnSaleStatusOrder($ID, $val) {
		$arHash = CLeadhitBasketUserHash::getBasketUserHash(CSaleBasket::GetBasketUserID());
		if(!is_array($arHash) || !$arHash["HASH"]){
			CLeadhitBasketUserHash::addBasketUserHash();
		}
		if($arParams = self::GetSendToCartParams($ID, array())) {
			CLeadhitRequest::Add($arParams);
		}
	}

	function MyOnBeforePrologHandler()
	{
	    global $USER;
	    if(/*$USER->IsAdmin() && */isset($_GET["fuser_id"]) && isset($_GET["init_basket"]) && isset($_GET["hash"]) && CModule::IncludeModule("sale") && CModule::IncludeModule("catalog")){

	    	if(CLeadhitBasketUserHash::checkBasketUserHash(intval($_GET["fuser_id"]),strval($_GET["hash"]))){
	    		$arCurBasket = CLeadhitBasket::getUserCartBasket();
		    	//delete if not emtpy
		    	if(!empty($arCurBasket)){
		    		foreach ($arCurBasket as $key => $arItem) {
		    			CSaleBasket::Delete($arItem["basket_id"]);
		    		}
		    	}

		    	$dbBasketItems = CSaleBasket::GetList(
			    array(
			            "NAME" => "ASC",
			            "ID" => "ASC"
			        ),
			    array(
			            "FUSER_ID" => intval($_GET["fuser_id"]),
			            //"LID" => SITE_ID,
			            "ORDER_ID" => "NULL"
			        ),
			    false,
			    false,
			    array("ID",
			          "CALLBACK_FUNC", 
			          "MODULE", 
			          "PRODUCT_ID", 
			          "QUANTITY", 
			          "DELAY", 
			          "CAN_BUY", 
			          "PRICE", 
			          "WEIGHT")
			    );

				while ($arItems = $dbBasketItems->Fetch())
				{
					if (strlen($arItems["CALLBACK_FUNC"]) > 0)
				    {
				        CSaleBasket::UpdatePrice($arItems["ID"], 
				                                 $arItems["CALLBACK_FUNC"], 
				                                 $arItems["MODULE"], 
				                                 $arItems["PRODUCT_ID"], 
				                                 $arItems["QUANTITY"]);
				        $arItems = CSaleBasket::GetByID($arItems["ID"]);
				    }

				    if($arItems["QUANTITY"] > 1){
				    	for ($i = 1; $i <= $arItems["QUANTITY"]; $i++) {
				    		Add2BasketByProductID($arItems["PRODUCT_ID"]);
				    	}
				    }else{
				    	Add2BasketByProductID($arItems["PRODUCT_ID"]);
				    }
				}
	    	}
		}
	}
}
?>
