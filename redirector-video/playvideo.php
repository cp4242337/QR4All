<?php
include('Browscap.php');
include('ipinfodb.class.php');

$db = new mysqli('localhost','qr4all','qr4all','qr4all');
$q ='SELECT * FROM qr4_videos as v ';
$q.='RIGHT JOIN qr4_viddom as d ON v.vid_domain=d.vd_id ';
$q.='WHERE v.vid_code= "'.$_GET['c'].'"';
$res = $db->query($q);
$vidinfo = $res->fetch_object(); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>VidPlyr</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript" src="swfobject.js"></script>
		<script type="text/javascript">
			<!-- Adobe recommends that developers use SWFObject2 for Flash Player detection. -->
			<!-- For more information see the SWFObject page at Google code (http://code.google.com/p/swfobject/). -->
			<!-- Information is also available on the Adobe Developer Connection Under Detecting Flash Player versions and embedding SWF files with SWFObject 2" -->
			<!-- Set to minimum required Flash Player version or 0 for no version detection -->
			var swfVersionStr = "10.0.2";
			<!-- xiSwfUrlStr can be used to define an express installer SWF. -->
			var xiSwfUrlStr = "";
			var flashvars = {};
			flashvars.vidfile="<?php echo $vidinfo->vid_file; ?>_iphone.mp4";
			flashvars.vidtitle="<?php echo $vidinfo->vid_pubtitle; ?>";
			flashvars.vidrat="<?php echo $vidinfo->vid_ratio; ?>";
			var params = {};
			params.quality = "high";
			params.bgcolor = "#000000";
			params.play = "true";
			params.loop = "true";
			params.wmode = "window";
			params.scale = "showall";
			params.menu = "true";
			params.devicefont = "false";
			params.salign = "";
			params.allowscriptaccess = "sameDomain";
			params.allowFullScreen = "true";
			var attributes = {};
			attributes.id = "VidPlyr";
			attributes.name = "VidPlyr";
			attributes.align = "middle";
			swfobject.createCSS("html", "height:100%; background-color: #000000;");
			swfobject.createCSS("body", "margin:0; padding:0; overflow:hidden; height:100%;");
			swfobject.embedSWF(
				"VidPlyr.swf", "flashContent",
				"512", "288",
				swfVersionStr, xiSwfUrlStr,
				flashvars, params, attributes);
		</script>
	</head>
	<body>
		<!-- SWFObject's dynamic embed method replaces this alternative HTML content for Flash content when enough JavaScript and Flash plug-in support is available. -->
		<div id="flashContent">
			<a href="http://www.adobe.com/go/getflash">
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
			</a>
			<p>This page requires Flash Player version 10.0.2 or higher.</p>
		</div>
	</body>
</html>