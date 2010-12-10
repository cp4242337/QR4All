<?php
class Hits {
	function Hits() {
		
	}
	
	function getTitle() {
		return 'Hits';
	}
	
	function display($view='hits') {
		$this->year = $_POST['data_year']; if (!$year) $this->year=date("Y");
		$this->month = $_POST['data_month']; if (!$month) $this->month=date("n");
		$this->style = $_POST['data_style']; if (!$style) $this->style="value";
		
		
		$rq='SELECT ent_campaign,count(*) as hits FROM qr4_hits WHERE YEAR(ent_time) = '.$this->year.' && MONTH(ent_time) = '.$this->month.' && ent_what = "review" GROUP BY ent_campaign';
		$this->rqr = mysql_query($rq);
		
		$tq='SELECT ent_campaign,count(*) as hits FROM qr4_hits WHERE YEAR(ent_time) = '.$this->year.' && MONTH(ent_time) = '.$this->month.' && ent_what = "tool" GROUP BY ent_campaign';
		$this->tqr = mysql_query($tq); 
		
		$vq='SELECT ent_campaign,count(*) as hits FROM qr4_hits WHERE YEAR(ent_time) = '.$this->year.' && MONTH(ent_time) = '.$this->month.' && ent_what = "video" GROUP BY ent_campaign';
		$this->vqr = mysql_query($vq); 
		
		include 'mods/hits/default.php';
	}
	
}