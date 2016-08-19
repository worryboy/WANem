<html>
<head>
  <script>
   function save_state() {
	var win1 = window.open("download.php", "Save", 'width=200, height=90, status=no, left=315, top=320, screenX=300, screenY=300');
   }	
  </script>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> </link>
</head>
<body>
	<table width="500" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
	<tr>
		<td>
			<strong>Save WANem state to Client</strong>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
			<tr>
				<td>
					<a href="#" ONCLICK="save_state();">Click here to Save</a>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	<br>
	<table width="500" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
	<tr>
		<td>
			<strong>Restore WANem state from Client</strong>
		</td>
	</tr>
	<tr>
		<form action="upload.php" method="post" enctype="multipart/form-data" name="form1" id="form1">
		<td>
			<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
			<tr>
				<td>Select file 
				<input name="ufile" type="file" id="ufile" size="50" /></td>
			</tr>
			<tr>
				<td align="center"><input type="submit" name="Submit" value="Upload" /></td>
			</tr>
			</table>
		</td>
		</form>
	</tr>
	</table>
</body>
</html>
