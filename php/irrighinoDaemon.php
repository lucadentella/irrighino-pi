<?php

require('include.php');

require_once("./PiPHP/src/GPIOInterface.php");
require_once("./PiPHP/src/GPIO.php");

require_once("./PiPHP/src/FileSystem/FileSystemInterface.php");
require_once("./PiPHP/src/FileSystem/FileSystem.php");

require_once("./PiPHP/src/Pin/PinInterface.php");
require_once("./PiPHP/src/Pin/InputPinInterface.php");
require_once("./PiPHP/src/Pin/OutputPinInterface.php");
require_once("./PiPHP/src/Pin/Pin.php");
require_once("./PiPHP/src/Pin/InputPin.php");
require_once("./PiPHP/src/Pin/OutputPin.php");

require_once("./PiPHP/src/Interrupt/InterruptWatcherInterface.php");
require_once("./PiPHP/src/Interrupt/InterruptWatcher.php");

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;
use PiPHP\GPIO\Pin\InputPinInterface;

function getOutputFromManualPin($pin) {
	
	if($pin == 14) return [0, MANUAL_ON];
	else return [0, MANUAL_OFF];
}

function setManualOn($output) {

	// access the global variable with outputs
	global $outputs;

	// open DB connection
	$db_handler  = DBconnect();

	// check if output is already ON
	$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE OUT_ID = $output";
	$data_set = DBquery($db_handler, $sql);
	$row = $data_set->fetch();
	$out_status = intval($row['OUT_STATUS']);
	
	// if not, turn it ON and update the DB
	if($out_status == STATUS_OFF) {
		$response = togglePin($outputs[$output]["relayPin"], $outputs[$output]["ledPin"], 1);
		if(strpos($response, "OK") !== false) {
			$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_ON . " WHERE OUT_ID = $output";
			DBexec($db_handler, $sql);
			logEvent($db_handler, TYPE_OUT_ON, "Output $output turned ON by switch");
			logMessage("IrrighinoDaemon - Output $output turned ON");
		}
	}
	
	// check if output is already MANAGED BY SWITCH
	$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $output";
	$data_set = DBquery($db_handler, $sql);
	$row = $data_set->fetch();
	$managed_by = intval($row['MANAGED_BY']);

	// if not, update the DB
	if($managed_by != MANAGED_BY_SWITCH) {
		$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_SWITCH . " WHERE OUT_ID = $output";
		DBexec($db_handler, $sql);
		logEvent($db_handler, TYPE_CFG_CHANGE, "Output $output set managed by MANUAL via switch");
		logMessage("IrrighinoDaemon - Output $output set managed by MANUAL");
	}					
	
	// close DB connection
	DBdisconnect();
}

function setManualOff($output) {

	// access the global variable with outputs
	global $outputs;

	// open DB connection
	$db_handler  = DBconnect();

	// check if output is already OFF
	$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE OUT_ID = $output";
	$data_set = DBquery($db_handler, $sql);
	$row = $data_set->fetch();
	$out_status = intval($row['OUT_STATUS']);
	
	// if not, turn it OFF and update the DB
	if($out_status == STATUS_ON) {
		$response = togglePin($outputs[$output]["relayPin"], $outputs[$output]["ledPin"], 0);
		if(strpos($response, "OK") !== false) {
			$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_OFF . " WHERE OUT_ID = $output";
			DBexec($db_handler, $sql);
			logEvent($db_handler, TYPE_OUT_OFF, "Output $output turned OFF by switch");
			logMessage("IrrighinoDaemon - Output $output turned OFF");
		}
	}
	
	// check if output is already MANAGED BY SWITCH
	$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $output";
	$data_set = DBquery($db_handler, $sql);
	$row = $data_set->fetch();
	$managed_by = intval($row['MANAGED_BY']);

	// if not, update the DB
	if($managed_by != MANAGED_BY_SWITCH) {
		$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_SWITCH . " WHERE OUT_ID = $output";
		DBexec($db_handler, $sql);
		logEvent($db_handler, TYPE_CFG_CHANGE, "Output $output set managed by MANUAL via switch");
		logMessage("IrrighinoDaemon - Output $output set managed by MANUAL");
	}

	// close DB connection
	DBdisconnect();
}

function setAuto($output) {

	// open DB connection
	$db_handler  = DBconnect();

	// check if output is already MANAGED BY AUTO
	$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $output";
	$data_set = DBquery($db_handler, $sql);
	$row = $data_set->fetch();
	$managed_by = intval($row['MANAGED_BY']);

	// if not, update the DB
	if($managed_by != MANAGED_BY_AUTO) {
		$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_AUTO . " WHERE OUT_ID = $output";
		DBexec($db_handler, $sql);
		logEvent($db_handler, TYPE_CFG_CHANGE, "Output $output set managed by AUTO via switch");
		logMessage("IrrighinoDaemon - Output $output set managed by AUTO");
	}

	// close DB connection
	DBdisconnect();	
}

function pinWatcher($pin, $value) {

	logMessage("IrrighinoDaemon - New event detected on pin " . $pin->getNumber());

	// get output and action based on the pin
	[$output, $action] = getOutputFromManualPin($pin->getNumber());

	// switch in manual position
	if($value == 0) {
		
		if($action == MANUAL_ON) setManualOn($output);
		else setManualOff($output);
	}
	
	// switch in automatic position
	else setAuto($output);
}

logMessage("IrrighinoDaemon - Started");

// Create a GPIO object
$gpio = new GPIO();

// Configure pins as input and configure the interrupt events
$pin14 = $gpio->getInputPin(14);
$pin15 = $gpio->getInputPin(15);
$pin14->setEdge(InputPinInterface::EDGE_BOTH);
$pin15->setEdge(InputPinInterface::EDGE_BOTH);

// Attach the interrupt watcher
$interruptWatcher = $gpio->createWatcher();
$interruptWatcher->register($pin14, 'pinWatcher');
$interruptWatcher->register($pin15, 'pinWatcher');

logMessage("IrrighinoDaemon - Pin configuration complete, starting watcher...");

// Start the watcher
while ($interruptWatcher->watch(1000));

logMessage("IrrighinoDaemon - Completed");
?>