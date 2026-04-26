<?php
/********************************************************************/
/*								    */ 	
/* COPYRIGHT:							    */
/* Please have a look at CopyrightInformation.txt		    */
/*								    */
/********************************************************************/

//Main function which creates and runs the netem rules based on the user input
//****************************************************************************
include_once "disc.inc.php";

function make_command($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, &$corrupt, &$sym, &$disc, $limit, $combination, &$displayCmd, $src, $srcSub, $dest, $destSub, $port, &$storedCommands, $advancedMode)
{

	//Make an array containing all possible handle strings
	$handleid  =  array(1 => 'root', 1, 10, 20, 30, 40, 50, 60, 70);
	//Reset storedCommands variable
	$storedCommands="";
	$disconnect_found = FALSE;

	//If in advanced mode then set a separate handle counter
	if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
		$ha=10; //$ha = 'handle advanced'
		$pa=1; //$pa = 'parent advanced'
		$prioLineAdded=false; //This flag is to make sure that the prio command is only added once
	}
	//Loop through all interfaces in the $interfaces[] array
	for ($x=0; $x<count($interfaces); $x++) {

		//If the interface element is empty then that means the interface that was there is to be ignored,
		//therefore skip the following code and move to the next interface.
		if (!empty($interfaces[$x])) {
			$interface=$interfaces[$x];
	
			//Point $h to first handle
			$h=1;
			//print "ha = $ha";
			//Reset command
			$command="";
	
			//Check for specified ip address matching
			//If an ip address for the interface is anything other than "any" OR $advancedMode
			//then add a prio command first.
			global $tc_CMD;
			if ($prioLineAdded==false) {
				if ($src[0]!="any" | $dest[0]!="any") {
					++$h;
					$command=$tc_CMD.' qdisc add dev '. $interface . ' root handle 1: prio bands 10';
					//Add the command to $displayCmd if necessary
					if (isset($_POST['chkDisplay'])) {
						//Strip 'sudo' and '/path/' from the start of the command
						//find /tc in $command then set $displayCmd to substr($command, [/ in /tc])
						$n=strpos($command, "/tc");
						$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
					} else {
						//Add the command to the storedCommands variable
						$storedCommands=$storedCommands . $command . "\n";
						//Run the command
						exec($command);
					}
				}
			}
	
			//Set $prioLineAdded to true if in advanced mode
			if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
				$prioLineAdded=true;
			}
	
			//**Check all elements of the $combination array**
			if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
				//Set correct handle and parent, the parent minor number will be $x+1
				$netemHandle=$ha;
				$netemParent=$pa;
				//Set $pa to handle
				$pa=$ha;
				//increment $ha
				++$ha;
				$tree = " parent $netemParent:" . ($x+1) . " handle $netemHandle: ";
			} else {
				$netemHandle=$handleid[$h+1];
				$netemParent=$handleid[$h];
				//increment $h
				++$h;
				if ($netemParent != 'root') {
					$tree = " parent $netemParent:1 handle $netemHandle: ";
				} else {
				$tree = " root handle $netemHandle: ";
				}
			}

			 $command=$tc_CMD.' qdisc add dev '. $interface .  $tree .  'netem ';
	
			//Check for delay
			if ($combination[$x][1]==TRUE) {
	
				//Create delay command (including reordering if necessary)
				if ($combination[$x][4]==TRUE) {
					delay_command($x, $command, $del, $delJitter, $delCorrelation, $reorder, $reorderCorrelation, $gap, $delDistribution );
				} else {
					delay_command($x, $command, $del, $delJitter, $delCorrelation,0, 0, 0, $delDistribution);
				}
			}
			//Check for loss
			if ($combination[$x][2]==TRUE) {

				//Create loss command
				loss_command($x, $command, $loss, $lossCorrelation);
			}
	
			//Check for duplication
			if ($combination[$x][3]==TRUE) {
				//Create duplication command
				duplication_command ($x, $command, $dup, $dupCorrelation);
			}
	
			//Check for duplication
			if ($combination[$x][5]==TRUE) {
				//Create corrupt command
				//print "corrupt Handle = $corruptHandle";
				corrupt_command ($x, $command, $corrupt);
			}

			//Add packet limit if necessary
			if (($limit[$x]>0) and ($limit[$x]!=1000)) {
				$command = $command . ' limit ' . $limit[$x];
			}

			//Add the command to $displayCmd if necessary
			if (isset($_POST['chkDisplay'])) {
				//Strip 'sudo' and '/path/' from the start of the command
				$n=strpos($command, "/tc");
				$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
			} else {
				//Add the command to the storedCommands variable
				$storedCommands=$storedCommands . $command . "\n";
				//Run the command
				exec($command);
			}

	
			//Check for disconnect
			//print "Before disc combination check";
			if ($combination[$x][6]==TRUE) {
				//Create bandwidth command
				//print "after disc combination check";
				$disconnect_found = TRUE;
				disconnect_command ($x, $interface, $command, $disc, $src, $dest, $port, $sym, $displayCmd, $storedCommands, $advancedMode);
			}

			//Check for bandwidth
			if ($combination[$x][7]==TRUE) {
				//Use next handle
				if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
					$bandwidthHandle=$ha;
					$bandwidthParent=$pa;
					//Set $pa to handle
					$pa=$ha;
					//increment $ha
					++$ha;
				} else {
					$bandwidthHandle=$handleid[$h+1];
					$bandwidthParent=$handleid[$h];
				}
				//Create bandwidth command
				bandwidth_command ($x, $interface, $handleid, $h, $bandwidthParent, $bandwidthHandle, $command, $bandwidth, $src, $dest, $displayCmd, $storedCommands, $advancedMode);
			}
	
			if (!$advancedMode) {
				//Check for ip address matching
				if ($src[$x]!="any" | $dest[$x]!="any") {
					//Create ip address matching command
					ip_command($x, $interface, $h, $src, $srcSub, $dest, $destSub, $port, $sym, $displayCmd, $storedCommands, $advancedMode);
				}
			}
			//print "hab = $ha";
			//Set $ha to the next multiple of 10
			While ($ha % 10 != 0):
				++$ha;
			endwhile;
			//print "hae = $ha";
	
			//Reset $pa to 1
			$pa=1;
		}
	}

	if (($advancedMode) && ($disconnect_found))
		restart_disconnect($displayCmd, $storedCommands);

	//Do the advanced mode IP matching commands
	if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
		for ($x=0; $x<count($interfaces); $x++) {
			//Create ip address matching command
			ip_command($x, $interface, $h, $src, $srcSub, $dest, $destSub, $port, $sym, $displayCmd, $storedCommands, $advancedMode);
		}
	}
	//Add the final qdisc and filter rule to direct all non-matching traffic to a pfifo queue
	//If in basic mode then loop through all interfaces, else just loop through the single interface in
	//advanced mode
	if ($advancedMode) {
		$loop=1;
	} else {
		$loop=count($interfaces);
	}
	for ($x=0; $x<$loop; $x++) {
		if ($src[$x]!="any" | $dest[$x]!="any" ) {
			$i=1;
			while ($i<=2):
				switch ($i) {
					case 1:
					if ($advancedMode) {
						$tree = " parent $pa:" . (count($interfaces)+1) . " handle $ha: ";
					} else {
						$tree = $tree = " parent $pa:2 handle 20: ";
					}
					$command=$tc_CMD.' qdisc add dev '. $interfaces[$x] . $tree . ' pfifo';
					break;

					case 2:
					if ($advancedMode) {
						$command=$tc_CMD . ' filter add dev ' . $interfaces[$x] . ' protocol ip parent 1:0 prio ' . (count($interfaces)+1) . ' u32 match ip src 0.0.0.0/0 match ip dst 0.0.0.0/0 flowid ' . (count($interfaces)+1) * 10 . ':' . (count($interfaces)+1);
					} else {
						$command=$tc_CMD . ' filter add dev ' . $interfaces[$x] . ' protocol ip parent 1:0 prio 2 u32 match ip src 0.0.0.0/0 match ip dst 0.0.0.0/0 flowid 20:2' ;
					}
					break;
				}
				//Add the command to $displayCmd if necessary
				if (isset($_POST['chkDisplay'])) {
					//Strip 'sudo' and '/path/' from the start of the command
					$n=strpos($command, "/tc");
					$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
				} else {
					//Add the command to the storedCommands variable
					$storedCommands=$storedCommands . $command . "\n";
					//Run the command
					exec($command);
				}
				++$i;
			endwhile;
		}
	}
}

//******************************************
//Function to create the netem delay command
//******************************************


function delay_command($x, &$command, $del, $delJitter, $delCorrelation, $reorder, $reorderCorrelation, $gap, $delDistribution)
{
	if (empty($delCorrelation[$x])==FALSE and empty($delJitter[$x])==FALSE ) {
		//First 3 actions
		$command= $command . ' delay '. $del[$x] . 'ms ' . $delJitter[$x] . 'ms ' . $delCorrelation[$x] . '%';
	} elseif (empty($delCorrelation[$x]) and empty($delJitter[$x])==FALSE ) {
		//First 2 actions
		$command=$command .  ' delay '. $del[$x] . 'ms ' . $delJitter[$x] . 'ms';
	} elseif (empty($delCorrelation[$x]) and empty($delJitter[$x]) ) {
		//just delay time
		$command=$command . ' delay '. $del[$x] . 'ms';
	} elseif (empty($delCorrelation[$x])==FALSE and empty($delJitter[$x]) ) {
		//Delay time and correlation but not jitter (just ignores correlation value)
		$command=$command . ' delay '. $del[$x] . 'ms';
	}

	//Add reorder commands if necessary
	if ($reorder[$x]>0) {
		$command=$command . ' reorder ' . $reorder[$x] . '%';
		if ($reorderCorrelation[$x]>0) {
			$command=$command . ' ' . $reorderCorrelation[$x] . '%';
		}
		if ($gap[$x]>0) {
			$command=$command . ' gap ' . $gap[$x];
		}
	}
	
	//Add distribution if necessary
	if ($delDistribution[$x]!="-N/A-" & $delJitter[$x]>0) {
		$command=$command . ' distribution ' . strtolower($delDistribution[$x]);
	}

}

//*****************************************
//Function to create the netem loss command
//*****************************************
function loss_command($x, &$command, $loss, $lossCorrelation)
{
	if (empty($lossCorrelation[$x])==FALSE) {
		$command= $command .  ' loss '. $loss[$x] . '% ' . $lossCorrelation[$x] . '%';
	//Just loss percentage
	} elseif (empty($lossCorrelation[$x]))  {
		$command=$command .  ' loss '. $loss[$x] . '%';
	}
}

//************************************************
//Function to create the netem duplication command
//************************************************
function duplication_command ($x, &$command, $dup, $dupCorrelation)
{
	if (empty($dupCorrelation[$x])==FALSE) {
		$command=$command .  ' duplicate '. $dup[$x] . '% ' . $dupCorrelation[$x] . '%';
	//Just loss percentage
	} elseif (empty($dupCorrelation[$x]))  {
		$command=$command .  ' duplicate '. $dup[$x] . '%';
	}
}

//************************************************
//Function to create the netem corrupt command
//************************************************
function corrupt_command ($x, &$command, $corrupt)
{
	$command=$command .  ' corrupt '. $corrupt[$x] . '%';
}

//************************************************
//Function to create the netem corrupt command
//************************************************
function disconnect_command ($x, $interface, &$command, $disc, $src, $dest, $port, $sym, &$displayCmd, &$storedCommands, $advancedMode)
{
	global $disconnect_DIR;

	if (!($advancedMode))  return;

	if (trim($src[$x]) == "any") $source = "anywhere"; else $source = $src[$x];
	if (trim($dest[$x]) == "any") $destination = "anywhere"; else $destination = $dest[$x];
	if (trim($port[$x]) == "any") $appport = 0; else $appport = $port[$x];

	
	if ($disc[$x]->idl_type != "none") {
		$command='sudo echo "IDL '. $disc[$x]->idl_timer .' '. $disc[$x]->idl_disc_timer .' '.$source.' '.$destination.' '.$appport.' '. $disc[$x]->idl_type .' '. $interface .' " >> '. $disconnect_DIR .'/input.dsc';
		$s_command = $command;

		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay'])) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "echo");
			$displayCmd=$displayCmd . substr($command, $n) . "</br>";
		} else {
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		}
	}

	if ($disc[$x]->rnd_type != "none") {
		if ($sym[$x] == "Yes")
			$bi = "B";
		else
			$bi = "";
		$command='sudo echo "RND '. $disc[$x]->rnd_mttf_lo.':'. $disc[$x]->rnd_mttf_hi .' '.  $disc[$x]->rnd_mttr_lo.':'. $disc[$x]->rnd_mttr_hi.' '.$source.' '.$destination.' '.$appport.' '. $disc[$x]->rnd_type .' '. $interface .' '.$bi.' " >> '. $disconnect_DIR .'/input.dsc';
		$s_command = $s_command."\n".$command;

		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay'])) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "echo");
			$displayCmd=$displayCmd . substr($command, $n) . "</br>";
		} else {
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		}
	}

	if ($disc[$x]->rcd_type != "none") {
		$command='sudo echo "RDCONN '. $disc[$x]->rcd_mttf_lo.':'. $disc[$x]->rcd_mttf_hi .' '.  $disc[$x]->rcd_mttr_lo.':'. $disc[$x]->rcd_mttr_hi.' '.$source.' '.$destination.' '.$appport.' '. $disc[$x]->rcd_type .' '. $interface .' " >> '. $disconnect_DIR .'/input.dsc';
		$s_command = $s_command."\n".$command;

		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay'])) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "echo");
			$displayCmd=$displayCmd . substr($command, $n) . "</br>";
		} else {
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		}
	}

	$command = $s_command;
}

//****************************************
//Function to create the bandwidth command
//****************************************
function bandwidth_command ($x, $interface, $handleid, $h, $bandwidthParent, $bandwidthHandle, &$command, $bandwidth, $src, $dest, &$displayCmd, &$storedCommands, $advancedMode) {

	global $tc_CMD;
	
	$BW=$bandwidth[$x];
	
	$r2q = 10;

	if ($BW < 120) $r2q = 1;

	//Create the 3 bandwidth commands
	for ($i=1; $i<=2; ++$i) {
		if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
			//Set correct handle and parent, the parent minor number will be $x+1
			$tree = " parent $bandwidthParent:" . ($x+1) . " handle $bandwidthHandle: ";
		} else {
			if ($bandwidthParent != 'root') {
				$tree = " parent $bandwidthParent:1 handle $bandwidthHandle: ";
			} else {
				$tree = " root handle $bandwidthHandle: ";
			}
		}
		switch ($i) {
			case 1:
			//Bandwidth 1: Add htb qdisc
			$command=$tc_CMD.' qdisc add dev '. $interface .
						$tree .
						'htb default 1'. ' r2q '. $r2q;
			break;
			case 2:
			//Bandwidth 2: Add bandwidth vlaue
			$command=$tc_CMD.' class add dev '. $interface .
						' parent ' . $bandwidthHandle . ': classid 0:1' .
						' htb rate '.  $BW . 'kbit ceil ' . $BW .
						'kbit';
			break;
		}
		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay'])) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "/tc");
		$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		} else {
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		}
	}

}

//**************************************************
//Function to create the ip address matching command
//**************************************************
function ip_command($x, $interface, $h, $src, $srcSub, $dest, $destSub, $port, $sym, &$displayCmd, &$storedCommands, $advancedMode)
{
	global $tc_CMD;

	//print "Sym = $sym[$x]";
	//Create the beginning of the command
	if ($advancedMode) {
		$command=$tc_CMD. ' filter add dev ' . $interface . ' protocol ip parent 1:0 prio ' . ($x+1) . ' u32 ';
		$symcommand=$tc_CMD. ' filter add dev ' . $interface . ' protocol ip parent 1:0 prio ' . ($x+1) . ' u32 ';
	} else {
		$command=$tc_CMD. ' filter add dev ' . $interface . ' protocol ip parent 1:0 prio 1 u32 ';
	}
	//Check if source address has an ip address specified
	if ($src[$x]!="any") {
		$command=$command . ' match ip src ' . $src[$x] . '/' . $srcSub[$x];
		$symcommand=$symcommand . ' match ip dst ' . $src[$x] . '/' . $srcSub[$x];
	}
	//Check if destination address has an ip address specified
	if ($dest[$x]!="any") {
		$command=$command . ' match ip dst ' . $dest[$x] . '/' . $destSub[$x];
		$symcommand=$symcommand . ' match ip src ' . $dest[$x] . '/' . $destSub[$x];
	}
	//Check if destination address has an ip address specified
	if ($port[$x]!="any") {
		$command=$command . ' match ip dport ' . $port[$x] . ' 0xffff';
		$symcommand=$symcommand . ' match ip sport ' . $port[$x] . ' 0xffff';
	}
	//Add the flowid part to the command
	if ($advancedMode & ($src[0]!="any" | $dest[0]!="any")) {
		$command=$command . ' flowid ' . ($x+1) * 10 . ':' . ($x+1);
		$symcommand=$symcommand . ' flowid ' . ($x+1) * 10 . ':' . ($x+1);
	} else {
		$command=$command . ' flowid 10:1';
	}
	//Add the command to $displayCmd if necessary
	if (isset($_POST['chkDisplay'])) {
		if($sym[$x] == "Yes") {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($symcommand, "/tc");
			$displayCmd=$displayCmd . substr($symcommand, $n+1) . "</br>";
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "/tc");
			$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		} else {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "/tc");
			$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		}

	} else {
		if($sym[$x] == "Yes") {
			//Add the symcommand to the storedCommands variable
			$storedCommands=$storedCommands . $symcommand . "\n";
			//Run the symcommand
			exec($symcommand);
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		} else {
			//Add the command to the storedCommands variable
			$storedCommands=$storedCommands . $command . "\n";
			//Run the command
			exec($command);
		}
	}
}

//*********************************************************************
//Function to reset the netem values for all interfaces being displayed
//*********************************************************************
function reset_tc($interfaces, &$displayCmd)
{
	global $tc_CMD, $disconnect_DIR;
	//Remove all currently running netem commands from the all interfaces.
	for ($i=0; $i<count($interfaces); ++$i) {
		//Build the tc command
		if ($interfaces[$i] == $interfaces[$i - 1]) continue;

		$command=$tc_CMD.' qdisc del dev '. $interfaces[$i] .' root';
		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay']) & isset($_POST['btnReset'])==false) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "/tc");
			$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		} else {
			exec($command);
		}
		//Build the disconnect command
		$command='sudo '. $disconnect_DIR .'/reset_disc.sh '. $disconnect_DIR .' '. $interfaces[$i] . ' > /dev/null &';
		//Add the command to $displayCmd if necessary
		if (isset($_POST['chkDisplay']) & isset($_POST['btnReset'])==false) {
			//Strip 'sudo' and '/path/' from the start of the command
			$n=strpos($command, "/reset_disc.sh");
			$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		} else {
			shell_exec($command);
		}
	}

	// Restart is going to affect all interfaces
//	$command='sudo '. $disconnect_DIR .'/kill_disc.sh '. $disconnect_DIR;
//	//	$move_command='mv -f '. $disconnect_DIR .'/input.dsc '. $disconnect_DIR .'/input.dsc.bkp';
//	if (isset($_POST['chkDisplay']) & isset($_POST['btnReset'])==false) {
//		//Strip 'sudo' and '/path/' from the start of the command
//		$n=strpos($command, "/kill_disc.sh");
//		$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
//	//	$displayCmd=$displayCmd . $move_command . "</br>";
//	} else {
//		exec($command);
//	//	exec($move_command);
//		print $command;
//	}
}

//*********************************************************************
//Function to start/restart the disconnection management process 
//*********************************************************************
function restart_disconnect(&$displayCmd, &$storedCommands)
{
	global $disconnect_DIR;
	
	//print "Inside disonnect restart";

	// Restart is going to affect all interfaces
	$command='sudo '. $disconnect_DIR . '/disconnect.sh ' .$disconnect_DIR. ' > /dev/null &';
	if (isset($_POST['chkDisplay']) & isset($_POST['btnReset'])==false) {
		//Strip 'sudo' and '/path/' from the start of the command
		$n=strpos($command, "/disconnect.sh");
		$displayCmd=$displayCmd . substr($command, $n+1) . "</br>";
		#print "Display disconnect = $displayCmd";
	} else {
		// $tmp = exec($command);
		$storedCommands=$storedCommands . $command . "\n";
		exec($command);
		//print $command;
		//print "disco restart command = $tmp";
	}
}
?>
