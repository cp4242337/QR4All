<?php
class PageList {
	var $db;
	
	function PageList() {
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
			case 'display':	$title='Pages'; break;
			case 'pageadd': $title='Add Page'; break;
			case 'pageedit': $title='Edit Page'; break;
			case 'showstats': $title='Stats'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'pageadd':
			case 'pageedit':
			case 'showstats':
			case 'qaedit':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}

	function getSubMenu($task='display') {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl_edit) {
				echo '<li><a href="index.php?mod=pagelist&task=addpage&form='.JRequest::getInt('form',0).'">Add Page</a></li>';
				echo '<li><a href="#" onclick="allTask(\'copyPage\');">Copy Page</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
			}
			if ($user->lvl_admin) {
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
			echo '<li><a href="index.php?mod=formlist">Forms</a></li>';
		}
		if ($task == 'pageadd' || $task == 'pageedit' || $task == 'qaedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=pagelist&form='.JRequest::getInt('form',0).'">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Page</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$curclient=(int)$_POST['client'];
		$pages=$this->getPageList($form,$user);
		include 'mods/pagelist/default.php';

	}
	
	function savePage() {
		global $app;
		$page_id=JRequest::getInt('page_id',0);
		$page_form=JRequest::getInt('page_form',0);
		$page_title=JRequest::getString('page_title');
		$page_type=JRequest::getString('page_type');
		$page_action=JRequest::getString('page_action');
		$page_actiontext=JRequest::getString('page_actiontext');
		$page_redirurl=JRequest::getString('page_redirurl');
		$page_reset=JRequest::getString('page_reset');
		$page_resettext=JRequest::getString('page_resettext');
		$page_content= $this->db->getEscaped(JRequest::getVar( 'page_content', null, 'default', 'none', 2));
		$page_qa=JRequest::getString('page_qa');
		$ordering=$this->getNextOrderNum($page_form);
		if ($page_id == 0) {
			$q = 'INSERT INTO qr4_formpages (page_form,page_title,page_type,page_action,page_actiontext,page_reset,page_resettext,page_redirurl,ordering,page_content,page_qa) VALUES ("'.$page_form.'","'.$page_title.'","'.$page_type.'","'.$page_action.'","'.$page_actiontext.'","'.$page_reset.'","'.$page_resettext.'","'.$page_redirurl.'","'.$ordering.'","'.$page_content.'","'.$page_qa.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$page_form); $app->redirect(); }
			$form_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_formpages SET page_title="'.$page_title.'", page_type="'.$page_type.'", page_action="'.$page_action.'", page_actiontext="'.$page_actiontext.'", page_reset="'.$page_reset.'", page_resettext="'.$page_resettext.'", page_redirurl="'.$page_redirurl.'", page_content="'.$page_content.'", page_qa="'.$page_qa.'" WHERE page_id = '.$page_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$page_form); $app->redirect(); }
		}
		$app->setError('Page Saved', 'message');
		$app->setRedirect('pagelist','display','&form='.$page_form); 
		$app->redirect();
		
	}
	
	function copyPage() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		foreach ($cids as $c) {
			$q = 'SELECT * FROM qr4_formpages WHERE page_id = '.$c;
			$this->db->setQuery($q);
			$info = $this->db->loadObject();
			$ordering=$this->getNextOrderNum($info->page_form);
			$q = 'INSERT INTO qr4_formpages (page_form,page_title,page_type,page_action,page_actiontext,page_reset,page_resettext,page_redirurl,ordering,page_content,page_qa,published,trashed) ';
			$q.= 'VALUES ("'.$info->page_form.'","'.$info->page_title.'","'.$info->page_type.'","'.$info->page_action.'","'.$info->page_actiontext.'","'.$info->page_reset.'","'.$info->page_resettext.'","'.$info->page_redirurl.'","'.$ordering.'","'.$this->db->getEscaped($info->page_content).'","'.$info->page_qa.'","'.$info->published.'","'.$info->trashed.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$form); $app->redirect(); }
			$page_id=$this->db->insertid();
			
			$qi = 'SELECT * FROM qr4_formitems WHERE item_page = '.$info->page_id. ' ORDER BY ordering';
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
					$app->setRedirect('pagelist','display','&form='.$form); 
					$app->redirect();
					return 0;
				}
				
				$item_id=$this->db->insertid();
				
				if ($i->item_type == "rad" || $i->item_type == "mcb" || $i->item_type == "dds") {
					$qo = "SELECT * FROM qr4_formitems_opts WHERE opt_item = ".$i->item_id;
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
							$app->setRedirect('pagelist','display','&form='.$form); 
							$app->redirect();
							return 0;
						}
						$opt_id=$this->db->insertid();
					}
				}
			}
			
			$qe = 'SELECT * FROM qr4_formpages_emails WHERE eml_page = '.$info->page_id;
			$this->db->setQuery($qe);
			$emls = $this->db->loadObjectList();
			
			foreach ($emls as $e) {
				$q  = 'INSERT INTO qr4_formpages_emails (eml_title,eml_page,eml_fromname,eml_fromaddr,eml_toname,eml_toaddr,eml_subject,eml_content) ';
				$q .= 'VALUES ("'.$e->eml_title.'","'.$page_id.'","'.$e->eml_fromname.'","'.$e->eml_fromaddr.'","'.$e->eml_toname.'","'.$e->eml_toaddr.'","'.$e->eml_subject.'","'.$this->db->getEscaped($e->eml_content).'")';
				$this->db->setQuery($q); 
				if (!$this->db->query()) { 
					$app->setError($this->db->getErrorMsg(), 'error'); 
					$app->setRedirect('pagelist','display','&form='.$form); 
					$app->redirect();
					return 0;
				}
			}
			
			$qa = 'SELECT * FROM qr4_formpages_qa WHERE qa_page = '.$info->page_id;
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
		$app->setError('Page(s) Copied', 'message');
		$app->setRedirect('pagelist','display','&form='.$form); 
		$app->redirect();
	}
	
	function saveQA() {
		global $app;
		$page_form=JRequest::getInt('page_form',0);
		$qa_page=JRequest::getInt('qa_page',0);
		$qa_who=JRequest::getString('qa_who');
		$qa_whodetail=JRequest::getString('qa_whodetail');
		$qa_instruct= $this->db->getEscaped(JRequest::getVar( 'qa_instruct', null, 'default', 'none', 2));
		$ordering=$this->getNextOrderNum($page_form);
		$q = 'UPDATE qr4_formpages_qa SET qa_who="'.$qa_who.'", qa_whodetail="'.$qa_whodetail.'", qa_instruct="'.$qa_instruct.'" WHERE qa_page = '.$qa_page;
		$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$page_form); $app->redirect(); }
		$app->setError('Q&A Saved', 'message');
		$app->setRedirect('pagelist','display','&form='.$page_form); 
		$app->redirect();
		
	}
	
	function getNextOrderNum($form) {
		$q='SELECT ordering FROM qr4_formpages WHERE page_form = '.$form.' ORDER BY ordering DESC LIMIT 1';
		$this->db->setQuery($q);
		$on = (int)$this->db->loadResult();
		if ($on) return ($on+1);
		else return 1;
	}
		
	function items() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$page = $cids[0];
		$form = JRequest::getInt( 'form', 0 );
		$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
		$app->redirect();
		
	}
		
	function setupemail() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$page = $cids[0];
		$form = JRequest::getInt( 'form', 0 );
		$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
		$app->redirect();
		
	}
	
	function pageAdd() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		include 'mods/pagelist/pageform.php';
	}
	function pageEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$pageinfo=$this->getPageInfo(JRequest::getInt('page',0));
		$aitems = $this->getAvailableItems($form,$pageinfo->ordering);
		include 'mods/pagelist/pageform.php';
	}
	function qaEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$qainfo=$this->getQAInfo(JRequest::getInt('page',0));
		$pageinfo=$this->getPageInfo(JRequest::getInt('page',0));
		if (!$qainfo) {
			$q='INSERT INTO qr4_formpages_qa (qa_page) VALUES ('.JRequest::getInt('page',0).')';
			$this->db->setQuery($q);
			$this->db->query();
			$qainfo=$this->getQAInfo(JRequest::getInt('page',0));
		}
		$aitems = $this->getAvailableItems($form,$pageinfo->ordering);
		include 'mods/pagelist/pageqa.php';
	}
	
	function addpage() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$app->setRedirect('pagelist','pageadd','&form='.$form);
		$app->redirect();
	}
	function editpage() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$app->setRedirect('pagelist','pageedit','&form='.$form.'&page='.(int)$cids[0]);
		$app->redirect();
	}
	function editqa() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$app->setRedirect('pagelist','qaedit','&form='.$form.'&page='.(int)$cids[0]);
		$app->redirect();
	}
	
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages SET published = 0 WHERE page_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Page(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}

	function saveorder() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$ordering = JRequest::getVar( 'order', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$total = count( $cids );
			for( $i=0; $i < $total; $i++ ) {
				$q='UPDATE qr4_formpages SET ordering = '.(int)$ordering[$i].' WHERE page_id = '.$cids[$i];
				$this->db->setQuery($q);  $this->db->query();
			}
			$app->setError('Page(s) Ordered', 'message');
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}

	function orderup() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formpages WHERE page_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT page_id,ordering FROM qr4_formpages WHERE page_form = '.$form.' && ordering < '.$oold.' ORDER BY ordering DESC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->page_id;
			$q3 = 'UPDATE qr4_formpages SET ordering = '.$onew.' WHERE page_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formpages SET ordering = '.$oold.' WHERE page_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	
	function orderdown() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formpages WHERE page_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT page_id,ordering FROM qr4_formpages WHERE page_form = '.$form.' && ordering > '.$oold.' ORDER BY ordering ASC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->page_id;
			$q3 = 'UPDATE qr4_formpages SET ordering = '.$onew.' WHERE page_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formpages SET ordering = '.$oold.' WHERE page_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages SET published = 1 WHERE page_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Page(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages SET trashed = 0 WHERE page_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Page(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages SET trashed = 1 WHERE page_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Page(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'page', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_formpages WHERE trashed = 1 && page_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Page(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('pagelist','display','&form='.$form);
			$app->redirect();
		}
	}
	function getPageInfo($form) {
		$q = 'SELECT * FROM qr4_formpages WHERE page_id = '.$form;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	function getQAInfo($page) {
		$q = 'SELECT * FROM qr4_formpages_qa WHERE qa_page = '.$page;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getPageList($form,$user) {
		$q2  = 'SELECT * FROM qr4_formpages as fp ';
		$q2 .= 'WHERE fp.page_form = '.$form.' ';
		$q2 .= 'ORDER BY ordering ASC';
		$this->db->setQuery($q2); 
		$pagel = $this->db->loadObjectList();
		foreach ($pagel as &$pg) {
			$q4  = 'SELECT COUNT(*) FROM qr4_formitems WHERE item_page = '.$pg->page_id;
			$q4 .= ' GROUP BY item_page';
			$this->db->setQuery($q4); $pg->items = $this->db->loadResult(); if (!$pg->items) $pg->items=0;
		}
		$pages = $pagel;

		return $pages;
		
	}
	
	function getAvailableItems($form,$order) {
		$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$form.'  && trashed = 0 && published = 1 && ordering < '.$order;
		$this->db->setQuery($qpp);
		$prevpages = $this->db->loadResultArray();
		if ($prevpages) {
			$qi  = 'SELECT item_id,item_title FROM qr4_formitems as i ';
			$qi .= 'WHERE item_page IN ('.implode(",",$prevpages).') && published = 1 ';
			$qi .= 'ORDER BY i.ordering';	
			$this->db->setQuery($qi);
			$items = $this->db->loadObjectList();
		}
		return $items;
	}
	
}