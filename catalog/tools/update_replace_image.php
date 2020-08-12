<?php
/**
 * Варианты замены, добавления изображений:
 * 1. Заменить.
 * Загружаем изображение, в наименование указываем ШтрихКод_Ключ, так же правильным будет считаться ШтрихКод без ключа, по умолчанию будет 0 (первый элемент)
 * Если в поле одно изображение и загруженно на сервер, одно - произайдет замена файла. при условии, если ключ равен 0 или отсутствует.
 * Если в поле доступно более одного изоражения, можно заменить при помощи Ключа.
 * Пример: 4602242001379_2 - Заменится картинка по счету 3, если отсчет ведется от 0. Массив данных от 0.
 * 2. Добавить.
 * ШтрихКод_Ключ - указывать ключ больше чем имеется в поле.
 * Пример: В поле одно изображение, если загрузить 4602242001379_1, ключ будет не найден - изображение автоматически добавиться. Так как одно изображение в поле лежить с ключем 0.
 */

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);
define("NEED_AUTH",false);
define("SITE_ID","s1");

$_REQUEST["SITE_ID"] = $_GET["SITE_ID"] = "s1";

if(!isset($_SERVER['DOCUMENT_ROOT']))
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$sDirImages     = $_SERVER['DOCUMENT_ROOT'] . '/upload/update_image';
$sSeparator     = "_";
$sSeparatorExep = ".";
$arFiles        = [];
$arBarCodes     = [];
// Ключ с которого начнется отсчет
$iFirstKey      = 0;
$iBlockCatalog 	= 1;

foreach(glob($sDirImages . '/*') as $file) {
    if (is_file($file)) {
        list($sBarCode, $sExepsion) = explode($sSeparatorExep, basename($file));
        $iFind = strripos($sBarCode, $sSeparator);
        if($iFind === false)
        {
            $arFiles[ $sBarCode ][] = $file;
        }
        else
        {
            list($sBarCode, $sPosition) = explode($sSeparator, $sBarCode);
            $arFiles[ $sBarCode ][ $sPosition ] = $file;
        }
        $arBarCodes[] = trim($sBarCode);
    }
}

if(!empty($arBarCodes))
{
    $arBarCodes = array_unique($arBarCodes);

    CIBlock::clearIblockTagCache( $iBlockCatalog );

    $rsElements = \CIBlockElement::GetList(
        ['SORT' => 'ASC'],
        [
            "IBLOCK_ID"                 => $iBlockCatalog,
            "ACTIVE"                    => "Y",
            "PROPERTY_CML2_BAR_CODE"    => $arBarCodes,
        ],
        false,
        ['nTopCount' => 10000],
        ["ID", "IBLOCK_ID", "NAME"]
    );

    while($arElement = $rsElements->GetNextElement(0,0))
    {
        $arFields = $arElement->GetFields();
        $arProps = $arElement->GetProperties();

        if(!empty( $arFiles[ $arProps['CML2_BAR_CODE']['VALUE'] ] ))
        {
            if( !empty( $arFiles[ $arProps['CML2_BAR_CODE']['VALUE'] ] ) )
            {
                foreach ( $arFiles[ $arProps['CML2_BAR_CODE']['VALUE'] ] as $cImage => $sUrlImage)
                {
                    $iKey = $cImage + $iFirstKey;
                    $arFileData = CFile::MakeFileArray( $sUrlImage );
                    if(!empty($arFileData)) {
                        if( !empty( $arProps['MORE_PHOTO']['PROPERTY_VALUE_ID'][ $iKey ] ) )
                        {
                            $iFileID = (int) $arProps['MORE_PHOTO']['PROPERTY_VALUE_ID'][ $iKey ];
                            CIBlockElement::SetPropertyValueCode($arFields['ID'], "MORE_PHOTO", [$iFileID => ["VALUE"=>$arFileData]] );
                        }
                        else
                        {
                            CIBlockElement::SetPropertyValueCode($arFields['ID'], "MORE_PHOTO", [$iKey => ["VALUE"=>$arFileData]] );
                        }
                        unlink( $sUrlImage );
                    }
                }
            }
        }
    }
}