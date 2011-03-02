<?php 
$count=0;
echo '<form name="catlistform" method="post" action="">';
echo '<table border="0" cellspacing="0" cellpadding="0" class="catlist-table">';
echo '<tr>';
echo '<th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($cats).',\'cb\','.$count.');" /></th>';
echo '<th width="30">ID</th><th>Name</th><th width="150">Client</th><th width="300">Actions</th></tr>';
foreach ($cats as $u) {
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="cat[]" value="'.$u->cat_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td width="30">'.$u->cat_id.'</td>';
	echo '<td><a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editcat\')">'.$u->cat_name.'</a></td>';
	echo '<td>'.$u->cl_name.'</td>';
	echo '<td class="clientlist-ops">';
	if ($user->lvl_edit) {
		if ($u->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')">Unpub</a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')">Pub</a> ';
		if (!$u->published && $user->lvl_admin) {
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')">Delete</a> ';
		}
	}
	echo '</td>';
	echo '</tr>';
	$count++;
	
} 
echo '</table>';
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="mod" value="cats">';
echo '<input type="hidden" name="boxchecked" value="0" />';
echo '</form>';

?>

<script type="text/javascript">
window.addEvent('domready', function() { 
	var zebraTables = new ZebraTable({
    	elements: 'table.catlist-table',
    	cssEven: 'calt-even',
    	cssOdd: 'calt-odd',
    	cssHighlight: 'calt-highlight',
    	cssMouseEnter: 'calt-mo'
	});
});
</script>

<script type="text/javascript">
function checkAll( n, fldName,start ) {
	if (!fldName) {
		fldName = 'cb';
	}
	start = parseInt(start);
	var f = document.catlistform;
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
		document.catlistform.boxchecked.value = n2;
	} else {
		document.catlistform.boxchecked.value = 0;
	}
}

function listItemTask( id, task ) {
    var f = document.catlistform;
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
		document.catlistform.boxchecked.value++;
	}
	else {
		document.catlistform.boxchecked.value--;
	}
}

function allTask(task) {
	if(document.catlistform.boxchecked.value==0) {
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
		document.catlistform.task.value=pressbutton;
	}
	if (typeof document.catlistform.onsubmit == "function") {
		document.catlistform.onsubmit();
	}
	document.catlistform.submit();
}
</script>