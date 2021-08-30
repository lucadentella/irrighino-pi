
# irrighino-pi
![](https://github.com/lucadentella/irrighino-pi/raw/main/images/logo-pi.png)

## Description

Smart watering controller based on Raspberry Pi.

![](https://img.shields.io/badge/license-CC--BY--NC--SA-green)

## Features :trophy:

 - **Web-based** GUI
 - **Weekly** calendar with **2 minutes** resolution
 - **Manual** control of each valve (via GUI or physical switches)

## How to install :notebook:

**0. Upgrade the raspbian distribution**

    sudo apt update
    sudo apt upgrade
    reboot

**1. Install nginx and PHP** 
- Follow the [official guide](https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md)
- When complete, make sure you can see the phpinfo page:

**2. Install the required libraries and modules**

    sudo apt-get install php-sqlite3 php-curl
    sudo apt-get install sqlite3
    sudo apt-get install wiringpi

**3. Clone the Github repository**

sudo chmod -R o+w /var/www/html/irrighino-pi/db/
sudo mkdir /var/www/html/irrighino-pi/logs
sudo chmod o+w /var/www/html/irrighino-pi/logs
sudo usermod -a -G www-data pi
sudo usermod -a -G gpio www-data

// modifica in 664 il log quando lo crei da zero
