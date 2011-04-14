<?php 
/*
 * QR4All 0.7b
 * Liscensed under GPLv2
 * (C) Corona Productions
 */

include 'lib/app.php';
include 'lib/user.php';
include 'lib/filterinput.php';
include 'lib/request.php';
include 'lib/database.php';
include 'lib/database/mysql.php';
include 'lib/database/mysqli.php';

global $dbc,$user,$app;
$dbc['user'] = 'qr4all';
$dbc['password'] = 'qr4all';
$dbc['database'] = 'qr4all';
$dbc['driver'] = 'mysqli';

//$db = new JDatabase($dbc);
$db = JDatabase::getInstance($dbc);
session_start();
$app = new App($db);
$user=new User($app->sess->sess_user,$db);

$module = JRequest::getWord('mod','home');
$task = JRequest::getWord('task','display');

if (!$user->id && $task != 'loginuser') {$module='users'; $task='login';}

include 'mods/'.$module.'.php';
$mod = new $module();

if (!$mod->hasContent($task)) {
	$mod->$task();
} else {

	?>
	<html>
	<head><title>QR4All Administration: <?php echo $mod->getTitle($task); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<link href="admin.css" rel="stylesheet" type="text/css">
	<link href="scripts/datepicker/datepicker.css" rel="stylesheet" type="text/css">
	<?php 
	include 'headjava.php'; //Headers - Javascripts
	?>
	</head><body bgcolor="#000000">
	<div id="container">
		<div id="top">
			<div id="topl"><img src="qr4all.png"></div>
			<div id="topr"><a href="http://coronapro.com"><img src="coronapro.png" border="0"></a></div>
		</div>
		<div id="header">
			<div id="maintitle">Administration</div>
			<div id="menu"><?php $app->getMainMenu(); ?></div>	
		</div>
		
		<div id="subheader">
			<div id="subtitle">
				<?php echo $mod->getTitle($task); ?>
			</div>
			<div id="submenu">
				<?php $mod->getSubMenu($task); ?>
			</div>	
		</div>
		
		<?php 
		if ($_SESSION['errormsg'] && !$app->_redirurl) {
			echo '<div id="errormsg" class="'.$_SESSION['errortype'].'">'.$_SESSION['errormsg'].'</div>';
			unset($_SESSION['errormsg']);
			unset($_SESSION['errortype']);
		} 
		?>
		
		<div id="content">
			<?php $mod->$task(); ?>
		</div>
		
		<div id="footer">
			<?php 
				if ($user->id) echo 'User: '.$user->name.' | ';
				echo 'IP: '.$_SERVER['REMOTE_ADDR']; 
				echo ' | v0.7RC';
			?>
		</div>
		
		<div id="copyright">&copy;2010-2011 Corona Productions</div>
		
	</div>
	</body></html>
	<?php 
}
?>