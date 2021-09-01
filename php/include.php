<?php

// --------------- Include PiPHP library ---------------

require_once(__DIR__ ."/PiPHP/src/GPIOInterface.php");
require_once(__DIR__ ."/PiPHP/src/GPIO.php");

require_once(__DIR__ ."/PiPHP/src/FileSystem/FileSystemInterface.php");
require_once(__DIR__ ."/PiPHP/src/FileSystem/FileSystem.php");

require_once(__DIR__ ."/PiPHP/src/Pin/PinInterface.php");
require_once(__DIR__ ."/PiPHP/src/Pin/InputPinInterface.php");
require_once(__DIR__ ."/PiPHP/src/Pin/OutputPinInterface.php");
require_once(__DIR__ ."/PiPHP/src/Pin/Pin.php");
require_once(__DIR__ ."/PiPHP/src/Pin/InputPin.php");
require_once(__DIR__ ."/PiPHP/src/Pin/OutputPin.php");

require_once(__DIR__ ."/PiPHP/src/Interrupt/InterruptWatcherInterface.php");
require_once(__DIR__ ."/PiPHP/src/Interrupt/InterruptWatcher.php");

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;

// --------------- Output configuration ---------------

define ("OUTPUTS_NUMBER", 4);
define ("ENABLE_MANUAL_SWITCHES", true);

$outputs = array();
$outputs[0]["name"] = "Front    ";
$outputs[0]["baseColor"] = "#FFD800";
$outputs[0]["borderColor"] = "#E5BF00";
$outputs[0]["relayPin"] = 2;
$outputs[0]["ledPin"] = 27;
$outputs[0]["manualOnPin"] = 14;
$outputs[0]["manualOffPin"] = 15;

$outputs[1]["name"] = "Left";
$outputs[1]["baseColor"] = "#FF0000";
$outputs[1]["borderColor"] = "#E00000";
$outputs[1]["relayPin"] = 3;
$outputs[1]["ledPin"] = 22;
$outputs[1]["manualOnPin"] = 18;
$outputs[1]["manualOffPin"] = 23;

$outputs[2]["name"] = "Right";
$outputs[2]["baseColor"] = "#00FF21";
$outputs[2]["borderColor"] = "#00E21A";
$outputs[2]["relayPin"] = 4;
$outputs[2]["ledPin"] = 10;
$outputs[2]["manualOnPin"] = 24;
$outputs[2]["manualOffPin"] = 25;

$outputs[3]["name"] = "Rear";
$outputs[3]["baseColor"] = "#005DFF";
$outputs[3]["borderColor"] = "#0049C9";
$outputs[3]["relayPin"] = 17;
$outputs[3]["ledPin"] = 9;
$outputs[3]["manualOnPin"] = 8;
$outputs[3]["manualOffPin"] = 7;

// --------------- Rain sensor configuration ---------------

define("ENABLE_RAINSENSOR", true);
define("RAINSENSOR_PIN", 21);


// --------------- Timestamp and logging configuration ---------------

date_default_timezone_set("Europe/Rome");
define("LOG_DIR", "/var/www/html/irrighino-pi/logs/");
define("RETENTION_DAYS", 7);


// !! --------------- YOU SHOULD NOT CHANGE ANYTHING BELOW --------------- !!

// --------------- Database path configuration ---------------

define ("DB_PATH", "sqlite:../db/irrighino.db");

// --------------- Constants configuration ---------------

define ("TYPE_OUT_ON", 0);
define ("TYPE_OUT_OFF", 1);
define ("TYPE_RAIN_ON", 2);
define ("TYPE_RAIN_OFF", 3);
define ("TYPE_CFG_CHANGE", 4);
define ("TYPE_MANUAL_OVERRIDE", 5);
define ("TYPE_ERROR", 6);

define ("MANAGED_BY_SWITCH", 0);
define ("MANAGED_BY_WEB", 1);
define ("MANAGED_BY_AUTO", 2);

define ("STATUS_OFF", 0);
define ("STATUS_ON", 1);

define ("MANUAL_ON", 0);
define ("MANUAL_OFF", 1);

// --------------- Logging functions ---------------

function logEvent($db_handler, $eventType, $message) {
	
	$sql = "INSERT INTO LOG(EVENT_ID, DATE, EVENT_DESC) VALUES($eventType, DATETIME('NOW'), '$message')";
	DBexec($db_handler, $sql);
}

function logMessage($msg) {
	
		if(!file_exists(LOG_DIR)) mkdir(LOG_DIR);
	
		chdir (__DIR__);
		$logfile_suffix = date("Ymd");
		$current_time = date("Y-m-d H:i:s");
		
		$pos = strpos($msg, " - ");
		if($pos == false) $log_file_name = "irrighino.log.";
		else $log_file_name = substr($msg, 0, $pos) . ".log.";
		
		$msg = "$current_time - $msg";
		$log_file_descriptor = fopen(LOG_DIR . $log_file_name . $logfile_suffix, "a");
		fwrite($log_file_descriptor, $msg . PHP_EOL);
		fclose($log_file_descriptor);
}

// --------------- Return code functions ---------------

function sendReturnCode($code, $message) {
	
	$returnCode = new ReturnCode();
	$returnCode->code = $code;
	$returnCode->message = $message;
	
	$out = json_encode($returnCode);
	header('Content-Type: application/json');
	echo $out;
}

// --------------- Database functions ---------------

function DBconnect() {
	
	try {
		$db_handler = new PDO(DB_PATH);
	} catch (PDOException $e) {
		echo $e;
		$db_handler = false;
	}
	return $db_handler;
}

function DBdisconnect() {
	
	$db_handler = null;
}

function DBquery($handler, $sql) {
	
	if($handler == false) return false;
	return $handler->query($sql);
}

function DBexec($handler, $sql) {
	
	if($handler == false) return false;
	return $handler->exec($sql);
}

// --------------- PIN functions ---------------

function togglePin($relay_pin, $led_pin, $new_status) {
	
	// GPIO object
	$gpio = new GPIO();
	
	// set the pin direction as OUT
	$relay_pin_object = $gpio->getOutputPin($relay_pin);
	$led_pin_object = $gpio->getOutputPin($led_pin);
	
	// toggle pin status
	if($new_status == 1) {
		$relay_pin_object->setValue(PinInterface::VALUE_HIGH);
		$led_pin_object->setValue(PinInterface::VALUE_HIGH);
	}
	else {
		$relay_pin_object->setValue(PinInterface::VALUE_LOW);
		$led_pin_object->setValue(PinInterface::VALUE_LOW);		
	}

	/*// set the pin direction as OUT
	exec("/usr/bin/gpio mode $relay_pin out");
	exec("/usr/bin/gpio mode $led_pin out");
	
	// toggle pin status
	exec("/usr/bin/gpio write $relay_pin $new_status");
	exec("/usr/bin/gpio write $led_pin $new_status");*/
	
	return "OK";
}

function getOutputFromManualPin($pin) {
	
	global $outputs;
	
	for($i = 0; $i < OUTPUTS_NUMBER; $i++) {
	
		if($outputs[$i]["manualOnPin"] == $pin) return [$i, MANUAL_ON];
		else if($outputs[$i]["manualOffPin"] == $pin) return [$i, MANUAL_OFF];
	}
	
	return [-1,-1];
}
?>
