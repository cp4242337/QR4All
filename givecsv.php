<?php
include 'dbconnect.php';
include 'check_login.php';
if (!$_SESSION['logged']) { header("Location: index.php?e=1"); exit; }
$view=$_GET['v'];
/*******************/
//View Entrants
/*******************/
if ($view=='reviews') {
	//get RegData
	$filename="reviews.csv";
	$rdq='SELECT * FROM fmp_contest ORDER BY con_time DESC'; 
	$rdres=mysql_query($rdq); 

	//set up table, table sort, & form & reg item arrays
	$thedata = 'Time,Name,Email,Degree,Specialty,In comparison to other articles on this topic that you have read how would you rate this article?,What percentage of the information in this article is new to you?,As a result of reading this article are you making any changes in your practice?,Please give two examples of changes you plan to implement,Do you find the information in this article to be objective balanced and evidence-based,Is there subject matter that you would like included in future publications,Would you like to become a Clinical Reactor,Campaign'."\n";
	
	while ($rd = mysql_fetch_array($rdres,MYSQL_ASSOC)) { 
		$thedata .=  $rd['con_time'].','.$rd['con_name'].','.$rd['con_email'].','.$rd['con_degree'].','.$rd['con_specialty'].','.$rd['con_comparison'].','.$rd['con_newtoyou'].','.$rd['con_makechanges'].','.$rd['con_changes'].','.$rd['con_objective'].','.$rd['con_comments'].','.$rd['con_feedback'].','.$rd['con_campaign']."\n";
	}
}

/*******************/
//View Entrants
/*******************/
if ($view=='tool') {
	//get RegData
	$filename="reviews.csv";
	$rdq='SELECT * FROM fmp_tool ORDER BY con_time DESC'; 
	$rdres=mysql_query($rdq); 

	//set up table, table sort, & form & reg item arrays
	$thedata = 'Time,Name,Email,Would you like to become a Clinical Reactor,Campaign'."\n";
	
	while ($rd = mysql_fetch_array($rdres,MYSQL_ASSOC)) { 
		$thedata .=  $rd['con_time'].','.$rd['con_name'].','.$rd['con_email'].','.$rd['con_feedback'].','.$rd['con_campaign']."\n";
	}
}

/*******************/
//View Stats
/*******************/
if ($view=='stats') {
	//get Data
	$filename="hits.csv";
	$rdq='SELECT * FROM fmp_entry ORDER BY ent_time DESC'; 
	$rdres=mysql_query($rdq); 
	
	$thedata = 'Time,Campaign,Page'."\n";

	while ($rd = mysql_fetch_array($rdres,MYSQL_ASSOC)) { 
		$thedata .= $rd['ent_time'].','.$rd['ent_what'].','.$rd['ent_campaign']."\n";
	}
	
}
	header('Pragma: public');
    header("Expires: Wed, 12 Sep 2001 05:00:00 GMT");                  // Date in the past   
    header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
    header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
    header('Content-Transfer-Encoding: none');
    header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
    //header("Content-type: application/x-msexcel");                    // This should work for the rest
    header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $thedata;
	exit;

?>