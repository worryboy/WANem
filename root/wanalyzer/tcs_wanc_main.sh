#!/bin/bash

#HOSTS="172.19.140.79"
HOSTS=$1

#No of packets to be sent in one ping call
COUNT=1
#size=64

#Interval of analysis of results in seconds
INTERVAL=5

#First call to awk file, 1 means FIRST CALL to the AWK Script is true
FIRST_CALL=1

#Number of ICMP packets sent in each duration/interval
ICMP_SENT=0

#trap 'exit' INT
trap 'break' TSTP

#In seconds since 1970
START=$(date +%s)

while [ $0 ]
do
	
	#ping -c $COUNT -s $size $HOSTS|grep 'bytes'|grep 'from'|awk -f create_ping_dump.awk 
	if [ $ICMP_SENT -eq 0 ]; then 
		ping -c $COUNT $HOSTS | grep 'bytes' | grep 'from' | awk '{print $0 > "/tmp/tcs_wanc_dump.dmp"}' 
	else 
		ping -c $COUNT $HOSTS | grep 'bytes' | grep 'from' | awk '{print $0 >> "/tmp/tcs_wanc_dump.dmp"}' 
	fi

	ICMP_SENT=$(($ICMP_SENT+$COUNT))
	
	END=$(date +%s)
	
	DIFF=$(($END-$START))
	#echo "It took $DIFF Seconds...."
	
	if  [ $DIFF -ge $INTERVAL ]; then

		#Time of analysis in hh:mm:ss(24 hour) format		
		ANAL_TIME=$(date +%T)
		
		awk -f /root/wanalyzer/tcs_wanc_analyser.awk -v start_time=$ANAL_TIME icmp_sent=$ICMP_SENT call=$FIRST_CALL /tmp/tcs_wanc_dump.dmp
		
		FIRST_CALL=0
		
		ICMP_SENT=0

		START=$(date +%s)

		#echo -n "TERMINATE ? (y=1/n=0) : "
		break	
		
	fi
done
#read q
