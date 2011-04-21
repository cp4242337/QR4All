<form action="" method="post" name="tmplform" id="tmplform">
<table border="0" cellspacing="0" cellpadding="0" class="clientlist-form">
<tr><td align="right" class="ftitle">Template Name:</td><td class="ffield"><input name="tmpl_name" class="field required maxLength:100" type="text" title="Client name must be 5-100 characters"  value="<?php echo $tmplinfo->tmpl_name; ?>"></td></tr>
<tr><td align="right" class="ftitle">Template URL:</td><td class="ffield"><input name="tmpl_url" class="field required minLength:5" type="text" title="URL name must be more than 5"  value="<?php echo $tmplinfo->tmpl_url; ?>"></td></tr>
<tr><td align="right" class="ftitle">Template Type:</td><td class="ffield"><select name="tmpl_type" class="field required" title="Template type is required">
		<option value="form" <?php echo ($tmplinfo->tmpl_type == 'form' ? 'selected' : '') ?>>Form</option>
		<option value="video" <?php echo ($tmplinfo->tmpl_type == 'video' ? 'selected' : '') ?>>Video</option>
		<option value="admin" <?php echo ($tmplinfo->tmpl_type == 'admin' ? 'selected' : '') ?>>Admin</option>
</select></td></tr>
</table>
<input name="task" type="hidden" value="savetmpl">
<input name="mod" type="hidden" value="templates">
<input name="tmpl_id" id="tmpl_id" type="hidden" value="<?php echo $tmplinfo->tmpl_id; ?>">

</form>

<script type="text/javascript">
	window.addEvent('load', function() {
	
		new Form.Validator.Inline($('tmplform'), {
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
