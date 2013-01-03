<?php
/*
 * QR4All Forms 0.9.3
 * Liscensed under GPLv2
 * (C) Corona Productions
 */

include 'lib/factory.php';
include 'lib/loader.php';
include 'lib/object.php';
include 'lib/table.php';
include 'lib/table/session.php';
include 'lib/filterinput.php';
include 'lib/request.php';
include 'lib/database.php';
include 'lib/database/mysql.php';
include 'lib/database/mysqli.php';
include 'lib/session.php';
include 'lib/storage.php';
include('Browscap.php');
include('ipinfodb.class.php');

//Init Database
global $dbc;
$dbc['user'] = 'qr4all';
$dbc['password'] = 'qr4all';
$dbc['database'] = 'qr4all';
$dbc['driver'] = 'mysqli';
$dbc['host'] = 'localhost';
$db = JDatabase::getInstance($dbc);

//Get Form Info
$form = JRequest::getVar('c',null);
if (!$form) { echo 'No Form Specified'; exit; }
$qf  = 'SELECT * FROM qr4_forms as f ';
$qf .= 'RIGHT JOIN qr4_templates as t ON f.form_template=t.tmpl_id ';
$qf .= 'WHERE f.form_code = "'.$form.'"  && f.published = 1 && f.trashed = 0';
$db->setQuery($qf);
$forminfo = $db->loadObject();
if (!$forminfo->published || !$forminfo) { echo 'Form not found'; exit; }

//Setup Session
$options = array();
$options['name'] = $form;
$options['expire'] = ($forminfo->form_sessiontime);
$session =& JFactory::getSession($options);
$storage = & JTable::getInstance('session');
$storage->purge($session->getExpire(),$form);

$cookiename = 'form_'.$form.'_session';

//have we been here before??
if (!$storage->load($session->getId())) { //if not set up cookie and sessin id
	$session->restart();
	$storage->insert( $session->getId());
	$session->set('step',NULL);
	$session->set($cookiename,NULL);
	$sessid = $session->getId();
	$session->set($cookiename,$sessid);
	$stable = & JTable::getInstance('session');
	$stable->load( $session->getId() );
	$stable->form = $form;
	$stable->update();
	
	//set up dataid
	$qi = 'INSERT INTO qr4_formdata (data_form,data_ip,data_session) VALUES ("'.$forminfo->form_id.'","'.$_SERVER['REMOTE_ADDR'].'","'.$sessid.'")';
	$db->setQuery($qi);
	$db->query();
	$dataid = $db->insertid();
	
	//log hit
	try {
		$bc = new Browscap("bc_cache"); 
		$browser = $bc->getBrowser(null,true);
	} catch (Browscap_Exception $exp) {
	}
	$ipaddr=$_SERVER['REMOTE_ADDR'];
	$ipinfodb = new ipinfodb;
	$ipinfodb->setKey('81352021746a636eadfd91bb40315d2846de4ae57fe07fbbc88f84a871d24b7f');
	$locations = $ipinfodb->getGeoLocation($ipaddr);
	$errors = $ipinfodb->getError();
	if ($locations['Status']=='OK') {
		$city=$locations['City'];
		$region=$locations['RegionName'];
		$lat=$locations['Latitude'];
		$long=$locations['Longitude'];
		$country=$locations['CountryName'];
		$countrycode=$locations['CountryCode'];
		$timezone=$locations['TimezoneName'];
	}
	$qh  = 'INSERT INTO qr4_fhits (hit_form,hit_data,hit_ipaddr,hit_useragent,hit_browser,hit_browserver,hit_platform,hit_ismobile,hit_lat,hit_long,hit_city,hit_region,hit_country,hit_countrycode,hit_timezone) ';
	$qh .= 'VALUES ('.$forminfo->form_id.','.$dataid.',"'.$ipaddr.'","'.$db->getEscaped($_SERVER['HTTP_USER_AGENT']).'","'.$browser['browser'].'","'.$browser['version'].'","'.$browser['platform'].'","'.$browser['ismobiledevice'].'","'.$lat.'","'.$long.'","'.$city.'","'.$region.'","'.$country.'","'.$countrycode.'","'.$timezone.'")';
	$db->setQuery($qh);
	$db->query($qh);
} else { //if we have check session id and get dataid
	$storage->update();
	$sessid = $session->get($cookiename);
	//get dataid
	$qr = 'SELECT data_id FROM qr4_formdata WHERE data_session = "'.$sessid.'"';
	$db->setQuery($qr);
	$dataid = $db->loadResult();
	if (!$dataid) {
		setcookie($cookiename,'',(time()-60*60*24));
		$session->destroy();
		header("Location:$forminfo->form_code");
	}
	
}

if (!$session->get('step',0)) { $curstep = 1; $session->set('step',1); }
else { $curstep = $session->get('step',0); }

$qp = 'SELECT * FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && published = 1  && trashed = 0 ORDER BY ordering ASC LIMIT '.($curstep - 1).',1';
$db->setQuery($qp);
$pageinfo = $db->loadObject();

//Handle submission of page
if ($pagesub=JRequest::getVar("pagesubmit",0)) {
	if ($pagesub != $pageinfo->page_id) { echo 'Incorrect Page ID'; exit; }
	//save data
	$qi = 'SELECT * FROM qr4_formitems WHERE item_page = '.$pageinfo->page_id.' && published = 1 ORDER BY ordering';	
	$db->setQuery($qi);
	$items = $db->loadObjectList();
	if (sizeof($items)) {
		//remove old answers
		$itemids = array();
		foreach ($items as $item) { $itemids[] = $item->item_id; }
		$qra = 'DELETE FROM qr4_formdata_answers WHERE ans_question IN ('.implode(",",$itemids).') && ans_data = '.$dataid;
		$db->setQuery($qra);
		$db->query();
		//insert new answers
		foreach ($items as $item) {
			$answer = '';
			switch ($item->item_type) {
				case "txt":
				case "tbx":
				case "eml":
				case "phn":
				case "rad":
				case "dds":
				case "cbx":
				case "hdn":
					$answer = $db->getEscaped(JRequest::getVar("i".$item->item_id."f",""));
					break;
				case "mcb":
					$answer = implode(" ",JRequest::getVar("i".$item->item_id."f",""));
					break;
				case "dob":
					$fmonth = $db->getEscaped(JRequest::getInt("i".$item->item_id."f",""));
					$fday = $db->getEscaped(JRequest::getInt("i".$item->item_id."f_day",""));
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$answer = $fmonth.$fday;
					break;
			}
			if ($item->item_type != 'msg') {
				$qia = 'INSERT INTO qr4_formdata_answers (ans_data,ans_question,ans_answer) VALUES ('.$dataid.','.$item->item_id.',"'.$answer.'")';
				$db->setQuery($qia);
				$db->query();
			}
		}
	}
	//page action
	if ($pageinfo->page_action == 'submitmail') {
		include 'lib/swift/swift_required.php';
		$transport = Swift_SendmailTransport::newInstance();
		//get emails
		$qe = 'SELECT * FROM qr4_formpages_emails WHERE eml_page = '.$pageinfo->page_id.' && published = 1 && trashed = 0';
		$db->setQuery($qe);
		$emldata = $db->loadObjectList();
		foreach ($emldata as $eml) {
			//get to name and address
			$qte = 'SELECT ans_answer FROM qr4_formdata_answers WHERE ans_data = '.$dataid.' && ans_question = '.$eml->eml_toaddr;
			$db->setQuery($qte);
			$toaddr = $db->loadResult();
			$qte = 'SELECT ans_answer FROM qr4_formdata_answers WHERE ans_data = '.$dataid.' && ans_question = '.$eml->eml_toname;
			$db->setQuery($qte);
			$toname = $db->loadResult();
			if ($toaddr) {
				$mailer = Swift_Mailer::newInstance($transport);
				$logger = new Swift_Plugins_Loggers_ArrayLogger();
				$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
				$message = Swift_Message::newInstance();
				$message->setSubject($eml->eml_subject);
				$message->setFrom(array($eml->eml_fromaddr => $eml->eml_fromname));
				$message->setTo(array($toaddr => $toname));
				$dba = 'SELECT * FROM qr4_formpages_emails_attach WHERE at_email = '.$eml->eml_id;
				$db->setQuery($dba);
				$atlist = $db->loadObjectList();
				foreach ($atlist as $at) {
					$attachment = Swift_Attachment::newInstance($at->at_content, $at->at_filename, $at->at_filetype);
					$message->attach($attachment);
				}
				$body = $eml->eml_content;
				$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && trashed = 0 && published = 1 && ordering < '.($pageinfo->ordering+1);
				$db->setQuery($qpp);
				$prevpages = $db->loadResultArray();
				if ($prevpages) {
					$qi  = 'SELECT * FROM qr4_formitems as i ';
					$qi .= 'RIGHT JOIN qr4_formdata_answers as a ON i.item_id = a.ans_question '; //future data retrevial
					$qi .= 'WHERE item_page IN ('.implode(",",$prevpages).') && published = 1 && a.ans_data = '.$dataid.' ';
					$qi .= 'ORDER BY i.ordering';	
					$db->setQuery($qi);
					
					$items = $db->loadObjectList();
					foreach ($items as $item) {
						$answer = "";
						switch ($item->item_type) {
							case "txt":
							case "tbx":
							case "eml":
							case "phn":
								$answer = $item->ans_answer;
								break;
							case "rad":
							case "dds":
								$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id = '.$item->ans_answer;
								$db->setQuery($qa);
								$answer = $db->loadResult();
								break;
							case "mcb":
								$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id IN ('.str_replace(" ",",",$item->ans_answer).')';
								$db->setQuery($qa);
								$answer = implode("<br>",$db->loadResultArray());
								break;
							case "cbx":
								$answer = ($item->ans_answer == "on" ? 'Yes' : 'No');
								break;
								
						}
						$body = str_replace("{i".$item->item_id."}",$answer,$body);
					}
				}
				$message->setBody($body,'text/html');
				$mailer->send($message);
				$dbl = 'INSERT INTO qr4_formpages_emails_logs (log_eml,log_msg) VALUES ("'.$eml->eml_id.'","'.$db->getEscaped($logger->dump()).'")';
				$db->setQuery($dbl);
				$db->query();
			}
			
		}
	}
	// --- EMAIL ACTION TO GO HERE ---
	//set end if submitting
	if ($pageinfo->page_action == 'submit' || $pageinfo->page_action == 'submitmail' || $pageinfo->page_action == 'redirect') {
		$qe = 'UPDATE qr4_formdata SET data_end = "'.date("Y-m-d H:i:s").'" WHERE data_id = '.$dataid;
		$db->setQuery($qe);
		$db->query();
	}
	
	if ($pageinfo->page_action == 'reset') {
		$session->restart();
	}
	
	if ($pageinfo->page_action != "redirect") {
		//go to next page
		$session->set('step',$curstep+1);
		header("Location:$forminfo->form_code");
	} else {
		header("Location:$pageinfo->page_redirurl");
	}
}

//set title
$title=$forminfo->form_publictitle.' - '.$pageinfo->page_title;

//start page
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
echo '<html>'."\n";
echo '<head><title>'.$title.'</title>'."\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n"; 
echo $forminfo->form_header."\n";
echo '<link rel="stylesheet" href="'.$forminfo->tmpl_url.'" type="text/css" />'."\n";
echo '<script src="scripts/jquery.js" type="text/javascript"></script>'."\n";
echo '<script src="scripts/jquery.validate.js" type="text/javascript"></script>'."\n";
echo '<script src="scripts/additional-methods.js" type="text/javascript"></script>'."\n";
echo '<script src="scripts/jquery.metadata.js" type="text/javascript"></script>'."\n";
echo '<script src="scripts/jquery.simplemodal.js" type="text/javascript"></script>'."\n";
echo '</head>'."\n";
if ($forminfo->form_body) echo '<body '.$forminfo->form_body.'>'."\n";
else echo '<body>'."\n";
echo '<div id="wrapper">'."\n";
echo '<div id="header"></div>'."\n";
echo '<div id="content">'."\n";


//************
// Page action
//************

// Page Content
if ($pageinfo->page_type=="text" || $pageinfo->page_type=="form" || $pageinfo->page_type=="confirm") {
	$pagecontent = $pageinfo->page_content;
	$pagecontent = str_replace("{dataid}",$dataid,$pagecontent);
	$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && trashed = 0 && published = 1 && ordering < '.$pageinfo->ordering;
	$db->setQuery($qpp);
	$prevpages = $db->loadResultArray();
	if ($prevpages) {
		$qi  = 'SELECT * FROM qr4_formitems as i ';
		$qi .= 'RIGHT JOIN qr4_formdata_answers as a ON i.item_id = a.ans_question '; //future data retrevial
		$qi .= 'WHERE item_page IN ('.implode(",",$prevpages).') && published = 1 && a.ans_data = '.$dataid.' ';
		$qi .= 'ORDER BY i.ordering';	
		$db->setQuery($qi);
		$items = $db->loadObjectList();
		foreach ($items as $item) {
			$answer = "";
			switch ($item->item_type) {
				case "txt":
				case "tbx":
				case "eml":
				case "phn":
				case "dob":
					$answer = $item->ans_answer;
					break;
				case "rad":
				case "dds":
					$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id = '.$item->ans_answer;
					$db->setQuery($qa);
					$answer = $db->loadResult();
					break;
				case "mcb":
					$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id IN ('.str_replace(" ",",",$item->ans_answer).')';
					$db->setQuery($qa);
					$answer = implode("<br>",$db->loadResultArray());
					break;
				case "cbx":
					$answer = ($item->ans_answer == "on" ? 'Yes' : 'No');
					break;
					
			}
			$pagecontent = str_replace("{i".$item->item_id."}",$answer,$pagecontent);
		}
	}
	echo $pagecontent."\n";
}

//start form
if ($pageinfo->page_action != 'none') {
	echo '<form action="" method="post" name="qr4form" id="qr4form">'."\n";
	
}

// Form Page
if ($pageinfo->page_type=="form") {
	$qi = 'SELECT * FROM qr4_formitems WHERE item_page = '.$pageinfo->page_id.' && published = 1 ORDER BY ordering';	
	$db->setQuery($qi);
	$items = $db->loadObjectList();
	
	echo '<div id="form">';
	//show items
	foreach ($items as $item) {
		echo '<div class="form-row">';
		//Question text if not a single checkbox
		echo '<div class="form-row-title">';
		if ($item->item_type != 'cbx' && $item->item_type != 'hdn' && $item->item_type != 'msg') {
			echo $item->item_text;
		}
		echo '</div>'."\n";
		if ($item->item_type != 'msg') echo '<div class="form-row-field">';
		else echo '<div class="form-row-message">';
		
		if ($item->item_type == 'msg') {
			echo $item->item_text;
		}
		
		//output checkbox
		if ($item->item_type == 'cbx') {
			echo '<div class="form-radio"><input type="checkbox" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f"';
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Required";
				echo '\'}}"';
			}
			echo ' />'."\n";
			
			echo '<label for="i'.$item->item_id.'f">'.$item->item_text.'</label></div>'."\n";
		}
	
		
		
		//output radio select
		if ($item->item_type == 'rad') {
			if ($item->item_verify_msg == "") $item->item_verify_msg = "Please select an answer";
			$query = 'SELECT * FROM qr4_formitems_opts WHERE published = 1 && opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$first=true;
			$numopts=0;
			foreach ($iopts as $opts) {
				echo '<div class="form-radio"><input type="radio" name="i'.$item->item_id.'f" id="i'.$item->item_id.$numopts.'f" value="'.$opts->opt_id.'"';
				if ($item->item_req && $first) {
					echo ' validate="{';
					if (!$item->item_depend_item) echo 'required:true';
					else {
						echo 'required: function(element) { var met=false; '; 
						foreach ($dependables[$item->item_depend_item] as $di) { 
							if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
							else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
						}
	     				echo ' return met; }';
					}
					echo ', messages:{required:\'';
					if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
					else echo "Required";
					echo '\'}}"'; 
					$first=false;
				}
				echo '><label for="i'.$item->item_id.$numopts.'f">'.$opts->opt_text.'</label></div>'."\n";
				if ($opts->opt_depend) {
					$dependables[$item->item_id][$numopts]->num=$numopts; 
					$dependables[$item->item_id][$numopts]->id=$opts->opt_id;
					$dependables[$item->item_id][$numopts]->type="rad";
				}
				$numopts++;
			}
			echo "\n";
		}
	
		//output dropdown select
		if ($item->item_type == 'dds') {
			$query = 'SELECT * FROM qr4_formitems_opts WHERE published = 1 && opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$numopts=0;
			echo '<div class="form-field"><select name="i'.$item->item_id.'f"  id="i'.$item->item_id.'f" class="inputfield"';
			
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Required";
				echo '\'}}"';
			}
			echo '>'."\n";
			foreach ($iopts as $opts) {
				echo '<option value="'.$opts->opt_id.'"';
				echo '>'.$opts->opt_text.'</option>'."\n";
				if ($opts->opt_depend) {
					$dependables[$item->item_id][$numopts]->num=$numopts; 
					$dependables[$item->item_id][$numopts]->id=$opts->opt_id;
					$dependables[$item->item_id][$numopts]->type="dds";
				}
				$numopts++;
			}
			echo '</select></div>'."\n";
		}
	
		//output multi checkbox
		if ($item->item_type == 'mcb') {
			$query = 'SELECT * FROM qr4_formitems_opts WHERE published = 1 && opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$first=true;
			$numopts=0;
			foreach ($iopts as $opts) {
				echo '<div class="form-radio"><input type="checkbox" name="i'.$item->item_id.'f[]" id="i'.$item->item_id.$numopts.'f" value="'.$opts->opt_id.'"';
				if ($item->item_req && $first) {
					echo ' validate="{';
					if (!$item->item_depend_item) echo 'required:true';
					else {
						echo 'required: function(element) { var met=false; '; 
						foreach ($dependables[$item->item_depend_item] as $di) { 
							if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
							else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
						}
	     				echo ' return met; }';
					}
					if ($item->item_verify_limit) echo ', minlength:'.$item->item_verify_limit;
					echo ', messages:{required:\'';
					if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
					else echo "Required";
					echo '\'';
					if ($item->item_verify_limit) echo ', minlength:\'Select at least '.$item->item_verify_limit.'\'';
					echo '}}"';
					$first=false;
				}
				echo '><label for="i'.$item->item_id.$numopts.'f">'.$opts->opt_text.'</label></div>'."\n";
				if ($opts->opt_depend) {
					$dependables[$item->item_id][$numopts]->num=$numopts; 
					$dependables[$item->item_id][$numopts]->id=$opts->opt_id;
					$dependables[$item->item_id][$numopts]->type="mcb";
				}
				$numopts++;
			}
		}
	
		//output text field
		if ($item->item_type == 'txt' || $item->item_type == 'eml' || $item->item_type == 'phn') { 
			echo '<div class="form-field"><input type="text" size="40" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" class="inputfield"';
			
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				if ($f->uf_min) echo ', minlength:'.(int)$item->item_verify_limit;
				if ($item->item_type == 'eml') echo ', email:true';
				if ($item->item_type == 'phn') echo ', phoneUS:true';
				if ($item->item_match_item) echo ', equalTo: \'#i'.$item->item_match_item.'f\'';
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Required";
				echo '\'';
				if ($item->item_verify_limit) echo ', minlength:\'Min length '.$item->item_verify_limit.' characters\'';
				if ($item->item_type == 'eml') echo ', email:\'Email address must be valid\'';
				if ($item->item_type == 'phn') echo ', phoneUS:\'US Phone Number Required\'';
				if ($item->item_match_item) echo ', equalTo: \'Fields must match\'';
				echo '}}"';
			}
			if ($item->item_verify_msg) echo ' title="'.$item->item_verify_msg.'"';
			echo '></div>'."\n"; 
		}
	
		//output text box
		if ($item->item_type == 'tbx') { 
			echo '<div class="form-field"><textarea cols="60" rows="3" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" class="inputbox"';
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Required";
				echo '\'}}"';
			}
			echo '></textarea></div>'."\n"; 
		}
	
		//Birthday
		if ($item->item_type == 'dob') {
			$selected = ' selected="selected"';
			echo '<div class="form-field"><select id="i'.$item->item_id.'f" name="i'.$item->item_id.'f" class="inputmonth"';
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Month Required";
				echo '\'}}"';
			}
			echo '>';
			echo '<option value="">- Select Month -</option>';
			echo '<option value="01">01 - January</option>';
			echo '<option value="02">02 - February</option>';
			echo '<option value="03">03 - March</option>';
			echo '<option value="04">04 - April</option>';
			echo '<option value="05">05 - May</option>';
			echo '<option value="06">06 - June</option>';
			echo '<option value="07">07 - July</option>';
			echo '<option value="08">08 - August</option>';
			echo '<option value="09">09 - September</option>';
			echo '<option value="10">10 - October</option>';
			echo '<option value="11">11 - November</option>';
			echo '<option value="12">12 - December</option>';
			echo '</select>';
			echo '<select id="i'.$item->item_id.'f_day" name="i'.$item->item_id.'f_day" class="inputday"';
			if ($item->item_req) {
				echo ' validate="{';
				if (!$item->item_depend_item) echo 'required:true';
				else {
					echo 'required: function(element) { var met=false; '; 
					foreach ($dependables[$item->item_depend_item] as $di) { 
						if ($di->type=="dds") echo 'if (jQuery(\'#i'.$item->item_depend_item.'f\').val() == '.$di->id.') met=true; ';
						else  echo 'if (jQuery(\'#i'.$item->item_depend_item.$di->num.'f:checked\').val()) met=true; ';
					}
     				echo ' return met; }';
				}
				echo ', messages:{required:\'';
				if ($item->item_verify_msg) echo addslashes($item->item_verify_msg);
				else echo "Day Required";
				echo '\'}}"';
			}
			echo '>';
			echo '<option value="">- Select Day -</option>';
			for ($i=1;$i<=31;$i++) {
				if ($i<10) $val = "0".$i;
				else $val=$i;
				echo '<option value="'.$val.'">'.$val.'</option>';
			}
			echo '</select></div>';
		}
		
		//output hidden field
		if ($item->item_type == 'hdn') { 
			echo '<input type="hidden" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" value="'.$item->item_text.'">';
		}
		if ($item->item_hint) {
			echo '<span class="form-row-hint">'.$item->item_hint.'</span>';
		}
		echo '</div>';
		if ($item->item_type != 'msg') {
			echo '<div class="form-row-msg">';
			
			echo '</div>';
		}
		echo '</div>';
	}
	echo '<div style="clear:both;"></div></div>';	

}

// Confirm Page
if ($pageinfo->page_type=="confirm") {
	$qpp = 'SELECT page_id FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && trashed = 0 && published = 1 && ordering < '.$pageinfo->ordering;
	$db->setQuery($qpp); 
	$prevpages = $db->loadResultArray();
	$qi  = 'SELECT * FROM qr4_formitems as i ';
	$qi .= 'RIGHT JOIN qr4_formdata_answers as a ON i.item_id = a.ans_question '; //future data retrevial
	$qi .= 'WHERE item_confirm = 1 && item_page IN ('.implode(",",$prevpages).') && published = 1 && a.ans_data = '.$dataid.' ';
	$qi .= 'ORDER BY i.ordering';	
	$db->setQuery($qi);
	$items = $db->loadObjectList();
	echo '<table class="confirm-table">';
	foreach ($items as $item) {
		echo '<tr><td class="confirm-field">';
		echo $item->item_text;
		echo '</td><td class="confirm-ans">';
		$answer = "";
		switch ($item->item_type) {
			case "txt":
			case "tbx":
			case "eml":
			case "phn":
				$answer = $item->ans_answer;
				break;
			case "rad":
			case "dds":
				$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id = '.$item->ans_answer;
				$db->setQuery($qa);
				$answer = $db->loadResult();
				break;
			case "mcb":
				$qa = 'SELECT opt_text FROM qr4_formitems_opts WHERE opt_id IN ('.str_replace(" ",",",$item->ans_answer).')';
				$db->setQuery($qa);
				$answer = implode("<br>",$db->loadResultArray());
				break;
			case "cbx":
				$answer = ($item->ans_answer == "on" ? 'Yes' : 'No');
				break;
				
		}
		echo $answer;
		echo '</td></tr>';
	}
	echo '</table>';

	
}

//************
// Page action
//************

if ($pageinfo->page_action != "none") {
	echo '<div id="page-action">';
	echo '<input type="hidden" name="pagesubmit" value="'.$pageinfo->page_id.'">'."\n";
	echo '<input type="submit" value="'.$pageinfo->page_actiontext.'" class="button" name="submit">'."\n";
	echo '</div>';
	
}

//end form
if ($pageinfo->page_action != 'none') {
	echo '</form>'."\n";
	?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery.metadata.setType("attr", "validate");
		jQuery("#qr4form").validate({
			errorClass:"validation-advice",
			validClass:"validation-good",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").next("div") );
		    }
	    });

	});


</script>
	<?php 
}


//end page
echo '</div>'."\n";
echo '<div id="footer"></div>'."\n";
echo '<div style="clear:both;">'."\n";
echo '</div>'."\n";
echo '</body>'."\n";
echo '</html>';
