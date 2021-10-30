<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,38)){exit;}
echo "<div class='grid_12 page_heading'>PASSWORD CHANGE</div>";


//change password
if( isset($_POST['token_pc_1']) and isset($_SESSION['token_pc_1']) and $_SESSION['token_pc_1']==$_POST['token_pc_1']){
			$_SESSION['token_pc_1']='';
			$exit_flag=false;
			//check current password is corrcet 
			if($_POST['current_password']==''){
				$exit_flag=true;
				$error_message=" Current password was not specified. ";
			}
			if(!$exit_flag and $_POST['new_password1']==''){
				$exit_flag=true;
				$error_message=" New password was not specified. ";
			}
			if(!$exit_flag and $_POST['new_password2']==''){
				$exit_flag=true;
				$error_message=" New passwords do not macth. ";
			}
			if(!$exit_flag and $_POST['new_password2']!=$_POST['new_password1']){
				$exit_flag=true;
				$error_message=" New passwords do not macth. ";
			}
			
			
			if(!$exit_flag){
				$current_password = hash_hmac('sha1', $_POST['current_password'], $salt);
				//check if the current password is for the logged in user
				$sql=$error=$s='';$placeholders=array();
				$sql = "select id from users where password = :password and id=:id ";
				$placeholders[':id'] = $_SESSION['id'];
				$placeholders[':password'] = "$current_password";
				$error = "Unable to change password";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()==0){
					$error_message="Incorrect current password!!";
					$exit_flag=true;
				}	
				if(!$exit_flag){
					//check password criteria
					$pwd = $_POST['new_password1'];

					if(!$exit_flag and  strlen($pwd) < 8 ) {
						$error_message="Password is too short";
						$exit_flag=true;
					}

					elseif( !$exit_flag and   !preg_match("#[0-9]+#", $pwd) ) {
						$error_message="Password must include at least one number";
						$exit_flag=true;
					}

					elseif(!$exit_flag and    !preg_match("#[a-z]+#", $pwd) ) {
						$error_message="Password must include at least one lower case letter";
						$exit_flag=true;
					}


					elseif(!$exit_flag and    !preg_match("#[A-Z]+#", $pwd) ) {
						$error_message="Password must include at least one upper case letter";
						$exit_flag=true;
					}


					elseif(!$exit_flag and    !preg_match("#\W+#", $pwd)){//("#\W+#", $pwd) ) {
						$error_message="Password must include at least one special character";
						$exit_flag=true;
					}

					//check last six password
					if(!$exit_flag){
						$new_password = hash_hmac('sha1', $_POST['new_password1'], $salt);
						//check if the password matches any of the last six
						$sql=$error=$s='';$placeholders=array();
						$sql="select id,user_id, old_pass from old_passes where user_id=:user_id order by id  desc";
						$error="11";
						$placeholders[':user_id'] = $_SESSION['id'];
						$s= select_sql($sql, $placeholders, $error, $pdo);
						$password_count=$old_id=0;
						$password_count=$s->rowCount();
						foreach($s as $row){
							if($row['old_pass']=="$new_password"){
								$error_message="Your new password must not match any of your last six passwords";
								$exit_flag=true;
								break;
							}
						}
						
						//get record to delete if any
						if($password_count == 6){
							$s= select_sql($sql, $placeholders, $error, $pdo);
							foreach($s as $row){$old_id=$row['id'];}
						}
					}
					
					//update password
					if(!$exit_flag){
						$sql=$error=$s='';$placeholders=array();
						$sql="update users set password=:password , date_of_last_password_change=now() where id=:id";
						$error="11";
						$placeholders[':id'] = $_SESSION['id'];
						$placeholders[':password'] = "$new_password";
						$s= 	insert_sql($sql, $placeholders, $error, $pdo);
						
						//insert into old_pass
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into old_passes set user_id=:user_id, old_pass=:old_pass";
						$error="11";
						$placeholders[':user_id'] = $_SESSION['id'];
						$placeholders[':old_pass'] = "$new_password";
						$s= 	insert_sql($sql, $placeholders, $error, $pdo);	
						
						//now delete the oldes entry for this guy
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from old_passes where id=:id";
						$error="11";
						$placeholders[':id'] = $old_id;
						$s= 	insert_sql($sql, $placeholders, $error, $pdo);						
						
						if($s){$success_message=" Password changed ";}
						elseif(!$s){$error_message=" Unable to change password ";}	
					}
				}
			}
		
}


?>
<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
		<div class='grid-50 alpha'>
			
				<form action="" method="post" name="" id="">
					<div class='grid-30 '><label for="user" class="label">Current Password</label></div>
					<div class='grid-40 '><input type=password name=current_password value=' ' /></div> <!-- drug -->
					<div class=clear></div><br>
					<div class='grid-30 '><label for="user" class="label">New Password</label></div>
					<div class='grid-40 '><input type=password name=new_password1 /></div> <!-- drug -->
					<div class=clear></div><br>
					<div class='grid-30 '><label for="user" class="label">Re-type New Password</label></div>
					<div class='grid-40 '><input type=password name=new_password2 /></div> <!-- drug -->
					<div class=clear></div><br>
					<div class='prefix-30 grid-15'><input type="submit"  value="Submit"/></div>
					<?php $token = form_token(); $_SESSION['token_pc_1'] = "$token";  ?>
				<input type="hidden" name="token_pc_1"  value="<?php echo $_SESSION['token_pc_1']; ?>" />
					
					</form>
		</div>
		<div class='grid-50 omega label'>	
			New password must:<br>
			Be at least 8 characters long<br>
			Include at least one number [0-9]<br>
			Include at least one lower case letter [a-z]]<br>
			Include at least one upper case letter [A-Z]]<br>
			include at least one special character  e.g. !# <br>
		</div>
</div>
