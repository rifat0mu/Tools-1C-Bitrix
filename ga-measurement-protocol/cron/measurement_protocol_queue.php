<?php
define("NEED_AUTH", false);
define("SITE_ID", "s1");
define("NOT_CHECK_PERMISSIONS", true);
//define("LANG","ru");
$_REQUEST["SITE_ID"] = $_GET["SITE_ID"] = "s1";

$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../public_html');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");


$obQueue = \Fitomarket\MeasurementProtocol\MeasurementProtocolQueue::getInstance();
$obQueue->process();