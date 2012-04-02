<?php
global $user;
if (sizeof($clients) > 1) { 
echo '<form name="clientlist" action="" method="post">';
echo 'Client: <select name="client" onchange="document.clientlist.submit();" class="field">';
echo '<option value="0"';
if ($curclient == 0) echo ' SELECTED';
echo '>All</option>';
foreach ($clients as $cl) {
	echo '<option value="'.$cl->cl_id.'"';
	if ($curclient == $cl->cl_id) echo ' SELECTED';
	echo '>'.$cl->cl_name.'</option>';
}
echo '</select>';
if ($curclient) {
	echo ' Category: <select name="cat" onchange="document.clientlist.submit();" class="field">';
	echo '<option value="0"';
	if ($curcat == 0) echo ' SELECTED';
	echo '>All</option>';
	foreach ($cats as $cat) {
		echo '<option value="'.$cat->cat_id.'"';
		if ($curcat == $cat->cat_id) echo ' SELECTED';
		echo '>'.$cat->cat_name.'</option>';
	}
	echo '</select>';
}

echo '<input type="hidden" name="task" value="setVar">';
echo '</form>';
}
$count=0;
echo '<form name="codelistform" method="post" action="">';
foreach ($vids as $c) { 
	if ($c->cats) {
		echo '<div class="codelist-client">'.$c->cl_name.($user->lvl_edit?' <span class="codelist-client-func"><a href="index.php?mod=vidlist&task=addvid&client='.$c->cl_id.'">Add Video</a></span>':'').'<br>'; 
		foreach ($c->cats as $t) { 
			echo '<div class="codelist-cat">'.$t->cat_name.'<br><div class="codelist-codes">'; 
			if ($t->vids) {
				echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
				echo '<tr><th width="10"><input type="checkbox" name="toggle'.$count.'" value="" onclick="checkAll('.sizeof($t->vids).',\'cb\','.$count.');" /></th>';
				echo '<th width="250">Name</th><th width="250">Video</th><th>URL</th><th width="70">Total Hits</th><th width="300">Ops</th></tr>';
				foreach ($t->vids as $d) { 
					echo '<tr>';
					echo '<td width="10"><input type="checkbox" id="cb'.$count.'" name="vid[]" value="'.$d->vid_id.'" onclick="isChecked(this.checked);"></td>';
					echo '<td>';
					if ($user->lvl_edit) echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'editvid\')">'.$d->vid_title.'</a>';
					else echo $d->vid_title;
					echo '&nbsp;</td>';
					echo '<td>'.$d->vid_file.'&nbsp;</td>';
					echo '<td>http://'.$d->dom_dom.'/'.$d->vid_code.'&nbsp;</td>';
					echo '<td>'.$d->hits.'&nbsp;</td>';
					echo '<td class="codelist-ops">';
					echo '<a href="#" onclick="return listItemTask(\'cb'.$count.'\',\'stats\')" title="View Stats"><img src="images/stats.png" border="0" alt="View Stats" /></a> ';
					if ($user->lvl_edit) {
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
			} else {
				echo '<table cellpadding="0" cellspacing="0" border="0" class="codelist-table">';
				echo '<tr><th><em>No Videos Available</em></th></tr>';
				echo '</table>';
			}
			echo '</div></div>';
			
		} 
		echo '</div>';
	} 
}
echo '<input type="hidden" name="task" value="">';
echo '<input type="hidden" name="mod" value="vidlist">';
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