<form action="index.php" method="post" name="codeform" id="codeform">
<table border="0" cellspacing="0" cellpadding="0" class="codelist-form">
<tr><td align="right" class="ftitle">Video Int Title:</td><td class="ffield"><input name="vid_title" class="field required maxLength:40" type="text" title="Video title must be 5-40 characters"  value="<?php echo $vidinfo->vid_title; ?>"></td></tr>
<tr><td align="right" class="ftitle">Video Ext Title:</td><td class="ffield"><input name="vid_pubtitle" class="field required maxLength:40" type="text" title="Video title must be 5-40 characters"  value="<?php echo $vidinfo->vid_pubtitle; ?>"></td></tr>
<tr><td align="right" class="ftitle">Video File:</td><td class="ffield"><input name="vid_file" class="field required" type="text" title="A valid URL is required" value="<?php echo $vidinfo->vid_file; ?>"></td></tr>
<tr><td align="right" class="ftitle">Aspect Ratio:</td><td class="ffield"><select name="vid_ratio" class="field"><?php 

echo '<option value="43"';
if ("43" == $vidinfo->vid_ratio) echo ' selected';
echo '>4x3</option>';

echo '<option value="169"';
if ("169" == $vidinfo->vid_ratio) echo ' selected';
echo '>16x9</option>';


?></select></td></tr>
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
<tr><td align="right" class="ftitle">Video Domain:</td><td class="ffield"><select name="vid_domain" class="field required" title="Domain is required">
			<?php 
			foreach ($doms as $d) {
				echo '<option value="'.$d->dom_id.'"';
				if ($d->dom_id == $vidinfo->vid_domain) echo ' selected';
				echo '>'.$d->dom_dom.'</option>';
			}
			?>

			</select></td></tr>
<tr><td align="right" class="ftitle">Stream Domain:</td><td class="ffield"><select name="vid_sdomain" class="field required" title="Domain is required">
			<?php 
			foreach ($sdoms as $d) {
				echo '<option value="'.$d->dom_id.'"';
				if ($d->dom_id == $vidinfo->vid_sdomain) echo ' selected';
				echo '>'.$d->dom_dom.'</option>';
			}
			?>

			</select></td></tr>
<tr><td align="right" class="ftitle">Video Template:</td><td class="ffield"><select name="vid_tmpl" class="field required" title="Domain is required">
			<?php 
			foreach ($tmpls as $d) {
				echo '<option value="'.$d->tmpl_id.'"';
				if ($d->tmpl_id == $vidinfo->vid_tmpl) echo ' selected';
				echo '>'.$d->tmpl_name.'</option>';
			}
			?>

			</select></td></tr><tr><td align="right" class="ftitle">Return Title:</td><td class="ffield"><input name="vid_rettitle" class="field maxLength:255" type="text" title="Must be 5-255 characters"  value="<?php echo $vidinfo->vid_rettitle; ?>"></td></tr>
<tr><td align="right" class="ftitle">Return URL:</td><td class="ffield"><input name="vid_returl" class="field validate-url maxLength:255" type="text" title="Must be 5-255 characters and a URL"  value="<?php echo $vidinfo->vid_returl; ?>"></td></tr>
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
