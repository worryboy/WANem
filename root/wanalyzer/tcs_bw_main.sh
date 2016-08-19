#!/bin/bash

HOSTS=$1

#No of packets to be sent in one ping call
COUNT=1

P2_SIZE=50000

FOUND=0

while [ $FOUND -eq 0  ]
do
        ping -c 1 -s $P2_SIZE  $HOSTS > /tmp/tempf 2>&1
        var=`cat /tmp/tempf| grep "100%"`
        rm -f tempf

        if [ -z "$var" ] 
	then
                FOUND=1
        elif [ $P2_SIZE -le 5000 ] 
		then			
			if [ $P2_SIZE -le 1000 ]
			then
                		P2_SIZE=$(($P2_SIZE - 100))
			else
                		P2_SIZE=$(($P2_SIZE - 1000))
			fi
		else
               		P2_SIZE=$(($P2_SIZE - 5000))
	fi
	if [ $P2_SIZE -le 32 ] 
	then
		FOUND=1
		P2_SIZE=32
	fi
done

P1_SIZE=$(($P2_SIZE / 2))

C=0
ping -c 2 -s $P1_SIZE $HOSTS|grep 'bytes'|grep 'from'|awk '{print $0 > "/tmp/tcs_bw_low.dmp"}' 
ping -c 2 -s $P2_SIZE $HOSTS|grep 'bytes'|grep 'from'|awk '{print $0 > "/tmp/tcs_bw_high.dmp"}' 

while [ $C -le $COUNT ] 
do
	ping -c 2 -s $P1_SIZE $HOSTS|grep 'bytes'|grep 'from'|awk '{print $0 >> "/tmp/tcs_bw_low.dmp"}' 
	#echo
	ping -c 2 -s $P2_SIZE $HOSTS|grep 'bytes'|grep 'from'|awk '{print $0 >> "/tmp/tcs_bw_high.dmp"}' 
	#echo $P2_SIZE, $P1_SIZE, $C
	C=$(($C + 1))
done

P=$(($P2_SIZE-$P1_SIZE))
awk -f /root/wanalyzer/tcs_bw_analyser.awk -v PSIZE=$P /tmp/tcs_bw_low.dmp /tmp/tcs_bw_high.dmp

