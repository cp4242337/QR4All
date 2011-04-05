<?php
/*
 * QR4All Forms 0.7b
 * Liscensed under GPLv2
 * (C) Corona Productions
 */

include 'lib/filterinput.php';
include 'lib/request.php';
include 'lib/database.php';
include 'lib/database/mysql.php';
include 'lib/database/mysqli.php';
include('Browscap.php');
include('ipinfodb.class.php');

global $dbc;
$dbc['user'] = 'qr4all';
$dbc['password'] = 'qr4all';
$dbc['database'] = 'qr4all';
$dbc['driver'] = 'mysqli';
$dbc['host'] = 'localhost';

//$db = new JDatabase($dbc);
$db = JDatabase::getInstance($dbc);
session_start();
$form = JRequest::getVar('c',null);
if (!$form) { echo 'No Form Specified'; exit; }
$cookiename = 'form_'.$form.'_session';

$qf = 'SELECT * FROM qr4_forms WHERE form_code = "'.$form.'"  && published = 1';
$db->setQuery($qf);
$forminfo = $db->loadObject();

if (!$forminfo->published || !$forminfo) { echo 'Form not found'; exit; }



//have we been here before??
if (!isset($_COOKIE[$cookiename])) { //if not set up cookie and sessin id
	$_SESSION['step'] = NULL;
	$_SESSION[$cookiename] = NULL;
	$sessid = md5("time".rand(0,1551761));
	setcookie($cookiename,$sessid,(time()+60*60*24));
	$_SESSION[$cookiename] = $sessid;
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
	if ($_COOKIE[$cookiename] != $_SESSION[$cookiename]) { 
		setcookie($cookiename,'',(time()-3600));
		$_SESSION['step'] = NULL;
		$_SESSION[$cookiename] = NULL;
		header("Location:$forminfo->form_code");
	} else {
		$sessid = $_SESSION[$cookiename];
		//get dataid
		$qr = 'SELECT data_id FROM qr4_formdata WHERE data_session = "'.$sessid.'"';
		$db->setQuery($qr);
		$dataid = $db->loadResult();
		if (!$dataid) {
			setcookie($cookiename,'',(time()-60*60*24));
			$_SESSION['step'] = NULL;
			$_SESSION['id'] = NULL;
			header("Location:$forminfo->form_code");
		}
	}
}

if (!isset($_SESSION['step'])) { $curstep = 1; $_SESSION['step']=1; }
else { ($curstep = $_SESSION['step']); }

$qp = 'SELECT * FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && published = 1 ORDER BY ordering ASC LIMIT '.($curstep - 1).',1';
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
				case "rad":
				case "dds":
				case "cbx":
					$answer = $db->getEscaped(JRequest::getVar("i".$item->item_id."f",""));
					break;
				case "mcb":
					$answer = implode(" ",JRequest::getVar("i".$item->item_id."f",""));
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
	// --- EMAIL ACTION TO GO HERE ---
	//set end if submitting
	if ($pageinfo->page_action == 'submit' || $pageinfo->page_action == 'submitmail') {
		$qe = 'UPDATE qr4_formdata SET data_end = "'.date("Y-m-d H:i:s").'" WHERE data_id = '.$dataid;
		$db->setQuery($qe);
		$db->query();
	}
	//go to next page
	$_SESSION['step'] = $curstep+1;
	header("Location:$forminfo->form_code");
}

//set title
$title=$forminfo->form_publictitle.' - '.$pageinfo->page_title;

//start oage
echo '<html>'."\n";
echo '<head><title>'.$title.'</title>'."\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n"; 
// css goes here echo '<link href="admin.css" rel="stylesheet" type="text/css">'."\n";
echo '<script type="text/javascript" src="scripts/mootools.js"></script>'."\n";
echo '<script type="text/javascript" src="scripts/mootools-more.js"></script>'."\n";
echo '</head>'."\n";
echo '<body>'."\n";
echo '<div id="container">'."\n";
echo '<div id="header"></div>'."\n";
echo '<div id="content">'."\n";
//start form
if ($pageinfo->page_action != 'none') {
	echo '<form action="" method="post" name="qr4form" id="qr4form">'."\n";
	
}

//************
// Page action
//************

// Page
if ($pageinfo->page_type=="text") {
	echo $pageinfo->page_content."\n";
}

// Form Page
if ($pageinfo->page_type=="form") {
	$qi = 'SELECT * FROM qr4_formitems WHERE item_page = '.$pageinfo->page_id.' && published = 1 ORDER BY ordering';	
	$db->setQuery($qi);
	$items = $db->loadObjectList();
	//show items
	foreach ($items as $item) {
		//Question text if not a single checkbox
		if ($item->item_type != 'cbx') {
			echo '<strong>';
			echo $item->item_text;
			echo '</strong><br>'."\n";
		}
	
		//output checkbox
		if ($item->item_type == 'cbx') {
			echo '<label><input type="checkbox" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f"';
			if ($item->item_req) echo ' class="validate-required-check"';
			echo '>'.$item->item_text.'</label><br>'."\n";
		}
	
		//output radio select
		if ($item->item_type == 'rad') {
			$query = 'SELECT * FROM qr4_formitems_opts WHERE opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$numopts=0;
			echo '<span id="i'.$item->id.'list">'."\n";
			foreach ($iopts as $opts) {
				echo '<label><input type="radio" name="i'.$item->item_id.'f" id="i'.$item->item_id.$numopts.'f" value="'.$opts->opt_id.'"';
				if ($item->item_req && $numopts == 0) echo ' class="validate-reqchk-byname"';
				echo '>'.$opts->opt_text.'</label><br>'."\n";
				$numopts++;
			}
			echo '</span>'."\n";
		}
	
		//output dropdown select
		if ($item->item_type == 'dds') {
			$query = 'SELECT * FROM qr4_formitems_opts WHERE opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$numopts=0;
			echo '<select name="i'.$item->item_id.'f"  id="i'.$item->item_id.'f" class="inputfield';
			if ($item->item_req) echo ' required';
			echo '">'."\n";
			foreach ($iopts as $opts) {
				echo '<option value="'.$opts->opt_id.'"';
				echo '>'.$opts->opt_text.'</option>'."\n";
				$numopts++;
			}
			echo '</select><br>'."\n";
		}
	
		//output multi checkbox
		if ($item->item_type == 'mcb') {
			$query = 'SELECT * FROM qr4_formitems_opts WHERE opt_item = '.$item->item_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$iopts = $db->loadObjectList();
			$numopts=0;
			foreach ($iopts as $opts) {
				echo '<label><input type="checkbox" name="i'.$item->item_id.'f[]" id="i'.$item->item_id.$numopts.'f" value="'.$opts->opt_id.'"';
				if ($item->item_req && $numopts ==0 && !$item->item_verify) echo ' class="validate-reqchk-byname"';
				if ($item->item_verify && $numopts == 0) echo ' class="checkAtLeast:'.$item->item_verify_limit.'"';
				echo '>'.$opts->opt_text.'</label><br>'."\n";
				$numopts++;
			}
		}
	
		//output text field
		if ($item->item_type == 'txt') { 
			echo '<input type="text" size="40" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" class="inputfield';
			if ($item->item_verify && !$item->item_depend_item) echo ' minLength:'.(int)$item->item_verify_limit;
			if ($item->item_req && !$item->item_depend_item) echo ' required';
			if ($item->item_verify && $item->item_depend_item) echo " required-with validate-match matchInput:'i".$item->item_depend_item."f'";
			echo '"><br>'."\n"; 
		}
	
		//output text box
		if ($item->item_type == 'tbx') { 
			echo '<textarea cols="60" rows="3" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" class="inputbox';
			if ($item->item_req) echo ' required';
			echo '"></textarea><br>'."\n"; 
		}
	
		//output email field
		if ($item->item_type == 'eml') { 
			echo '<input type="text" size="40" name="i'.$item->item_id.'f" id="i'.$item->item_id.'f" class="inputfield';
			if ($item->item_verify && !$item->item_depend_item) echo ' validate-email';
			if ($item->item_req && !$item->item_depend_item) echo ' required';
			if ($item->item_verify && $item->item_depend_item) echo " required-with validate-match matchInput:'i".$item->item_depend_item."f'";
			echo '"><br>'."\n";
		}
		
		echo '<br>'."\n";
	}
	

}

// Confirm Page
if ($pageinfo->page_type=="confirm") {
	$qpp = 'SELECT * FROM qr4_formpages WHERE page_form = '.$forminfo->form_id.'  && published = 1 ORDER BY ordering ASC LIMIT '.($curstep - 2).',1';
	$db->setQuery($qpp);
	$prevpageinfo = $db->loadObject();
	$qi  = 'SELECT * FROM qr4_formitems WHERE item_page = '.$prevpageinfo->page_id.' && published = 1 ';
	$qi .= ''; //future data retrevial
	$qi .= 'ORDER BY ordering';	
	$db->setQuery($qi);
	$items = $db->loadObjectList();
	print_r($items);
}

//************
// Page action
//************

if ($pageinfo->page_action=="next" || $pageinfo->page_action=="submit" || $pageinfo->page_action=="submitmail") {
	echo '<input type="hidden" name="pagesubmit" value="'.$pageinfo->page_id.'">'."\n";
	echo '<input type="submit" ';
	switch ($pageinfo->page_action) {
		case "next": echo 'value="next"'; break;
		case "submit":
		case "submitmail": echo 'value="Submit"'; break;
	}
	echo ' class="button" name="submit">'."\n";

	
}

//end form
if ($pageinfo->page_action != 'none') {
	echo '</form>'."\n";
	?>
<script type="text/javascript">
	window.addEvent('load', function() {
	
		new Form.Validator.Inline($('qr4form'), {
			stopOnFailure: true,
			useTitles: true,
			errorPrefix: "",
			onFormValidate: function(passed, form, event) {
				if (passed) {
					form.submit();
				}
			}
		});
	});

	FormValidator.add('required-with', {
		test: function(element,props) {
			if (!element.value && document.id(props.matchInput).get('value')) { return false; }
			else return true;
		}
	});

	FormValidator.add('checkAtLeast', {
		errorMsg: function(element, props){
			return props.useTitle ? element.get('title') : "Check at least "+props.checkAtLeast+" items";
			},
		test: function(element,props) {
			var grpName = props.groupName || element.get('name');
			var atleast = props.checkAtLeast;
			var checked = $$(document.getElementsByName(grpName)).filter(function(item, index){
				return item.checked;
			});
			if (checked.length >= atleast) return true;
			else return false;
		} 
	});
</script>
	<?php 
}


//end page
echo '</div>'."\n";
echo '<div id="footer"></div>'."\n";
echo '</div>'."\n";
echo '</body>'."\n";
echo '</html>';
