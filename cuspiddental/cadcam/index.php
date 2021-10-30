<?php
if(!isset($_SESSION))
{
session_start();
}/*
include_once  '../../dental_includes/magicquotes.inc.php'; 
include_once   '../../dental_includes/db.inc.php'; 
include_once   '../../dental_includes/DatabaseSession.class.php';
include_once   '../../dental_includes/access.inc.php';
include_once   '../../dental_includes/encryption.php';
include_once    '../../dental_includes/helpers.inc.php';*/
include_once     '../../dental_includes/includes_file.php';
?>	<div id=cadcam_tabs>
		<ul>
		<?php
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select id, name from cadcam_types where listed=0 and level = 1 order by name";
		$error2="Unable to get manufacturers for cadcam";
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		foreach($s2 as $row2){
			$menu_name=html("$row2[name]");
			$var=urlencode($encrypt->encrypt($row2['id']));
			echo 	"<li ><a class='tab_link' href='dental_b/?cms=$var' >$menu_name</a></li>";
			
		}
		?>
		</ul>
	</div>