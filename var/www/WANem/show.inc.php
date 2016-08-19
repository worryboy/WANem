<?
/****************************************************************************/
/*                              COPYRIGHT                                   */                 
/****************************************************************************/
/*                                                                          */
/*             Please have a look at CopyrightInformation.txt               */
/*                                                                          */
/****************************************************************************/
/*                                                                          */

#include_once("disc.inc.php")
//******************************
//Function to display interfaces
//******************************
function show_interfaces ($advflag,$interfaces, $del, $delJitter, $delCorrelation, $delDistribution, $loss, $lossCorrelation, $dup, $dupCorrelation, $reorder, $reorderCorrelation, $gap, $bandwidth, $corrupt, $sym, $disc, $limit, $displayCmd, $src, $srcSub, $dest, $destSub, $port, $advanced, &$showButton) {
	
	//Set showButton to false
	$showButton=false;
         if($advflag)
               $width="100%";
         else
               $width="100%"; 
	//Display the commands if the 'Display commands' checkbox was checked.
	if (isset($_POST['chkDisplay']) & isset($_POST['btnRefresh'])==false & isset($_POST['btnReset'])==false & $displayCmd!="") {
?>
		<center>
		<div style="color: #000000; background-color: #ffffff; width:auto; align: center; display:table;">
			<table border="0" align="center">
			<tr>
			<td>
			<p align="left"><b>The following commands would be executed:</b></p>
			<p align="left"><?=$displayCmd?></p>
			<p> </p>
			</td>
			</tr>
			</table>
		</div>
		</center>
<?
	}
	for ($i=1; $i<=count($interfaces);++$i) {
                
		//echo "sym[$i-1] =", $sym[$i-1];
                if($advflag == 1)
                   {?>
                       <center>
                       <div style="color: #000000; background-color: #aaccff; border: thin solid #000000; width: auto; align: center; display: table;">
                <table border="1" align="center" width="auto">
                  <tr>
                        <td colspan="6" align="center">
                          <p align="center"><b>Interface: <?=$interfaces[$i-1]?></b>
                        </td>
                  </tr>
                  <tr>
                        <td nowrap width="80%" colspan="4" >
                          <p align="center"><b>Bandwidth(BW)</b></td>
                        <td nowrap width="20%" colspan="2" >
                          <p align="center"><b>Delay</b></td>
                   </tr>
                    <tr>
                        <td nowrap width="10%">Choose BW</td>
                        <td nowrap width="40%">
                                <select name="txtBandwidthAuto<?=$i?>" size="1" align="left">
                                <option value=<?=getVal($advflag, $bandwidth[$i-1])?>><? echo linkName($advflag, $bandwidth[$i-1]); ?></option>
				<? if ($bandwidth[$i-1] != "9") {?> <option value="9"><? echo "Modem - 9600 bps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "20") {?> <option value="20"><? echo "Level 1 cable - 20 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "64") {?> <option value="64"><? echo "DS-0, Pulse Code Modulation - 64 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "128") {?> <option value="128"><? echo "ISDN - 128 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "1581") {?> <option value="1581"><? echo "T-1, DS-1 North America - 1.544 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "2097") {?> <option value="2097"><? echo "E-1, DS-1 Europe - 2.048 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "3228") {?> <option value="3228"><? echo "DS-1c - 3.152 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "6291") {?> <option value="6291"><? echo "Standard ADSL downstream - 6.144 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "6463") {?> <option value="6463"><? echo "T-2, DS-2 North America - 6.312 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "8651") {?> <option value="8651"><? echo "E-2 Europe - 8.448 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "10240") {?> <option value="10240"><? echo "Thin Ethernet, CAT-3 cable - 10 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "16384") {?> <option value="16384"><? echo "Token ring LAN - 16 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "20480") {?> <option value="20480"><? echo "CAT-4 cable - 20 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "35193") {?> <option value="35193"><? echo "E-3 Europe - 34.368 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "45810") {?> <option value="45810"><? echo "T-3, DS-3 North America - 44.736 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "53084") {?> <option value="53084"><? echo "OC-1, STS-1 - 51.84 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "102400") {?> <option value="102400"><? echo "CDDI, FDDI, Fast ethernet - 100 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "159252") {?> <option value="159252"><? echo "OC-3, STS-3, CAT-5, High speed ADSL downstream - 155.52 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "477757") {?> <option value="477757"><? echo "OC-9 - 466.56 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
				<? if ($bandwidth[$i-1] != "1304000") {?> <option value="1304000"><? echo "OC-24 - 1.244 Gbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
                                </select>
                        </td>
                	<td nowrap width="20%">Other: Specify BW(Kbps)</td>
                	<td width="10%"><input type="text" name="txtBandwidth<?=$i?>" size="7" value=<? echo $bandwidth[$i-1] ?>></td>
                        <td nowrap width="10%">Delay time(ms) </td>
                        <td nowrap width="10%"><input type="text" name="txtDelay<?=$i?>" size="7" value=<? echo $del[$i-1] ?>></td>
                   </tr>
                   </table>
                   </div>
                   </center>
                   <input type="hidden" name="txtSrc<?=$i?>" value="any">
                   <input type="hidden" name="txtDest<?=$i?>" value="any">
                   <input type="hidden" name="txtPort<?=$i?>" value="any">
<?
			$showButton=true;
                   }
                else{
		if ($advanced[$i-1]!=1) {
		    //There is an interface with none or one rule set so the apply settings button should be set to visible
			$showButton=true;
?>
	<center>
	<div style="color: #000000; background-color: #aaccff; border: thin solid #000000; width:auto; align: center; display: table;">
	<table border="1" align="center" width="100%">
		<tr>
		<td width="20%" colspan="2" align="center">
		  <b>Interface: <?=$interfaces[$i-1]?></b>
		</td>
		<td width="50%" colspan="5" align="center"><b>Packet Limit <input type="text" name="txtLimit<?=$i?>" size="9" value=<? echo $limit[$i-1] ?>></b> (Default=1000)
		</td>
		<td width="30%" colspan="3" align="center">
		<b>Symmetrical Network:<select size="1" name="selSym<?=$i?>"></b>
		  <option><? if (!(($sym[$i-1] == "Yes") || ($sym[$i-1] == "No"))) $sym[$i-1] = "Yes"; echo $sym[$i-1] ?></option>
		  <option><? if ($sym[$i-1] == "No") echo "Yes"; else echo "No"; ?></option>
		</select>
		</td>
		</tr>
		<tr>
                <td width="10%">
                 <p align="left"><b>Bandwidth</b></td>
                <td width="10%" >Choose BW</td>
                <td width="50%" colspan="5" >
                  <select name="txtBandwidthAuto<?=$i?>" size="1" align="left">
                  <option value=<?=getVal($advflag, $bandwidth[$i-1])?>><? echo linkName($advflag, $bandwidth[$i-1]); ?></option>
			<? if ($bandwidth[$i-1] != "9") {?> <option value="9"><? echo "Modem - 9600 bps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "20") {?> <option value="20"><? echo "Level 1 cable - 20 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "64") {?> <option value="64"><? echo "DS-0, Pulse Code Modulation - 64 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "128") {?> <option value="128"><? echo "ISDN - 128 Kbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "1581") {?> <option value="1581"><? echo "T-1, DS-1 North America - 1.544 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "2097") {?> <option value="2097"><? echo "E-1, DS-1 Europe - 2.048 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "3228") {?> <option value="3228"><? echo "DS-1c - 3.152 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "6291") {?> <option value="6291"><? echo "Standard ADSL downstream - 6.144 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "6463") {?> <option value="6463"><? echo "T-2, DS-2 North America - 6.312 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "8651") {?> <option value="8651"><? echo "E-2 Europe - 8.448 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "10240") {?> <option value="10240"><? echo "Thin Ethernet, CAT-3 cable - 10 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "16384") {?> <option value="16384"><? echo "Token ring LAN - 16 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "20480") {?> <option value="20480"><? echo "CAT-4 cable - 20 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "35193") {?> <option value="35193"><? echo "E-3 Europe - 34.368 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "45810") {?> <option value="45810"><? echo "T-3, DS-3 North America - 44.736 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "53084") {?> <option value="53084"><? echo "OC-1, STS-1 - 51.84 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "102400") {?> <option value="102400"><? echo "CDDI, FDDI, Fast ethernet - 100 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "159252") {?> <option value="159252"><? echo "OC-3, STS-3, CAT-5, High speed ADSL downstream - 155.52 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "477757") {?> <option value="477757"><? echo "OC-9 - 466.56 Mbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
			<? if ($bandwidth[$i-1] != "1304000") {?> <option value="1304000"><? echo "OC-24 - 1.244 Gbps"; ?></option><? } else {?>  <option value="Other"><? echo "Other"; ?></option>  <? } ?>
                  </select>
                </td>
                <td width="20%" colspan="2" >Other: Specify BW(Kbps)</td>
                <td width="10%" ><input type="text" name="txtBandwidth<?=$i?>" size="7" value=<? echo $bandwidth[$i-1] ?>></td>
                </tr>
		<tr>                  	
		<td width="20%" colspan="2" >
		  <p align="center"><b>Delay</b></td>
		<td width="20%" colspan="2" >
		  <p align="center"><b>Loss</b></td>
		<td width="20%" colspan="2" >
		  <p align="center"><b>Duplication</b></p></td>
		<td width="20%" colspan="2" >
		  <p align="center"><b>Packet reordering</b></td>
		<td width="20%" colspan="2" >
		  <p align="center"><b>Corruption</b></td>
		</tr>

		<tr>
		<td width="10%" >Delay time(ms) </td>
		<td width="10%" ><input type="text" name="txtDelay<?=$i?>" size="7" value=<? echo $del[$i-1] ?>></td>
		<td width="10%" >Loss(%)</td>
		<td width="10%" ><input type="text" name="txtLoss<?=$i?>" size="7" value=<? echo $loss[$i-1] ?>></td>
		<td width="10%" >Duplication(%)</td>
		<td width="10%" ><input type="text" name="txtDup<?=$i?>" size="7" value=<? echo $dup[$i-1] ?>></td>
		<td width="10%" >Reordering(%)</td>
		<td width="10%" ><input type="text" name="txtReorder<?=$i?>" size="7" value=<? echo $reorder[$i-1] ?>></td>
		<td width="10%" >Corruption(%)</td>
		<td width="10%" ><input type="text" name="txtCorrupt<?=$i?>" size="7" value=<? echo $corrupt[$i-1] ?>></td>
		</tr>
		<tr>
		<td width="10%" >Jitter(ms)</td>
		<td width="10%" ><input type="text" name="txtDelayJitter<?=$i?>" size="7" value=<? echo $delJitter[$i-1] ?>></td>
		<td width="10%" >Correlation(%)</td>
		<td width="10%" ><input type="text" name="txtLossCorrelation<?=$i?>" size="7" value=<? echo $lossCorrelation[$i-1] ?>></td>
		<td width="10%" >Correlation(%)</td>
		<td width="10%" ><input type="text" name="txtDupCorrelation<?=$i?>" size="7" value=<? echo $dupCorrelation[$i-1] ?>></td>
		<td width="10%" >Correlation(%)</td>
		<td width="10%" ><input type="text" name="txtReorderCorrelation<?=$i?>" size="7" value=<? echo $reorderCorrelation[$i-1] ?>></td>
		</tr>
		<tr>
		<td width="10%" colspan="1" >Correlation(%)</td>
		<td width="10%" colspan="1" ><input type="text" name="txtDelayCorrelation<?=$i?>" size="7" value=<? echo $delCorrelation[$i-1] ?>></td>
		<td width="10%" colspan="4" ></td>
		<td width="10%" colspan="1" >Gap(packets)</td>
		<td width="10%" colspan="1" ><input type="text" name="txtGap<?=$i?>" size="7" value=<? echo $gap[$i-1] ?>></td>
		</tr>
		<tr>
		<td width="10%" >Distribution</td>
		<td width="10%" ><select size="1" name="selDelayDistribution<?=$i?>">
		  <option <?if($delDistribution=="0") echo "selected"?>>-N/A-</option>
		  <option>Normal</option>
		  <option>Pareto</option>
		  <option>Paretonormal</option>
		  </select></td>
		<td width="10%" > </td>
		<td width="10%" > </td>
		<td width="10%" > </td>
		<td width="10%" > </td>
		<td width="10%" > </td>
		<td width="10%" > </td>
		</tr>
		</table>

	<table border="1" width="auto" valign=top>
		  <tr>
			<td width="21%" align="left" colspan=2><b>Idle timer Disconnect</b></td>
			<td width="4%"  align=right>Type</td>
			<td width="20%" colspan=2>
			<select size="1" name="selidtyp<?=$i?>" align="left">
			<option><? if ($disc[$i-1]->idl_type == "") $disc[$i-1]->idl_type = "none"; echo $disc[$i-1]->idl_type ?></option>
			<option><? if ($disc[$i-1]->idl_type == "tcp-reset") echo "none"; else echo "tcp-reset"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-net-unreachable") echo "none"; else echo "icmp-net-unreachable"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-host-unreachable") echo "none"; else echo "icmp-host-unreachable"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-port-unreachable") echo "none"; else echo "icmp-port-unreachable"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-proto-unreachable") echo "none"; else echo "icmp-proto-unreachable"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-net-prohibited") echo "none"; else echo "icmp-net-prohibited"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-host-prohibited") echo "none"; else echo "icmp-host-prohibited"; ?></option>
			<option><? if ($disc[$i-1]->idl_type == "icmp-admin-prohibited") echo "none"; else echo "icmp-admin-prohibited"; ?></option>
			</select></td>
			<td width="10%" align=right colspan=3>Idle Timer</td>
			<td width="4%"  align=left><input type="text" name="txtidtmr<?=$i?>" size="5" value=<? echo $disc[$i-1]->idl_timer ?>></td>
			<td width="15%" align=right colspan=3>Disconnect Timer</td>
			<td width="4%" align=left><input type="text" name="txtidsctmr<?=$i?>" size="5" value=<? echo $disc[$i-1]->idl_disc_timer ?>></td>
		  </tr>
		  <tr>
			<td width="20%" colspan="2" align="left"><b>Random Disconnect</b></td>
			<td width="4%" align=right>Type</td>
			<td width="20%" colspan="2"align=left>
			<select size= "1"  name="selrndtyp<?=$i?>" align="left">
			  <option><? if ($disc[$i-1]->rnd_type == "") $disc[$i-1]->rnd_type = "none"; echo $disc[$i-1]->rnd_type ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "tcp-reset") echo "none"; else echo "tcp-reset"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-net-unreachable") echo "none"; else echo "icmp-net-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-host-unreachable") echo "none"; else echo "icmp-host-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-port-unreachable") echo "none"; else echo "icmp-port-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-proto-unreachable") echo "none"; else echo "icmp-proto-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-net-prohibited") echo "none"; else echo "icmp-net-prohibited"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-host-prohibited") echo "none"; else echo "icmp-host-prohibited"; ?></option>
			  <option><? if ($disc[$i-1]->rnd_type == "icmp-admin-prohibited") echo "none"; else echo "icmp-admin-prohibited"; ?></option>
			  </select></td>
			<td nowrap width="10%" align=right>MTTF Low</td>
			<td width="4%" align=left><input type="text" name="txtrndmttflo<?=$i?>" size="5" value=<? echo $disc[$i-1]->rnd_mttf_lo ?>></td>
			<td nowrap width="10%" align=right>MTTF High</td>
			<td width="4%" align=left><input type="text" name="txtrndmttfhi<?=$i?>" size="5" value=<? echo $disc[$i-1]->rnd_mttf_hi ?>></td>
			<td nowrap width="10%" align=right>MTTR Low</td>
			<td width="4%" align=left><input type="text" name="txtrndmttrlo<?=$i?>" size="5" value=<? echo $disc[$i-1]->rnd_mttr_lo ?>></td>
			<td nowrap width="10%" align=right>MTTR High</td>
			<td width="4%" align=left><input type="text" name="txtrndmttrhi<?=$i?>" size="5" value=<? echo $disc[$i-1]->rnd_mttr_hi ?>></td>
		  </tr>
		  <tr>
			<td width="20%" colspan=2 align="left"><b>Random connection Disconnect</b></td>
			<td width="4%" align=right>Type</td>
			<td width="20%" colspan=2 align=left>
			<select size="1" name="selrcdtyp<?=$i?>" align="right">
			  <option><? if ($disc[$i-1]->rcd_type == "") $disc[$i-1]->rcd_type = "none"; echo $disc[$i-1]->rcd_type ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "tcp-reset") echo "none"; else echo "tcp-reset"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-net-unreachable") echo "none"; else echo "icmp-net-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-host-unreachable") echo "none"; else echo "icmp-host-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-port-unreachable") echo "none"; else echo "icmp-port-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-proto-unreachable") echo "none"; else echo "icmp-proto-unreachable"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-net-prohibited") echo "none"; else echo "icmp-net-prohibited"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-host-prohibited") echo "none"; else echo "icmp-host-prohibited"; ?></option>
			  <option><? if ($disc[$i-1]->rcd_type == "icmp-admin-prohibited") echo "none"; else echo "icmp-admin-prohibited"; ?></option>
			  </select></td>
			<td nowrap width="10%" align=right>MTTF Low</td>
			<td width="4%" align=left><input type="text" name="txtrcdmttflo<?=$i?>" size="5" value=<? echo $disc[$i-1]->rcd_mttf_lo ?>></td>
			<td nowrap width="10%" align=right>MTTF High</td>
			<td width="4%" align=left><input type="text" name="txtrcdmttfhi<?=$i?>" size="5" value=<? echo $disc[$i-1]->rcd_mttf_hi ?>></td>
			<td nowrap width="10%" align=right>MTTR Low</td>
			<td width="4%" align=left><input type="text" name="txtrcdmttrlo<?=$i?>" size="5" value=<? echo $disc[$i-1]->rcd_mttr_lo ?>></td>
			<td nowrap width="10%" align=right>MTTR High</td>
			<td width="4%" align=left><input type="text" name="txtrcdmttrhi<?=$i?>" size="5" value=<? echo $disc[$i-1]->rcd_mttr_hi ?>></td>
		  </tr>
		</table>
		<table border="1" align="center" width="auto">
		  <tr>
			<td width="9%" >IP source address</td>
			<td width="11%" ><input type="text" name="txtSrc<?=$i?>" size="13" value=<? echo $src[$i-1] ?>></td>
			<td width="9%" >IP source subnet</td>
			<td width="11%" ><input type="text" name="txtSrcSub<?=$i?>" size="13" value=<? echo $srcSub[$i-1] ?>></td>
			<td width="9%" >IP dest address</td>
			<td width="11%" ><input type="text" name="txtDest<?=$i?>" size="13" value=<? echo $dest[$i-1] ?>></td>
			<td width="9%" >IP dest subnet</td>
			<td width="11%" ><input type="text" name="txtDestSub<?=$i?>" size="13" value=<? echo $destSub[$i-1] ?>></td>
			<td width="9%" >Application port if any</td>
			<td width="11%" ><input type="text" name="txtPort<?=$i?>" size="13" value=<? echo $port[$i-1] ?>></td>
		  </tr>
		</table>
	</div>
	</center>
<?
		} else {
			//Display the advanced mode warning in the interface box instead of all the expected fields
?>
		<div style="color: #000000; background-color: #aaccff; border: thin solid #000000; width: 970;  align: center">
		<table border="1" width="100%" align="center">
		  <tr>
			<td width="100%" colspan="12" align="center">
			  <p align="center"><b>Interface: <?=$interfaces[$i-1]?></b>
			</td>
		  </tr>
		</table>
		<p align="center"><b>There is more than one set of netem rules running on this interface. Use advanced mode to edit these rules or delete them with the "reset settings" button.</b></p>
		</div>
<?
		}
           }
	}
}

//Function to resolve link name from its value
function linkName($advflag, $bw) {

	switch($bw) {
		case "9": $link="Modem - 9600 bps"; break;
		case "20": $link="Level 1 Cable - 20 Kbps"; break;
		case "64": $link="DS-0, Pulse Code Modulation - 64 Kbps"; break;
		case "128": $link="ISDN 128 Kbps"; break;
		case "1581": $link="T-1, DS-1 North America - 1.544 Mbps"; break;
		case "2097": $link="E-1, DS-1 Europe - 2.048 Mbps"; break;
		case "3228": $link="DS-1c - 3.152 Mbps"; break;
		case "6291": $link="Standard ADSL Downstream - 6.144 Mbps "; break;
		case "6463": $link="T-2, DS-2 North America - 6.312 Mbps"; break;
		case "8651": $link="E-2 Europe - 8.448 Mbps"; break;
		case "10240": $link="Thin Ethernet, CAT-3 cable - 10 Mbps"; break;
		case "16384": $link="Token ring LAN - 16 Mbps"; break;
		case "20480": $link="CAT-4 Cable - 20 Mbps"; break;
		case "35193": $link="E-3 Europe - 34.368 Mbps"; break;
		case "45810": $link="T-3, DS-3 North America - 44.736 Mbps"; break;
		case "53084": $link="OC-1, STS-1 - 51.84 Mbps"; break;
		case "102400": $link="CDDI, FDDI, Fast Ethernet - 100 Mbps"; break;
		case "159252": $link="OC-3, STS-3, CAT-5, Highspeed ADSL downstream - 155 Mbps"; break;
		case "477757": $link="OC-9 - 466.56 Mbps"; break;
		case "1304000": $link="OC-24 - 1.244 Gbps"; break;
		default: $link="Other";
			/*
			if ($advflag == 1) 
				$link="None";
			else
				$link="Other";*/
			break;
	} 
	return($link);
}

//Function to resolve Other value from its value
function getVal($advflag, $bw) {
	$rbw=$bw;

	if (linkName($advflag, $bw) == "Other") $rbw="Other";

	return $rbw;
}

?>
