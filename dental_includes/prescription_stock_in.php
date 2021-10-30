<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,115)){exit;}
echo "<div class='grid_12 page_heading'>PRESCRIPTION DRUGS STOCK IN</div>";
$user=$user_name=$var='';



//add strock
if( isset($_POST['token_pres_sin1']) and isset($_SESSION['token_pres_sin1']) and $_SESSION['token_pres_sin1']==$_POST['token_pres_sin1']){
	$_SESSION['token_pres_sin1']='';
	//save entries
	$n=count($_POST['ninye']);
	$drug_id=$_POST['ninye'];
	$quantity=$_POST['quantity'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					if($quantity[$i]==''){$i++;continue;}
					$amount='';
									
					//check quanitty format
						$amount=str_replace(",", "", $quantity[$i]);				
						if(!ctype_digit($amount)){
							$invalid_value=html($amount);
							$exit_flag=false;
							$error_message="quantity specified, $invalid_value is not a valid number. ";
							break;
							/*//check if it has only 2 decimal places
							$data=explode('.',$amount);
							$invalid_value=html($amount);
							if ( count($data) != 2 ){
							$exit_flag=false;
							$error_message="quantity specified, $invalid_value is not a valid number. ";
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$exit_flag=false;
							$error_message="quantity specified, $invalid_value is not a valid number. ";
							break;
							}*/
						}
					
			
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into drug_stock_in set drug_id=:drug_id , when_added=now() , stock_in=:stock_in, added_by=:added_by";
					$error="Unable to add prescription drugs stock";
					$placeholders[':drug_id']=$encrypt->decrypt($drug_id[$i]);
					$placeholders[':stock_in']=$amount;
					$placeholders[':added_by']=$_SESSION['id'];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					
					//add stock
					$sql=$error=$s='';$placeholders=array();
					$sql="update drugs set quantity=(quantity + :stock_in) where id=:drug_id ";
					$error="Unable to add prescription drugs stock quantity";
					$placeholders[':drug_id']=$encrypt->decrypt($drug_id[$i]);
					$placeholders[':stock_in']=$amount;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		

			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$tx_result=false;}
			if($tx_result){$success_message=" Stock added  ";}
			elseif(!$tx_result){$pdo->rollBack();}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	//$error_message="   Unable to edit Insurance Companies   ";
	}
		
}
?>
	<div class="grid-100 margin_top">
	<?php include  '../dental_includes/response.php'; ?>

			
		
	
<?php
	
	$sql=$error=$s='';$placeholders=array();
	$sql="select name,id,drug_type from drugs where id > 1 order by name ";
	$error="Unable to select prescription drugs";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='drugs'><caption>Prescription Drugs Stock In</caption><thead>
		<tr><th class=presc_count3></th><th class=presc_name3>DRUG NAME</th><th class=presc_type3>DRUG TYPE</th><th class=presc_quantity3>QUANTITY</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$drug_type=html($row['drug_type']);
			//if($row['listed'] == 1){$checked=" checked ";}
			//else{$checked="";}
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td>$name</td><td>$drug_type</td>
			<td><input type=hidden name=ninye[] value='$val' /> <input type=text name=quantity[] class=input_in_table_cell  /></td>
			</tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_pres_sin1'] = "$token";  
		echo "<input type=hidden name=token_pres_sin1  value='$_SESSION[token_pres_sin1]' /><input class=put_right type=submit  value='Submit' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
