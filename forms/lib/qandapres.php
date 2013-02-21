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

$formid = JRequest::getVar('form','0');
$db = JDatabase::getInstance($dbc);
session_start();
$qc = 'SELECT * FROM qr4_qa_questions WHERE qa_answered = 0 && qa_published = 1 && qa_form = '.$formid.' ORDER BY qa_order DESC';
$db->setQuery( $qc );
$qslist = $db->loadObjectList();
if ($qslist) {
	echo '<h2>Asked</h2>';
	echo '<ul>';
	foreach ($qslist as $q) {
		echo '<li>'.$q->qa_question.'<br /><em>-'.$q->qa_who.'</em><br /><br /></li>';
	}
	echo '</ul>';
}
$db = JDatabase::getInstance($dbc);
session_start();
$qc = 'SELECT * FROM qr4_qa_questions WHERE qa_answered = 1 && qa_published = 1 && qa_form = '.$formid.' ORDER BY qa_order DESC';
$db->setQuery( $qc );
$qalist = $db->loadObjectList();
if ($qalist) {
	if ($qslist) echo '<hr size="1">';
	echo '<h2>Answered</h2>';
	echo '<ul>';
	foreach ($qalist as $q) {
		echo '<li>'.$q->qa_question.'<br /><em>-'.$q->qa_who.'</em><br /><br /></li>';
	}
	echo '</ul>';
}
?>