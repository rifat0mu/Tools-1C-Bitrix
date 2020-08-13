<?
namespace Fitomarket\MeasurementProtocol;

use Bitrix\Highloadblock\HighloadBlockTable;
use Fitomarket\Mindbox\QueueBase;

class MeasurementProtocolQueue extends QueueBase
{
    /**
     * @var string
     */
    public static $hlIBlockName = "MeasurementProtocolQueue";

    /**
     * @param int $iOrderID
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function queueTransactionProcessed($iOrderID = 0)
    {
        if(!$iOrderID)
        {
            return false;
        }

        $obItem = HighloadBlockTable::getList(
            ["filter" => ["TABLE_NAME" => MeasurementProtocol::getTableName()]]
        );
        if($arHLBlock = $obItem->Fetch())
        {
            $obEntity = HighloadBlockTable::compileEntity($arHLBlock);
            $obIBlock = $obEntity->getDataClass();
            $hlBlock = new $obIBlock;

            $arHLBlockParams = [
                'filter'    => [
                    'UF_ORDER_ID'   => $iOrderID,
                ],
                'select'    => ['UF_TRACKING_ID', 'UF_CLIENT_ID'],
                'limit'     => 1,
            ];

            if( $obItems = $hlBlock->getList($arHLBlockParams)->fetch() )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $arItem
     * @return array
     */
    public function processItem($arItem)
    {
        $arItem["UF_PARAMS"] = unserialize(base64_decode($arItem["UF_PARAMS"]));
        $bAddNextExec = false;
        $sError = "";

        $obMeasurementProtocol = MeasurementProtocol::getInstance();

        switch($arItem["UF_OPERATION"])
        {
            case $obMeasurementProtocol->getOperationTransaction():
                $obMeasurementProtocol->sendTransaction( $arItem );
                break;
        }
        return [
            "ERROR"         => $sError,
            "ADD_NEXT_EXEC" => $bAddNextExec
        ];
    }
}
