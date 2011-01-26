<?php 
$count=0;
echo '<form name="userlistform" method="post" action="">';
echo '<table border="0" cellspacing="0" cellpadding="0" class="userlist-table">';
echo '<tr>';
echo '<th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($users).',\'cb\','.$count.');" /></th>';
echo '<th width="30">ID</th><th width="200">Name</th><th width="120">UserName</th><th>EMail</th><th width="70">Access</th>';
echo '<th width="50">#Clients</th><th width="50">#Codes</th><th width="50">#Videos</th><th width="50">#Cats</th><th width="300">Actions</th></tr>';
foreach ($users as $u) {
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="user[]" value="'.$u->usr_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td width="30">'.$u->usr_id.'</td>';
	echo '<td><a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'edituser\')">'.$u->usr_fullname.'</a></td>';
	echo '<td>'.$u->usr_name.'</td>';
	echo '<td>'.$u->usr_email.'</td>';
	echo '<td align="center">';
	switch ($u->usr_level) {
		case 1: echo 'Client'; break;
		case 2: echo 'Admin'; break;
		case 3: echo 'Root'; break;
	}
	echo '</td>';
	echo '<td align="center">'.($u->usr_level==1 ? count($u->usr_clients) : '').'</td>';
	echo '<td align="center">'.($u->usr_level==1 ? count($u->usr_codes) : '').'</td>';
	echo '<td align="center">'.($u->usr_level==1 ? count($u->usr_videos) : '').'</td>';
	echo '<td align="center">'.($u->usr_level==1 ? count($u->usr_cats) : '').'</td>';
	echo '<td class="userlist-ops">';
	if ($u->usr_level == 1) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editclients\')">Clients</a> ';
	if ($u->usr_level != 3) {
		if (!$u->trashed) {
			if ($u->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')">Unpub</a> ';
			else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')">Pub</a> ';
		}
		if (!$u->trashed && !$u->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'trash\')">Trash</a> ';
		if ($u->trashed) {
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'untrash\')">Restore</a> ';
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')">Delete</a> ';
		}
	}
	echo '</td>';
	echo '</tr>';
	$count++;
	
} 
echo '</table>';
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="mod" value="users">';
echo '<input type="hidden" name="boxchecked" value="0" />';
echo '</form>';

?>

<script type="text/javascript">
window.addEvent('domready', function() { 
	var zebraTables = new ZebraTable({
    	elements: 'table.userlist-table',
    	cssEven: 'ult-even',
    	cssOdd: 'ult-odd',
    	cssHighlight: 'ult-highlight',
    	cssMouseEnter: 'ult-mo'
	});
});
</script>

<script type="text/javascript">
function checkAll( n, fldName,start ) {
	if (!fldName) {
		fldName = 'cb';
	}
	start = parseInt(start);
	var f = document.userlistform;
	var c = eval('f.toggle'+''+start).checked; 
	var n2 = 0;
	for (i=start; i < (n+start); i++) {
		cb = eval( 'f.' + fldName + '' + i );
		if (cb) {
			cb.checked = c;
			n2++;
		}
	}
	if (c) {
		document.userlistform.boxchecked.value = n2;
	} else {
		document.userlistform.boxchecked.value = 0;
	}
}

function listItemTask( id, task ) {
    var f = document.userlistform;
    cb = eval( 'f.' + id );
    if (cb) {
        for (i = 0; true; i++) {
            cbx = eval('f.cb'+i);
            if (!cbx) break;
            cbx.checked = false;
        } // for
        cb.checked = true;
        f.boxchecked.value = 1;
        submitbutton(task);
    }
    return false;
}

function isChecked(isitchecked){
	if (isitchecked == true){
		document.userlistform.boxchecked.value++;
	}
	else {
		document.userlistform.boxchecked.value--;
	}
}

function allTask(task) {
	if(document.userlistform.boxchecked.value==0) {
		alert('Please make a selection from the list');
	} else {  
		submitbutton(task);
	}
}

function submitbutton(pressbutton) {
	submitform(pressbutton);
}

function submitform(pressbutton){
	if (pressbutton) {
		document.userlistform.task.value=pressbutton;
	}
	if (typeof document.userlistform.onsubmit == "function") {
		document.userlistform.onsubmit();
	}
	document.userlistform.submit();
}
</script>