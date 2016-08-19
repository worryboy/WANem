<html>
<head>
	<title>TCS WANem v 3.0</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
</head>
<script language="JavaScript">
function IsNumeric(sText) {

	var ValidChars = "0123456789";
   	var IsNumber=true;
   	var C;
 
   	for (j = 0; j < sText.length && IsNumber == true; j++) {
 
      		C = sText.charAt(j); 
      		if (ValidChars.indexOf(C) == -1) {

			IsNumber = false;
         	}
      	}
   	return IsNumber;
}

function ValidateForm(obj) {

	var ip=document.input_ip.pc.value;
        var tar="https://"+ip;
	if(ip == "") {
             alert('Enter IP Address');
             document.input_ip.pc.select(); 
             return false;
	}

	var arr=ip.split(".");

	len=arr.length;

	for(i=0;i<len;i++) {
		if (IsNumeric(arr[i]) == false)
                    break;
	}

	if (i != 4) {
		alert('Invalid IP Address');
               	document.input_ip.pc.select();
               	return false;
	}
	
        window.open(tar,'mywindow','width=700,height=410,status=yes, left=305, top=275, screenX=300, screenY=200');
        return true;
}

</script>
<body bgcolor="white" onload="document.input_ip.submit.focus()">
<center><b>
<br>
<form name="input_ip" method="post" action="wanc.html" onSubmit= "return ValidateForm(this)";>  
Enter the IP of WANem machine: <input name=pc value=<?php echo $_SERVER['SERVER_ADDR']; ?> >
<input type = "button" name = "submit" value = "Submit" onClick= "return ValidateForm(this)";>
<br>
<br>
<p> <font color="red" size="2">
Note: This remote terminal should be used for executing commands and basic administration purpose only. <br>The "vi" editor is not supported completely as the 'Esc' key does not work.<br><br> The "startx" command is not recommended to used from the remote terminal. 
</font>
</p>
<br>
</form>
</b></center>

</body>

</html>

