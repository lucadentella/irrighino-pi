<?php

require('include.php');
logMessage("IrrighinoDaemon - Started");

// loop
for ($count = 0; $count < 15; $count++) {
	
	// open DB connection
	$db_handler  = DBconnect();
	
	// check the switch status
	for($i = 0; $i < OUTPUTS_NUMBER; $i++) {
	
		// Manual ON
		if(isSwitchOn($outputs[$i]["manualOnPin"])) {
		
			// check if output is already ON
			$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE OUT_ID = $i";
			$data_set = DBquery($db_handler, $sql);
			$row = $data_set->fetch();
			$out_status = intval($row['OUT_STATUS']);
			
			// if not, turn it ON and update the DB
			if($out_status == STATUS_OFF) {
				$response = togglePin($outputs[$i]["relayPin"], $outputs[$i]["ledPin"], 1);
				if(strpos($response, "OK") !== false) {
					$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_ON . " WHERE OUT_ID = $i";
					DBexec($db_handler, $sql);
					logEvent($db_handler, TYPE_OUT_ON, "Output $i turned ON by switch");
				}
			}
			
			// check if output is already MANAGED BY SWITCH
			$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $i";
			$data_set = DBquery($db_handler, $sql);
			$row = $data_set->fetch();
			$managed_by = intval($row['MANAGED_BY']);

			// if not, update the DB
			if($managed_by != MANAGED_BY_SWITCH) {
				$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_SWITCH . " WHERE OUT_ID = $i";
				DBexec($db_handler, $sql);
				logEvent($db_handler, TYPE_CFG_CHANGE, "Output $i set managed by switch");
			}					
		}
		
		// Manual OFF
		else if(isSwitchOn($outputs[$i]["manualOffPin"])) {
		
			// check if output is already OFF
			$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE OUT_ID = $i";
			$data_set = DBquery($db_handler, $sql);
			$row = $data_set->fetch();
			$out_status = intval($row['OUT_STATUS']);
			
			// if not, turn it ON and update the DB
			if($out_status == STATUS_ON) {
				$response = togglePin($outputs[$i]["relayPin"], $outputs[$i]["ledPin"], 0);
				if(strpos($response, "OK") !== false) {
					$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_OFF . " WHERE OUT_ID = $i";
					DBexec($db_handler, $sql);
					logEvent($db_handler, TYPE_OUT_OFF, "Output $i turned OFF by switch");
				}
			}
			
			// check if output is already MANAGED BY SWITCH
			$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $i";
			$data_set = DBquery($db_handler, $sql);
			$row = $data_set->fetch();
			$managed_by = intval($row['MANAGED_BY']);

			// if not, update the DB
			if($managed_by != MANAGED_BY_SWITCH) {
				$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_SWITCH . " WHERE OUT_ID = $i";
				DBexec($db_handler, $sql);
				logEvent($db_handler, TYPE_CFG_CHANGE, "Output $i set managed by switch");
			}		
		}
		
		// Auto
		else {
			
			// check if output is already MANAGED BY AUTO
			$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $i";
			$data_set = DBquery($db_handler, $sql);
			$row = $data_set->fetch();
			$managed_by = intval($row['MANAGED_BY']);

			// if not, update the DB
			if($managed_by != MANAGED_BY_AUTO) {
				$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_AUTO . " WHERE OUT_ID = $i";
				DBexec($db_handler, $sql);
				logEvent($db_handler, TYPE_CFG_CHANGE, "Output $i set managed by auto via switch");
			}
		}
	}
	
	// close DB connection
	DBdisconnect();
	
	// sleep for 2 sec
	sleep(2);
}

logMessage("IrrighinoDaemon - Completed");
?>