<?php
global $user;
$count=0;
echo '<form name="codelistform" method="post" action="">';
echo '<div class="codelist-codes">';
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr><th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($emails).',\'cb\','.$count.');" /></th>';
echo '<th>Title</th>';
echo '<th width="100"># Attach</th>';
echo '<th width="300">Ops</th></tr>';
foreach ($emails as $d) { 
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="eml[]" value="'.$d->eml_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td>';
	if ($user->lvl > 1) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editeml\')">'.$d->eml_title.'</a>';
	else echo $d->eml_title;
	echo '&nbsp;</td>';
	echo '<td>'.$d->attachs.'</td>';
	echo '<td class="codelist-ops">';
	if ($user->lvl > 1) {
		echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'attach\')" title="Edit Attachments"><img src="images/pages.png" border="0" alt="Edit Attachments" /></a> ';
		if ($d->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')" title="Unpublish"><img src="images/unpublish.png" border="0" alt="Unpublish" /></a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')" title="Publish"><img src="images/publish.png" border="0" alt="Publish" /></a> ';
		if (!$d->trashed) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'trash\')" title="Send to Trash"><img src="images/trash.png" border="0" "alt="Send to Trash" /></a> ';
	}
	if ($user->lvl > 2) {
		if ($d->trashed) {
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'untrash\')" title="Restore from Trash"><img src="images/untrash.png" border="0" alt="Restore from Trash" /></a> ';
			echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'delete\')" title="Permanently Delete"><img src="images/delete.png" border="0" alt"Permanently Delete" /></a> ';
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
echo '<input type="hidden" name="page" value="'.$page.'">';
echo '<input type="hidden" name="mod" value="femllist">';
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