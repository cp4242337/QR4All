<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Detailed Reference:</td><td class="ffield"><input name="qa_whodetail" class="field" type="text" title="Reset Text must be 2-100 characters"  value="<?php echo $qainfo->qa_whodetail; ?>"></td></tr>
<tr><td align="right" class="ftitle">Short Reference:</td><td class="ffield"><input name="qa_who" class="field" type="text" title="Redirect URL must be a url"  value="<?php echo $qainfo->qa_who; ?>"></td></tr>
<tr><td align="right" class="ftitle">Q&A Instruction Content:</td><td class="ffield"><textarea name="qa_instruct" class="farea"><?php echo $qainfo->qa_instruct; ?></textarea><br><br>
<?php 
if ($aitems) {
	echo '<b>Available items for Q&A:</b><br>';
	foreach ($aitems as $i) {
		echo '{i'.$i->item_id.'} - '.$i->item_title.'<br>';
	}
	echo '<br>Place the {i##} element where you want the answer to that item';
	
} else {
	echo 'No items available for content or new page';
}


?>
</td></tr>
</table>
<input name="task" type="hidden" value="saveQA">
<input name="page_form" type="hidden" value="<?php echo $form; ?>">
<input name="mod" type="hidden" value="pagelist">
<input name="qa_page" type="hidden" value="<?php echo $qainfo->qa_page; ?>">

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
