#!/bin/bash
ifconfig -s -a |grep -v Iface|grep -v lo|cut -d " " -f1 > intfs.txt
FILE="intfs.txt"
exec 3<&0
exec 0<$FILE
while read line
do
      tc qdisc del dev $line root > /dev/null 2>&1
done
/root/disc_new_port_int/reset_all_disc.sh /root/disc_new_port_int/
exec 0<&3
