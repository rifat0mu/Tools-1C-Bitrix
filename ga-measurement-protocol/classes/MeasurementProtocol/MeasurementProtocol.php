<?php
namespace Fitomarket\MeasurementProtocol;

use TheIconic\Tracking\GoogleAnalytics\Analytics;

class MeasurementProtocol
{
    /** @var static|null Объект класса */
    protected static $instance = null;
    /**
     * @var string
     */
    public static $hlIBlockTableName = "hl_measurement_protocol_queue";
    /**
     * @var string
     */
    protected static $sOperationTransaction = "sendTransaction";
    /**
     * @var string
     */
    protected static $sProtocolVersion = "1";

    /**
     * @return string
     */
    public static function getProtocolVersion()
    {
        return self::$sProtocolVersion;
    }

    /**
     * @return string
     */
    public static function getTableName()
    {
        return self::$hlIBlockTableName;
    }

    /**
     * @return string
     */
    public static function  getOperationTransaction()
    {
        return self::$sOperationTransaction;
    }

    public static function getClientID()
    {
        $sClientId = rand(1000000000, 2147483647) . '.' . time();

        if(isset( $_COOKIE['_ga'] ))
        {
            $arClientId  = explode(".", $_COOKIE['_ga']);
            $sClientId = $arClientId[2].".".$arClientId[3];
        }

        return $sClientId;
    }

    public static function sendTransaction( $arItem )
    {
        $sProtocolVersion = self::getProtocolVersion();

        if(!empty($arItem['UF_TRACKING_ID']) && !empty($arItem['UF_CLIENT_ID']) && !empty($arItem['UF_ORDER_ID']))
        {
            $arParams = $arItem["UF_PARAMS"];

            $analytics = new Analytics();
            $analytics->setProtocolVersion($sProtocolVersion)
                ->setTrackingId($arItem['UF_TRACKING_ID'])
                ->setClientId($arItem['UF_CLIENT_ID'] );

            if(!empty( $arParams["PRICE_ITEMS"] ) && !empty($arParams["PRICE_DELIVERY"]))
            {
                $analytics->setTransactionId( $arItem['UF_ORDER_ID'] ) // transaction id. required
                    ->setRevenue($arParams["PRICE_ITEMS"])
                    ->setShipping($arParams["PRICE_DELIVERY"])
                    // ->setTax(10.83) // налогов нет
                    // make the 'transaction' hit
                    ->sendTransaction();

            }

            if(!empty( $arParams["BASKET"] ))
            {
                foreach ($arParams["BASKET"] as $arCartProduct) {
                    $response = $analytics->setTransactionId( $arItem['UF_ORDER_ID'] )
                        ->setItemName( $arCartProduct['NAME'] )
                        ->setItemCode( $arCartProduct['ID'] )
                        ->setItemCategory( $arCartProduct['CATEGORY'] )
                        ->setItemPrice( $arCartProduct['PRICE'] )
                        ->setItemQuantity( $arCartProduct['QUANTITY'] )
                        // ->setDebug(true)
                        // make the 'item' hit
                        ->sendItem();
                }
            }
        }
    }

    /**
     * Создает и возвращает объект класса
     * @param array ...$argument
     * @return static
     */
    public static function getInstance(...$argument)
    {
        if(static::$instance === null)
        {
            static::$instance = new static(...$argument);
        }

        return static::$instance;
    }

    public static function reset()
    {
        static::$instance = null;
    }

    protected function __construct(...$argument)
    {
    }

    protected function __wakeup()
    {
    }

    protected function __clone()
    {
    }
}
