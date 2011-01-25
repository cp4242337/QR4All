<?php
class CodeList {
	var $db;
	
	function CodeList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Codes'; break;
			case 'codeadd': $title='Add Code'; break;
			case 'codeedit': $title='Edit Code'; break;
			case 'gencodes': $title='Generated Codes'; break;
			case 'showstats': $title='Stats'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'codeadd':
			case 'codeedit':
			case 'gencodes':
			case 'showstats':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}

	function getSubMenu($task='display') {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			echo '<li><a href="#" onclick="allTask(\'stats\');">Code Stats</a></li>';
			echo '<li><a href="#" onclick="allTask(\'getcodes\');">View Codes</a></li>';
			if ($user->lvl > 1) {
				echo '<li><a href="index.php?mod=codelist&task=addcode">Add Code</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
			}
			if ($user->lvl > 2) {
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
		}
		if ($task == 'codeadd' || $task == 'codeedit') {
			if ($user->lvl > 1) echo '<li><a href="index.php?mod=codelist">Cancel</a></li>';
			if ($user->lvl > 1) echo '<li><a href="#" onclick="document.codeform.validate();">Save Code</a></li>';
		}
		if ($task=="gencodes" || $task=='showstats') {
			echo '<li><a href="index.php?mod=codelist">Codes</a></li>';
		}
		if ($task == 'showstats') {
			
			echo '<li><a href="index.php?mod=codelist&task=getexcel&codes='.JRequest::getVar('codes').'&st_sdate='.JRequest::getVar('st_sdate',date("Y-m-d", strtotime("-1 months"))).'&st_edate='.JRequest::getVar('st_edate',date("Y-m-d")).'">Export to Excel</a></li>';
		}
		echo '</ul>';
		
	}
	
	function display() {
		global $user;
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user->id,$user->lvl);
		$codes=$this->getCodeList($clients,$curclient,$user->lvl);
		include 'mods/codelist/default.php';

	}
	
	function saveCode() {
		global $app;
		$code_id=JRequest::getInt('code_id',0);
		$code_name=JRequest::getString('code_name');
		$code_type=JRequest::getString('code_type');
		$code_url=JRequest::getVar('code_url');
		$code_cat=JRequest::getInt('code_cat');
		if ($code_id == 0) {
			$code_code=$this->gen_uuid();
			$q = 'INSERT INTO qr4_codes (cd_code,cd_name,cd_url,cd_type) VALUES ("'.$code_code.'","'.$code_name.'","'.$code_url.'","'.$code_type.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
			$code_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_codes SET cd_name="'.$code_name.'",cd_type="'.$code_type.'",cd_url="'.$code_url.'" WHERE cd_id = '.$code_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		$code_client=$this->getClientIdFromCat($code_cat);
		$qd1 = 'DELETE FROM qr4_catcodes WHERE catcd_code = '.$code_id;
		$this->db->setQuery($qd1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		$qd2 = 'DELETE FROM qr4_clientcodes WHERE clcd_code = '.$code_id;
		$this->db->setQuery($qd2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		$qi1 = 'INSERT INTO qr4_catcodes (catcd_cat,catcd_code) VALUES ('.$code_cat.','.$code_id.')';
		$this->db->setQuery($qi1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		$qi2 = 'INSERT INTO qr4_clientcodes (clcd_client,clcd_code) VALUES ('.$code_client.','.$code_id.')';
		$this->db->setQuery($qi2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		$app->setError('Code Saved', 'message');
		$app->setRedirect('codelist'); 
		$app->redirect();
		
	}
	
	function getClientIdFromCat($catid) {
		$q='SELECT clcat_client FROM qr4_clientcats WHERE clcat_cat = '.$catid;
		$this->db->setQuery($q);
		return $this->db->loadResult();
	}
	
	function gen_uuid($len=8) {
	    $hex = md5("in_the_beginning_there_were_qr_codes" . uniqid("", true));
		$pack = pack('H*', $hex);
	    $uid = base64_encode($pack);        // max 22 chars
	    $nuid = preg_replace("/[^a-zA-Z0-9]/", "",$uid);    // uppercase only
	    if ($len<4) $len=4;
	    if ($len>128) $len=128;                       // prevent silliness, can remove
	    while (strlen($nuid)<$len)
	        $nuid = $nuid . gen_uuid(22);     // append until length achieved
	    return substr($nuid, 0, $len);
	}
	function getExcel() {
		global $user;
		ini_set('memory_limit', '1024M');
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$cids = urldecode(JRequest::getVar('codes'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user->id,$user->lvl);
		$codes=$this->getCodeList($clients,$curclient,$user->lvl,$cids,$sdate,$edate);
		$data=$this->getHits($codes,$cids,$sdate,$edate);
		$filename = "website_data_" . date('Y-m-d') . ".xls";

		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel");
		
		$flag = false;
		foreach($data as $row) {
			if(!$flag) {
				# display field/column names as first row
				echo implode("\t", array_keys($row)) . "\n";
				$flag = true;
			}
			$this->cleanRow($row);
			echo implode("\t", array_values($row)) . "\n";
		}
		exit;
		
	}
	function cleanRow(&$row)
	{
		foreach ($row as &$r) {
			$r = preg_replace("/\t/", "\\t", $r);
			$r = preg_replace("/\r?\n/", "\\n", $r);
			if(strstr($r, '"')) $r = '"' . str_replace('"', '""', $r) . '"';
		}
	}
	function getcodes() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('codelist','gencodes','&codes='.urlencode($cids));
		$app->redirect();
	}
	
	function gencodes() {
		global $user;
		$cids = urldecode(JRequest::getVar('codes'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user->id,$user->lvl);
		$codes=$this->getCodeList($clients,$curclient,$user->lvl,$cids);
		include 'mods/codelist/gencodes.php';
		
	}
	
	function stats() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('codelist','showstats','&codes='.urlencode($cids));
		$app->redirect();
		
	}
	
	function codeAdd() {
		global $user;
		$clients = $this->getClientList($user->id,$user->lvl);
		$cats = $this->getClientCats($clients);
		include 'mods/codelist/codeform.php';
	}
	function codeEdit() {
		global $user;
		$clients = $this->getClientList($user->id,$user->lvl);
		$cats = $this->getClientCats($clients);
		$codeinfo=$this->getCodeInfo(JRequest::getInt('code',0));
		include 'mods/codelist/codeform.php';
	}
	
	function addcode() {
		global $app;
		$app->setRedirect('codelist','codeadd');
		$app->redirect();
	}
	function editcode() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		$app->setRedirect('codelist','codeedit','&code='.(int)$cids[0]);
		$app->redirect();
	}
	
	function showstats() {
		global $user;
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$cids = urldecode(JRequest::getVar('codes'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user->id,$user->lvl);
		$codes=$this->getCodeList($clients,$curclient,$user->lvl,$cids,$sdate,$edate);
		$stats=$this->getStats($codes,$sdate,$edate);
		include 'mods/codelist/showstats.php';
		
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_codes SET published = 0 WHERE cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('codelist');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_codes SET published = 1 WHERE cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('codelist');
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_codes SET trashed = 0 WHERE cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('codelist');
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_codes SET trashed = 1 WHERE cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('codelist');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'code', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_codes WHERE trashed = 1 && cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('codelist');
			$app->redirect();
		}
	}
	function getCodeInfo($code) {
		$q = 'SELECT * FROM qr4_codes WHERE cd_id = '.$code;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		$q2 = 'SELECT catcd_cat FROM qr4_catcodes WHERE catcd_code = '.$code;
		$this->db->setQuery($q2); 
		$info->cd_cat = $this->db->loadResult();
		return $info;
	}
	
	function getClientList($uid,$ulvl) {
		$q  = 'SELECT * FROM qr4_usersclients as uc ';
		$q .= 'RIGHT JOIN qr4_clients as cl ON uc.cu_client=cl.cl_id ';
		$q .= 'WHERE cl.published = 1 ';
		if ($ulvl == "1") $q .= ' && cu_user = '.$uid.' ';
		$q .= 'GROUP BY cl.cl_id ';
		$q .= 'ORDER BY cl.cl_name ';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}
	
	function getClientCats($clients) {
		$cats = Array();
		foreach ($clients as $cl) {	
			if ($curclient == $cl->cl_id || !$curclient) {
				$q  = 'SELECT * FROM qr4_clientcats as cc ';
				$q .= 'RIGHT JOIN qr4_cats as ct ON cc.clcat_cat = ct.cat_id ';
				$q .= 'WHERE ct.published = 1 && cc.clcat_client = '.$cl->cl_id;
				$this->db->setQuery($q);
				$cl->cats = $this->db->loadObjectList();
				$cats[] = $cl;
			}
		}
		return $cats;
	}
	
	function getCodeList($clients,$curclient,$ulvl,$cids=array(),$sdate=null,$edate=null) {
		$codes = Array();
		foreach ($clients as $cl) {	
			if ($curclient == $cl->cl_id || !$curclient) {
				$q  = 'SELECT * FROM qr4_clientcats as cc ';
				$q .= 'RIGHT JOIN qr4_cats as ct ON cc.clcat_cat = ct.cat_id ';
				$q .= 'WHERE ct.published = 1 && cc.clcat_client = '.$cl->cl_id;
				$this->db->setQuery($q);
				$cats = $this->db->loadObjectList();
				foreach ($cats as &$ct) {
					$q2  = 'SELECT * FROM qr4_catcodes as cc ';
					$q2 .= 'RIGHT JOIN qr4_codes as cd ON cc.catcd_code = cd.cd_id ';
					$q2 .= 'WHERE cc.catcd_cat = '.$ct->clcat_cat;
					if ($ulvl == 1) $q2 .= ' && cd.published = 1';
					if (count($cids)) $q2 .= ' && cd.cd_id IN ('.$cids.')';
					$this->db->setQuery($q2); 
					$codel = $this->db->loadObjectList();
					foreach ($codel as &$cd) {
						$q3  = 'SELECT COUNT(*) FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
						if ($sdate && $edate) $q3 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
						$q3 .= ' GROUP BY hit_code';
						$this->db->setQuery($q3); $cd->hits = $this->db->loadResult(); if (!$cd->hits) $cd->hits=0;
					}
					$ct->codes = $codel;
				}
				$cl->cats = $cats;
				$codes[] = $cl;
			}
		}
		return $codes;
		
	}
	
	function getStats($codes,$sdate=null,$edate=null) {
		foreach ($codes as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->codes as &$cd) {
					$q4  = 'SELECT date(h.hit_time) as date,count(*) as hits '; 
					$q4 .= 'FROM qr4_hits as h, qr4_codes as c '; 
					$q4 .= 'WHERE c.cd_id=h.hit_code && c.cd_id = '.$cd->cd_id.' ';
					if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
					$q4 .= 'GROUP BY c.cd_id,date(h.hit_time)';
					$this->db->setQuery($q4); $thits = $this->db->loadObjectList();
					$hits = Array();
					foreach ($thits as $h) {
						$hits[$h->date] = $h->hits;
					}
					$cd->dhits = $hits;
					
					//Browsers by Code
					$q5  = 'SELECT CONCAT(hit_browser," ",hit_browserver) as hit_browser,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $q5 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q5 .= ' GROUP BY CONCAT(hit_browser," ",hit_browserver)';
					$this->db->setQuery($q5); 
					$cd->browsers = $this->db->loadObjectList();
					
					//Platforms by Code
					$q6  = 'SELECT hit_platform,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $q6 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q6 .= ' GROUP BY hit_platform';
					$this->db->setQuery($q6); 
					$cd->platforms = $this->db->loadObjectList();
					
					//ismobile by Code
					$q7  = 'SELECT hit_ismobile,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $q7 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q7 .= ' GROUP BY hit_ismobile';
					$this->db->setQuery($q7); 
					$cd->ismobile = $this->db->loadObjectList();
					
					//Countries by Code
					$q8  = 'SELECT hit_country,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $q8 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q8 .= ' GROUP BY hit_country';
					$this->db->setQuery($q8); 
					$cd->countries = $this->db->loadObjectList();
					
					//Timezones by Code
					$q9  = 'SELECT hit_timezone,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $q9 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q9 .= ' GROUP BY hit_timezone';
					$this->db->setQuery($q9); 
					$cd->timezones = $this->db->loadObjectList();
					
					//Coordinates by Code
					$qA  = 'SELECT concat(hit_lat,", ",hit_long) as coord,hit_lat,hit_long,hit_city,hit_region,hit_country,COUNT(*) as hits FROM qr4_hits WHERE hit_code = '.$cd->cd_id;
					if ($sdate && $edate) $qA .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$qA .= ' GROUP BY coord';
					$this->db->setQuery($qA); 
					$cd->coords = $this->db->loadObjectList();
				}
			}
		}
		return $codes;
	}
	
	function getHits($codes,$cids,$sdate=null,$edate=null) {
		$usecodes = array();
		foreach ($codes as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->codes as $cd) {
					if (in_array($cd->cd_id,explode(',',$cids))) $usecodes[] = $cd->cd_id;
				}
			}
		}
		$q4  = 'SELECT c.cd_name as code, c.cd_type as codetype, h.hit_time as timehit, CONCAT(h.hit_browser," ",h.hit_browserver) as browser, ';
		$q4 .= 'h.hit_platform as platform, h.hit_ismobile as ismobile, h.hit_ipaddr as ipaddress, '; 
		$q4 .= 'h.hit_city,h.hit_region,h.hit_country,h.hit_timezone ';
		$q4 .= 'FROM qr4_hits as h, qr4_codes as c '; 
		$q4 .= 'WHERE c.cd_id=h.hit_code && c.cd_id IN ('.implode(',',$usecodes).') ';
		if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
		$this->db->setQuery($q4); $thits = $this->db->loadAssocList();
		return $thits;
	}
	
}