<?

namespace Fitomarket\Mindbox;


use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Type\DateTime;
use Fitomarket\Helpers\Main\OptionHelper;
use Fitomarket\Traits\SingletonHighloadBlock;

class QueueBase
{
	use SingletonHighloadBlock;
	/**
	 * @var HighloadBlockTable $hlIBlockName
	 */
	public static $hlIBlockName;
	public static $xmlRegex = "#[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-#i";

	public function process()
	{
		if(!OptionHelper::get("use_mindbox")) return false;

		$arFilter[] = [
			"LOGIC" => "OR",
			[
				"UF_PROCESSED" => false,
				"UF_NEXT_EXEC" => false
			],
			[
				"UF_PROCESSED"   => 1,
				"!UF_NEXT_EXEC"  => false,
				"<=UF_NEXT_EXEC" => date("d.m.Y H:i:s")
			],
		];
		$obItems = self::$hlBlock->getList(["filter" => $arFilter, "order" => ["UF_DATE" => "ASC"]]);
		$iTime = microtime(1);

		while($arItem = $obItems->fetch())
		{
			$arUpdate = [
				"UF_NEXT_EXEC"      => false,
				"UF_PROCESSED"      => 1,
				"UF_PROCESS_RESULT" => "success"
			];
			try
			{
				$arResult = $this->processItem($arItem);
			}
			catch(\Exception $e)
			{
				$arResult["ERROR"] = $e->getMessage();
				$arResult["ADD_NEXT_EXEC"] = 1;
			}

			if($arResult["ADD_NEXT_EXEC"])
			{
				$obDate = new \DateTime("now");
				$arUpdate["UF_NEXT_EXEC"] = $obDate->modify("+5 min")->format("d.m.Y H:i:s");
			}

			if($arResult["ERROR"])
			{
				$arUpdate["UF_PROCESS_RESULT"] = "error";
				$arUpdate["UF_ERROR"] = $arResult["ERROR"];
			}
			$result = self::$hlBlock->update($arItem["ID"], $arUpdate);

			if(microtime(1) - $iTime > 15)
			{
				return true;
			}

		}
		return true;
	}

	public function add($arFields)
	{
		try
		{

			if(!isset($arFields["UF_DATE"])) $arFields["UF_DATE"] = new DateTime();
			if(is_array($arFields["UF_PARAMS"]))
			{
				if($sUID = Core::getInstance()->getDeviceUUID())
				{
					if(preg_match(self::$xmlRegex, $sUID))
					{
						$arFields["UF_PARAMS"]["DEVICE_UUID"] = $sUID;
					}
				}
				$arFields["UF_PARAMS"] = base64_encode(serialize($arFields["UF_PARAMS"]));
			}

			if(self::$hlBlock)
			{
				$result = self::$hlBlock->add($arFields);
				return $result->getId();
			}
		}
		catch(\Exception $e)
		{
			self::setError("Exception: ".$e->getMessage().". Function: ".__NAMESPACE__.__CLASS__."::".__FUNCTION__);
		}
		return false;
	}

	protected function __construct()
	{
		$arFilter = [];
		if($this::$hlIBlockName)
		{
			$arFilter["NAME"]=$this::$hlIBlockName;
		}
		if(!empty($arFilter))
		{
			$obHLBlocks = HighloadBlockTable::getList(["filter"=>$arFilter]);
			if($arItem = $obHLBlocks->fetch())
			{
				$entity = HighloadBlockTable::compileEntity($arItem);
				$entityClass = $entity->getDataClass();
				self::$hlBlock = new $entityClass();
				return;
			}
		}

	}
}
