<form action="" method="post" name="userform" id="userform">
<table border="0" cellspacing="0" cellpadding="0" class="userlist-form">
<tr><td align="right" class="ftitle">UserName:</td><td class="ffield"><input name="user_name" class="field required maxLength:40" type="text" title="Username must be 5-40 characters"  value="<?php echo $userinfo->usr_name; ?>"></td></tr>
<tr><td align="right" class="ftitle">Full Name:</td><td class="ffield"><input name="user_fullname" class="field required maxLength:150" type="text" title="Full Name must be 5-150 characters"  value="<?php echo $userinfo->usr_fullname; ?>"></td></tr>
<tr><td align="right" class="ftitle">EMail:</td><td class="ffield"><input name="user_email" class="field required validate-email" type="text" title="A valid email address is required"  value="<?php echo $userinfo->usr_email; ?>"></td></tr>
<tr><td align="right" class="ftitle">User Level:</td><td class="ffield"><select name="user_level" class="field required" title="User type is required">
			<option value="1" <?php echo ($userinfo->usr_level == '1' ? 'selected' : '') ?>>Basic</option>
			<option value="4" <?php echo ($userinfo->usr_level == '4' ? 'selected' : '') ?>>Editor</option>
			<option value="2" <?php echo ($userinfo->usr_level == '2' ? 'selected' : '') ?>>Admin</option>
			<option value="3" <?php echo ($userinfo->usr_level == '3' ? 'selected' : '') ?>>Root</option>
			</select></td></tr>
<tr><td align="right" class="ftitle">New Password:</td><td class="ffield"><input id="user_pass" name="user_pass" class="field required-without matchInput:'user_id' optionalMinLength:8 " type="password" title="Password must be at least 8 characters"></td></tr>
<tr><td align="right" class="ftitle">Confirm:</td><td class="ffield"><input id="user_passc" name="user_passc" class="field required-with validate-match matchInput:'user_pass'" type="password" title="Passwords must match"></td></tr>
</table>
<input name="task" type="hidden" value="saveuser">
<input name="mod" type="hidden" value="users">
<input name="user_id" id="user_id" type="hidden" value="<?php echo $userinfo->usr_id; ?>">

</form>

<script type="text/javascript">
	window.addEvent('load', function() {
	
		new Form.Validator.Inline($('userform'), {
			stopOnFailure: true,
			useTitles: true,
			errorPrefix: "",
			onFormValidate: function(passed, form, event) {
				if (passed) {
					form.submit();
				}
			}
		});
		
	
		FormValidator.add('optionalMinLength', {
			test: function(element,props) {
			    if (element.value.length > 0 && element.value.length < props.optionalMinLength) { return false; }
			    else return true;
			}
		});

		FormValidator.add('required-with', {
			test: function(element,props) {
				if (!element.value && document.id(props.matchInput).get('value')) { return false; }
				else return true;
			}
		});
		
		FormValidator.add('required-without', {
			test: function(element,props) {
				if (!element.value && !document.id(props.matchInput).get('value')) { return false; }
				else return true;
			}
		});
	});
</script>
<?php
