<?php

require('include.php');

// infinite loop
for (;;) {
	
	// check the switch status
	for($i = 0; $i < 1; $i++) {
	
		if(isSwitchOn($outputs[$i]["manualOnPin"])) print("manual ON");
		else if(isSwitchOn($outputs[$i]["manualOffPin"])) print("manual OFF");
	}
}