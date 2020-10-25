#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "pmbegateway");
require_once 'bill_util.php';

header('Content-Type: text/html');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$client_ip = ClientIp();
$param = CgiInput();

if ($param["auth"] == "")
{
	throw new ISPErrorException("no auth info");
} else
{
	$info = LocalQuery("payment.info", array("elid" => $param["elid"]));

	$payment = $info->payment[0];

	$shop_id = (string)$payment->paymethod[1]->SHOP_ID;
  $shop_key = (string)$payment->paymethod[1]->SHOP_KEY;
  $timeout = intval((string)$payment->paymethod[1]->TIMEOUT);
  $attempts = intval((string)$payment->paymethod[1]->ATTEMPTS);

  $currency = (string)$payment->currency[1]->iso;
	$amount = intval(strval(floatval($payment->paymethodamount) * _currency_multiplyer($currency)));

  $customer_name = explode(" ", (string)$payment->userrealname);
  $first_name = (isset($customer_name[0])) ? $customer_name[0] : '';
  $last_name = (isset($customer_name[1])) ? $customer_name[1] : '';

  $payment_attributes = $info->payment->attributes();
  $lang = (isset($payment_attributes->lang)) ? (string)$payment_attributes->lang : 'en';

	$api_url = 'https://' . (string)$payment->paymethod[1]->CHECKOUT_DOMAIN;

  $notification_url = parse_url((string)$payment->manager_url);
  $notification_url = $notification_url['scheme'] . '://' . $notification_url['host'] . ':' . $notification_url['port'];
  $notification_url = $notification_url . "/mancgi/begatewaypayurl.php";
  $notification_url = str_replace('0.0.0.0:1500', 'webhook.begateway.com:8443', $notification_url);

  $token_data = array(
    'checkout' => array(
      'transaction_type' => 'payment',
      'test' => (string)$payment->paymethod[1]->TEST_MODE == 'on',
      'order' => array(
        'amount' => $amount,
        'currency' => $currency,
        'description' => (string)$payment->description,
        'tracking_id' => (string)$payment->id,
        'additional_data' => array(
          'meta' => array(
            'cms' => 'billmanager'
          )
        )
      ),
      'settings' => array(
        'success_url' => (string)$payment->manager_url . "?func=payment.success&elid=" . (string)$payment->id . "&module=" . __MODULE__,
        'cancel_url' => (string)$payment->manager_url . "?startpage=payment",
        'decline_url' => (string)$payment->manager_url . "?func=payment.fail&elid=" . (string)$payment->id . "&module=" . __MODULE__,
        'fail_url' => (string)$payment->manager_url . "?startpage=payment",
        'notification_url' => $notification_url,
        'language' => $lang
      ),
      'customer' => array(
        'email' => (string)$payment->useremail,
        'phone' => (string)$payment->userphone,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'ip' => $client_ip
      )
    )
  );

  if ($timeout > 0) {
    $token_data['checkout']['order']['expired_at'] = date("c", $timeout*60 + time());
  }

  if ($attempts > 0) {
    $token_data['checkout']['order']['attempts'] = $attempts;
  }

  $ctp_url = $api_url . '/ctp/api/checkouts';
  $post_string = json_encode($token_data);

  Debug("Request: " . $post_string);

  $curl = curl_init($ctp_url);
  curl_setopt($curl, CURLOPT_PORT, 443);
  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'X-API-Version: 2',
    'Content-Type: application/json',
    'Content-Length: '.strlen($post_string))) ;
  curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
  curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_USERPWD, "$shop_id:$shop_key");
  curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);

  $response = curl_exec($curl) or die(curl_error($curl));
  curl_close($curl);

  Debug("Response: " . $response);

  $arToken = array();

  $token = json_decode($response, true);

  if (is_null($token)) {
    Error("Payment token response parse error: $response");
  }

  if (isset($token['errors'])) {
    Error("Payment token request validation errors: $response");
  }

  if (isset($token['response']) && isset($token['response']['message'])) {
    Error("Payment token request error: $response");
  }

  if (isset($token['checkout']) && isset($token['checkout']['token'])) {
    $arToken = array(
      'token' => $token['checkout']['token'],
      'action' => preg_replace('/(.+)\?token=(.+)/', '$1', $token['checkout']['redirect_url'])
    );
  } else {
    Error("No payment token in response: $response");
  }

	echo
		"<html>\n" .
		"<head>\n" .
		"<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n" .
		"<link rel='shortcut icon' href='billmgr.ico' type='image/x-icon' />\n" .
		"<script language='JavaScript'>function DoSubmit() { document.begatewayform.submit(); }</script>\n" .
		"</head>\n";
  if (isset($arToken['token'])) {
    echo
      "<body onload='DoSubmit()'>\n" .
  		"<form name='begatewayform' action='{$arToken["action"]}' method='post'>\n" .
  		"<input type='hidden' name='token' value='{$arToken["token"]}'>\n" .
  		"</form></body>\n";
  } else {
    echo
      "<body><p>Error to get a payment token / Ошибка получения токена платежа</p></body>\n";
  }

  echo "</html>";
}

function _currency_power($currency) {

  //array currency code => mutiplyer
  $exceptions = array(
      'BIF' => 0, 'BYR' => 0, 'CLF' => 0, 'CLP' => 0, 'CVE' => 0,
      'DJF' => 0, 'GNF' => 0, 'IDR' => 0, 'IQD' => 0, 'IRR' => 0,
      'ISK' => 0, 'JPY' => 0, 'KMF' => 0, 'KPW' => 0, 'KRW' => 0,
      'LAK' => 0, 'LBP' => 0, 'MMK' => 0, 'PYG' => 0, 'RWF' => 0,
      'SLL' => 0, 'STD' => 0, 'UYI' => 0, 'VND' => 0, 'VUV' => 0,
      'XAF' => 0, 'XOF' => 0, 'XPF' => 0, 'MOP' => 1, 'BHD' => 3,
      'JOD' => 3, 'KWD' => 3, 'LYD' => 3, 'OMR' => 3, 'TND' => 3
  );

  $power = 2; //default value
  foreach ($exceptions as $key => $value) {
      if (($currency == $key)) {
          $power = $value;
          break;
      }
  }
  return $power;
}

function _currency_multiplyer($currency) {
  return pow(10,_currency_power($currency));
}
?>
