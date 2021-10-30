<?php
//this will file will provide include files for the ajax loaded files so that i dont't rpeat the same in all of them
include_once  '../../dental_includes/magicquotes.inc.php'; 
include_once   '../../dental_includes/db.inc.php'; 
include_once   '../../dental_includes/dbsession.php';
$session = new dbSession($pdo);
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';
$encrypt = new Encryption();

?>
<!--	
<script type="text/javascript"> </script>
<link rel="stylesheet" type="text/css" media="all" href="dental_css/reset.css" />
<link rel="stylesheet" type="text/css" media="all" href="dental_css/text.css" />

<link rel="stylesheet" type="text/css" media="all" href="dental_css/unsemantic-grid-responsive.css" />
<link rel="stylesheet" type="text/css" media="all" href="dental_css/jquery-ui-1.9.2.custom.min.css" />
<link rel="stylesheet" type="text/css" media="all" href="dental_css/hide.css" />
<link rel="stylesheet" type="text/css" media="all" href="dental_css/style1.css" />--><!--
<script type="text/javascript" src="dental_b/jquery-1.8.3.js"></script>
<script type="text/javascript" src="dental_b/jquery-ui-1.9.2.custom.min.js"></script>-->
<script type="text/javascript" src="dental_b/jquery-1.8.3.js"></script>
<script type="text/javascript" src="dental_b/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="dental_b/menu.js"></script>
<script type="text/javascript" src="dental_b/jquery.printElement.min.js"></script>

