<?
/**
 * Получаем Json данные, обрабатываем, передаем дальше в json формате
 */

// Отарвляем данные по адресу
$query 		= "https://bitus.pro";

// Логин и пароль базовой авторизации
$Login		= "";
$Password	= "";

// параметры 
$arParams 	= [];
			
$sData = file_get_contents('php://input');
$arRequest = json_decode($sData, true);
if(!empty($arRequest))
{
	$arParams = array_merge($arParams, $arRequest);
}

$sJson = json_encode ($arParams, JSON_UNESCAPED_UNICODE);

if($curl = curl_init())
{
	$arHeaders = [
		'Content-Type: application/json;charset=utf-8',
		'Content-Length:' .  strlen($sJson),
	];
	
	curl_setopt($curl, CURLOPT_URL, $query);

	curl_setopt($curl, CURLOPT_HTTPHEADER, $arHeaders);
	
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $sJson); // http_build_query($arParams) http_build_query($array, '', '&')
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	
	
	// Если есть базовая авторизация
	//curl_setopt($curl, CURLOPT_USERPWD, $Login . ":" . $Password);
	
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
?>