<form action="" method="post" name="catform" id="catform">
<table border="0" cellspacing="0" cellpadding="0" class="catlist-form">
<tr><td align="right" class="ftitle">Name:</td><td class="ffield"><input name="cat_name" class="field required maxLength:100" type="text" title="Cat name must be 5-100 characters"  value="<?php echo $catinfo->cat_name; ?>"></td></tr>
<tr><td align="right" class="ftitle">Client:</td><td class="ffield">
<select name="cat_client" class="field required" title="Client is required">
			<?php 
			foreach ($clients as $cl) {
				echo '<option value="'.$cl->cl_id.'"';
				if ($cl->cl_id == $catinfo->cl_id) echo ' selected';
				echo '>'.$cl->cl_name.'</option>';
			}
			?>

			</select>
</td></tr>
</table>
<input name="task" type="hidden" value="savecat">
<input name="mod" type="hidden" value="cats">
<input name="cat_id" id="cat_id" type="hidden" value="<?php echo $catinfo->cat_id; ?>">

</form>

<script type="text/javascript">
	window.addEvent('load', function() {
	
		new Form.Validator.Inline($('catform'), {
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
