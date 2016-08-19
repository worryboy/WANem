<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*                                                                          */
/*          Please have a look at CopyrightInformation.txt            	    */
/*                                                                          */
/****************************************************************************/
/*                                                                          */

session_start();

include_once("config.inc.php");
include_once("disc.inc.php");
include("currval-advanced.inc.php");
include_once("show.inc.php");
include_once("find_3.0.inc.php");
include_once("command.inc.php");
include_once("validate.inc.php");

//Check if an interface has been selected
if ($_POST[selInt]) {
	$_SESSION[selectedInterface] = $_POST[selInt];
}
if (!$_SESSION[selectedInterface]) {
	$_SESSION[selectedInterface] = "";
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TCS WANem GUI</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
<!--
<link rel="stylesheet" type="text/css" href="/phpnetemguimain.css">
-->
</head>
<body bgcolor="white">
<!--
Keep the following two lines inside the body tag for automatic font re-size:
onLoad="document.body.style.fontSize=document.body.clientWidth/48+'px';"
onResize="document.body.style.fontSize=document.body.clientWidth/48+'px';"
-->

<!--
<style>
table, tr, td {font-size: .85em;}
</style>
-->
<!--
	<div style="align=center; background-color: white; border: thin solid #000000;">
		<table border="0" width="100%" colspan=10>
		  <tr>
			<td width="20%" align="left">
			<img src="TCS-PERC-logo.gif" align="left" style="margin-left:5px;border-style:none" />
			</td>
			<td width="60%" align="center">
			<p align="center"><font color=#488ac7> <font size="6"><b>WANem v1.2<br><font size="3">Advanced Mode</b></font></p>
			</td>
			<td width="20%" align="right">
			<p><a href="help.htm" target="_blank">Help</a></p>
	  		<p><a href="index.php">Main page</a></p>
			</td>
		  </tr>
		</table>
	</div>
-->

	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

<?

//Add selected interface to $interfaces if no interfaces are there already
//unset ($interfaces);
if (count($interfaces==0)) {
	$interfaces[]=$_SESSION[selectedInterface];
}

//If 'Apply settings' button was pressed then call the validation function, the reset_tc
//function, then the make_command function.
if (isset($_POST['btnApply'])) {
	//Allow the validation function to change the textbox value arrays because they will
	//be got from the post variables within this function.  These arrays will then be
	//passed onto the make command function.
	validate_primary_values($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $valid, $combination, $errMsg, $src, $srcSub, $dest, $destSub, $port, 1);
		//echo "disco 0 = ", $disc[0]->idl_timer;
		//echo "disco 1 = ", $disc[1]->foo;
		//print "disco 2 = $disc[2]->foo";
	//Reset all tc commands and make the new tc commands if all validation was successful
	if ($valid==TRUE) {
		//$displayCmd is a variable to hold the command strings that would be executed,
		//if any.  These will be displayed if the user checked the 'Display commands'
		//checkbox.  The commands that are displayed will be stripped of 'sudo /sbin/'.
		$displayCmd="";
		reset_tc($interfaces, $displayCmd);
		$storedCommands="";
		make_command($interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $combination, $displayCmd, $src, $srcSub, $dest, $destSub, $port, $storedCommands, 1);
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
			if (empty($del[$i]) and empty($loss[$i]) and empty($dup[$i]) and empty($reorder[$i]) and empty($corrupt[$i]) and empty($bandwidth[$i]) and empty($disc[$i]->idl_type)) {
				//increment $valuesReset
				++$valuesReset;
			}
		}
		//If $valuesReset=count($interfaces) then all displayed interfaces have all
		//empty/zero values

		if ($valuesReset==count($interfaces)) {
			if (isset($_POST['chkDisplay'])==false) {
?>
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%">
<?
				echo '<b>' . "WANem commands successfully created, all values set to zero" . '</b>';
			}
?>
	</div>
<?
		} else {
			if (isset($_POST['chkDisplay'])==false) {
?>
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%">
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
	<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%">
<?
		echo '<b>' . $errMsg . '</b>';
		//Make sure the command display section is not shown by setting the $displayCmd
		//variable to ""
		$displayCmd="";
		unset($disc);
?>
	</div>
<?
	}
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
<div align="center": style="color: #000000; background-color: #9999ff; border: thin solid #000044; width: 100%">
<?
	echo '<b>' . "All WANem values have been reset" . '</b>';
?>
</div>
<?
}

//If the 'Stop WANtem' button was pressed then set then reset the commands
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
//If the 'Start WANem' button was pressed then run the commands that were stored in the
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

//Stop / start netem button section
if (file_exists($onOffFile)) {
	//Get the first character of the text file
	//Check if the character is 1
	if (substr(file_get_contents($onOffFile),0,1)=="1") {
?>
		<div align=center style="color: #000000; background-color: #488ac7; border: thin solid #000000; width: 989px; margin:0 auto;">
		<table border="0" width="100%" colspan=10>
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
		<div align=center style="color: #000000; background-color: #488ac7; border: thin solid #000000; width: 989px; margin:0 auto;">
		<table border="0" width="100%" colspan=10>
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

//Call the function to process 'tc -s qd', 'tc class show dev (int)' and 'tc filter show dev (int)' to get the number of netem rule sets in use for the selected interface and the values of each rule set.
//Call the get_current_values function with the correct flags set based if the add or the delete rule set button were clicked.

if (isset($_POST['btnDelete'])) {
	$ruleSets=get_current_values($interfaces[0], $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $msFound, $src, $srcSub, $dest, $destSub, $port, 0, 1);
} else {
	if (isset($_POST['btnAdd'])) {
		$ruleSets=get_current_values($interfaces[0], $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $msFound, $src, $srcSub, $dest, $destSub, $port, 1, 0);
	} else {
		$ruleSets=get_current_values($interfaces[0], $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $msFound, $src, $srcSub, $dest, $destSub, $port, 0, 0);
	}
//print "sym1 = $sym[0]";
//print "sym2 = $sym[1]";
}
	//print  "Corrupt equals $corrupt[0]"; 
//Add the correct number of instances of the selected interface to the $interfaces array, always at least 1
unset($interfaces);
$interfaces[]=$_SESSION[selectedInterface];
for ($i=2; $i<=$ruleSets; ++$i) {
	$interfaces[]=$_SESSION[selectedInterface];
}

//Display all interfaces that are currently in the $interfaces array so their netem settings
//can be edited
show_interfaces(0,$interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $displayCmd, $src, $srcSub, $dest, $destSub, $port, 0, $showButton);

//Add the add and delete rule set buttons
?>
	  <p align="center"><input type="submit" value="Add a rule set" name="btnAdd">
<?
if (count($interfaces)>1) {
?>
	  <input type="submit" value="Delete last rule set" name="btnDelete">
<?
}
?>
	  <input type="submit" value="Apply settings" name="btnApply">
	  <input type="submit" value="Reset settings" name="btnReset">
	  <input type="submit" value="Refresh settings" name="btnRefresh"></br>
	  <input type="checkbox" name="chkDisplay" value="OFF">Display commands only, do not execute them
	</form>
	<form action="<?php echo 'status.php'; ?>" method="post" target="_blank">
<?
	//Make a string with all interface names separated by a space to pass into the
	//status page
	find_bridges($bridgeName, $bridgeInts, 0);
	find_interfaces($ints, $bridgeName, $bridgeInts);
	$interfaceList="";
	for ($i=0;$i<count($ints);++$i) {
		$interfaceList=$interfaceList . $ints[$i] . " ";
	}

?>
	  <input type="hidden" name="interfaceList" value="<? echo $interfaceList; ?>">
	  <p align="center"><input type="submit" value="Check current status" name="btnStatus"></p>
	</form>
</body>
</html>
