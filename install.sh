#!/bin/bash

sgreen=\\e[1\;32m
egreen=\\e[0m

apt-get install sudo -y
echo -e "${sgreen}[SUDO is installed]${egreen}"
apt-get install vim -y
echo -e "${sgreen}[VIM is installed]${egreen}"
apt-get install net-tools -y
echo -e "${sgreen}[net-tools is installed]${egreen}"
apt-get install ufw -y
echo -e "${sgreen}[ufw is installed]${egreen}"

sed -ie '/^# Allow members/i barrett ALL=(ALL:ALL) ALL' /etc/sudoers

sed -ie 's/#Port 22/Port 50000/' /etc/ssh/sshd_config
sed -ie 's/#PermitRootLogin prohibit-password/PermitRootLogin no/' /etc/ssh/sshd_config
sed -ie 's/#PasswordAuthentification yes/PasswordAuthentification no/' /etc/ssh/sshd_config
sed -ie 's/#PubkeyAuthentication/PubkeyAuthentication/' /etc/ssh/sshd_config

touch /etc/network/interfaces.d/enp0s8
echo 'iface enp0s8 inet static' > /etc/network/interfaces.d/enp0s8
echo 'address 192.168.1.42' >> /etc/network/interfaces.d/enp0s8
echo 'netmask 255.255.255.252' >> /etc/network/interfaces.d/enp0s8

echo -e "${sgreen}[Confuguration is done. Reboot? y/n]${egreen}"
read item
case "$item" in
    y|Y) echo -e "${sgreen}[Reboot...]${egreen}"
    reboot
    ;;
    n|N) echo -e "${sgreen}[Need reboot later...]${egreen}"
    ;;
esac
