<?
/**
* 
*/
class CLeadhitBasket
{

	public function getUserOrderBasket($ID) {
		$arBasketItemList = array();
		$dbBasketItems = CSaleBasket::GetList(
	    array(
	            "NAME" => "ASC",
	            "ID" => "ASC"
	        ),
	    array(
	            //"FUSER_ID" => CSaleBasket::GetBasketUserID(),
	            //"LID" => SITE_ID,
	            "ORDER_ID" => $ID
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

		    $arProducts[] = $arItems["PRODUCT_ID"];

		    $arBasketItemList[] = $arItems;
		}


		if(!empty($arProducts)){
			$arCatalogItems = self::getCatalogItemsList($arProducts);
			foreach ($arBasketItemList as $key => $arItem) {
				$arBasketItems[] = array(
					"fuser_id" => CLeadhitUtils::PrepareParam(CSaleBasket::GetBasketUserID()),
					"item_id" => CLeadhitUtils::PrepareParam($arItem["PRODUCT_ID"]),
					"quantity" => CLeadhitUtils::PrepareParam($arItem["QUANTITY"]),
					"price" => CLeadhitUtils::PrepareParam($arItem["PRICE"]),
					"name" => "'".CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString( $arCatalogItems[ $arItem["PRODUCT_ID"] ]["NAME"]))."'",
					"url" => CLeadhitUtils::PrepareParam($arCatalogItems[ $arItem["PRODUCT_ID"] ]["DETAIL_PAGE_URL"])
				);
			}
		}
		return $arBasketItems;
	}

	public function getUserCartBasket() {
		$arBasketItemList = array();
		$dbBasketItems = CSaleBasket::GetList(
	    array(
	            "NAME" => "ASC",
	            "ID" => "ASC"
	        ),
	    array(
	            "FUSER_ID" => CSaleBasket::GetBasketUserID(),
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

		    $arProducts[] = $arItems["PRODUCT_ID"];

		    $arBasketItemList[] = $arItems;
		}

		if(!empty($arProducts)){
			$arCatalogItems = self::getCatalogItemsList($arProducts);

			foreach ($arBasketItemList as $key => $arItem) {
				$arBasketItems[] = array(
					"basket_id" => CLeadhitUtils::PrepareParam($arItem["ID"]),
					"fuser_id" => CLeadhitUtils::PrepareParam(CSaleBasket::GetBasketUserID()),
					"item_id" => CLeadhitUtils::PrepareParam($arItem["PRODUCT_ID"]),
					"quantity" => CLeadhitUtils::PrepareParam($arItem["QUANTITY"]),
					"price" => CLeadhitUtils::PrepareParam($arItem["PRICE"]),
					"name" => "'".CLeadhitUtils::PrepareParam(CLeadhitUtils::GetConvertedString( $arCatalogItems[ $arItem["PRODUCT_ID"] ]["NAME"]))."'",
					"url" => CLeadhitUtils::PrepareParam($arCatalogItems[ $arItem["PRODUCT_ID"] ]["DETAIL_PAGE_URL"])
				);
			}
		}
		return $arBasketItems;
	}
	
	public function getUserBasket($ID) {
		if(CModule::IncludeModule("sale")){
			if($ID > 0){
				$arBasketItems = self::getUserOrderBasket($ID);
			}else{
				$arBasketItems = self::getUserCartBasket();
			}
		}

		return $arBasketItems;
	}

	public function getCatalogItemsList($ID) {
		$arItems = array();
		if(CModule::IncludeModule("iblock")){
			$rsItems = CIBlockElement::GetList(array(),array("ID" => $ID), false, false);
			while($arItem = $rsItems -> GetNext()){
				$arItems[ $arItem["ID"] ] = $arItem;
			}
		}
		return $arItems;
	}
}
?>