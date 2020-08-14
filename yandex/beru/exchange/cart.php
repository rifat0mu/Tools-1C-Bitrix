<?php

use Bitrix\Catalog;
use Projects\Finders\IBlockFinder;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$sData = file_get_contents("php://input");

/*
$sData = '
{ "cart": { "currency": "RUR", "items": [ { "feedId": 782467, "offerId": "4612744980294", "offerName": "Мыло кусковое Jurassic SPA Каннабикум сандал+ментол+травы, 110 г", "subsidy": 0, "count": 1, "params": "Вес: 110 г, С дозатором: Нет, Запасной блок: Нет, Упаковка: коробка", "fulfilmentShopId": 633225, "sku": "100420298170", "shopSku": "4612744980294" } ], "delivery": { "region": { "id": 213, "name": "Москва", "type": "CITY", "parent": { "id": 1, "name": "Москва и Московская область", "type": "SUBJECT_FEDERATION", "parent": { "id": 3, "name": "Центральный федеральный округ", "type": "COUNTRY_DISTRICT", "parent": { "id": 225, "name": "Россия", "type": "COUNTRY" } } } } } } }
';
*/
$arRequest = [];
if ($sData) {
    $arRequest = json_decode($sData, true);
}

$arResult = ['cart' => ['items' => []]];

/** @var $token */
$beruToken = "";
$authToken = trim($_REQUEST['token']);

$arJSON = ["errors" => [], "response" => []];
if ($authToken == $beruToken) {
    $arJSON['response'] = "success";
}

if(isset($arRequest['cart']))
{
    if(!empty($arRequest['cart']['items']))
    {
        $arItems = $arRequest['cart']['items'];
        foreach ($arItems as $clef => $arItem)
        {
            $amount = 0;
            $productId = 0;

            $arSelect = ["ID", "IBLOCK_ID", "XML_ID", "NAME", "PROPERTY_CML2_BAR_CODE"];
            $arFilter = [
                'IBLOCK_ID'                 => IBlockFinder::catalog(),
                "ACTIVE"                    => "Y",
                "CHECK_PERMISSIONS"         => "Y",
                "PROPERTY_CML2_BAR_CODE"    => $arItem['offerId'],
            ];

            $arElementsID = [];
            $arElements = [];

            $rsElements = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            while($arElement = $rsElements->Fetch())
            {
                $productId = $arElement['ID'];
            }

            if( $productId )
            {
                $arProduct = CCatalogProduct::GetByID($productId);
                if(!empty($arProduct))
                {
                    if( $arItem['count'] <= $arProduct['QUANTITY'])
                    {
                        $amount = intval($arItem['count']);
                    }
                    elseif ( $arItem['count'] > $arProduct['QUANTITY'] && $arProduct['QUANTITY'] > 0 )
                    {
                        $amount = intval($arProduct['QUANTITY']);
                    }
                }
                $arItem['count'] = $amount;

                $arResult['cart']['items'][] = $arItem;
            }
        }
    }
}

echo json_encode($arResult);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");