<?php
global $user;
$count=0;
echo '<form name="codelistform" method="post" action="">';
echo '<div class="codelist-codes">';
echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
echo '<tr><th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($items).',\'cb\','.$count.');" /></th>';
echo '<th>Title</th><th width="20">Id</th><th width="250">Type</th>';
if ($user->lvl_edit && (sizeof($items) > 1)) echo '<th width="100">Order <a href="javascript:saveorder('.(sizeof($items)-1).', \'saveorder\')" title="Save Order">Save</a></th>';
else echo '<th width="100">Order</th>';
echo '<th width="75"># Options</th><th width="300">Ops</th></tr>';
foreach ($items as $d) { 
	echo '<tr>';
	echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="item[]" value="'.$d->item_id.'" onclick="isChecked(this.checked);"></td>';
	echo '<td>';
	if ($user->lvl_edit) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'edititem\')">'.$d->item_title.'</a>';
	else echo $d->item_title;
	echo '&nbsp;</td>';
	echo '<td>'.$d->item_id.'&nbsp;</td>';
	echo '<td>';
	switch ($d->item_type) {
		case "msg": echo "Message"; break;
		case "txt": echo "Text Field"; break;
		case "tbx": echo "Text Box"; break;
		case "eml": echo "Email Form"; break;
		case "rad": echo "Radio Select"; break;
		case "mcb": echo "Multi Checkbox"; break;
		case "cbx": echo "Checkbox"; break;
		case "dds": echo "Drop Down"; break;
	}
	echo '&nbsp;</td>';
	if ($user->lvl_edit) {
		echo '<td><input type="text" name="order[]" size="5" value="'.$d->ordering.'" ';
		if (sizeof($items) <= 1) echo 'disabled="diabled" ';
		echo 'class="forder" style="text-align: center" />';
		if ($d->ordering != 1) echo ' <a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'orderup\')" title="Move Up"><img src="images/moveup.png" border="0" alt="Move Up" /></a> ';
		else echo ' <img src="images/moveup_i.png" border="0" alt="Move Up" /> ';
		if ($count != (sizeof($items)-1)) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'orderdown\')" title="Move Down"><img src="images/movedown.png" border="0" alt="Move Down" /></a> ';
		else echo '<img src="images/movedown_i.png" border="0" alt="Move Down" />';
		echo '</td>';
	} else {
		echo '<td>'.$d->ordering.'&nbsp;</td>';
	}
	echo '<td>'.$d->opts.'&nbsp;</td>';
	echo '<td class="codelist-ops">';
	$optsqs=Array("rad","mcb","dds");
	if ($user->lvl_edit) {
		if (in_array($d->item_type,$optsqs)) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'options\')" title="Item Options"><img src="images/options.png" border="0" alt="Item Options" /></a> ';
		else echo '<img src="images/options_i.png" border="0" class="nolink" />';
		if ($d->item_req) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'notreq\')" title="Set Not Required"><img src="images/unrequire.png" border="0" alt="Set no Required" /></a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'req\')" title="Set Required"><img src="images/require.png" border="0" alt="Set Required" /></a> ';
		if ($d->item_confirm) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'notonconf\')" title="Set Not on Confirmation Page"><img src="images/unconfirm.png" border="0" alt="Set Not on Confirmation Page" /></a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'onconf\')" title="Set on Confirmation Page"><img src="images/confirm.png" border="0" alt="Set on Confirmation Page" /></a> ';
		if ($d->published) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'unpublish\')" title="Unpublish"><img src="images/unpublish.png" border="0" alt="Unpublish" /></a> ';
		else echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'publish\')" title="Publish"><img src="images/publish.png" border="0" alt="Publish" /></a> ';
		if (!$d->trashed) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'trash\')" title="Send to Trash"><img src="images/trash.png" border="0" "alt="Send to Trash" /></a> ';
	}
	if ($user->lvl_admin) {
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
echo '<input type="hidden" name="mod" value="itemlist">';
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