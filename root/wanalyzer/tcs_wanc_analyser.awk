#
BEGIN {
	count = 0; #Packets
	avg_latency = 0.0;
	loss_perc = 0.0
	sum_rtt = 0;
	sum_bytes = 0;
}
/bytes/{#row containing  $size bytes
	split($5, _seqno,"=");
	seqno = _seqno[2];
	
	split($7, _rtt ,"=");
	rtt = _rtt[2];

	#This array is used for calculation of jitter.
	#Stores the one-way latency which is rtt/2 (approximately).
	arr[count] = rtt/2;

	count++; 
	#sum_bytes += 2*$1;
	sum_rtt += rtt; #in milli second
}
END {#Summary results

	#Calcuate average latency. Latency is rtt/2. 
	avg_latency = sum_rtt/(2*count);


	#Calculate percentage of loss
	loss = icmp_sent - count;
	loss_perc = (loss/icmp_sent)*100;

	#Calculate Jitter-----------------------------
	
	# Calculate mean: x{-}
	mean_latency = avg_Latency;

	sum_sqr = 0;

	# Calculate summation of (x{i}-x{-})^2
	for (i = 0; i < count; i++) {
		diff = arr[i] - mean_latency;
		sum_sqr += (diff * diff);
	}

	jitter = sqrt(sum_sqr/count);

	print "Time of measurement,",start_time,",";
	#print "..................................................";
	print "Latency,",avg_latency,"ms,";
	#print "..................................................";
	print "Loss of packet,",loss_perc,"%,";
	#print "..................................................";
	print "Jitter,",jitter,",";
	#print "..................................................";

	print "Time:",start_time>>"/tmp/tcs_wanc_report.csv";
	print "Latency=",avg_latency,"ms">>"/tmp/tcs_wanc_report.csv";
	print "Loss(%)=",loss_perc,"%">>"/tmp/tcs_wanc_report.csv";
	print "Jitter=",jitter>>"/tmp/tcs_wanc_report.csv";
}

