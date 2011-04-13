<?php
class FormList {
	var $db;
	
	function FormList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Froms'; break;
			case 'formadd': $title='Add Form'; break;
			case 'formedit': $title='Edit Form'; break;
			case 'showstats': $title='Stats'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'formadd':
			case 'formedit':
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
			echo '<li><a href="#" onclick="allTask(\'stats\');">Form Stats</a></li>';
			if ($user->lvl_edit) {
				echo '<li><a href="index.php?mod=formlist&task=addform">Add Form</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
			}
			if ($user->lvl_admin) {
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
		}
		if ($task == 'formadd' || $task == 'formedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=formlist">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Form</a></li>';
		}
		if ($task=='showstats') {
			echo '<li><a href="index.php?mod=formlist">Forms</a></li>';
			echo '<li><a href="index.php?mod=formlist&task=getexcel&vids='.JRequest::getVar('vids').'&st_sdate='.JRequest::getVar('st_sdate',date("Y-m-d", strtotime("-1 months"))).'&st_edate='.JRequest::getVar('st_edate',date("Y-m-d")).'">Export to Excel</a></li>';
		}
		echo '</ul>';
		
	}
	
	function display() {
		global $user;
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$forms=$this->getFormList($clients,$curclient,$user);
		include 'mods/formlist/default.php';

	}
	
	function saveForm() {
		global $app;
		$form_id=JRequest::getInt('form_id',0);
		$form_title=JRequest::getString('form_title');
		$form_cat=JRequest::getInt('form_cat');
		$form_pubtitle=JRequest::getString('form_pubtitle');
		$form_tmpl=JRequest::getInt('form_tmpl');
		$form_domain=JRequest::getInt('form_domain');
		$form_client=$this->getClientIdFromCat($form_cat);
		
		if (!$this->CheckFormCount($form_client)) {
			$app->setError("Maximum number of forms reached for client","error");
			$app->setRedirect('codelist');
			$app->redirect();
			return 0;
		}
		
		if ($form_id == 0) {
			$form_code=$this->gen_uuid();
			$q = 'INSERT INTO qr4_forms (form_code,form_title,form_publictitle,form_template,form_domain) VALUES ("'.$form_code.'","'.$form_title.'","'.$form_pubtitle.'","'.$form_tmpl.'","'.$form_domain.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			$form_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_forms SET form_title="'.$form_title.'", form_publictitle="'.$form_pubtitle.'", form_template="'.$form_tmpl.'", form_domain="'.$form_domain.'" WHERE form_id = '.$form_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		}
		
		$qd1 = 'DELETE FROM qr4_catforms WHERE catform_form = '.$form_id;
		$this->db->setQuery($qd1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qd2 = 'DELETE FROM qr4_clientforms WHERE clform_form = '.$vid_id;
		$this->db->setQuery($qd2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qi1 = 'INSERT INTO qr4_catforms (catform_cat,catform_form) VALUES ('.$form_cat.','.$form_id.')';
		$this->db->setQuery($qi1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qi2 = 'INSERT INTO qr4_clientforms (clform_client,clform_form) VALUES ('.$form_client.','.$form_id.')';
		$this->db->setQuery($qi2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$app->setError('Form Saved', 'message');
		$app->setRedirect('formlist'); 
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
		$filename = "form_stat_data_" . date('Y-m-d') . ".xls";

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
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('formlist','showstats','&forms='.urlencode($cids));
		$app->redirect();
		
	}	
	
	function pages() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('pagelist','display','&form='.urlencode($cids));
		$app->redirect();
		
	}
	
	function formAdd() {
		global $user;
		$uc=JRequest::getInt('useclient');
		$clients = $this->getClientList($user,$uc);
		$cats = $this->getClientCats($clients);
		$tmpls = $this->getTmplList();
		$doms = $this->getDomainList();
		include 'mods/formlist/formform.php';
	}
	function formEdit() {
		global $user;
		$clients = $this->getClientList($user);
		$cats = $this->getClientCats($clients);
		$forminfo=$this->getFormInfo(JRequest::getInt('form',0));
		$tmpls = $this->getTmplList();
		$doms = $this->getDomainList();
		include 'mods/formlist/formform.php';
	}
	
	function addform() {
		global $app;
		$cl=JRequest::getInt('client');
		$clurl='';
		if ($this->CheckFormCount($cl)) {
			if ($cl) { $clurl = '&useclient='.$cl;}
			$app->setRedirect('formlist','formadd',$clurl);
		} else {
			$app->setError("Maximum number of Forms Reached","error");
			$app->setRedirect('formlist');
		}
		$app->redirect();
	}
	function editform() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		$app->setRedirect('formlist','formedit','&form='.(int)$cids[0]);
		$app->redirect();
	}
	
	function showstats() {
		global $user;
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$cids = urldecode(JRequest::getVar('forms'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$forms=$this->getFormList($clients,$curclient,$user,$cids,$sdate,$edate);
		$stats=$this->getStats($forms,$sdate,$edate);
		include 'mods/formlist/showstats.php';
		
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_forms SET published = 0 WHERE form_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Form(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('formlist');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_forms SET published = 1 WHERE form_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Form(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('formlist');
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_forms SET trashed = 0 WHERE form_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Form(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('formlist');
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_forms SET trashed = 1 WHERE form_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Form(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('formlist');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_forms WHERE trashed = 1 && form_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Form(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('formlist');
			$app->redirect();
		}
	}
	function getFormInfo($form) {
		$q = 'SELECT * FROM qr4_forms WHERE form_id = '.$form;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		$q2 = 'SELECT catform_cat FROM qr4_catforms WHERE catform_form = '.$form;
		$this->db->setQuery($q2); 
		$info->form_cat = $this->db->loadResult();
		return $info;
	}
	
	function getClientList($user,$clid=0) {
		$q  = 'SELECT * FROM qr4_usersclients as uc ';
		$q .= 'RIGHT JOIN qr4_clients as cl ON uc.cu_client=cl.cl_id ';
		$q .= 'WHERE cl.published = 1 ';
		if (!$user->lvl_admin) $q .= ' && cu_user = '.$user->id.' ';
		if ($clid) $q.=" && cl.cl_id = ".$clid.' ';
		$q .= 'GROUP BY cl.cl_id ';
		$q .= 'ORDER BY cl.cl_name ';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}
	
	function getTmplList() {
		$q  = 'SELECT * FROM qr4_templates ';
		$q .= 'WHERE tmpl_type = "form" ';
		$q .= 'ORDER BY tmpl_name ';
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
	
	function getFormList($clients,$curclient,$user,$cids=array(),$sdate=null,$edate=null) {
		$vids = Array();
		foreach ($clients as $cl) {	
			if ($curclient == $cl->cl_id || !$curclient) {
				$q  = 'SELECT * FROM qr4_clientcats as cc ';
				$q .= 'RIGHT JOIN qr4_cats as ct ON cc.clcat_cat = ct.cat_id ';
				$q .= 'WHERE ct.published = 1 && cc.clcat_client = '.$cl->cl_id;
				$this->db->setQuery($q);
				$cats = $this->db->loadObjectList();
				foreach ($cats as &$ct) {
					$q2  = 'SELECT * FROM qr4_catforms as cc ';
					$q2 .= 'RIGHT JOIN qr4_forms as cd ON cc.catform_form = cd.form_id ';
					$q2 .= 'RIGHT JOIN qr4_templates as vd ON vd.tmpl_id = cd.form_template ';
					$q2 .= 'RIGHT JOIN qr4_domains as dom ON dom.dom_id = cd.form_domain ';
					$q2 .= 'WHERE cc.catform_cat = '.$ct->clcat_cat;
					if (!$user->lvl_admin) $q2 .= ' && cd.trashed=0';
					if (count($cids)) $q2 .= ' && cd.form_id IN ('.$cids.')';
					$this->db->setQuery($q2); 
					$forml = $this->db->loadObjectList();
					foreach ($forml as &$cd) {
						$q3  = 'SELECT COUNT(*) FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
						if ($sdate && $edate) $q3 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
						$q3 .= ' GROUP BY hit_form';
						$this->db->setQuery($q3); $cd->hits = $this->db->loadResult(); if (!$cd->hits) $cd->hits=0;
						
						$q4  = 'SELECT COUNT(*) FROM qr4_formpages WHERE page_form = '.$cd->form_id;
						$q4 .= ' GROUP BY page_form';
						$this->db->setQuery($q4); $cd->pages = $this->db->loadResult(); if (!$cd->pages) $cd->pages=0;
					}
					$ct->forms = $forml;
				}
				$cl->cats = $cats;
				$vids[] = $cl;
			}
		}
		return $vids;
		
	}
	
	function getStats($forms,$sdate=null,$edate=null) {
		foreach ($forms as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->forms as &$cd) {
					$q4  = 'SELECT date(h.hit_time) as date,count(*) as hits '; 
					$q4 .= 'FROM qr4_fhits as h, qr4_forms as c '; 
					$q4 .= 'WHERE c.form_id=h.hit_form && c.form_id = '.$cd->form_id.' ';
					if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
					$q4 .= 'GROUP BY c.form_id,date(h.hit_time)';
					$this->db->setQuery($q4); $thits = $this->db->loadObjectList();
					$hits = Array();
					foreach ($thits as $h) {
						$hits[$h->date] = $h->hits;
					}
					$cd->dhits = $hits;
					
					//Browsers by Code
					$q5  = 'SELECT CONCAT(hit_browser," ",hit_browserver) as hit_browser,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $q5 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q5 .= ' GROUP BY CONCAT(hit_browser," ",hit_browserver)';
					$this->db->setQuery($q5); 
					$cd->browsers = $this->db->loadObjectList();
					
					//Platforms by Code
					$q6  = 'SELECT hit_platform,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $q6 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q6 .= ' GROUP BY hit_platform';
					$this->db->setQuery($q6); 
					$cd->platforms = $this->db->loadObjectList();
					
					//ismobile by Code
					$q7  = 'SELECT hit_ismobile,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $q7 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q7 .= ' GROUP BY hit_ismobile';
					$this->db->setQuery($q7); 
					$cd->ismobile = $this->db->loadObjectList();
					
					//Countries by Code
					$q8  = 'SELECT hit_country,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $q8 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q8 .= ' GROUP BY hit_country';
					$this->db->setQuery($q8); 
					$cd->countries = $this->db->loadObjectList();
					
					//Timezones by Code
					$q9  = 'SELECT hit_timezone,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $q9 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$q9 .= ' GROUP BY hit_timezone';
					$this->db->setQuery($q9); 
					$cd->timezones = $this->db->loadObjectList();
					
					//Coordinates by Code
					$qA  = 'SELECT concat(hit_lat,", ",hit_long) as coord,hit_lat,hit_long,hit_city,hit_region,hit_country,COUNT(*) as hits FROM qr4_fhits WHERE hit_form = '.$cd->form_id;
					if ($sdate && $edate) $qA .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" ';
					$qA .= ' GROUP BY coord';
					$this->db->setQuery($qA); 
					$cd->coords = $this->db->loadObjectList();
				}
			}
		}
		return $forms;
	}
	
	function getHits($forms,$cids,$sdate=null,$edate=null) {
		$useforms = array();
		foreach ($forms as $cl) {
			foreach ($cl->cats as $ct) {
				foreach ($ct->forms as $cd) {
					if (in_array($cd->form_id,explode(',',$cids))) $usevids[] = $cd->form_id;
				}
			}
		}
		$q4  = 'SELECT c.form_title as form, h.hit_time as timehit, CONCAT(h.hit_browser," ",h.hit_browserver) as browser, ';
		$q4 .= 'h.hit_platform as platform, h.hit_ismobile as ismobile, h.hit_ipaddr as ipaddress, '; 
		$q4 .= 'h.hit_city,h.hit_region,h.hit_country,h.hit_timezone ';
		$q4 .= 'FROM qr4_fhits as h, qr4_forms as c '; 
		$q4 .= 'WHERE c.form_id=h.hit_form && c.form_id IN ('.implode(',',$useforms).') ';
		if ($sdate && $edate) $q4 .= '&& date(hit_time) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
		$this->db->setQuery($q4); $thits = $this->db->loadAssocList();
		return $thits;
	}
	
	function checkFormCount($clid) {
		if (!$clid) return true;
		$q = 'SELECT cl_maxforms FROM qr4_clients WHERE cl_id='.$clid;
		$this->db->setQuery($q); $maxforms = $this->db->loadResult();
		if ($maxforms == 0) return true;
		if ($maxforms == -1) return false;
		$q2  = 'SELECT * FROM qr4_clientforms as cf ';
		$q2 .= 'LEFT JOIN qr4_forms as cd ON cf.clform_form = cd.form_id ';
		$q2 .= 'WHERE cf.clform_client = '.$clid;
		$this->db->setQuery($q2);
		$curforms = count($this->db->loadObjectList()); 
		if ($curforms < $maxforms) return true;
		else return false;
	}
	

	
	function getDomainList() {
		$q  = 'SELECT * FROM qr4_domains ';
		$q .= 'WHERE dom_type = "form" ';
		$q .= 'ORDER BY dom_dom ';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}
	
}