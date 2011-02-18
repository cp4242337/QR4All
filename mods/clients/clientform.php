<?php 
$limits[]=-1;
$limits[]=0;
$limits[]=25;
$limits[]=50;
$limits[]=100;
$limits[]=200;


?>
<form action="" method="post" name="clientform" id="clientform">
<table border="0" cellspacing="0" cellpadding="0" class="clientlist-form">
<tr><td align="right" class="ftitle">Name:</td><td class="ffield"><input name="cl_name" class="field required maxLength:100" type="text" title="Client name must be 5-100 characters"  value="<?php echo $clientinfo->cl_name; ?>"></td></tr>
<tr><td align="right" class="ftitle">Max Codes:</td><td class="ffield"><select name="cl_maxcodes" class="field">
<?php 
foreach ($limits as $l) {
	echo '<option value="'.$l.'"';
	if ($l==$clientinfo->cl_maxcodes) echo ' selected';
	echo '>';
	if ($l==0) echo 'No Limit';
	else if ($l==-1) echo 'None';
	else echo $l;
	echo '</option>';
}
?>
</select></td></tr>
<tr><td align="right" class="ftitle">Max Videos:</td><td class="ffield"><select name="cl_maxvids" class="field">
<?php 
foreach ($limits as $l) {
	echo '<option value="'.$l.'"';
	if ($l==$clientinfo->cl_maxvids) echo ' selected';
	echo '>';
	if ($l==0) echo 'No Limit';
	else if ($l==-1) echo 'None';
	else echo $l;
	echo '</option>';
}
?>
</select></td></tr>
<tr><td align="right" class="ftitle">Max Forms:</td><td class="ffield"><select name="cl_maxforms" class="field">
<?php 
foreach ($limits as $l) {
	echo '<option value="'.$l.'"';
	if ($l==$clientinfo->cl_maxforms) echo ' selected';
	echo '>';
	if ($l==0) echo 'No Limit';
	else if ($l==-1) echo 'None';
	else echo $l;
	echo '</option>';
}
?>
</select></td></tr>
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
