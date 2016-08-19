#/****************************************************************************/
#/*                              TCS WANem                                   */

#/****************************************************************************/
#/*                   COPYRIGHT (c) 2007 TCS All Rights Reserved             */
#/*                                                                          */
#/*                                                                          */
#/* WANem is a WAN emulation tool Conceptualized and developed by Innovation */
#/* LAB TCS. We thank the open source community as we have taken the inspira */
#/* tion from the Netem, the open source network emulator.                   */
#/*                                                                          */
#/*                                                                          */
#/****************************************************************************/
#/****************************************************************************/
#/*   Author       : Manoj Nambiar, TCS Innovation Lab Performance Engg.     */

#/*   Date         : March 2007                                              */
#/*   Synopsis     : disc.awk                                                */
#/*   Description  :                                                         */
#/*                                                                          */
#/*   Modifications:                                                         */
#/****************************************************************************/
#/*                                                                          */

BEGIN { 
	ilines = 1; 
	rlines = 1; 
	trackrules = 0; 
	rulenum = 0;
	ip_rules = 0;
	rnd = randy/32767.0;
	print "Random = ", rnd;
	cmd = "touch timers.out";
	system(cmd);
}

(FILENAME == "firewall.out") {
	if ($1 == "Chain") {
		if ($2 == "FORWARD")
			trackrules = 1;
		else
			trackrules = 0;
		next;
	}

	if ((trackrules) && ($1 != "num") && (NF > 4) ) {
		rulenum++; /* 4 is just a conservative no */
		ispt = index($0, "spt");
		idpt = index($0, "dpt");
		if (!(ispt || idpt)) {
			ip_rules++;
			fw_iprules_src[ip_rules] = $5;
			fw_iprules_dst[ip_rules] = $6;
		}
	}

	print "rulenum = ", rulenum;
	print "ip_rules = ", ip_rules;
}

(FILENAME == "timers.out") {

	rule_expiry[$1] = $2 - 1;
	print "timers.out ", $1, $2;
}

(FILENAME == "input.dsc") {
	if ($1 == "IDL") {
		idle_time[ilines] = $2;
		idl_disc_duration[ilines] = $3;
		iip1[ilines] = $4;
		iip2[ilines] = $5;
		ireason[ilines] = $6;
		ilines++;
	} else if ($1 == "RAND") {
		
		disc_prob[rlines] = $2;
		rnd_disc_duration[rlines] = $3;
		rip1[rlines] = $4;
		rip2[rlines] = $5;
		rreason[rlines] = $6;
		if (rnd < disc_prob[rlines]) {
			ip_rule_match = 0;
			for(i = 1;i <= ip_rules; i++) {
				c1 = (rip1[rlines] == fw_iprules_src[i]);
				c2 = (rip2[rlines] == fw_iprules_dst[i]);
				print "Rule matching = ", i, " rip1 = ", rip1[rlines], " rip2 = ", rip2[rlines]
				print  "fw_iprules_src = ", fw_iprules_src[rlines], " fw_iprules_dst = ", fw_iprules_dst[rlines]
				if (c1 && c2) { 
					ip_rule_match = 1;
					print "Rules matched = ",i;
					break;
				}
			}
			if (!(ip_rule_match)) {
				rulenum++;
				if (rip1[rlines] == "anywhere")
					cmd = sprintf("iptables -I FORWARD %d -d %s -j REJECT --reject-with %s", rulenum,
						rip2[rlines], rreason[rlines]);
				else if (rip2[rlines] == "anywhere")
					cmd = sprintf("iptables -I FORWARD %d -s %s -j REJECT --reject-with %s", rulenum,
						rip1[rlines], rreason[rlines]);
				else
					cmd = sprintf("iptables -I FORWARD %d -s %s -d %s -j REJECT --reject-with %s", rulenum,
						rip1[rlines], rip2[rlines], rreason[rlines]);
				system(cmd);
				rule_expiry[rulenum] =  rnd_disc_duration[rlines];	
			}
		}
		rlines++;
	}
	print "ilines = ",ilines;
}

(FILENAME == "ctrack.out") {
		time_to_live = net_ttl - $3;	
		print "time to live = ", time_to_live, net_ttl;
		print "idle time [1] = ", idle_time[1];

		for(i = 1; i <= ilines; i++)
			if (time_to_live >= idle_time[i]) {
			split($5, a, "="); 
			split($6, b, "="); 
			ip1=a[2];ip2=b[2];
			print "iip1[i] = ", iip1[i], " iip2[1] = ", iip2[i], " ip1 = ", ip1, " ip2 = ", ip2;
			c1 = ((iip1[i] == ip1) || (iip1[i] == "anywhere"));
			c2 = ((iip1[i] == ip2) || (iip1[i] == "anywhere")) ;
			c3 = ((iip2[i] == ip1) || (iip2[i] == "anywhere"));
			c4 = ((iip2[i] == ip2) || (iip2[i] == "anywhere"));
			c5 = (c1 || c2);
			c6 = (c3 || c4);
				if (c5 && c6) {
					print "Main AAA gaya";
					split($7, a, "="); 
					split($8, b, "="); 
					sport = a[2]; dport = b[2];
					rulenum++;	
					params = sprintf("-p tcp -s %s -d %s --sport %d --dport %d ", ip1, ip2, sport, dport);	
					cmd = sprintf("iptables -I FORWARD %d %s -j REJECT --reject-with %s", rulenum, params, ireason[i]);
					print cmd;
					system(cmd);
					#system("sleep 30");
					#cmd = sprintf("iptables -D FORWARD %d", rulenum);
					#print cmd;
					#system(cmd);
					cmd = sprintf("conntrack -D -p tcp -s %s -d %s --orig-port-src %d --orig-port-dst %d", ip1,ip2,sport,dport);
					print cmd;
					system(cmd);
					rule_expiry[rulenum] = idl_disc_duration[i];
				}
			}
}

END{
	system("rm timers.out");
	for(i in rule_expiry) {
		print "Rules Expiry = ", i, rule_expiry[i];
		if (rule_expiry[i] <= 0) {
			cmd = sprintf("iptables -D FORWARD %d", i);
			system(cmd);
		}
		else 
			print i, rule_expiry[i] >> "timers.out";
	}
}
