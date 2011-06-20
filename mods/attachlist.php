<?php
class AttachList {
	var $db;
	
	function AttachList() {
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
			case 'display':	$title='Form Page EMail Attachments'; break;
			case 'atadd': $title='Add Attachment'; break;
			case 'atedit': $title='Edit Attachment'; break;
		}		
		return $title;
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'atadd':
			case 'atedit':
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
				echo '<li><a href="index.php?mod=attachlist&task=addat&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'&eml='.JRequest::getInt('eml',0).'">Add Attachment</a></li>';
				echo '<li><a href="#" onclick="allTask(\'publish\');">Publish</a></li>';
				echo '<li><a href="#" onclick="allTask(\'unpublish\');">Unpublish</a></li>';
			}
			echo '<li><a href="index.php?mod=femllist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'">Emails</a></li>';
		}
		if ($task == 'atadd' || $task == 'atedit') {
			if ($user->lvl_edit) echo '<li><a href="index.php?mod=attachlist&form='.JRequest::getInt('form',0).'&page='.JRequest::getInt('page',0).'&eml='.JRequest::getInt('eml',0).'">Cancel</a></li>';
			if ($user->lvl_edit) echo '<li><a href="#" onclick="document.codeform.validate();">Save Attachment</a></li>';
		}
		echo '</ul>';
		
	}
	function display() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		$attachs=$this->getAtList($eml);
		include 'mods/attachlist/default.php';

	}
	
	function saveAt() {
		global $app;
		$at_id=JRequest::getInt('at_id',0);
		$at_name=JRequest::getString('at_name');
		$at_email=JRequest::getInt('at_email',0);
		$at_page=JRequest::getString('at_page');
		$at_form=JRequest::getInt('at_form',0);
		
		if ($at_id == 0) {
			
			if($_FILES['at_file']['size'] > 0)
			{
				$fileName = $_FILES['at_file']['name'];
				$tmpName  = $_FILES['at_file']['tmp_name'];
				$fileSize = $_FILES['at_file']['size'];
				$fileType = $_FILES['at_file']['type'];
				
				$fp      = fopen($tmpName, 'r');
				$content = fread($fp, filesize($tmpName));
				$content = addslashes($content);
				fclose($fp);
			} else {
				$app->setError($_FILES['at_file']['error'], 'error'); 
				$app->setRedirect('attachlist','display','&form='.$at_form.'&page='.$at_page.'&eml='.$at_email); 
				$app->redirect();
				return 0;
			} 
			$q  = 'INSERT INTO qr4_formpages_emails_attach (at_email,at_name,at_filename,at_filetype,at_filesize,at_content) ';
			$q .= 'VALUES ("'.$at_email.'","'.$at_name.'","'.$fileName.'","'.$fileType.'","'.$fileSize.'","'.$content.'")';
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('attachlist','display','&form='.$at_form.'&page='.$at_page.'&eml='.$at_email); 
				$app->redirect();
				return 0;
			}
			$eml_id=$this->db->insertid();
		} else {
			$q  = 'UPDATE qr4_formpages_emails_attach SET at_name="'.$at_name.'" WHERE at_id = '.$at_id;
			$this->db->setQuery($q); 
			if (!$this->db->query()) { 
				$app->setError($this->db->getErrorMsg(), 'error'); 
				$app->setRedirect('attachlist','display','&form='.$at_form.'&page='.$at_page.'&eml='.$at_email); 
				$app->redirect(); 
				return 0;
			}
		}
		$app->setError('Attachment Saved', 'message');
		$app->setRedirect('attachlist','display','&form='.$at_form.'&page='.$at_page.'&eml='.$at_email);  
		$app->redirect();
		
	}
	
	function atAdd() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		include 'mods/attachlist/attachform.php';
	}
	function atEdit() {
		global $user;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		$at = JRequest::getInt( 'at', 0 );
		$atinfo=$this->getAtInfo($at);
		include 'mods/attachlist/attachform.php';
	}
	
	function addat() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		$app->setRedirect('attachlist','atadd','&form='.$form.'&page='.$page.'&eml='.$eml);
		$app->redirect();
	}
	function editat() {
		global $app;
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		$cids = JRequest::getVar( 'at', array(0), 'post', 'array' );
		$at=$cids[0];
		$app->setRedirect('attachlist','atedit','&form='.$form.'&page='.$page.'&eml='.$eml.'&at='.$at);
		$app->redirect();
	}
	function delete() {
		global $app;
		$cids = JRequest::getVar( 'at', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_formpages_emails_attach WHERE at_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Attachment(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('attachlist','display','&form='.$form.'&page='.$page.'&eml='.$eml);
			$app->redirect();
		}
	}

	function download() {
		global $app;
		$cids = JRequest::getVar( 'at', array(0), 'post', 'array' );
		$form = JRequest::getInt( 'form', 0 );
		$page = JRequest::getInt( 'page', 0 );
		$eml = JRequest::getInt( 'eml', 0 );
		$at = $cids[0];
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='SELECT * FROM qr4_formpages_emails_attach WHERE at_id = '.$at.' ';
			$this->db->setQuery($q); $data=$this->db->loadObject();
			header("Content-length: $data->at_filesize");
			header("Content-type: $data->at_filetype");
			header("Content-Disposition: attachment; filename=$data->at_filename");
			echo $data->at_content;
			exit;
		}
	}
	
	function getAtInfo($at) {
		$q = 'SELECT at_name,at_email,at_id FROM qr4_formpages_emails_attach WHERE at_id = '.$at;
		$this->db->setQuery($q);
		$info = $this->db->loadObject();
		return $info;
	}
	
	function getAtList($page) {
		$q2  = 'SELECT at_id,at_email,at_name,at_filename,at_filetype,at_filesize FROM qr4_formpages_emails_attach as at ';
		$q2 .= 'WHERE at.at_email = '.$page.' ';
		$q2 .= 'ORDER BY at_name ASC';
		$this->db->setQuery($q2); 
		$emls = $this->db->loadObjectList();
		return $emls;
		
	}

	
}