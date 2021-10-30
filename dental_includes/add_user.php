<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,25)){exit;}
echo "<div class='grid_12 page_heading'>USER MANAGEMENT</div>";


?>
<div class=grid-container>
	<?php 
	//insert or update user
	if(isset($_POST['token_add_user1']) and $_POST['token_add_user1']!='' 
	and $_POST['token_add_user1']==$_SESSION['token_add_user1']){
	if($_POST['action_type']=='add_user'){
		$_SESSION['user_first_name']=$_SESSION['user_middle_name']=$_SESSION['user_last_name']=$_SESSION['user_gender']='';
		$_SESSION['user_photo_path']=$_SESSION['user_address']=$_SESSION['user_mobile_no']=$_SESSION['user_home_phone']='';
		$_SESSION['user_email_address']=$_SESSION['status']=$_SESSION['user_login_id']='';
		$_SESSION['user_type']=$_SESSION['user_login_name']=$_SESSION['user_login_id']='';	
	}
	elseif($_POST['action_type']=='edit_user'){
		//get user profile
		$sql=$error1=$s='';$placeholders=array();
		$sql="select * from users where id=:id";
		$error="Unable to get user details";
		$placeholders[':id']=$encrypt->decrypt("$_POST[current_users]");	
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$_SESSION['user_first_name']=html($row['first_name']);
			$_SESSION['user_middle_name']=html($row['middle_name']);
			$_SESSION['user_last_name']=html($row['last_name']);
			$_SESSION['user_gender']=html($row['gender']);
			$_SESSION['user_photo_path']=html($row['photo_image']);
			$_SESSION['user_address']=html($row['address']);
			$_SESSION['user_mobile_no']=html($row['mobile_number']);
			$_SESSION['user_home_phone']='';html($row['home_phone']);
			$_SESSION['user_email_address']=html($row['email_address']);
			$_SESSION['user_type']=html($row['user_type']);
			$_SESSION['user_login_name']=html($row['user_name']);
			$_SESSION['user_login_id']=$row['id'];	
			$_SESSION['status']=$row['status'];	
		}		
	}	
	?>
	<div class=grid-100 >
	<div class='feedback '></div>
	<?php //include  '../../dental_includes/response.php'; 
			$_SESSION['tab_name']="";
			
	?>
			<fieldset><legend>User Names</legend>
	<form action="" method="POST" enctype="multipart/form-data" name="" id="" class='patient_form'>

	<input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>

			<div class='grid-75'>
				<!--first name-->
				<div class='grid-15'>
					<?php 
					$action=$encrypt->encrypt("$_POST[action_type]");
					$token = form_token(); $_SESSION['token_add_user2'] = "$token";  ?>
	<input type="hidden" name="token_add_user2"  value="<?php echo $_SESSION['token_add_user2']; ?>" />
	<input type="hidden" name="to_do"  value="<?php echo $action; ?>" />	
		
				<label for="" class="label">First Name </label></div>
				<div class='grid-25'><input type=text name=first_name value='<?php echo $_SESSION['user_first_name']; ?>' /></div>
				
				<!--second name-->
				<div class='grid-15'><label for="" class="label">Middle Name </label></div>
				<div class='grid-25'><input type=text name=middle_name value='<?php echo $_SESSION['user_middle_name']; ?>' /></div>
				<div class=clear></div><br>
				<!--last name-->
				<div class='grid-15'><label for="" class="label">Last Name </label></div>
				<div class='grid-25'><input type=text name=last_name value='<?php echo $_SESSION['user_last_name']; ?>' /></div>
					<!--gender-->
				<div class='grid-15'><label for="" class="label">Gender</label></div>
				<div class='grid-25'><select name=gender><option></option>
				<?php if($_SESSION['user_gender']=='MALE'){ $male_selected=" selected ";$female_selected="";}
					  elseif($_SESSION['user_gender']=='FEMALE'){ $male_selected="";$female_selected=" selected ";}
					  else{ $male_selected="";$female_selected="";}?>
					<option value='MALE' <?php echo $male_selected; ?> >MALE</option><option value='FEMALE' <?php echo $female_selected; ?> >FEMALE</option></select></div>
			
	<div class=clear></div>				
			

				</div>
			<div class='grid-25'>
		
				<?php if(!isset($_SESSION['user_photo_path']) or $_SESSION['user_photo_path']==''){$_SESSION['user_photo_path']="/profile/patient_photo.png";}?>
				<div class=''><label for="" class="label"><img src='<?php echo "$_SESSION[user_photo_path]"; ?>' /> </label></div>
				<div class=''><input type=file value='Upload Patient Photo' name=image_upload /></div>

			</div>

			
		</fieldset>
		<div class=clear></div>
	
		<!--this will have contacts etc -->
		<fieldset><legend>User Contacts</legend>
			
				<!--address-->
				<div class='grid-15'><label for="" class="label">Address</label></div>
				<div class='grid-25'><input type=text name=address value='<?php echo $_SESSION['user_address']; ?>' /></div>
				
				<!--home phone-->
				<div class='grid-10'><label for="" class="label">Mobile No.</label></div>
				<div class='grid-20'><input type=text name=user_mobile_no value='<?php echo $_SESSION['user_mobile_no']; ?>' /></div>
				
				<!--home phone-->
				<div class='grid-15'><label for="" class="label">Home phone No.</label></div>
				<div class='grid-15'><input type=text name=user_home_phone value='<?php echo $_SESSION['user_home_phone']; ?>' /></div>
				<div class=clear></div><br>
				<!--email address-->
				<div class='grid-15'><label for="" class="label">Email Address</label></div>
				<div class='grid-25'><input type=text name=user_email_address value='<?php echo $_SESSION['user_email_address']; ?>' /></div>						
				
				<div class=clear></div><br>		
				
			
			<div class=clear></div>
		

		</fieldset>
		<fieldset><legend>Login Details</legend>
			
				<!--user type-->
				<div class='grid-15'><label for="" class="label">User Type</label></div>
				<?php $doctor_val=$encrypt->encrypt("1");
					$non_doctor_val=$encrypt->encrypt("0");
					if($_SESSION['user_type']=='1'){ $doctor=" checked=checked ";$non_doctor="";}
					  elseif($_SESSION['user_type']=='0'){ $non_doctor=" checked=checked ";$doctor="";}
						else{$non_doctor="";$doctor="";}		
					
				echo "	
				<div class='grid-25'><input type=radio name=user_type value='$doctor_val'  $doctor />
									<label for='' class=label>Doctor</label>
									<input type=radio name=user_type value='$non_doctor_val' $non_doctor />
									<label for='' class=label>Non-Doctor</label>"; ?>
				</div>
				<div class=clear></div><br>		
				<!--login name-->
				<div class='grid-15'><label for="" class="label">Login Name</label></div>
				<div class='grid-25'><input type=text name=user_login_name value='<?php echo $_SESSION['user_login_name']; ?>' /></div>
				
				<?php
				if($_SESSION['user_login_id']==''){ ?>
					<!--user passowrd 1-->
					<div class='grid-15'><label for="" class="label">Login Passowrd</label></div>
					<div class='grid-25'><input type=password name=user_password1 /></div>
					<br>
					<div class=clear></div>
					<br>
					<!--user passowrd 2-->
					<div class='prefix-40 grid-15'><label for="" class="label">Re-type Password</label></div>
					<div class='grid-25'><input type=password name=user_password2 /></div>						
				<?php } 
				elseif($_SESSION['user_login_id']!=''){ ?>
					<!--reset password-->
					<div class='grid-50'><input type=checkbox name=reset_password value='reset' />
						<label for="" class="label">Reset password to username and prompt password change after first login</label>
					</div>
					<br>
					<div class=clear></div>
					<br>
					<!--lock account -->
					<?php
						$locked='';
						if($_SESSION['status']=='locked'){$locked=" checked=checked ";}
					echo "
					<div class='prefix-40 grid-50'><input $locked type=checkbox name=lock_account value='lock_account' />"; ?>
						<label for="" class="label">Lock account to prevent login</label>
					</div>
										
				<?php } 				
				
				?>

				
				

				
			
		

			

			<div class=clear></div>
	<div class='grid-25 prefix-15'>	<br><input type="submit"  value="Submit"/></div>	
	</form>
		</fieldset>		
	</div>
	<?php exit; } ?>
	<!--this is for selcting action to perform-->
	<?php if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
	?>
			<fieldset><legend>Select Action</legend>
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_add_user1'] = "$token";  ?>
	<input type="hidden" name="token_add_user1"  value="<?php echo $_SESSION['token_add_user1']; ?>" />
		
	<label for="" class="label">Select Action</label></div>
	<div class='grid-25'><select class='input_in_table_cell add_user_action' name=action_type><option></option>
						<option value='add_user'>Add New User</option>
						<option value='edit_user'>Edit User Profile</option>
						</select></div>
	<div class=clear></div>
	<br>
	<div class='grid-100 grid-parent select_user'>
		<div class='grid-15 alpha'><label for="" class="label">Select User</label></div>
		<div class='grid-45 omega'><select class=input_in_table_cell name=current_users><option></option>
			<?php
				$sql=$error=$s='';$placeholders=array();
				$sql = "select id,first_name, middle_name, last_name  from users order by first_name";
				$error = "Unable to list users";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				foreach($s as $row){
					$name=html("$row[first_name] $row[middle_name] $row[last_name]" );
					$val=$encrypt->encrypt(html($row['id']));							
					echo "<option value='$val'>$name</option>";
				}
			
			?>					
			</select/>
		</div>
		<div class=clear></div>
	</div>
	<div class='grid-25 prefix-15'>	<br><input type="submit"  value="Submit"/></form></div>
<div class=clear></div>
			
		</fieldset>	
</div>