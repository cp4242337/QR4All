<?php
class PageList {
	var $db;
	
	function PageList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
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
				$hascontent = true;
				break;
		}
		return $hascontent;
	}

	function getSubMenu($task='display') {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl > 1) {
				echo '<li><a href="index.php?mod=pagelist&task=addpage&form='.JRequest::getInt('form',0).'">Add Page</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
			}
			if ($user->lvl > 2) {
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
			echo '<li><a href="index.php?mod=formlist">Forms</a></li>';
		}
		if ($task == 'pageadd' || $task == 'pageedit') {
			if ($user->lvl > 1) echo '<li><a href="index.php?mod=pagelist&form='.JRequest::getInt('form',0).'">Cancel</a></li>';
			if ($user->lvl > 1) echo '<li><a href="#" onclick="document.codeform.validate();">Save Page</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$curclient=(int)$_POST['client'];
		$pages=$this->getPageList($form,$user->lvl);
		include 'mods/pagelist/default.php';

	}
	
	function savePage() {
		global $app;
		$page_id=JRequest::getInt('page_id',0);
		$page_form=JRequest::getInt('page_form',0);
		$page_title=JRequest::getString('page_title');
		$page_type=JRequest::getString('page_type');
		$page_action=JRequest::getString('page_action');
		$page_content= $this->db->getEscaped(JRequest::getVar( 'page_content', null, 'default', 'none', 2));
		$ordering=$this->getNextOrderNum($page_form);
		if ($page_id == 0) {
			$q = 'INSERT INTO qr4_formpages (page_form,page_title,page_type,page_action,ordering,page_content) VALUES ("'.$page_form.'","'.$page_title.'","'.$page_type.'","'.$page_action.'","'.$ordering.'","'.$page_content.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$page_form); $app->redirect(); }
			$form_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_formpages SET page_title="'.$page_title.'", page_type="'.$page_type.'", page_action="'.$page_action.'", page_content="'.$page_content.'" WHERE page_id = '.$page_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('pagelist','display','&form='.$page_form); $app->redirect(); }
		}
		$app->setError('Page Saved', 'message');
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
		include 'mods/pagelist/pageform.php';
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
	
	function getPageList($form,$ulvl) {
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
	
}