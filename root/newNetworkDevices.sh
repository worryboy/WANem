#!/bin/bash
#
# This script lists all the available network devices
# /proc/net/dev file is used to identify the available network devices
# The script further attempts to assign DHCP to all the devices
#

# Counter variable
count=0

# Get the total number of lines from the /proc/net/dev file
totalLines=`cat /proc/net/dev | grep -v lo | wc -l`

# Remove the two header lines from the /proc/net/dev file
requiredLines=`expr $totalLines - 2`

# Run a loop to start the network devices
availableDevices=`cat /proc/net/dev | tail -$requiredLines | awk '{ print $1 }' | cut -d: -f1`
availableDevicesCount=`cat /proc/net/dev | tail -$requiredLines | awk '{ print $1 }' | cut -d: -f1 | wc -l`

# Check if the network interface got configured
activeDevices=`ifconfig -s | grep -v lo | grep -v Iface | wc -l`

# Restart the network manager if for some reason it is  unable to acquire IP addresses for the NICs 
if [ $availableDevicesCount != $activeDevices ]
then
# Attempt a network manager restart
	/etc/init.d/network-manager restart 2>/dev/null 1>/dev/null
fi

# Sleep for few seconds 
sleep 3;

# Compare with the count variable
if [ $availableDevicesCount != $activeDevices ]
then
	echo -e "\033[31mNetwork Device(s) could not be configured using DHCP"
	echo -e "\033[31mPlease configure them manually using 'reset' command from WANem console."
	echo -e "\033[31mIf you want to attempt DHCP restart the network manager" 

	sleep 5

# Open the modified eth_setup for configuring the NICs
#	/root/modified_eth_setup $availableDevices
fi
