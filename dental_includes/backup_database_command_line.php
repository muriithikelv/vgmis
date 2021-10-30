<?PHP
$now=date('Y-m-d-g-i-s');
$file = "\"C:\\Program Files\\VertrigoServ_230\\dental_backups\\$now".".sql";
$command="\"C:\\Program Files\\VertrigoServ_230\\Mysql\\bin\\mysqldump.exe\"  --host=localhost --user=root --password=CoA!b_U dental --flush-logs --master-data=2 --delete-master-logs > $file";
//$command="\"C:\\Program Files\\VertrigoServ_230\\Mysql\\bin\\mysqldump.exe\"  --host=localhost --user=root --password=CoA!b_U dental  > $file";

exec($command, $ret_arr, $ret_code);

//if($ret_code==0) {echo "Database backup  taken successfully";}
?>
