#
BEGIN {
	pcount_low = 1; #Packets
	pcount_high = 1; #Packets

	avg_latency = 0.0;
	loss_perc = 0.0
	sum_rtt1 = 0;
	sum_rtt22 = 0;
	sum_bytes = 0;
}
/bytes/ {#row containing  $size bytes
	
	split($5, _seqno,"=");
	seqno = _seqno[2];
	
	split($7, _rtt ,"=");
	rtt = _rtt[2];

	if (FILENAME == ARGV[1]) {

		if (seqno > 0) {

			arr_low[pcount_low] = rtt;

			pcount_low++;

		}
	}
		
	if (FILENAME == ARGV[2]) {

		if (seqno > 0) {

			arr_high[pcount_high] = rtt;

			pcount_high++;

		}
	}	
}
END {#Summary results
	psize=(PSIZE*8)/(1024*1024);#Mb

	#print PSIZE, psize;	
	
	rtt1_low = arr_low[1];
	rtt1_high = arr_low[1];

	for (i=2; i < pcount_low; i++) {
		
		if (arr_low[i] < rtt1_low) rtt1_low = arr_low[i];

		if (arr_low[i] > rtt1_high) rtt1_high = arr_low[i];
	}

	#print rtt1_low, rtt1_high;

	rtt2_low = arr_high[1];
	rtt2_high = arr_high[1];

	for (i=2; i < pcount_high; i++) {
		
		if (arr_high[i] < rtt2_low) rtt2_low = arr_high[i];

		if (arr_high[i] > rtt2_high) rtt2_high = arr_high[i];
	}

	#diff_low = rtt2_low - rtt1_low;

	diff_low = (rtt2_low - rtt1_low)/1000;#second

	bw_low =2*(psize/diff_low);

        diff_high = (rtt2_high - rtt1_high)/1000;#second

        bw_high =2*(psize/diff_high);

	#bw_low = 381.47/diff_low;

	#diff_high = rtt2_high - rtt1_high;

	#bw_high = 381.47/diff_high;

	avl_bw = (bw_high > bw_low) ? bw_high : bw_low;

	if (avl_bw < 0) {
		print "Available Band Width,", "can't measure.Repeat", "Mbps";

		print "Avl_bw=","can't measure.Repeat","Mbps">>"/tmp/tcs_wanc_report.csv";
	}
	else {
		print "Available Band Width,", avl_bw, "Mbps";
		print "Avl_bw=",avl_bw,"Mbps">>"/tmp/tcs_wanc_report.csv";
	}
}

