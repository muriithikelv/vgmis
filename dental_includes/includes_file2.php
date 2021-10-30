<?php
//this will file will provide include files for the ajax loaded files so that i dont't rpeat the same in all of them
include_once  '../../dental_includes/magicquotes.inc.php'; 
include_once   '../../dental_includes/db.inc.php'; 
include_once   '../../dental_includes/dbsession.php';
$session = new dbSession($pdo);
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';
include_once    '../../dental_includes/phpmailer/class.phpmailer.php';
include_once     '../../dental_includes/fpdf/fpdf.php';

//include_once     '../../dental_includes/fpdf/wordwrap.php';
$encrypt = new Encryption();
$mail = new PHPMailer_mine();
?>