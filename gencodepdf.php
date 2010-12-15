<?php
require_once('lib/tcpdf/config/lang/eng.php');
require_once('lib/tcpdf/tcpdf.php');
include 'lib/request.php';
include 'lib/filterinput.php';
$code = JRequest::getVar('code');
$errorc = JRequest::getVar('errorc',"L");
$size = JRequest::getVar('size',4);
$name = JRequest::getVar('name');




$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('qr4all.com');
$pdf->SetTitle('QR Code generated from qr4all.com');
// set font
$pdf->SetFont('helvetica', '', 10);

$pdf->SetHeaderData('../../../qr4all.png', 50, urldecode($name),'qr4all.com');
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// add a page
$pdf->AddPage();
// set style for barcode
$style = array(
	'border' => false,
	'padding' => 0,
	'fgcolor' => array(0,0,0),
	'bgcolor' => false //array(255,255,255)
);

// QRCODE,L : QR-CODE Low error correction
$pdf->SetXY(30, 40);
$pdf->write2DBarcode(urldecode($code), 'QRCODE,'.$errorc, '', '', 100, 100, $style, 'N');
$pdf->Text(30, 150, urldecode($name));
$pdf->Text(30, 154, urldecode($code));
$pdf->Output(urldecode($name).'_qr4all_qrcode.pdf', 'D');