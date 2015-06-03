<?
IncludeModuleLangFile(__FILE__);
Class leadhit_leadhit extends CModule
{
	const MODULE_ID = 'leadhit.leadhit';
	var $MODULE_ID = 'leadhit.leadhit'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("leadhit.leadhit_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("leadhit.leadhit_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("leadhit.leadhit_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("leadhit.leadhit_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $errors;

		RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBuildGlobalMenu');
		RegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CLeadhitLeadhit', 'ChangeBufferContent');
		RegisterModuleDependences('sale', 'OnBasketAdd', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketAdd');
		RegisterModuleDependences('sale', 'OnBasketUpdate', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketUpdate');
		RegisterModuleDependences('sale', 'OnBasketDelete', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketDelete');
		RegisterModuleDependences('sale', 'OnSaleComponentOrderComplete', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleComponentOrderComplete');
		RegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepFinal', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleComponentOrderOneStepFinal');
		RegisterModuleDependences('sale', 'OnSaleCancelOrder', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleCancelOrder');
		RegisterModuleDependences('sale', 'OnSaleStatusOrder', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleStatusOrder');
		RegisterModuleDependences('main', 'OnBeforeProlog', self::MODULE_ID, 'CLeadhitLeadhit', 'MyOnBeforePrologHandler');


		if (!$DB->Query("SELECT 'x' FROM leadhit_request", true)) $EMPTY = "Y"; else $EMPTY = "N";

		if ($EMPTY=="Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/leadhit.leadhit/install/db/mysql/install.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBuildGlobalMenu');
		UnRegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CLeadhitLeadhit', 'ChangeBufferContent');
		UnRegisterModuleDependences('sale', 'OnBasketAdd', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketAdd');
		UnRegisterModuleDependences('sale', 'OnBasketUpdate', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketUpdate');
		UnRegisterModuleDependences('sale', 'OnBasketDelete', self::MODULE_ID, 'CLeadhitLeadhit', 'OnBasketDelete');
		UnRegisterModuleDependences('sale', 'OnSaleComponentOrderComplete', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleComponentOrderComplete');
		UnRegisterModuleDependences('sale', 'OnSaleComponentOrderOneStepFinal', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleComponentOrderOneStepFinal');
		UnRegisterModuleDependences('sale', 'OnSaleCancelOrder', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleCancelOrder');
		UnRegisterModuleDependences('sale', 'OnSaleStatusOrder', self::MODULE_ID, 'CLeadhitLeadhit', 'OnSaleStatusOrder');
		UnRegisterModuleDependences('main', 'OnBeforeProlog', self::MODULE_ID, 'CLeadhitLeadhit', 'MyOnBeforePrologHandler');

		global $APPLICATION, $DB, $errors;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$errors = false;
			// delete whole base
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/leadhit.leadhit/install/db/mysql/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors));
				return false;
			}
		}

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
