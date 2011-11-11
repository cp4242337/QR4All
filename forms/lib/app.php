<?php

class App {
	
	var $db;
	var $sess;
	var $_redirurl = null;
	var $sessionTime = 30;
	
	function App($db,$name) {
		$jsession =& $this->_createSession('QR4AllForms',$name);
		
		
		$this->sess = $jsession;
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
		if (!$storage->insert( $session->getId())) {
			
		}

		return $session;
	}
	
}

?>