#!/bin/bash
# 
# This script checks if the auto network setup script was able to get IP addresses for all the available 
# network interfaces (except lo) using DHCP
# If auto DHCP has failed then this script prompts for a manual setup
#
# Sleep for sometime 
sleep 5

# Global variable for updating the status
flag=0
failFlag=0

# Get the count of network devices available for configuration
numberOfLines=`cat /proc/net/dev | wc -l`

# Get the count of lines after removing the three (3) header lines
count=`expr $numberOfLines - 3`

# Get the list of available interfaces for configuration. 
# This variable is used later if the devices are required to be configured manually
for nic in `cat /proc/net/dev | grep -v lo | grep -v face | grep -v Receive | cut -d: -f1`
do
# Check of the devices is configured

	for loop in {1..15}
	do
	# Get the IP address		
		inet6Flag=`ifconfig $nic | grep inet | grep -v inet6 | wc -l`

		if [ $inet6Flag -eq 1 ]
		then
			IPAddress=`ifconfig $nic | grep inet | grep -v inet6 | cut -d: -f2 | awk '{ print $1 }'`
			echo -e "\033[32;1mIP Address for $nic: \033[1;33m$IPAddress\033[0m"
			break;
		else
			if [ $loop -eq 15 ]
			then
				failFlag=1;
				echo -e "\033[31mIP Address for $nic: Not Assigned.\033[0m"
			else
				continue
			fi
		fi

	# Sleep for few second before proceeding 
		sleep 4
	done
done
	if [ $failFlag -gt 0 ]
	then
		echo -e "\033[31m "
		echo -e "\033[31mPlease run the 'status' command to see if all the NICs have IP addresses." 
		echo -e "\033[31mIf required, use network manager for assigning IP addresses." 

sleep 6
	fi

# Reset the prompt color to original
echo -e "\033[0m"

