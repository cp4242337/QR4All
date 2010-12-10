<form action="" method="post" name="clientform" id="clientform">
<table border="0" cellspacing="0" cellpadding="0" class="clientlist-form">
<tr><td align="right" class="ftitle">Name:</td><td class="ffield"><input name="cl_name" class="field required maxLength:100" type="text" title="Client name must be 5-100 characters"  value="<?php echo $clientinfo->cl_name; ?>"></td></tr>
</table>
<input name="task" type="hidden" value="saveclient">
<input name="mod" type="hidden" value="clients">
<input name="cl_id" id="cl_id" type="hidden" value="<?php echo $clientinfo->cl_id; ?>">

</form>

<script type="text/javascript">
	window.addEvent('load', function() {
	
		new Form.Validator.Inline($('clientform'), {
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
