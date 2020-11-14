#!/bin/bash
#colors-------------------------------------------------------------------------
green=\\e[1\;32m
rose=\\e[1\;1\;35m
end_c=\\e[0m
#-------------------------------------------------------------------------------
echo -en "${rose}Updating system [${end_c}"
sudo apt-get update && sudo apt-get upgrade -y
echo -en "${rose}.] ${end_c}"
echo -e "${green}[done]${end_c}"
echo -en "${rose}Cloning from github [${end_c}"
git clone git@github.com:blavonne/roger.git
echo -en "${rose}.] ${end_c}"
echo -e "${green}[done]${end_c}"
echo -en "${rose}Installing apache2 && php [${end_c}"
sudo apt-get apache2 -y
echo -en "${rose}.${end_c}"
sudo apt install php libapache2-mod-php php -y
echo -en "${rose}.] ${end_c}"
echo -e "${green}[done]${end_c}"
echo -en "${rose}Repair apache-config [${end_c}"
sudo rm -rf /var/www/barni21.com
echo -en "${rose}.${end_c}"
sudo a2enmod ssl
echo -en "${rose}.${end_c}"
sudo cp -rf ./roger/Web-part/barni21.com/ /var/www/
echo -en "${rose}.${end_c}"
sudo rm -f /etc/apache2/sites-available/barni21-ssl.conf
echo -en "${rose}.${end_c}"
sudo cp -f ./roger/Web-part/barni21-ssl.conf /etc/apache2/sites-available/
echo -en "${rose}.${end_c}"
sudo rm -f /etc/apache2/sites-available/barni21.com.conf
echo -en "${rose}.${end_c}"
sudo cp ./roger/Web-part/barni21.com.conf /etc/apache2/sites-available/
echo -en "${rose}.${end_c}"
sudo a2dissite 000-default.conf
echo -en "${rose}.${end_c}"
sudo a2ensite barni21.com
echo -en "${rose}.${end_c}"
sudo a2ensite barni21-ssl
echo -en "${rose}.${end_c}"
sudo cp -f /roger/dir.conf /etc/apache2/mods-enabled/
echo -en "${rose}.${end_c}"
sudo systemctl restart apache2
echo -en "${rose}.] ${end_c}"
echo -e "${green}[done]${end_c}"
echo -en "${rose}Cleaning [${end_c}"
sudo rm -rf roger/
echo -en "${rose}.] ${end_c}"
echo -e "${green}[done]${end_c}"
