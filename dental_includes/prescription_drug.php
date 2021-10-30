<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,61)){exit;}
echo "<div class='grid_12 page_heading'>PRESCRIPTION DRUGS</div>";
$user=$user_name=$var='';

//add prescription drug
if( isset($_POST['token_pres_1']) and isset($_SESSION['token_pres_1']) and $_SESSION['token_pres_1']==$_POST['token_pres_1']){
			$_SESSION['token']=$amount='';
			//check thata the drug is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select name from drugs where upper(name)=:name";
			$error="Unable to check drugs";
			$placeholders[':name']=strtoupper($_POST['drug']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($_POST['sell_price']!=''){
				$amount=str_replace(",", "", $_POST["sell_price"]);				
				if(!ctype_digit($amount)){
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_value=html($amount);
					if ( count($data) != 2 ){
					
					$error_message="price specified, $invalid_value is not a valid number. ";
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$error_message="price specified, $invalid_value is not a valid number. ";
					}
				}
			}
			if($error_message==''){
				if($s->rowCount()>0){
					$name=html($_POST['drug']);
					$error_message=" Unable to add prescription drug $name as it already exists";
				}
				elseif($_POST['drug']==''){$error_message=" No prescription drug has not been specified. ";}
				elseif($_POST['drug_type']=='' or ($_POST['drug_type']!='Tablet' and $_POST['drug_type']!='Syrup' ) ){$error_message=" Prescription drug type has not been specified. ";}
				else{
					//insert prescription drug
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into drugs set name=:name, selling_price=:selling_price, drug_type=:drug_type";
					$error="Unable to add prescription drug";
					$placeholders[':name']=$_POST['drug'];
					$placeholders[':drug_type']=$_POST['drug_type'];
					$placeholders[':selling_price']=$amount;
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if($s){$success_message=" Prescription drug added ";}
						elseif(!$s){$error_message=" Unable to add prescription drug ";}			
				}
			}
		
}

//edit insurance compnay
if( isset($_POST['token_pres_2']) and isset($_SESSION['token_pres_2']) and $_SESSION['token_pres_2']==$_POST['token_pres_2']){
	$_SESSION['token_pres_2']='';
	//save entries
	$n=count($_POST['ninye']);
	$drug_id=$_POST['ninye'];
	$drug_name=$_POST['old_drug'];
	$drug_price=$_POST['old_price'];
	$drug_type=$_POST['old_drug_type'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					$amount='';
					//check if drug type is set
					if($drug_type[$i]=='' or ($drug_type[$i]!='Tablet' and $drug_type[$i]!='Syrup' ) ){
						$error_message=" Prescription drug type has not been specified for all prescription drugs. ";
						$exit_flag=false;
						break;
					}
					
					//check price format
					elseif($drug_price[$i]!=''){
						$amount=str_replace(",", "", $drug_price[$i]);				
						if(!ctype_digit($amount)){
							//check if it has only 2 decimal places
							$data=explode('.',$amount);
							$invalid_value=html($amount);
							if ( count($data) != 2 ){
							$exit_flag=false;
							$error_message="price specified, $invalid_value is not a valid number. ";
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$exit_flag=false;
							$error_message="price specified, $invalid_value is not a valid number. ";
							break;
							}
						}
					}
			
					$sql=$error=$s='';$placeholders=array();
					$sql="update drugs set name=:name , selling_price=:price , drug_type=:drug_type where id=:id";
					$error="Unable to edit prescription drugs";
					$placeholders[':name']="$drug_name[$i]";
					$placeholders[':price']="$amount";
					$placeholders[':drug_type']="$drug_type[$i]";
					$placeholders[':id']=$encrypt->decrypt($drug_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
			if($exit_flag){
				//before unlisting list all
					$sql=$error=$s='';$placeholders=array();
					$sql="update drugs set listed=0";
					$error="Unable to list drugs";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				//now unlist drugs
				if(isset($_POST['unlist'])){
					$n=count($_POST['unlist']);
					$drug_id=$_POST['unlist'];
					$i=0;
					while($i < $n){
							$sql=$error=$s='';$placeholders=array();
							$sql="update drugs set listed=1 where id=:id";
							$error="Unable to unlist prescription drugs";
							$placeholders[':id']=$encrypt->decrypt($drug_id[$i]);
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
							if(!$s and $exit_flag){$exit_flag=false;}	
							$i++;
					}	
				}
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$tx_result=false;}
			if($tx_result){$success_message=" Prescription drugs edited  ";}
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
		<form action="" method="post" name="" id="">
			<div class='grid-10 '><label for="user" class="label">Drug Name</label></div>
			<div class='grid-40 '><input type=text name=drug /></div>
			<div class='grid-5 '><label for="user" class="label">Type</label></div>
			<div class='grid-10 '>
				<select name=drug_type>
					<option value=""></option>
					<option value="Tablet">Tablet</option>
					<option value="Syrup">Syrup</option>
				</select>
			</div>
			<div class='grid-10 '><label for="user" class="label">Selling Price</label></div>
			<div class='grid-10 '><input type=text name=sell_price /></div>

			<div class='grid-10'><input type="submit"  value="Submit"/></div>
			<?php $token = form_token(); $_SESSION['token_pres_1'] = "$token";  ?>
		<input type="hidden" name="token_pres_1"  value="<?php echo $_SESSION['token_pres_1']; ?>" />
			<div class=clear></div>
			</form>
			
		
	
<?php
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from drugs where id > 1 order by name ";
	$error="Unable to select prescription drugs";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='drugs'><caption>Prescription Drugs</caption><thead>
		<tr><th class=presc_count></th><th class=presc_name>DRUG NAME</th><th class=presc_type>DRUG TYPE</th><th class=presc_price>SELLING PRICE</th><th class=presc_del>UNLIST</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$price=html($row['selling_price']);
			$drug_type=html($row['drug_type']);
			$tablet =$syrup=$empty='';
			if("$drug_type" == 'Tablet'){$tablet = ' selected ';}
			elseif("$drug_type" == 'Syrup'){$syrup = ' selected ';}
			if($tablet!='' or $syrup!=''){$empty='<option value=></option>';}
			if($price > 0){$price=number_format($price,2);}
			else{$price='';}
			if($row['listed'] == 1){$checked=" checked ";}
			else{$checked="";}
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_drug[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td><td><select name=old_drug_type[]>
					<option value='Tablet' $tablet>Tablet</option>
					<option value='Syrup' $syrup>Syrup</option></select></td>
			<td><input type=text name=old_price[] class=input_in_table_cell value='$price' /></td>
			<td><input type=checkbox name=unlist[] value='$val' $checked /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_pres_2'] = "$token";  
		echo "<input type=hidden name=token_pres_2  value='$_SESSION[token_pres_2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
