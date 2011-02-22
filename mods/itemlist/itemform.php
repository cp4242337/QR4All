<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Item Title:</td><td class="ffield"><input name="item_title" class="field required maxLength:240" type="text" title="Item title must be 5-240 characters"  value="<?php echo $iteminfo->item_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">Display Text:</td><td class="ffield"><textarea name="item_text" class="farea"><?php echo $iteminfo->item_text; ?></textarea></td></tr>
<tr><td align="right" class="ftitle">Page Type:</td><td class="ffield"><select name="item_type" class="field"><?php 

echo '<option value="msg"'; if ("msg" == $iteminfo->item_type) echo ' selected'; echo '>Message</option>';
echo '<option value="txt"'; if ("txt" == $iteminfo->item_type) echo ' selected'; echo '>Text Field</option>';
echo '<option value="tbx"'; if ("tbx" == $iteminfo->item_type) echo ' selected'; echo '>Text Box</option>';
echo '<option value="eml"'; if ("eml" == $iteminfo->item_type) echo ' selected'; echo '>EMail Field</option>';
echo '<option value="rad"'; if ("rad" == $iteminfo->item_type) echo ' selected'; echo '>Radio Select</option>';
echo '<option value="mcb"'; if ("mcb" == $iteminfo->item_type) echo ' selected'; echo '>Multi Checkbox</option>';
echo '<option value="cbx"'; if ("cbx" == $iteminfo->item_type) echo ' selected'; echo '>Single Checkbox</option>';
echo '<option value="dds"'; if ("dds" == $iteminfo->item_type) echo ' selected'; echo '>Dropdown Select</option>';

?></select></td></tr>
<tr><td align="right" class="ftitle">Required:</td><td class="ffield">
<label><input type="radio" name="item_req" value="0" <?php if (!$iteminfo->item_req) echo 'checked="checked"'; ?>> No</label> 
<label><input type="radio" name="item_req" value="1" <?php if ($iteminfo->item_req) echo 'checked="checked"'; ?>> Yes</label></td></tr>
<tr><td align="right" class="ftitle">On Confirmation Page:</td><td class="ffield">
<label><input type="radio" name="item_confirm" value="0" <?php if (!$iteminfo->item_confirm) echo 'checked="checked"'; ?>> No</label> 
<label><input type="radio" name="item_confirm" value="1" <?php if ($iteminfo->item_confirm) echo 'checked="checked"'; ?>> Yes</label></td></tr>
<tr><td align="right" class="ftitle">Verification:</td><td class="ffield">
<label><input type="radio" name="item_verify" value="0" <?php if (!$iteminfo->item_verify) echo 'checked="checked"'; ?>> No</label> 
<label><input type="radio" name="item_verify" value="1" <?php if ($iteminfo->item_verify) echo 'checked="checked"'; ?>> Yes</label></td></tr>
<tr><td align="right" class="ftitle">Verify Limit:</td><td class="ffield"><input name="item_verify_limit" class="field number" type="text" value="<?php echo $iteminfo->item_verify_limit; ?>"></td></tr>
<tr><td align="right" class="ftitle">Dependent Item:</td><td class="ffield"><select name="item_depend_item" class="field"><?php 
echo '<option value="0"'; if (0 == $iteminfo->item_depend_item) echo ' selected'; echo '>None</option>';
foreach ($items as $i) {
	echo '<option value="'.$i->item_id.'"'; if ($i->item_id == $iteminfo->item_depend_item) echo ' selected'; echo '>'.$i->item_title.'</option>';
}
?></select></td></tr>
</table>
<input name="task" type="hidden" value="saveitem">
<input name="item_page" type="hidden" value="<?php echo $page; ?>">
<input name="item_form" type="hidden" value="<?php echo $form; ?>">
<input name="mod" type="hidden" value="itemlist">
<input name="item_id" type="hidden" value="<?php echo $iteminfo->item_id; ?>">
<input name="ordering" type="hidden" value="<?php echo $iteminfo->ordering; ?>">

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
