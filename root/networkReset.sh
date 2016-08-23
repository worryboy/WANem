#!/bin/bash
# This script resets the NIC using the network 
# manager command line utility nmcli

# Shutdown the device
for i in `/usr/bin/nmcli -t -f UUID con`
do
	/usr/bin/nmcli con down uuid $i 2>/dev/null 1>/dev/null
done

# Sleep for few seconds before attempting a restart of the device
sleep 3

# Restart the device
for i in `/usr/bin/nmcli -t -f UUID con`
do
	/usr/bin/nmcli con up uuid $i 2>/dev/null 1>/dev/null
done
