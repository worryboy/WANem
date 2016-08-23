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

for value in $availableDevices
do

# Remove the trailing : from the network device name
	newValue=`echo $value | cut -d: -f1`
	linkDetected=`ethtool $newValue | tail -1 | awk '{ print $3 }'`

	if [ $newValue != 'lo' -a $linkDetected == 'yes' ]
	then

# Bring down the interface if it is up
		/sbin/ifconfig $newValue down 2>/dev/null

# Bring up the network interface other than lo
		/sbin/pump -i $newValue 2>/dev/null
		/sbin/ifup $newValue  2>/dev/null
# Increment the counter variable
		count=`expr $count + 1`

	fi

# Complete the for loop
done

# Check if the network interface got configured
availableDevicesCount=`ifconfig -s | grep -v lo | grep -v Iface | wc -l`

echo count: $count
echo availableCount: $availableCount

# Compare with the count variable
if [ $count != $availableDevicesCount ]
then
	echo -e "\033[31mNetwork Devices could not be configured using DHCP"
	echo -e "\033[31mPlease configure them manually"

	sleep 3

	#/root/modified_eth_setup $availableDevices
fi
