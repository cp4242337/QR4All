<form action="" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Code Name:</td><td class="ffield"><input name="code_name" class="field required maxLength:40" type="text" title="Code name must be 5-40 characters"  value="<?php echo $codeinfo->cd_name; ?>"></td></tr>
<tr><td align="right" class="ftitle">Code Type:</td><td class="ffield"><select name="code_type" class="field required" title="Code type is required">
			<option value="qr" <?php echo ($codeinfo->cd_type == 'qr' ? 'selected' : '') ?>>QR Code</option>
			<option value="txt" <?php echo ($codeinfo->cd_type == 'txt' ? 'selected' : '') ?>>Txt Link</option>
			<option value="web" <?php echo ($codeinfo->cd_type == 'web' ? 'selected' : '') ?>>Web Link</option>
			</select></td></tr>
<tr><td align="right" class="ftitle">Code URL:</td><td class="ffield"><input name="code_url" class="field required validate-url" type="text" title="A valid URL is required" value="<?php echo $codeinfo->cd_url; ?>"></td></tr>
<tr><td align="right" class="ftitle">Client/Cat:</td><td class="ffield"><select name="code_cat" class="field required" title="Client/Category is required">
			<?php 
			foreach ($cats as $cl) {
				foreach ($cl->cats as $ct) {
					echo '<option value="'.$ct->cat_id.'"';
					if ($ct->cat_id == $codeinfo->cd_cat) echo ' selected';
					echo '>'.$cl->cl_name.'/'.$ct->cat_name.'</option>';
				}
			}
			?>

			</select></td></tr>
</table>
<input name="task" type="hidden" value="savecode">
<input name="mod" type="hidden" value="codelist">
<input name="code_id" type="hidden" value="<?php echo $codeinfo->cd_id; ?>">

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
