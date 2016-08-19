<?
// ---------------------------------------------------------------
// PHPnetemGUI
//
// Copyright 2005 British Telecommunications plc
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc.
// 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// ---------------------------------------------------------------

include_once("find_3.0.inc.php");

//Call the function to check if one or more bridges are present on the machine.
find_bridges($bridgeName, $bridgeInts, 0);

//Call the function to find all non-bridge interfaces on the machine.
find_interfaces($interfaces, $bridgeName, $bridgeInts);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>TCS Wanem GUI</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
<!--
<link rel="stylesheet" type="text/css" href="/phpnetemguimain.css">
-->
</head>
<body bgcolor="white">
<!--
	<div align="center" style="color: #000000; background-color: #bbffff; border: thin solid #000044; height: 50" :>
		<table border="0" width="100%" height="100%">
		  <tr>

			<td width="33%" align="left">
			<p><a href="help.htm" target="_blank">Help</a></p>

			</td>
			<td width="34%" align="center">
			<p align="center"><font size="4" color=#083294737><b>TCS Wanem GUI</b></font></p>
			</td>
			<td width="33%" align="right"><font size="2">TCS Wanem GUI <a href="http://www.smyles.plus.com/phpnetemgui/" target="_blank">homepage</a></font></td>
		  </tr>
		</table>
	</div>
	<form>
	<div style="color: #000000; background-color: #aaccff; border: thin solid #000000; height: 100; align: center">
		<table border="0" width="100%" height="100%">
		<tr>
			<td width="33%" align="center">
			<p><a href="wanc.html" target="_blank"><b>Enter TCS WANalyser</b></a></p>

			</td>
		</tr>
		</table>
	</div>
	</form>
	<form action="index-basic.php" method="post">
	<div style="color: #000000; background-color: #aaccff; border: thin solid #000000; height: 100; align: center">
		<table border="1" width="100%" height="100%">
		  <tr>
			<td width="100%" colspan="10" height="100%" align="center">
				<p><font size="4">Enter basic mode </font><br><font size="2">(You
				can edit all interfaces at once but only enter one set of rules
				per interface)&nbsp;</font></p>
				<input type="submit" value="Start" name="btnBasic">
			</td>
		</table>
	  </div>
	</form>
	<br><br>
	<form action="index-advanced.php" method="post" target="MAIN">-->
	<div style="color: #000000; background-color: white; border: #000000; height: 150; align: center">
	<form action="index-advanced.php" method="post">
		<table border="0" width="100%" height="100%">
		  <tr>
			<td width="100%" colspan="10" height="100%" align="center">
			  <p><font size="4">Enter advanced mode</font><br><font size="4"> </font><font size="2">(You
			  can enter more than one set of rules per interface but you can
			  only edit one interface at a time)</font></p><br>
<?
				//echo count($interfaces);	
				if (count($interfaces)<=2) {
?>				
				<div>
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
<?
				}				
				//Create the advanced mode interface select box.
				$selectHTML="\t\t\t  " . '<p><select size="1" name="selInt">' . "\n\t\t\t\t" . '<option selected>' . $interfaces[0] . '</option>' . "\n";
				//Check if there are more interfaces to add
				if (count($interfaces)>0) {
					for ($i=1; $i<count($interfaces);++$i) {
						$selectHTML=$selectHTML . "\t\t\t\t" . '<option>' . $interfaces[$i] . '</option>' . "\n";
					}
				}
				$selectHTML=$selectHTML . "\t\t\t  " . '</select></p>' . "\n";
				echo $selectHTML;
?>
			  <p align="center"><b><input type="submit" value="Start" name="btnAdvanced"></b> </p>
			</td>
		  </tr>
		</table>
	</form>
	</div>
</body>
</html>
