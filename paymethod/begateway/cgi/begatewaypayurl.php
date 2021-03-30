#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "begatewaypayurl");

echo "Content-Type: text/html\n\n";

require_once 'bill_util.php';

$postData =  (string)file_get_contents("php://input");

if ($_SERVER["REQUEST_METHOD"] == 'POST'){
    $size = $_SERVER["CONTENT_LENGTH"];
    if ($size == 0) {
        $size =	$_SERVER["HTTP_CONTENT_LENGTH"];
    }
    if (!feof(STDIN)) {
        $input = fread(STDIN, $size);
    }
} else {
  Debug("Webhook: method not allowed");
  die('Method not allowed');
}

$postData = $input;

$post_array = json_decode($postData, true);

Debug("Webhook: " . $postData);

if (!isset($post_array['transaction'])) {
  Debug("Webhook: No data");
  die("No data");
}

$payment_id = $post_array['transaction']['tracking_id'];
$info = LocalQuery("payment.info", array('elid' => $payment_id));

if (empty($info)) {
  Debug("Webhook: No payment found for payment id " . $payment_id);
  die("No payment found");
}

$shop_id = $info->payment->paymethod[1]->SHOP_ID;
$shop_key = $info->payment->paymethod[1]->SHOP_KEY;
$shop_public_key = $info->payment->paymethod[1]->SHOP_PUBLIC_KEY;

if (!isset($_SERVER['CONTENT_SIGNATURE'])) {
  Debug("Webhook: No signature");
  die('No signature');
}

$signature = base64_decode($_SERVER['CONTENT_SIGNATURE']);
$public_key = "-----BEGIN PUBLIC KEY-----\n$shop_public_key\n-----END PUBLIC KEY-----";
$key = openssl_pkey_get_public($public_key);

if (openssl_verify($postData, $signature, $key, OPENSSL_ALGO_SHA256) != 1) {
  Debug("Webhook: signature mismatch");
  die('Not authorized');
}

$status = $post_array['transaction']['status'];
$uid = $post_array['transaction']['uid'];
$message = $post_array['transaction']['message'];
$currency = $info->payment->currency[1]->iso;
$amount = intval(strval(floatval($info->payment->paymethodamount) * _currency_multiplyer($currency)));

if ($amount != intval($post_array['transaction']['amount'])) {
  Debug("Webhook: invalid amount");
  $message = $message . ' ------ ';
  $message = $message . $currency . ' ' . _currency_multiplyer($currency);
  $message = $message . ' ------ ' . $post_array['transaction']['amount'];
  $message = $message . ' ------ ' . $amount;

  die('Invalid amount'. $message);
}

if ($currency != $post_array['transaction']['currency']) {
  Debug("Webhook: invalid currency");
  die('Invalid currency');
}

if ($post_array['transaction']['test']) {
  $message = '***TEST MODE*** ' . $message;
}

$method = null;

if ($status == 'successful') {
  $method = "payment.setpaid";
} elseif ($satus == 'failed' || $status == 'expired') {
  $method = "payment.setnopay";
} elseif ($status == "pending") {
  $method = "payment.setinpay";
}

if (!is_null($method)) {
  LocalQuery($method,
    array(
      'elid' => $payment_id,
      'info' => $message . " " . $info->payment->paymethodamount . " " . $currency,
      'externalid' => $uid
    )
  );

  Debug("Webhook: " . $method . " processed. External id " . $uid);
  die("OK");
}

Debug("Webhook: no updates");
die("No updates");

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
