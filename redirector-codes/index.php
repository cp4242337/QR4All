<?php
include('ipinfodb.class.php');
include('Browscap.php');

$db = new mysqli('localhost','qr4all','qr4all','qr4all');
$q='SELECT * FROM qr4_codes WHERE cd_code= "'.$_GET['c'].'"';
$res = $db->query($q);
$codeinfo = $res->fetch_object();
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




$q2  = 'INSERT INTO qr4_hits (hit_code,hit_ipaddr,hit_useragent,hit_browser,hit_browserver,hit_platform,hit_ismobile,hit_lat,hit_long,hit_city,hit_region,hit_country,hit_countrycode,hit_timezone) ';
$q2 .= 'VALUES ('.$codeinfo->cd_id.',"'.$ipaddr.'","'.$db->real_escape_string($_SERVER['HTTP_USER_AGENT']).'","'.$browser['browser'].'","'.$browser['version'].'","'.$browser['platform'].'","'.$browser['ismobiledevice'].'","'.$lat.'","'.$long.'","'.$city.'","'.$region.'","'.$country.'","'.$countrycode.'","'.$timezone.'")';
$db->query($q2);

header("Location:".$codeinfo->cd_url);