<?php
include('Browscap.php');
include('ipinfodb.class.php');

$db = new mysqli('localhost','qr4all','qr4all','qr4all');
$q ='SELECT * FROM qr4_videos as v ';
$q.='RIGHT JOIN qr4_templates as t ON v.vid_tmpl=t.tmpl_id ';
$q.='WHERE v.vid_code= "'.$_GET['c'].'"';
$res = $db->query($q);
$vidinfo = $res->fetch_object(); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title><?php echo $vidinfo->vid_pubtitle; ?></title>
		<link rel="stylesheet" href="<?php echo $vidinfo->tmpl_url; ?>" type="text/css" />
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
			flashvars.returl="<?php echo $vidinfo->vid_returl; ?>";
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
			swfobject.embedSWF(
				"VidPlyr.swf", "flashContent",
				"512", "288",
				swfVersionStr, xiSwfUrlStr,
				flashvars, params, attributes);
		</script>
	</head>
	<body>
		<div id="wrapper"><div id="header"></div>
		<div id="content">
		<div align="center">
		<div id="flashContent">
			<a href="http://www.adobe.com/go/getflash">
				<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
			</a>
			<p>This page requires Flash Player version 10.0.2 or higher.</p>
		</div></div><?php if ($vidinfo->vid_returl) { ?>
		<div align="center">
		<br /><a href="<?php echo $vidinfo->vid_returl; ?>"><?php echo $vidinfo->vid_rettitle; ?></a>
		</div>
		<?php } ?>
		</div>
		<div id="footer"></div>
		<div style="clear:both;"></div>
		</div>
	</body>
</html>