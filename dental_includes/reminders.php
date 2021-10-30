<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,40)){exit;}
echo "<div class='grid_12 page_heading'>REMINDERS</div>";


//add new bank
if( isset($_POST['token_rm_1']) and isset($_SESSION['token_rm_1']) and $_SESSION['token_rm_1']==$_POST['token_rm_1']){
			$_SESSION['token_rm_1']='';
			$exit_flag=false;
			//check date 
			if($_POST['reminder_date']==''){
				$exit_flag=true;
				$error_message=" Reminder date has not been specified. ";
			}
			if(!$exit_flag and $_POST['reminder']==''){
				$exit_flag=true;
				$error_message=" Reminder text has not been specified. ";
			}
			
			if(!$exit_flag)	{
				$date='';
				$date=explode('-',$_POST['reminder_date']);
				if(!checkdate( $date[1],$date[2],$date[0] )){
				$dob=html($_POST['reminder_date']);
				$exit_flag=true;
				$error_message=" Unable to save reminder as date of reminder $dob is not in the correct format";		
				}
			}			
			if(!$exit_flag){
				//insert reminder
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into reminders set
						when_added=now(),
						created_by=:created_by,
						description=:description,
						reminder_date=:reminder_date,
						approved='no'";
				$error="11";
				$placeholders[':created_by']=$_SESSION['id'];
				$placeholders[':description']=$_POST['reminder'];
				$placeholders[':reminder_date']=$_POST['reminder_date'];
				$s= 	insert_sql($sql, $placeholders, $error, $pdo);
				if($s){$success_message=" Reminder added ";}
					elseif(!$s){$error_message=" Unable to add reminder ";}			
			}
		
}

//clear reminders
if( isset($_POST['token_rm_2']) and isset($_SESSION['token_rm_2']) and $_SESSION['token_rm_2']==$_POST['token_rm_2']){
	$_SESSION['token_rm_2']='';
	try{
		$pdo->beginTransaction();	
			//now clear reminders
			if(isset($_POST['clear_reminder'])){
				$n=count($_POST['clear_reminder']);
				$reminder_id=$_POST['clear_reminder'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update reminders set approved='yes' where id=:id";
						$error="Unable to clear reminders";
						$placeholders[':id']=$encrypt->decrypt("$reminder_id[$i]");
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						$i++;
				}
				$tx_result = $pdo->commit();
				if($tx_result){$success_message=" Changes saved  ";}
				elseif(!$tx_result){$pdo->rollBack();;}
			}
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to edit Insurance Companies   ";
	}
		
}

if(userHasRole($pdo,109) and !isset($_POST['user'])){
	$token = form_token(); $_SESSION['token_rm_3'] = "$token";
	?>
	<form action="" class='' method="post" name="" id="">
		<input type="hidden" name="token_rm_3"  value='<?php echo "$_SESSION[token_rm_3]"; ?>' />
		<div class='grid-10 label'>Select User</div>
		<div class='grid-20'>
			<select name=user><option value='all'>All Users</option>
			<?php
				//get users with open reminders
				$sql=$error=$s='';$placeholders=array();
				$sql="SELECT DISTINCT created_by FROM reminders WHERE approved = 'no' ";
				$error="Error: Unable to get users ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				$users_array=array();
				foreach($s as $row){$users_array[]=html($row['created_by']);}
				

				$sql=$error=$s='';$placeholders=array();
				$sql="select first_name, middle_name, last_name, id from users ";
				$error="Error: Unable to get users ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					if(!in_array($row['id'],$users_array)){continue;}
					$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$id=$encrypt->encrypt("$row[id]");
					echo "<option value=$id>$name</option>";
				}
			?>
			</select>
		</div>
		<input type=submit value='Submit' />
	</form>	
	<?php
	exit;
}	

if(isset($_SESSION['token_rm_3']) and isset($_POST['token_rm_3']) and $_POST['token_rm_3']==$_SESSION['token_rm_3']){
	$_SESSION['token_rm_3']='';
	if($_POST['user']=='all'){$user_id='all';}
	elseif($_POST['user']!='all'){$user_id=$encrypt->decrypt("$_POST[user]");}
}
if(!isset($user_id) or (isset($user_id) and $user_id==$_SESSION['id'])){
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
		<form action="" method="post" name="" id="">
			<div class='grid-10 '><label for="user" class="label">Remind me to</label></div>
			<div class='grid-50 '><input type=text name=reminder /></div> <!-- drug -->
			<div class='grid-10 '><label for="user" class="label">On this date</label></div>
			<div class='grid-10 '><input type=text name=reminder_date class=date_picker_no_past /></div><!-- sell_price -->
			<div class='grid-15'><input type="submit"  value="Add Reminder"/></div>
			<?php $token = form_token(); $_SESSION['token_rm_1'] = "$token";  ?>
		<input type="hidden" name="token_rm_1"  value="<?php echo $_SESSION['token_rm_1']; ?>" />
			<div class=clear></div>
			</form>
			
		
	
<?php
	}
	//now show current reminders
	$sql=$error=$s='';$placeholders=array();
	$created_by='';
	if(!isset($user_id)){
		$created_by =" created_by=:created_by and ";
		$placeholders[':created_by']=$_SESSION['id'];
	} 
	elseif(isset($user_id)){
		if($user_id!='all'){
			$created_by =" created_by=:created_by and ";
			$placeholders[':created_by']=$user_id;
		}
		elseif($user_id=='all'){
			
		}
	}
	$sql="select a.when_added,a.description,a.reminder_date,a.id, b.first_name, b.middle_name, b.last_name 
		from reminders a , users b where $created_by approved='no' and 
			reminder_date <= now() and a.created_by=b.id";
	$error="Unable to select reminders";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='normal_table'><caption>Uncleared Reminders</caption><thead>
		<tr><th class=rm_count></th><th class=rm_date>DATE CREATED</th><th class=rm_creater>OWNER</th>
		<th class=rm_details>REMINDER</th>
		<th class=rm_date>DUE DATE</th><th class=rm_clear>CLEAR</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$owner=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
			$reminder=html($row['description']);
			$date_created=html($row['when_added']);
			$date_due=html($row['reminder_date']);
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td >$count</td><td >$date_created</td><td >$owner</td><td >$reminder</td><td >$date_due</td>
				<td><input type=checkbox name=clear_reminder[] value='$val'  /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_rm_2'] = "$token";  
		echo "<input type=hidden name=token_rm_2  value='$_SESSION[token_rm_2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
