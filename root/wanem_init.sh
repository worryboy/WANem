#!/bin/bash
flagFile="/root/flagFile"
trap "" SIGINT SIGTERM SIGSTOP SIGTSTP
if [ ! -f $flagFile ]
then
	var1=`ifconfig -a -s|grep -v Iface|grep -v lo|cut -d  " " -f1|wc -l`
	if [ $var1 == 0 ]; 
	then 
		echo "Either there are no interfaces ...OR.... WANem must not be having drivers to detect your NICs"; 
		echo "* If you are booting WANem off the CD you might want to load the drivers via your USB drive"
		echo "* If you are booting WANem as a virtual machine then you need to add a network interface for WANem using your virtualization software"
		exit 0
	fi

# Check if the network interface is already configured. If so, do not attempt a DHCP 
	if [ `ifconfig | grep -v 127.0.0.1 | grep "inet addr" | wc -l` -lt 1 ]
	then
		echo
		echo -en "\033[1;32mConfiguring network devices..."
		 /etc/init.d/network-manager restart 2>/dev/null 1>/dev/null
		echo -e " Done\033[0m"
	fi

	# Check if the NIC have IP addresses
	/root/newNetworkDevices.sh

	echo -en "\033[1;32mNetwork settings... "
	echo 1 > /proc/sys/net/ipv4/ip_forward
	echo 0 > /proc/sys/net/ipv4/conf/default/send_redirects
	echo 0 > /proc/sys/net/ipv4/conf/all/send_redirects
	echo 0 > /proc/sys/net/ipv4/conf/eth0/send_redirects
	echo 1 > /proc/sys/net/ipv4/ip_no_pmtu_disc
	echo -e "Done\033[0m"

	echo -en "\033[1;32mMTU=1500 settings... "
	for i in $var; 
	do
  		intf="/proc/sys/net/ipv4/conf/$i/send_redirects";
  		echo 0 > $intf
  		ifconfig $i mtu 1500
	done
	echo -e "Done\033[0m"

	echo -e "\033[1;32mChecking the IP addresses of NIC(s)... \033[0m"
	# Sleep for sufficient time to allow the network interfaces to come up
	sleep 10

	/root/newCheckIPAddress.sh

	if [ `ps -aef | grep /etc/init.d/apache2 | grep -v grep | wc -l` -lt 0 ]
	then
		echo ""
		echo -en "\033[1;32mStarting Apache... "
		/etc/init.d/apache2 start > /dev/null 2>&1
		echo "Done"
		echo -e "\033[0m"
	fi


	if [ `ps -aef | grep ajaxterm | grep daemon | grep -v grep | wc -l` -lt 1 ]
	then
		echo -en "\033[1;32mStarting Remote Terminal daemon (ajaxterm)... "
		/etc/init.d/ajaxterm start > /dev/null 2>&1
		echo "Done"
		echo -en "\033[0m"
		echo " "
	fi

	rm -f eth_setup.sh

	echo -e "\033[0m= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = ="
	echo -en "\033[0;37m Use the \033[0;35mWANem shell\033[0;37m for \033[1;37mAdministration. \033[0;37mType " 
	echo -e "\033[1;33mhelp \033[0;37mfor list of commands"
	echo -en " Access WANem from any machine by using \033[0;36mhttp://<IP of this machine>/WANem\033[0;37m"
	echo -en "\n= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =\n"
	echo ""

sleep 3

# Start the LXDE desktop
/usr/bin/startx > /dev/null 2>&1

# Create the flagFile for avoiding the this loop when remote terminal is opened
	echo 1 > /root/flagFile

fi

/root/displayIP.sh
/root/wanem.sh

