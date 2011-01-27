<?php
global $user;
$count=0;
echo '<form name="codelistform" method="post" action="">';
echo '<div class="codelist-codes">';
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr><th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($pages).',\'cb\','.$count.');" /></th>';
echo '<th>Title</th><th width="250">Type</th><th width="250">Action</th>';
if ($user->lvl > 1 && (sizeof($pages) > 1)) echo '<th width="100">Order <a href="javascript:saveorder('.(sizeof($pages)-1).', \'saveorder\')" title="Save Order">Save</a></th>';
else echo '<th width="100">Order</th>';
echo '<th width="50"># Items</th><th width="300">Ops</th></tr>';
foreach ($pages as $d) { 
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="page[]" value="'.$d->page_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td>';
	if ($user->lvl > 1) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editpage\')">'.$d->page_title.'</a>';
	else echo $d->page_title;
	echo '&nbsp;</td>';
	echo '<td>';
	switch ($d->page_type) {
		case "text": echo "Text"; break;
		case "form": echo "Form"; break;
		case "confirm": echo "Confirmation"; break;
	}
	echo '&nbsp;</td>';
	echo '<td>';
	switch ($d->page_action) {
		case "none": echo "None"; break;
		case "next": echo "Navigate Next Page"; break;
		case "submit": echo "Submit Form"; break;
		case "submitmail": echo "Submit Form and Send EMail"; break;
	}
	echo '&nbsp;</td>';
	if ($user->lvl > 1) {
		echo '<td><input type="text" name="order[]" size="5" value="'.$d->ordering.'" ';
		if (sizeof($pages) <= 1) echo 'disabled="diabled" ';
		echo 'class="forder" style="text-align: center" />';
		if ($d->ordering != 1) echo ' <a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'orderup\')">Up </a>| ';
		else echo ' Up | ';
		if ($count != (sizeof($pages)-1)) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'orderdown\')">Down</a> ';
		else echo 'Down';
		echo '</td>';
	} else {
		echo '<td>'.$d->ordering.'&nbsp;</td>';
	}
	echo '<td>'.$d->items.'&nbsp;</td>';
	echo '<td class="codelist-ops">';
	if ($user->lvl > 1) {
		if ($d->page_type == 'form') echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'items\')">Items</a> ';
		if ($d->page_action == 'submitemail') echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'setup\')">Setup</a> ';
		if ($d->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')">UnPub</a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')">Pub</a> ';
		if (!$d->trashed) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'trash\')">Trash</a> ';
	}
	if ($user->lvl > 2) {
		if ($d->trashed) {
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'untrash\')">Restore</a> ';
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')">Delete</a> ';
		}
	}
	echo '</td>';
	echo '</tr>'; 
	$count++;
} 
echo '</table>';
echo '</div>';				
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="form" value="'.$form.'">';
echo '<input type="hidden" name="mod" value="pagelist">';
echo '<input type="hidden" name="boxchecked" value="0" />';
echo '</form>';

?>
<script type="text/javascript">
window.addEvent('domready', function() { 
	var zebraTables = new ZebraTable({
    	elements: 'table.codelist-table',
    	cssEven: 'clt-even',
    	cssOdd: 'clt-odd',
    	cssHighlight: 'clt-highlight',
    	cssMouseEnter: 'clt-mo'
	});
});
</script>
<script type="text/javascript">

function saveorder( n,  task ) {
	checkAll_button( n, task );
}

//needed by saveorder function
function checkAll_button( n, task ) {

    if (!task ) {
		task = 'saveorder';
	}

	for ( var j = 0; j <= n; j++ ) {
		box = eval( "document.codelistform.cb" + j );
		if ( box ) {
			if ( box.checked == false ) {
				box.checked = true;
			}
		} else {
			alert("You cannot change the order of items, as an item in the list is `Checked Out`");
			return;
		}
	}
	submitform(task);
}

function checkAll( n, fldName,start ) {
	if (!fldName) {
		fldName = 'cb';
	}
	start = parseInt(start);
	var f = document.codelistform;
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
		document.codelistform.boxchecked.value = n2;
	} else {
		document.codelistform.boxchecked.value = 0;
	}
}

function listItemTask( id, task ) {
    var f = document.codelistform;
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
		document.codelistform.boxchecked.value++;
	}
	else {
		document.codelistform.boxchecked.value--;
	}
}

function allTask(task) {
	if(document.codelistform.boxchecked.value==0) {
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
		document.codelistform.task.value=pressbutton;
	}
	if (typeof document.codelistform.onsubmit == "function") {
		document.codelistform.onsubmit();
	}
	document.codelistform.submit();
}
</script>
<style>
</style>