<?php
class ItemList {
	var $db;
	
	function ItemList() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':	$title='Form Items'; break;
			case 'pageadd': $title='Add Item'; break;
			case 'pageedit': $title='Edit Item'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'itemadd':
			case 'itemedit':
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
				echo '<li><a href="index.php?mod=itemlist&task=additem&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Add Item</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
			echo '<li><a href="index.php?mod=pagelist&form='.JRequest::getInt('form',0).'">Pages</a></li>';
		}
		if ($task == 'itemadd' || $task == 'itemedit') {
			if ($user->lvl > 1) echo '<li><a href="index.php?mod=itemlist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Cancel</a></li>';
			if ($user->lvl > 1) echo '<li><a href="#" onclick="document.codeform.validate();">Save Item</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$items=$this->getItemList($page,$user->lvl);
		include 'mods/itemlist/default.php';

	}
	
	function saveItem() {
		global $app;
		$item_id=JRequest::getInt('item_id',0);
		$item_page=JRequest::getInt('item_page',0);
		$item_title=JRequest::getString('item_title');
		$item_text= $this->db->getEscaped(JRequest::getVar( 'item_text', null, 'default', 'none', 2));
		$item_type=JRequest::getString('item_type');
		$item_req=JRequest::getInt('item_req',0);
		$item_confirm=JRequest::getInt('item_confirm',0);
		$item_verify=JRequest::getInt('item_verify',0);
		$item_verify_limit=JRequest::getInt('item_verify_limit',0);
		$item_depend_item=JRequest::getInt('item_dpend_item',0);
		$item_form=JRequest::getInt('item_form',0);
		
		if ($item_id == 0) {
			$ordering=$this->getNextOrderNum($item_page);
			$q  = 'INSERT INTO qr4_formitems (item_page,item_title,item_text,item_type,item_req,item_confirm,item_verify,item_verify_limit,item_depend_item,ordering) ';
			$q .= 'VALUES ("'.$item_page.'","'.$item_title.'","'.$item_text.'","'.$item_type.'","'.$item_req.'","'.$item_confirm.'","'.$item_verify.'","';
			$q .= $item_verify_limit.'","'.$item_depend_item.'","'.$ordering.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('itemlist','default','&form='.$item_form.'&page='.$item_page); 
				$app->redirect();
				return 0;
			}
			$item_id=$this->db->insertid();
		} else {
			$q  = 'UPDATE qr4_formitems SET item_title="'.$item_title.'", item_text="'.$item_text.'", item_type="'.$item_type.'", item_req="'.$item_req.'", ';
			$q .= 'item_confirm="'.$item_confirm.'", item_verify="'.$item_verify.'", item_verify_limit="'.$item_verify_limit.'", item_depend_item="'.$item_depend_item.'" ';
			$q .= 'WHERE item_id = '.$item_id;
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('itemlist','default','&form='.$item_form.'&page='.$item_page); 
				$app->redirect(); 
				return 0;
			}
		}
		$app->setError('Item Saved', 'message');
		$app->setRedirect('itemlist','display','&form='.$item_form.'&page='.$item_page); 
		$app->redirect();
		
	}
	
	function getNextOrderNum($page) {
		$q='SELECT ordering FROM qr4_formitems WHERE item_page = '.$form.' ORDER BY ordering DESC LIMIT 1';
		$this->db->setQuery($q);
		$on = (int)$this->db->loadResult();
		if ($on) return ($on+1);
		else return 1;
	}
		
	function options() {
		global $app;
		$page = JRequest::getInt('page');
		$form = JRequest::getInt('form');
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$item = $cids[0];
		$app->setRedirect('optlist','display','&form='.$form.'&page='.$page.'&item='.$item);
		$app->redirect();
		
	}
	
	function itemAdd() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$items=$this->getItemList($page,$user->lvl);
		include 'mods/itemlist/itemform.php';
	}
	function itemEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$items=$this->getItemList($page,$user->lvl);
		$iteminfo=$this->getItemInfo($item);
		include 'mods/itemlist/itemform.php';
	}
	
	function additem() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$app->setRedirect('itemlist','itemadd','&form='.$form.'&page='.$page);
		$app->redirect();
	}
	function edititem() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$item=$cids[0];
		$app->setRedirect('itemlist','itemedit','&form='.$form.'&page='.$page.'&item='.$item);
		$app->redirect();
	}
	

	function saveorder() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$ordering = JRequest::getVar( 'order', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$total = count( $cids );
			for( $i=0; $i < $total; $i++ ) {
				$q='UPDATE qr4_formitems SET ordering = '.(int)$ordering[$i].' WHERE item_id = '.$cids[$i];
				$this->db->setQuery($q);  $this->db->query();
			}
			$app->setError('Item(s) Ordered', 'message');
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}

	function orderup() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formitems WHERE item_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT item_id,ordering FROM qr4_formitems WHERE item_page = '.$page.' && ordering < '.$oold.' ORDER BY ordering DESC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->item_id;
			$q3 = 'UPDATE qr4_formitems SET ordering = '.$onew.' WHERE item_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formitems SET ordering = '.$oold.' WHERE item_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function orderdown() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cid = $cids[0];
			$q = 'SELECT ordering FROM qr4_formitems WHERE item_id = '.$cid;
			$this->db->setQuery($q); $oold = $this->db->loadResult();
			$q2 = 'SELECT item_id,ordering FROM qr4_formitems WHERE item_page = '.$form.' && ordering > '.$oold.' ORDER BY ordering ASC LIMIT 1';
			$this->db->setQuery($q2); $other = $this->db->loadObject();
			$onew = $other->ordering; $idnew=$other->item_id;
			$q3 = 'UPDATE qr4_formitems SET ordering = '.$onew.' WHERE item_id='.$cid;
			$this->db->setQuery($q3);  $this->db->query();
			$q4 = 'UPDATE qr4_formitems SET ordering = '.$oold.' WHERE item_id='.$idnew;
			$this->db->setQuery($q4);  $this->db->query();
			$app->setError('Order Changed', 'message');
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function notonconf() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET item_confirm = 0 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Not on Confirmation Page', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function onconf() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET item_confirm = 1 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) On Confirmation Page', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function notreq() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET item_req = 0 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Unrequired', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function req() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET item_req = 1 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Requireded', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function unpublish() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET published = 0 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function publish() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET published = 1 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_formitems SET trashed = 0 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function trash() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_itempages SET trashed = 1 WHERE item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_itempages WHERE trashed = 1 && item_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Item(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);
			$app->redirect();
		}
	}
	function getItemInfo($form) {
		$q = 'SELECT * FROM qr4_formitems WHERE item_id = '.$form;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getItemList($page,$ulvl) {
		$q2  = 'SELECT * FROM qr4_formitems as fi ';
		$q2 .= 'WHERE fi.item_page = '.$page.' ';
		$q2 .= 'ORDER BY ordering ASC';
		$this->db->setQuery($q2); 
		$iteml = $this->db->loadObjectList();
		foreach ($iteml as &$it) {
			$q4  = 'SELECT COUNT(*) FORM qr4_formitems_opts WHERE opt_item = '.$it->item_id;
			$q4 .= ' GROUP BY opt_item';
			$this->db->setQuery($q4); $it->opts = $this->db->loadResult(); if (!$it->opts) $it->opts=0;
		}
		$items = $iteml;

		return $items;
		
	}
	
}