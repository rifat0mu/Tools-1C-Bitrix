## GA Measurement protocol

### Использовать
Order Tracking with simple E-commerce:
```php
use TheIconic\Tracking\GoogleAnalytics\Analytics;

$analytics = new Analytics();

// Build the order data programmatically, including each order product in the payload
// Take notice, if you want GA reports to tie this event with previous user actions
// you must get and set the same ClientId from the GA Cookie
// First, general and required hit data
$analytics->setProtocolVersion('1')
    ->setTrackingId('UA-26293624-12')
    ->setClientId('2133506694.1448249699');

// To report an order we need to make single hit of type 'transaction' and a hit of
// type 'item' for every item purchased. Just like analytics.js would do when
// tracking e-commerce from JavaScript
$analytics->setTransactionId(1667) // transaction id. required
    ->setRevenue(65.00)
    ->setShipping(5.00)
    ->setTax(10.83)
    // make the 'transaction' hit
    ->sendTransaction();

foreach ($cartProducts as $cartProduct) {
    $response = $analytics->setTransactionId(1667) // transaction id. required, same value as above
        ->setItemName($cartProduct->name) // required
        ->setItemCode($cartProduct->code) // SKU or id
        ->setItemCategory($cartProduct->category) // item variation: category, size, color etc.
        ->setItemPrice($cartProduct->price)
        ->setItemQuantity($cartProduct->qty)
        // make the 'item' hit
        ->sendItem();
}
```

Использование на сайте, после оформления заказа:
```php
if(isset( $_COOKIE['_ga'] ))
{
    $arClientId  = explode(".", $_COOKIE['_ga']);
    $sClientId = $arClientId[2].$arClientId[3];
}
else
{
    $sClientId = rand(1000000000, 2147483647) . '.' . time();
}

$analytics = new Analytics();
$analytics->setProtocolVersion('1')
    ->setTrackingId('UA-106414849-1')
    ->setClientId( $sClientId );

$analytics->setTransactionId( $iOrderID ) // transaction id. required
    ->setRevenue($arResult["SHIPMENTS"]['fitomarket']["ITEMS_PRICE"])
    ->setShipping($arResult["SHIPMENTS"]['fitomarket']["PRICE_DELIVERY"])
    // ->setDebug(true)
    // ->setTax(10.83) // налогов нет
    // make the 'transaction' hit
    ->sendTransaction();

if(!empty( $arResult["TOTAL_DATA"]["DATA_LAYER_BASKET"] ))
{
    foreach ($arResult["TOTAL_DATA"]["DATA_LAYER_BASKET"] as $arCartProduct) {
        $response = $analytics->setTransactionId( $iOrderID )
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

$debugResponse = $response->getDebugResponse();
//print_r($debugResponse);
```
