<?php
include 'lib/phpqrcode/qrlib.php';
include 'lib/request.php';
include 'lib/filterinput.php';
$code = JRequest::getVar('code');
$errorc = JRequest::getVar('errorc',"L");
$size = JRequest::getVar('size',4);
QRcode::png(urldecode($code), null, $errorc, $size, 2);