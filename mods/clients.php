<?php
class Clients {
	function Clients() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'clientadd':
			case 'clientedit':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}
	
	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':
				$title='Clients'; break;
			case 'clientadd':
				$title='Add Client';	break;
			case 'clientedit':
				$title='Edit Client';	break;
		}		
		return $title;
	}
	
	function getSubMenu($task) {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl >= 2) {
				echo '<li><a href="index.php?mod=clients&task=addclient">Add Client</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
		}
		if ($task == 'clientadd' || $task == 'clientedit') {
			if ($user->lvl > 1) echo '<li><a href="index.php?mod=clients">Cancel</a></li>';
			if ($user->lvl > 1) echo '<li><a href="#" onclick="document.clientform.validate();">Save Client</a></li>';
		}
		echo '</ul>';
	}
	
	function display() {
		global $user;
		if ($user->lvl == 1) {
			echo 'You should not be here';
		} else {
			$clients=$this->getClients();
			include 'mods/clients/default.php';
		}

	}
	
	function saveClient() {
		global $app,$user;
		if ($user->lvl >= 2) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cl_id=JRequest::getInt('cl_id',0);
		$cl_name=JRequest::getString('cl_name');
		if ($cl_id == 0) {
			$q = 'INSERT INTO qr4_clients (cl_name) VALUES ("'.$cl_name.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
			$cl_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_clients SET cl_name="'.$cl_name.' WHERE cl_id = '.$cl_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		$app->setError('Client Saved', 'message');
		$app->setRedirect('clients'); 
		$app->redirect();
		
	}
	
	
	function clientAdd() {
		global $user;
		if ($user->lvl >= 2) {
			include 'mods/clients/clientform.php';
		}
	}
	function clientEdit() {
		global $user;
		if ($user->lvl >= 2) {
			$clientinfo=$this->getClientInfo(JRequest::getInt('client',0));
			include 'mods/clients/clientform.php';
		}
	}
	
	function addclient() {
		global $app,$user;
		if ($user->lvl == 1) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$app->setRedirect('clients','clientadd');
		$app->redirect();
	}
	function edituser() {
		global $app,$user;
		if ($user->lvl == 1) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		$app->setRedirect('clients','clientedit','&client='.(int)$cids[0]);
		$app->redirect();
	}
	
		
	function getClients() {
		$q='SELECT * FROM qr4_clients ORDER BY cl_name';
		$this->db->setQuery($q);
		return $this->db->loadObjectList();
	}
	
	function getClientInfo($client) {
		$q = 'SELECT * FROM qr4_clients WHERE cl_id = '.$client;
		$this->db->setQuery($q);
		return $this->db->loadObject();
	}
	
	function unpublish() {
		global $app,$user;
		if ($user->lvl == 1) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_clients SET published = 0 WHERE cl_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Client(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('clients');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app,$user;
		if ($user->lvl == 1) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_clients SET published = 1 WHERE cl_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Client(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('clients');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app,$user;
		if ($user->lvl != 3) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_clients WHERE cl_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Client(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('clients');
			$app->redirect();
		}
	}
}