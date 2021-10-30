<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,21)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT RELATIONSHIPS</div>";
$user=$user_name=$var='';

//add insurance compnay
if( isset($_POST['rel_name']) and $_POST['rel_name']!='' and $_SESSION['token']==$_POST['token']){
			$_SESSION['token']='';
			//check thata the relationship is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select name from patient_relationships where upper(name)=:name";
			$error="Unable to get patient relationship name";
			$placeholders[':name']=strtoupper($_POST['rel_name']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount()>0){
				$name=html($_POST['rel_name']);
				$error_message=" Unable to add Patient Relationship $name as it already exists";
			}
			else{
				//insert insurance value
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into patient_relationships set name=:name";
				$error="Unable to add patient relationship";
				$placeholders[':name']=$_POST['rel_name'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Patient Relationship added ";}
					elseif(!$s){$error_message=" Unable to add Patient Relationship ";}			
			}
}

//edit insurance compnay
if( isset($_POST['old_rel']) and $_POST['old_rel']!='' and $_SESSION['token2']==$_POST['token2']){
	$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$rel_id=$_POST['ninye'];
	$rel_name=$_POST['old_rel'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					$sql=$error=$s='';$placeholders=array();
					$sql="update patient_relationships set name=:name where id=:id";
					$error="Unable to edit patient relationships";
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
						$sql="delete from patient_relationships  where id=:id";
						$error="Unable to delete patient relationships";
						$placeholders[':id']=$encrypt->decrypt($rel_id[$i]);
					//	$s = 	insert_sql($sql, $placeholders, $error, $pdo);	first chck if the compnay has patients
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$pdo->rollBack();$tx_result=false;}
			if($tx_result){$success_message=" Patient Relationships Edited  ";}
			elseif(!$tx_result){$error_message="   Unable to edit Patient Relationships  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$error_message="   Unable to edit Patient Relationships   ";
	}
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New Patient Relationship' class='button_style pop_up_form'  />
	<div  id=patient_relationship_form_div title="New Patient Relationship">		
		<form action="" method="post" name="" id="">
			<div class='grid-25 alpha'><label for="user" class="label">Relationship Name </label></div><div class='grid-75 omega'><input type=text name=rel_name /></div>
			<?php $token = form_token(); $_SESSION['token'] = "$token";  ?>
		<input type="hidden" name="token"  value="<?php echo $_SESSION['token']; ?>" />
			<div class='grid-75 prefix-25'>	<br><input type="submit"  value="Add Relationship"/></div>
			<div class=clear></div>
			</form>
	</div>		
		
	
<?php
	//now show current patien relationships
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from patient_relationships order by name";
	$error="Unable to select patient relationships";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='insurance_company'><caption>Patient Relationships</caption><thead>
		<tr><th class=rel_count></th><th class=rel_name>Relationship Title</th><th class=del>Delete</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_rel[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td><td><input type=checkbox name=del[] value='$val' /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token2'] = "$token";  
		echo "<input type=hidden name=token2  value='$_SESSION[token2]' /><input type=submit  value='Submit Changes' /></form><br>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
