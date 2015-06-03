<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");

$MODULE_ID = "leadhit.leadhit";
CModule::IncludeModule($MODULE_ID);

if(!$USER->IsAdmin())
    $APPLICATION->AuthForm();

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("leadhit.leadhit_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if ($REQUEST_METHOD == "POST"){
    if ($_REQUEST["CLID"]) COption::SetOptionString($MODULE_ID, "CLID", trim($_REQUEST["CLID"]));
    if ($_REQUEST["INCLUDE_SCRIPT"]){
        COption::SetOptionString($MODULE_ID, "INCLUDE_SCRIPT", trim($_REQUEST["INCLUDE_SCRIPT"]));
    }else{
        COption::SetOptionString($MODULE_ID, "INCLUDE_SCRIPT", "off");
    };
}

$aTabs = array(
    array(
        "DIV"   => "TAB_TEMPLATE",
        "TAB"   => GetMessage("leadhit.leadhit_TAB"),
        "ICON"  => "main_user_edit",
        "TITLE" => GetMessage("leadhit.leadhit_TITLE"),
    ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<form
    action="<? echo $APPLICATION->GetCurPage(); ?>?mid=<?=htmlspecialcharsbx($MODULE_ID)?>&lang=<?=LANGUAGE_ID?>"
    method="POST" id="leadhit_leadhit">
<?=bitrix_sessid_post()?>
<?$tabControl->BeginNextTab();?>
<tr>
    <td>
        <h3><?=GetMessage("leadhit.leadhit_CLID")?></h3>
        <textarea style="width: 50%;height: 100px" name="CLID" id="CLID"><?=COption::GetOptionString($MODULE_ID, "CLID")?></textarea>

        <h3><?=GetMessage("leadhit.leadhit_ADD_ALL_PAGES_SCRIPT_CHECKBOX")?></h3>
        <input type="checkbox" name="INCLUDE_SCRIPT"<?=(COption::GetOptionString($MODULE_ID, "INCLUDE_SCRIPT") == "on" ? " checked='checked'" : "" )?> />
    </td>
</tr>
<?$tabControl->Buttons();?>
<input type="submit" class="adm-btn-save" name="save" value="<?=GetMessage("leadhit.leadhit_SAVE_BUTTON")?>">
<?$tabControl->End();?>
</form>