<?php

// --------------- Output configuration ---------------

define ("OUTPUTS_NUMBER", 4);

$outputs = array();
$outputs[0]["name"] = "Front    ";
$outputs[0]["baseColor"] = "#FFD800";
$outputs[0]["borderColor"] = "#E5BF00";
$outputs[0]["relayPin"] = 8;
$outputs[0]["ledPin"] = 2;
$outputs[0]["manualOnPin"] = 15;
$outputs[0]["manualOffPin"] = 16;

$outputs[1]["name"] = "Left";
$outputs[1]["baseColor"] = "#FF0000";
$outputs[1]["borderColor"] = "#E00000";
$outputs[1]["relayPin"] = 9;
$outputs[1]["ledPin"] = 3;
$outputs[1]["manualOnPin"] = 1;
$outputs[1]["manualOffPin"] = 4;

$outputs[2]["name"] = "Right";
$outputs[2]["baseColor"] = "#00FF21";
$outputs[2]["borderColor"] = "#00E21A";
$outputs[2]["relayPin"] = 7;
$outputs[2]["ledPin"] = 12;
$outputs[2]["manualOnPin"] = 5;
$outputs[2]["manualOffPin"] = 6;

$outputs[3]["name"] = "Rear";
$outputs[3]["baseColor"] = "#005DFF";
$outputs[3]["borderColor"] = "#0049C9";
$outputs[3]["relayPin"] = 0;
$outputs[3]["ledPin"] = 13;
$outputs[3]["manualOnPin"] = 10;
$outputs[3]["manualOffPin"] = 11;

// --------------- Rain sensor configuration ---------------

define("RAINSENSOR_PIN", 20);


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
	
	// set the pin direction as OUT
	exec("/usr/bin/gpio mode $relay_pin out");
	exec("/usr/bin/gpio mode $led_pin out");
	
	// toggle pin status
	exec("/usr/bin/gpio write $relay_pin $new_status");
	exec("/usr/bin/gpio write $led_pin $new_status");
	
	return "OK";
}

function isSwitchOn($switch_pin) {
	
	// set the pin direction as INPUT
	exec("/usr/bin/gpio mode $switch_pin input");
	
	// get pin status
	$output = null;
	exec("/usr/bin/gpio read $switch_pin", $output);
	
	if($output[0] == "0") return true;
	else return false;
}
?>
