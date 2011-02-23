<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Display Text:</td><td class="ffield"><textarea name="opt_text" class="farea required"><?php echo $optinfo->opt_text; ?></textarea></td></tr>
<tr><td align="right" class="ftitle">Dependent:</td><td class="ffield">
<label><input type="radio" name="opt_depend" value="0" <?php if (!$optinfo->opt_depend) echo 'checked="checked"'; ?>> No</label> 
<label><input type="radio" name="opt_depend" value="1" <?php if ($optinfo->opt_depend) echo 'checked="checked"'; ?>> Yes</label></td></tr>
</table>
<input name="task" type="hidden" value="saveopt">
<input name="opt_page" type="hidden" value="<?php echo $page; ?>">
<input name="opt_form" type="hidden" value="<?php echo $form; ?>">
<input name="opt_item" type="hidden" value="<?php echo $item; ?>">
<input name="mod" type="hidden" value="optlist">
<input name="opt_id" type="hidden" value="<?php echo $optinfo->opt_id; ?>">
<input name="ordering" type="hidden" value="<?php echo $optinfo->ordering; ?>">

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
