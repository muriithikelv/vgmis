<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,63)){exit;}
echo "<div class='grid_12 page_heading'>EXPENSE TYPES</div>";
$user=$user_name=$var='';

//add insurance compnay
if( isset($_POST['token_et2']) and isset($_SESSION['token_et2']) and $_SESSION['token_et2']==$_POST['token_et2']){
			$_SESSION['token_et2']='';
			$exit_flag=false;
			//check that expense name is not empty
			if(!$exit_flag and $_POST['exp_name']==''){
				//{echo "<div class='$result_class'>$result_message</div>";}
				$error_message="   Expense type name was not set   ";
				$exit_flag=true;
			}
			if(	!$exit_flag){
				//check thata the expense type is not entered twice
				$sql=$error=$s='';$placeholders=array();
				$sql="select name from expense_types where upper(name)=:name";
				$error="Unable to get expense types";
				$placeholders[':name']=strtoupper($_POST['exp_name']);
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				if($s->rowCount()>0){
					$name=html($_POST['exp_name']);
					$error_message=" Unable to add expense type $name as it already exists";
				}
				else{
					//insert expense type
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into expense_types set name=:name";
					$error="Unable to add expense type";
					$placeholders[':name']=$_POST['exp_name'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if($s){$success_message=" Expense type added ";}
						elseif(!$s){$error_message=" Unable to add expense type ";}			
				}
			}
			
}

//edit expense types
if( isset($_POST['token_et1']) and isset($_SESSION['token_et1']) and $_SESSION['token_et1']==$_POST['token_et1']){
	$_SESSION['token_et1']='';
	//save entries
	$n=count($_POST['ninye']);
	$exp_id=$_POST['ninye'];
	$exp_name=$_POST['old_exp'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update expense_types set name=:name where id=:id";
					$error="Unable to edit expense types";
					$placeholders[':name']="$exp_name[$i]";
					$placeholders[':id']=$encrypt->decrypt($exp_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
				//now delete entries
			if(isset($_POST['del'])){
				$n=count($_POST['del']);
				$exp_id=$_POST['del'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update expense_types set deleted=1  where id=:id";
						$error="Unable to unlist expense type";
						$placeholders[':id']=$encrypt->decrypt($exp_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" Expense types edited  ";}
			elseif(!$tx_result){$error_message="   Unable to edit expense types  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to edit Patient Relationships   ";
	}
	
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New Expense Type' class='button_style pop_up_form'  />
	<div  id=patient_relationship_form_div title="New Expense Type">		
		<form action="" method="post" name="" id="">
			<div class='grid-25 alpha'><label for="user" class="label">Expense Name </label></div><div class='grid-75 omega'><input type=text name=exp_name /></div>
			<?php $token = form_token(); $_SESSION['token_et2'] = "$token";  ?>
		<input type="hidden" name="token_et2"  value="<?php echo $_SESSION['token_et2']; ?>" />
			<div class='grid-75 prefix-25'>	<br><input type="submit"  value="Add Expense Type"/></div>
			<div class=clear></div>
			</form>
	</div>		
		
	
<?php
	//now show current patien relationships
	$sql=$error=$s='';$placeholders=array();
	$sql="select name, deleted,id from expense_types order by name";
	$error="Unable to select expense types";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='expense_types'><caption>Expense Types</caption><thead>
		<tr><th class=exp_count></th><th class=exp_name>EXPENSE</th><th class=exp_del>UNLIST</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$val=$encrypt->encrypt(html($row['id']));//
			$checked='';
			if($row['deleted']==1){$checked=" checked ";}
			echo "<tr><td>$count</td><td><input type=text name=old_exp[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td><td><input type=checkbox name=del[] $checked value='$val' /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_et1'] = "$token";  
		echo "<input type=hidden name=token_et1  value='$_SESSION[token_et1]' /><input type=submit  value='Submit Changes' /></form><br>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
