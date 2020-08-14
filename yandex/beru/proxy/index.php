<?php
 
class Proxy
{
	/**
	 * Перенаправляем запрос на скрипт проверки наличия товаров
	 */
	public static $queryCart 		= "";
	/**
	 * Перенаправляем запрос на скрипт получения остатков по товарам
	 */
    public static $queryStocks 		= "";
	/**
	 * 
	 */
    public static $queryOrderAccept = "";
    public static $queryOrderStatus = "";

    public static $token 			= "";
	
	/**
	 * Если сервер интернет магазина, имеет базовую авторизацию
	 */
	public static $BaseLogin 		= "";
	public static $BasePassword 		= "";

    public static function auth()
    {
        if(!empty($_REQUEST['auth-token']))
        {
            $authToken = trim($_REQUEST['auth-token']);
        }

        if( $authToken == self::$token )
        {
            return true;
        }

        return false;
    }

    public static function getQuery()
    {
        $query = "";
		$method = "";
        $arUrl = parse_url($_SERVER['REQUEST_URI']);
        $arPath = explode("/", $arUrl['path']);
        $arPath = array_filter($arPath, function($element) {
            return !empty($element);
        });

		if(!empty($arPath[2]))
		{
			$method = $arPath[2];
		}
        $sCode = array_shift($arPath);

        switch ($sCode)
        {
            case 'cart':
                $query = self::$queryCart;
				
                break;
			case 'stocks':
                $query = self::$queryStocks;
				
                break;
            case 'order':
                if($method == 'accept')
                {
                    $query = self::$queryOrderAccept;
                }
                if($method == 'status')
                {
                    $query = self::$queryOrderStatus;
                }

                break;
        }

        return $query;
    }

    public static function execute()
    {
        if(!self::auth())
        {
            header('HTTP/1.1 403 Forbidden');
            return false;
        }

        $query = self::getQuery();
		
        if( !empty($query) ) {
			/**
			 * В тело запроса, можно передать токен, для дополнительной обработки
			 */
            //$arParams = ['auth-token' => self::$token];
			$arParams = [];
			
			$sData = file_get_contents('php://input');
			$arRequest = json_decode($sData, true);
			if(!empty($arRequest))
			{
				$arParams = array_merge($arParams, $arRequest);
			}
			
			$sJson = json_encode ($arParams, JSON_UNESCAPED_UNICODE);

            if($curl = curl_init())
			{
				$arExHeader = ['Content-Type', 'Content-Length', 'Host'];
								
				$arHeaders = [
					'Content-Type: application/json;charset=utf-8',
					'Content-Length:' .  strlen($sJson),
				];
				
				$arRequestHeaders = apache_request_headers();
				if(!empty($arRequestHeaders))
				{
					foreach($arRequestHeaders as $clef => $header)
					{
						if( !in_array($clef, $arExHeader) )
						{
							//$arHeaders[] = $clef . ": " . $header;
						}
					}
				}

				
				curl_setopt($curl, CURLOPT_URL, $query);

	            curl_setopt($curl, CURLOPT_HTTPHEADER, $arHeaders);
				
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $sJson); // http_build_query($arParams) http_build_query($array, '', '&')
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_TIMEOUT, 10);
				
				// Лог
				self::logs($query, $sJson);
				
				/**
				 * К примеру, запросы перенаправляющий на сервер проверки наличия и остатков по товарам, имеется базовая авторизация
				 */
				if( $query == self::$queryCart || $query == self::$queryStocks )
				{
					curl_setopt($curl, CURLOPT_USERPWD, self::$BaseLogin . ":" . self::$BasePassword);
				}
				
				$response = curl_exec($curl);
				curl_close($curl);
				
				
				/*
				if ($response !== false) {
					$response = json_decode($response, true);
				} 
				*/
				
				Header('Content-Type: application/json;charset=utf-8');
				Header('Content-Length:' .  strlen($response));
				
				echo $response;
			}    
        }
    }
	
	public static function logs($query, $data)
	{
		$sURL = $_SERVER["DOCUMENT_ROOT"]."/logs/";

		$sFileName = $sURL."log_".date("Y-m-d").".log";
		
		file_put_contents($sFileName, date("d.m.Y H:i:s") . " | Query: {$query} " . var_export($data,true) . PHP_EOL, FILE_APPEND );
	}
}

Proxy::execute();
?>