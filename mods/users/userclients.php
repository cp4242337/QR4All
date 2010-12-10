<?php 
$count=0;
echo '<form name="userlistform" method="post" action="">';
echo '<table border="0" cellspacing="0" cellpadding="0" class="userlist-table" style="width:280px">';
echo '<tr>';
echo '<th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($clients).',\'cb\','.$count.');" /></th>';
echo '<th width="200">Client</th><th width="70">Has Access</th>';
echo '</tr>';
foreach ($clients as $u) {
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="client[]" value="'.$u->cl_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td>'.$u->cl_name.'</a></td>';
	echo '<td align="center">';
	if ($u->cu_user) {
		echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unhaveclient\')">Yes</a> ';
	} else {
		echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'haveclient\')">No</a> ';
	}
	echo '</td>';
	
	echo '</tr>';
	$count++;
	
} 
echo '</table>';
echo '<input type="hidden" name="user" value="'.JRequest::getInt('user',0).'">';
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