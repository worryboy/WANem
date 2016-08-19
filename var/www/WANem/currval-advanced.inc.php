<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*									    */	
/*            Please have a look at CopyrightInformation.txt                */
/*									    */	
/****************************************************************************/

//***************************************************************************************
//Function to replace the $_POST values with the netem values which are currently running
//on a selected interface.
//***************************************************************************************
function get_current_values(&$interface, &$del, &$delJitter, &$delCorrelation, &$delDistribution, &$loss, &$lossCorrelation, &$dup, &$dupCorrelation, &$reorder, &$reorderCorrelation, &$gap, &$bandwidth, &$corrupt, &$sym, &$disc, &$limit, &$msFound, &$src, &$srcSub, &$dest, &$destSub, &$port, $addInt, $deleteInt) {
	global $tc_CMD;
	global $disconnect_DIR;

	//Variable to return from the function: $ruleSets(The number of rule sets for the selected interface)

	//Get the output of "tc filter show"
	$output=shell_exec($tc_CMD . ' filter show dev ' . $interface);

	//Set $ruleSets to zero
	$ruleSets=0;

	//Find instances of the word "flowid" in $output
	//Set pointer to zero
	$i=0;
	//Not more than 10,000 ruleSets
	$px=9999;
	$pips = "xxxx";
	$psubs = "xxxx";
	$pipd = "xxxx";
	$psubd = "xxxx";
	$psport = "xxxx";
	$pdport = "xxxx";
	while (strpos($output, 'flowid', $i)!==false):
		++$ruleSets;
		$i=strpos($output, 'flowid', $i) + 7;
		//Get the IP address data...

		$tmpStr="";
		//While $n doesn't point to a ':' character
		while (substr($output, $i, 1)!=":"):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		//**Set $x**
		//Divide $tmpStr by 10
		$tmpStr=$tmpStr/10;
		$tmpStr=$tmpStr - 1;
		//Round $tmpStr down to the nearest integer and assign to $x
		$x=floor($tmpStr);

		//Find next instance of "match" and set the pointer to the start of the ip address data
		$dstpos=strpos($output, ' at 16', $i);
		$dstpos = $dstpos - 22;

		$srcpos=strpos($output, ' at 12', $i);
		$srcpos = $srcpos - 22;

		$matchpos=strpos($output, ' at 20', $i);
		$dportpos=strpos($output, ' at 20', $i);
		
		if (is_numeric($matchpos)) 
		{
			$matchpos = $matchpos - 22;
		}

		if (is_numeric($dportpos)) 
		{
			$dportpos = $dportpos - 22;
		}

		$nextflowpos=strpos($output, 'flow', $i);

		$dstmatch = (($dstpos < $nextflowpos) || ($nextflowpos === FALSE)) && ($dstpos !== FALSE);
		$srcmatch = (($srcpos < $nextflowpos) || ($nextflowpos === FALSE)) && ($srcpos !== FALSE);
		$matchmatch = ($matchpos < $nextflowpos) && ($matchpos !== FALSE);
		$dportmatch = ($dportpos < $nextflowpos) && ($dportpos !== FALSE) && ($dportpos > 300);

		$src[$x]="any";
		$srcSub[$x]="";
		$srcFound[$x]=false;
		$dest[$x]="any";
		$destSub[$x]="";
		$destFound[$x]=false;
		$port[$x]="any";

		$sport = "any";
		$dport = "any";

		if ($dstmatch) {
			$tmpEnd=strpos($output, "\n", $dstpos);
			$tmpSize = $tmpEnd - ($dstpos + 4);
			$tmpStr=substr($output, $dstpos + 4, $tmpSize);
			//list($destT,$destSubT)=split('/', $tmpStr);
			$destTemp = substr($tmpStr, 1, 8);
			$destSubTemp = substr($tmpStr, 10, 8);

			//Get the IP address in Ascii format
			$str="";

			for($Ti=0;$Ti<8;$Ti+=2) {
				$str .= hexdec(substr($destTemp, $Ti, 2));
			
				if ($Ti < 5)
					$str = $str . ".";
			}

			$dest[$x] = $str;
			
			//Get the Subnet mask IP address in Ascii format
			$str="";
			$str = hexdec($destSubTemp);
		
			if ($str != 0) {
				$str = "";
				for($Ti=0; $Ti<8; $Ti+=2) {
					$str .= hexdec(substr($destSubTemp, $Ti, 2));
				
					if ($Ti < 5)
						$str = $str . ".";
				}

				$destSub[$x] = $str;
			}
			else {
				$destSub[$x] = $str;
			}

			$destFound[$x]=true;
		}

		if ($srcmatch) {
			$tmpEnd=strpos($output, "\n", $srcpos);
			$tmpSize = $tmpEnd - ($srcpos + 4);
			$tmpStr=substr($output, $srcpos + 4, $tmpSize);
			//list($src[$x],$srcSub[$x])=split('/', $tmpStr);
			
			$srcTemp = substr($tmpStr, 1, 8);
			$srcSubTemp = substr($tmpStr, 10, 8);

			//Get the IP address in Ascii format
			$str="";
			
			for($Ti=0;$Ti<8;$Ti+=2) {
				$str .= hexdec(substr($srcTemp, $Ti, 2));
			
				if ($Ti < 5)
					$str = $str . ".";
			}	
			
			$src[$x] = $str;
				
		
			//Get the Subnet mask IP address in Ascii format
			$str="";
			
			$str = hexdec ($srcSubTemp);
		
			if ($str != 0) {
				$str = "";
				for($Ti=0; $Ti<8; $Ti+=2) {
					$str .= hexdec(substr($srcSubTemp, $Ti, 2));
				
					if ($Ti < 5)
						$str = $str . ".";
				}
				$srcSub[$x] = $str;
			}
			else {
				$srcSub[$x] = $str;
			}

			$srcFound[$x]=true;
		}

		if ($matchmatch) {
                        //Set pointer to 5 spaces after the first instance of 'match'
			$i=$matchpos+5;
			//Get ip port data
			$tmpStr1=substr($output, $i, 8);

			//Get mask data
			$i=$i+9;
			$tmpStr2=substr($output, $i, 8);

			if ($tmpStr2 == "ffff0000") {
				$sport=substr($tmpStr1,0,4);
				$port[$x]=hexdec($sport);
				$sport=$port[$x];
			} else if ($tmpStr2 == "0000ffff") {
				$dport=substr($tmpStr1,4,4);
				$port[$x]=hexdec($dport);
				$dport=$port[$x];
			}
		}

		if ($dportmatch) {
			$n = $dportpos + 5;
			$tmpS = substr($output, $n, 8);
		
			for ($j=0; $j < 6; $j += 1) { 
			}
			$tmpStr = "";
			while (is_numeric(substr($output, $n, 1))) {
				$tmpStr = $tmpStr . substr($output, $n, 1);
				$n++;
			}
			
			//$dport=$tmpStr;
			//$port[$x]=$dport;

			$dport=hexdec($tmpS);
			$port[$x] = $dport;

		}

		//$i=strpos($output, 'match', $i) + 6;

		//Do not add IP data if it is 0.0.0.0/0 (automatically generated filter rule to match all
		//other packets)

		$destzero=($dest[$x]=="0.0.0.0") && ($destSub[$x]=="0");
		$srczero=($dest[$x]=="0.0.0.0") && ($destSub[$x]=="0");

		if ($destzero && $srczero) {
			//Subtract a rule set
			$src[$x]="any";
			$srcSub[$x]="";
			$dest[$x]="any";
			$destSub[$x]="";
			$port[$x]="any";
			$destFound[$x]=false;
			$srcFound[$x]=false;
			--$ruleSets;
		}

		//print "\n";
		//print "x = $x , px = $px";
		//print "pipd = $pipd , src[$x] = $src[$x] , psubd = $psubd , srcSub[$x] = $srcSub[$x]";
		//print "pips = $pips , dest[$x] = $dest[$x] , psubs = $psubs , destSub[$x] = $destSub[$x]";
		//print "psport = $psport, dport = $dport, pdport = $pdport, sport = $sport, port[$x] = $port[$x]";
		//print "\n";
		if (($x == $px) && ($pipd == $src[$x]) && ($psubd == $srcSub[$x]) && ($pips == $dest[$x]) && ($psubs == $destSub[$x]) && ($psport == $dport) && ($pdport == $sport)) {
			--$ruleSets;
			$sym[$x]="Yes";
		} else {
			$sym[$x]="No";
		}
			
		$px=$x;
		$pips = $src[$x];
		$psubs = $srcSub[$x];
		$pipd = $dest[$x];
		$psubd = $destSub[$x];
		$psport = $sport;
		$pdport = $dport;
	//End of pasted code.
	//print "rules = $ruleSets";
	endwhile;

	//print "ruleso = $ruleSets";
	//Check if no rulesets were found
	if ($ruleSets==0) {
		$ruleSets=1;
		//Set ip address data to default values
		$src[0]="any";
		$srcSub[0]="";
		$dest[0]="any";
		$destSub[0]="";
		$port[0]="any";
		$sym[0]="Yes";
	}

	//Add one to $ruleSets if the add rule set button was clicked
	if ($addInt==1) {
		++$ruleSets;
		//Set ip address data to default values
		$src[$ruleSets-1]="any";
		$srcSub[$ruleSets-1]="";
		$dest[$ruleSets-1]="any";
		$destSub[$ruleSets-1]="";
		$port[$ruleSets-1]="any";
		//$bandwidth[$ruleSets-1]="0";
		$sym[$ruleSets-1]="Yes";
		$_SESSION[addRuleSet]=true;
	}

	//Subtract one from $rulesets if the delete last rule set button was clicked
	if ($deleteInt==1) {
		--$ruleSets;
	}

	//If $output is empty then there are no values or there are only non-ip-matching values.
	//If this is the case then carry on with checking 'tc -d qdisc'

	//Get the tc status
	$output=shell_exec($tc_CMD.' -d qdisc');
	//Check for the word 'netem' in the output. If it's not there then there's no need to
	//carry on with this part of the function.
	if (strstr($output, "netem")!=FALSE) {
		//****Get the number of lines in $output and the starting character of each line****

		//Loop through all characters in $output
		for ($i = 0; $i <= strlen($output); $i++) {
			//Check each character in turn to see if it has an ascii value of 10
			//ascii 10 = linefeed character
			if (ord(substr($output, $i, 1)) == 10) {
				//if a linefeed character was found then add 1 to lines
				$lines=$lines+1;
				//Add the starting character of the next line to the array $lineStart
				$lineStart[] = $i+1;
			}
		}
		//Loop through the number of interface instances in $ruleSets
		for ($r=1; $r<=$ruleSets; $r++) {

			//Set selected interface variable
			$selectedInterface=$interface;

			//Reset variables that will be used to show which rules were found and which ones
			//weren't
			$delFound=FALSE;
			$lossFound=FALSE;
			$dupFound=FALSE;
			$reorderFound=FALSE;
			$corruptFound=FALSE;
			$bandwidthFound=FALSE;
			$limitFound=FALSE;

			//Loop through lines
			for ($i = 0; $i < $lines; $i++) {
				//If on the first line then set pointer($n) to first character else set it to
				//the corresponding $linestart value
				if ($i==0) {
					$n=0;
				} else {
					$n=$lineStart[$i-1];
				}
				//Check if the first word in the line is 'qdisc', if it is then carry on processing
				//if not then do nothing and move to the next line.
				if (substr($output, $n, 5) == "qdisc") {

					//**Get the qdisc name**
					//Empty $tmpStr
					$tmpStr="";
					//Set $n to point to 6 characters into the line
					$n=($n+6);
					//While $n doesn't point to a space character
					while (ord(substr($output, $n, 1))!=32):
						//Add character to $tmpStr
						$tmpStr=$tmpStr . substr($output, $n, 1);
						//increment $n
						$n=($n+1);
					endwhile;
					//**Check if the qdisc name is 'netem'**
					if ($tmpStr=='netem') {
						//Get current line into a string
						if ($i==0) {
							$lineStr=substr($output, 0, ($lineStart[0]-1));
						} else {
							$lineStr=substr($output, $lineStart[$i-1], (($lineStart[$i]-1)-$lineStart[$i-1]));
						}

						//**Get qdisc handle (6 space after the beginning of 'netem')
						//This will only be needed if there is more than one ruleset
						//Reset $tmpStr
						$tmpStr="";
						if ($ruleSets > 1) {
							$n=(strpos($lineStr, 'netem') + 6);
							while (substr($lineStr, $n, 1)!=":"):
								//Add character to $tmpStr
								$tmpStr=$tmpStr . substr($lineStr, $n, 1);
								//increment $n
								++$n;
							endwhile;
							//Check qdisc handle number and set the interface instance($x) accordingly.
							//10-19=1,20-29=2,30-39=3.. etc
							//Subtract 10 from $tmpStr
							$tmpStr-=10;
							//Divide $tmpStr by 10
							$tmpStr=$tmpStr/10;
							//Round $tmpStr down to the nearest integer and assign to $x
							$x=floor($tmpStr);
						} else {
							//Else just set $x to 0
							$x=0;
						}

						//**Get interface name**
						//Move the pointer to 4 characters after the start of 'dev'
						$n=(strpos($lineStr, 'dev')+4);
						//Reset $tmpStr2
						$tmpStr2="";
						while (substr($lineStr, $n, 1)!=" "):
							//Add character to $tmpStr
							$tmpStr2=$tmpStr2 . substr($lineStr, $n, 1);
							//increment $n
							++$n;
						endwhile;
						//Set $limitFound to false, this flag will be used so that
						//the $limit value for the interface is only checked once
						//$limitFound=false;

						//Check if interface name matches selected interface
						if ($tmpStr2==$selectedInterface) {

							//**Get netem values and set the correct $_POST variables**
							//Check for delay
							if (strstr($lineStr, "delay")!=FALSE) {
								//Set $delFound to TRUE
								$delFound[$x]=TRUE;
								//Set $n to start of delay time value
								$n=(strpos($lineStr, "delay")+6);

								//****************
								//Get delay values
								//****************

								//**Get delay time**
								//Reset tmpStr3
								$tmpStr3="";
								//$usFound will be set to true when 999us is found and
								//therefore the value is 1ms.  This is just a quirk of the delay
								//command.
								$usFound=false;
								$gotNumber=false;
								while ($gotNumber==false):
									//If character is not '.' or 'u'
									if (substr($lineStr, $n, 1)!="." & substr($lineStr, $n, 1)!="u")  {
										//Add character to $tmpStr
										$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
										//increment $n
										$n=($n+1);
									} else {
										//else set $gotNumber to true
										$gotNumber=true;
										if (substr($lineStr, $n, 1)=="u") {
											$usFound=true;
										}
									}
								endwhile;

								if ($usFound==false) {
									$tmpStr4="";
									$z=$n+1;
									//set tmpStr4 to $tmpStr3 plus a decimal point
									$tmpStr4=$tmpStr3 . ".";
									$msFound=false;
									//while $z is not pointing at an "s"
									while (substr($lineStr, $z, 1)!="s"):
										//if a "m" is found
										if (substr($lineStr, $z, 1)=="m") {
											$msFound=true;
										//else it will be the number after the decimal point
										} else {
											$tmpStr4=$tmpStr4 . substr($lineStr, $z, 1);
										}
										++$z;
									endwhile;
								}

								//Set delay variable to tmpStr3 or tmpStr4 depending on
								//whether delay is over a second or not
								if ($usFound==false) {
									if ($msFound==false) {
										$del[$x]=$tmpStr4*1000;
									} else {
										$del[$x]=$tmpStr3;
									}
								} else {
									$del[$x]=1;
								}

								//**Get delay jitter**
								//Check for delay jitter if there was a delay time value
								if (($tmpStr3!="") and (strlen($lineStr) > ($n+6))) {
									//increment $n by 5 or 6 to get to first jitter character
									if ($msFound==true) {
										$n=($n+6);
									} else {
										$n=($n+5);
									}
									if (is_numeric(substr($lineStr, $n, 1))) {
										//Get delay jitter
										//Reset tmpStr3
										$tmpStr3="";
										//$usFound will be set to true when 999us is found and
										//therefore the value is 1ms.  This is just a quirk of the delay
										//command.
										$usFound=false;
										$gotNumber=false;
										while ($gotNumber==false):
											//If character is not '.' or 'u'
											if (substr($lineStr, $n, 1)!="." & substr($lineStr, $n, 1)!="u")  {
												//Add character to $tmpStr
												$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
												//increment $n
												$n=($n+1);
											} else {
												//else set $gotNumber to true
												$gotNumber=true;
												if (substr($lineStr, $n, 1)=="u") {
													$usFound=true;
												}
											}
										endwhile;

										if ($usFound==false) {
											$tmpStr4="";
											$z=$n+1;
											//set tmpStr4 to $tmpStr3 plus a decimal point
											$tmpStr4=$tmpStr3 . ".";
											$msFound=false;
											//while $z is not pointing at an "s"
											while (substr($lineStr, $z, 1)!="s"):
												//if a "m" is found
												if (substr($lineStr, $z, 1)=="m") {
													$msFound=true;
												//else it will be the number after the decimal point
												} else {
													$tmpStr4=$tmpStr4 . substr($lineStr, $z, 1);
												}
												++$z;
											endwhile;
										}

										//Set delay jitter variable to tmpStr3 or tmpStr4 depending on
										//whether delay is over a second or not
										if ($usFound==false) {
											if ($msFound==false) {
												$delJitter[$x]=$tmpStr4*1000;
											} else {
												$delJitter[$x]=$tmpStr3;
											}
										} else {
											$delJitter[$x]=1;
										}
									} else {
										//Set delay jitter variable to zero
										$delJitter[$x]="0";
									}
								} else {
									$tmpStr3="";
									//Set delay jitter variable to zero
									$delJitter[$x]="0";
								}

								//**Get delay correlation**
								//Check for delay correlation if there was a delay correlation
								//value
								if (($tmpStr3!="") and (strlen($lineStr) > ($n+5))) {
									//increment $n by 5 to get to start of delay correlation
									//value
									$n=($n+5);
									if (is_numeric(substr($lineStr, $n, 1))) {
										//Get delay jitter
										//Reset tmpStr3
										$tmpStr3="";
										//While $n not pointing at a '%' character
										while (ord(substr($lineStr, $n, 1))!=37):
											//Add character to $tmpStr
											$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
											//increment $n
											$n=($n+1);
										endwhile;

										//Set delay correlation variable to tmpStr3
										$delCorrelation[$x]=$tmpStr3;
									} else {
										//Set delay correlation variable to zero
										$delCorrelation[$x]="0";
									}
								} else {
									//Set delay correlation variable to zero
									$delCorrelation[$x]="0";
								}
							}

							//Check for loss
							if ((strstr($lineStr, "loss")!=FALSE)) {
								//Set $lossFound to TRUE
								$lossFound[$x]=TRUE;
								//Set $n to start of loss value
								$n=(strpos($lineStr, "loss")+5);

								//****************
								//Get loss values
								//****************
								//Get loss
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a '%' character
								while (ord(substr($lineStr, $n, 1))!=37):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
								endwhile;
								//Set loss variable to tmpStr3
								$loss[$x]=$tmpStr3;

								//**Get loss correlation**
								//Check for loss correlation if there was a loss correlation
								//value
								if (($tmpStr3!="") and (strlen($lineStr) > ($n+2))) {
									//increment $n by 2 to get to start of loss correlation value
									$n=($n+2);
									if (is_numeric(substr($lineStr, $n, 1))) {
										//Get loss correlation
										//Reset tmpStr3
										$tmpStr3="";
										//While $n not pointing at a '%' character
										while (ord(substr($lineStr, $n, 1))!=37):
											//Add character to $tmpStr
											$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
											//increment $n
											$n=($n+1);
										endwhile;

										//Set loss correlation variable to tmpStr3
										$lossCorrelation[$x]=$tmpStr3;
									}
								} else {
									//Set loss correlation variable to zero
									$lossCorrelation[$x]="0";
								}
							}

							//Check for duplication
							if ((strstr($lineStr, "duplicate")!=FALSE)) {
								//Set $dupFound to TRUE
								$dupFound[$x]=TRUE;
								//Set $n to start of duplicate value
								$n=(strpos($lineStr, "duplicate")+10);

								//**********************
								//Get duplication values
								//**********************
								//Get duplication
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a '%' character
								while (ord(substr($lineStr, $n, 1))!=37):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
								endwhile;

								//Set duplication variable to tmpStr3
								$dup[$x]=$tmpStr3;

								//**Get duplication correlation**
								//Check for duplication correlation if there was a loss
								//correlation value
								if (($tmpStr3!="") and (strlen($lineStr) > ($n+2))) {
									//increment $n by 2 to get to start of duplication
									//correlation value
									$n=($n+2);
									if (is_numeric(substr($lineStr, $n, 1))) {
										//Get duplication correlation
										//Reset tmpStr3
										$tmpStr3="";
										//While $n not pointing at a '%' character
										while (ord(substr($lineStr, $n, 1))!=37):
											//Add character to $tmpStr
											$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
											//increment $n
											$n=($n+1);
										endwhile;

										//Set duplication correlation variable to tmpStr3
										$dupCorrelation[$x]=$tmpStr3;
									}
								} else {
									//Set duplication correlation variable to zero
									$dupCorrelation[$x]="0";
								}
							}

							//Check for corruption
							if ((strstr($lineStr, "corrupt")!=FALSE)) {
								//Set $corruptFound to TRUE
								$corruptFound[$x]=TRUE;
								//Set $n to start of duplicate value
								$n=(strpos($lineStr, "corrupt")+8);

								//**********************
								//Get corruption values
								//**********************
								//Get corruption
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a '%' character
								while (ord(substr($lineStr, $n, 1))!=37):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
								endwhile;
								
								//Set corruption variable to tmpStr3
								$corrupt[$x]=$tmpStr3;

							}

							//Check for reorder
							if ((strstr($lineStr, "reorder")!=FALSE)) {
								//Set $reorderFound to TRUE
								$reorderFound[$x]=TRUE;
								//Set $n to start of reorder value
								$n=(strpos($lineStr, "reorder")+8);
								//**********************
								//Get reorder values
								//**********************
								//**Get reorder**
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a % character
								while (ord(substr($lineStr, $n, 1))!=37):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									++$n;
								endwhile;

								//Set reorder variable to tmpStr3
								$reorder[$x]=$tmpStr3;

								//**Get reorder correlation**
								//Check if substr($lineStr, $n+2, 1)="g" (Start of the word "gap" or if it
								//is a number (start of correlation value)
								//Set $n to start of delay time value
								if (substr($lineStr, $n+2, 1)!="g") {
									//Add 2 to $n to move the pointer to the start of the reorder
									//correlation value
									$n=$n+2;
									$tmpStr3="";
									//While $n not pointing at a % character
									while (ord(substr($lineStr, $n, 1))!=37):
										//Add character to $tmpStr
										$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
										//increment $n
										$n=($n+1);
									endwhile;

								//Set reorder variable to tmpStr3
								$reorderCorrelation[$x]=$tmpStr3;
								} else {
									//Set reorder correlation variable to zero
									$reorderCorrelation[$x]="0";
								}

								//**Get gap value**
								$n=(strpos($lineStr, "gap")+4);
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a newline character
								while ($n <= strlen($lineStr)):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
								endwhile;

								//Set gap variable to tmpStr3
								$gap[$x]=$tmpStr3;

							}
							//**Check for limit value**
							//Look for first occurence of 'limit' in $output. It doesn't
							//matter where the first occurence is because all limits will
							//be the same unless the user manually typed commands with
							//different limits.
							if ((strstr($lineStr, "limit")!=FALSE)) {
								//Set $reorderFound to TRUE
								$limitFound[$x]=TRUE;
								//Set $n to start of reorder value
								$n=(strpos($lineStr, "limit")+6);
								$tmpStr3="";
								$limiterpos = substr($lineStr, $n, 1);
								$limitstrlen = strlen($lineStr);
								while (($limiterpos!==" ")&&($n < $limitstrlen)):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
									$limiterpos = substr($lineStr, $n, 1);
								endwhile;
								$limit[$x]=$tmpStr3;
							//	print "for limit x = $x, limit = $limit[$x]";
							} else
								$limitFound[$x]=FALSE;
								
						}
					}
				}
			}

			//Set non-found netem values to zero
			for ($n=0; $n<$ruleSets; ++$n) {
				if ($delFound[$n]==FALSE) {
					$del[$n]="0";
					$delJitter[$n]="0";
					$delCorrelation[$n]="0";
					$delDistribution[$n]="-N/A-";
				}
				if ($lossFound[$n]==FALSE) {
					$loss[$n]="0";
					$lossCorrelation[$n]="0";
				}
				if ($dupFound[$n]==FALSE) {
					$dup[$n]="0";
					$dupCorrelation[$n]="0";
				}
				if ($corruptFound[$n]==FALSE) {
					$corrupt[$n]="0";
				}
				if ($reorderFound[$n]==FALSE) {
					$reorder[$n]="0";
					$reorderCorrelation[$n]="0";
					$gap[$n]="0";
				}
				if ($limitFound[$n]==FALSE) {
					$limit[$n]=1000;
				}
			}
		}
	} else {
		//Show all values as zero
		//Loop through all interface instances
		for ($x=0; $x<$ruleSets; $x++) {
			$del[$x]="0";
			$delJitter[$x]="0";
			$delCorrelation[$x]="0";
			$loss[$x]="0";
			$lossCorrelation[$x]="0";
			$dup[$x]="0";
			$dupCorrelation[$x]="0";
			$corrupt[$x]="0";
			$reorder[$x]="0";
			$reorderCorrelation[$x]="0";
			$gap[$x]="0";
			$limit[$x]=1000;
		}
	}

	//for ($n=0; $n<$ruleSets; ++$n) 
	//	print "n = $n , limit = $limit[$n]";
	//**Check for 'htb'**

	//Loop through all interface instances
	for ($r=0; $r<$ruleSets; $r++) {
		//Get the tc class status
		$output=shell_exec($tc_CMD.' class show dev ' . $interface);

		//****Get the number of lines in $output and the starting character of each line****

		//Loop through all characters in $output
		for ($i = 0; $i <= strlen($output); $i++) {
			//Check each character in turn to see if it has an ascii value of 10
			//ascii 10 = linefeed character
			if (ord(substr($output, $i, 1)) == 10) {
				//if a linefeed character was found then add 1 to lines
				$lines=$lines+1;
				//Add the starting character of the next line to the array $lineStart
				$lineStart2[] = $i+1;
			}
		}
		//Loop through lines
		for ($i = 0; $i < $lines; $i++) {
			//If on the first line then set pointer($n) to first character else set it to
			//the corresponding $linestart value
			if ($i==0) {
				$n=0;
			} else {
				$n=$lineStart2[$i-1];
			}
			//Get current line into a string
			if ($i==0) {
				$lineStr=substr($output, 0, ($lineStart2[0]-1));
			} else {
				$lineStr=substr($output, $lineStart2[$i-1], (($lineStart2[$i]-1)-$lineStart2[$i-1]));
			}
			if (strstr($lineStr, "htb")!=FALSE & strstr($lineStr, "rate")!=FALSE) {
				//**Get htb handle (4 space after the beginning of 'htb')
				//This will only be needed if there is more than one ruleset
				//Reset $tmpStr
				$tmpStr="";
				if ($ruleSets > 1) {
					$n=(strpos($lineStr, 'htb') + 4);

					//Get htb major number into $tmpStr
					while (substr($lineStr, $n, 1)!=":"):
						//Add character to $tmpStr
						$tmpStr=$tmpStr . substr($lineStr, $n, 1);
						//increment $n
						++$n;
					endwhile;
					//Check qdisc handle number and set the interface instance($x) accordingly.
					//10-19=1,20-29=2,30-39=3.. etc
					//Subtract 10 from $tmpStr
					$tmpStr-=10;
					//Divide $tmpStr by 10
					$tmpStr=$tmpStr/10;
					//Round $tmpStr down to the nearest integer and assign to $x
					$x=floor($tmpStr);
				} else {
					//Else just set $x to 0
					$x=0;
				}

				//Set $n to start of bandwidth value
				$n=(strpos($lineStr, "rate")+5);

				//**********************
				//Get bandwidth value
				//**********************
				//Get bandwidth (Kbits/s)
				//Reset tmpStr3
				$tmpStr3="";
				$gotNumber=false;
				while ($gotNumber==false):
					//If character is not 'b', 'K', or 'M'
					if (substr($lineStr, $n, 1)!="b" & substr($lineStr, $n, 1)!="K" & substr($lineStr, $n, 1)!="M") {
						//Add character to $tmpStr
						$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
						//increment $n
						$n=($n+1);
					} else {
						//else set $gotNumber to true
						$gotNumber=true;
					}
				endwhile;
				//Check whether $n is 'b', 'K' or 'M' and convert $tmpStr3 accordingly
				if (substr($lineStr, $n, 1)=="b") {
					$bandwidth[$x]=$tmpStr3/1000;
				} elseif (substr($lineStr, $n, 1)=="K") {
					$bandwidth[$x]=$tmpStr3;
				} elseif (substr($lineStr, $n, 1)=="M") {
					$bandwidth[$x]=$tmpStr3*1000;
				}
				$bandwidthFound[$x]=true;
			}
		}
	}
	//Set non-found bandwidth values to zero
	for ($x=0; $x<$ruleSets; $x++) {
		if ($bandwidthFound[$x]==false) {
			$bandwidth[$x]=0;
		}
	}

	//check for disconnect
	$output=intval(shell_exec($disconnect_DIR . '/check_disco.sh'));
	if ($output < 1) return $ruleSets;

	//Get the output of "tc filter show"
	$disc_cmd='sudo grep -v "#" '. $disconnect_DIR .'/input.dsc | grep '. $interface;
	//$disc_cmd='sudo grep -v "#" '. $disconnect_DIR .'/input.dsc 2>&1'; 
	$output=shell_exec($disc_cmd);
	//echo "disc_cmd = ",$disc_cmd;
	//echo "interface = ",$interface;
	//echo "output = ",$output;

	for ($x=0; $x<$ruleSets; $x++) {
		if (!($disc[$x])) 
			$disc[$x] = new disc_object;
		$disc[$x]->idl_type = "none";
		$disc[$x]->rnd_type = "none";
		$disc[$x]->rcd_type = "none";
	}
	//Set $ruleSets to zero
	$didleruleSets=0;
	$i = 0;

	$idl_found = (strpos($output, 'IDL', $i)!==false);
	$rnd_found = (strpos($output, 'RND', $i)!==false);
	$rcd_found = (strpos($output, 'RDCONN', $i)!==false);

	while ($idl_found || $rnd_found || $rcd_found):
		++$didruleSets;
		if ($idl_found) 
			$idl_pos = strpos($output, 'IDL', $i);
		else
			$idl_pos = 999999;
		if ($rnd_found) 
			$rnd_pos = strpos($output, 'RND', $i);
		else
			$rnd_pos = 999999;
		if ($rcd_found) 
			$rcd_pos = strpos($output, 'RDCONN', $i);
		else
			$rcd_pos = 999999;
			
		$i = min($idl_pos, $rnd_pos, $rcd_pos);

		if (($idl_found) && ($i == $idl_pos)) 
			$dtype = "IDL";
		else if (($rnd_found) && ($i == $rnd_pos))
			$dtype = "RND";
		else if (($rcd_found) && ($i == $rcd_pos))
			$dtype = "RDCONN";
		else
			$dtype = "NONE";
		
		//if ($dtype == "NONE") return $ruleSets;
		//print "rnd_found = $rnd_found";
		//print "rnd_pos = $rnd_pos , i_pos = $i";
		//print "dtype = $dtype ";

		$i = strpos($output, ' ', $i) + 1;

		if ($dtype != "IDL") $separator = ":"; else $separator = " ";

		$tmpStr="";
		//While $n doesn't point to a ':' character
		while (substr($output, $i, 1)!= $separator):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		$mttf_timer_lo = $tmpStr;
		//print "mttf_lo = $mttf_timer_lo";

		if ($separator == ":") {
			$i=($i+1);
			$tmpStr="";
			//While $n doesn't point to a ':' character
			while (substr($output, $i, 1)!= " "):
				//Add character to $tmpStr
				$tmpStr=$tmpStr . substr($output, $i, 1);
				//increment $i
				$i=($i+1);
			endwhile;

			$mttf_timer_hi = $tmpStr;
		}

		$i = strpos($output, ' ', $i) + 1;
                $tmpStr="";
                //While $n doesn't point to a ':' character
                while (substr($output, $i, 1)!= $separator):
                        //Add character to $tmpStr 
                        $tmpStr=$tmpStr . substr($output, $i, 1); 
                        //increment $i
                        $i=($i+1);
                endwhile;

                $mttr_timer_lo = $tmpStr;

                if ($separator == ":") {
			$i=($i+1);
                        $tmpStr="";
                        //While $n doesn't point to a ':' character
                        while (substr($output, $i, 1)!= " "):
                                //Add character to $tmpStr 
                                $tmpStr=$tmpStr . substr($output, $i, 1); 
                                //increment $i
                                $i=($i+1);
                        endwhile;

                        $mttr_timer_hi = $tmpStr;
                }       
		
		// Get the 1st src IP address 
		$i = $i + 1;
		$tmpStr="";
		//While $n doesn't point to a ' ' character
		while (substr($output, $i, 1)!=" "):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		$srcip = $tmpStr ;
		if ($srcip == "anywhere") $srcip = "any";

		// Get the 2nd dst IP address 
		$i = $i + 1;
		$tmpStr="";
		//While $n doesn't point to a ' ' character
		while (substr($output, $i, 1)!=" "):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		$dstip = $tmpStr;
		if ($dstip == "anywhere") $dstip = "any";
		//print "dstip = $dstip";

		// Get the port value 
		$i = $i + 1;
		$tmpStr="";
		//While $n doesn't point to a ' ' character
		while (substr($output, $i, 1)!=" "):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;
		
		$aport=$tmpStr;
		if ($aport == 0) $aport = "any";

		// Get the disc type value
		$i = $i + 1;
		$tmpStr="";
		//While $n doesn't point to a ' ' character
		while (substr($output, $i, 1)!=" "):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		$disc_type = $tmpStr ;
		//print "disc type = $disc_type";

		// Get the idle disc type interface
		$i = $i + 1;
		$tmpStr="";
		//While $n doesn't point to a ' ' character
		while (substr($output, $i, 1)!=" "):
			//Add character to $tmpStr
			$tmpStr=$tmpStr . substr($output, $i, 1);
			//increment $i
			$i=($i+1);
		endwhile;

		$intf = $tmpStr;

                // Get the bidirectional type 
                //While $n doesn't point to a ' ' character
                while (substr($output, $i, 1)==" "):
                        //Add character to $tmpStr
                        //increment $i
                        $i=($i+1);
                endwhile;

		$bi = substr($output, $i, 1);
		//$bi = $tmpStr;

		if ($dtype == "RND")
			if ($bi == "B") $bd = "Yes"; else $bd = "No";
		else
			$bd = "Yes";
				

		//print "bi = $bi";

		//print "Interface = $iintf";
		//print "aport = $aport";
		$found = FALSE;
		for ($x=0; $x<$ruleSets; $x++) {


			if (($dest[$x] == $dstip) && ($src[$x] == $srcip) && ($port[$x] == $aport) && ($intf == $interface) && ($sym[$x] == $bd)) {
				switch($dtype) {
					case "IDL":
						$disc[$x]->idl_type = $disc_type;
						$disc[$x]->idl_timer = $mttf_timer_lo;
						$disc[$x]->idl_disc_timer = $mttr_timer_lo;
						$found = TRUE;
						break;

					case "RND":
						$disc[$x]->rnd_type = $disc_type;
						$disc[$x]->rnd_mttf_lo = $mttf_timer_lo;
						$disc[$x]->rnd_mttf_hi = $mttf_timer_hi;
						$disc[$x]->rnd_mttr_lo = $mttr_timer_lo;
						$disc[$x]->rnd_mttr_hi = $mttr_timer_hi;
						$found = TRUE;
						break;

					case "RDCONN":
						$disc[$x]->rcd_type = $disc_type;
						$disc[$x]->rcd_mttf_lo = $mttf_timer_lo;
						$disc[$x]->rcd_mttf_hi = $mttf_timer_hi;
						$disc[$x]->rcd_mttr_lo = $mttr_timer_lo;
						$disc[$x]->rcd_mttr_hi = $mttr_timer_hi;
						$found = TRUE;
						break;
				}
				if ($found) break;
			}
		}

		$idl_found = (strpos($output, 'IDL', $i)!==false);
		$rnd_found = (strpos($output, 'RND', $i)!==false);
		$rcd_found = (strpos($output, 'RDCONN', $i)!==false);
	endwhile;

	return $ruleSets;
}
?>
