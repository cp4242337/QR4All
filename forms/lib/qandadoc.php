<?php
include 'filterinput.php';
include 'request.php';
$formid = JRequest::getVar('form','0');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Q&A Submissions</title>
<link rel="stylesheet" href="../aegismod.css" type="text/css" />
<script type="text/javascript"> 
function GetQA(){
	var xmlHttp;
	try { xmlHttp=new XMLHttpRequest(); } 
	catch (e){ 
		try { xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");  } 
		catch (e){
			try { xmlHttp=new ActiveXObject("Microsoft.XMLHTTP"); } 
				catch (e){ alert("No AJAX!?"); return false; } 
			} 
		} 
		xmlHttp.onreadystatechange=function(){ 
		if(xmlHttp.readyState==4){ document.getElementById('content').innerHTML=xmlHttp.responseText; setTimeout('GetQA()',10000); } 
	} 
	xmlHttp.open("GET","qandapres.php?form=<?php echo $formid; ?>",true); xmlHttp.send(null); 
} 
window.onload=function()
{ 
	setTimeout('GetQA()',1000); 
} 
</script> 
</head>

<body>
<div id="wrapper"><div id="header"></div><div id="content"><h1 align="center">Loading...</h1></div><div id="footer"></div><div style="clear:both;"></div>

</body>
</html>

