#!/bin/bash
trap "" SIGINT SIGTERM SIGSTOP SIGTSTP
while [ 1 ];
do
   echo -n "WANemControl@PERC>"
   read str;
   case "$str" in
   help)
	 echo "about -- About WANem"
	 echo "assign -- Assign a device to a host. usage: assign IPAddr device"
	 echo "bridge -- Enable/disable bridge for the available interfaces"
	 echo "clear -- Clear the Screen"
	 echo "exit2shell -- Go to shell."
	 echo "nat -- Help for enabling WANem to work across subnets."
	 echo "reset -- Reset the network setting and services"
	 echo "restart -- Restart the System"
	 echo "shutdown -- Shutdown the System"
	 echo "startx -- Start the LXDE Desktop Manager"
	 echo "status -- Check the status of Network settings and services"
	 echo "wanem -- Return to WANem shell."
	 echo "wanemreset -- Reset WANem settings if the GUI is very slow"
         echo "help -- Displays this help."
	 echo 
         ;;
   nat*)
         command=`echo $str|cut -d " " -f2`
   	 case "$command" in
	 show)
	 	ifs=`/sbin/iptables -t nat -L -v | awk '/MASQUERADE/{print $7;}'`
		if [ $ifs ]
		then
			echo -n "NAT enabled interface(s) in WANem :"
			for i in "$ifs" ;
			do
				echo $i
			done
		else
			echo "No NAT enabled interface in WANem"
		fi
		;;
	 add)
         	if=`echo $str|cut -d " " -f3`
       	 	interfaces=`ifconfig -s -a |grep -v Iface|grep -v lo|cut -d " " -f1`
	 	natif=`/sbin/iptables -t nat -L -v | awk '/MASQUERADE/{print $7;}'|cut -d " " -f1`

		if echo "$interfaces"|grep -q "$if"
		# 'if' found in 'interfaces'
		then
			if echo "$natif"|grep -q "$if"
			# 'if' found in 'natif'
			then
				echo "$if is already NAT enabled."
			else
				#add rules
	 			/sbin/iptables -t nat -A POSTROUTING -o $if -j MASQUERADE > /dev/null 2>&1
			fi
		else
			echo "$if is not a valid interface name."
		fi
   	 	;;
	 del)
         	if=`echo $str|cut -d " " -f3`
       	 	interfaces=`ifconfig -s -a |grep -v Iface|grep -v lo|cut -d " " -f1`
	 	natif=`/sbin/iptables -t nat -L -v | awk '/MASQUERADE/{print $7;}'|cut -d " " -f1`

		if echo "$interfaces"|grep -q "$if"
		# 'if' found in 'interfaces'
		then
			if echo "$natif"|grep -q "$if"
			# 'if' found in 'natif'
			then
				#delete rule
	 			/sbin/iptables -t nat -D POSTROUTING -o $if -j MASQUERADE > /dev/null 2>&1 	 
			else
				echo "$if is already NAT disabled."
			fi
		else
			echo "$if is not a valid interface name."
		fi
   	 	;;
	 help)
	 	echo
	 	echo "Use the following commands to use WANem across multiple subnets:"
	 	echo "nat add <interface-name> --  Enable network address translation (nat) on this interface"
		echo "nat del <interface-name> --  Disable network address translation (nat) on this interface"
		echo "nat show -- List nat enabled interfaces"
		echo "nat help -- nat help"
		echo
	 	;;
	 *)
	 	echo "nat: invalid option"
		echo "Try 'nat help' for more information."
		;;
	 esac
	 ;;
   assign*)
        ip=`echo $str|cut -d " " -f2`
        dev=`echo $str|cut -d " " -f3`
        root/ip_dev.sh $ip $dev
	;;
  about)
        more /var/www/WANem/About.txt
	;;
   reset)
         cd /root
         ./reset_wanem.sh
	 cd ..
	 ;;
   shutdown)
         init 0
	 ;;
   startx)
	 startx > /dev/null 2>&1
	 ;;
   restart)
         init 6
	 ;;
   clear)
         clear
	 ;;
   #dontquit)
   #      read -s pw;
   #	 if [ "$pw" = "rmdahod@perc" ]; 
   #	 then
   #	      exit 0
   #	 fi     
   #	 ;;
   exit2shell)
       echo "Type 'wanem' to return to WANem console"	
       exit 0
       ;;	
   status)
       clear
       echo "IP Settings"
       echo "========================================================================="
       ifconfig -a|more
       echo "========================================================================="
       echo -n "Press any key to continue"
       read -s x
       clear
       echo "Route Settings"
       echo "========================================================================="
       route -n
       echo "========================================================================="
       tempstr=`ps -el|grep apache2`
       echo -n "Apache ..... "
       if [ -z "$tempstr" ];
       then
          echo "down";
	  echo "WANem can't be accessed, reset to start  it first"
       else
          echo "running";
       fi
       tempstr=`ps -el|grep sshd`
       echo -n "SSH Server ..... "
       if [ -z "$tempstr" ];
       then
	  echo "down";
       else 
	  echo "up"
       fi
       echo -n "Enter IP Address to test reachability(q to skip): ";
       read ip;

# Check if ip value is null before attempting comparison or ping
	if [ ! -z "$ip" ]; then
      	 if [ $ip != "q" ]; then
          	tempvar=`ping -c 1 $ip | grep loss | cut -d "," -f3 | cut -d " " -f2`
	  	if [ -z $tempvar ]
	  	then
	     		echo -e "\033[31mWrong IPAddress. \033[0m"
			echo " "
	  	else
              	if [ $tempvar = "0%" ]
              	then
	       		echo -e "\033[32m$ip reachable. \033[0m"
			echo " "
              	else
               		echo -e "\033[31m$ip not reachable. Check network settings. \033[0m"
			echo " "
              	fi
	   	fi   
      	 fi	  
	fi
       ;;
   wanemreset)
       /root/wanem_reset.sh
       ;;
   "")
        ;;
   bridge*)
		command=`echo $str | cut -d " " -f2`
		case $command in
		help)
			echo "bridge show -- Shows the status of the available bridge"
			echo "bridge add <BridgeName> <Device1> <Device2> ... <DeviceN>-- Start the bridge"
			echo "bridge del <BridgeName> -- Stop the running bridge"
			echo "bridge help -- Displays this help message"
			echo ""
		;;
		show)
			echo "Displaying the available bridges ..."
			/usr/sbin/brctl show
		;;
		add)
			# Get Bridge Name and the NIC list
			flag=0
			nicList=""
			for i in $str
			do
				if [ $flag -eq 2 ]
				then
					bridgeName=$i
				fi
		
				if [ $flag -gt 2 ]
				then
					nicList=`echo $nicList $i`
				fi
			
				flag=`expr $flag + 1`
			done
			
			if [ ! -n "bridgeName" ] || [ ! -n "$nicList" ]
			then
				echo "Please provide all the parameters required for starting the bridge"
				echo "Use 'bridge help' for more details"
			else

				# Get the eth0 IP address for the bridge
				bridgeIP=`/sbin/ifconfig eth0 | grep inet | grep -v inet6 | awk '{ print $2 }' | awk -F: '{ print $2 }'`
				netmaskIP=`/sbin/ifconfig eth0 | grep inet | grep -v inet6 | awk '{ print $4 }' | awk -F: '{ print $2 }'`
				gatewayIP=`/sbin/route -n | grep UG | grep eth0 | awk '{ print $2 }'`
			
				echo "IP Address of bridge is set to $bridgeIP. Please reopen the browser using this IP address"

				# Zero-in the IP address of all the NICs
				for nic in $nicList
				do
					/sbin/ifconfig $nic down
					/sbin/ifconfig $nic 0.0.0.0 up
				done

				# Create bridge
					/usr/sbin/brctl addbr $bridgeName
			
				# Change bridge attributes
					/usr/sbin/brctl stp $bridgeName on
					/usr/sbin/brctl setfd $bridgeName 0
		
				# Add NICs to the bridge
				for nicName in $nicList
				do
					/usr/sbin/brctl addif $bridgeName $nicName
				done

				# Bring up the bridge
					/sbin/ifconfig $bridgeName $bridgeIP netmask $netmaskIP up 2>/dev/null
					/sbin/route add default gw $gatewayIP $bridgeName 2>/dev/null

				# Display the status of the bridge
					/usr/sbin/brctl show
			fi
		;;
		del)
			# Get the bridge name
			bridgeName=`echo $str | cut -d " " -f3`
			
			# Get the NICs associated with the bridge
			flag=1
			nicList=""
			for nic in `/usr/sbin/brctl show $bridgeName | grep -v STP`
			do
				if [ $flag -ge 4 ]
				then
					nicList=`echo $nicList $nic`
				fi
				flag=`expr $flag + 1`
			done	
		
			# Bring down the bridge
			/sbin/ifconfig $bridgeName down
			/usr/sbin/brctl delbr $bridgeName

			# Attempt DHCP for the NICs
			for nicname in $nicList
			do
				/sbin/ifconfig $nicname down
				/sbin/pump -i $nicname
				/sbin/ifconfig $nicname up
			done
			
			# Check for the IP addresses
			for i in $nicList
			do	
				lineCount=`/sbin/ifconfig $i | grep -inet | grep -v inet6 | wc -l`
				if [ $lineCount -lt 1 ]
				then
					echo "DHCP for $i failed."
				else
					temp=`/sbin/ifconfig $i | grep inet | grep -v inet6 | awk '{ print $2 }' | awk -F: '{ print $2 }'`
					echo "IP for $i : $temp"
				fi
			done				
		;;
		*)
			echo "Invalid Option ..."
			echo " "
			echo "Use any of the following available options ..."
			echo "bridge show -- Shows the status of the available bridge"
			echo "bridge add <BridgeName> <Device1> <Device2> . . . <DeviceN> -- Start the bridge"
			echo "bridge del <BridgeName> -- Stop the running bridge"
			echo "bridge help -- Displays help message"
			echo " "
		;;
		esac
	;;
   *)
        echo "Invalid Command...."
	echo "Type help to get a list of commands"
	;;
   esac
 done
 

        
       
