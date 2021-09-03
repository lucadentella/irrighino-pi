# Harware Setup :electric_plug:

## Relay board :low_brightness:

To control the watering valves, you need an external **relay board**.

Depending on the model of your relay board, connect board VCC to Raspberry Pi 5V or 3.3V.

Connect board GND to Raspberry Pi GND.

Connect board inputs (IN1...) to the Raspberry Pi pins defined as **relayPin** in the [Configuration file](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/configuration.md)

![](https://github.com/lucadentella/irrighino-pi/raw/main/images/hw-relay.png)

## Watering valves :potable_water:

Connect one wire of the valve to the power supply (usually 12V or 24V, check your valve manual).

Connect the other wire of the power supply to the **common** contact of one relay.

Connect the other wire of the valve to the **normally open** (N.O.) contact of the same relay.

![](https://github.com/lucadentella/irrighino-pi/raw/main/images/hw-valves.png)

## Control panel (optional) :pushpin:

irrighino Pi can be connected to an external **physical** control panel.

Outputs can be controlled using three-way switches and monitored via LEDs.

Connect switches and LEDs as follows:

![](https://github.com/lucadentella/irrighino-pi/raw/main/images/hw-controlpanel.png)

ledPin, manualOnPin and manualOffPin are the pins defined in the [Configuration file](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/configuration.md).

If you don't connect a control panel, make sure to **disable** the physical switches in the [Configuration file](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/configuration.md).

## Rain Sensor :umbrella:

irrighino Pin can be connected to an external rain sensor, to stop automatic schedules if it rains:

![](https://github.com/lucadentella/irrighino-pi/raw/main/images/hw-rainsensor.png)

The Raspberry Pi pin the rain sensor is connected to can be changed in the [Configuration file](https://github.com/lucadentella/irrighino-pi/tree/main/documentation/configuration.md).

