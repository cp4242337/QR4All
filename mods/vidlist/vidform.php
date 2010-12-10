<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Video Title:</td><td class="ffield"><input name="vid_title" class="field required maxLength:40" type="text" title="Video title must be 5-40 characters"  value="<?php echo $vidinfo->vid_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">Video File:</td><td class="ffield"><input name="vid_file" class="field required" type="text" title="A valid URL is required" value="<?php echo $vidinfo->vid_file; ?>"></td></tr>
<tr><td align="right" class="ftitle">Client/Cat:</td><td class="ffield"><select name="vid_cat" class="field required" title="Client/Category is required">
			<?php 
			foreach ($cats as $cl) {
				foreach ($cl->cats as $ct) {
					echo '<option value="'.$ct->cat_id.'"';
					if ($ct->cat_id == $vidinfo->vid_cat) echo ' selected';
					echo '>'.$cl->cl_name.'/'.$ct->cat_name.'</option>';
				}
			}
			?>

			</select></td></tr>
</table>
<input name="task" type="hidden" value="saveVid">
<input name="mod" type="hidden" value="vidlist">
<input name="vid_id" type="hidden" value="<?php echo $vidinfo->vid_id; ?>">

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
