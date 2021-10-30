<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,39)){exit;}
echo "<div class='grid_12 page_heading'>Clinics</div>";
$user=$user_name=$var='';

//add dental chair
if( isset($_POST['surgery_name']) and $_POST['surgery_name']!='' and $_SESSION['token_surgery1']==$_POST['token_surgery1']){
			$_SESSION['token_surgery1']='';
			//check thata the name is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select surgery_name from surgery_names where upper(surgery_name)=:name";
			$error="Unable to get surgery names";
			$placeholders[':name']=strtoupper($_POST['surgery_name']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount()>0){
				$name=html($_POST['surgery_name']);
				$error_message=" Unable to add dental surgery name, $name as it already exists";
			}
			else{
				//insert dental chair value
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into surgery_names set surgery_name=:name";
				$error="Unable to add dental chair ";
				$placeholders[':name']=$_POST['surgery_name'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Dental surgery name added ";}
					elseif(!$s){$error_message=" Unable to add dental surgery name ";}			
			}
}

//edit dental chairs
if( isset($_POST['old_chair']) and $_POST['old_chair']!='' and $_SESSION['token_surgery2']==$_POST['token_surgery2']){
	$_SESSION['token_surgery2']='';
	//save entries
	$n=count($_POST['ninye']);
	$rel_id=$_POST['ninye'];
	$rel_name=$_POST['old_chair'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update surgery_names set surgery_name=:name where surgery_id=:id";
					$error="Unable to edit dental chairs";
					$placeholders[':name']="$rel_name[$i]";
					$placeholders[':id']=$encrypt->decrypt($rel_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
				//now delete entries
			if(isset($_POST['del'])){
				$n=count($_POST['del']);
				$ins_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from surgery_names  where surgery_id=:id";
						$error="Unable to delete dental chair";
						$placeholders[':id']=$encrypt->decrypt($rel_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);//	first chck if the compnay has patients
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" Dental surgery names edited  ";}
			elseif(!$tx_result){$error_message="   Unable to edit dental surgery names  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$error_message="   Unable to edit dental surgery names   ";
	}
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New Clinic Unit' class='button_style pop_up_form'  />
	<div  id=patient_relationship_form_div title="New Dental Surgery Name">		
		<form action="" method="post" name="" id="">
			<div class='grid-25 alpha'><label for="user" class="label">Clinics </label></div><div class='grid-75 omega'><input type=text name=surgery_name /></div>
			<?php $token = form_token(); $_SESSION['token_surgery1'] = "$token";  ?>
		<input type="hidden" name="token_surgery1"  value="<?php echo $_SESSION['token_surgery1']; ?>" />
			<div class='grid-75 prefix-25'>	<br><input type="submit"  value="Submit"/></div>
			<div class=clear></div>
			</form>
	</div>		
		
	
<?php
	//now show current patien relationships
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from surgery_names order by surgery_name";
	$error="Unable to select dental chairs";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='insurance_company'><caption>Clinics</caption><thead>
		<tr><th class=rel_count></th><th class=rel_name>Clinics</th><th class=del>Delete</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['surgery_name']);
			$val=$encrypt->encrypt(html($row['surgery_id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_chair[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td><td><input type=checkbox name=del[] value='$val' /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_surgery2'] = "$token";  
		echo "<input type=hidden name=token_surgery2  value='$_SESSION[token_surgery2]' /><input type=submit  value='Submit Changes' /></form><br>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
