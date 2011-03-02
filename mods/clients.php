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
			if ($user->lvl_admin) {
				echo '<li><a href="index.php?mod=clients&task=addclient">Add Client</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
		}
		if ($task == 'clientadd' || $task == 'clientedit') {
			if ($user->lvl_admin) echo '<li><a href="index.php?mod=clients">Cancel</a></li>';
			if ($user->lvl_admin) echo '<li><a href="#" onclick="document.clientform.validate();">Save Client</a></li>';
		}
		echo '</ul>';
	}
	
	function display() {
		global $user;
		if (!$user->lvl_admin) {
			$app->setError($this->db->getErrorMsg(), 'error'); 
			$app->setRedirect('home'); 
			$app->redirect();
			return 0;
		} else {
			$clients=$this->getClients();
			include 'mods/clients/default.php';
		}

	}
	
	function saveClient() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0; }
		$cl_id=JRequest::getInt('cl_id',0);
		$cl_name=JRequest::getString('cl_name');
		$cl_maxcodes=JRequest::getInt('cl_maxcodes',0);
		$cl_maxvids=JRequest::getInt('cl_maxvids',0);
		$cl_maxforms=JRequest::getInt('cl_maxforms',0);
		if ($cl_id == 0) {
			$q = 'INSERT INTO qr4_clients (cl_name,cl_maxcodes,cl_maxvids,cl_maxforms) VALUES ("'.$cl_name.'","'.$cl_maxcodes.'","'.$cl_maxvids.'","'.$cl_maxforms.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
			$cl_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_clients SET cl_name="'.$cl_name.'", cl_maxcodes = "'.$cl_maxcodes.'", cl_maxvids = "'.$cl_maxvids.'", cl_maxforms = "'.$cl_maxforms.'" WHERE cl_id = '.$cl_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		$app->setError('Client Saved', 'message');
		$app->setRedirect('clients'); 
		$app->redirect();
		
	}
	
	
	function clientAdd() {
		global $user;
		if ($user->lvl_admin) {
			include 'mods/clients/clientform.php';
		}
	}
	function clientEdit() {
		global $user;
		if ($user->lvl_admin) {
			$clientinfo=$this->getClientInfo(JRequest::getInt('client',0));
			include 'mods/clients/clientform.php';
		}
	}
	
	function addclient() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
		$app->setRedirect('clients','clientadd');
		$app->redirect();
	}
	function editclient() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		$app->setRedirect('clients','clientedit','&client='.(int)$cids[0]);
		$app->redirect();
	}
	
		
	function getClients() {
		$q='SELECT * FROM qr4_clients ORDER BY cl_name';
		$this->db->setQuery($q);
		$data=$this->db->loadObjectList();
		foreach ($data as &$d) {
			$q2  = 'SELECT * FROM qr4_clientcodes as cc ';
			$q2 .= 'LEFT JOIN qr4_codes as cd ON cc.clcd_code = cd.cd_id ';
			$q2 .= 'WHERE cc.clcd_client = '.$d->cl_id;
			$this->db->setQuery($q2);
			$d->numcodes = $this->db->loadObjectList();
			
			$q3  = 'SELECT * FROM qr4_clientforms as cf ';
			$q3 .= 'LEFT JOIN qr4_forms as ca ON cf.clform_form = ca.form_id ';
			$q3 .= 'WHERE cf.clform_client = '.$d->cl_id;
			$this->db->setQuery($q3);
			$d->numforms = $this->db->loadObjectList();
			
			
			$q2  = 'SELECT * FROM qr4_clientvids as cv ';
			$q2 .= 'LEFT JOIN qr4_videos as vd ON cv.clvid_vid = vd.vid_id ';
			$q2 .= 'WHERE cv.clvid_client = '.$d->cl_id;
			$this->db->setQuery($q2);
			$d->numvideos = $this->db->loadObjectList();
		}
		return $data;
	}
	
	function getClientInfo($client) {
		$q = 'SELECT * FROM qr4_clients WHERE cl_id = '.$client;
		$this->db->setQuery($q);
		return $this->db->loadObject();
	}
	
	function unpublish() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
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
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
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
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
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