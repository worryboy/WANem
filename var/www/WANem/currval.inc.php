<?
/****************************************************************************/
/*                   		COPYRIGHT				    */
/*                                                                          */
/* 		Please have a look at CopyrightInformation.txt		    */
/*                                                                          */
/****************************************************************************/

//Function to replace the $_POST values with the netem values which are currently running
//on a selected interface.
//***************************************************************************************
function get_current_values($interfaces, &$del, &$delJitter, &$delCorrelation, &$delDistribution, &$loss, &$lossCorrelation, &$dup, &$dupCorrelation, &$reorder, &$reorderCorrelation, &$gap, &$bandwidth, &$corrupt, &$sym, &$disc, &$limit, &$msFound, &$src, &$srcSub, &$dest, &$destSub, &$advanced) {
	
	global $tc_CMD;
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

		//Loop through all interfaces in the $interfaces[] array
		for ($x=0; $x<count($interfaces); $x++) {

			//Set selected interface variable
			$selectedInterface=$interfaces[$x];

			//***Check the interface for more than one running rule set***
			$i=0; //output position counter
			$n=0; //flowid instance counter

			//Get the output of "tc filter show dev" . $selectedInterface
			$output2=shell_exec($tc_CMD.' filter show dev ' . $selectedInterface);
			while (strpos($output2, 'flowid', $i)!==false):
				++$n;
				$i=strpos($output2, 'flowid', $i) + 7;
			endwhile;
			//If more than two instances of flowid were found then set $advanced[x] to 1. In advanced mode
			//there will always be at least 3 flowids if there's more than 1 rule set.
			if ($n>=3) {
				$advanced[$x]=1;
			}

			//Reset variables that will be used to show which rules were found and which ones
			//weren't
			$delFound=FALSE;
			$lossFound=FALSE;
			$dupFound=FALSE;
			$reorderFound=FALSE;
			$corruptFound=FALSE;
			$bandwidthFound=FALSE;
			$symFound=FALSE;
			//Empty $disc
			unset($disc);
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
						//**Get interface name**
						//Move the pointer to 4 characters after the start of 'dev'
						$n=(strpos($lineStr, 'dev')+4);
						//Reset $tmpStr2
						$tmpStr2="";
						while (ord(substr($lineStr, $n, 1))!=32):
							//Add character to $tmpStr
							$tmpStr2=$tmpStr2 . substr($lineStr, $n, 1);
							//increment $n
							$n=($n+1);
						endwhile;
						//Set $limitFound to false, this flag will be used so that
						//the $limit value for the interface is only checked once
						$limitFound=false;

						//Check if interface name matches selected interface
						if ($tmpStr2==$selectedInterface) {

							//**Get netem values and set the correct $_POST variables**
							//Check for delay
							if (strstr($lineStr, "delay")!=FALSE) {
								//Set $delFound to TRUE
								$delFound=TRUE;
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
									while (ord(substr($lineStr, $z, 1))!="s"):
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
							}

							//**Check for limit value**
							//Look for first occurence of 'limit' in $output. It doesn't
							//matter where the first occurence is because all limits will
							//be the same unless the user manually typed commands with
							//different limits.
							if ($limitFound==false) {
								$output2=shell_exec($tc_CMD.' -d qdisc');
								$n=strpos($output2, "limit");
								if ($n===false) {
									//If a limit value was not found then set it to the default
									//value of 1000
									$limit[$x]=1000;
								} else {
									//Set $n to 6 spaces after 'limit'
									$n=$n+6;
									$tmpStr3="";
									while (ord(substr($output2, $n, 1))!=32):
										//Add character to $tmpStr
										$tmpStr3=$tmpStr3 . substr($output2, $n, 1);
										//increment $n
										$n=($n+1);
									endwhile;
									$limit[$x]=$tmpStr3;
								}
								$limitFound=true;
							}
						}
					}
				}
			}
			//Set non-found netem values to zero
			if ($delFound==FALSE) {
				$del[$x]="0";
				$delJitter[$x]="0";
				$delCorrelation[$x]="0";
				$delDistribution[$x]="-N/A-";
			}
			if ($lossFound==FALSE) {
				$loss[$x]="0";
				$lossCorrelation[$x]="0";
			}
			if ($dupFound==FALSE) {
				$dup[$x]="0";
				$dupCorrelation[$x]="0";
			}
			if ($corruptFound==FALSE) {
				$corrupt[$x]="0";
			}
			if ($reorderFound==FALSE) {
				$reorder[$x]="0";
				$reorderCorrelation[$x]="0";
				$gap[$x]="0";
			}
			$limit[$x]=99999999;
			$sym="No";
		}
	} else {
		//Show all values as zero
		//Loop through all interfaces
		for ($x=0; $x<count($interfaces); $x++) {
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
			$gapDel[$x]="0";
			$limit[$x]=99999999;
			$sym="No";
		}
	}

	//**Check for 'htb'**

	//Loop through all interfaces
	for ($x=0; $x<count($interfaces); $x++) {
		//Get the tc class status
		$output=shell_exec($tc_CMD.' class show dev ' . $interfaces[$x]);
		if (strstr($output, "htb")!=FALSE & strstr($output, "rate")!=FALSE) {
			//Set $n to start of bandwidth value
			$n=(strpos($output, "rate")+5);

			//**********************
			//Get bandwidth value
			//**********************
			//Get bandwidth (Kbits/s)
			//Reset tmpStr3
			$tmpStr3="";
			$gotNumber=false;
			while ($gotNumber==false):
				//If character is not 'b', 'K', or 'M'
				if (substr($output, $n, 1)!="b" & substr($output, $n, 1)!="K" & substr($output, $n, 1)!="M") {
					//Add character to $tmpStr
					$tmpStr3=$tmpStr3 . substr($output, $n, 1);
					//increment $n
					$n=($n+1);
				} else {
					//else set $gotNumber to true
					$gotNumber=true;
				}
			endwhile;
			//Check whether $n is 'b', 'K' or 'M' and convert $tmpStr3 accordingly
			if (substr($output, $n, 1)=="b") {
				$bandwidth[$x]=$tmpStr3/1000;
			} elseif (substr($output, $n, 1)=="K") {
				$bandwidth[$x]=$tmpStr3;
			} elseif (substr($output, $n, 1)=="M") {
				$bandwidth[$x]=$tmpStr3*1000;
			}
		} else {
			//Set non-found bandwidth value to zero
			$bandwidth[$x]="0";
		}
	}

	//**Check for ip matching**
	//Set ip addresses to default values initially
	$src[$x]="any";
	$srcSub[$x]="";
	$dest[$x]="any";
	$destSub[$x]="";
	$srcFound=false;
	$destFound=false;
	//Loop through all interfaces
	for ($x=0; $x<count($interfaces); $x++) {
		$output=shell_exec($tc_CMD.' filter show dev ' . $interfaces[$x]);
		if (strstr($output, "match")!=FALSE) {
			//Set pointer to 6 spaces after the first instance of 'match'
			$n=strpos($output, "match")+6;
			//Get ip address data
			$tmpStr=substr($output, $n, 8);
			//Convert ip address to four octet format
			$ipAddr="";
			for ($m=0;$m<=7;$m=$m+2) {
				$ipAddr=$ipAddr . hexdec(substr($tmpStr,$m,2)) . ".";
			}
			//Remove trailing dot
			$ipAddr=substr($ipAddr,0,-1);

			//Get subnet data
			$n=$n+9;
			$tmpStr=substr($output, $n, 8);
			//Convert subnet to single number format
			$tmpStr=decbin(hexdec($tmpStr));
			$ipSubnet=0;
			for ($m=0;$m<strlen($tmpStr);++$m) {
				if (substr($tmpStr,$m,1)=="1") {
					++$ipSubnet;
				}
			}
			//Check whether it's the source or the destination address.  Set $src or $dest
			//to $ipAddr and set $srcSub or destSub to $ipSubnet. 12=source 16=dest
			//Set $n to the number after "at" 21 and 22
			
			//Do not add IP data if it is 0.0.0.0/0 (automatically generated filter rule to match all
			//other packets)
			if ($ipAddr!="0.0.0.0" & $ipSubnet!="0") {
				if (substr($output, $n+12, 2)==12) {
					$src[$x]=$ipAddr;
					$srcSub[$x]=$ipSubnet;
					$srcFound=true;
				} else {
					$dest[$x]=$ipAddr;
					$destSub[$x]=$ipSubnet;
					$destFound=true;
				}
			}

			//Check for a second occurance of the word 'match'
			//Get the part of $output after the first occurance of 'match'
			$output=substr($output,$n);
			if (strstr($output, "match")!=FALSE) {
				//Set pointer to 6 spaces after the first instance of 'match'
				$n=strpos($output, "match")+6;
				//Get ip address data
				$tmpStr=substr($output, $n, 8);
				//Convert ip address to four octet format
				$ipAddr="";
				for ($m=0;$m<=7;$m=$m+2) {
					$ipAddr=$ipAddr . hexdec(substr($tmpStr,$m,2)) . ".";
				}
				//Remove trailing dot
				$ipAddr=substr($ipAddr,0,-1);

				//Get subnet data
				$n=$n+9;
				$tmpStr=substr($output, $n, 8);
				//Convert subnet to single number format
				$tmpStr=decbin(hexdec($tmpStr));
				$ipSubnet=0;
				for ($m=0;$m<strlen($tmpStr);++$m) {
					if (substr($tmpStr,$m,1)=="1") {
						++$ipSubnet;
					}
				}
				//Check whether it's the source or the destination address.  Set $src or $dest
				//to $ipAddr and set $srcSub or destSub to $ipSubnet. 12=source 16=dest
				//Check whether it's the source or the destination address.  Set $src or $dest
				//to $ipAddr and set $srcSub or destSub to $ipSubnet. 12=source 16=dest
				//Set $n to the number after "at" 21 and 22
				
				//Do not add IP data if it is 0.0.0.0/0 (automatically generated filter rule to match all
				//other packets)
				if ($ipAddr!="0.0.0.0" & $ipSubnet!="0") {
					if (substr($output, $n+12, 2)==12) {
						$src[$x]=$ipAddr;
						$srcSub[$x]=$ipSubnet;
					} else {
						$dest[$x]=$ipAddr;
						$destSub[$x]=$ipSubnet;
					}
				}
			} else {
				if ($srcFound==false) {
					$src[$x]="any";
					$srcSub[$x]="";
				}
				if ($destFound==false) {
					$dest[$x]="any";
					$destSub[$x]="";
				}
			}
		} else {
			$src[$x]="any";
			$srcSub[$x]="";
			$dest[$x]="any";
			$destSub[$x]="";
		}
		$port[$x]="any";
	}
}
?>
