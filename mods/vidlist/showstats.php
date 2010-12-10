
<?php

echo '<form action="" method="post" name="statstimeper">';
echo 'Start: <input name="st_sdate" type="text" class="field statsdate" style="width:100px;" value="'.$sdate.'"> '; 
echo 'End: <input name="st_edate" type="text" class="field statsdate" style="width:100px;" value="'.$edate.'"> ';
echo '<input type="submit" name="submitdates" value="Go">'; 
echo '</form>';

$syear=date("Y",strtotime($sdate));
$smonth=date("m",strtotime($sdate));
$sday=date("d",strtotime($sdate));

$eyear=date("Y",strtotime($edate));
$emonth=date("m",strtotime($edate));
$eday=date("d",strtotime($edate));

//$dts=new DateTime($sdate);
//$dte=new DateTime($edate);
//$interval=date_diff($sdate,$edate);
//$numdays = $interval;//->days+1
$current = $sdate; 
$datetime2 = date_create($edate); 
$count = 0; 
while(date_create($current) <= $datetime2){ 
	$current = gmdate("Y-m-d", strtotime("+1 day", strtotime($current))); 
	$count++; 
} 
$numdays = $count; 

echo '<h3>Hits by Day</h3>';

?>
<script type="text/javascript">
window.addEvent('domready', function() {
	new DatePicker($$('input.statsdate'), { format: '%Y-%m-%d'}); //,pickerClass: 'datepicker_dashboard'
});

</script>

<script type='text/javascript' src='http://www.google.com/jsapi'></script>
<script type='text/javascript'>
	google.load('visualization', '1', {'packages':['annotatedtimeline','imagepiechart']});
	google.setOnLoadCallback(drawHitsByDay);
	function drawHitsByDay() {
		var data = new google.visualization.DataTable();
		data.addColumn('date', 'Date');
		<?php 
		foreach ($vids as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->vids as $cd) {
					echo 'data.addColumn(\'number\',\''.$cd->vid_title.'\');'."\n";
					$clcount++;
				}
			}
		}    
		?>
		data.addRows(<?php echo $numdays; ?>);
		<?php 
		$col = 0;
		foreach ($stats as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->vids as $cd) {
					$row=0;
					for ($y=$syear;$y<=$eyear;$y++) {
						if ($y==$syear) $sm = (int)$smonth;
						else $sm = 1;
						if ($y==$eyear) $em = (int)$emonth;
						else $em = 12;
						for ($m=$sm;$m<=$em;$m++) {
							if ($m==$smonth) $sd = (int)$sday;
							else $sd = 1;
							if ($m==$emonth) $ed = (int)$eday;
							else $ed = date("t",strtotime($y.'-'.$m));
							for ($d=$sd;$d<=$ed;$d++) {
								$pdate = date("Y-m-d",strtotime($y.'-'.$m.'-'.$d));
								if ($col==0) echo 'data.setValue('.$row.','.$col.',new Date('.$y.','.($m-1).','.$d.'));'."\n";
								echo 'data.setValue('.$row.','.($col+1).','.(int)$cd->dhits[$pdate].'); //'.$pdate."\n";
								$row++;
							}
						}
					}
		    		$col++;
		    	}
	    	}
    	} 
		?>
	    var chart_hitsbyday = new google.visualization.AnnotatedTimeLine(document.getElementById('hitsbyday'));
	    chart_hitsbyday.draw(data, {wmode: 'opaque'});
	}
</script>

<div id='hitsbyday' style='width: 100%; height: 300px;'></div>

<hr size="1">
<h3>Total Hits by Category</h3> 
<?php 

foreach ($vids as $cl) {
	foreach ($cl->cats as $ct) {
		if ($ct->vids) {
			echo '<div class="codelist-client" style="clear:both">'.$cl->cl_name.'<br>'; 
			echo '<div class="codelist-cat" style="clear:both">'.$ct->cat_name.'<br>';
			echo '<script type="text/javascript">';
			echo 'google.setOnLoadCallback(drawHitsByCat'.$ct->cat_id.');'."\n";
			echo 'function drawHitsByCat'.$ct->cat_id.'() {'."\n";
			echo 'var data'.$ct->cat_id.' = new google.visualization.DataTable();'."\n";
			echo 'data'.$ct->cat_id.'.addColumn(\'string\',\'Video\');'."\n";
			echo 'data'.$ct->cat_id.'.addColumn(\'number\',\'Hits\');'."\n";
			echo 'data'.$ct->cat_id.'.addRows('.count($ct->vids).');'."\n";
			$row=0;
			$hitstotal=0;
			$pertot=0;
			foreach ($ct->vids as $cd) {
				echo 'data'.$ct->cat_id.'.setValue('.$row.',0,\''.$cd->vid_title.'\');'."\n";
				echo 'data'.$ct->cat_id.'.setValue('.$row.',1,'.$cd->hits.');'."\n";
				$hitstotal=$hitstotal+$cd->hits;
				$row++;
			}
			echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbc'.$ct->cat_id.'\'));'."\n";
        	echo 'chart'.$ct->cat_id.'.draw(data'.$ct->cat_id.', {width: 400, height: 300, is3D: true});'."\n";
        	echo '}'."\n";
        	echo '</script>';
        	echo '<div id="chart_hbc'.$ct->cat_id.'" style="float:left;"></div>';
        	echo '<div class="codelist-codes" style="width:330;float:left;">'; 
			echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
			echo '<tr>';
			echo '<th width="180">Name</th><th width="50">Type</th><th width="100" colspan="2">Hits</th></tr>';
			foreach ($ct->vids as $d) { 
				echo '<tr>';
				echo '<td>'.$d->vid_title.'&nbsp;</td>';
				echo '<td align="right">'.$d->hits.'</td>';
				$per = round((($d->hits/$hitstotal)*100),1);//,PHP_ROUND_HALF_UP
				$pertot = $pertot+$per;
				echo '<td align="right">'.$per.'%</td>';
				echo '</tr>';
			} 
			echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$hitstotal.'</td><td align="right">'.$pertot.'%</td></tr>';
			echo '</table></div>';
			echo '</div>';
			echo '</div>';
			foreach ($ct->vids as $o) {
				foreach ($o->browsers as $ob) {
					$browsers[$ob->hit_browser] = $ob->hits + $browsers[$ob->hit_browser];
					$browsertotal=$browsertotal+$ob->hits;
				}
				foreach ($o->platforms as $op) {
					$platforms[$op->hit_platform] = $op->hits + $platforms[$op->hit_platform];
					$plattotal=$plattotal+$op->hits;
				}
				foreach ($o->ismobile as $om) {
					$ismobile[$om->hit_ismobile] = $om->hits + $ismobile[$om->hit_ismobile];
					$ismtotal=$ismtotal+$om->hits;
				}
				foreach ($o->countries as $oc) {
					$countries[$oc->hit_country] = $oc->hits + $countries[$oc->hit_country];
					$ctrytotal=$ctrytotal+$oc->hits;
				}
				foreach ($o->timezones as $ot) {
					$timezones[$ot->hit_timezone] = $ot->hits + $timezones[$ot->hit_timezone];
					$tztotal=$tztotal+$ot->hits;
				}
			}
		}
	}
}    
echo '<div style="clear:both">';
echo '<hr size="1">';
echo '<h3>Browsers, Platforms, & More...</h3>';

//Browsers
echo '<div style="clear:both;">';
echo '<script type="text/javascript">';
echo 'google.setOnLoadCallback(drawHitsByBrowser);'."\n";
echo 'function drawHitsByBrowser() {'."\n";
echo 'var datab = new google.visualization.DataTable();'."\n";
echo 'datab.addColumn(\'string\',\'Video\');'."\n";
echo 'datab.addColumn(\'number\',\'Hits\');'."\n";
echo 'datab.addRows('.count($browsers).');'."\n";
$row=0;
$hitstotal=0;
$pertot=0;
foreach ($browsers as $key=>$value) {
	echo 'datab.setValue('.$row.',0,\'';
	if ($key != ' ') echo $key;
	else echo 'Unknown';
	echo '\');'."\n";
	echo 'datab.setValue('.$row.',1,'.$value.');'."\n";
	$row++;
}
echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbb\'));'."\n";
echo 'chart'.$ct->cat_id.'.draw(datab, {title: \'Browsers\', width: 400, height: 300, is3D: true});'."\n";
echo '}'."\n";
echo '</script>';
echo '<div id="chart_hbb" style="float:left;"></div>';
echo '<div class="codelist-codes" style="width:280;float:left;">'; 
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr>';
echo '<th width="180">Browser</th><th width="100" colspan="2">Hits</th></tr>';
foreach ($browsers as $key=>$value) { 
	echo '<tr>';
	echo '<td>';
	if ($key != ' ') echo $key;
	else echo 'Unknown';
	echo '&nbsp;</td>';
	echo '<td align="right">'.$value.'</td>';
	$per = round((($value/$ismtotal)*100),1);//,PHP_ROUND_HALF_UP
	$pertot = $pertot+$per;
	echo '<td align="right">'.$per.'%</td>';
	echo '</tr>';
} 
echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$browsertotal.'</td><td align="right">'.$pertot.'%</td></tr>';
echo '</table></div>';
echo '</div>';


//Platforms
echo '<div style="clear:both;">';
echo '<script type="text/javascript">';
echo 'google.setOnLoadCallback(drawHitsByPlat);'."\n";
echo 'function drawHitsByPlat() {'."\n";
echo 'var datap = new google.visualization.DataTable();'."\n";
echo 'datap.addColumn(\'string\',\'Video\');'."\n";
echo 'datap.addColumn(\'number\',\'Hits\');'."\n";
echo 'datap.addRows('.count($platforms).');'."\n";
$row=0;
$hitstotal=0;
$pertot=0;
foreach ($platforms as $key=>$value) {
	echo 'datap.setValue('.$row.',0,\'';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '\');'."\n";
	echo 'datap.setValue('.$row.',1,'.$value.');'."\n";
	$row++;
}
echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbp\'));'."\n";
echo 'chart'.$ct->cat_id.'.draw(datap, {title: \'Platforms\', width: 400, height: 300, is3D: true});'."\n";
echo '}'."\n";
echo '</script>';
echo '<div id="chart_hbp" style="float:left;"></div>';
echo '<div class="codelist-codes" style="width:280;float:left;">'; 
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr>';
echo '<th width="180">Platform</th><th width="100" colspan="2">Hits</th></tr>';
foreach ($platforms as $key=>$value) { 
	echo '<tr>';
	echo '<td>';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '&nbsp;</td>';
	echo '<td align="right">'.$value.'</td>';
	$per = round((($value/$ismtotal)*100),1);//,PHP_ROUND_HALF_UP
	$pertot = $pertot+$per;
	echo '<td align="right">'.$per.'%</td>';
	echo '</tr>';
} 
echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$plattotal.'</td><td align="right">'.$pertot.'%</td></tr>';
echo '</table></div>';
echo '</div>';


//isMobile
echo '<div style="clear:both;">';
echo '<script type="text/javascript">';
echo 'google.setOnLoadCallback(drawHitsByM);'."\n";
echo 'function drawHitsByM() {'."\n";
echo 'var datam = new google.visualization.DataTable();'."\n";
echo 'datam.addColumn(\'string\',\'Video\');'."\n";
echo 'datam.addColumn(\'number\',\'Hits\');'."\n";
echo 'datam.addRows('.count($ismobile).');'."\n";
$row=0;
$hitstotal=0;
$pertot=0;
foreach ($ismobile as $key=>$value) {
	echo 'datam.setValue('.$row.',0,\'';
	if ($key) echo 'Yes';
	else echo 'No';
	echo '\');'."\n";
	echo 'datam.setValue('.$row.',1,'.$value.');'."\n";
	$row++;
}
echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbm\'));'."\n";
echo 'chart'.$ct->cat_id.'.draw(datam, {title: \'Is Mobile?\', width: 400, height: 300, is3D: true});'."\n";
echo '}'."\n";
echo '</script>';
echo '<div id="chart_hbm" style="float:left;"></div>';
echo '<div class="codelist-codes" style="width:280;float:left;">'; 
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr>';
echo '<th width="180">Is Mobile?</th><th width="100" colspan="2">Hits</th></tr>';
foreach ($ismobile as $key=>$value) { 
	echo '<tr>';
	echo '<td>';
	if ($key) echo 'Yes';
	else echo 'No';
	echo '&nbsp;</td>';
	echo '<td align="right">'.$value.'</td>';
	$per = round((($value/$ismtotal)*100),1);//,PHP_ROUND_HALF_UP
	$pertot = $pertot+$per;
	echo '<td align="right">'.$per.'%</td>';
	echo '</tr>';
} 
echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$ismtotal.'</td><td align="right">'.$pertot.'%</td></tr>';
echo '</table></div>';
echo '</div>';

//countries
echo '<div style="clear:both;">';
echo '<script type="text/javascript">';
echo 'google.setOnLoadCallback(drawHitsByC);'."\n";
echo 'function drawHitsByC() {'."\n";
echo 'var datac = new google.visualization.DataTable();'."\n";
echo 'datac.addColumn(\'string\',\'Video\');'."\n";
echo 'datac.addColumn(\'number\',\'Hits\');'."\n";
echo 'datac.addRows('.count($countries).');'."\n";
$row=0;
$hitstotal=0;
$pertot=0;
foreach ($countries as $key=>$value) {
	echo 'datac.setValue('.$row.',0,\'';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '\');'."\n";
	echo 'datac.setValue('.$row.',1,'.$value.');'."\n";
	$row++;
}
echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbc\'));'."\n";
echo 'chart'.$ct->cat_id.'.draw(datac, {title: \'Countries\', width: 400, height: 300, is3D: true});'."\n";
echo '}'."\n";
echo '</script>';
echo '<div id="chart_hbc" style="float:left;"></div>';
echo '<div class="codelist-codes" style="width:280;float:left;">'; 
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr>';
echo '<th width="180">Country</th><th width="100" colspan="2">Hits</th></tr>';
foreach ($countries as $key=>$value) { 
	echo '<tr>';
	echo '<td>';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '&nbsp;</td>';
	echo '<td align="right">'.$value.'</td>';
	$per = round((($value/$ctrytotal)*100),1);//,PHP_ROUND_HALF_UP
	$pertot = $pertot+$per;
	echo '<td align="right">'.$per.'%</td>';
	echo '</tr>';
} 
echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$ctrytotal.'</td><td align="right">'.$pertot.'%</td></tr>';
echo '</table></div>';
echo '</div>';

//timezones
echo '<div style="clear:both;">';
echo '<script type="text/javascript">';
echo 'google.setOnLoadCallback(drawHitsByZ);'."\n";
echo 'function drawHitsByZ() {'."\n";
echo 'var dataz = new google.visualization.DataTable();'."\n";
echo 'dataz.addColumn(\'string\',\'Video\');'."\n";
echo 'dataz.addColumn(\'number\',\'Hits\');'."\n";
echo 'dataz.addRows('.count($timezones).');'."\n";
$row=0;
$hitstotal=0;
$pertot=0;
foreach ($timezones as $key=>$value) {
	echo 'dataz.setValue('.$row.',0,\'';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '\');'."\n";
	echo 'dataz.setValue('.$row.',1,'.$value.');'."\n";
	$row++;
}
echo 'var chart'.$ct->cat_id.' = new google.visualization.ImagePieChart(document.getElementById(\'chart_hbz\'));'."\n";
echo 'chart'.$ct->cat_id.'.draw(dataz, {title: \'Timezones\', width: 400, height: 300, is3D: true});'."\n";
echo '}'."\n";
echo '</script>';
echo '<div id="chart_hbz" style="float:left;"></div>';
echo '<div class="codelist-codes" style="width:280;float:left;">'; 
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr>';
echo '<th width="180">Timezone</th><th width="100" colspan="2">Hits</th></tr>';
foreach ($timezones as $key=>$value) { 
	echo '<tr>';
	echo '<td>';
	if ($key) echo $key;
	else echo 'Unknown';
	echo '&nbsp;</td>';
	echo '<td align="right">'.$value.'</td>';
	$per = round((($value/$tztotal)*100),1);//,PHP_ROUND_HALF_UP
	$pertot = $pertot+$per;
	echo '<td align="right">'.$per.'%</td>';
	echo '</tr>';
} 
echo '<tr><td align="right"><b>Total:</b></td><td align="right">'.$tztotal.'</td><td align="right">'.$pertot.'%</td></tr>';
echo '</table></div>';
echo '</div>';

echo '</div>';


echo '<div style="clear:both">';
echo '<hr size="1">';
echo '<h3>Locations</h3>';
//the map
echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> '."\n";
echo '<script type="text/javascript">'."\n"; 
echo 'google.setOnLoadCallback(initMap);'."\n";
echo 'function initMap() { '."\n";
echo 'var myLatlng = new google.maps.LatLng(41,-70);'."\n";
echo 'var myOptions = {zoom: 4,center: myLatlng, mapTypeId: google.maps.MapTypeId.ROADMAP};'."\n";
echo 'var map = new google.maps.Map(document.getElementById("theMap"), myOptions);'."\n";
    
$count=0; 

foreach ($vids as $cl) {
	foreach ($cl->cats as $ct) {
		if ($ct->vids) {
			foreach ($ct->vids as $cd) {
				foreach ($cd->coords as $h) {
					if ($h->hit_lat) {
						echo 'var latlong'.$count.' = new google.maps.LatLng('.$h->hit_lat.','.$h->hit_long.');'."\n";
						echo 'var marker'.$count.' = new google.maps.Marker({position: latlong'.$count.', map: map, title:"'.$cd->vid_title.': '.$h->hits.'"});'."\n";
					}
					$count++;
				}
			}
		}
	}
}

echo '  }'."\n";
echo '</script>'."\n";

echo '<div id="theMap" style="clear:both;width:100%; height: 700px;"></div>'; 



echo '</div>';

?>
<script type="text/javascript">
window.addEvent('domready', function() { 
	var zebraTables = new ZebraTable({
    	elements: 'table.codelist-table',
    	cssEven: 'clt-even',
    	cssOdd: 'clt-odd',
    	cssHighlight: 'clt-highlight',
    	cssMouseEnter: 'clt-mo'
	});
});
</script>
<!-- 

http://code.google.com/apis/visualization/documentation/gallery/annotatedtimeline.html
http://code.google.com/apis/ajax/playground/?type=visualization#annotated_time_line
 -->