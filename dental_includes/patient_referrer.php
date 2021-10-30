<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,11)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT REFERRERS</div>";
$user=$user_name=$var='';

//add insurance compnay
if( isset($_POST['ref_name']) and $_POST['ref_name']!='' and $_SESSION['token']==$_POST['token']){
			$_SESSION['token']='';
			//check thata the compnay is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select name from patient_referrer where upper(name)=:name";
			$error="Unable to get patient referrer";
			$placeholders[':name']=strtoupper($_POST['ref_name']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount()>0){
				$name=html($_POST['ref_name']);
				$error_message=" Unable to add Patient Referrer $name as it already exists";
			}
			else{
				//insert insurance value
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into patient_referrer set name=:name, telephone=:telephone, email_address=:email";
				$error="Unable to add patient referrer";
				$placeholders[':name']=$_POST['ref_name'];
				$placeholders[':telephone']=$_POST['telephone_no'];
				$placeholders[':email']=$_POST['email_address'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Patient Referrers   added ";}
					elseif(!$s){$error_message=" Unable to add Patient Referrer ";}			
			}
}

//edit insurance compnay
if( isset($_POST['old_ref']) and $_POST['old_ref']!='' and $_SESSION['token2']==$_POST['token2']){
	$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$ref_id=$_POST['ninye'];
	$ref_name=$_POST['old_ref'];
	$ref_email=$_POST['old_email'];
	$ref_tel=$_POST['old_tel'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update patient_referrer set name=:name , telephone=:tel, email_address=:email where id=:id";
					$error="Unable to edit patient referrer name";
					$placeholders[':name']="$ref_name[$i]";
					$placeholders[':tel']="$ref_tel[$i]";
					$placeholders[':email']="$ref_email[$i]";
					$placeholders[':id']=$encrypt->decrypt($ref_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
				//now delete entries
			if(isset($_POST['del'])){
				$n=count($_POST['del']);
				$ref_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update patient_referrer set listed=1 where id=:id";
						$error="Unable to delete patient referrer";
						$placeholders[':id']=$encrypt->decrypt($ref_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	//first chck if the compnay has patients
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" Patient Referrers Edited  ";}
			elseif(!$tx_result){$error_message="   Unable to edit Patient Referrers  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$error_message="   Unable to edit Patient Referrers   ";
	}
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New Patient Referrer' class=button_style id=add_new_patient_referrer />
	<div  id="patient_referrer_form_div" >	
		<form action="" method="post" name="" id="">
			<div class='grid-20 alpha'><label for="user" class="label"> Referrer Name </label></div>
			<div class='grid-30'><input type=text name=ref_name /></div>
			<div class='grid-20'><label for="user" class="label"> Telephone </label></div>
			<div class='grid-30 omega'><input type=text name=telephone_no /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="user" class="label">Email Address </label></div>
			<div class='grid-30 suffix-50 omega'><input type=text name=email_address /></div>
			
			<?php $token = form_token(); $_SESSION['token'] = "$token";  ?>
		<input type="hidden" name="token"  value="<?php echo $_SESSION['token']; ?>" />
			<div class='grid-30 prefix-20 suffix-50'>	<br><input type="submit"  value="Add Referrer"/></div>
			<div class=clear></div>
			</form>
	</div>		
		
	
<?php
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from patient_referrer order by name";
	$error="Unable to select patient referrers";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='normal_table'><caption>Patient Referrers</caption><thead>
		<tr><th class=ref_count></th><th class=ref_name>Referrer Name</th><th class=ref_tel>Telephone</th><th class=ref_email>Email</th><th class=ref_del>Unlist</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$tel=html($row['telephone']);
			$email=html($row['email_address']);
			$val=$encrypt->encrypt(html($row['id']));
			$checked='';
			if($row['listed']==1){$checked=' checked ';}//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_ref[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td>
			<td><input type=text name=old_tel[] class=input_in_table_cell value='$tel' /></td>
			<td><input type=text name=old_email[] class=input_in_table_cell value='$email' /></td>
			<td><input type=checkbox name=del[] value='$val' $checked /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token2'] = "$token";  
		echo "<input type=hidden name=token2  value='$_SESSION[token2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
