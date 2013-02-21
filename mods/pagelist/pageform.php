<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Page Title:</td><td class="ffield"><input name="page_title" class="field required maxLength:240" type="text" title="Page title must be 5-240 characters"  value="<?php echo $pageinfo->page_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">Page Type:</td><td class="ffield"><select name="page_type" class="field"><?php 

echo '<option value="text"';
if ("text" == $pageinfo->page_type) echo ' selected';
echo '>Text</option>';

echo '<option value="form"';
if ("form" == $pageinfo->page_type) echo ' selected';
echo '>Form</option>';

echo '<option value="confirm"';
if ("confirm" == $pageinfo->page_type) echo ' selected';
echo '>Confirmation</option>';

?></select></td></tr>
<tr><td align="right" class="ftitle">Page Action:</td><td class="ffield"><select name="page_action" class="field"><?php 

echo '<option value="none"';
if ("none" == $pageinfo->page_action) echo ' selected';
echo '>None</option>';

echo '<option value="next"';
if ("next" == $pageinfo->page_action) echo ' selected';
echo '>Navigate Next Page</option>';

echo '<option value="submit"';
if ("submit" == $pageinfo->page_action) echo ' selected';
echo '>Submit Form</option>';

echo '<option value="submitmail"';
if ("submitmail" == $pageinfo->page_action) echo ' selected';
echo '>Submit Form and Send EMail</option>';

echo '<option value="reset"';
if ("reset" == $pageinfo->page_action) echo ' selected';
echo '>Reset Form</option>';

echo '<option value="redirect"';
if ("redirect" == $pageinfo->page_action) echo ' selected';
echo '>Submit Form and Redirect</option>';

?></select></td></tr>
<tr><td align="right" class="ftitle">Button Text:</td><td class="ffield"><input name="page_actiontext" class="field maxLength:100" type="text" title="Button Text must be 2-100 characters"  value="<?php echo $pageinfo->page_actiontext; ?>"></td></tr>
<tr><td align="right" class="ftitle">Reset Button:</td><td class="ffield"><?php 

echo '<input type="radio" name="page_reset" value="0"';
if ("1" != $pageinfo->page_reset) echo ' checked="checked"';
echo '> Do Not Display ';

echo '<input type="radio" name="page_reset" value="1"';
if ("1" == $pageinfo->page_reset) echo ' checked="checked"';
echo '> Display ';

?></td></tr>
<tr><td align="right" class="ftitle">Reset Text:</td><td class="ffield"><input name="page_resettext" class="field" type="text" title="Reset Text must be 2-100 characters"  value="<?php echo $pageinfo->page_resettext; ?>"></td></tr>
<tr><td align="right" class="ftitle">Redirct URL:</td><td class="ffield"><input name="page_redirurl" class="field validate-url" type="text" title="Redirect URL must be a url"  value="<?php echo $pageinfo->page_redirurl; ?>"></td></tr>
<tr><td align="right" class="ftitle">Show Q & A:</td><td class="ffield"><?php 

echo '<input type="radio" name="page_qa" value="0"';
if ("1" != $pageinfo->page_qa) echo ' checked="checked"';
echo '> Do Not Display ';

echo '<input type="radio" name="page_qa" value="1"';
if ("1" == $pageinfo->page_qa) echo ' checked="checked"';
echo '> Display ';

?></td></tr>
<tr><td align="right" class="ftitle">Page Content:</td><td class="ffield"><textarea name="page_content" class="farea"><?php echo $pageinfo->page_content; ?></textarea><br><br>
<?php 
if ($aitems) {
	echo '<b>Available items for content:</b><br>';
	foreach ($aitems as $i) {
		echo '{i'.$i->item_id.'} - '.$i->item_title.'<br>';
	}
	echo '<br>Place the {i##} element where you want the answer to that item';
	
} else {
	echo 'No items available for content or new page';
}


?>
</td></tr>
</table>
<input name="task" type="hidden" value="savepage">
<input name="page_form" type="hidden" value="<?php echo $form; ?>">
<input name="mod" type="hidden" value="pagelist">
<input name="page_id" type="hidden" value="<?php echo $pageinfo->page_id; ?>">
<input name="ordering" type="hidden" value="<?php echo $pageinfo->ordering; ?>">

</form>

<script type="text/javascript">
	window.addEvent('load', function() {
	
	new Form.Validator.Inline($('codeform'), {
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
</script>
<?php
