<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*                                                                          */
/*            Please have a look at CopyrightInformation.txt                */
/*                                                                          */
/****************************************************************************/
/*                                                                          */

include_once("disc.inc.php");

//********************
//Validation functions
//********************

//Check which primary values have been set, if any, and set the correct $combination array
//values to true when a valid value is found.

function validate_primary_values(&$interfaces, &$del, &$delJitter, &$delCorrelation, &$delDistribution, &$loss, &$lossCorrelation, &$dup, &$dupCorrelation, &$reorder, &$reorderCorrelation, &$gap, &$bandwidth, &$corrupt, &$sym, &$disc, &$limit, &$valid, &$combination, &$errMsg, &$src, &$srcSub, &$dest, &$destSub, &$port, $advancedMode)
{
	//Set $errorDisplayed to false. This will stop the script from carrying on with
	//validation after an error.
	$errorDisplayed=false;

	//Loop through all interfaces.  A do-while loop is used to check for validation errors
	//AND whether the last interface has been reached.  The interface will be incremented
	//from the array at the end of each loop.

	//Set $x to the first non-empty $interfaces element
	$x=0;
	//Check $interfaces[$x] to see if it's empty, if it is then add 1 to $x and check if that is empty.  Stop when the
	//first non-empty element was found.
	if (empty($interfaces[$x])) {
		$found=false;
		while ($found==false):
			//If next interface is not empty
			if (!empty($interfaces[$x+1])) {
				++$x;
				$found=true;
			//else if next interface is empty and past the last possible interface element
			} else {
				++$x;
				if (($x)>=count($interfaces)) {
					$found=true;
				}
			}
		endwhile;
	}

	//*****Find the correct number of interfaces for advanced mode*****
	if ($advancedMode==1) {
		//Count the number of rule sets that were being displayed by checking for post values

		//Set counter to 2
		$n=2;
		//Get the name of the selected interface
		$tmpInt=$interfaces[0];
		//Add an initial interface instance to the array
		$ints[]=$tmpInt;
		//Set $tmp to "selDelayDistribution2"
		$tmp="selDelayDistribution" . ($n);
		while ($_POST[$tmp]):
			//Add an interface
			$ints[]=$tmpInt;
			++$n;
			$tmp="selDelayDistribution" . ($n);
		endwhile;
		$interfaces=$ints;
	}
	//**********

	while ($x<count($interfaces)):

		//Set all value variables from $_POST variables
		$tmp="txtDelay" . ($x+1);
		$del[$x]=$_POST[$tmp];
		$tmp="txtDelayJitter" . ($x+1);
		$delJitter[$x]=$_POST[$tmp];
		$tmp="txtDelayCorrelation" . ($x+1);
		$delCorrelation[$x]=$_POST[$tmp];
		$tmp="selDelayDistribution" . ($x+1);
		$delDistribution[$x]=$_POST[$tmp];
		$tmp="txtLoss" . ($x+1);
		$loss[$x]=$_POST[$tmp]; //echo "floss=".$loss[$x];
		$tmp="txtLossCorrelation" . ($x+1);
		$lossCorrelation[$x]=$_POST[$tmp];
		$tmp="txtDup" . ($x+1);
		$dup[$x]=$_POST[$tmp];
		$tmp="txtDupCorrelation" . ($x+1);
		$dupCorrelation[$x]=$_POST[$tmp];
		$tmp="txtReorder" . ($x+1);
		$reorder[$x]=$_POST[$tmp];
		$tmp="txtReorderCorrelation" . ($x+1);
		$reorderCorrelation[$x]=$_POST[$tmp];
		$tmp="txtGap" . ($x+1);
		$gap[$x]=$_POST[$tmp];
		$tmp="txtCorrupt" . ($x+1);
		$corrupt[$x]=$_POST[$tmp];
		$tmp="txtBandwidth" . ($x+1);
		$bandwidth[$x]=$_POST[$tmp];
		$tmp="txtBandwidthAuto" . ($x+1);
		$bandwidthAuto[$x]=$_POST[$tmp];
		$tmp="selSym" . ($x+1);
		$sym[$x]=$_POST[$tmp];
		if (!($disc[$x])) $disc[$x] = new disc_object;
		$tmp="selidtyp" . ($x+1);
		$disc[$x]->idl_type=$_POST[$tmp];
		$tmp="txtidtmr" . ($x+1);
		$disc[$x]->idl_timer=$_POST[$tmp];
		//print "idle timer value = $_POST[$tmp] ";
		//print "idle timer value = ". $disc[$x]->idl_timer ;
		$tmp="txtidsctmr" . ($x+1);
		$disc[$x]->idl_disc_timer=$_POST[$tmp];
		$tmp="selrndtyp" . ($x+1);
		$disc[$x]->rnd_type=$_POST[$tmp];
		$tmp="txtrndmttflo" . ($x+1);
		$disc[$x]->rnd_mttf_lo=$_POST[$tmp];
		$tmp="txtrndmttfhi" . ($x+1);
		$disc[$x]->rnd_mttf_hi=$_POST[$tmp];
		$tmp="txtrndmttrlo" . ($x+1);
		$disc[$x]->rnd_mttr_lo=$_POST[$tmp];
		$tmp="txtrndmttrhi" . ($x+1);
		$disc[$x]->rnd_mttr_hi=$_POST[$tmp];
		$tmp="selrcdtyp" . ($x+1);
		$disc[$x]->rcd_type=$_POST[$tmp];
		$tmp="txtrcdmttflo" . ($x+1);
		$disc[$x]->rcd_mttf_lo=$_POST[$tmp];
		$tmp="txtrcdmttfhi" . ($x+1);
		$disc[$x]->rcd_mttf_hi=$_POST[$tmp];
		$tmp="txtrcdmttrlo" . ($x+1);
		$disc[$x]->rcd_mttr_lo=$_POST[$tmp];
		$tmp="txtrcdmttrhi" . ($x+1);
		$disc[$x]->rcd_mttr_hi=$_POST[$tmp];
		$tmp="txtLimit" . ($x+1);
		$limit[$x]=$_POST[$tmp];
		$tmp="txtSrc" . ($x+1);
		$src[$x]=$_POST[$tmp];
		$tmp="txtSrcSub" . ($x+1);
		$srcSub[$x]=$_POST[$tmp];
		$tmp="txtDest" . ($x+1);
		$dest[$x]=$_POST[$tmp];
		$tmp="txtDestSub" . ($x+1);
		$destSub[$x]=$_POST[$tmp];
		$tmp="txtPort" . ($x+1);
		$port[$x]=$_POST[$tmp];
		//Set $valid to false. This will become true when there is at least one valid value or
		//set of values which can make up a netem command.
		$valid=false;

		//Set all combination values to false
		for ($i=1;$i<=7;++$i) {
			$combination[$x][$i]=FALSE;
		}

		//****Check delay value****
		//If delay is set ok then check the delay sub-values

		//Check if the delay value is empty or zero.  If it is empty or zero then the delay
		//command will be ignored.
		if ((empty($del[$x]))==FALSE) {
			//Check if the value is numeric, if it is not numeric then display an error
			//message.
			if (is_numeric($del[$x])) {
				//The following command makes the value an integer.  This means that if the
				//entered value had a decimal point in it, the decimal point and anything
				//after it is stripped off. (eg. 10.6 goes to 10, 150.000004 goes to 150)
				$del[$x]=intval($del[$x]);

				//Check if the value is zero. This would occur if the value entered was between
				//zero and one. (eg. 0.5) Once the command above stripped away the decimal
				//point the number would turn to zero and should be ignored.
				if ((empty($del[$x]))==FALSE) {
				//Call the function to fully validate the delay value and its sub-values
					validate_delay($interfaces, $x, $errorDisplayed, $del, $delJitter, $delCorrelation, $errMsg);

					//If the validation was successful then set $combination[$x][1] to true
					//and set $valid to true.
					$combination[$x][1]=TRUE;
					$valid=true;

				}
		} else {
			//Display this error message if the value of delay is not numeric.
			$errMsg='ACTION FAILED: Delay for ' . $interfaces[$x] . ' must have a numeric value.';
			$errorDisplayed=true;
			}
		}

		if ($errorDisplayed==false) {
			//****Check loss value****
			//If loss is set ok then check the loss correlation value

			//Check if the loss value is empty or zero.  If it is empty or zero then the loss
			//command will be ignored.
			if ((empty($loss[$x]))==FALSE) {
				if (is_numeric($loss[$x])) {
					//make sure the value is a floating point number
					//as netem allows fractional loss
					$loss[$x]=floatval($loss[$x]);

					if ((empty($loss[$x]))==FALSE) {
						//Call the function to fully validate the loss and loss correlation values.
						validate_loss($interfaces, $x, $errorDisplayed, $loss, $lossCorrelation, $errMsg);
						$combination[$x][2]=TRUE;
						$valid=true;
					}
			} else {
				$errMsg='ACTION FAILED: Loss for ' . $interfaces[$x] . ' must have a numeric value.';
				$errorDisplayed=true;
				}
			}
		}

		if ($errorDisplayed==false) {
			//****Check duplication value****
			//If duplication is set ok then check the duplication correlation value

			//Check if the duplication value is empty or zero.  If it is empty or zero then the
			//duplication command will be ignored.
			if ((empty($dup[$x]))==FALSE) {
				if (is_numeric($dup[$x])) {
					//make sure the value is a floating point number
					$dup[$x]=floatval($dup[$x]);

					if ((empty($dup[$x]))==FALSE) {
						//Call the function to fully validate the duplication value and its
						//sub-values.
						validate_duplication($interfaces, $x, $errorDisplayed, $dup, $dupCorrelation, $errMsg);
						$combination[$x][3]=TRUE;
						$valid=true;
					}
			} else {
				if ($errorDisplayed==false) {
					$errMsg='ACTION FAILED: Duplication for ' . $interfaces[$x] . ' must have a numeric value.';
					$errorDisplayed=true;
					}
				}
			}
		}

		if ($errorDisplayed==false) {
			//****Check reorder value****
			//If reorder is set ok then check the correlation and gap values

			//Check if the reorder value is empty or zero.  If it is empty or zero then the
			//reorder command will be ignored.
			if ((empty($reorder[$x]))==FALSE) {
				if (is_numeric($reorder[$x])) {
					//make sure the value is a floating point number
					$reorder[$x]=floatval($reorder[$x]);

					if ((empty($reorder[$x]))==FALSE) {
						//Call the function to fully validate the reorder value and its
						//sub-values.
						validate_reorder($interfaces, $x, $errorDisplayed, $del, $reorder, $reorderCorrelation, $gap, $errMsg);
						$combination[$x][4]=TRUE;
						$valid=true;
					}
			} else {
				if ($errorDisplayed==false) {
					$errMsg='ACTION FAILED: Reorder for ' . $interfaces[$x] . ' must have a numeric value.';
					$errorDisplayed=true;
					}
				}
			}
		}

		if ($errorDisplayed==false) {
			//****Check corruption value****
			//If corruption is set ok then $valid=true & $combination[$x][5]=TRUE

			//Check if the corrupt value is empty or zero.  If it is empty or zero then the
			//reorder command will be ignored.
			if ((empty($corrupt[$x]))==FALSE) {
				if (is_numeric($corrupt[$x])) {
					//make sure the value is a floating point number
					$corrupt[$x]=floatval($corrupt[$x]);

					if ((empty($corrupt[$x]))==FALSE) {
						//Call the function to fully validate the corrupt value.
						validate_corrupt($interfaces, $x, $errorDisplayed, $corrupt, $errMsg);
						$combination[$x][5]=TRUE;
						$valid=true;
					}
			} else {
				if ($errorDisplayed==false) {
					$errMsg='ACTION FAILED: Corruption for ' . $interfaces[$x] . ' must have a numeric value.';
					$errorDisplayed=true;
					}
				}
			}
		}

		
		if ($errorDisplayed==false) {
			//****Check bandwidth value****
			//If bandwidth is set ok then $valid=true & $combination[$x][7]=TRUE
			if ($bandwidthAuto[$x] != "Other") {
				$bandwidth[$x]=$bandwidthAuto[$x];
			}
			// Validate the user specified bandwidth
			if ((empty($bandwidth[$x])) == FALSE) {
                               	if (is_numeric($bandwidth[$x])) {
                                       	//make sure the value is an integer
                                       	$bandwidth[$x]=intval($bandwidth[$x]);
                                       	if ((empty($bandwidth[$x]))==FALSE) {
                                               	//Call the function to fully validate the bandwidth.
                                               	validate_bandwidth($interfaces, $x, $errorDisplayed, $bandwidth, $errMsg);
                                               	$combination[$x][7]=TRUE;
                                               	$valid=true;
                                       	}
				} else {
                               		$errMsg='ACTION FAILED: Bandwidth for ' . $interfaces[$x] . ' must have a numeric value.';
                               		$errorDisplayed=true;
                        	}
			}                	
		}

		if ($errorDisplayed==false) {
			//***Check limit value***
			if ((empty($limit[$x]))==FALSE) {
				if (is_numeric($limit[$x])) {
					//make sure the value is an integer
					$limit[$x]=intval($limit[$x]);
					if ((empty($limit[$x]))==FALSE) {
						if ($limit[$x]<=0) {
							$limit[$x]=1000;
						}
					} else {
						$limit[$x]=1000;
					}
				} else {
					$limit[$x]=1000;
				}
			} else {
				$limit[$x]=1000;
			}
		}

		//****Check IP address values****
		//Subnets must be given a value if their matching ip address is given
		//a valid value other than 'any'.  The default value for ip addresses is 'any'
		// and if an ip address has the default value then a subnet value is not required.
		//The ip address value can *only* be 'any' or a valid ip address, nothing else.
		//Both source and destination ip addresses must have a valid value.  They will
		//both have the default value of 'any' if they have not been set by the user.

		//Check source address for a valid value
		if ($errorDisplayed==false) {
			if (trim($src[$x])!="any") {
				//If not valid ip address
				if (ereg ("^([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\$",trim($src[$x]))==FALSE) {
					$errMsg='ACTION FAILED: Source IP Address for ' . $interfaces[$x] . ' must be "any" or a valid IP Address.' . $src[$x];
					$errorDisplayed=true;
				}
			}
			//Check source address subnet
			if ($errorDisplayed==false) {
				if (trim($src[$x])!="any") {
					//check for single number version
					if ((is_numeric(trim($srcSub[$x])) & trim($srcSub[$x])>0  & trim($srcSub[$x])<=32)==FALSE) {
						//Check for four octet version
						if (ereg ("^(128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)\$",trim($srcSub[$x]),$regs)) {
							//Check for correct subnet (if the 2nd 3rd or 4th octet are greater than 0
							//then the octet to the left must be 255)
							for ($n=1;$n<=3;++$n) {
								if ($regs[$n]>0 & $regs[$n-1]<255) {
									$errMsg='ACTION FAILED: Invalid source subnet for ' . $interfaces[$x];
									$errorDisplayed=true;
								}
							}
							//Convert the subnet to the single number version.
							$binStr="";
							for ($n=1;$n<=4;++$n) {
								$binStr=$binStr . decbin($regs[$n]);
							}
							$srcSub[$x]=0;
							for ($n=0;$n<strlen($binStr);++$n) {
								if (subStr($binStr,$n,1)=="1") {
									++$srcSub[$x];
								}
							}
						} else {
							$errMsg='ACTION FAILED: Invalid source subnet for ' . $interfaces[$x];
							$errorDisplayed=true;
						}
					}
				}
			}
		}

		//Check destination address for a valid value
		if ($errorDisplayed==false) {
			if (trim($dest[$x])!="any") {
				//If not valid ip address
				if (ereg ("^([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\.([0-9]{1}|[1-9]{1}[0-9]{1}|1[0-9]{2}|2[0-4]{1}[0-9]{1}|25[0-5]{1})\$",trim($dest[$x]))==FALSE) {
					$errMsg='ACTION FAILED: Destination IP Address for ' . $interfaces[$x] . ' must be "any" or a valid IP Address.';
					$errorDisplayed=true;
				}
			}
			//Check destination address subnet
			if ($errorDisplayed==false) {
				if (trim($dest[$x])!="any") {
					//check for single number version
					if ((is_numeric(trim($destSub[$x])) & trim($destSub[$x])>0  & trim($destSub[$x])<=32)==FALSE) {
						//Check for four octet version
						if (ereg ("(128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)\.(0|128|192|224|240|248|252|254|255?)",trim($destSub[$x]),$regs)) {
							//Check for correct subnet (if the 2nd 3rd or 4th octet are greater than 0
							//then the octet to the left must be 255)
							for ($n=1;$n<=3;++$n) {
								if ($regs[$n]>0 & $regs[$n-1]<255) {
									$errMsg='ACTION FAILED: Invalid destination subnet for ' . $interfaces[$x];
									$errorDisplayed=true;
								}
							}
							//Convert the subnet to the single number version.
							$binStr="";
							for ($n=1;$n<=4;++$n) {
								$binStr=$binStr . decbin($regs[$n]);
							}
							$destSub[$x]=0;
							for ($n=0;$n<strlen($binStr);++$n) {
								if (subStr($binStr,$n,1)=="1") {
									++$destSub[$x];
								}
							}
						} else {
							$errMsg='ACTION FAILED: Invalid destination subnet for ' . $interfaces[$x];
							$errorDisplayed=true;
						}
					}
				}
			}
		}

		//check port value 
		if ($errorDisplayed==false) {
			//***Check limit value***
			if (trim($port[$x])!="any") {
				//echo "Port = ",$port[$x];
				if ((empty($port[$x]))==FALSE) {
					if (is_numeric($port[$x])) {
						//make sure the value is an integer
						$port[$x]=intval($port[$x]);
						if ((empty($port[$x]))==FALSE) {
							if (($port[$x]<1) || ($port[$x]>=65535)) {
								$errMsg='ACTION FAILED: Port No should be "any" or between 1 to 65535 for ' . $interfaces[$x];
								$errorDisplayed=true;
							} else {
								if (($srcSub[$x] != 32) || ($destSub[$x] != 32) || ($src[$x] == "any") || ($dest[$x] == "any")) {
									$errMsg='ACTION FAILED: Port Number should be used only for specific source and destination addresses with subnets of 32/255.255.255.255 for ' . $interfaces[$x];
									$errorDisplayed=true;
								}
							}
								
						} else {
							$errMsg='ACTION FAILED: Invalid port for ' . $interfaces[$x];
							$errorDisplayed=true;
						}
					} else {
						$errMsg='ACTION FAILED: Invalid port for ' . $interfaces[$x];
						$errorDisplayed=true;
					}
				} else {
					$errMsg='ACTION FAILED: Invalid port for ' . $interfaces[$x];
					$errorDisplayed=true;
				}
			}
		}



		//check for disconnect
		if ($errorDisplayed==false && $advancedMode == 1) {
			//****Check disconnection value****
			//If disconnection is set ok then $valid=true & $combination[$x][6]=TRUE

			//Check if the disc value is empty or zero.  If it is empty or zero then the
			//reorder command will be ignored.
			if ($disc[$x]) {
				if (is_string($disc[$x]->idl_type)) {
					//Call the function to fully validate the disconnect values.
					validate_disconnect($interfaces, $x, $errorDisplayed, $disc, $sym, $src, $srcSub, $dest, $destSub, $port, $combination, $errMsg);
					$combination[$x][6]=TRUE;
					$valid=true;
			} else {
				if ($errorDisplayed==false) {
					$errMsg='ACTION FAILED: Disconnection type for ' . $interfaces[$x] . ' must have a valid value.';
					$errorDisplayed=true;
					}
				}
			}
		}

		//**Advanced mode checks**
		//Check for advanced mode
		if ($advancedMode==1) {
			if ($errorDisplayed==false) {
				//Check if this rule set is not the first and has 'any any' as the IP matching values
				if ($x>0) {
					if ($src[$x]=="any" and $dest[$x]=="any") {
						$errMsg='ACTION FAILED: You cannot have "any" and "any" for both the source and destination IP address when more than one rule set is being used';
						$errorDisplayed=true;
					} else {
						//Loop through previous values and compare them against $src[$x] and $dest[$x]
						for ($n=0; $n<$x; ++$n) {
							if ($src[$x]==$src[$n] and $dest[$x]==$dest[$n] and $port[$x]==$port[$n]) {
								$errMsg='ACTION FAILED: Two rule sets cannot have the same IP address and port matching rules';
								$errorDisplayed=true;
							}
							if (($sym[$x]=="No") && ($sym[$n]=="No")) continue; 
							if ($src[$x]==$dest[$n] and $dest[$x]==$src[$n] and trim($port[$x])=="any" and trim($port[$n]=="any")) {
								$errMsg='ACTION FAILED: Two rule sets cannot have the same IP address  and port matching rules';
								$errorDisplayed=true;
							}
						}
					}
				}
			}
		}

		if ($errorDisplayed==false) {
			//If all of the primary values were empty or zero then set combination to zero,
			//this indicates a straight reset of netem values.
			if (empty($del[$x])==TRUE AND empty($loss[$x])==TRUE AND empty($dup[$x])==TRUE AND empty($reorder[$x])==TRUE AND empty($corrupt[$x])==TRUE AND empty($disc[$x]->idl_type)==TRUE AND empty($bandwidth[$x])==TRUE AND $bandwidthAuto[$x]=="Other") {
				//Set all combination values to false
				for ($i=1;$i<=7;++$i) {
					$combination[$x][$i]=FALSE;
				}
			}
		}
		//Increment $x to point to next interface if there is a next one
		++$x;
		//Check that $x is less than or equal to the number of $interface elements then find the next
		//none empty element.  If the last element is empty then still add 1 to $x because the while loop
		//at the top of the function will then evaluate to false and the function will end as normal.
		if (empty($interfaces[$x])) {
			$found=false;
			while ($found==false):
				//If next interface is not empty
				if (!empty($interfaces[$x+1])) {
					++$x;
					$found=true;
				//else if next interface is empty and past the last possible interface element
				} else {
					++$x;
					if (($x)>=count($interfaces)) {
						$found=true;
					}
				}
			endwhile;
		}
	endwhile;
	if ($errorDisplayed==FALSE) {
		$valid=TRUE;
	} else {
		$valid=FALSE;
	}
}


//*********************
//Validate delay values
//*********************
function validate_delay($interfaces, $x, &$errorDisplayed, $del, &$delJitter, &$delCorrelation, &$errMsg)
{
	//Validate delay time

	//Check if delay is a negative integer
	if ($del[$x] < 0) {
		$errMsg='ACTION FAILED: The delay value for ' . $interfaces[$x] . ' cannot be a negative number.';
		$errorDisplayed=TRUE;
	}
	//Check delay against maximum value
	if ($del[$x] > 10000) {
		$errMsg='ACTION FAILED: The delay value for ' . $interfaces[$x] . ' is too high, must be less than 10000.';
		$errorDisplayed=TRUE;
	}

//Validate delay jitter

//Check that it's numeric and between 1 and 10000, if it's 0 or empty then it's ignored.
//If it's lower than 0, higher than 10000 or not numeric then an error is displayed.

	//If not empty
	if (empty($delJitter[$x])==FALSE) {
		//If not empty and numeric
		if  (is_numeric($delJitter[$x])) {
		//Get rid of any numbers after the decimal point if the user included a
		//decimal point in the value (100.99 = 100, 52.2 = 52)
			$delJitter[$x]=intval($delJitter[$x]);
			if ($delJitter[$x] < 0) {
				$errMsg='ACTION FAILED: The delay jitter value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($delJitter[$x] > 10000) {
				$errMsg='ACTION FAILED: The delay jitter value for ' . $interfaces[$x] . ' is too high, must be less than 10000.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The delay jitter value for ' . $interfaces[$x] . ' must be an integer.';
			$errorDisplayed=TRUE;
		}
	}

//Validate delay correlation

//Check that it's numeric and between 1 and 100, if it's 0 or empty then it's ignored.
//If it's lower than 0, higher than 100 or not numeric then an error is displayed.

	if (empty($delCorrelation[$x])==FALSE) {
		if  (is_numeric($delCorrelation[$x])) {
		//Get rid of any numbers after the decimal point if the user included a
		//decimal point in the value (100.99 = 100, 52.2 = 52)
			$delCorrelation[$x]=intval($delCorrelation[$x]);
			if ($delCorrelation[$x] < 0) {
				$errMsg='ACTION FAILED: The delay correlation value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($delCorrelation[$x] > 100) {
				$errMsg='ACTION FAILED: The delay correlation value for ' . $interfaces[$x] . ' cannot be more than 100%.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The delay correlation value for ' . $interfaces[$x] . ' must be an integer';
			$errorDisplayed=TRUE;
		}
	}
}

//********************
//Validate loss values
//********************

function validate_loss($interfaces, $x, &$errorDisplayed, $loss, &$lossCorrelation, &$errMsg)
{
	//Validate loss percentage

	//Check if loss is a negative integer
	if ($loss[$x] < 0) {
		$errMsg='ACTION FAILED: The loss value for ' . $interfaces[$x] . ' cannot be a negative number.';
		$errorDisplayed=TRUE;
	}
	//Check loss against maximum value
	if ($loss[$x] > 100) {
		$errMsg='ACTION FAILED: The loss correlation value for ' . $interfaces[$x] . ' cannot be more than 100%.';
		$errorDisplayed=TRUE;
	}

	//Validate loss correlation

	if (empty($lossCorrelation[$x])==FALSE) {
		if  (is_numeric($lossCorrelation[$x])) {
		//include any numbers after the decimal point if the user included a
		//decimal point in the value (100.99 = 100, 52.2 = 52)
			$lossCorrelation[$x]=floatval($lossCorrelation[$x]);
			if ($lossCorrelation[$x] < 0) {
				$errMsg='ACTION FAILED: The loss correlation value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($lossCorrelation[$x] > 100) {
				$errMsg='ACTION FAILED: The loss correlation value for ' . $interfaces[$x] . ' cannot be more than 100%.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The loss correlation value for ' . $interfaces[$x] . ' must be an integer.';
			$errorDisplayed=TRUE;
		}
	}
}

//***************************
//Validate duplication values
//***************************

function validate_duplication($interfaces, $x, &$errorDisplayed, $dup, &$dupCorrelation, &$errMsg)
{
	//Validate duplication percentage

	//Check if duplication is a negative integer
	if ($dup[$x] < 0) {
		$errMsg='ACTION FAILED: The duplication value for ' . $interfaces[$x] . ' cannot be a negative number.';
		$errorDisplayed=TRUE;
	}
	//Check duplication against maximum value
	if ($dup[$x] > 100) {
		$errMsg='ACTION FAILED: The duplication value for ' . $interfaces[$x] . ' cannot be more than 100%.';
		$errorDisplayed=TRUE;
	}

	//Validate duplication correlation

	if (empty($dupCorrelation[$x])==FALSE) {
		if (is_numeric($dupCorrelation[$x])) {
		//include any numbers after the decimal point if the user included a
		//decimal point in the value (100.99 = 100, 52.2 = 52)
			$dupCorrelation[$x]=floatval($dupCorrelation[$x]);
			if ($dupCorrelation[$x] < 0) {
				$errMsg='ACTION FAILED: The duplication correlation value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($dupCorrelation[$x] > 100) {
				$errMsg='ACTION FAILED: The duplication correlation value for ' . $interfaces[$x] . ' cannot be more than 100%.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The duplication correlation value for ' . $interfaces[$x] . ' must be an integer.';
			$errorDisplayed=TRUE;
		}
	}
}

//***********************
//Validate reorder values
//***********************
function validate_reorder($interfaces, $x, &$errorDisplayed, $del, $reorder, &$reorderCorrelation, &$gap, &$errMsg)
{
	//Validate reorder

	//Check if reorder is a negative integer
	if ($gap[$x] < 0) {
		$errMsg='ACTION FAILED: The reorder value for ' . $interfaces[$x] . ' cannot be a negative number.';
		$errorDisplayed=TRUE;
	}
	//Check reorder against maximum value
	if ($gap[$x] > 100) {
		$errMsg='ACTION FAILED: The reorder value for ' . $interfaces[$x] . ' cannot be more than 100%.';
		$errorDisplayed=TRUE;
	}

	//Validate reorder correlation
	//(optional, cannot be negative or above 100)
	if (empty($reorderCorrelation[$x])==FALSE) {
		if (is_numeric($reorderCorrelation[$x])) {
			//include any numbers after the decimal point if the user included a
			//decimal point in the value (100.99 = 100, 52.2 = 52)
			$reorderCorrelation[$x]=floatval($reorderCorrelation[$x]);
			if ($reorderCorrelation[$x] < 0) {
				$errMsg='ACTION FAILED: The reorder correlation value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($reorderCorrelation[$x] > 100) {
				$errMsg='ACTION FAILED: The reorder correlation value for ' . $interfaces[$x] . ' cannot be more than 100%.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The reorder correlation value for ' . $interfaces[$x] . ' must be an integer.';
			$errorDisplayed=TRUE;
		}
	}

	//Validate gap
	//(optional, cannot be negative or above 1000)
	if (empty($gap[$x])==FALSE) {
		if (is_numeric($gap[$x])) {
			//get rid of any numbers after the decimal point if the user included a
			//decimal point in the value (100.99 = 100, 52.2 = 52)
			$gap[$x]=intval($gap[$x]);
			if ($gap[$x] < 0) {
				$errMsg='ACTION FAILED: The gap value for ' . $interfaces[$x] . ' cannot be a negative number.';
				$errorDisplayed=TRUE;
			}
			if ($gapCorrelation[$x] > 1000) {
				$errMsg='ACTION FAILED: The gap value for ' . $interfaces[$x] . ' is too high. Cannot be more than 1000.';
				$errorDisplayed=TRUE;
			}
		} else {
			$errMsg='ACTION FAILED: The gap value for ' . $interfaces[$x] . ' must be an integer.';
			$errorDisplayed=TRUE;
		}
	}

	//Check for delay
	//(If no delay then reordering doesn't work therefore error)
	if (empty($del[$x])) {
		$errMsg='ACTION FAILED: ' . $interfaces[$x] . ' must be given a delay value if reordering is in use';
		$errorDisplayed=TRUE;
	}
}

//*******************
//Validate corruption
//*******************
function validate_corrupt($interfaces, $x, &$errorDisplayed, $corrupt, &$errMsg)
{
	//Validate corrupt value

	//Check if corrupt is a negative integer
	if ($corrupt[$x] < 0) {
		$errMsg='ACTION FAILED: The corrupt value for ' . $interfaces[$x] . ' cannot be a negative number,';
		$errorDisplayed=TRUE;
	}

	//Check corrupt against maximum value
	if ($corrupt[$x] > 100) {
		$errMsg='ACTION FAILED: The corrupt value for ' . $interfaces[$x] . ' cannot be greater than 100%,';
		$errorDisplayed=TRUE;
	}
}

//*******************
//Validate disconnect
//*******************
function validate_disconnect($interfaces, $x, &$errorDisplayed, $disc, $sym, $src, $srcSub, $dest, $destSub, $port, $combination, &$errMsg)
{
	//Pls check if any of the other parameters are set
	$found = FALSE;

	for ($i=1;$i<=7;++$i) {
		if ($combination[$x][$i]==TRUE) { $found = TRUE; break; }
	}

	//print "x = $x";
	//print "idle = $disc[$x]->idl_type ";
	//print "rnd = $disc[$x]->rnd_type ";
	//print "rcd = $disc[$x]->rcd_type ";
	//Validate disconnect type
	if (($disc[$x]->idl_type == "none") && ($disc[$x]->rnd_type == "none") && ($disc[$x]->rcd_type == "none")) return; 

	if (!($found)) {
		$errMsg='ACTION FAILED: To use disconnect type on ' . $interfaces[$x] . ' atleast one out of Delay, Loss, Corruption, Reordering or Bandwidth should be set to a valid non-zero value';
		$errorDisplayed=TRUE;
		return;
	}

	
	if (($sym[$x] == "No")  && ($disc[$x]->idl_type != "none")) {
		$errMsg='ACTION FAILED: The idle disconnect type for ' . $interfaces[$x] . ' can be set only on a Symmetric Network';
		$errorDisplayed=TRUE;
		return;
	}

	if (($sym[$x] == "No")  && ($disc[$x]->rcd_type != "none")) {
		$errMsg='ACTION FAILED: The random connection disconnect type for ' . $interfaces[$x] . ' can be set only on a Symmetric Network';
		$errorDisplayed=TRUE;
		return;
	}

	//print "Source IP = $src[$x]";
	//print "Source subnet = $srcSub[$x]";
	if ((trim($src[$x])!="any") &&  ($srcSub[$x] != 32)){
		$errMsg='ACTION FAILED: Disconnect for ' . $interfaces[$x] . ' can be set only for a source subnet of 32 for a specific source IP address - Otherwise you can give a source IP address of any';
		$errorDisplayed=TRUE;
		return;
	}

	if ((trim($dest[$x])!="any") &&  ($destSub[$x] != 32)){
		$errMsg='ACTION FAILED: Disconnect for ' . $interfaces[$x] . ' can be set only for a destination subnet of 32 for a specific destination IP address - Otherwise you can give a destination IP address of any';
		$errorDisplayed=TRUE;
		return;
	}

	if (($disc[$x]->idl_type != "none")) { 
		validate_disc_timer($interfaces, $x, $disc[$x]->idl_timer, "Idle Timer", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		validate_disc_timer($interfaces, $x, $disc[$x]->idl_disc_timer, "Idle Disconnect Timer", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
	}

	if (($disc[$x]->rnd_type != "none")) { 
		validate_disc_timer($interfaces, $x, $disc[$x]->rnd_mttf_lo, "Random Disconnect Timer MTTF Low", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		validate_disc_timer($interfaces, $x, $disc[$x]->rnd_mttf_hi, "Random Disconnect Timer MTTF High", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		if ($disc[$x]->rnd_mttf_lo > $disc[$x]->rnd_mttf_hi) {
			$errMsg='ACTION FAILED: Random Disconnect MTTF Low cannot be greater than Random Disconnect MTTF High for ' . $interfaces[$x]; 
			$errorDisplayed=TRUE;
			return;
		}

		validate_disc_timer($interfaces, $x, $disc[$x]->rnd_mttf_lo, "Random Disconnect Timer MTTR Low", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		validate_disc_timer($interfaces, $x, $disc[$x]->rnd_mttf_hi, "Random Disconnect Timer MTTR High", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		if ($disc[$x]->rnd_mttr_lo > $disc[$x]->rnd_mttr_hi) {
			$errMsg='ACTION FAILED: Random Disconnect MTTR Low cannot be greater than Random Disconnect MTTR High for ' . $interfaces[$x]; 
			$errorDisplayed=TRUE;
			return;
		}
		
	}

	if (($disc[$x]->rcd_type != "none")) { 
		validate_disc_timer($interfaces, $x, $disc[$x]->rcd_mttf_lo, "Random Connection Disconnect Timer MTTF Low", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		validate_disc_timer($interfaces, $x, $disc[$x]->rcd_mttf_hi, "Random Connection Disconnect Timer MTTF High", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;

		if ($disc[$x]->rcd_mttf_lo > $disc[$x]->rcd_mttf_hi) {
			$errMsg='ACTION FAILED: Random Connection Disconnect MTTF Low cannot be greater than Random Connection Disconnect MTTF High for ' . $interfaces[$x]; 
			$errorDisplayed=TRUE;
			return;
		}

		validate_disc_timer($interfaces, $x, $disc[$x]->rcd_mttf_lo, "Random Connection Disconnect Timer MTTR Low", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;
		validate_disc_timer($interfaces, $x, $disc[$x]->rcd_mttf_hi, "Random Connection Disconnect Timer MTTR High", $errorDisplayed, $errMsg);
		if ($errorDisplayed == TRUE) return;

		if ($disc[$x]->rcd_mttr_lo > $disc[$x]->rcd_mttr_hi) {
			$errMsg='ACTION FAILED: Random Connection Disconnect MTTR Low cannot be greater than Random Connection Disconnect MTTR High for ' . $interfaces[$x]; 
			$errorDisplayed=TRUE;
			return;
		}
	}
}

//******************
//Validate bandwidth
//******************
function validate_bandwidth($interfaces, $x, &$errorDisplayed, $bandwidth, &$errMsg)
{
	//Validate bandwidth value

	//Check if bandwidth is a negative integer
	if ($bandwidth[$x] < 0) {
		$errMsg='ACTION FAILED: The bandwidth value for ' . $interfaces[$x] . ' cannot be a negative number,';
		$errorDisplayed=TRUE;
	}
}

function validate_disc_timer($interfaces, $x, &$timer, $name, &$errorDisplayed, &$errMsg)
{
	//print "Inside validate timer = $timer end1";

        if (!(is_numeric($timer))) {
                        $errMsg='ACTION FAILED: The '. $name .' for ' . $interfaces[$x] . ' must be an integer greater than zero.';
                        $errorDisplayed=TRUE;
			//print "validate err = $errMsg";
                        return;
        }

        //make sure the value is an integer
        $timer = intval($timer);
	//print "After intval validate timer = $timer end2";

        if ((empty($timer))==FALSE) {
                //Call the function to fully validate the timer value.
                if ($timer <= 0) {
                        $errMsg='ACTION FAILED: The '. $name .' for ' . $interfaces[$x] . ' cannot be less than or equal to zero.';
                        $errorDisplayed=TRUE;
			//print "validate err = $errMsg";
                        return;
                }
        } else {
		$errMsg='ACTION FAILED: The '. $name .' for ' . $interfaces[$x] . ' must be an integer greater than zero.';
                $errorDisplayed=TRUE;
		//print "validate err2 = $errMsg";
                return;
        }
}

?>
