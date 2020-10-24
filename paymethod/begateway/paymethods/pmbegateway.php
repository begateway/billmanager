#!/usr/bin/php
<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "pmbegateway");

require_once 'bill_util.php';

$longopts = array(
	"command:",
	"payment:",
	"amount:",
);

$options = getopt("", $longopts);

/**
 * Processing --command
 */
try
{
	$command = $options['command'];
	Debug("command " . $options['command']);

	if ($command == "config")
	{
		$config_xml = simplexml_load_string($default_xml_string);
		$feature_node = $config_xml->addChild("feature");

		$feature_node->addChild("redirect", "on"); // If redirect supported
		$feature_node->addChild("notneedprofile", "on"); // If notneedprofile supported
		//$feature_node->addChild("pmvalidate", "on");

		$param_node = $config_xml->addChild("param");

		$param_node->addChild("payment_script", "/mancgi/begatewaypayment.php");

		echo $config_xml->asXML();
	}
/*
	elseif ($command == "pmvalidate")
	{
		$paymethod_form = simplexml_load_string(file_get_contents('php://stdin'));
		Debug($paymethod_form->asXML());

		$MNT_ID = $paymethod_form->MNT_ID;

		if (!preg_match("/^\d+$/", $MNT_ID))
		{
			throw new ISPErrorException("Incorrect value (Недопустимое значение)", "MNT_ID", $MNT_ID);
		}

		echo $paymethod_form->asXML();
	}
*/
	else
	{
		throw new ISPErrorException("Unknown command / Неизвестная команда");
	}
} catch (Exception $e)
{
	echo $e;
}

?>
