#!/bin/bash
#colors-------------------------------------------------------------------------
green=\\e[1\;32m
rose=\\e[1\;1\;35m
end_c=\\e[0m
#-------------------------------------------------------------------------------
echo -e "${rose}Updating system${end_c}"
sudo apt-get update && sudo apt-get upgrade -y
echo -e "${green}[done]${end_c}"
echo -e "${rose}Cloning from github${end_c}"
git clone git@github.com:blavonne/roger.git
echo -e "${green}[done]${end_c}"
echo -e "${rose}Installing apache2 && php${end_c}"
sudo apt-get apache2 -y
sudo apt install php libapache2-mod-php php -y
echo -e "${green}[done]${end_c}"
echo -e "${rose}Repair apache-config${end_c}"
sudo rm -rf /var/www/barni21.com
sudo a2enmod ssl
sudo cp -rf ./roger/Web-part/barni21.com/ /var/www/
sudo rm -f /etc/apache2/sites-available/barni21-ssl.conf
sudo cp -f ./roger/Web-part/barni21-ssl.conf /etc/apache2/sites-available/
sudo rm -f /etc/apache2/sites-available/barni21.com.conf
sudo cp ./roger/Web-part/barni21.com.conf /etc/apache2/sites-available/
sudo a2dissite 000-default.conf
sudo a2ensite barni21.com
sudo a2ensite barni21-ssl
sudo cp -f /roger/dir.conf /etc/apache2/mods-eabled/
sudo systemctl restart apache2
echo -e "${green}[done]${end_c}"
echo -e "${rose}Cleaning${end_c}"
sudo rm -rf roger/
echo -e "${green}[done]${end_c}"
