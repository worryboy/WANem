<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*                                                                          */
/*           Please have a look at CopyrightInformation.txt                 */
/*                                                                          */
/****************************************************************************/
/*                                                                          */

include("config.inc.php");
?>
<html>
<head>
<title>WANem status</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
</head>
<body>
<form method="POST" action="status.php">
	<p align="center"><b>WANem status</b></p>
	<?
	//Get interfaces
	if  ($_POST[interfaceList]) {
		echo '<input type="hidden" name="interfaces" value="' . $_POST[interfaceList] . '"/>';
	} else {
		echo '<input type="hidden" name="interfaces" value="' . $_POST[interfaces] . '"/>';
	}

	//Put interfaces into the $interfaces array
	unset($interfaces);
	if  ($_POST[interfaceList]) {
		$tmpStr=$_POST[interfaceList];
	} else {
		$tmpStr=$_POST[interfaces];
	}
	$tmpStr2="";  //tmpStr2 will hold a single interface name
	for ($i = 0; $i<=strlen($tmpStr); $i++) {
		//Each interface name has a space after it, including the last one, so check
		//for a space after each one
		if (ord(substr($tmpStr, $i, 1)) != 32) {
			$tmpStr2=$tmpStr2 . substr($tmpStr, $i, 1);
		} else {
			$interfaces[]=$tmpStr2;
			$tmpStr2="";
		}
	}

	//***Check each interface for more than one rule set then rewrite the $interfaces array
	//accordingly***
	//Loop through all interfaces in the $interfaces[] array
	for ($x=0; $x<count($interfaces); $x++) {

		//Set selected interface variable
		$selectedInterface=$interfaces[$x];

		//***Check the interface for more than one running rule set***
		$i=0; //output position counter
		$n=0; //flowid instance counter

		//Get the output of "tc filter show dev" . $selectedInterface
		$output2=shell_exec('/sbin/tc filter show dev ' . $selectedInterface);
		$pflowstr = "";
		while (($j = strpos($output2, 'flowid', $i))!==false):
			$cflowstr = subStr($output2, $j + 7, 4);
			if ($pflowstr !== $cflowstr) ++$n;
			$pflowstr = $cflowstr;
			$i=$j + 7;
		endwhile;
		//Check all instances of match for 00000000
		//Divide the number found by 2 (2 per final filter value)
		//Subtract that number from $n
		$i=0;
		$count=0;
		while (strpos($output2, 'match', $i)!==false):
			$i=strpos($output2, 'match', $i)+6;
			if (subStr($output2, $i, 17)=="00000000/00000000") {
				++$count;
			}
		endwhile;
		$n=$n-($count/2);
		//add $n interface instances to $tmpArray
		//If $n==0 then just add one instance
		if ($n<=0) {
			$n=1;
		}
		for ($m=1; $m<=$n; $m++) {
			$tmpArray[]=$interfaces[$x];
		}
	}
	//Set $interfaces to the new values
	$interfaces=$tmpArray;
	//Flag to only tell the user once if no values have been found on any interface
	$noValuesFound=false;

	//Ruleset is an array which mirrors the $interfaces array and holds the interface element's
	//rule set position.
	//The first element of $ruleSet is always 1
	$ruleSet[0]=1;
	$tmpStr=$interfaces[0]; //The value of the first interface element
	for ($n = 1; $n < count($interfaces); $n++) {
		//if the next interface matches the previous interface then the next $ruleSet element value =
		// the previous $ruleSet element value + 1, otherwise it equals 1
		//Example arrays:
		//$interfaces[lo, eth0, eth0, eth1]
		//$ruleSet[1, 1, 2, 1]
		if ($interfaces[$n]==$tmpStr) {
			$ruleSet[$n]=$ruleSet[$n-1]+1;
		} else {
			$ruleSet[$n]=1;
		}
		$tmpStr=$interfaces[$n];
	}
	for ($int = 0; $int < count($interfaces); $int++) {
		//Call function to get and display currently running netem commands for each interface
                if(strcmp($interfaces[$int],"lo")!=0 && strcmp($interfaces[$int],"")!=0){
	        	get_current_values($interfaces, $int, $noValuesFound, $ruleSet);
               }
	}
	?>
	<p><input type="submit" value="Refresh" name="B1"></p>
</form>

<?
//***************************************************************************************
//Function to find the netem rules which are currently running on a selected interface
//and display them on screen.
//***************************************************************************************
function get_current_values($interfaces, $int, &$noValuesFound, $ruleSet) {
        include("config.inc.php");

	global $tc_CMD;
	//Get the tc status
	$output=shell_exec($tc_CMD.' -s qdisc');
	//Check for the word 'netem' or 'htb' in the output. If none are there then there's
	//no need to carry on with this part of the function.
	if (strstr($output, "netem")!=FALSE | strstr($output, "htb")!=FALSE ) {
		//****Get the number of lines in $output and the starting character of each line****
		//unset($lineStart);
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

		//Set selected interface variable
		$selectedInterface=$interfaces[$int];
		//$display is everything that will be output for the current interface
		//First add the interface name to $display
		//Only display this if $ruleSet is 0 or 1
		if ($ruleSet[$int]==1) {
			$display="<b>Interface: " .$selectedInterface . "</b></br>";
		}

		//Reset variables that will be used to show which rules were found and which ones
		//weren't
		$delFound=FALSE;
		$lossFound=FALSE;
		$dupFound=FALSE;
		$reorderFound=FALSE;
		$corruptFound=FALSE;
		$bandwidthFound=FALSE;
		$bytesFound=FALSE;

		//Loop through lines
		for ($i = 0; $i < $lines; $i++) {
			//If on the first line then set pointer($n) to first character else set it to
			//the corresponding $linestart value
			if ($i==0) {
				$n=0;
			} else {
				$n=$lineStart[$i-1];
			}

			//$checkBytes is for whether the byte/packet data should be found after this
			//itteration.  Set it to false before each line check.
			$checkBytes=FALSE;

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
						//Check for a match against $ruleSet
						//Check for a parent minor value and whether it matches the value of $ruleSet.
						//If it exists and does not match then ignore the rest of this section.
						//Check for "parent"
						$ruleSetMatch=false;
						If (strpos($lineStr, 'parent')>0) {
							$m=strpos($lineStr, 'parent');
							//Find the next occurance of ":"
							$m=strpos($lineStr, ':', $m)+1;
							if ($ruleSet[$int]==substr($lineStr, $m, 1)) {
								$ruleSetMatch=true;
							}
						} else {
							//if "parent" was not found then $ruleSetMatch will be true
							$ruleSetMatch=true;
						}
						//If "parent was found then get the number after ":" and check for a match
						//against $ruleSet
						if ($ruleSetMatch==true) {
							//**Get netem values and set the correct $_POST variables**
							//Check for delay
							if (strstr($lineStr, "delay")!=FALSE) {
								//Set $delFound to TRUE
								$delFound=TRUE;

								//Set $checkBytes to true if $bytesFound is false so that we get
								//the byte/packet data after this itteration
								if ($bytesFound==false) {
									$checkBytes=true;
								}

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
	
								//Add the correct delay value in ms to the display variable
								if ($usFound==false) {
									if ($msFound==false) {
										$display=$display . "Delay: " . $tmpStr4*1000 . "ms";
									} else {
										$display=$display . "Delay: " . $tmpStr3 . "ms";
									}
								} else {
									$display=$display . "Delay: 1ms";
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
	
										//Add the correct delay jitter value in ms to the
										//display variable
										if ($usFound==false) {
											if ($msFound==false) {
												$display=$display . "  Delay Jitter: " . $tmpStr4*1000 . "ms";
											} else {
												$display=$display . "  Delay Jitter: " . $tmpStr3 . "ms";
											}
										} else {
											$display=$display . "Delay Jitter: 1ms";
										}
									}
								} else {
									$tmpStr3="";
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
	
										//Add delay correlation and a line break to the display variable
										$display=$display . "  Delay correlation: " . $tmpStr3 . "%";
									}
								}
							//Add a line to the display variable
							$display=$display. "</br>";
							}
	
							//Check for loss
							if ((strstr($lineStr, "loss")!=FALSE)) {
								//Set $lossFound to TRUE
								$lossFound=TRUE;
	
								//Set $checkBytes to true if $bytesFound is false so that we get
								//the byte/packet data after this itteration
								if ($bytesFound==false) {
									$checkBytes=true;
								}
	
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
								//Add loss to the display variable
								$display=$display . "Loss: " . $tmpStr3 . "%";
	
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
	
										//Add loss correlation to the display variable
										$display=$display . "  Loss correlation: " . $tmpStr3 . "%";
									}
								}
							//Add a line to the display variable
							$display=$display . "</br>";
							}

							//Check for duplication
							if ((strstr($lineStr, "duplicate")!=FALSE)) {
								//Set $dupFound to TRUE
								$dupFound=TRUE;
								//Set $checkBytes to true if $bytesFound is false so that we get
								//the byte/packet data after this itteration
								if ($bytesFound==false) {
									$checkBytes=true;
								}

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
	
								//Add duplication to the display variable
								$display=$display . "Duplication:  " . $tmpStr3 . "%";
	
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
	
										//Add dupliction correlation to the display variable
										$display=$display . "  Duplication correlation: " . $tmpStr3 . "%";
									}
								}
							//Add a line to the display variable
							$display=$display . "</br>";
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
								$display=$display . "Corruption:  " . $tmpStr3 . "%";

                                                        }

							//Check for reorder
							if ((strstr($lineStr, "reorder")!=FALSE)) {
								//Set $reorderFound to TRUE
								$reorderFound=TRUE;
	
								//Set $checkBytes to true if $bytesFound is false so that we get
								//the byte/packet data after this itteration
								if ($bytesFound==false) {
									$checkBytes=true;
								}


								//**********************
								//Get reorder values
								//**********************

								//**Get reorder**
								//Set $n to start of reorder value
								$n=(strpos($lineStr, "reorder")+8);
								//Reset tmpStr3
								$tmpStr3="";
								//While $n not pointing at a % character
								while (ord(substr($lineStr, $n, 1))!=37):
									//Add character to $tmpStr
									$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
									//increment $n
									$n=($n+1);
								endwhile;

								//Add reorder to the display variable
								$display=$display . " Reorder: " . $tmpStr3;

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

									//Add reorder correlation to the display variable
									$display=$display . " Reorder correlation= " . $tmpStr3;

								}


								//**Get gap**
								//Set $n to start of gap value
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
	
								//Add gap to the display variable
								$display=$display . "  Gap: " . $tmpStr3;

								//Add a line to the display variable
								$display=$display . "</br>";
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
									$limit=1000;
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
									$limit=$tmpStr3;
								}
								$limitFound=true;
							}
						}
					}
				}
			}
			//*********************************
			//Get byte and packet transfer data
			//*********************************

			//Check if $checkBytes is true and $bytesFound is false
			if ($checkBytes==true & $bytesFound==false) {
				$bytesFound=true;
				//Get next line into a string if the current line is an even line
				$x=($i);
				if ($x <= $lines) {
					$tmpStr3="";
					$lineStr=substr($output, $lineStart[$x], (($lineStart[$x+1]-1)-$lineStart[$x]));

					//Get Bytes sent
					//Set the pointer to the 6th character of the line, just after 'Sent'
					$n=6;
					//While $n not pointing at a space character
					while (ord(substr($lineStr, $n, 1))!=32):
						//Add character to $tmpStr
						$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
						//increment $n
						$n=($n+1);
					endwhile;
					$bytesSent=$tmpStr3;
					$tmpStr3="";

					//Get packets sent
					//Set the pointer to the 6th character after 'bytes'
					$n=(strpos($lineStr, "bytes") + 6);
					//While $n not pointing at a space character
					while (ord(substr($lineStr, $n, 1))!=32):
						//Add character to $tmpStr
						$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
						//increment $n
						$n=($n+1);
					endwhile;
					$packetsSent=$tmpStr3;
					$tmpStr3="";

					//Get packets dropped
					//Set the pointer to the 8th character after 'dropped'
					$n=(strpos($lineStr, "dropped") + 8);
					//While $n not pointing at a space character
					while (ord(substr($lineStr, $n, 1))!=32):
						//Add character to $tmpStr
						$tmpStr3=$tmpStr3 . substr($lineStr, $n, 1);
						//increment $n
						$n=($n+1);
					endwhile;
					$packetsDropped=$tmpStr3;
				}
			}
		}

		if ($bandwidthFound==false) {
			//**Check for 'htb'**
			//Get the tc class status
			$output=shell_exec($tc_CMD.' class show dev ' . $selectedInterface);
			$lines=0;
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
				if (strpos($lineStr, "htb")!==FALSE & strpos($lineStr, "rate")!==FALSE) {
					//Check if the major class number value corresponds with the current $ruleSet value
					//Example:  htb 11:1 matches $ruleSet=1 , htb 23:1 matches $ruleSet=2. Get the first
					//digit after htb
					if (substr($lineStr, (strpos($lineStr, "htb")+4), 1) == $ruleSet[$int]) {
						//Set $n to start of bandwidth value
						$n=(strpos($lineStr, "rate")+5);
						$bandwidthFound=true;

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
							$display=$display . "Bandwidth: " . $tmpStr3/1000 . "Kbits/s</br>";
						} elseif (substr($lineStr, $n, 1)=="K") {
							$display=$display . "Bandwidth: " . $tmpStr3 . "Kbits/s</br>";
						} elseif (substr($lineStr, $n, 1)=="M") {
							$display=$display . "Bandwidth: " . $tmpStr3*1000 . "Kbits/s</br>";
						}
					}
				}
			}
		}

		//**Check for ip matching**

		$output=shell_exec($tc_CMD.' filter show dev ' . $selectedInterface);

		if (strpos($output, "flowid")!==false) {
			$loop=0;
			$flowidPos[0]=strpos($output, "flowid");
			while (strpos($output, "flowid", $flowidPos[$loop]+10)!==false):
				++$loop; //$n=loop counter
				$flowidPos[$loop]=strpos($output, "flowid", $flowidPos[$loop-1]+10);
			endwhile;
		}

		//Loop through $loop.  If there is only one rule set or it is in basic mode then there will be just
		//one itteration.
		for ($x=0; $x<=$loop; ++$x) {
			if (strstr($output, "match")!=FALSE) {
				//Check if the flowid minor number matches $ruleSet.  This number will always be 10 spaces
				//after $flowidPos. e.g. flowid xx:y
				//                       <-------->
				//							10 spc
				if (subStr($output, $flowidPos[$x]+10,1)==$ruleSet[$int]) {
					//Set pointer to 6 spaces after the first instance of 'match' after the current
					//instance of flowid
					$n=strpos($output, "match", $flowidPos[$x])+6;
					//Get ip address data
					$tmpStr=substr($output, $n, 8);
					//Convert ip address to four octet format
					$ipAddr="";
					for ($m=0;$m<=7;$m=$m+2) {
						$ipAddr=$ipAddr . hexdec(substr($tmpStr,$m,2)) . ".";
					}
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

					if ($ipAddr!="0.0.0.0" & $ipSubnet!="0") {
						if (substr($output, $n+12, 2)==12) {
							//Remove trailing dot and add ip address to display
							$display=$display . "IP source address: " . substr($ipAddr,0,-1);
							//Add destination subnet to display
							$display=$display . "/" . $ipSubnet . " ";
						} else {
							//Remove trailing dot and add ip address to display
							$display=$display . "IP destination address: " . substr($ipAddr,0,-1);
							//Add destination subnet to display
							$display=$display . "/" . $ipSubnet . " ";
						}
						$lastFilterRule=true;
					} else {
						//Set the flag to prevent "No running netem rules" from being shown
						$lastFilterRule=true;
					}

					//Check for a second occurance of the word 'match'
					//Get the part of $output after the first occurance of 'match' (source)
					$output2=substr($output,$n);
					//Check if there is an instance of "flowid" before the next instance of "match"
					//if there is then do not continue with this section, as the next "match" is
					//for the next rule set.
					$ignore=false;
					if (strstr($output2, "match")!=false) {
						if (strstr($output2, "flowid")) {
							//if the first instance of "match" appears after flowid then it is for the
							//next rule set and so should be ignored.
							if (strpos($output2, "match") > strpos($output2, "flowid")) {
								$ignore=true;
							}
						}
						if ($ignore==false) {
							//Set pointer to 6 spaces after the first instance of 'match' (destination)
							$n=strpos($output2, "match")+6;
							//Get ip address data
							$tmpStr=substr($output2, $n, 8);
							//Convert ip address to four octet format
							$ipAddr="";
							for ($m=0;$m<=7;$m=$m+2) {
								$ipAddr=$ipAddr . hexdec(substr($tmpStr,$m,2)) . ".";
							}
							//Get subnet data
							$n=$n+9;
							$tmpStr=substr($output2, $n, 8);
							//Convert subnet to single number format
							$tmpStr=decbin(hexdec($tmpStr));
							$ipSubnet=0;
							for ($m=0;$m<strlen($tmpStr);++$m) {
								if (substr($tmpStr,$m,1)=="1") {
									++$ipSubnet;
								}
							}
							if ($ipAddr!="0.0.0.0" & $ipSubnet!="0") {
								if (substr($output2, $n+12, 2)==12) {
									//Remove trailing dot and add ip address to display
									$display=$display . "IP source address: " . substr($ipAddr,0,-1);
									//Add destination subnet to display
									$display=$display . "/" . $ipSubnet;
								} else {
									//Remove trailing dot and add ip address to display
									$display=$display . "IP destination address: " . substr($ipAddr,0,-1);
									//Add destination subnet to display
									$display=$display . "/" . $ipSubnet;
								}
								$lastFilterRule=false;
							} else {
								//Set the flag to prevent "No running netem rules" from being shown
								$lastFilterRule=true;
							}
						}
					}
				$display=$display."</BR>";	
				}
			}
		}
		//Add a line break to display
		if (strstr($output, "match")!=FALSE) {
			$display=$display . "</br>";
		}
                 if (($delFound==FALSE) and ($lossFound==FALSE) and ($dupFound==FALSE) and ($reorderFound==FALSE) and ($bandwidthFound==FALSE))
                 {
			//Add 'No running WANem commands' to the display variable
			$display=$display . "No running WANem rules";
			//Add a horizontal rule and a line break to the display variable
			$display=$display . "<hr></br>";
		} else {
			//Add limit to the display variable
			$display=$display . "  WANem packet limit: " . $limit . " packets</br>";
			
			//Only display byte/packet data if this is the last rule set for this interface
			//Check if there are any more $interfaces elements to loop through
			//Add bytes sent to display variable
			$display=$display . "Bytes sent: " . $bytesSent . "  ";
			$display=$display . "Packets sent: " . $packetsSent . "  ";
			$display=$display . "Packets dropped: " . $packetsDropped . "</br>";

			//Add a horizontal rule and a line break to the display variable
			//If $ruleSet is not the last for this interface then do not add a horizontal rule
			if ($int+1<=count($interfaces) & $ruleSet[$int]<$ruleSet[$int+1]) {
				$display=$display . "</br>";
			} else {
				$display=$display . "<hr></br>";
			}
		}

		//Display the display variable
		echo $display;
		$output=intval(shell_exec($disconnect_DIR . '/check_disco.sh'));
		if ($output >= 1) {

			//Get the output of "tc filter show"
			$disc_cmd='sudo grep -v "#" '. $disconnect_DIR .'/input.dsc | grep '. $selectedInterface;
			//$disc_cmd='sudo grep -v "#" '. $disconnect_DIR .'/input.dsc 2>&1';
			$output=shell_exec($disc_cmd);
			echo $output;
		}

	} else {
		if ($noValuesFound==false) {
			//No running WANem rules for any interface
			echo "No running WANem rules on any interface";
			$noValuesFound=true;
		}
	}
}
?>
