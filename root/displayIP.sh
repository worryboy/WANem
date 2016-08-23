#!/bin/bash

# Get the count of network devices available for configuration
numberOfLines=`cat /proc/net/dev | wc -l`

# Get the count of lines after removing the three (3) header lines
count=`expr $numberOfLines - 3`

# Get the list of available interfaces for configuration. 
for nic in `cat /proc/net/dev | grep -v lo | grep -v face | grep -v Receive | cut -d: -f1`
do
	echo -e "\033[1;30m+------+-----------------+\033[0m";
	echo -e "\033[1;30m| \033[0;35mName\033[1;30m | \033[1;34mIP Address\033[1;30m      |";
	echo -e "\033[1;30m+------+-----------------+\033[0m";
# Check of the devices is configured
	for loop in {1..1}
	do
	# Get the IP address		
		inet6Flag=`ifconfig $nic | grep inet | grep -v inet6 | wc -l`

		if [ $inet6Flag -eq 1 ]
		then
	IPAddress=`ifconfig $nic | grep inet | grep -v inet6 | cut -d: -f2 | awk '{ print $1 }'`
	echo $nic $IPAddress | awk '{ printf("\033[1;30m|\033[0m \033[0;35m%-4s\033[1;30m | \033[1;34m%-16s\033[1;30m|\n", $1, $2) }'
			break;
		fi
	done
		echo -e "\033[1;30m+------+-----------------+\033[0m";
done

# Reset the prompt color to original
echo -e "\033[0m"

