<?php
include('Browscap.php');
include('ipinfodb.class.php');

$db = new mysqli('localhost','qr4all','qr4all','qr4all');
$q ='SELECT v.*,d.dom_dom as vdom, s.dom_dom as sdom FROM qr4_videos as v ';
$q.='RIGHT JOIN qr4_domains as d ON v.vid_domain=d.dom_id ';
$q.='RIGHT JOIN qr4_domains as s ON v.vid_sdomain=s.dom_id ';
$q.='WHERE v.vid_code= "'.$_GET['c'].'"';
$res = $db->query($q);
$vidinfo = $res->fetch_object(); 
//$browser = get_browser(null,true);
$bc = new Browscap("bc_cache"); 
$browser = $bc->getBrowser(null,true);
$ipaddr=$_SERVER['REMOTE_ADDR'];


//Load the class
$ipinfodb = new ipinfodb;
$ipinfodb->setKey('81352021746a636eadfd91bb40315d2846de4ae57fe07fbbc88f84a871d24b7f');
 
//Get errors and locations
$locations = $ipinfodb->getGeoLocation($ipaddr);
$errors = $ipinfodb->getError();
 
if ($locations['Status']=='OK') {
	$city=$locations['City'];
	$region=$locations['RegionName'];
	$lat=$locations['Latitude'];
	$long=$locations['Longitude'];
	$country=$locations['CountryName'];
	$countrycode=$locations['CountryCode'];
	$timezone=$locations['TimezoneName'];
}




$q2  = 'INSERT INTO qr4_vhits (hit_vid,hit_ipaddr,hit_useragent,hit_browser,hit_browserver,hit_platform,hit_ismobile,hit_lat,hit_long,hit_city,hit_region,hit_country,hit_countrycode,hit_timezone) ';
$q2 .= 'VALUES ('.$vidinfo->vid_id.',"'.$ipaddr.'","'.$db->real_escape_string($_SERVER['HTTP_USER_AGENT']).'","'.$browser['browser'].'","'.$browser['version'].'","'.$browser['platform'].'","'.$browser['ismobiledevice'].'","'.$lat.'","'.$long.'","'.$city.'","'.$region.'","'.$country.'","'.$countrycode.'","'.$timezone.'")';
$db->query($q2);


if ($browser['ismobiledevice']) {
	switch ($browser['platform']) {
		case 'iPhone OSX':
			$url="http://".$vidinfo->sdom.":1935/vod/".$vidinfo->vid_file."_iphone.mp4/playlist.m3u8";
			break;
		case 'Android':
			//$url="rtsp://medicom04.costeffectivedev.com:1935/vod/mp4:".$vidinfo->vid_file."_android.mp4";
			$url="http://".$vidinfo->vdom."/content/".$vidinfo->vid_file."_android.3gp";
			break;
		default:
			//$url="rtsp://medicom04.costeffectivedev.com:1935/vod/mp4:".$vidinfo->vid_file."_android.mp4";
			$url="http://".$vidinfo->vdom."/content/".$vidinfo->vid_file."_android.3gp";
			break;
	}
} else {
	$url="playvideo.php?c=".$_GET['c'];
}

header("Location:".$url);
//echo $url;