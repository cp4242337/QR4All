<?php
$errorc=JRequest::getVar('gencode-ecl','L');
$size=JRequest::getVar('gencode-sz','4');
echo '<form name="codeops" action="" method="post">';
echo 'Error Correction: <select name="gencode-ecl" onchange="document.codeops.submit()" class="field">';
echo '<option value="L"'.(($errorc=='L')?' selected':'').'>L - smallest</option>';
echo '<option value="M"'.(($errorc=='M')?' selected':'').'>M</option>';
echo '<option value="Q"'.(($errorc=='Q')?' selected':'').'>Q</option>';
echo '<option value="H"'.(($errorc=='H')?' selected':'').'>H - best</option>';
echo '</select>';
echo ' Size: <select name="gencode-sz" onchange="document.codeops.submit()" class="field">';
for($i=4;$i<=12;$i++) echo '<option value="'.$i.'"'.(($size==$i)?' selected':'').'>'.$i.'</option>';
echo '</select>';
echo '</form>';
foreach ($codes as $c) {  
	foreach ($c->cats as $t) { 
		if ($t->codes) {
			echo '<div class="codelist-client">'.$c->cl_name.'<br>';
			echo '<div class="codelist-cat">'.$t->cat_name.'<br><div class="codelist-codes">'; 
			echo '<table border="0" class="codelist-table" cellspacing="0" cellpadding"0"><tr>';
			foreach ($t->codes as $d) { 
				echo '<th>'.$d->cd_name.'</th>';
			} 
			echo '</tr><tr>';
			foreach ($t->codes as $d) { 
				$codeurl = 'http://'.$d->cd_type.'.qr4all.com/'.$d->cd_code;
				echo '<td>';
				echo '<img src="gencode.php?code=';
				echo urlencode($codeurl);
				echo '&size='.$size.'&errorc='.$errorc.'"><br />';
				echo '<a href="gencodepdf.php?code=';
				echo urlencode($codeurl).'&name='.urlencode($d->cd_name);
				echo '&size='.$size.'&errorc='.$errorc.'" target="_blank">Generate PDF</a>';
				echo '<br />'.$codeurl;
				echo '</td>';
			} 
			echo '</tr></table></div></div></div>';
		}
		
	}  
}
