# Маркетплейс Беру

Интеграция компании с агрегатором Беру.

## Описание

Запросы выполняются POST

```php
POST /stocks
```

> Внимание. Запрос выполняется маркетплейсом Беру и поддерживает обмен данными только в формате JSON.

URL ресурса:
```php
https://<URL_запроса>/stocks
```
Таймаут на получение ответа: 5,5 секунд.

### Передаваемые магазину данные
Структура данных в теле запроса приведена ниже. Порядок следования параметров не гарантируется.
```json
{
  "warehouseId": 2,
  "skus":
  [
    "A200.190",
    "A287.14"
  ]
}
```

Описание параметров:

1. warehouseId - Идентификатор склада.
Тип: Int64	
В ответе магазин должен указать тот же идентификатор.

2. skus	- Список ваших SKU товаров.
Тип: String[]	

### Ответные данные от магазина
В ответе магазин должен передать актуальные данные для переданных товаров.
Структура ответных данных:
```json
HTTP/1.1 200 OK
...

{
  "skus":
  [
    {
      "sku": "A200.190",
      "warehouseId": 2,
      "items":
      [
        {
          "type": "FIT",
          "count": 15,
          "updatedAt": "2019-09-09T13:01:18+03:00"
        }
      ]
    },
    {
      "sku": "A287.14",
      "warehouseId": 2,
      "items":
      [
        {
          "type": "FIT",
          "count": 7,
          "updatedAt": "2019-09-09T12:44:08+03:00"
        }
      ]
    }
  ]
}
```


## Модель «Витрина + доставка»

Ссылка на документацию
https://yandex.ru/support/marketplace/delivered-by-beru/about-delivered-by-beru.html

Отладка c помощью тестовых заказов
https://yandex.ru/support/marketplace/delivered-by-beru/test-orders.html

Запрос информации о товарах
https://yandex.ru/dev/market/partner-marketplace-cd/doc/dg/reference/post-cart-docpage/

Передача заказа и запрос на принятие заказа
https://yandex.ru/dev/market/partner-marketplace-cd/doc/dg/reference/post-order-accept-docpage/

Уведомление о смене статуса заказа
https://yandex.ru/dev/market/partner-marketplace-cd/doc/dg/reference/post-order-status-docpage/

Запрос информации об остатках
https://yandex.ru/dev/market/partner-marketplace-cd/doc/dg/reference/post-stocks-docpage/