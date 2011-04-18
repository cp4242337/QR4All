<?php
class FEmlList {
	var $db;
	
	function FEmlList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Form Page EMails'; break;
			case 'emladd': $title='Add EMail'; break;
			case 'emledit': $title='Edit EMail'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'emladd':
			case 'emledit':
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
				echo '<li><a href="index.php?mod=femllist&task=addeml&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Add EMail</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
			echo '<li><a href="index.php?mod=pagelist&form='.JRequest::getInt('form',0).'">Pages</a></li>';
		}
		if ($task == 'emladd' || $task == 'emledit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=femllist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save EMail</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$emails=$this->getEmlList($page,$user);
		include 'mods/femllist/default.php';

	}
	
	function saveEml() {
		global $app;
		$eml_id=JRequest::getInt('eml_id',0);
		$eml_title=JRequest::getString('eml_title');
		$eml_page=JRequest::getInt('eml_page',0);
		$eml_fromname=JRequest::getString('eml_fromname');
		$eml_fromaddr=JRequest::getString('eml_fromaddr');
		$eml_toname=JRequest::getInt('eml_toname',0);
		$eml_toaddr=JRequest::getInt('eml_toaddr',0);
		$eml_subject=JRequest::getString('eml_subject');
		$eml_content= $this->db->getEscaped(JRequest::getVar( 'eml_content', null, 'default', 'none', 2));
		$eml_form=JRequest::getInt('eml_form',0);
		
		if ($eml_id == 0) {
			$q  = 'INSERT INTO qr4_formpages_emails (eml_title,eml_page,eml_fromname,eml_fromaddr,eml_toname,eml_toaddr,eml_subject,eml_content) ';
			$q .= 'VALUES ("'.$eml_title.'","'.$eml_page.'","'.$eml_fromname.'","'.$eml_fromaddr.'","'.$eml_toname.'","'.$eml_toaddr.'","'.$eml_subject.'","'.$eml_content.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('femllist','display','&form='.$eml_form.'&page='.$eml_page); 
				$app->redirect();
				return 0;
			}
			$eml_id=$this->db->insertid();
		} else {
			$q  = 'UPDATE qr4_formpages_emails SET eml_title="'.$eml_title.'", eml_fromname="'.$eml_fromname.'", eml_fromaddr="'.$eml_fromaddr.'", eml_toname="'.$eml_toname.'", eml_toaddr="'.$eml_toaddr.'", eml_subject="'.$eml_subject.'",  eml_content="'.$eml_content.'" ';
			$q .= 'WHERE eml_id = '.$eml_id;
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('femllist','display','&form='.$eml_form.'&page='.$eml_page); 
				$app->redirect(); 
				return 0;
			}
		}
		$app->setError('Email Saved', 'message');
		$app->setRedirect('femllist','display','&form='.$eml_form.'&page='.$eml_page);  
		$app->redirect();
		
	}
	function attach() {
		global $user,$app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$eml=$cids[0];
		$app->setRedirect('attachlist','display','&form='.$form.'&page='.$page.'&eml='.$eml);  
		$app->redirect();
	}
	
	function emlAdd() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$items=$this->getFormItems($form);
		include 'mods/femllist/emlform.php';
	}
	function emlEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$items=$this->getFormItems($form);
		$eml = JRequest::getInt( 'eml', 0 );
		$emlinfo=$this->getEmlInfo($eml);
		include 'mods/femllist/emlform.php';
	}
	
	function addeml() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$app->setRedirect('femllist','emladd','&form='.$form.'&page='.$page);
		$app->redirect();
	}
	function editeml() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$eml=$cids[0];
		$app->setRedirect('femllist','emledit','&form='.$form.'&page='.$page.'&eml='.$eml);
		$app->redirect();
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages_emails SET published = 0 WHERE eml_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('EMail(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages_emails SET published = 1 WHERE eml_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Email(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages_emails SET trashed = 0 WHERE eml_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Email(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formpages_emails SET trashed = 1 WHERE eml_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('EMail(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'eml', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_formpages_emails WHERE trashed = 1 && eml_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('EMail(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('femllist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	function getEmlInfo($eml) {
		$q = 'SELECT * FROM qr4_formpages_emails WHERE eml_id = '.$eml;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getEmlList($page,$user) {
		$q2  = 'SELECT * FROM qr4_formpages_emails as fi ';
		$q2 .= 'WHERE fi.eml_page = '.$page.' ';
		$q2 .= 'ORDER BY eml_title ASC';
		$this->db->setQuery($q2); 
		$emls = $this->db->loadObjectList();
		foreach ($emls as &$e) {
			$q4  = 'SELECT COUNT(*) FROM qr4_formpages_emails_attach WHERE at_email = '.$e->eml_id;
			$q4 .= ' GROUP BY at_email';
			$this->db->setQuery($q4); $e->attachs = $this->db->loadResult(); if (!$e->attachs) $e->attachs=0;
		}
		return $emls;
		
	}
	
	function getFormItems($form) {
		$q2 = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$form.' ORDER BY ordering';
		$this->db->setQuery($q2); $pages = $this->db->loadResultArray();
		$q  = 'SELECT item_id,item_title,item_type FROM qr4_formitems WHERE item_page IN('.implode(",",$pages).') ';
		$q .= 'ORDER BY item_page,ordering ASC';
		$this->db->setQuery($q);
		return $this->db->loadObjectList();
	}
}