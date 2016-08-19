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
#/*   Synopsis     : disco.awk                                               */
#/*   Description  :                                                         */
#/*                                                                          */
#/*   Modifications:                                                         */
#/****************************************************************************/
#/*                                                                          */

BEGIN {
	file[1] = sprintf("%s/input.dsc", disc_dir);
	file[2] = sprintf("%s/ctrack.out", disc_dir);
	#file[2] = disc_dir "/ctrack.out";
	#print file[1], file[2];
	ilines = rlines = rclines = 1;
	rulenum = 0;
	x = 1;
}

function compute_timer(lo, hi) {

	range = hi - lo;
	offset = range*rand();
	#print "offset = ", offset;
	res = lo + offset;

	return int(res);
}

function process_rule_timers() {

	#print "Inside Process Rule Timers";

        for (i = 1; i <= rulenum; i++) {
		if (rule_expiry[i] > 0)
			rule_expiry[i] --;
		else {
			cmd = sprintf("iptables -D FORWARD %d", i);
			if (system(cmd) == 0) {
				rulenum--;
				for(k = i; k <= rulenum; k++) {
					rule_expiry[k] = rule_expiry[k+1];
					rule_type[k] = rule_type[k+1];
					rule_cross_index[k] = rule_cross_index[k+1];
					if (rule_type[k] == "RND") 
						r_rule[rule_cross_index[k]] = k;
					else if (rule_type[k] == "RDCONN") 
						rc_rule[rule_cross_index[k]] = k;
				}
			}
		}
	}
}

function process_rstate_timers() {
	#print "Inside Process Rstate Timers";
	for(ir = 1; ir < rlines; ir++) {
		#print "ir = ",ir,"Timer = ", rstate_timer[ir];
		if (rstate[ir] == 0) {
			if (rstate_timer[ir] >0) rstate_timer[ir]--;
			if (rstate_timer[ir] == 0) {
				rstate_timer[ir] = 0;
				if (rport2[ir] == 0)
					if (rreason[ir] == "tcp-reset") {
						pstr = "-p tcp ";
						pstrb = "-p tcp ";
					}
					else {
						pstr = "";
						pstrb = "";
					}
				else {
					pstr = sprintf("-p tcp --dport %d ", rport2[ir]);
					pstrb = sprintf("-p tcp --sport %d ", rport2[ir]);
				}
				if ((rip1[ir] == "anywhere") && (rip2[ir] == "anywhere")) {
                                        cmd = sprintf("iptables -I FORWARD %d -o %s %s -j REJECT --reject-with %s", rulenum + 1,
							rintf[ir], pstr, rreason[ir]);
                                        cmdb = sprintf("iptables -I FORWARD %d -o %s %s -j REJECT --reject-with %s", rulenum + 1,
							rintf[ir], pstrb, rreason[ir]);
				}
                                else if (rip1[ir] == "anywhere") {
                                        cmd = sprintf("iptables -I FORWARD %d -o %s -d %s %s -j REJECT --reject-with %s", rulenum + 1,
							rintf[ir], rip2[ir], pstr, rreason[ir]);
                                        cmdb = sprintf("iptables -I FORWARD %d -o %s -s %s %s -j REJECT --reject-with %s", rulenum + 1,
							rintf[ir], rip2[ir], pstrb, rreason[ir]);
				}
                                else if (rip2[ir] == "anywhere") {
                                        cmd = sprintf("iptables -I FORWARD %d -o %s -s %s %s -j REJECT --reject-with %s", rulenum + 1,
                                                rintf[ir], rip1[ir], pstr, rreason[ir]);
                                        cmdb = sprintf("iptables -I FORWARD %d -o %s -d %s %s -j REJECT --reject-with %s", rulenum + 1,
                                                rintf[ir], rip1[ir], pstrb, rreason[ir]);
				}
                                else {
                                        cmd = sprintf("iptables -I FORWARD %d -o %s -s %s -d %s %s -j REJECT --reject-with %s", rulenum + 1,
                                                rintf[ir], rip1[ir], rip2[ir], pstr, rreason[ir]);
                                        cmdb = sprintf("iptables -I FORWARD %d -o %s -d %s -s %s %s -j REJECT --reject-with %s", rulenum + 1,
                                                rintf[ir], rip1[ir], rip2[ir], pstrb, rreason[ir]);
				}
                                if (system(cmd) == 0) {
					#print cmd;
					rulenum++;
					rule_expiry[rulenum] = compute_timer(rnd_mttr_lo[ir], rnd_mttr_hi[ir]); 
					rule_type[rulenum] = "RND";
					rule_cross_index[rulenum] = ir;
					#fw_ip_src[rulenum] = rip1[rlines];
					#fw_ip_dst[rulenum] = rip2[rlines];
					rstate[ir] = 1;
					r_rule[ir] = rulenum;
					if (rdup[ir] == "B") 
						if (system(cmdb) == 0) {
							#print cmdb;
							rulenum++;
							rule_type[rulenum] = "RDB";
							rule_expiry[rulenum] = rule_expiry[rulenum-1];
							rule_cross_index[rulenum] = ir;
						}
				}

			}
		} else {
			if (r_rule[ir] <= rulenum) {
				if (rule_expiry[r_rule[ir]] <= 0) 
					cstate = 1;
				else
					cstate = 0;
			} else 
				cstate = 1;
				
			if (cstate == 1) {
				rstate[ir] = 0;
				r_rule[ir] = 0;
				rstate_timer[ir] = compute_timer(rnd_mttf_lo[ir], rnd_mttf_hi[ir]); 
			}
		}
	}
}

function process_rcstate_timers() {

	#print "Inside Process Rcstate Timers";
	for(i = 1; i < rclines; i++) conns[i] = 0; 

	for(i = 1; i < rclines; i++) {
		#print "Inside RClines loop = ", i;

		for(j = 1; j <= ctracks; j++) {
			#print "j = ",j,"ip1 = ", ct_ip_1[j], "ip2 = ", ct_ip_2[j];
			c1 = ((rcip1[i] == ct_ip_1[j]) || (rcip1[i] == "anywhere"));
			c2 = ((rcip1[i] == ct_ip_2[j]) || (rcip1[i] == "anywhere")) ;
			c3 = ((rcip2[i] == ct_ip_1[j]) || (rcip2[i] == "anywhere"));
			c4 = ((rcip2[i] == ct_ip_2[j]) || (rcip2[i] == "anywhere"));
			c7 = ((rcport2[i] == ct_sport[j]) || (rcport2[i] == 0));
			c8 = ((rcport2[i] == ct_dport[j]) || (rcport2[i] == 0));
			c5 = (c1 || c2);
			c6 = ((c3&&c7) || (c4&&c8));
			if (c5 && c6) {
				conns[i]++;
				#printf "RC connection matched";
				conns_index[i,conns[i]] = j;
			}
		}

		if(conns[i] > 0) {
			if (rcstate[i] == 0) {
				rcstate[i] = 1;
				rcstate_timer[i] = compute_timer(rcd_mttf_lo[i], rcd_mttf_hi[i]); 
			} else if (rcstate[i] == 1) {
				rcstate_timer[i]--;
				if (rcstate_timer[i] <= 0) {
					rcstate_timer[i] = 0;
					cs = conns_index[i, compute_timer(1, conns[i])];	
					ip1 = ct_ip_1[cs];
					ip2 = ct_ip_2[cs];
					sport = ct_sport[cs];
					dport = ct_dport[cs];
					params = sprintf("-p tcp -s %s -d %s --sport %d --dport %d ", ip1, ip2, sport, dport);
					cmd = sprintf("iptables -I FORWARD %d -o %s %s -j REJECT --reject-with %s", rulenum+1, rcintf[i], params, rcreason[i]);
					#print cmd;
					if (system(cmd) == 0)  {
						rulenum++;
						cmd = sprintf("conntrack -D -p tcp -s %s -d %s --orig-port-src %d --orig-port-dst %d", ip1,ip2,sport,dport);
						#print cmd;
						notbroken = system(cmd);
						rule_expiry[rulenum] = compute_timer(rcd_mttr_lo[i], rcd_mttr_hi[i]); 
						rc_rule[i] = rulenum;
						rule_type[rulenum] = "RDCONN";
						rule_cross_index[rulenum] = i;
						rcstate[i] = 2;
					}
				}
			} else if (rcstate[i] == 2) {
				if (rc_rule[i] <= rulenum) {
					if (rule_expiry[rc_rule[i]] <= 0) 
						cstate = 1;
					else
						cstate = 0;
				} else 
					cstate = 1;
					
				if (cstate == 1) {
					rcstate[i] = 1;
					rc_rule[i] = 0;
					rcstate_timer[i] = compute_timer(rnd_mttf_lo[i], rnd_mttf_hi[i]); 
				}
			}
		} else {
			#start from here
			if (rcstate[i] == 0) {
				# Do Nothing
			} else if (rcstate[i] == 1) {
				rcstate_timer[i] = 0;
				rcstate[i] = 0;
				rc_rule[i] = 0;
			} else if (rcstate[i] == 2) {
				if (rc_rule[i] <= rulenum) {
					if (rule_expiry[rc_rule[i]] <= 0) 
						cstate = 1;
					else
						cstate = 0;
				} else 
					cstate = 1;
					
				if (cstate == 1) {
					rcstate[i] = 0;
					rc_rule[i] = 0;
					rcstate_timer[i] = 0; 
				}
			}
		}
	}
}

function process_input() {
	#print "Inside Process Input";
        if ($1 == "IDL") {
                idle_time[ilines] = $2;
                idl_disc_duration[ilines] = $3;
                iip1[ilines] = $4;
                iip2[ilines] = $5;
                ireason[ilines] = $7;
                iintf[ilines] = $8;
		iport2[ilines] = $6;
                ilines++;
        } else if ($1 == "RND") {
		split($2,mttf,":");
		rnd_mttf_lo[rlines] = mttf[1];
		rnd_mttf_hi[rlines] = mttf[2];
		split($3,mttr,":");
		rnd_mttr_lo[rlines] = mttr[1];
		rnd_mttr_hi[rlines] = mttr[2];
                rip1[rlines] = $4;
                rip2[rlines] = $5;
		rport2[rlines] = $6;
                rreason[rlines] = $7;
                rintf[rlines] = $8;
                rdup[rlines] = $9;
		rstate[rlines] = 0; 
		rstate_timer[rlines] = compute_timer(rnd_mttf_lo[rlines], rnd_mttf_hi[rlines]); 
		r_rule[rlines] = 0; 
                rlines++;
        } else if ($1 == "RDCONN") {
		split($2,mttf,":");
		rcd_mttf_lo[rclines] = mttf[1];
		rcd_mttf_hi[rclines] = mttf[2];
		split($3,mttr,":");
		rcd_mttr_lo[rclines] = mttr[1];
		rcd_mttr_hi[rclines] = mttr[2];
                rcip1[rclines] = $4;
                rcip2[rclines] = $5;
		rcport2[rclines] = $6;
                rcreason[rclines] = $7;
                rcintf[rclines] = $8;
		rcstate[rclines] = 0; 
		rcstate_timer[rclines] = 0;
		rc_rule[rclines] = 0; 
		rclines++;
	}
        #print "ilines = ",ilines;
        #print "rlines = ",rlines;
        #print "rclines = ",rclines;
}

function process_conntrack() {
	#print "Inside Process Conntrack";
	time_to_live = net_ttl - $3;
	#print "time to live = ", time_to_live, net_ttl;
	#print "idle time [1] = ", idle_time[1];
	split($5, a, "=");
	split($6, b, "=");
	split($7, c, "=");
	split($8, d, "=");
	ip1=a[2];ip2=b[2];
	port1=c[2];port2=d[2];
	notbroken = 1;

	for(i = 1; i <= ilines; i++)
		if (time_to_live >= idle_time[i]) {
			#print "idl i = ", i, "iip1[i] = ", iip1[i], " iip2[1] = ", iip2[i], " ip1 = ", ip1, " ip2 = ", ip2;
			c1 = ((iip1[i] == ip1) || (iip1[i] == "anywhere"));
			c2 = ((iip1[i] == ip2) || (iip1[i] == "anywhere"));
			c3 = ((iip2[i] == ip1) || (iip2[i] == "anywhere"));
			c4 = ((iip2[i] == ip2) || (iip2[i] == "anywhere"));
			c7 = ((iport2[i] == port1) || (iport2[i] == 0));
			c8 = ((iport2[i] == port2) || (iport2[i] == 0));
			c5 = (c1 || c2);
			c6 = ((c3 && c7) || (c4 && c8));
			#split($7, a, "=");
			#split($8, b, "=");
			#sport = a[2]; dport = b[2];
			sport = c[2]; dport = d[2];
			if (c5 && c6) {
				#print "Main andar AAA gaya";
				params = sprintf("-p tcp -s %s -d %s --sport %d --dport %d ", ip1, ip2, sport, dport);
				cmd = sprintf("iptables -I FORWARD %d -o %s %s -j REJECT --reject-with %s", rulenum+1, iintf[i], params, ireason[i]);
				#print cmd;
				if (system(cmd) == 0)  {
					rulenum++;
					cmd = sprintf("conntrack -D -p tcp -s %s -d %s --orig-port-src %d --orig-port-dst %d", ip1,ip2,sport,dport);
					#print cmd;
					notbroken = system(cmd);
					rule_expiry[rulenum] = idl_disc_duration[i];
					rule_type[rulenum] = "IDL";
					rule_cross_index[rulenum] = i;
				}
			}
		}

	if (notbroken == 1) {
		ctracks++;
		ct_ip_1[ctracks] = ip1;
		ct_sport[ctracks] = sport;
		ct_ip_2[ctracks] = ip2;
		ct_dport[ctracks] = dport;
		#print "Increment conntracks = ", ctracks;
	}
}

{
	#if (system("flockn -n -x /var/lock/mylockfile") == 1) exit;
	inc = 1;
	while ((getline < file[inc]) > 0) process_input();
	close(file[inc]);
	if ((ilines == 1) && (rlines == 1) && (rclines == 1)) exit;
	while(x) {
		inc = 2;
		ctracks = 0;
		while ((getline < file[inc]) > 0) process_conntrack();
		close(file[inc]);

		#print "ilines = ", ilines;
		#print "Hello World";
		#print "rlines = ", rlines;
		#print "rclines = ", rclines;
		#print "Hello World";

		process_rule_timers();
		process_rstate_timers();
		process_rcstate_timers();
		#print "Release end lock for interface ", intf;
		system("sleep 1");
		cmd = sprintf("conntrack -L | grep tcp | grep ESTABLISHED > %s/ctrack.out", disc_dir)
        	system(cmd);
	}
}
