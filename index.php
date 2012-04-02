<?php 
/*
 * QR4All 0.9.1
 * Liscensed under GPLv2
 * (C) Corona Productions
 */

include 'settings.php';
include 'lib/factory.php';
include 'lib/loader.php';
include 'lib/object.php';
include 'lib/table.php';
include 'lib/table/session.php';
include 'lib/app.php';
include 'lib/user.php';
include 'lib/filterinput.php';
include 'lib/request.php';
include 'lib/database.php';
include 'lib/database/mysql.php';
include 'lib/database/mysqli.php';
include 'lib/session.php';
include 'lib/storage.php';

global $dbc,$user,$app;
$dbc['user'] = $settings['dbuser'];
$dbc['password'] = $settings['dbpass'];
$dbc['database'] = $settings['dbname'];
$dbc['driver'] = 'mysqli';

//$db = new JDatabase($dbc);
$db = JDatabase::getInstance($dbc);
//session_start();
$app = new App($db);
$session = JFactory::getSession();
//$user=new User($session->get('user')->userid,$db);
$user = JFactory::getUser();

$module = JRequest::getWord('mod','home');
$task = JRequest::getWord('task','display');

if (!$user->id && $task != 'loginuser') {$module='users'; $task='login';}

include 'mods/'.$module.'.php';
$mod = new $module();

//Get Template Info
if ($user->tmpl == 0) $usetmpl= $settings['template'];
else $usetmpl=$user->tmpl;
$qt  = 'SELECT * FROM qr4_templates ';
$qt .= 'WHERE tmpl_id = "'.$usetmpl.'" ';
$db->setQuery($qt);
$deftmpl = $db->loadObject();

if (!$mod->hasContent($task)) {
	$mod->$task();
} else {

	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head><title>QR4All Administration: <?php echo $mod->getTitle($task); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
	<link href="<?php echo $deftmpl->tmpl_url; ?>" rel="stylesheet" type="text/css">
	<link href="scripts/datepicker/datepicker.css" rel="stylesheet" type="text/css">
	<?php 
	include 'headjava.php'; //Headers - Javascripts
	?>
	</head><body bgcolor="#000000">
	<div id="container">
		<div id="top">
			<div id="topl"></div>
			<div id="topr"></div>
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
				if ($user->id) {
					echo 'User: '.$user->name.' | ';
					echo '<a href="index.php?mod=users&task=myaccount">My Account</a> | ';
					echo '<a href="index.php?mod=users&task=logoutuser">Logout</a> | ';
				}
				echo 'IP: '.$_SERVER['REMOTE_ADDR']; 
				echo ' | '.date("Y-m-d H:i:s"); 
				echo ' | v0.9.1';
			?>
		</div>
		
		<div id="copyright">&copy;2010-2011 Corona Productions</div>
		
	</div>
	</body></html>
	<?php 
}
?>