<?php 
$count=0;
echo '<form name="clientlistform" method="post" action="">';
echo '<table border="0" cellspacing="0" cellpadding="0" class="clientlist-table">';
echo '<tr>';
echo '<th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($clients).',\'cb\','.$count.');" /></th>';
echo '<th width="30">ID</th><th>Name</th><th width="75">Codes</th><th width="75">Videos</th><th width="75">Forms</th><th width="300">Actions</th></tr>';
foreach ($clients as $u) {
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="client[]" value="'.$u->cl_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td width="30">'.$u->cl_id.'</td>';
	echo '<td><a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editclient\')">'.$u->cl_name.'</a></td>';
	if ($u->cl_maxcodes == -1) echo '<td>N/A</td>';
	else echo '<td>'.sizeof($u->numcodes).'/'.($u->cl_maxcodes==0?'&#8734;':$u->cl_maxcodes).'</td>';
	if ($u->cl_maxvids == -1) echo '<td>N/A</td>';
	else echo '<td>'.sizeof($u->numvideos).'/'.($u->cl_maxvids==0?'&#8734;':$u->cl_maxvids).'</td>';
	if ($u->cl_maxforms == -1) echo '<td>N/A</td>';
	else echo '<td>'.sizeof($u->numforms).'/'.($u->cl_maxforms==0?'&#8734;':$u->cl_maxforms).'</td>';
	echo '<td class="clientlist-ops">';
	if ($user->lvl >= 2) {
		if ($u->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')">Unpub</a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')">Pub</a> ';
		if (!$u->published && $user->lvl == 3) {
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')">Delete</a> ';
		}
	}
	echo '</td>';
	echo '</tr>';
	$count++;
	
} 
echo '</table>';
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="mod" value="clients">';
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