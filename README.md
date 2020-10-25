# Платёжный модуль BeGateway для Billmanager

Этот платёжный модуль для Billmanager позволит вам принимать платежи через провайдера платежей, работающего на платформе beGateway.

## Требования

  * Установленный на сервере PHP 5.4+
  * Поддержка PHP модулей: `php5-curl`,`php5-json` и `php5-mysql`

Пример установки PHP на Debian Jessie

```
apt-get -y update
apt-get -y --no-install-recommends install php5-cli php5-common php5-curl php5-json php5-mysql
```

## Установка

1. Скачайте [последнюю версию платёжного модуля](https://github.com/begateway/billmanager/raw/master/billmanager_begateway.tar.gz)
2. Распакуйте архив на сервере с установленным Billmanager
3. Выполните команду `make install`

Зайдите как администратор в Billmanager и в разделе _Провайдер → Методы оплаты_ добавьте модуль оплаты __BeGateway__.

Заполните ключевые поля модуля:

![Настройка модуля](https://github.com/begateway/billmanager/raw/master/img/install_ru.png)

Модуль готов к работе.

Перед запуском модуля в боевой режим, рекомендуем провести тестовый платеж, чтобы убедиться в корректности работы системы. Для этого просто в настройках модуля в биллинге выберите тестовый режим работы и укажите тестовые или ваши данные магазина.

## Тестовые данные

Если вам еще не известны ваши настройки, то вы можете настроить модуль, используя демо-данные:

  * ID магазина ```361```
  * Секретный ключ магазина ```b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d```
  * Домен страницы оплаты ```checkout.begateway.com```

### Тестовые карты

  * Карта ```4200000000000000``` для успешной оплаты
  * Карта ```4005550000000019``` для неуспешной оплаты
  * Имя на карте ```JOHN DOE```
  * Срок действия карты ```01/30```
  * CVC ```123```

# BeGateway Payment Module for Billmanager

This is a payment module for Billmanager, that gives you the ability to process payments through payment service providers running on beGateway platform.

## Requirements

  * Installed PHP 5.4+
  * Installed PHP modules: `php5-curl`,`php5-json` и `php5-mysql`

PHP installation commands for Debian Jessie:

```
apt-get -y update
apt-get -y --no-install-recommends install php5-cli php5-common php5-curl php5-json php5-mysql
```

## Installation

1. Download [payment module](https://github.com/begateway/billmanager/raw/master/billmanager_begateway.tar.gz)
2. Unpack the payment module archive to your server with Billmanager installed
3. Execute on your server the command `make install`

Login as an administrator to Billmanager and in the section _Provider → Payment methods_ add the __BeGateway__ payment module.

Populate the module settings:

![Настройка модуля](https://github.com/begateway/billmanager/raw/master/img/install_en.png)

The module is ready to work.

Make sure the module works properly before to launch it in production mode. Just enable the module test mode and setup parameters either the test shop below or your own shop.

## Test data

If you setup the module with default values, you can use the test data to make a test payment:

  * Shop ID ```361```
  * Secret key ```b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d```
  * Payment page domain ```checkout.begateway.com```

### Test card details

  * Card ```4200000000000000``` to get successful payment
  * Card ```4005550000000019``` to get failed payment
  * Card name ```JOHN DOE```
  * Card expiry date ```01/30```
  * CVC ```123```
