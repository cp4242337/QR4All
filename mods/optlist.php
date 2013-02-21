<?php
class OptList {
	var $db;
	
	function OptList() {
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
			case 'display':	$title='Item Options'; break;
			case 'optadd': $title='Add Option'; break;
			case 'optedit': $title='Edit Option'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'optadd':
			case 'optedit':
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
				echo '<li><a href="index.php?mod=optlist&task=addopt&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'&item='.JRequest::getInt('item',0).'">Add Option</a></li>';
				echo '<li><a href="#" onclick="allTask(\'copyOpt\');">Copy</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
			echo '<li><a href="index.php?mod=itemlist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Items</a></li>';
		}
		if ($task == 'optadd' || $task == 'optedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=optlist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'&item='.JRequest::getInt('item',0).'">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Option</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$options=$this->getOptList($item,$user);
		include 'mods/optlist/default.php';

	}
	
	function saveOpt() {
		global $app;
		$opt_id=JRequest::getInt('opt_id',0);
		$opt_item=JRequest::getInt('opt_item',0);
		$opt_text= $this->db->getEscaped(JRequest::getVar( 'opt_text', null, 'default', 'none', 2));
		$opt_depend=JRequest::getInt('opt_depend',0);
		$opt_form=JRequest::getInt('opt_form',0);
		$opt_page=JRequest::getInt('opt_page',0);
		
		if ($opt_id == 0) {
			$ordering=$this->getNextOrderNum($opt_item);
			$q  = 'INSERT INTO qr4_formitems_opts (opt_item,opt_text,opt_depend,ordering) ';
			$q .= 'VALUES ("'.$opt_item.'","'.$opt_text.'","'.$opt_depend.'","'.$ordering.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('optlist','display','&form='.$opt_form.'&page='.$opt_page.'&item='.$opt_item); 
				$app->redirect();
				return 0;
			}
			$opt_id=$this->db->insertid();
		} else {
			$q  = 'UPDATE qr4_formitems_opts SET opt_text="'.$opt_text.'", opt_depend="'.$opt_depend.'" ';
			$q .= 'WHERE opt_id = '.$opt_id;
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('optlist','display','&form='.$opt_form.'&page='.$opt_page.'&item='.$opt_item); 
				$app->redirect(); 
				return 0;
			}
		}
		$app->setError('Option Saved', 'message');
		$app->setRedirect('optlist','display','&form='.$opt_form.'&page='.$opt_page.'&item='.$opt_item);  
		$app->redirect();
		
	}
	
	function copyOpt() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		foreach ($cids as $c) {
			$q = 'SELECT * FROM qr4_formitems_opts WHERE opt_id = '.$c;
			$this->db->setQuery($q);
			$info = $this->db->loadObject();
			
			$ordering=$this->getNextOrderNum($item);
			$q  = 'INSERT INTO qr4_formitems_opts (opt_item,opt_text,opt_depend,ordering,trashed,published) ';
			$q .= 'VALUES ("'.$info->opt_item.'","'.$this->db->getEscaped($info->opt_text).'","'.$info->opt_depend.'","'.$ordering.'","'.$info->trashed.'","'.$info->published.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item); 
				$app->redirect();
				return 0;
			}
			$opt_id=$this->db->insertid();
		}
		$app->setError('Option(s) Copied', 'message');
		$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);  
		$app->redirect();
		
	}
	
	
	
	function getNextOrderNum($item) {
		$q='SELECT ordering FROM qr4_formitems_opts WHERE opt_item = '.$item.' ORDER BY ordering DESC LIMIT 1';
		$this->db->setQuery($q);
		$on = (int)$this->db->loadResult();
		if ($on) return ($on+1);
		else return 1;
	}
		
	function optAdd() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		include 'mods/optlist/optform.php';
	}
	function optEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$opt = JRequest::getInt( 'opt', 0 );
		$optinfo=$this->getOptInfo($opt);
		include 'mods/optlist/optform.php';
	}
	
	function addopt() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$app->setRedirect('optlist','optadd','&form='.$form.'&page='.$page.'&item='.$item);
		$app->redirect();
	}
	function editopt() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$opt=$cids[0];
		$app->setRedirect('optlist','optedit','&form='.$form.'&page='.$page.'&item='.$item.'&opt='.$opt);
		$app->redirect();
	}
	

	function saveorder() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$ordering = JRequest::getVar( 'order', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$total = count( $cids );
			for( $i=0; $i < $total; $i++ ) {
				$q='UPDATE qr4_formitems_opts SET ordering = '.(int)$ordering[$i].' WHERE opt_id = '.$cids[$i];
				$this->db->setQuery($q);  $this->db->query();
			}
			$app->setError('Options(s) Ordered', 'message');
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}

	function orderup() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formitems_opts WHERE opt_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT opt_id,ordering FROM qr4_formitems_opts WHERE opt_item = '.$item.' && ordering < '.$oold.' ORDER BY ordering DESC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->opt_id;
			$q3 = 'UPDATE qr4_formitems_opts SET ordering = '.$onew.' WHERE opt_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formitems_opts SET ordering = '.$oold.' WHERE opt_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function orderdown() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formitems_opts WHERE opt_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT opt_id,ordering FROM qr4_formitems_opts WHERE opt_item = '.$item.' && ordering > '.$oold.' ORDER BY ordering ASC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->opt_id;
			$q3 = 'UPDATE qr4_formitems_opts SET ordering = '.$onew.' WHERE opt_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formitems_opts SET ordering = '.$oold.' WHERE opt_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function notdepend() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET opt_depend = 0 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Options(s) Not Dependent', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function depend() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET opt_depend = 1 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Options(s) Dependent', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET published = 0 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Option(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET published = 1 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Option(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET trashed = 0 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Option(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems_opts SET trashed = 1 WHERE opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Option(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'opt', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_formitems_opts WHERE trashed = 1 && opt_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Option(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
			$app->redirect();
		}
	}
	function getOptInfo($opt) {
		$q = 'SELECT * FROM qr4_formitems_opts WHERE opt_id = '.$opt;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getOptList($item,$user) {
		$q2  = 'SELECT * FROM qr4_formitems_opts as fi ';
		$q2 .= 'WHERE fi.opt_item = '.$item.' ';
		$q2 .= 'ORDER BY ordering ASC';
		$this->db->setQuery($q2); 
		$opts = $this->db->loadObjectList();
		return $opts;
		
	}
	
}