<?php
class User {
	var $id = 0; // the current user's id
	var $lvl = 'reg';
	var $db;
	var $name = '';
	
	function User($id,$db) {
		$this->db = $db;
		$this->id = $id;
		$this->name = '';
		if ($this->id) {
			$this->getUser($this->id);
		}
	} 

	function loginUser($username, $password) {
		$username = mysql_escape_string($username);
		$password = mysql_escape_string(md5($password));
		$sql = 'SELECT * FROM qr4_users WHERE usr_name = "'.$username.'" AND usr_pass = "'.$password.'"'; 
		$this->db->setQuery($sql); $res = $this->db->loadObject();
		
		if ( $res->usr_id ) {
			$this->id = $res->usr_id;
			$this->lvl = $res->usr_level;
			$this->name=$res->usr_name;
			if ($res->published) { $this->_updateSession();	return true;}
			else { $this->_logout('Account Disabled','error'); return false; }
		} else {
			$this->_logout('Incorect Username/Password','error');
			return false;
		}
	} 
	function getUser($uid) {
		$sql = 'SELECT * FROM qr4_users WHERE usr_id = "'.$uid.'"'; 
		$this->db->setQuery($sql); $res = $this->db->loadObject();
		if ( $res->usr_id ) {
			$this->id = $res->usr_id;
			$this->lvl = $res->usr_level;
			$this->name=$res->usr_name;
		} else {
			$this->_logout('User does not exist','error');
			return false;
		}
	} 
	
	function _updateSession() {
		$q = 'UPDATE qr4_session SET sess_user = '.$this->id.' WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
		$this->db->setQuery($q); $this->db->query();
		
	}
	function logoutUser() {
		$this->_logout('You Have Been Logged Out','message');
	}
	
	function _logout($erc,$type) { 
		global $app;
		$this->id='';
		$this->name='';
		$this->lvl='';
		$q = 'DELETE FROM qr4_session WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
		$this->db->setQuery($q); $this->db->query();
		$app->setError($erc, $type);
	}
}
?>
