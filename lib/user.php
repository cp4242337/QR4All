<?php
class User {
	var $id = 0; // the current user's id
	var $lvl_basic = 0;
	var $lvl_edit = 0;
	var $lvl_admin = 0;
	var $lvl_root = 0;
	var $lvl_order=0;
	var $db;
	var $name = '';
	var $fullname = '';
	var $type = '';
	var $expdate = '';
	var $lvl = 0;
	
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
		$sql  = 'SELECT * FROM qr4_users as u ';
		$sql .= 'RIGHT JOIN qr4_userlvels as l ON u.usr_level = l.lvl_id ';
		$sql .= 'WHERE u.usr_name = "'.$username.'" AND u.usr_pass = "'.$password.'"'; 
		$this->db->setQuery($sql); $res = $this->db->loadObject();
		
		if ( $res->usr_id ) {
			$this->id = $res->usr_id;
			$this->lvl_basic = $res->lvl_basic;
			$this->lvl_edit = $res->lvl_edit;
			$this->lvl_admin = $res->lvl_admin;
			$this->lvl_root = $res->lvl_root;
			$this->lvl_order = $res->lvl_order;
			$this->name=$res->usr_name;
			$this->fullname=$res->usr_fullname;
			$this->type=$res->usr_type;
			$this->expdate=$res->usr_expdate;
			$this->lvl=$res->usr_level;
			if ($res->published) { $this->_updateSession();	return true;}
			else { $this->_logout('Account Disabled','error'); return false; }
			if (strtotime($this->expdate."+ 1 day") <= strtotime("now") || $this->expdate == "0000-00-00") { $this->_updateSession();	return true;}
			else { $this->_logout('Account Expired','error'); return false; }
		} else {
			$this->_logout('Incorect Username/Password','error');
			return false;
		}
	} 
	function getUser($uid) {
		$sql  = 'SELECT * FROM qr4_users as u ';
		$sql .= 'RIGHT JOIN qr4_userlvels as l ON u.usr_level = l.lvl_id ';
		$sql .= 'WHERE u.usr_id = "'.$uid.'"'; 
		$this->db->setQuery($sql); $res = $this->db->loadObject();
		if ( $res->usr_id ) {
			$this->id = $res->usr_id;
			$this->lvl_basic = $res->lvl_basic;
			$this->lvl_edit = $res->lvl_edit;
			$this->lvl_admin = $res->lvl_admin;
			$this->lvl_root = $res->lvl_root;
			$this->lvl_order = $res->lvl_order;
			$this->name=$res->usr_name;
			$this->fullname=$res->usr_fullname;
			$this->type=$res->usr_type;
			$this->expdate=$res->usr_expdate;
			$this->lvl=$res->usr_level;
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
		$this->fullname='';
		$this->type='';
		$this->lvl=0;
		$this->lvl_basic = 0;
		$this->lvl_edit = 0;
		$this->lvl_admin = 0;
		$this->lvl_root = 0;
		$this->lvl_order = 0;
		$this->expdate='';
		$q = 'DELETE FROM qr4_session WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
		$this->db->setQuery($q); $this->db->query();
		$app->setError($erc, $type);
	}
}
?>
