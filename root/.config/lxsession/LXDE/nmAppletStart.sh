#!/bin/bash
# 
# This script checks if the auto network setup script was able to get IP addresses for all the available 
# network interfaces (except lo) using DHCP
# If auto DHCP has failed then this script prompts for a manual setup
#

# Global variable for updating the status
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

	for loop in {1..5}
	do
	# Get the IP address		
		inet6Flag=`ifconfig $nic | grep inet | grep -v inet6 | wc -l`

		if [ $inet6Flag -eq 1 ]
		then
			IPAddress=`ifconfig $nic | grep inet | grep -v inet6 | cut -d: -f2 | awk '{ print $1 }'`
			break;
		else
			if [ $loop -eq 5 ]
			then
				failFlag=1;
			else
				continue
			fi
		fi

	# Sleep for 1 second before proceeding 
		sleep 1
	done
done
	if [ $failFlag -gt 0 ]
	then
		/usr/bin/nm-connection-editor
	fi

