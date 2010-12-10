<?php

class App {
	
	var $db;
	var $sess;
	var $_redirurl = null;
	
	function App($db) {
		$this->db = $db;
		if (!$_SESSION['QR4AllAdmin']) {
			$this->startNewSession();
		} 
		$sess = $this->getSessionData($_SESSION['QR4AllAdmin']);
		$ctime=time();
		$chktime=$ctime-7200;
		if ($sess->sess_time >= $chktime) {
			$q = 'UPDATE qr4_session SET sess_time = "'.time().'" WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
			$this->db->setQuery($q); $this->db->query();
		} else {
			$q = 'DELETE FROM qr4_session WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
			$this->db->setQuery($q); $this->db->query();
			$this->setError('Session Expired','error');
			$_SESSION['QR4AllAdmin'] = md5(date("Ymdhis").rand(1,1771561));
			$q = 'INSERT INTO qr4_session (sess_id,sess_user,sess_time) VALUES ("'.$_SESSION['QR4AllAdmin'].'",0,"'.time().'")';
			$this->db->setQuery($q); $this->db->query();
			$sess=null;
		}
		$this->sess = $sess;
	}
	
	function startNewSession() {
		$_SESSION['QR4AllAdmin'] = md5(date("Ymdhis").rand(1,1771561));
		$q = 'INSERT INTO qr4_session (sess_id,sess_user,sess_time) VALUES ("'.$_SESSION['QR4AllAdmin'].'",0,"'.time().'")';
		$this->db->setQuery($q); $this->db->query();
	}
	
	function getSessionData($sessid) {
		$q = 'SELECT * FROM qr4_session WHERE sess_id = "'.$_SESSION['QR4AllAdmin'].'"';
		$this->db->setQuery($q); $sessinfo = $this->db->loadObject();
		if (!$sessinfo->sess_id) { $this->startNewSession(); return $this->getSessionData($_SESSION['QR4AllAdmin']); }
		return $sessinfo;
	}
	
	function getMainMenu() {
		global $user;
		$q = 'SELECT * FROM qr4_menu WHERE menu_lvl <= '.$user->lvl.' ORDER BY ordering';
		$this->db->setQuery($q); $menu = $this->db->loadObjectList();
		echo '<ul>';
		if ($menu) foreach ($menu as $m) {
			$needand = false;
			echo '<li';
			if ($m->menu_mod == $_REQUEST['mod'] && $m->menu_mod && $m->menu_task != 'logoutuser') echo ' class="active"';
			echo '><a href="index.php?';
			if ($m->menu_mod) { echo 'mod='.$m->menu_mod; $needand=true; }
			
			if ($m->menu_task) { if ($needand) { echo '&'; $needand=false; } echo 'task='.$m->menu_task; }
			
		
			echo '">'.$m->menu_name.'</a></li>';
		}
		echo '</ul>';
	}
	
	function setError($msg,$type) {
		$_SESSION['errormsg'] = $msg;
		$_SESSION['errortype'] = $type;
	}
	
	function setRedirect($mod=null,$task=null,$extra='') {
		$url="index.php";
		if ($mod) $url.='?mod='.$mod;
		if ($task) $url.= '&task='.$task;
		if ($mod || $task) $url .= $extra;
		$this->_redirurl = $url;
	}
	
	function redirect() {
		if ($this->_redirurl) {
			header("Location: ".$this->_redirurl); 
		}
	}
	
}

?>