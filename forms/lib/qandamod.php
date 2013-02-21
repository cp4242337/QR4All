<?php
session_start();
include 'filterinput.php';
include 'request.php';
include 'database.php';
include 'database/mysql.php';
include 'database/mysqli.php';

$dbc['user'] = 'qr4all';
$dbc['password'] = 'qr4all';
$dbc['database'] = 'qr4all';
$dbc['driver'] = 'mysqli';
$dbc['host'] = 'localhost';
$db = JDatabase::getInstance($dbc);

$canmod = false;
$formid = JRequest::getVar('form','0');
$url = '/lib/qandamod.php?action=list&form='.$formid;

$qf="SELECT * FROM qr4_forms WHERE form_id = ".$formid;
$db->setQuery($qf);
$forminfo=$db->loadObject(); 

if (md5(JRequest::getVar('password')) == $forminfo->form_password) { 
	$_SESSION['qamoderation'] = 'ICanModerateThis';
	$_SESSION['qamoderationid'] = $formid;
	//$url = '/lib/qandamod.php?action=list';
	header("Location: $url");
	exit;
}
if (isset($_SESSION['qamoderation'])) {
	if ($_SESSION['qamoderation'] && $_SESSION['qamoderationid'] == $formid) $canmod=true;
}


if ($canmod) {
	
	$action = JRequest::getVar('action','list');
	
	if ($action == 'publish') {
		$q = 'UPDATE qr4_qa_questions SET qa_published = 1 WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'unpublish') {
		$q = 'UPDATE qr4_qa_questions SET qa_published = 0 WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'answer') {
		$q = 'UPDATE qr4_qa_questions SET qa_answered = 1 WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'unanswer') {
		$q = 'UPDATE qr4_qa_questions SET qa_answered = 0 WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'down') {
		$pq = "SELECT qa_id,qa_order FROM qr4_qa_questions WHERE qa_order < ".$db->getEscaped(JRequest::getVar('o'))." ORDER BY qa_order DESC LIMIT 1";
		$db->setQuery($pq); $p=$db->loadObject();
		$q = 'UPDATE qr4_qa_questions SET qa_order = '.$p->qa_order.' WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		$q = 'UPDATE qr4_qa_questions SET qa_order = '.$db->getEscaped(JRequest::getVar('o')).' WHERE qa_id = '.$p->qa_id;
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'up') {
		$pq = "SELECT qa_id,qa_order FROM qr4_qa_questions WHERE qa_order > ".$db->getEscaped(JRequest::getVar('o'))." ORDER BY qa_order ASC LIMIT 1";
		$db->setQuery($pq); $p=$db->loadObject();
		$q = 'UPDATE qr4_qa_questions SET qa_order = '.$p->qa_order.' WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		$q = 'UPDATE qr4_qa_questions SET qa_order = '.$db->getEscaped(JRequest::getVar('o')).' WHERE qa_id = '.$p->qa_id;
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'delete') {
		$q = 'UPDATE qr4_qa_questions SET qa_published = -2 WHERE qa_id = '.$db->getEscaped(JRequest::getVar('q'));
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'save') {
		$q = 'UPDATE qr4_qa_questions SET qa_question = "'.$db->getEscaped(JRequest::getVar('qtext')).'" WHERE qa_id = '.JRequest::getVar('q');
		$db->setQuery($q); $db->query();
		//$url = '/lib/qandamod.php?action=list';
		header("Location: $url"); exit;

	}
	if ($action == 'logout') {
		session_destroy();
		$url = '/lib/qandamod.php?form='.$formid;
		header("Location: $url"); exit;

	}
	
	if ($action == 'list') {
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo '<title>Q&A Moderation</title><link rel="stylesheet" href="../aegismod.css" type="text/css" /></head><body>';
		echo '<div id="wrapper"><div id="header"></div><div id="content">';
		echo '<h2>Q&A Moderation: '.$forminfo->form_publictitle.'</h2>';
		echo '<p>Questions will appear to the presenter in the order below.</p>';
		echo '<p><a href="/lib/qandamod.php?action=list&form='.$formid.'" class="button">Refresh</a><a href="/lib/qandamod.php?action=logout&form='.$formid.'" class="button">Logout</a></p>';
		$qc  = 'SELECT * FROM qr4_qa_questions as q ';
		$qc .= 'LEFT JOIN qr4_fhits as h ON q.qa_data = h.hit_data ';
		$qc .= 'WHERE q.qa_published >= 0 && q.qa_form = '.$formid.' ORDER BY q.qa_order DESC';
		$db->setQuery( $qc );
		$qslist = $db->loadObjectList();
		$count = 0;
		$total = count($qslist);
		foreach ($qslist as $q) {
			$count++;
			echo '<div class="mod-question"><div class="mod-question-content">';
			echo '<span class="';
			if ($q->qa_published == 1 && $q->qa_answered == 0) echo ' published';
			else if ($q->qa_published == 1 && $q->qa_answered == 1) echo ' answered';
			else if ($q->qa_published == 0 && $q->qa_answered == 1) echo ' answerednp';
			else if ($q->qa_published == 0 && $q->qa_answered == 0) echo ' unpublished';
			echo '">';
			echo '<b>'.$q->qa_when.' - <em>'.$q->qa_whodetail.'</em></b></span> '.$q->hit_ipaddr.'<br />'.$q->qa_question.'';
			echo '<span class="useragent"><br><br><em>'.$q->hit_useragent.'</em></span>';
			echo '</div>'; 
			echo '<div class="mod-question-actions">';
			//Publish
			if ($q->qa_published == 0) echo '<a href="/lib/qandamod.php?action=publish&form='.$formid.'&q='.$q->qa_id.'" class="button">Publish</a>';
			else if ($q->qa_published == 1) echo '<a href="/lib/qandamod.php?action=unpublish&form='.$formid.'&q='.$q->qa_id.'" class="button">UnPub</a>';
			//Answer
			if ($q->qa_answered == 0) echo '<a href="/lib/qandamod.php?action=answer&form='.$formid.'&q='.$q->qa_id.'" class="button">Answer</a>';
			else echo '<a href="/lib/qandamod.php?action=unanswer&form='.$formid.'&q='.$q->qa_id.'" class="button">Ask</a>';
			echo '<a href="/lib/qandamod.php?action=edit&form='.$formid.'&q='.$q->qa_id.'" class="button">Edit</a>';
			//echo '<a href="/lib/qandamod.php?action=delete&form='.$formid.'&q='.$q->qa_id.'" class="button" onclick="return confirm(\'Are you sure you want to delete this question?\');">Delete</a>';
			if ($count != $total) echo '<a href="/lib/qandamod.php?action=down&form='.$formid.'&q='.$q->qa_id.'&o='.$q->qa_order.'" class="buttons");">Down</a>';
			else echo '<span class="ibuttons">Down</span>';
			if ($count !=1 )echo '<a href="/lib/qandamod.php?action=up&form='.$formid.'&q='.$q->qa_id.'&o='.$q->qa_order.'" class="buttons");">Up</a>';
			else echo '<span class="ibuttons">Up</span>';
			echo '<br /><span class="number';
			if ($q->qa_published == 1 && $q->qa_answered == 0) echo ' published';
			else if ($q->qa_published == 1 && $q->qa_answered == 1) echo ' answered';
			else if ($q->qa_published == 0 && $q->qa_answered == 1) echo ' answerednp';
			else if ($q->qa_published == 0 && $q->qa_answered == 0) echo ' unpublished';
			echo '">ID#'.$q->qa_id.'</span>';
			echo '</div></div>';
			echo '<div style="clear:both;"></div>';
		}
		echo '<p><a href="/lib/qandamod.php?action=list&form='.$formid.'" class="button">Refresh</a><a href="/lib/qandamod.php?action=logout&form='.$formid.'" class="button">Logout</a></p>';
		echo '</div><div id="footer"></div><div style="clear:both;"></div>';
		echo '</body></html>';
	}
	if ($action == 'edit') {
		
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo '<title>Q&A Moderation: Edit Question</title><link rel="stylesheet" href="../aegismod.css" type="text/css" /></head><body>';
		echo '<div id="wrapper"><div id="header"></div><div id="content">';
		echo '<h2>Q&A Moderation: '.$forminfo->form_publictitle.'</h2>';
		$qc = 'SELECT * FROM qr4_qa_questions WHERE qa_id = '.JRequest::getVar('q');
		$db->setQuery( $qc );
		$q = $db->loadObject();
		echo '<form action="qandamod.php?action=save&form='.$formid.'&q='.$q->qa_id.'" method="post">';
		echo '<textarea name="qtext" class="editfield">'.$q->qa_question.'</textarea><br />';
		echo '<input type="submit" name="submitpass" value="Save" class="button"></form>';
		echo '</div><div id="footer"></div><div style="clear:both;"></div>';
		echo '</body></html>';
	}
} else if ($forminfo) {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo '<title>Q&A Moderation Login</title><link rel="stylesheet" href="../aegismod.css" type="text/css" /></head><body>';
		echo '<div id="wrapper"><div id="header"></div><div id="content">';
		echo '<h2>Q&A Moderation Login: '.$forminfo->form_publictitle.'</h2>';
		echo '<form action="" method="post"><b>Enter Password:</b> <input name="password" type="password" class="inputfield">';
		echo '<input type="submit" name="submitpass" value="Enter" class="button">';
		echo '<input type="hidden" name="form" value="'.$formid.'"></form>';
		echo '</div><div id="footer"></div><div style="clear:both;"></div>';
		echo '</body></html>';

} else {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		echo '<title>ERROR</title><link rel="stylesheet" href="../aegismod.css" type="text/css" /></head><body>';
		echo '<div id="wrapper"><div id="header"></div><div id="content">';
		echo '<h1 align="center">FORM ID MISSING</h1>';
		echo '</div><div id="footer"></div><div style="clear:both;"></div>';
		echo '</body></html>';

}

?>



