<?
/**
 * Добавление пользователя
 */
$obUser = new CUser;

$sPassword = randString(6);
$arUserFields = Array(
	"LAST_NAME"         => $arFields["NAME"],
	"EMAIL"             => $arFields["EMAIL"],
	"LOGIN"             => $arFields["EMAIL"],
	"ACTIVE"            => "Y",
	"PASSWORD"          => $sPassword,
	"CONFIRM_PASSWORD"  => $sPassword,
);

$iUserID = $obUser->Add($arUserFields);
if (intval($iUserID) <= 0)
	$sError = $obUser->LAST_ERROR;
?>