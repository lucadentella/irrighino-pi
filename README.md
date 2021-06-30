# irrighino-pi

Upgrade the raspbian distribution
sudo apt update
sudo apt upgrade
reboot

Install nginx and PHP following the official guide:
https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md

Make sure you can see the phpinfo page:

Install the required php modules:
sudo apt-get install php-sqlite3 php-curl

Install SQlite client
sudo apt-get install sqlite3

Install the WiringPi library to control GPIO pins
sudo apt-get install wiringpi


sudo chmod -R o+w /var/www/html/irrighino-pi/db/
sudo mkdir /var/www/html/irrighino-pi/logs
sudo chmod o+w /var/www/html/irrighino-pi/logs
sudo usermod -a -G www-data pi
sudo usermod -a -G gpio www-data

// modifica in 664 il log quando lo crei da zero
