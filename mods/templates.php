<?php
class Templates {
	function Templates() {
		global $dbc, $user, $app;
		$this->db =& JDatabase::getInstance($dbc);
		if ($user->type == 'exp' || $user->type == 'paid' || $user->type == 'trial' || $user->type=="ext") {
			$app->setError('Unauthorized Access', 'error');
			$app->setRedirect('home'); 
			$app->redirect();
		}
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
			case 'tmpladd':
			case 'tmpledit':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}
	
	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':
				$title='Templates'; break;
			case 'tmpladd':
				$title='Add Template';	break;
			case 'tmpledit':
				$title='Edit Template';	break;
		}		
		return $title;
	}
	
	function getSubMenu($task) {
		global $user;
		echo '<ul>';
		if ($task == 'display') {
			if ($user->lvl_admin) {
				echo '<li><a href="index.php?mod=templates&task=addtmpl">Add Template</a></li>';
			}
		}
		if ($task == 'tmpladd' || $task == 'tmpledit') {
			if ($user->lvl_admin) echo '<li><a href="index.php?mod=templates">Cancel</a></li>';
			if ($user->lvl_admin) echo '<li><a href="#" onclick="document.tmplform.validate();">Save Template</a></li>';
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
			$templates=$this->getTemplates();
			include 'mods/templates/default.php';
		}

	}
	
	function saveTmpl() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0; }
		$tmpl_id=JRequest::getInt('tmpl_id',0);
		$tmpl_name=JRequest::getString('tmpl_name');
		$tmpl_url=JRequest::getString('tmpl_url');
		$tmpl_type=JRequest::getString('tmpl_type');
		if ($tmpl_id == 0) {
			$q = 'INSERT INTO qr4_templates (tmpl_name,tmpl_url,tmpl_type) VALUES ("'.$tmpl_name.'","'.$tmpl_url.'","'.$tmpl_type.'")';
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
			$cl_id=$this->db->insertid();
		} else {
			$q = 'UPDATE qr4_templates SET tmpl_name="'.$tmpl_name.'", tmpl_url = "'.$tmpl_url.'", tmpl_type = "'.$tmpl_type.'" WHERE tmpl_id = '.$tmpl_id;
			$this->db->setQuery($q); if (!$this->db->query()) { $app->setError($this->db->getErrorMsg(), 'error'); $app->setRedirect('codelist'); $app->redirect(); }
		}
		$app->setError('Template Saved', 'message');
		$app->setRedirect('templates'); 
		$app->redirect();
		
	}
	
	function tmplAdd() {
		global $user;
		if ($user->lvl_admin) {
			include 'mods/templates/tmplform.php';
		}
	}
	function tmplEdit() {
		global $user;
		if ($user->lvl_admin) {
			$tmplinfo=$this->getTemplateInfo(JRequest::getInt('tmpl',0));
			include 'mods/templates/tmplform.php';
		}
	}
	
	function addtmpl() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
		$app->setRedirect('templates','tmpladd');
		$app->redirect();
	}
	function edittmpl() {
		global $app,$user;
		if (!$user->lvl_admin) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
		$cids = JRequest::getVar( 'tmpl', array(0), 'post', 'array' );
		$app->setRedirect('templates','tmpledit','&tmpl='.(int)$cids[0]);
		$app->redirect();
	}
	
	function getTemplates() {
		$q='SELECT * FROM qr4_templates ORDER BY tmpl_type,tmpl_name';
		$this->db->setQuery($q);
		$data=$this->db->loadObjectList();
		return $data;
	}
	
	function getTemplateInfo($tmpl) {
		$q = 'SELECT * FROM qr4_templates WHERE tmpl_id = '.$tmpl;
		$this->db->setQuery($q);
		return $this->db->loadObject();
	}
	
	function delete() {
		global $app,$user;
		if (!$user->lvl_root) { $app->setError('No Access', 'error'); $app->setRedirect('home'); $app->redirect(); return 0;  }
		$cids = JRequest::getVar( 'tmpl', array(0), 'post', 'array' );
		if (count($cids)) {
			$cids = implode( ',', $cids );
			$q='DELETE FROM qr4_templates WHERE tmpl_id IN('.$cids.')';
			$this->db->setQuery($q); 
			if ($this->db->query()) {
				$app->setError('Template(s) Deleted', 'message');
			} else {
				$app->setError($this->db->getErrorMsg(), 'error');
			}
			$app->setRedirect('templates');
			$app->redirect();
		}
	}
}