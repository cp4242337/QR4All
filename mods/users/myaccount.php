<table border="0" cellspacing="0" cellpadding="0" class="userlist-form">
<tr><td align="right" class="ftitle">UserName:</td><td class="ffield"><?php echo $userinfo->usr_name; ?></td></tr>
<?php 
if ($userinfo->usr_type == 'trial' || $userinfo->usr_type == 'paid' || $userinfo->usr_type == 'exp') {
	?>
	<tr><td align="right" class="ftitle">Expiration Date:</td><td class="ffield"><?php echo $userinfo->usr_expdate; ?></td></tr>
	<tr><td align="right" class="ftitle">Last Bill Date:</td><td class="ffield"><?php echo $userinfo->usr_lastbilldate; ?></td></tr>
	<tr><td align="right" class="ftitle">Next Bill Date:</td><td class="ffield"><?php echo $userinfo->usr_nextbilldate; ?></td></tr>
	<?php 
}
?>

<tr><td align="right" class="ftitle">Full Name:</td><td class="ffield"><?php echo $userinfo->usr_fullname; ?></td></tr>
<tr><td align="right" class="ftitle">EMail:</td><td class="ffield"><?php echo $userinfo->usr_email; ?></td></tr>
<!-- <tr><td align="right" class="ftitle">Address:</td><td class="ffield"><?php 
	echo $userinfo->usr_address1; 
	if ($userinfo->usr_address2) echo '<br>'.$userinfo->usr_address2;
	echo '<br>'.$userinfo->usr_city.' '.$userinfo->usr_state.', '.$userinfo->usr_zip;
?></td></tr>
<tr><td align="right" class="ftitle">Phone:</td><td class="ffield"><?php echo $userinfo->usr_phone; ?></td></tr>
<tr><td align="right" class="ftitle">Fax:</td><td class="ffield"><?php echo $userinfo->usr_fax; ?></td></tr> -->
</table>
<?php
