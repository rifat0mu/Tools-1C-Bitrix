<?php

use Bitrix\Catalog;
use Projects\Finders\IBlockFinder;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$sData = file_get_contents("php://input");

/*
$sData = '{ "warehouseId": 49817, "skus": [ "4602242006411" ] }';
*/

$arRequest = [];
if ($sData) {
    $arRequest = json_decode($sData, true);
}

$arResult = ['skus' => []];

/** @var $token */
$beruToken = "";
$authToken = trim($_REQUEST['token']);

$arJSON = ["errors" => [], "response" => []];
if ($authToken == $beruToken) {
    $arJSON['response'] = "success";
}

if(!empty($arRequest['skus']))
{
    $arItems = $arRequest['skus'];

    $arSelect = ["ID", "IBLOCK_ID", "XML_ID", "NAME", "PROPERTY_CML2_BAR_CODE"];
    $arFilter = [
        'IBLOCK_ID'                 => IBlockFinder::catalog(),
        "ACTIVE"                    => "Y",
        "CHECK_PERMISSIONS"         => "Y",
        "PROPERTY_CML2_BAR_CODE"    => $arItems,
    ];

    $arElementsID = [];
    $arElements = [];

    $rsElements = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
    while($arElement = $rsElements->Fetch())
    {
        $arElementsID[] = $arElement['ID'];
        $arElements[ $arElement['ID'] ] = $arElement;
    }

    if(!empty($arElements))
    {
        $arCCatalogSelect = ["ID", "QUANTITY"];
        $arCCatalogFilter = [
            "=ID" => $arElementsID,
        ];

        $rsProducts = CCatalogProduct::GetList([], $arCCatalogFilter, false, false, $arCCatalogSelect);
        while ( $arProduct = $rsProducts->Fetch() )
        {
            $arItem = [
                "sku"           => $arElements[ $arProduct['ID'] ]['PROPERTY_CML2_BAR_CODE_VALUE'],
                "warehouseId"   => $arRequest['warehouseId'],
            ];

            $arItem["items"][] = [
                "type"      => "FIT",
                "count"     => $arProduct['QUANTITY'],
                "updatedAt" => "2020-08-04T09:01:18+03:00",
            ];

            $arResult['skus'][] = $arItem;
        }
    }
}

echo json_encode($arResult);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");