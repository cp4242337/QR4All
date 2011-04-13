
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

echo '<h3>Survey Data</h3>';
echo '<table width="100%" cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr><th>Started</th><th>Ended</th><th>Elapsed Time (secs)</th>';
foreach ($items as $i) {
	echo '<th>'.$i->item_title.'</th>';
}
echo '</tr>';
foreach ($data as $d) {
	echo '<tr>';
	echo '<td>'.$d->data_start.'</td>';
	echo '<td>'.$d->data_end.'</td>';
	if ($d->data_end != '0000-00-00 00:00:00') echo '<td>'.(strtotime($d->data_end)-strtotime($d->data_start)).'</td>';
	else echo '<td>Incomplete</td>';
	foreach ($items as $i) {
		$ans = 'i'.$i->item_id;
		echo '<td>'.$d->$ans.'</td>';
	}
	echo '</tr>';
}
echo '</table>';
				
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