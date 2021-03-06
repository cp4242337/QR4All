<?php
class FormList {
	var $db;
	
	function FormList() {
		global $dbc, $user, $app;
		$this->db =& JDatabase::getInstance($dbc);
		if ($user->type == 'exp' || $user->type == 'paid' || $user->type == 'trial') {
			$app->setError('Unauthorized Access', 'error');
			$app->setRedirect('home'); 
			$app->redirect();
		}
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Forms'; break;
			case 'formadd': $title='Add Form'; break;
			case 'formedit': $title='Edit Form'; break;
			case 'showstats': $title='Stats'; break;
			case 'showdata': $title='Data'; break;
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
			case 'showdata':
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
				echo '<li><a href="#" onclick="allTask(\'copyForm\');">Copy Form</a></li>';
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
			echo '<li><a href="index.php?mod=formlist&task=getexcel&forms='.JRequest::getVar('forms').'&st_sdate='.JRequest::getVar('st_sdate',date("Y-m-d", strtotime("-1 months"))).'&st_edate='.JRequest::getVar('st_edate',date("Y-m-d")).'">Export to Excel</a></li>';
		}
		if ($task=='showdata') {
			echo '<li><a href="index.php?mod=formlist">Forms</a></li>';
			echo '<li><a href="index.php?mod=formlist&task=dataexcel&form='.JRequest::getVar('form').'&st_sdate='.JRequest::getVar('st_sdate',date("Y-m-d", strtotime("-1 months"))).'&st_edate='.JRequest::getVar('st_edate',date("Y-m-d")).'">Export to Excel</a></li>';
		}
		echo '</ul>';
		
	}
	
	function display() {
		global $user;
		$session =& JFactory::getSession();
		$curclient=(int)$session->get('client',0);
		$curcat=(int)$session->get('cat',0);
		$clients = $this->getClientList($user);
		if ($curclient) $cats = $this->getCatList($curclient);
		$forms=$this->getFormList($clients,$curclient,$user);
		include 'mods/formlist/default.php';

	}
	
	function setVar() {
		global $app;
		$session =& JFactory::getSession();
		$session->set('client',JRequest::getInt('client',JSession::get('client',0)));
		$session->set('cat',JRequest::getInt('cat',JSession::get('cat',0)));
		$app->setRedirect('formlist'); 
		$app->redirect();
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
		$form_header= $this->db->getEscaped(JRequest::getVar( 'form_header', null, 'default', 'none', 2));
		$form_body= $this->db->getEscaped(JRequest::getVar( 'form_body', null, 'default', 'none', 2));
		$form_sessiontime=$this->db->getEscaped(JRequest::getInt('form_sessiontime',30));
		$form_password=JRequest::getVar('form_password',"");
		
		if (!$this->CheckFormCount($form_client)) {
			$app->setError("Maximum number of forms reached for client","error");
			$app->setRedirect('codelist');
			$app->redirect();
			return 0;
		}
		
		if ($form_id == 0) {
			$form_code=$this->gen_uuid();
			$q = 'INSERT INTO qr4_forms (form_code,form_title,form_publictitle,form_template,form_domain,form_header,form_body,form_sessiontime,form_password) ';
			$q.= 'VALUES ("'.$form_code.'","'.$form_title.'","'.$form_pubtitle.'","'.$form_tmpl.'","'.$form_domain.'","'.$form_header.'","'.$form_body.'","'.$form_sessiontime.'","'.md5($form_password).'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			$form_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_forms SET form_title="'.$form_title.'", form_publictitle="'.$form_pubtitle.'", form_template="'.$form_tmpl.'", form_domain="'.$form_domain.'", form_header="'.$form_header.'", form_body="'.$form_body.'", form_sessiontime="'.$form_sessiontime.'" WHERE form_id = '.$form_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			if ($form_password) {
				$qp = 'UPDATE qr4_forms SET form_password="'.md5($form_password).'" WHERE form_id = '.$form_id;
				$this->db->setQuery($qp); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			}
		}
		
		$qd1 = 'DELETE FROM qr4_catforms WHERE catform_form = '.$form_id;
		$this->db->setQuery($qd1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qd2 = 'DELETE FROM qr4_clientforms WHERE clform_form = '.$form_id;
		$this->db->setQuery($qd2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qi1 = 'INSERT INTO qr4_catforms (catform_cat,catform_form) VALUES ('.$form_cat.','.$form_id.')';
		$this->db->setQuery($qi1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$qi2 = 'INSERT INTO qr4_clientforms (clform_cl,clform_form) VALUES ('.$form_client.','.$form_id.')';
		$this->db->setQuery($qi2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		$app->setError('Form Saved', 'message');
		$app->setRedirect('formlist'); 
		$app->redirect();
		
	}
	
	function copyForm() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		foreach ($cids as $c) {
			$q = 'SELECT * FROM qr4_forms WHERE form_id = '.$c;
			$this->db->setQuery($q);
			$info = $this->db->loadObject();
			$form_code=$this->gen_uuid();
			
			$q = 'INSERT INTO qr4_forms (form_code,form_title,form_publictitle,form_template,form_domain,form_header,form_body,form_sessiontime,published,trashed) ';
			$q.= 'VALUES ("'.$form_code.'","'.$info->form_title.' COPY","'.$info->form_publictitle.'","'.$info->form_template.'","'.$info->form_domain.'","'.$this->db->getEscaped($info->form_header).'","'.$this->db->getEscaped($info->form_body).'","'.$info->form_sessiontime.'","'.$info->published.'","'.$info->trashed.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			$form_id=$this->db->insertid();
			
			$qd1 = 'SELECT * FROM qr4_catforms WHERE catform_form = '.$info->form_id;
			$this->db->setQuery($qd1); $cf = $this->db->loadObject();
			$qi1 = 'INSERT INTO qr4_catforms (catform_cat,catform_form) VALUES ('.$cf->catform_cat.','.$form_id.')';
			$this->db->setQuery($qi1); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
			$qd2 = 'SELECT * FROM qr4_clientforms WHERE clform_form = '.$info->form_id;
			$this->db->setQuery($qd1); $cl = $this->db->loadObject();
			$qi2 = 'INSERT INTO qr4_clientforms (clform_cl,clform_form) VALUES ('.$cl->clform_cl.','.$form_id.')';
			$this->db->setQuery($qi2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist'); $app->redirect(); }
		
			
			$qp = "SELECT * FROM qr4_formpages WHERE page_form = ".$info->form_id. ' ORDER BY ordering';
			$this->db->setQuery($qp);
			$pages = $this->db->loadObjectList();
			
			foreach ($pages as $p) {
				$q='SELECT ordering FROM qr4_formpages WHERE page_form = '.$form_id.' ORDER BY ordering DESC LIMIT 1';
				$this->db->setQuery($q);
				$on = (int)$this->db->loadResult();
				if ($on) $orderingp = ($on+1);
				else $orderingp = 1;
						
				$q = 'INSERT INTO qr4_formpages (page_form,page_title,page_type,page_action,page_actiontext,page_reset,page_resettext,page_redirurl,ordering,page_content,page_qa,published,trashed) ';
				$q.= 'VALUES ("'.$form_id.'","'.$p->page_title.'","'.$p->page_type.'","'.$p->page_action.'","'.$p->page_actiontext.'","'.$p->page_reset.'","'.$p->page_resettext.'","'.$p->page_redirurl.'","'.$orderingp.'","'.$this->db->getEscaped($p->page_content).'","'.$p->page_qa.'","'.$p->published.'","'.$p->trashed.'")';
				$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('formlist','display',''); $app->redirect(); }
				$page_id=$this->db->insertid();
				
				
				$qi = 'SELECT * FROM qr4_formitems WHERE item_page = '.$p->page_id. ' ORDER BY ordering';
				$this->db->setQuery($qi);
				$items = $this->db->loadObjectList();
				
				foreach ($items as $i) {
					$q='SELECT ordering FROM qr4_formitems WHERE item_page = '.$page_id.' ORDER BY ordering DESC LIMIT 1';
					$this->db->setQuery($q);
					$on = (int)$this->db->loadResult();
					if ($on) $orderingi = ($on+1);
					else $orderingi = 1;
	
					$q  = 'INSERT INTO qr4_formitems (item_page,item_title,item_text,item_hint,item_type,item_req,item_confirm,item_verify,item_verify_limit,item_verify_msg,item_depend_item,item_match_item,ordering,published) ';
					$q .= 'VALUES ("'.$page_id.'","'.$i->item_title.'","'.$this->db->getEscaped($i->item_text).'","'.$this->db->getEscaped($i->i_hint).'","'.$i->item_type.'","'.$i->i_req.'","'.$item->i_confirm.'","'.$i->item_verify.'","';
					$q .= $i->item_verify_limit.'","'.$i->item_verify_msg.'","'.$i->item_depend_item.'","'.$i->item_match_item.'","'.$orderingi.'","'.$i->published.'")';
					$this->db->setQuery($q); 
					if (!$this->db->query()) { 
						$app->setError($this->db->getErrorMsg(), 'error'); 
						$app->setRedirect('formlist','display',''); 
						$app->redirect();
						return 0;
					}
					$item_id=$this->db->insertid();
					
					if ($i->item_type == "rad" || $i->item_type == "mcb" || $i->item_type == "dds") {
						$qo = "SELECT * FROM qr4_formitems_opts WHERE opt_item = ".$i->item_id. ' ORDER BY ordering';
						$this->db->setQuery($qo);
						$opts = $this->db->loadObjectList();
						foreach ($opts as $o) {
							$q='SELECT ordering FROM qr4_formitems_opts WHERE opt_item = '.$item_id.' ORDER BY ordering DESC LIMIT 1';
							$this->db->setQuery($q);
							$on = (int)$this->db->loadResult();
							if ($on) $ordering = ($on+1);
							else $ordering = 1;
						
							$q  = 'INSERT INTO qr4_formitems_opts (opt_item,opt_text,opt_depend,ordering,trashed,published) ';
							$q .= 'VALUES ("'.$item_id.'","'.$this->db->getEscaped($o->opt_text).'","'.$o->opt_depend.'","'.$ordering.'","'.$o->trashed.'","'.$o->published.'")';
							$this->db->setQuery($q); 
							if (!$this->db->query()) { 
								$app->setError($this->db->getErrorMsg(), 'error'); 
								$app->setRedirect('formlist','display',''); 
								$app->redirect();
								return 0;
							}
							$opt_id=$this->db->insertid();
						}
					}
				}
				
			
				
				$qe = 'SELECT * FROM qr4_formpages_emails WHERE eml_page = '.$p->page_id;
				$this->db->setQuery($qe);
				$emls = $this->db->loadObjectList();
				
				foreach ($emls as $e) {
					$q  = 'INSERT INTO qr4_formpages_emails (eml_title,eml_page,eml_fromname,eml_fromaddr,eml_toname,eml_toaddr,eml_subject,eml_content) ';
					$q .= 'VALUES ("'.$e->eml_title.'","'.$page_id.'","'.$e->eml_fromname.'","'.$e->eml_fromaddr.'","'.$e->eml_toname.'","'.$e->eml_toaddr.'","'.$e->eml_subject.'","'.$this->db->getEscaped($e->eml_content).'")';
					$this->db->setQuery($q); 
					if (!$this->db->query()) { 
						$app->setError($this->db->getErrorMsg(), 'error'); 
						$app->setRedirect('formlist','display',''); 
						$app->redirect();
						return 0;
					}
				}
				
			
				$qa = 'SELECT * FROM qr4_formpages_qa WHERE qa_page = '.$p->page_id;
				$this->db->setQuery($qa);
				$qas = $this->db->loadObject();
				
				if ($qas) {
					$q  = 'INSERT INTO qr4_formpages_qa (qa_page,qa_who,qa_whodetail,qa_instruct) ';
					$q .= 'VALUES ("'.$page_id.'","'.$this->db->getEscaped($qas->qa_who).'","'.$this->db->getEscaped($qas->qa_whodetail).'","'.$this->db->getEscaped($qas->qa_instruct).'")';
					$this->db->setQuery($q); 
					if (!$this->db->query()) { 
						$app->setError($this->db->getErrorMsg(), 'error'); 
						$app->setRedirect('pagelist','display','&form='.$form); 
						$app->redirect();
						return 0;
					}
				}
			}
		}
		
		$app->setError('Form(s) Copied', 'message');
		$app->setRedirect('formlist','display',''); 
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
	
	function dataExcel() {
		global $user;
		ini_set('memory_limit', '1024M');
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$form = urldecode(JRequest::getVar('form'));
		$items=$this->getFormItems($form);
		$data=$this->getData($form,$items,$sdate,$edate);
		$filename = "form_data_" . date('Y-m-d') . ".xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel");
		$flag = false;
		echo "Started\tEnded\tElapsed Time (secs)\t";
		echo "Browser\tPlatform\tMobile\tLocation\t";
		foreach ($items as $i) {
			echo $this->cleanItem($i->item_title)."\t";
		}
		echo "\n";
		foreach ($data as $d) {
			echo $this->cleanItem($d->data_start)."\t";
			echo $this->cleanItem($d->data_end)."\t";
			if ($d->data_end != '0000-00-00 00:00:00') echo $this->cleanItem((strtotime($d->data_end)-strtotime($d->data_start)))."\t";
			else echo 'Incomplete'."\t";
			echo $this->cleanItem($d->hit_browser.' '.$d->hit_browserver)."\t";
			echo $this->cleanItem($d->hit_platform)."\t";
			echo ($d->hit_ismobile==1?'Yes':'No')."\t";
			echo $this->cleanItem($d->hit_city.', '.$d->hit_region.', '.$d->hit_country)."\t";
			foreach ($items as $i) {
				$ans = 'i'.$i->item_id;
				echo $this->cleanItem($d->$ans)."\t";
			}
			echo "\n";
		}
		exit;
	}
	
	function getExcel() {
		global $user;
		ini_set('memory_limit', '1024M');
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$cids = urldecode(JRequest::getVar('forms'));
		$curclient=(int)$_POST['client'];
		$clients = $this->getClientList($user);
		$forms=$this->getFormList($clients,$curclient,$user,$cids,$sdate,$edate);
		$data=$this->getHits($forms,$cids,$sdate,$edate);
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
	function cleanItem($item)
	{
		$item = preg_replace("/\t/", "\\t", $item);
		$item = preg_replace("/\r?\n/", "\\n", $item);
		if(strstr($item, '"')) $item = '"' . str_replace('"', '""', $item) . '"';
		return $item;
	}
		
	function stats() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		$cids = implode( ',', $cids );
		$app->setRedirect('formlist','showstats','&forms='.urlencode($cids));
		$app->redirect();
		
	}	
		
	function data() {
		global $app;
		$cids = JRequest::getVar( 'form', array(0), 'post', 'array' );
		$app->setRedirect('formlist','showdata','&form='.$cids[0]);
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
	
	function showdata() {
		global $user;
		$sdate = JRequest::getVar('st_sdate');
		if (!$sdate) $sdate = date("Y-m-d", strtotime("-1 months"));
		$edate = JRequest::getVar('st_edate');
		if (!$edate) $edate = date("Y-m-d");
		$form = urldecode(JRequest::getVar('form'));
		$forminfo=$this->getFormInfo($form);
		$items=$this->getFormItems($form);
		$data=$this->getData($form,$items,$sdate,$edate);
		include 'mods/formlist/showdata.php';
		
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
	
	function getCatList($clid=0) {
		$q  = 'SELECT * FROM qr4_clientcats as uc ';
		$q .= 'RIGHT JOIN qr4_cats as cat ON uc.clcat_cat=cat.cat_id ';
		$q .= 'WHERE cat.published = 1 ';
		$q .= '&& uc.clcat_client = '.$clid.' ';
		$q .= 'ORDER BY cat.cat_name ';
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
		$session =& JFactory::getSession();
		$curcat=(int)$session->get('cat',0);
		$vids = Array();
		foreach ($clients as $cl) {	
			if ($curclient == $cl->cl_id || !$curclient) {
				$q  = 'SELECT * FROM qr4_clientcats as cc ';
				$q .= 'RIGHT JOIN qr4_cats as ct ON cc.clcat_cat = ct.cat_id ';
				$q .= 'WHERE ct.published = 1 && cc.clcat_client = '.$cl->cl_id;
				if ($curcat) $q .= ' && ct.cat_id = '.$curcat;
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
						
						$q5  = 'SELECT COUNT(*) FROM qr4_formdata WHERE data_end != "0000-00-00 00:00:00" && data_form = '.$cd->form_id;
						$q5 .= ' GROUP BY data_form';
						$this->db->setQuery($q5); $cd->completes = $this->db->loadResult(); if (!$cd->completes) $cd->completes=0;
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
					if (in_array($cd->form_id,explode(',',$cids))) $useforms[] = $cd->form_id;
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
	
	function getData($form,$items,$sdate,$edate) {
		$q = 'SELECT * FROM qr4_forms WHERE form_id = '.$form.' ';
		$this->db->setQuery($q);
		$forminfo = $this->db->loadObject();
		$q2  = 'SELECT * FROM qr4_formdata as d ';
		$q2 .= 'RIGHT JOIN qr4_fhits as h ON d.data_id = h.hit_data ';
		$q2 .= 'WHERE d.data_end != "0000-00-00 00:00:00" && d.data_form = '.$form.' ';
		if ($sdate && $edate) $q2 .= '&& date(d.data_start) BETWEEN "'.$sdate.'" AND "'.$edate.'" '; 
		$q2 .= 'ORDER BY d.data_start DESC';
		$this->db->setQuery($q2);
		$formdata=$this->db->loadObjectList();
		$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$form.'  && trashed = 0 && published = 1';
		$this->db->setQuery($qpp);
		$prevpages = $this->db->loadResultArray();
		foreach ($items as $i) { $itemids[] = $i->item_id; }
		$q3 = 'SELECT opt_id,opt_text FROM qr4_formitems_opts WHERE opt_item IN ('.implode(",",$itemids).')';
		$this->db->setQuery($q3);
		$anskeydata = $this->db->loadObjectList();
		$anskey = array();
		foreach ($anskeydata as $o) {
			$anskey[$o->opt_id] = $o->opt_text;
		}
		foreach ($formdata as &$d) {
			$qi  = 'SELECT * FROM qr4_formitems as i ';
			$qi .= 'RIGHT JOIN qr4_formdata_answers as a ON i.item_id = a.ans_question '; //future data retrevial
			$qi .= 'WHERE item_page IN ('.implode(",",$prevpages).') && published = 1 && a.ans_data = '.$d->data_id.' ';
			$qi .= 'ORDER BY i.item_id';	
			$this->db->setQuery($qi);
			$ansdata = $this->db->loadObjectList();
			foreach ($ansdata as $a) {
				$ans = "i".$a->item_id;
				switch ($a->item_type) {
					case "txt":
					case "tbx":
					case "eml":
					case "phn":
					case "hdn":
					case "dob":
						$d->$ans = $a->ans_answer;
						break;
					case "rad":
					case "dds":
						$d->$ans = $anskey[$a->ans_answer];
						break;
					case "mcb":
						$ids = explode(" ",$a->ans_answer);
						foreach ($ids as $id) {
							$d->$ans .= $anskey[$id].'; ';
						}
						break;
					case "cbx":
						$d->$ans = ($a->ans_answer == "on" ? 'Yes' : 'No');
						break;
						
				}
			}
		}
		return $formdata;
	}
	
	function getFormItems($form) {
		$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$form.'  && trashed = 0 && published = 1';
		$this->db->setQuery($qpp);
		$prevpages = $this->db->loadResultArray();
		$qi  = 'SELECT * FROM qr4_formitems as i ';
		$qi .= 'WHERE item_type != "msg" && item_page IN ('.implode(",",$prevpages).') && published = 1 ';
		$qi .= 'ORDER BY i.item_page, i.ordering';	
		$this->db->setQuery($qi);
		$items = $this->db->loadObjectList();
		return $items;
	}
}