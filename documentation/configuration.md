# Configuration :gear:

## Rain Sensor :umbrella:

An optional rain sensor can be connected to irrighino Pi (see [Hardware Setup](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/hwsetup.md)).

> When the rain sensor is activated (= it rains), irrighino Pi **stops**
> all the scheduled activities. No changes are made for outputs that are
> **manually** controlled: if an output is activated manually, it remains active even if it rains.

In `include.php` the rain sensor is **enabled** by default:

    define("ENABLE_RAINSENSOR", true);

If the rain sensor is not connected to irrighino Pi, set the constant to false

    define("ENABLE_RAINSENSOR", true);

You may also change the Raspberry Pi pin the rain sensor is connected to:

	define("RAINSENSOR_PIN", 21);