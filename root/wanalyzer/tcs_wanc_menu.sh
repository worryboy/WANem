#!/bin/bash

#No of packets to be sent in one ping call
COUNT=1

#Interval of analysis of results in seconds
INTERVAL=5

#First call to awk file, 1 means FIRST CALL to the AWK Script is true
FIRST_CALL=1

#Number of ICMP packets sent in each duration/interval
ICMP_SENT=0
TARGET_IP=$1

echo "TCS WANALYSER RESULTS" > "/tmp/tcs_wanc_report.csv"
echo "..................................................." >> "/tmp/tcs_wanc_report.csv"
echo >> "/tmp/tcs_wanc_report.csv"
echo "Remote host IP: $TARGET_IP" >> "/tmp/tcs_wanc_report.csv"

var=`ping -c $COUNT $TARGET_IP | grep "100%"`
if [ -z "$var" ]
then
	echo "Remote host IP,$TARGET_IP,"
	/root/wanalyzer/tcs_wanc_main.sh $TARGET_IP 
	/root/wanalyzer/tcs_bw_main.sh $TARGET_IP
	#rm -f /tmp/*.dmp
else
   	echo "0"
fi
