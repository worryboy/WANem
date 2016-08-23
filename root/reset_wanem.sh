#!/bin/bash
trap "" SIGINT SIGTERM SIGSTOP SIGTSTP
echo -n "Stopping All... "
#/etc/init.d/apache2 stop >/dev/null 2>&1
service apache2 stop
echo "Done"

var=`ifconfig -a -s|grep -v Iface|grep -v lo|cut -d  " " -f1`
if [ -z "$var" ];
then
   echo "No network Interface found..... Exiting"
   exit;
fi

# Commented the eth_setup call as the network configuration will be handled using the network manager

# for i in $var;
# do
#        ifconfig $i down > /dev/null 2>&1
# done

# rm -f eth_setup.sh
# /root/eth_setup $var

# Reset the network settings using network manager
/root/networkReset.sh

if [ -f eth_setup.sh ];
then
    echo -n "IP Address Setting... "
     \rm -rf tempf
     /root/eth_setup.sh >tempf 2>&1
     if [ -s tempf ];
     then
	echo "failed"
     else
        echo "ok"
     fi
     \rm -rf tempf
fi   
#./do_putty.sh

clear
echo "ok"
echo -n "Network settings... "
echo 1 > /proc/sys/net/ipv4/ip_forward
echo 0 > /proc/sys/net/ipv4/conf/default/send_redirects
echo 0 > /proc/sys/net/ipv4/conf/all/send_redirects

for i in $var; 
do
  intf="/proc/sys/net/ipv4/conf/$i/send_redirects";
  echo 0 > $intf
done
echo "Done"

echo -n "Apache Start... "
 /etc/init.d/apache2 start > /dev/null 2>&1
echo "Done"

rm -f eth_setup.sh

echo "Reset Successful";
exit;
