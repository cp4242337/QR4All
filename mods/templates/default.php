<?php 
$count=0;
echo '<form name="clientlistform" method="post" action="">';
echo '<table border="0" cellspacing="0" cellpadding="0" class="clientlist-table">';
echo '<tr>';
echo '<th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($clients).',\'cb\','.$count.');" /></th>';
echo '<th width="30">ID</th><th>Name</th><th width="75">Type</th><th width="300">Operations</th></tr>';
foreach ($templates as $u) {
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="tmpl[]" value="'.$u->tmpl_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td width="30">'.$u->tmpl_id.'</td>';
	echo '<td><a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'edittmpl\')">'.$u->tmpl_name.'</a></td>';
	echo '<td align="center">';
	switch ($u->tmpl_type) {
		case 'admin': echo 'Admin'; break;
		case 'form': echo 'Form'; break;
		case 'video': echo 'Video'; break;
	}
	echo '</td>';
	echo '<td class="clientlist-ops">';
	if ($user->lvl_admin) {
		echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')">Delete</a> ';
	}
	echo '</td>';
	echo '</tr>';
	$count++;
	
} 
echo '</table>';
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="mod" value="templates">';
echo '<input type="hidden" name="boxchecked" value="0" />';
echo '</form>';

?>

<script type="text/javascript">
window.addEvent('domready', function() { 
	var zebraTables = new ZebraTable({
    	elements: 'table.clientlist-table',
    	cssEven: 'cllt-even',
    	cssOdd: 'cllt-odd',
    	cssHighlight: 'cllt-highlight',
    	cssMouseEnter: 'cllt-mo'
	});
});
</script>

<script type="text/javascript">
function checkAll( n, fldName,start ) {
	if (!fldName) {
		fldName = 'cb';
	}
	start = parseInt(start);
	var f = document.clientlistform;
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
		document.clientlistform.boxchecked.value = n2;
	} else {
		document.clientlistform.boxchecked.value = 0;
	}
}

function listItemTask( id, task ) {
    var f = document.clientlistform;
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
		document.clientlistform.boxchecked.value++;
	}
	else {
		document.clientlistform.boxchecked.value--;
	}
}

function allTask(task) {
	if(document.clientlistform.boxchecked.value==0) {
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
		document.clientlistform.task.value=pressbutton;
	}
	if (typeof document.clientlistform.onsubmit == "function") {
		document.clientlistform.onsubmit();
	}
	document.clientlistform.submit();
}
</script>