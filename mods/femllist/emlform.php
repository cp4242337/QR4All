<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Title:</td><td class="ffield"><input name="eml_title" class="field required maxLength:100" type="text" title="EMail title must be 5-100 characters"  value="<?php echo $emlinfo->eml_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">From Name:</td><td class="ffield"><input name="eml_fromname" class="field required maxLength:100" type="text" title="EMail from name must be 5-100 characters"  value="<?php echo $emlinfo->eml_fromname; ?>"></td></tr>
<tr><td align="right" class="ftitle">From EMail:</td><td class="ffield"><input name="eml_fromaddr" class="field required email maxLength:100" type="text" title="EMail from address must be 5-100 characters"  value="<?php echo $emlinfo->eml_fromaddr; ?>"></td></tr>
<tr><td align="right" class="ftitle">To Name:</td><td class="ffield"><select name="eml_toname" class="field">
<?php 
foreach ($items as $l) {
	echo '<option value="'.$l->item_id.'"';
	if ($l->item_id==$emlinfo->eml_toname) echo ' selected';
	echo '>';
	echo $l->item_title;
	echo '</option>';
}
?>
</select></td></tr>
<tr><td align="right" class="ftitle">To EMail:</td><td class="ffield"><select name="eml_toaddr" class="field">
<?php 
foreach ($items as $l) {
	if ($l->item_type == 'eml' || $l->item_type == 'hdn') {
		echo '<option value="'.$l->item_id.'"';
		if ($l->item_id==$emlinfo->eml_toaddr) echo ' selected';
		echo '>';
		echo $l->item_title;
		echo '</option>';
	}
}
?>
</select></td></tr>
<tr><td align="right" class="ftitle">Subject:</td><td class="ffield"><input name="eml_subject" class="field required maxLength:255" type="text" title="EMail Subject must be 5-255 characters"  value="<?php echo $emlinfo->eml_subject; ?>"></td></tr>
<tr><td align="right" class="ftitle">Content:</td><td class="ffield"><textarea name="eml_content" class="farea required"><?php echo $emlinfo->eml_content; ?></textarea></td></tr>
</table>
<input name="task" type="hidden" value="saveeml">
<input name="eml_page" type="hidden" value="<?php echo $page; ?>">
<input name="eml_form" type="hidden" value="<?php echo $form; ?>">
<input name="mod" type="hidden" value="femllist">
<input name="eml_id" type="hidden" value="<?php echo $emlinfo->eml_id; ?>">

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
