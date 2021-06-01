![Laravel Config Logo](https://s3-eu-west-1.amazonaws.com/media.tarfin.com/assets/logo-netgsm.svg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tarfin-labs/netgsm.svg?style=flat-square)](https://packagist.org/packages/tarfin-labs/netgsm)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/tarfin-labs/netgsm/tests?label=tests)
[![Quality Score](https://img.shields.io/scrutinizer/g/tarfin-labs/netgsm.svg?style=flat-square)](https://scrutinizer-ci.com/g/tarfin-labs/netgsm)
[![Total Downloads](https://img.shields.io/packagist/dt/tarfin-labs/netgsm.svg?style=flat-square)](https://packagist.org/packages/tarfin-labs/netgsm)

## Introduction
With this package, you can send easily [Netgsm notifications](https://www.netgsm.com.tr/dokuman/#api-dok%C3%BCman%C4%B1) with Laravel `^8.0`.
This package also provides some simple reporting.

> This package requires PHP 7.3 and Laravel `8.0` or higher.   
> For older versions of Laravel, please use version `^2.0.0` of this package!   

## Contents

- [Installation](#installation)
   - [Setting up the Netgsm service](#setting-up-the-Netgsm-service)
- [Usage](#usage)
    - [Service Methods](#service-methods)
    - [SMS Sending](#sms-sending)
       - [Available SMS Interfaces](#available-sms-interfaces)
   - [Reporting](#reporting)
       - [Available Reporting Interfaces](#available-report-interfaces)
   - [Account Balance](#account-balance)
        - [Remaining Balance](#remaining-balance)
        - [Remaining Package Credits](#remaining-package-credits)
   - [IYS Integration](#iys-integration)
        - [Add Address](#add-address)
        - [Search Address](#search-address)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

You can install the package via composer:

```bash
composer require tarfin-labs/netgsm
```
Next, you should publish the Laravel config migration file using the vendor:publish Artisan command.

```
php artisan vendor:publish --provider="TarfinLabs\Netgsm\NetgsmServiceProvider"
```

### Setting up the Netgsm service

Add your Netgsm User Code, Default header (name or number of sender), and secret (password) to your `.env`:

```php
// .env
...
NETGSM_USER_CODE=
NETGSM_SECRET=
NETGSM_LANGUAGE=
NETGSM_HEADER=
NETGSM_BRANDCODE=
],
...
```
NETGSM_USER_CODE and NETGSM_SECRET is authentication information of netgsm. NETGSM_HEADER is default header (name or number of sender) of sms messages.

### Usage
#### Service Methods

```php
 Netgsm::sendSms(AbstractSmsMessage $message):string $JobId
```
Sends an SMS message to the phone number on the message object passed as a parameter. 
If the message is sent successfully, a job id returned from the netgsm API service is returned.

```php
Netgsm::getReports(AbstractNetgsmReport $report): ?Collection
```

Returns a collection based on the report object passed as a parameter.

#### Sms Sending with Using Notification Channel

In order to let your Notification know which phone number you are sending to, add the routeNotificationForNetgsm method to your Notifiable model e.g your User Model

``` php
public function routeNotificationForNetgsm()
{
    /*
       where `phone` is a field in your users table, 
       phone number format can be either `5051234567` or `5051234567, 5441234568`.
    */
    return $this->phone;
}
```

You can use the channel in your `via()` method inside the notification:

``` php
use TarfinLabs\Netgsm\NetGsmChannel;
use TarfinLabs\Netgsm\NetGsmSmsMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    public function via($notifiable)
    {
        return [NetGsmChannel::class];
    }

    public function toNetgsm($notifiable)
    {
        return (new NetGsmSmsMessage("Hello! Welcome to the club {$notifiable->name}!"));
    }
}
```

You can add recipients (string or array)

``` php
return (new NetGsmSmsMessage("Your {$notifiable->service} was ordered!"))->setRecipients($recipients);
```

You can also set the sending date range of the message. (It does not work on OTP messages.)

``` php
$startDate = Carbon::now()->addDay(10)->setTime(0, 0, 0);
$endDate = Carbon::now()->addDay(11)->setTime(0, 0, 0);

return (new NetGsmSmsMessage("Great note from the future!"))
->setStartDate($startDate)
->setEndDate($endDate)
```

You can set authorized data parameter (It does not work on OTP messages.)

If this parameter passes as true, only SMS will be sent to phone numbers that have data permission.

``` php
return (new NetGsmSmsMessage("Your {$notifiable->service} was ordered!"))->setAuthorizedData(true);
```

Additionally, you can change the header.

``` php
return (new NetGsmSmsMessage("Your {$notifiable->service} was ordered!"))->setHeader("COMPANY");
```

You can use NetGsmOtpMessage instead of NetGsmSmsMessage to send an OTP message.

``` php
return (new NetGsmOtpMessage("Your {$notifiable->service} OTP Token Is : {$notifiable->otp_token}"));
```

For more information on sending OTP messages [Netgsm OTP SMS Documentation](https://www.netgsm.com.tr/dokuman/#otp-sms)

#### Sms Sending with Using Netgsm Facade

You can also send SMS or OTP messages using Netgsm Facade directly:

``` php

$message = new NetgsmSmsMessage("Your {$notifiable->service} was ordered!");
->setHeader("COMPANY")
->setRecipients(['5051234567','5441234568']);

Netgsm::sendSms($message);

```

#### Reporting

You can get SMS reports by date range or netgsm bulk id. 

To receive a report, a report object must be created.

``` php
$report = new NetgsmSmsReport();
```

##### Available Report Interfaces:

 - NetgsmSmsReport (basic reports): [Documentation](https://www.netgsm.com.tr/dokuman/#http-get-rapor)
 - NetgsmSmsDetailReport (detailed reports) [Documentation](https://www.netgsm.com.tr/dokuman/#detayl%C4%B1-rapor)


##### Object Parameters

| Method           | Description     | Type | Required | NetgsmSmsReport Support | NetgsmSmsDetailReport Support | 
| ------------ | -----------     | ---- |  -------- | --------------- | ----------------------------- |
| setStartDate()   | Start Date      | Carbon | No | Yes | Yes 
| setEndDate() | End Date    | Carbon | No | Yes | Yes 
| setBulkId()     | Netgsm Job Id    | Integer | No | Yes | Yes 
| setStatus()     | Message Status   | Integer | No | Yes | No 
| setPhone()      | Phone Number | String[] | No | Yes | Yes 
| setHeader()     | Header         | String | No | Yes | Yes 
| setVersion() | API Version     | Integer | No | Yes | Yes 

##### Sample Usage

You can get the SMS report to passing the report object to the Netgsm::getReports method. 
If successful, SMS report results will be returned as a collection. 

``` php
// Start and end dates
$startDate = Carbon::now()->subDay()->setTime(0, 0, 0);
$endDate = Carbon::now()->setTime(0, 0, 0);

$report = new NetgsmSmsReport();
$report->setStartDate($startDate)
    ->setEndDate($endDate);

$reports = Netgsm::getReports($report);
Netgsm::getReports($report);
```

Fields in the report result may differ depending on the specified report type and the report version parameter sent.

Report Results

| Field | Version | NetgsmSmsReport Support | NetgsmSmsDetailReport Support
| :----- | :----- | :---------------------- | :-------------------- |
| jobId  | All    | Yes                     | Yes
| message | 1     | No                      | Yes
| phone   | All   | Yes                     | No
| status  | All   | Yes                     | Yes
| operatorCode | 2 | Yes                   | No
| length | 2 | Yes | No
| startDate | 2 | Yes | No
| startTime | 2 | Yes | No
| endDate | All | No | Yes
| errorCode | 2 | Yes | No
| header | All | No | Yes
| total | All | No | Yes


### Account Balance

With this service, you can inquire the remaining balance of your netgsm account and the credit balances of your packages.

#### Remaining Balance

Returns the remaining money balance on netgsm account. (TL)

Usage:

```php
Netgsm::getCredit();
```

Output: 

```php
2,7
```

#### Remaining Package Credits

Returns the credit balances of the packages defined in the relevant netgsm account.

Usage:

```php
Netgsm::getAvailablePackages();
```

Output: 

```php
class Illuminate\Support\Collection#105 (1) {
  protected $items =>
  array(3) {
    [0] =>
    array(3) {
      'amount' =>
      int(1000)
      'amountType' =>
      string(14) "Adet Flash Sms"
      'packageType' =>
      string(0) ""
    }
    [1] =>
    array(3) {
      'amount' =>
      int(953)
      'amountType' =>
      string(12) "Adet OTP Sms"
      'packageType' =>
      string(0) ""
    }
    [2] =>
    array(3) {
      'amount' =>
      int(643)
      'amountType' =>
      string(4) "Adet"
      'packageType' =>
      string(3) "SMS"
    }
  }
}
```

### IYS Integration

With these services you can add and search any address to IYS.

#### Add Address

This service is used to add a phone number or email address to IYS using NetGsm IYS service.

##### Object Parameters

| Method                | Description | Type | Required | 
| --------------------- | ----------- | ---- | -------- |
| setRefId()            | Reference id to query your request | String | No
| setType()             | Communication type | String | Yes
| setSource()           | Source of permission | String | Yes
| setRecipient()        | phone number or email address | String | Yes
| setStatus()           | Permission status | String | Yes
| setConsentDate()      | Permission date | Datetime (YYYY-MM-DD H:i:s) | Yes
| setRecipientType()    | Recipient type | String | Yes
| setRetailerCode()     | Retailer code | Integer | No
| setRetailerAccess()   | Retailer access | Integer | No

##### Usage

```php
$address = new \TarfinLabs\Netgsm\Iys\Requests\Add();
$address->setRefId(999999)
        ->setType('MESAJ')
        ->setSource('HS_WEB')
        ->setRecipient('+905XXXXXXXXX')
        ->setStatus('ONAY')
        ->setConsentDate(now()->toDateTimeString())
        ->setRecipientType('TACIR');

\TarfinLabs\Netgsm\Netgsm::iys()->addAddress($address)->send();
```

##### Response Parameters
```json
{
    "code": "0",
    "error": "false",
    "uid": "73113cb9-dff0-415b-9491-xxxxxxxxxx"
}
```

#### Bulk Insert
```php
$address = new \TarfinLabs\Netgsm\Iys\Requests\Add();
$address->setRefId(999999)
        ->setSource('HS_WEB')
        ->setRecipient('+905XXXXXXXXX')
        ->setStatus('ONAY')
        ->setConsentDate(now()->toDateTimeString())
        ->setRecipientType('TACIR');

$iys = \TarfinLabs\Netgsm\Netgsm::iys();
$iys->addAddress($address->setType('MESAJ'));
$iys->addAddress($address->setType('ARAMA'));
$iys->send();
```

#### Response Paramaters for Bulk Insert
```json
{
    "code": "0",
    "error": "false",
    "uid": "16116f5e-ae2a-4745-927a-xxxxxxxxxxx",
    "erroritem": {
        "1": {
            "recipient": "Telefon numarası 13 karakter ve numerik olmalıdır.+9xxxx"
        }
    }
}
```

#### Search Address

This service is used to search a phone number or email address on IYS using NetGsm IYS service.

##### Object Parameters

| Method                | Description | Type | Required | 
| --------------------- | ----------- | ---- | -------- |
| setType()             | Communication type | String | Yes (if refId is set to null)
| setRecipient()        | phone number or email address | String | Yes (if refId is set to null)
| setRecipientType()    | Recipient type | String | Yes (if refId is set to null)
| setRefId()            | Reference id to query your request | String | No

##### Usage

```php
$address = new \TarfinLabs\Netgsm\Iys\Requests\Search();
$address->setType('MESAJ')
        ->setRecipient('+905XXXXXXXXX')
        ->setRecipientType('TACIR')
        ->setRefId(999999);

\TarfinLabs\Netgsm\Netgsm::iys()->searchAddress($address)->send();
```

##### Response Parameters
* Response with a matched address.
```json
{
    "code": "0",
    "error": "false",
    "query": {
        "consentDate": "2020-11-06 11:22:34",
        "source": "HS_FIZIKSEL_ORTAM",
        "recipient": "+905XXXXXXXXX",
        "recipientType": "BIREYSEL",
        "type": "MESAJ",
        "status": "ONAY",
        "creationDate": "2020-11-06 11:23:49",
        "retailerAccessCount": 0
        // "querystatus": null
    }
}
```

* Response without any matched address
```json
{
    "code": "50",
    "error": "Kayıt bulunamadi."
}
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update the tests as appropriate.

### Security

If you discover any security-related issues, please email development@tarfin.com instead of using the issue tracker.


## Credits

- [Hakan Özdemir](https://github.com/hozdemir)
- [Faruk Can](https://github.com/frkcn)
- [Yunus Emre Deligöz](https://github.com/deligoez)
- [Turan Karatuğ](https://github.com/tkaratug)
- [All Contributors](../../contributors)

### License
Laravel Netgsm is open-sourced software licensed under the MIT license.
