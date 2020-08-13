<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Fitomarket\MeasurementProtocol;
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(false);


$iOrderID = IntVal($arRequest["ORDER_ID"]);

$obMeasurementProtocol = MeasurementProtocol\MeasurementProtocol::getInstance();
$obQueueInstance = MeasurementProtocol\MeasurementProtocolQueue::getInstance();
if(!$obQueueInstance->queueTransactionProcessed($iOrderID))
{
    $sClientId = $obMeasurementProtocol->getClientID();
    $arTransactionParams = [
        'PRICE_ITEMS'       => $arResult["SHIPMENTS"]['shopId']["ITEMS_PRICE"],
        'PRICE_DELIVERY'    => $arResult["SHIPMENTS"]['shopId']["PRICE_DELIVERY"],
        'BASKET'            => $arResult["TOTAL_DATA"]["DATA_LAYER_BASKET"],
    ];

    $arQueueParams = [
        "UF_TRACKING_ID"    => 'UA-106414849-1',
        'UF_CLIENT_ID'      => $sClientId,
        "UF_OPERATION"      => $obMeasurementProtocol->getOperationTransaction(),
        "UF_PARAMS"         => $arTransactionParams,
        "UF_ORDER_ID"       => $iOrderID,
    ];

    $iQueueId = $obQueueInstance->add($arQueueParams);
}