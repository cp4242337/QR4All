<?php
class Users {
	function Users() {
		global $dbc;
		$this->db =& JDatabase::getInstance($dbc);
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'login':
			case 'useradd':
			case 'useredit':
			case 'userclients':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}
	
	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':
				$title='Users'; break;
			case 'login':
				$title='Login';	break;
			case 'useradd':
				$title='Add User';	break;
			case 'useredit':
				$title='Edit User';	break;
			case 'userclients':
				$title='User Clients';	break;
		}		
		return $title;
	}
	
	function getSubMenu($task) {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl_root) {
				echo '<li><a href="index.php?mod=users&task=adduser">Add User</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'trash\');">Trash</a></li>';
				echo '<li><a href="#" onclick="allTask(\'untrash\');">Restore</a></li>';
			}
		}
		if ($task == 'useradd' || $task == 'useredit') {
			if ($user->lvl_root) echo '<li><a href="index.php?mod=users">Cancel</a></li>';
			if ($user->lvl_root) echo '<li><a href="#" onclick="document.userform.validate();">Save User</a></li>';
		}
		if ($task == 'userclients') {
			if ($user->lvl_root) echo '<li><a href="index.php?mod=users">Users</a></li>';
				echo '<li><a href="#" onclick="allTask(\'haveclient\');">Yes</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unhaveclient\');">No</a></li>';
		}
		echo '</ul>';
	}
	
	function display() {
		global $user;
		if (!$user->lvl_root) {
			echo 'You should not be here';
		} else {
			$users=$this->getUsers();
			$users=$this->getUsersClients($users);
			include 'mods/users/default.php';
		}

	}
	function login() {
		
		include 'mods/users/login.php';

	}
	
	function loginuser() {
		global $user,$app;
		if ($user->loginUser($_POST['user'],$_POST['passwd'])) {
			$app->setRedirect('home');
			$app->redirect();
		} else {
			$app->setRedirect('users','login');
			$app->redirect();
		}
	}
	
	function logoutuser() {
		global $user,$app;
		$user->logoutUser();
		$app->setRedirect();
		$app->redirect();
	}
	
	function saveUser() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$user_id=JRequest::getInt('user_id',0);
		$user_name=JRequest::getString('user_name');
		$user_email=JRequest::getString('user_email');
		$user_level=JRequest::getInt('user_level',1);
		$user_fullname=JRequest::getString('user_fullname');
		$user_pass=JRequest::getString('user_pass');
		if ($user_id == 0) {
			$q = 'INSERT INTO qr4_users (usr_name,usr_fullname,usr_level,usr_email) VALUES ("'.$user_name.'","'.$user_fullname.'","'.$user_level.'","'.$user_email.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
			$user_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_users SET usr_name="'.$user_name.'",usr_fullname="'.$user_fullname.'", usr_email="'.$user_email.'",usr_level="'.$user_level.'" WHERE usr_id = '.$user_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		if ($user_pass) {
			$q2='UPDATE qr4_users SET usr_pass = "'.md5($user_pass).'" WHERE usr_id = '.$user_id;
			$this->db->setQuery($q2); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		$app->setError('User Saved', 'message');
		$app->setRedirect('users'); 
		$app->redirect();
		
	}
	
	
	function userAdd() {
		global $user;
		if ($user->lvl_root) {
			include 'mods/users/userform.php';
		}
	}
	function userEdit() {
		global $user;
		if ($user->lvl_root) {
			$userinfo=$this->getUserInfo(JRequest::getInt('user',0));
			include 'mods/users/userform.php';
		}
	}
	function userClients() {
		global $user;
		if ($user->lvl_root) {
			$clients=$this->getUserClients(JRequest::getInt('user',0));
			include 'mods/users/userclients.php';
		}
	}
	
	function adduser() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$app->setRedirect('users','useradd');
		$app->redirect();
	}
	function edituser() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		$app->setRedirect('users','useredit','&user='.(int)$cids[0]);
		$app->redirect();
	}
	function editclients() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		$app->setRedirect('users','userclients','&user='.(int)$cids[0]);
		$app->redirect();
	}
		
	function getUsers() {
		$q='SELECT * FROM qr4_users ORDER BY usr_name';
		$this->db->setQuery($q);
		return $this->db->loadObjectList();
	}
	
	function getUserInfo($user) {
		$q = 'SELECT * FROM qr4_users WHERE usr_id = '.$user;
		$this->db->setQuery($q);
		return $this->db->loadObject();
	}
	
	function getUserClients($user) {
		$q  = 'SELECT * FROM qr4_clients as cl ';
		$q .= 'LEFT JOIN qr4_usersclients as uc ON uc.cu_user = '.$user.' && uc.cu_client = cl.cl_id ';
		$q .= 'WHERE cl.published = 1';
		$this->db->setQuery($q);
		return $this->db->loadObjectList();
	}
	
	function getUsersClients($users) {
		foreach ($users as &$u) {
			$q  = 'SELECT * FROM qr4_usersclients as uc ';
			$q .= 'LEFT JOIN qr4_clients as cl ON uc.cu_client = cl.cl_id ';
			$q .= 'WHERE uc.cu_user = '.$u->usr_id;
			$this->db->setQuery($q);
			$u->usr_clients = $this->db->loadObjectList();
			
			$clids=array();
			foreach ($u->usr_clients as $c) { $clids[] = $c->cl_id;	}
		}
		return $users;
	}
	
	function unpublish() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_users SET published = 0 WHERE usr_level != 3 && usr_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Unpublished', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users');
			$app->redirect();
		}
	}
	
	function publish() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_users SET published = 1 WHERE usr_level != 3 && usr_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Published', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users');
			$app->redirect();
		}
	}
	
	function untrash() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_users SET trashed = 0 WHERE usr_level != 3 && usr_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Restored', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users');
			$app->redirect();
		}
	}
	
	function trash() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='UPDATE qr4_users SET trashed = 1 WHERE usr_level != 3 && usr_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Sent to Trash', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users');
			$app->redirect();
		}
	}
	
	function delete() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'user', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_users WHERE trashed = 1 && usr_level != 3 && usr_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Code(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users');
			$app->redirect();
		}
	}
	function unhaveclient() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		$euser = JRequest::getInt('user',0);
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_usersclients WHERE cu_user = '.$euser.' && cu_client IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Client Access Removed', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('users','userclients','&user='.$euser);
			$app->redirect();
		}
	}
	function haveclient() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); }
		$cids = JRequest::getVar( 'client', array(0), 'post', 'array' );
		$euser = JRequest::getInt('user',0);
		if (count($cids)) {
			foreach ($cids as $c) {
				$q='INSERT INTO qr4_usersclients (cu_user,cu_client) VALUES ('.$euser.','.$c.')';
				$this->db->setQuery($q); 
				$this->db->query();
			}
			$app->setError('Client Access Granted', 'message');
			$app->setRedirect('users','userclients','&user='.$euser);
			$app->redirect();
		}
	}
}