BEGIN {
	i = 0;
}
/=/{
	print $0;
	a[i++] = $2;
}
END {
	if (latency == "ms") latency=0;
	latency = int(a[0])"ms";

	loss = a[1]"%";

	jitter = a[2]"ms";

	_bw = a[3]*1024;
	bw = int(_bw)"kbit";

	#print latency, loss, jitter, bw;

	str = "eth0 "latency" "loss" "jitter" "bw;
	
	cmd = "/root/wanalyzer/tcs_wanem.sh "str;

	print str;
	 
	system(cmd);

	#print 1 "sudo /sbin/tc qdisc add dev eth0 root handle 1: netem delay" $LATENCY $JITTER > "/tmp/netemstate.txt"; 
	#print "sudo /sbin/tc qdisc add dev eth0 parent 1:1 handle 10: netem loss" $LOSS >> "/tmp/netemstate.txt";
	#print "sudo /sbin/tc qdisc add dev eth0 parent 10:1 handle 20: htb default 1" >> "/tmp/netemstate.txt";
	#print "sudo /sbin/tc class add dev eth0 parent 20: classid 0:1 htb rate" $BW "ceil" $BW >> "/tmp/netemstate.txt";
}
