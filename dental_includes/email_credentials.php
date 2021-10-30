<?php
/*if(!isset($_SESSION))
{
session_start();
}*/

$_SESSION['pid']='';
if(!userIsLoggedIn() or !userHasRole($pdo,114)){exit;}
echo "<div class=grid-100><div class='grid_12 page_heading'>EMAIL CREDENTIALS </div>";






?>
<div class="feedback hide_element"></div>
	<?php
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']=='success_response'){
			echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';
			
			//test connection
			if(isset($_SESSION['test_connection']) and $_SESSION['test_connection']=='yes'){
				$_SESSION['test_connection']='';
				echo "<br><div class=grid_100><input  class='button_style test_all_emails' type='button' value='Test Email Accounts'/></div>";
				echo "<div class=grid_100></div>";
				
			}
		}

	}
		
	
	//now show current emails
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from smtp_users  order by id ";
	$error="Unable to select emails users";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){ ?>
	<form action="" method="post" name="" id="" class='patient_form'>
			<?php
		$count=0;
		echo "<br><br>";
		echo"	
		<table class='normal_table  '><caption>Email Accounts</caption><thead>
			<tr><th class=ema_count></th><th class=ema_name>Account Name</th><th class=ema_address>Email Address</th>
				<th class=ema_pasw>Account Password</th><th class=ema_desc>Account Description</th>
			</tr></thead>
		<tbody>";
		foreach($s as $row){
			$count++;
			$email_address=html($row['smtp_user_name']);
			$password=html($row['smtp_password']);
			$description=html($row['description']);
			$id=$encrypt->encrypt($row['id']);
			$name=html($row['from_name']);
			if($password!=''){$password=$encrypt->decrypt("$password");}
			
			echo "<tr><td>$count</td><td><input type=text  name='name[]' value='$name'  /><input type=hidden name='ninye[]' value='$id' /></td>
				<td><input type=text  name='email_add[]' value='$email_address'  /></td><td><input type=text  name='passwd[]' value='$password'  /></td>
				<td>$description</td></tr>";
			
		}
		echo "</tbody></table>";
		echo "";
	   $token = form_token(); $_SESSION['token_em'] = "$token";  ?>
		<input type="hidden" name="token_em"  value="<?php echo $_SESSION['token_em']; ?>" />
			<div class='grid-30'>	<input type="submit"  value="Submit"/></div>
			<div class=clear></div>
			</form>	
		<?php
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
