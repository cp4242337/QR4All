<?php

class App {
	
	var $db;
	var $sess;
	var $_redirurl = null;
	var $sessionTime = 30;
	
	function App($db) {
		$session =& $this->_createSession('QR4AllAdmin');
		
		$this->db = $db;
		/*
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
			//$this->startNewSession();
			$this->setRedirect();
			$this->redirect();
			$sess=null;
		}
		*/
		$this->sess = $session;
	}
	
	/*
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
	*/
	
	function getMainMenu($showall = true) {
		global $user;
		$q = 'SELECT * FROM qr4_menu WHERE menu_parent = 0 && published = 1 ORDER BY ordering';
		$this->db->setQuery($q); $menu = $this->db->loadObjectList();
		echo '<ul id="nav">';
		if ($menu) foreach ($menu as $m) {
			$lvl = $m->menu_lvl; 
			$type="menu_".$user->type;
			if ($user->$lvl && $m->$type) {
				$needand = false;
				echo '<li';
				//if ($m->menu_mod == $_REQUEST['mod'] && $m->menu_mod && $m->menu_task != 'logoutuser') echo ' class="active"';
				echo '><a href="index.php?';
				if ($m->menu_mod) { echo 'mod='.$m->menu_mod; $needand=true; }
				if ($m->menu_task) { if ($needand) { echo '&'; $needand=false; } echo 'task='.$m->menu_task; }
				echo '">'.$m->menu_name.'</a>';
				$qp = 'SELECT * FROM qr4_menu WHERE menu_parent = '.$m->menu_id.'  && published = 1 ORDER BY ordering';
				$this->db->setQuery($qp); $menup = $this->db->loadObjectList();
			
				if ($menup && $showall) { 
					echo '<ul>';
					foreach ($menup as $mp) {
						$lvl = $m->menu_lvl; 
						if ($user->$lvl) {
							$needand = false;
							echo '<li';
							if ($mp->menu_mod == $_REQUEST['mod'] && $mp->menu_mod && $mp->menu_task != 'logoutuser') echo ' class="active"';
							echo '><a href="index.php?';
							if ($mp->menu_mod) { echo 'mod='.$mp->menu_mod; $needand=true; }
							if ($mp->menu_task) { if ($needand) { echo '&'; $needand=false; } echo 'task='.$mp->menu_task; }
							echo '">'.$mp->menu_name.'</a>';
							echo '</li>';
						}
					}
					echo '</ul>';
				}
				echo '</li>';
			}
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
	
	function &_createSession( $name )
	{
		$options = array();
		$options['name'] = $name;
		$session =& JFactory::getSession($options);

		$storage = & JTable::getInstance('session');
		$storage->purge($session->getExpire());

		// Session exists and is not expired, update time in session table
		if ($storage->load($session->getId())) {
			$storage->update();
			return $session;
		}

		//Session doesn't exist yet, initalise and store it in the session table
		$session->set('user', new User());
		
		if (!$storage->insert( $session->getId())) {
			
		}

		return $session;
	}
	
}

?>