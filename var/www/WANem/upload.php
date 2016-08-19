<?php
include_once("config.inc.php");
include_once("find_3.0.inc.php");
include_once("command.inc.php");

//Call the function to check if one or more bridges are present on the machine.  If there are then create and display a bridge select box.
	find_bridges($bridgeName, $bridgeInts, 1);

//If a bridge has been selected then just get the interfaces used by the bridge into the $interfaces array.  Otherwise get all valid non-bridge interfaces that are currently in use.
	find_interfaces($interfaces, $bridgeName, $bridgeInts);

	$displayCmd="";
	
	//remove all running WANem commands
	reset_tc($interfaces, $displayCmd);

	//Delete the $onOffFile file if it exists
	if (file_exists($onOffFile)) {
		unlink ($onOffFile);
	}

//upload new netemstate.txt file
//set where you want to store files
	$path= $onOffFile;
	if($ufile != none) {
		if(copy($_FILES['ufile']['tmp_name'], $path)) {
			//if new netemstate.txt file is in running state, then START WANem
			if (substr(file_get_contents($onOffFile),0,1)=="1") {
				//echo "INTERFACE=".$interfaces;
				reset_tc($interfaces, displayCmd);
				//reset_tc("eth0",$displayCmd);
				$storedCommands=substr(file_get_contents ($onOffFile),1);
				exec($storedCommands);

				//Rewrite the $onOffFile file with a "1" in front of the stored commands
				$storedCommands="1" . $storedCommands;
				$fp=fopen($onOffFile,"w+");
				flock($fp, LOCK_EX);
				fwrite($fp, $storedCommands);
				flock($fp, LOCK_UN);
				fclose($fp);
				chmod($onOffFile, 0644);
				
				echo "WANem running";
			}
			echo '<script language="javascript">alert("Restoration Successful")</script>';
		}
		else {	
			echo '<script language="javascript">alert("Restoration Failure !")</script>';
		}
	}
?>
