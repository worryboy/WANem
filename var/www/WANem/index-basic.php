<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*                                                                          */
/*             Please have a look at CopyrightInformation.txt               */
/*                                                                          */
/****************************************************************************/
/*                                                                          */

session_start();

include_once("config.inc.php");
include_once("disc.inc.php");

include("currval.inc.php");
include_once("show.inc.php");
include_once("find_3.0.inc.php");
include_once("command.inc.php");
include_once("validate.inc.php");

//Check for select/unselect bridge button presses
//Store the selected bridge status in memory using a session variable.
if ($_POST[btnSelectBridge]) {
	$_SESSION[bridgeSelected] = $_POST[bridges];
} else {
	if ($_POST[btnUnselectBridge]) {
		$_SESSION[bridgeSelected] = "";
	}
}
if (!$_SESSION[bridgeSelected]) {
	$_SESSION[bridgeSelected] = "";
}
//Set $showButton to true, this is used to say whether the apply settings button is shown on index-basic.php
$showButton=true;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TCS WANem GUI</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
<link rel="stylesheet" type="text/css" href="/phpnetemguimain.css">
</head>
<body bgcolor="white">
<br>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?

//Call the function to check if one or more bridges are present on the machine.  If
//there are then create and display a bridge select box.
find_bridges($bridgeName, $bridgeInts, 1);

//If a bridge has been selected then just get the interfaces used by the bridge into the
//$interfaces array.  Otherwise get all valid non-bridge interfaces that are currently in use.
if ($_SESSION[bridgeSelected]=="") {
	find_interfaces($interfaces, $bridgeName, $bridgeInts);
} else {
	//Loop through bridge name array and look for a match against $_SESSION[bridgeSelected]
	for ($i=0; $i<=count($bridgeName);++$i) {
		if ($_SESSION[bridgeSelected]==$bridgeName[$i]) {
			//Check for a blank space in $bridgeInts[$i]. If there's no blank space then
			//get the interface names.
			if ($bridgeInts[$i]!=" ") {
				$tmpStr="";
				//loop through all characters of $bridgeInts[$i]
				for ($n=0; $n<=strlen($bridgeInts[$i]);++$n) {
					if (substr($bridgeInts[$i], $n, 1)!="#") {
						//Add character to $tmpStr
						$tmpStr=$tmpStr . substr($bridgeInts[$i], $n, 1);
					} else {
						//Set $interfaces[] to $tmpStr
						$interfaces[]=$tmpStr;
						$tmpStr="";
					}
				}
				//Add final interface
				if ($tmpStr!="") {
					$interfaces[]=$tmpStr;
				}
			}
		}
	}
}
for($xctr=0,$zctr=0;$zctr<count($interfaces);$zctr++)
            {
              if(strcmp("lo",$interfaces[$zctr])!=0){
                    $tintfac[$xctr]=$interfaces[$zctr];
                    $xctr++;
               }
            }
 $interfaces=$tintfac;
//If 'Apply settings' button was pressed then call the validation function, the reset_tc
//function, then the make_command function.
if (isset($_POST['btnApply'])) {

	//If an interface has more than one rule set running then it should be excluded from the $interfaces
	//variable when apply settings is clicked

	//First store the unmodified $interfaces array
	$interfacesTemp=$interfaces;
	
	If (!empty($_SESSION[ignoreInts])) {
		for ($i=0; $i<count($interfaces); ++$i) {
			if ($_SESSION[ignoreInts][$i]==1) {
			$interfaces[$i]="";
			}
		}
	}

	//Allow the validation function to change the textbox value arrays because they will
	//be got from the post variables within this function.  These arrays will then be
	//passed onto the make command function.
	validate_primary_values($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $valid, $combination, $errMsg, $src, $srcSub, $dest, $destSub, $port, 0);
	//Reset all tc commands and make the new tc commands if all validation was successful
	if ($valid==TRUE) {
		//$displayCmd is a variable to hold the command strings that would be executed,
		//if any.  These will be displayed if the user checked the 'Display commands'
		//checkbox.  The commands that are displayed will be stripped of 'sudo /sbin/'.
		$displayCmd="";
		reset_tc($interfaces, $displayCmd);
		$storedCommands="";
		make_command($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $combination, $displayCmd, $src, $srcSub, $dest, $destSub, $port, $storedCommands, 0);
		//If commands were run then write them to the file in $onOffFile.  This file is for
		//temporarily storing the commands that were last run so you can stop and start
		//the last set of rules at any time.
		if ($storedCommands!="") {
			$storedCommands="1" . $storedCommands;
			$fp=fopen($onOffFile,"w+");
			flock($fp, LOCK_EX);
			fwrite($fp, $storedCommands);
			flock($fp, LOCK_UN);
			fclose($fp);
			chmod($onOffFile, 0644);
		}
		//Check through all values to check if every one of them is empty or zero
		$valuesReset=0;
		for ($i=0; $i<count($interfaces);++$i) {
			//If all empty/zero values for this interface then $valuesReset=true
			if (empty($del[$i]) and empty($loss[$i]) and empty($dup[$i]) and empty($reorder[$i]) and empty($bandwidth[$i])) {
				//increment $valuesReset
				++$valuesReset;
			}
		}
		//If $valuesReset=count($interfaces) then all displayed interfaces have all
		//empty/zero values

		if ($valuesReset==count($interfaces)) {
			if (isset($_POST['chkDisplay'])==false) {
?>
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%; height: 20">
<?
				echo '<b>' . "WANem commands successfully created, all values set to zero" . '</b>';
			}
?>
	</div>
<?
		} else {
			if (isset($_POST['chkDisplay'])==false) {
?>
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%; height: 20">
<?
				echo '<b>' . "WANem commands successfully created" . '</b>';
			}
?>
	</div>
<?
		}
	} else {
		//Display error
?>
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%; height: 20">
<?
		echo '<b>' . $errMsg . '</b>';
		//Make sure the command display section is not shown by setting the $displayCmd
		//variable to ""
		$displayCmd="";
?>
	</div>
<?
	}
	//Restore the original $interfaces array
	$interfaces=$interfacesTemp;
}

//If the 'Reset settings' button was pressed then call the reset_tc function for all
//displayed interfaces.
if (isset($_POST['btnReset'])) {
	reset_tc($interfaces, $displayCmd);
	//Delete the $onOffFile file if it exists
	if (file_exists($onOffFile)) {
		unlink ($onOffFile);
	}
?>
<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%; height: 20">
<?
	echo '<b>' . "All WANem values have been reset" . '</b>';
?>
</div>
<?
}

//If the 'Stop netem' button was pressed then set then reset the commands
if (isset($_POST['btnStopNetem'])) {
	reset_tc($interfaces, $displayCmd);

	//Rewrite the $onOffFile file with a "0" in front of the stored commands
	$storedCommands="0" . substr(file_get_contents ($onOffFile),1);
	$fp=fopen($onOffFile,"w+");
	flock($fp, LOCK_EX);
	fwrite($fp, $storedCommands);
	flock($fp, LOCK_UN);
	fclose($fp);
	chmod($onOffFile, 0644);
}
//If the 'Start netem' button was pressed then run the commands that were stored in the
//$onOffFile file and set 'netemStoppped' to false
if (isset($_POST['btnStartNetem'])) {
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
}

//Stop / start WANem button section
if (file_exists($onOffFile)) {
	//Get the first character of the text file
	//Check if the character is 1
	if (substr(file_get_contents($onOffFile),0,1)=="1") {
?>
		<div align=center : style="color: #000000; background-color: #0070C0; border: thin solid #000000; width: 966px; margin:0 auto;">
		<table border="0" width="100%">
		  <tr>
			<td width="100%">
			  <p align="center">WANem is running<input type="submit" value="Stop WANem" name="btnStopNetem">
			</td>
		  </tr>
		</table>
		</div>
<?
	} else {
?>
		<div align=center : style="color: #000000; background-color: #0070C0; border: thin solid #000000; width: 966px; margin:0 auto;">
		<table border="0" width="100%">
		  <tr>
			<td width="100%">
			  <p align="center">WANem is not running<input type="submit" value="Start WANem" name="btnStartNetem">
			</td>
		  </tr>
		</table>
		</div>
<?
	}
}

//Get current values into arrays according to how many interfaces there are being shown.
//e.g. $del[1 - 2] lossCorrelation[1 - 2].
get_current_values($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt,  $sym, $disc, $limit, $msFound, $src, $srcSub, $dest, $destSub, $port, $advanced);
//Turn $advanced into a session variable to it can be read further back in the program on this page.
$_SESSION[ignoreInts]=$advanced;

//Display all interfaces that are currently in the $interfaces array so their netem settings
//can be edited
show_interfaces(1,$interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $displayCmd, $src, $srcSub, $dest, $destSub, $port, $advanced, $showButton);


?>
<!--	  <p align="left"><a href="index.php">&lt Return to index page</a> -->
<?
	  if ($showButton==true) {
?>
	  <p align="center"><input type="submit" value="Apply settings" name="btnApply">
	  <input type="submit" value="Reset settings" name="btnReset">
	  <input type="submit" value="Refresh settings" name="btnRefresh"></br>
<?
	  } else {
?>
	  <p align="center"><input type="submit" value="Reset settings" name="btnReset">
	  <input type="submit" value="Refresh settings" name="btnRefresh"></br>
<?
   }
?>
	  <input type="checkbox" name="chkDisplay" value="OFF">Display commands only, do not execute them
	</form>
	<form action="<?php echo 'status.php'; ?>" method="post" target="_blank">
<?
	//Make a string with all interface names separated by a space to pass into the
	//status page
	$interfaceList="";
	for ($i=0;$i<count($interfaces);++$i) {
		$interfaceList=$interfaceList . $interfaces[$i] . " ";
	}
?>
	  <input type="hidden" name="interfaceList" value="<? echo $interfaceList; ?>">
	  <p align="center"><input type="submit" value="Check current status" name="btnStatus"></p>
	</form>
<!--
//<?
	//echo count($interfaces);	
//	if (count($interfaces)<=2) {
//?>				
	<div align=center>
	<table><tr><td align=left>
	<p><font color="red">
	The WANem machine has detected only 1 ethernet interface card.<br>
	There will be restricions on the maximum bandwidth that can be emulated<br>
	depending upon <br>
	-The Network Interface bandwidth. <br>
	-Application/Protocol traffic being tested with WANem.
	</font></p>
	</td></tr></table>
	</div>
//<?
//	}				
//?>
-->
</body>
</html>
