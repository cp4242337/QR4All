<form action="index.php" method="post" name="codeform" id="codeform" enctype="multipart/form-data">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Name:</td><td class="ffield"><input name="at_name" class="field required maxLength:100" type="text" title="Name must be 5-100 characters"  value="<?php echo $atinfo->at_title; ?>"></td></tr>
<?php if (JRequest::getString('task') == 'atadd') echo '<tr><td align="right" class="ftitle">File:</td><td class="ffield"><input name="at_file" class="field required" type="file"></td></tr>'; ?>
</table>
<input name="task" type="hidden" value="saveat">
<input name="at_page" type="hidden" value="<?php echo $page; ?>">
<input name="at_form" type="hidden" value="<?php echo $form; ?>">
<input name="at_email" type="hidden" value="<?php echo $eml; ?>">
<input name="mod" type="hidden" value="attachlist">
<input name="at_id" type="hidden" value="<?php echo $atinfo->at_id; ?>">

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
