
<h2 align="center">Welcome, Login below</h2>
<form id="login" name="login" method="post" action="" style="margin:0px;vertical-align:middle;">
	<table align="center" cellpadding="3" cellspacing="1">
		<tr><td align="right" valign="top">Username:</td><td><input name="user" type="text" class="field required" id="user" size="15" maxlength="60" tabindex="1" title="Username is Required"></td></tr>
		<tr><td align="right" valign="top">Password:</td><td><input name="passwd" type="password" class="field required" id="pass" size="15" maxlength="60" tabindex="2" title="Password is Required"></td></tr>
		<tr><td colspan="2" align="center"><input type="hidden" name="task" value="loginuser"><input type="hidden" name="mod" value="users"><input name="submit" type="submit" value="Login" tabindex="4"></td></tr>
	</table>
</form>
		
<script type="text/javascript">
	window.addEvent('load', function() {
	
	new Form.Validator.Inline($('login'), {
		stopOnFailure: true,
		useTitles: true,
		errorPrefix: ""
		});
	});
</script>