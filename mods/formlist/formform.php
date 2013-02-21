<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Form Int Title:</td><td class="ffield"><input name="form_title" class="field required maxLength:240" type="text" title="From title must be 5-240 characters"  value="<?php echo $forminfo->form_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">Form Ext Title:</td><td class="ffield"><input name="form_pubtitle" class="field required maxLength:240" type="text" title="Form title must be 5-240 characters"  value="<?php echo $forminfo->form_publictitle; ?>"></td></tr>
<tr><td align="right" class="ftitle">Client/Cat:</td><td class="ffield"><select name="form_cat" class="field required" title="Client/Category is required">
			<?php 
			foreach ($cats as $cl) {
				foreach ($cl->cats as $ct) {
					echo '<option value="'.$ct->cat_id.'"';
					if ($ct->cat_id == $forminfo->form_cat) echo ' selected';
					echo '>'.$cl->cl_name.'/'.$ct->cat_name.'</option>';
				}
			}
			?>

			</select></td></tr>
<tr><td align="right" class="ftitle">Template:</td><td class="ffield"><select name="form_tmpl" class="field required" title="Template is required">
			<?php 
			foreach ($tmpls as $d) {
				echo '<option value="'.$d->tmpl_id.'"';
				if ($d->tmpl_id == $forminfo->form_template) echo ' selected';
				echo '>'.$d->tmpl_name.'</option>';
			}
			?>

			</select></td></tr>
<tr><td align="right" class="ftitle">Domain:</td><td class="ffield"><select name="form_domain" class="field required" title="Domain is required">
<?php 
foreach ($doms as $d) {
	echo '<option value="'.$d->dom_id.'"';
	if ($d->dom_id == $forminfo->form_domain) echo ' selected';
	echo '>'.$d->dom_dom.'</option>';
}
?>

</select></td></tr>
<tr><td align="right" class="ftitle">Session Time (min):</td><td class="ffield"><input name="form_sessiontime" class="field required" type="text" title="Session Time must be more than 15min"  value="<?php echo ($forminfo->form_sessiontime) ? $forminfo->form_sessiontime : "60"; ?>"></td></tr>
<tr><td align="right" class="ftitle">Password:</td><td class="ffield"><input name="form_password" class="field" type="text" title="Password"></td></tr>
<tr><td align="right" class="ftitle">Header Lines:</td><td class="ffield"><textarea name="form_header" class="farea"><?php echo $forminfo->form_header; ?></textarea></td></tr>
<tr><td align="right" class="ftitle">Body Tag:</td><td class="ffield"><textarea name="form_body" class="farea"><?php echo $forminfo->form_body; ?></textarea></td></tr>
</table>
<input name="task" type="hidden" value="saveForm">
<input name="mod" type="hidden" value="formlist">
<input name="form_id" type="hidden" value="<?php echo $forminfo->form_id; ?>">

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
