<?
	include("config.inc.php")
?>

<html><head><title>Results of WANalyzer</title>
</head>

<script language="JavaScript">
function callWANEM(obj) {

        var tar="wanem.php";
        open(tar,"test","width=375,height=320,status=yes, left=700, top=350");
        return true;
}
</script>

<body bgcolor="#FFE87C">
<center>
<b><font color="blue">RESULTS</font></b>
<?php
                $ip=$_REQUEST['pc'];
                $command=$wanchar_DIR."/tcs_wanc_menu.sh $ip";

                $output=shell_exec($command." 2>&1");  //system call

                print "<br>";

                if(strlen($output) < 5) {

                    print("<br><br><b><font color=red>Remote Host Not Reachable !!</font></b>");
                }
                else {
                        $left=explode(",",$output);
			#echo "count=".count($left);
			#for ($i=0; $i < count($left); $i=$i+1) echo $i."=".$left[$i];

                	if (count($left) != 12) {

                    		print("<br><br><b><font color=red>Can't measure!! Please repeat.</font></b>");
			}
			else {
                        	//print $output;
                        	print "<table align=center border=1 cellspacing=1 cellpadding=8>";
                        	for($i=0,$j=1; $left[$i]; $i=$i+2,$j=$j+2) {

                                	if($i % 4 == 0)
                                        	$bgcol = "pink";
                                	else
                                        	$bgcol = "white";

                                	print "<tr bgcolor=$bgcol>";
                                	print "<td align=left><font color=blue size=3><b>$left[$i]</b></font></td>";
                                	print "<td align=left><font color=blue size=3><b>$left[$j]</b></font></td>";

                                	print "</tr>";
				}
                        	print "</table>";
?>
				</center>
				<center><b>
				<form name="input_ip" method="post" action="result.php" onSubmit= "return callWANEM(this)";>
<br>
				<input type = "button" name = "submit" value = "Start WAN Emulation"  onClick= "return callWANEM(this)";>
				</form>
				</center>
<?
                        }
                  } 
?>
</body>
</html>
