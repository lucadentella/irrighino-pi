# Configuration :gear:

## Outputs :tulip:

In `include.php` you can define the **number of outputs**:

    define ("OUTPUTS_NUMBER", 4);

For each output, you can configure name and colors:

	$outputs[0]["name"] = "Front    ";
	$outputs[0]["baseColor"] = "#FFD800";
	$outputs[0]["borderColor"] = "#E5BF00";

and the Raspberry Pi pins (see [Raspberry Pi pinout](https://it.pinout.xyz/)) for relay, LED and switch (see [Hardware Setup](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/hwsetup.md)):

	$outputs[0]["relayPin"] = 2;
	$outputs[0]["ledPin"] = 27;
	$outputs[0]["manualOnPin"] = 14;
	$outputs[0]["manualOffPin"] = 15;

## Manual switches :pushpin:

Optional physical switches can be connected to irrighino Pi (see [Hardware Setup](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/hwsetup.md)).

> Physical switches take precedence over the web interface and allow you to configure each output in three modes:
> manual ON, manual OFF, automatic (scheduled)

In `include.php` physical switches are **enabled** by default:

    define ("ENABLE_MANUAL_SWITCHES", true);

If physical switches are not connected to irrighino Pi, set the constant to false

    define("ENABLE_RAINSENSOR", false);

## Rain Sensor :umbrella:

An optional rain sensor can be connected to irrighino Pi (see [Hardware Setup](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/hwsetup.md)).

> When the rain sensor is activated (= it rains), irrighino Pi **stops**
> all the scheduled activities. No changes are made for outputs that are
> **manually** controlled: if an output is activated manually, it remains active even if it rains.

In `include.php` the rain sensor is **enabled** by default:

    define("ENABLE_RAINSENSOR", true);

If the rain sensor is not connected to irrighino Pi, set the constant to false

    define("ENABLE_RAINSENSOR", false);

You may also change the Raspberry Pi pin the rain sensor is connected to:

	define("RAINSENSOR_PIN", 21);