#!/bin/bash
if [ -z $2 ];
then
   echo "usage: assign <ipaddr> <device>"; 
   exit 0;
fi
var=`ifconfig $2|grep "inet addr"|cut -d ":" -f2|cut -d " " -f1`
if [ -z $var ];
then
    echo "$2 not a configured Interface"
    echo "usage: assign <ipaddr> <device>"; 
else
    route add -host $1 netmask 0.0.0.0 gw $var dev $2
fi
