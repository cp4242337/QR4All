<?php
class Cats {
	function Cats() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'catadd':
			case 'catedit':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}
	
	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':
				$title='Category'; break;
			case 'catadd':
				$title='Add Category';	break;
			case 'catedit':
				$title='Edit Category';	break;
		}		
		return $title;
	}
	
	function getSubMenu($task) {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl_edit) {
				echo '<li><a href="index.php?mod=cats&task=addcat">Add Category</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
		}
		if ($task == 'catadd' || $task == 'catedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=cats">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.catform.validate();">Save Category</a></li>';
		}
		echo '</ul>';
	}
	
	function display() {
		global $user;
		if (!$user->lvl_edit) {
			$app->setError($this->db->getErrorMsg(), 'error'); 
			$app->setRedirect('home'); 
			$app->redirect();
			return 0;
		} else {
			$clients=$this->getClients($user);
			$cats=$this->getCats($clients);
			include 'mods/cats/default.php';
		}

	}
	
	function saveCat() {
		global $app,$user;
		if ($user->lvl_edit) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cat_id=JRequest::getInt('cat_id',0);
		$cat_name=JRequest::getString('cat_name');
		$cat_client=JRequest::getInt('cat_client',0);
		if ($cat_id == 0) {
			$q = 'INSERT INTO qr4_cats (cat_name) VALUES ("'.$cat_name.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('cats'); $app->redirect(); return 0; }
			$cat_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_cats SET cat_name="'.$cat_name.'" WHERE cat_id = '.$cat_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('cats'); $app->redirect(); return 0;}
		}
		$q2='DELETE FROM qr4_clientcats WHERE clcat_cat = '.$cat_id;
		$this->db->setQuery($q2); $this->db->query();
		$q3='INSERT INTO qr4_clientcats (clcat_cat,clcat_client) VALUES ('.$cat_id.','.$cat_client.')';
		$this->db->setQuery($q3); $this->db->query();
		$app->setError('Category Saved', 'message');
		$app->setRedirect('cats'); 
		$app->redirect();
		
	}
	
	
	function catAdd() {
		global $user;
		if ($user->lvl_edit) {
			$clients=$this->getClients($user);
			include 'mods/cats/catform.php';
		}
	}
	function catEdit() {
		global $user;
		if ($user->lvl_edit) {
			$clients=$this->getClients($user);
			$catinfo=$this->getCatInfo(JRequest::getInt('cat',0));
			include 'mods/cats/catform.php';
		}
	}
	
	function addcat() {
		global $app,$user;
		if (!$user->lvl_edit) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;}
		$app->setRedirect('cats','catadd');
		$app->redirect();
	}
	function editcat() {
		global $app,$user;
		if (!$user->lvl_edit) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;}
		$cids = JRequest::getVar( 'cat', array(0), 'post', 'array' );
		$app->setRedirect('cats','catedit','&cat='.(int)$cids[0]);
		$app->redirect();
	}
	
	
	function getCats($cls) {
		foreach ($cls as $c) { $clids[] = $c->cl_id; }
		$q  = 'SELECT c.*,cl.cl_id,cl.cl_name FROM qr4_cats as c ';
		$q .= 'LEFT JOIN qr4_clientcats as cc ON c.cat_id = clcat_cat ';
		$q .= 'LEFT JOIN qr4_clients as cl ON cc.clcat_client = cl.cl_id ';
		$q .= 'WHERE cl.cl_id IN ('.implode(",",$clids).') ';
		$q .= 'ORDER BY c.cat_name';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}	
	
	function getClients($user) {
		$q  = 'SELECT * FROM qr4_usersclients as uc ';
		$q .= 'RIGHT JOIN qr4_clients as cl ON uc.cu_client=cl.cl_id ';
		$q .= 'WHERE cl.published = 1 ';
		if (!$user->lvl_admin) $q .= ' && cu_user = '.$user->id.' ';
		$q .= 'GROUP BY cl.cl_id ';
		$q .= 'ORDER BY cl.cl_name ';
		$this->db->setQuery($q); 
		return $this->db->loadObjectList();
	}
	
	function getCatInfo($cat) {
		$q  = 'SELECT c.*,cl.cl_id,cl.cl_name FROM qr4_cats as c ';
		$q .= 'LEFT JOIN qr4_clientcats as cc ON c.cat_id = clcat_cat ';
		$q .= 'LEFT JOIN qr4_clients as cl ON cc.clcat_client = cl.cl_id ';
		$q .= 'WHERE c.cat_id = '.$cat;
		$this->db->setQuery($q);
		return $this->db->loadObject();
	}
	
	function unpublish() {
		global $app,$user;
		if (!$user->lvl_edit) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;}
		$cids = JRequest::getVar( 'cat', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_cats SET published = 0 WHERE cat_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Category(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('cats');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app,$user;
		if (!$user->lvl_edit) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;}
		$cids = JRequest::getVar( 'cat', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_cats SET published = 1 WHERE cat_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Category(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('cats');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;}
		$cids = JRequest::getVar( 'cat', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_cats WHERE cat_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Category(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('cats');
			$app->redirect();
		}
	}
}