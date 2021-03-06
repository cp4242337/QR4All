<?php
class ItemList {
	var $db;
	
	function ItemList() {
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
			if ($user->lvl_edit) {
				echo '<li><a href="index.php?mod=itemlist&task=additem&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Add Item</a></li>';
				echo '<li><a href="#" onclick="allTask(\'copyItem\');">Copy</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Untrash</a></li>';
			}
			echo '<li><a href="index.php?mod=pagelist&form='.JRequest::getInt('form',0).'">Pages</a></li>';
		}
		if ($task == 'itemadd' || $task == 'itemedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=itemlist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Item</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$items=$this->getItemList($page,$user);
		include 'mods/itemlist/default.php';

	}
	
	function saveItem() {
		global $app;
		$item_id=JRequest::getInt('item_id',0);
		$item_page=JRequest::getInt('item_page',0);
		$item_title=JRequest::getString('item_title');
		$item_text= $this->db->getEscaped(JRequest::getVar( 'item_text', null, 'default', 'none', 2));
		$item_hint= $this->db->getEscaped(JRequest::getVar( 'item_hint', null, 'default', 'none', 2));
		$item_type=JRequest::getString('item_type');
		$item_req=JRequest::getInt('item_req',0);
		$item_confirm=JRequest::getInt('item_confirm',0);
		$item_verify=JRequest::getInt('item_verify',0);
		$item_verify_msg=JRequest::getString('item_verify_msg',0);
		$item_verify_limit=JRequest::getInt('item_verify_limit',0);
		$item_match_item=JRequest::getInt('item_match_item',0);
		$item_depend_item=JRequest::getInt('item_depend_item',0);
		$item_form=JRequest::getInt('item_form',0);
		
		if ($item_id == 0) {
			$ordering=$this->getNextOrderNum($item_page);
			$q  = 'INSERT INTO qr4_formitems (item_page,item_title,item_text,item_hint,item_type,item_req,item_confirm,item_verify,item_verify_limit,item_verify_msg,item_depend_item,item_match_item,ordering) ';
			$q .= 'VALUES ("'.$item_page.'","'.$item_title.'","'.$item_text.'","'.$item_hint.'","'.$item_type.'","'.$item_req.'","'.$item_confirm.'","'.$item_verify.'","';
			$q .= $item_verify_limit.'","'.$item_verify_msg.'","'.$item_depend_item.'","'.$item_match_item.'","'.$ordering.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('itemlist','display','&form='.$item_form.'&page='.$item_page); 
				$app->redirect();
				return 0;
			}
			$item_id=$this->db->insertid();
		} else {
			$q  = 'UPDATE qr4_formitems SET item_title="'.$item_title.'", item_text="'.$item_text.'", item_hint="'.$item_hint.'", item_type="'.$item_type.'", item_req="'.$item_req.'", ';
			$q .= 'item_confirm="'.$item_confirm.'", item_verify="'.$item_verify.'", item_verify_limit="'.$item_verify_limit.'", item_depend_item="'.$item_depend_item.'", ';
			$q .= 'item_match_item="'.$item_match_item.'", item_verify_msg="'.$item_verify_msg.'" ';
			$q .= 'WHERE item_id = '.$item_id;
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('itemlist','display','&form='.$item_form.'&page='.$item_page); 
				$app->redirect(); 
				return 0;
			}
		}
		$app->setError('Item Saved', 'message');
		$app->setRedirect('itemlist','display','&form='.$item_form.'&page='.$item_page); 
		$app->redirect();
		
	}
	
	function copyItem() {
		global $app;
		$cids = JRequest::getVar( 'item', array(0), 'post', 'array' ); 
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		foreach ($cids as $c) {
			$qi = 'SELECT * FROM qr4_formitems WHERE item_id = '.$c;
			$this->db->setQuery($qi);
			$info = $this->db->loadObject();
		
			$ordering=$this->getNextOrderNum($info->item_page);
			$q  = 'INSERT INTO qr4_formitems (item_page,item_title,item_text,item_hint,item_type,item_req,item_confirm,item_verify,item_verify_limit,item_verify_msg,item_depend_item,item_match_item,ordering,published) ';
			$q .= 'VALUES ("'.$info->item_page.'","'.$info->item_title.'","'.$this->db->getEscaped($info->item_text).'","'.$this->db->getEscaped($info->item_hint).'","'.$info->item_type.'","'.$info->item_req.'","'.$info->item_confirm.'","'.$info->item_verify.'","';
			$q .= $info->item_verify_limit.'","'.$info->item_verify_msg.'","'.$info->item_depend_item.'","'.$info->item_match_item.'","'.$ordering.'","'.$info->published.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page); 
				$app->redirect();
				return 0;
			}
			
			$item_id=$this->db->insertid();
			
			if ($info->item_type == "rad" || $info->item_type == "mcb" || $info->item_type == "dds") {
				$qo = "SELECT * FROM qr4_formitems_opts WHERE opt_item = ".$info->item_id. ' ORDER BY ordering';
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
						$app->setRedirect('optlist','display','&form='.$form.'&page='.$page); 
						$app->redirect();
						return 0;
					}
					$opt_id=$this->db->insertid();
				}
			}
		}
		$app->setError('Item(s) Copied', 'message');
		$app->setRedirect('itemlist','display','&form='.$form.'&page='.$page);  
		$app->redirect();
		
	}
	
	function getNextOrderNum($page) {
		$q='SELECT ordering FROM qr4_formitems WHERE item_page = '.$page.' ORDER BY ordering DESC LIMIT 1';
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
		$items=$this->getItemList($page,$user);
		include 'mods/itemlist/itemform.php';
	}
	function itemEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$item = JRequest::getInt( 'item', 0 );
		$items=$this->getItemList($page,$user);
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
			$q2 = 'SELECT item_id,ordering FROM qr4_formitems WHERE item_page = '.$page.' && ordering > '.$oold.' ORDER BY ordering ASC LIMIT 1';
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
			$q='UPDATE qr4_formitems SET trashed = 1 WHERE item_id IN('.$cids.')';
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
			$q='DELETE FROM qr4_formitems WHERE trashed = 1 && item_id IN('.$cids.')';
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
	function getItemInfo($item) {
		$q = 'SELECT * FROM qr4_formitems WHERE item_id = '.$item;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getItemList($page,$user) {
		$q2  = 'SELECT * FROM qr4_formitems as fi ';
		$q2 .= 'WHERE fi.item_page = '.$page.' ';
		$q2 .= 'ORDER BY ordering ASC';
		$this->db->setQuery($q2); 
		$iteml = $this->db->loadObjectList();
		foreach ($iteml as &$it) {
			$q4  = 'SELECT COUNT(*) FROM qr4_formitems_opts WHERE opt_item = '.$it->item_id;
			$q4 .= ' GROUP BY opt_item';
			$this->db->setQuery($q4); 
			$it->opts = $this->db->loadResult(); 
			if (!$it->opts) $it->opts=0;
		}
		$items = $iteml;

		return $items;
		
	}
	
}