<?php


?>


<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<p><a href="givecsv.php?v=stats">Download CSV of All Data</a></p>
<form action="" method="post">
	Year <select name="data_year">
	<?php 	
	for ($i=2010; $i<=2011; $i++) {
		echo '<option value="'.$i.'"';
		if ($this->year == $i) echo ' SELECTED';
		echo '>'.$i.'</option>';
	}
	?>
	</select>
	Month <select name="data_month">
	<?php 	
	for ($i=1; $i<=12; $i++) {
		echo '<option value="'.$i.'"';
		if ($this->month == $i) echo ' SELECTED';
		echo '>'.$i.'</option>';
	}
	?>
	</select>
	Show As <select name="data_style">
		<option value="value" <?php if ($this->style == "value") echo ' SELECTED'; ?>>Hits by Count</option>
		<option value="percentage" <?php if ($this->style == "percentage") echo ' SELECTED'; ?>>Hits by %</option>
	</select>
	<input type="submit" value="Go">
</form>
<div align="center">
	<script type="text/javascript">
		google.load('visualization', '1', {'packages':['corechart']});
		google.setOnLoadCallback(drawChartT);
		google.setOnLoadCallback(drawChartR);
		google.setOnLoadCallback(drawChartV);
		 
		function drawChartR() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Campaign');
			data.addColumn('number', 'Count');
			data.addRows([
				<?php
				$first=true;
			  	if (mysql_num_rows($this->rqr) < 1) echo "['No Data',0]";
				while($d=mysql_fetch_assoc($this->rqr)) {
				  	if (!$first) echo ",";
					else $first = false;
					echo "['".$d['ent_campaign']."', ".$d['hits']."]";
			
				}
				?>
			]);
				
			var chartR = new google.visualization.PieChart(document.getElementById('chart_divR'));
			chartR.draw(data, {height: 400,width:700, is3D: true, legend: 'right',legendFontSize: 11, title: 'Article',pieSliceText: '<?php echo $this->style; ?>'});
		}  
		function drawChartT() {
	
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Campaign');
			data.addColumn('number', 'Count');
			data.addRows([
			  	<?php
				$first=true;
				if (mysql_num_rows($this->tqr) < 1) echo "['No Data',0]";
				while($d=mysql_fetch_assoc($this->tqr)) {
					if (!$first) echo ",";
					else $first = false;
					echo "['".$d['ent_campaign']."', ".$d['hits']."]";
				}
				?>
			]);
				
			var chartT = new google.visualization.PieChart(document.getElementById('chart_divT'));
			chartT.draw(data, {height: 400,width:700, is3D: true, legend: 'right',legendFontSize: 11, title: 'Patient Handout',pieSliceText: '<?php echo $this->style; ?>'});
		}  
		function drawChartV() {
	
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Campaign');
			data.addColumn('number', 'Count');
			data.addRows([
			  	<?php 
			 	$first=true;
				if (mysql_num_rows($this->vqr) < 1) echo "['No Data',0]";
				while($d=mysql_fetch_assoc($this->vqr)) {
				  	if (!$first) echo ",";
					else $first = false;
					echo "['".$d['ent_campaign']."', ".$d['hits']."]";
				}
				?>
			]);
				
			var chartV = new google.visualization.PieChart(document.getElementById('chart_divV'));
			chartV.draw(data, {height: 400,width:700, is3D: true, legend: 'right',legendFontSize: 11, title: 'Video',pieSliceText: '<?php echo $this->style; ?>'});
		}
	</script>

	<div id="chart_divR"></div>
	<div id="chart_divT"></div>
	<div id="chart_divV"></div>
		
	
</div>