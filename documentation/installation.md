# Installation :floppy_disk:

**0. Upgrade the raspbian distribution**

    sudo apt update
    sudo apt upgrade
    sudo reboot

**1. Install Apache Web Server with PHP** 

	sudo apt install apache2 -y
	sudo apt install php libapache2-mod-php -y
	
Create the phpinfo.php page

	echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/phpinfo.php
	
and verify you can see the content

![](https://github.com/lucadentella/irrighino-pi/raw/main/images/phpinfo.png)

**2. Install the required libraries and modules**

    sudo apt-get install php-sqlite3 php-curl -y
    sudo apt-get install sqlite3 -y

**3. Enable the modules**

    sudo nano /etc/php/7.3/apache2/php.ini
	
Remove the ";" in front of extension=pdo_sqlite

    sudo nano /etc/php/7.3/cli/php.ini
	
Remove the ";" in front of extension=pdo_sqlite

    sudo systemctl restart apache2

**4. Clone the Github repository**

    cd /var/www/html/
	sudo git clone https://github.com/lucadentella/irrighino-pi

**5. Set the correct permissions**

    sudo chmod -R o+w /var/www/html/irrighino-pi/db/
    sudo mkdir /var/www/html/irrighino-pi/logs
    sudo chmod o+w /var/www/html/irrighino-pi/logs
    sudo usermod -a -G www-data pi
    sudo usermod -a -G gpio www-data

**6. Add crontab schedules**

    crontab -e

Add the following lines:

	@reboot sleep 120 && /usr/bin/php /var/www/html/irrighino-pi/php/irrighinoDaemon.php >/dev/null 2>&1
	* * * * * /usr/bin/php /var/www/html/irrighino-pi/php/irrighinoTask.php >/dev/null 2>&1
	2 0 * * * /var/www/html/irrighino-pi/php/purgeOldLogs.php >/dev/null 2>&1
	5 0 * * * /usr/bin/php /var/www/html/irrighino-pi/php/purgeOldEvents.php >/dev/null 2>&1
