<?php

require('include.php');

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\PinInterface;
use PiPHP\GPIO\Pin\InputPinInterface;

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
			logMessage("IrrighinoDaemon - Output $output turned ON by switch");
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
			logMessage("IrrighinoDaemon - Output $output turned OFF by switch");
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

function rainsensorWatcher($pin, $value) {

	logMessage("IrrighinoDaemon - New event detected for rainsensor");

	// rainsensor on
	if($value == 0) {
		
		// open DB connection
		$db_handler  = DBconnect();
		
		// get all the ouputs managed by AUTO
		$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE MANAGED_BY = " . MANAGED_BY_AUTO;
		$data_set = DBquery($db_handler, $sql);
		
		if($data_set) {
		
			foreach($data_set as $row) {

				$out_id = intval($row['OUT_ID']);
				$out_status = intval($row['OUT_STATUS']);
				
				// check only valid outputs
				if($out_id >= OUTPUTS_NUMBER) break;
				
				// turn them OFF
				if($out_status == STATUS_ON) {
					$response = togglePin($outputs[$out_id]["relayPin"], $outputs[$out_id]["ledPin"], 0);
					if(strpos($response, "OK") !== false) {
						$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_OFF . " WHERE OUT_ID = $out_id";
						DBexec($db_handler, $sql);					
						logEvent($db_handler, TYPE_OUT_OFF, "Output $out_id turned OFF by rainsensor");
						logMessage("IrrighinoDaemon - Output $out_id turned OFF by rainsensor");
					}					
				}
				
				// set all MANAGED_BY_RAINSENSOR
				$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_RAINSENSOR . " WHERE OUT_ID = $out_id";
				DBexec($db_handler, $sql);
				logEvent($db_handler, TYPE_CFG_CHANGE, "Output $out_id set managed by RAINSENSOR");
				logMessage("IrrighinoDaemon - Output $out_id set managed by RAINSENSOR");
			}
		}
		
		// close DB connection
		DBdisconnect();
	}
	
	// rainsensor off
	else {
		
		// reset to AUTO all the outputs managed by RAINSENSOR
		
		// open DB connection
		$db_handler  = DBconnect();
		
		// get all the ouputs managed by RAINSENSOR
		$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE MANAGED_BY = " . MANAGED_BY_RAINSENSOR;
		$data_set = DBquery($db_handler, $sql);
		
		if($data_set) {
		
			foreach($data_set as $row) {

				$out_id = intval($row['OUT_ID']);
				$out_status = intval($row['OUT_STATUS']);				
				
				$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_AUTO . " WHERE OUT_ID = $out_id";
				DBexec($db_handler, $sql);
				logEvent($db_handler, TYPE_CFG_CHANGE, "Output $out_id set managed by AUTO by rainsensor");
				logMessage("IrrighinoDaemon - Output $out_id set managed by AUTO by rainsensor");
			}
		}
	}
}

logMessage("IrrighinoDaemon - Started");

// Create a GPIO object and an Interrupt Watcher
$gpio = new GPIO();
$interruptWatcher = $gpio->createWatcher();

// create pin object array
$pins = array();

// Configure pins as input and configure the interrupt events
if(ENABLE_MANUAL_SWITCHES == true) {

	logMessage("IrrighinoDaemon - Manual switches are enabled, configuring the corresponding pins...");

	for($i = 0; $i < OUTPUTS_NUMBER; $i++) {
		
		$pin = $gpio->getInputPin($outputs[$i]["manualOnPin"]);
		$pin->setEdge(InputPinInterface::EDGE_BOTH);
		$interruptWatcher->register($pin, 'pinWatcher');
		$pin = $gpio->getInputPin($outputs[$i]["manualOffPin"]);
		$pin->setEdge(InputPinInterface::EDGE_BOTH);
		$interruptWatcher->register($pin, 'pinWatcher');	
	}
	
	logMessage("IrrighinoDaemon - Pins configuration complete");
}

// Configure rainsensor pin
if(ENABLE_RAINSENSOR == true) {

	logMessage("IrrighinoDaemon - Rainsensor is enabled, configuring the corresponding pin...");
	
	$pin = $gpio->getInputPin(RAINSENSOR_PIN);
	$pin->setEdge(InputPinInterface::EDGE_BOTH);
	$interruptWatcher->register($pin, 'rainsensorWatcher');
	
	logMessage("IrrighinoDaemon - Pin configuration complete");
}

logMessage("IrrighinoDaemon - Starting watcher...");

// Start the watcher
while ($interruptWatcher->watch(1000));

logMessage("IrrighinoDaemon - Completed");
?>