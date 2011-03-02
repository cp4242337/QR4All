<?php
class VidList {
	var $db;
	
	function VidList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Videos'; break;
			case 'vidadd': $title='Add Video'; break;
			case 'videdit': $title='Edit Video'; break;
			case 'showstats': $title='Stats'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'vidadd':
			case 'videdit':
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
			echo '<li><a href="#" onclick="allTask(\'stats\');">Vid Stats</a></li>';
			if ($user->lvl_edit) {
				echo '<li><a href="index.php?mod=vidlist&task=addvid">Add Vid</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
			}
			if ($user->lvl_admin) {
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
		}
		if ($task == 'vidadd' || $task == 'videdit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=vidlist">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Video</a></li>';
		}
		if ($task=='showstats') {
			echo '<li><a href="index.php?mod=vidlist">Videos</a></li>';
		}
		if ($task == 'showstats') {
			
			echo '<li><a href="index.php?mod=vidlist&task=getexcel&vids='.JRequest::getVar('vids').'&st_sdate='.JRequest::getVar('st_sdate',date("Y-m-d", strtotime("-1 months"))).'&st_edate='.JRequest::getVar('st_edate',date("Y-m-d")).'">Export to Excel</a></li>';
		}
		echo '</ul>';
		
	}
	
	function display() {
		global $user;
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$vids=$this->getVidList($clients,$curclient,$user);
		include 'mods/vidlist/default.php';

	}
	
	function saveVid() {
		global $app;
		$vid_id=JRequest::getInt('vid_id',0);
		$vid_title=JRequest::getString('vid_title');
		$vid_file=JRequest::getVar('vid_file');
		$vid_cat=JRequest::getInt('vid_cat');
		$vid_rat=JRequest::getInt('vid_rat');
		$vid_pubtitle=JRequest::getString('vid_pubtitle');
		$vid_domain=JRequest::getInt('vid_domain');
		$vid_client=$this->getClientIdFromCat($vid_cat);
		
		if (!$this->CheckVideoCount($vid_client)) {
			$app->setError("Maximum number of videos reached for client","error");
			$app->setRedirect('codelist');
			$app->redirect();
			return 0;
		}
		
		if ($vid_id == 0) {
			$vid_code=$this->gen_uuid();
			$q = 'INSERT INTO qr4_videos (vid_code,vid_title,vid_file,vid_ratio,vid_pubtitle,vid_domain) VALUES ("'.$vid_code.'","'.$vid_title.'","'.$vid_file.'","'.$vid_rat.'","'.$vid_pubtitle.'","'.$vid_domain.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
			$vid_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_videos SET vid_title="'.$vid_title.'",vid_file="'.$vid_file.'", vid_ratio = "'.$vid_rat.'", vid_pubtitle="'.$vid_pubtitle.'", vid_domain="'.$vid_domain.'" WHERE vid_id = '.$vid_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
		}
		$qd1 = 'DELETE FROM qr4_catvids WHERE catvid_vid = '.$vid_id;
		$this->db->setQuery($qd1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
		$qd2 = 'DELETE FROM qr4_clientvids WHERE clvid_vid = '.$vid_id;
		$this->db->setQuery($qd2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
		$qi1 = 'INSERT INTO qr4_catvids (catvid_cat,catvid_vid) VALUES ('.$vid_cat.','.$vid_id.')';
		$this->db->setQuery($qi1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
		$qi2 = 'INSERT INTO qr4_clientvids (clvid_client,clvid_vid) VALUES ('.$vid_client.','.$vid_id.')';
		$this->db->setQuery($qi2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('vidlist'); $app->redirect(); }
		$app->setError('Video Saved', 'message');
		$app->setRedirect('vidlist'); 
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
		$cids = urldecode(JRequest::getVar('vids'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$vids=$this->getVidList($clients,$curclient,$user,$cids,$sdate,$edate);
		$data=$this->getHits($vids,$cids,$sdate,$edate);
		$filename = "video_data_" . date('Y-m-d') . ".xls";

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
		
	function stats() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('vidlist','showstats','&vids='.urlencode($cids));
		$app->redirect();
		
	}
	
	function vidAdd() {
		global $user;
		$clients = $this->getClientList($user);
		$cats = $this->getClientCats($clients);
		$doms = $this->getDomainList();
		include 'mods/vidlist/vidform.php';
	}
	function vidEdit() {
		global $user;
		$uc=JRequest::getInt('useclient');
		$clients = $this->getClientList($user,$uc);
		$cats = $this->getClientCats($clients);
		$vidinfo=$this->getVidInfo(JRequest::getInt('vid',0));
		$doms = $this->getDomainList();
		include 'mods/vidlist/vidform.php';
	}
	
	function addvid() {
		global $app;
		$cl=JRequest::getInt('client');
		$clurl='';
		if ($this->CheckVideoCount($cl)) {
			if ($cl) { $clurl = '&useclient='.$cl;}
			$app->setRedirect('vidlist','vidadd',$clurl);
		} else {
			$app->setError("Maximum number of Videos Reached","error");
			$app->setRedirect('vidlist');
		}
		$app->redirect();
	}
	function editvid() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		$app->setRedirect('vidlist','videdit','&vid='.(int)$cids[0]);
		$app->redirect();
	}
	
	function showstats() {
		global $user;
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$cids = urldecode(JRequest::getVar('vids'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$vids=$this->getVidList($clients,$curclient,$user,$cids,$sdate,$edate);
		$stats=$this->getStats($vids,$sdate,$edate);
		include 'mods/vidlist/showstats.php';
		
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_videos SET published = 0 WHERE vid_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Video(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('vidlist');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_videos SET published = 1 WHERE vid_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Video(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('vidlist');
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_videos SET trashed = 0 WHERE vid_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Video(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('vidlist');
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_videos SET trashed = 1 WHERE vid_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Video(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('vidlist');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'vid', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_videos WHERE trashed = 1 && cd_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Video(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('vidlist');
			$app->redirect();
		}
	}
	function getVidInfo($vid) {
		$q = 'SELECT * FROM qr4_videos WHERE vid_id = '.$vid;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		$q2 = 'SELECT catvid_cat FROM qr4_catvids WHERE catvid_vid = '.$vid;
		$this->db->setQuery($q2); 
		$info->vid_cat = $this->db->loadResult();
		return $info;
	}
	
	function getClientList($user,$clid=0) {
		$q  = 'SELECT * FROM qr4_usersclients as uc ';
		$q .= 'RIGHT JOIN qr4_clients as cl ON uc.cu_client=cl.cl_id ';
		$q .= 'WHERE cl.published = 1 ';
		if (!$user->lvl_admin) $q .= ' && cu_user = '.$user->id.' ';
		if ($clid) $q.=' && cl.cl_id = '.$clid.' ';
		$q .= 'GROUP BY cl.cl_id ';
		$q .= 'ORDER BY cl.cl_name ';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}
	
	function getDomainList() {
		$q  = 'SELECT * FROM qr4_domains ';
		$q .= 'WHERE dom_type = "video" ';
		$q .= 'ORDER BY dom_dom ';
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
	
	function getVidList($clients,$curclient,$user,$cids=array(),$sdate=null,$edate=null) {
		$vids = Array();
		foreach ($clients as $cl) {	
			if ($curclient == $cl->cl_id || !$curclient) {
				$q  = 'SELECT * FROM qr4_clientcats as cc ';
				$q .= 'RIGHT JOIN qr4_cats as ct ON cc.clcat_cat = ct.cat_id ';
				$q .= 'WHERE ct.published = 1 && cc.clcat_client = '.$cl->cl_id;
				$this->db->setQuery($q);
				$cats = $this->db->loadObjectList();
				foreach ($cats as &$ct) {
					$q2  = 'SELECT * FROM qr4_catvids as cc ';
					$q2 .= 'RIGHT JOIN qr4_videos as cd ON cc.catvid_vid = cd.vid_id ';
					$q2 .= 'RIGHT JOIN qr4_domains as vd ON vd.dom_id = cd.vid_domain ';
					$q2 .= 'WHERE cc.catvid_cat = '.$ct->clcat_cat;
					if (!$user->lvl_admin) $q2 .= ' && cd.trashed = 0';
					if (count($cids)) $q2 .= ' && cd.vid_id IN ('.$cids.')';
					$this->db->setQuery($q2); 
					$vidl = $this->db->loadObjectList();
					foreach ($vidl as &$cd) {
						$q3  = 'SELECT COUNT(*) FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
						if ($sdate && $edate) $q3 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
						$q3 .= ' GROUP BY hit_vid';
						$this->db->setQuery($q3); $cd->hits = $this->db->loadResult(); if (!$cd->hits) $cd->hits=0;
					}
					$ct->vids = $vidl;
				}
				$cl->cats = $cats;
				$vids[] = $cl;
			}
		}
		return $vids;
		
	}
	
	function getStats($vids,$sdate=null,$edate=null) {
		foreach ($vids as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->vids as &$cd) {
					$q4  = 'SELECT date(h.hit_time) as date,count(*) as hits '; 
					$q4 .= 'FROM qr4_vhits as h, qr4_videos as c '; 
					$q4 .= 'WHERE c.vid_id=h.hit_vid && c.vid_id = '.$cd->vid_id.' ';
					if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
					$q4 .= 'GROUP BY c.vid_id,date(h.hit_time)';
					$this->db->setQuery($q4); $thits = $this->db->loadObjectList();
					$hits = Array();
					foreach ($thits as $h) {
						$hits[$h->date] = $h->hits;
					}
					$cd->dhits = $hits;
					
					//Browsers by Code
					$q5  = 'SELECT CONCAT(hit_browser," ",hit_browserver) as hit_browser,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $q5 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q5 .= ' GROUP BY CONCAT(hit_browser," ",hit_browserver)';
					$this->db->setQuery($q5); 
					$cd->browsers = $this->db->loadObjectList();
					
					//Platforms by Code
					$q6  = 'SELECT hit_platform,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $q6 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q6 .= ' GROUP BY hit_platform';
					$this->db->setQuery($q6); 
					$cd->platforms = $this->db->loadObjectList();
					
					//ismobile by Code
					$q7  = 'SELECT hit_ismobile,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $q7 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q7 .= ' GROUP BY hit_ismobile';
					$this->db->setQuery($q7); 
					$cd->ismobile = $this->db->loadObjectList();
					
					//Countries by Code
					$q8  = 'SELECT hit_country,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $q8 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q8 .= ' GROUP BY hit_country';
					$this->db->setQuery($q8); 
					$cd->countries = $this->db->loadObjectList();
					
					//Timezones by Code
					$q9  = 'SELECT hit_timezone,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $q9 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q9 .= ' GROUP BY hit_timezone';
					$this->db->setQuery($q9); 
					$cd->timezones = $this->db->loadObjectList();
					
					//Coordinates by Code
					$qA  = 'SELECT concat(hit_lat,", ",hit_long) as coord,hit_lat,hit_long,hit_city,hit_region,hit_country,COUNT(*) as hits FROM qr4_vhits WHERE hit_vid = '.$cd->vid_id;
					if ($sdate && $edate) $qA .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$qA .= ' GROUP BY coord';
					$this->db->setQuery($qA); 
					$cd->coords = $this->db->loadObjectList();
				}
			}
		}
		return $vids;
	}
	
	function getHits($vids,$cids,$sdate=null,$edate=null) {
		$usevids = array();
		foreach ($vids as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->vids as $cd) {
					if (in_array($cd->vid_id,explode(',',$cids))) $usevids[] = $cd->vid_id;
				}
			}
		}
		$q4  = 'SELECT c.vid_title as video, h.hit_time as timehit, CONCAT(h.hit_browser," ",h.hit_browserver) as browser, ';
		$q4 .= 'h.hit_platform as platform, h.hit_ismobile as ismobile, h.hit_ipaddr as ipaddress, '; 
		$q4 .= 'h.hit_city,h.hit_region,h.hit_country,h.hit_timezone ';
		$q4 .= 'FROM qr4_vhits as h, qr4_videos as c '; 
		$q4 .= 'WHERE c.vid_id=h.hit_vid && c.vid_id IN ('.implode(',',$usevids).') ';
		if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
		$this->db->setQuery($q4); $thits = $this->db->loadAssocList();
		return $thits;
	}
	
	function checkVideoCount($clid) {
		if (!$clid) return true;
		$q = 'SELECT cl_maxvids FROM qr4_clients WHERE cl_id='.$clid;
		$this->db->setQuery($q); $maxvids = $this->db->loadResult();
		if ($maxvids == 0) return true;
		if ($maxvids == -1) return false;
		$q2  = 'SELECT * FROM qr4_clientvids as cv ';
		$q2 .= 'LEFT JOIN qr4_videos as cd ON cv.clvid_vid = cd.vid_id ';
		$q2 .= 'WHERE cv.clvid_client = '.$clid;
		$this->db->setQuery($q2);
		$curvids = count($this->db->loadObjectList()); 
		if ($curvids < $maxvids) return true;
		else return false;
	}
	
}