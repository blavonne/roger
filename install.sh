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

echo -e "${sgreen}[Add barrett? y/n]${egreen}"
read item
case "$item" in
  y|Y)
sed -ie '/^# Allow members/i barrett ALL=(ALL:ALL) ALL' /etc/sudoers
echo -e "${sgreen}[barrett added to sudoers]${egreen}"
  ;;
  n|N)
  ;;
esac

echo -e "${sgreen}[Change network config? y/n]${egreen}"
read item
case "$item" in
  y|Y)
touch /etc/network/interfaces.d/enp0s8
echo -e "${sgreen}[enp0s8-config created.]${egreen}"
echo 'auto enp0s8' >> /etc/network/interfaces
echo 'iface enp0s8 inet static' >> /etc/network/interfaces.d/enp0s8
echo 'address 192.168.56.2' >> /etc/network/interfaces.d/enp0s8
echo 'netmask 255.255.255.252' >> /etc/network/interfaces.d/enp0s8
echo -e "${sgreen}[enp0s8-config set.]${egreen}"
  ;;
  n|N)
  ;;
esac

echo -e "${sgreen}[Change SSH config? y/n]${egreen}"
read item
case "$item" in
  y|Y)
sed -ie 's/#Port 22/Port 50000/' /etc/ssh/sshd_config
echo -e "${sgreen}[Port changed.]${egreen}"
sed -ie 's/#PermitRootLogin prohibit-password/PermitRootLogin no/' /etc/ssh/sshd_config
echo -e "${sgreen}[PermitRootLogin changed.]${egreen}"
sed -ie 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
echo -e "${sgreen}[PasswordAuthentication changed.]${egreen}"
sed -ie 's/#PubkeyAuthentication/PubkeyAuthentication/' /etc/ssh/sshd_config
echo -e "${sgreen}[PubkeyAuthentication changed.]${egreen}"
  ;;
  n|N)
  ;;
esac

echo -e "${sgreen}[Reboot? y/n]${egreen}"
read item
case "$item" in
    y|Y) echo -e "${sgreen}[Reboot...]${egreen}"
    reboot
    ;;
    n|N) echo -e "${sgreen}[Need reboot later...]${egreen}"
    ;;
esac
