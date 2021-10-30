<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,86)){exit;}
echo "<div class='grid_12 page_heading'>BACKUP DATABASE</div>";
?>
<div class=grid-container>
<?PHP
$now=date('Y-m-d-g-i-s');
$file = "..\\dental_backups\\$now".".sql";
$command="\"C:\\Program Files\\VertrigoServ_230\\Mysql\\bin\\mysqldump.exe\"  --host=localhost --user=root --password=CoA!b_U dental --flush-logs --master-data=2 --delete-master-logs > $file";
//$command="\"C:\\Program Files\\VertrigoServ_230\\Mysql\\bin\\mysqldump.exe\"  --host=localhost --user=root --password=CoA!b_U dental_new  > $file";
exec($command, $ret_arr, $ret_code);

if($ret_code==0) {echo "<label class=label>Database backup  taken successfully</label>";}
?>
</div>