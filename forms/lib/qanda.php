<?php
ini_set('error_reporting', E_ALL);
// Set flag that this is a parent file
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
session_start();
$who = $db->getEscaped(JRequest::getVar('who'));
$dataid = $db->getEscaped(JRequest::getVar('dataid'));
$whodetail = $db->getEscaped(JRequest::getVar('whodetail'));
$qtext  = $db->getEscaped(JRequest::getVar('qtext'));
$form  = $db->getEscaped(JRequest::getVar('form'));
//$cookiename = 'form_'.$form.'_session';
//if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == $_SESSION[$cookiename]) { 
	$qo="SELECT qa_order FROM qr4_qa_questions WHERE qa_form = ".$form." ORDER BY qa_order DESC";
	$db->setQuery($qo);
	$non = (int)$db->loadResult() + 1;
	$qc = 'INSERT INTO qr4_qa_questions (qa_form,qa_data,qa_who,qa_whodetail,qa_question,qa_order) ';
	$qc .= 'VALUES ("'.$form.'","'.$dataid.'","'.$who.'","'.$whodetail.'","'.$qtext.'","'.$non.'")';
	$db->setQuery( $qc );
	if ($db->query()) echo 'Question Submitted';
//} else { echo "Invalid Session"; }

