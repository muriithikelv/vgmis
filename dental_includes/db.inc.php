<?php
//this will connect to old databse and willbe removed after migratiion
/*
try
{
  $pdo_old = new PDO('mysql:host=localhost:3307;dbname=dental', 'root', 'CoA!b_U');  //when changin this remember to change also the file connect_to_session_db.php as it also has credentials
  $pdo_old->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo_old->exec('SET NAMES "utf8"');
}
catch (PDOException $e)
{
	echo 'Connection to old database failed: ' ;
  exit();
}
*/
//end migration connection
//$//time = microtime(true);
try
{
  $pdo = new PDO('mysql:host=127.0.0.1:3306;dbname=live', 'root', '');  //when changin this remember to change also the file connect_to_session_db.php as it also has credentials
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec('SET NAMES "utf8"');

}
catch (PDOException $e)
{
	echo 'Connection to database failed: ' ;
  exit();

}



//$time2 = microtime(true);
//echo "connection time is ";echo $time2 - $time; echo " end";

//this will get the salt for passwords
$salt = '994e4623659dccc023a92e6eb532e970cccce8f1jkhfkjdjugr434809834';

//set imaop timeout
/*$read=imap_timeout(IMAP_READTIMEOUT, 180);
$open=imap_timeout(IMAP_OPENTIMEOUT, 180);
$write=imap_timeout(IMAP_WRITETIMEOUT, 180);
$close=imap_timeout(IMAP_CLOSETIMEOUT, 180);

*/

//this will return last insert id
function get_insert_id($sql, $placeholders, $error, $pdo){
  try
  {
    $s = $pdo->prepare($sql);
    $s->execute($placeholders);
  }
  catch (PDOException $e){
  	echo "$error ";

	echo 'Error: ' . $e->getMessage() . '<br />';
	echo 'Code: ' . $e->getCode() . '<br />';
	echo 'File: ' . $e->getFile() . '<br />';
	echo 'Line: ' . $e->getLine() . '<br />';	$e->get.
	exit();
  }
  return $pdo->lastInsertId();
}

//this will return $s object which can be used to check if the tx was okay
function insert_sql($sql, $placeholders, $error, $pdo){
  try
  {
    $s = $pdo->prepare($sql);
   $result = $s->execute($placeholders);
  }
  catch (PDOException $e)
  {
	echo "$error ";
	echo 'Error: ' . $e->getMessage() . '<br />';
	echo 'Code: ' . $e->getCode() . '<br />';
	echo 'File: ' . $e->getFile() . '<br />';
	echo 'Line: ' . $e->getLine() . '<br />';	$e->get.
	//$now=date("Y-m-d H:i:s");
	//error_log("$now xxx " . $e->getMessage() . ' '.$e->getCode() .' '. $e->getFile() .' '.$e->getLine() ." \n ", 3, "../dental_includes/my_errors.log");
	exit();
  }
  return $result;
}

//this will do a select
function select_sql($sql, $placeholders, $error, $pdo){
//$name='';
//$items= array();
  try
  {
    $s = $pdo->prepare($sql);
     $s->execute($placeholders);

  }
  catch (PDOException $e)
  {

		//	echo "<div class='grid_100 error_response'>ERROR: $error</div> ";
	echo 'Error: ' . $e->getMessage() . '<br />';
	echo 'Code: ' . $e->getCode() . '<br />';
	echo 'File: ' . $e->getFile() . '<br />';
	echo 'Line: ' . $e->getLine() . '<br />';	$e->get.
	//$now=date("Y-m-d H:i:s");
	//error_log("$now ff " . $e->getMessage() . ' '.$e->getCode() .' '. $e->getFile() .' '.$e->getLine() ." \n ", 3, "../dental_includes/my_errors.log");
	exit();
  }
return $s;
}
