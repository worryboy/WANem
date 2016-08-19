<?
/****************************************************************************/
/*                              COPYRIGHT				    */
/****************************************************************************/
/*                                                                          */
/*           Please have a look at CopyrightInformation.txt                 */
/*                                                                          */
/****************************************************************************/

//**************************************************************************************
//Function to look for bridges on the machine and create a bridge select box if any were
//found.
//**************************************************************************************
function find_bridges(&$bridgeName, &$bridgeInts, $showBridges)
{
	//Get all directories within the /sys/class/net/ directory into the $dirs array
	if ($handle = opendir('/sys/class/net/')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$dirs[]=$file;
			}
		}
		closedir($handle);
	}
	//Loop through all the found directories, prepending /sys/class/net/ to each
	//See if the brif directory is in the currently looped directory with the file_exists()
	//function.
	for ($i=0;$i<count($dirs);++$i) {
		if (file_exists("/sys/class/net/" . $dirs[$i] . "/brif")) {
			$bridgeName[]=$dirs[$i];
		}
	}
	//Loop through found bridges if any exist and get the interfaces used in each
	if (count($bridgeName)>0) {
		for ($i=0;$i<count($bridgeName);++$i) {
			$bridgeInts[$i]="";
			if ($handle = opendir('/sys/class/net/' . $bridgeName[$i] . '/brif/')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						$bridgeInts[$i]=$bridgeInts[$i] . $file . "#";
					}
				}
				closedir($handle);
				//Check that at least one interface was found, if not then just make
				//$bridgeInts[$i] a blank space.
				if ($bridgeInts[$i]!="") {
					$bridgeInts[$i] = rtrim($bridgeInts[$i], "#");
				} else {
					$bridgeInts[$i]=" ";
				}
			}
		}
	}
	//If a bridge has been selected by the user then show the name of the bridge
	//and an 'Unselect bridge' button.
	if ($_SESSION[bridgeSelected] != "" & $showBridges==1) {
?>
		<div style="color: #000000; background-color: #0070C0; border: thin solid #000000; width: 966px; margin:0 auto;">
		<table border="0" width="100%" align="center">
		  <tr>
			<td width="100%">
			  <p align="center"><b>Selected bridge: <? echo $_SESSION[bridgeSelected]; ?></b>
			  <input type="submit" value="Unselect bridge" name="btnUnselectBridge">
			</td>
		  </tr>
		</table>
		</div>
<?
	} else {

		//****If bridge(s) exist then generate html for a bridge selectbox****
		//Add the bridges in reverse order so they display in creation order
		//Check that at least one bridge was found
		if ((empty($bridgeName[0]))==FALSE & $showBridges==1) {
?>
		<div style="color: #000000; background-color: #0070C0; border: thin solid #000000; width: 966px; margin:0 auto;">
		<table border="0" width="100%" align="center">
		  <tr>
<?
			//Create HTML for a select box within a table cell and add the last bridge to it
			$selectHTML="\t\t\t" . '<td width="100%">'  . "\n\t\t\t  " . '<p align="center"><b>Bridges</b>' . "\n\t\t\t  " . '<select size="1" name="bridges">' . "\n\t\t\t\t" . '<option selected>' . $bridgeName[(count($bridgeName)-1)] . '</option>' . "\n";

			//Loop through all remaining bridges in the array from 2nd-from-last to first
			if (count($bridgeName)>1) {
				$i=(count($bridgeName)-1);
				while ($i >= 1):
					//If a bridge name exists then add it to the select box.
					if ((empty($bridgeName[($i-1)]))==FALSE) {
						$selectHTML=$selectHTML . "\t\t\t\t" . '<option>' . $bridgeName[$i-1] . '</option>' . "\n";
					}
					//decrement $i
					$i = --$i;
				endwhile;
			}
			//Add a submit button
			$selectHTML=$selectHTML . "\t\t\t  " . '</select>' . "\n\t\t\t  " . '<input type="submit" value="Select bridge" name="btnSelectBridge">'  . "\n\t\t\t" . '</td>' . "\n\t\t  " .  '</tr>' . "\n\t\t" . '</table>' . "\n\t\t" . '</div>' . "\n";
	
			//Display the select box and the button
			echo $selectHTML;
		}
	}
}

//**************************************************************************************
//Function to look for interfaces on the machine and create a select box with all of
//the interfaces that were found.
//The function also checks if the 'select bridge' button was pressed and selects the
//correct interfaces for the selected bridge if the button was pressed.
//**************************************************************************************
function find_interfaces(&$interfaces, $bridgeName, $bridgeInts) {

	//Loop through all directories within the /sys/class/net/ directory
	if ($handle = opendir('/sys/class/net/')) {
		while (false !== ($file = readdir($handle))) {
			$validInt=TRUE;
			if ($file != "." && $file != "..") {
				//Check if $file is a bridge by checking for a bridge/ directory
				if (file_exists("/sys/class/net/" . $file . "/bridge")) {
					$validInt=FALSE;
				}
				//Check if the interface is up by checking wether the last bit of the value
				//in the flags file for the interface is 1 or 0.
				$handle2 = fopen("/sys/class/net/" . $file . "/flags", "r");
				$a = decbin(hexdec(fgets($handle2, 4096)));

				//Check the last bit
				if (substr($a, (strlen($a)-1) ,1)==0) {
					$validInt=FALSE;
				}
				fclose($handle2);

				//If the interface is not a bridge and is currently up then add it to
				//the $interfaces array
				if ($validInt==TRUE) {
					$tmpArray[]=$file;
				}
			}
		}
		closedir($handle);
	}
	//Set the $interfaces array as $tmpArray but in reverse order.  This is because the fopen
	//function gets files in reverse order.
	$interfaces=array_reverse($tmpArray);
}
?>
