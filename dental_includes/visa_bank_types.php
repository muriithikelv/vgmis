<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,106)){exit;}
echo "<div class='grid_12 page_heading'>VISA BANKS</div>";


//add new bank
if( isset($_POST['token_vb_1']) and isset($_SESSION['token_vb_1']) and $_SESSION['token_vb_1']==$_POST['token_vb_1']){
			$_SESSION['token']='';
			//check thata the bank is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select name from visa_banks where upper(name)=:name";
			$error="Unable to check visa banks";
			$placeholders[':name']=strtoupper($_POST['bank_name']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($_POST['percent_charge']!=''){
				$amount=str_replace(",", "", $_POST["percent_charge"]);				
				if(!ctype_digit($amount)){
					//check if it has only 2 decimal places
					$data=explode('.',$amount);
					$invalid_value=html($amount);
					if ( count($data) != 2 ){
					
					$error_message="Percentage charge specified, $invalid_value is not a valid value. ";
					}
					elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
					$error_message="Percentage charge specified, $invalid_value is not a valid value. ";
					}
				}
			}			
			if($s->rowCount()>0){
				$name=html($_POST['bank_name']);
				$error_message=" Unable to add $name as it already exists";
			}
			elseif($_POST['bank_name']==''){$error_message=" No bank name has been specified. ";}
			elseif($_POST['percent_charge']==''){$error_message=" No percentage charge has been specified. ";}
			else{
				//insert visa bank
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into visa_banks set name=:name, percent_charge=:percent_charge";
				$error="Unable to add visa babk";
				$placeholders[':name']=$_POST['bank_name'];
				$placeholders[':percent_charge']=$_POST['percent_charge'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Bank added ";}
					elseif(!$s){$error_message=" Unable to add bank ";}			
			}
		
}

//edit visa banks
if( isset($_POST['token_vb_2']) and isset($_SESSION['token_vb_2']) and $_SESSION['token_vb_2']==$_POST['token_vb_2']){
	$_SESSION['token_vb_2']='';
	//save entries
	$n=count($_POST['ninye']);
	$bank_id=$_POST['ninye'];
	$old_bank=$_POST['old_bank'];
	$old_charge=$_POST['old_charge'];
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					//check if the fileds are empty
					if($old_charge[$i]=='' or $old_bank[$i]==''){$i++;continue;}
			
					//check percent charge format
					if($old_charge[$i]!=''){
						$amount=str_replace(",", "", $old_charge[$i]);				
						if(!ctype_digit($amount)){
							//check if it has only 2 decimal places
							$data=explode('.',$amount);
							$invalid_value=html($amount);
							if ( count($data) != 2 ){
							$exit_flag=false;
							$error_message="Percentage charge specified, $invalid_value is not a valid value. ";
							break;
							}
							elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$exit_flag=false;
							$error_message="Percentage charge specified, $invalid_value is not a valid value. ";
							break;
							}
						}
					}
			
					$sql=$error=$s='';$placeholders=array();
					$sql="update visa_banks set name=:name , percent_charge=:percent_charge where id=:id";
					$error="Unable to edit visa banks";
					$placeholders[':name']="$old_bank[$i]";
					$placeholders[':percent_charge']="$old_charge[$i]";
					$placeholders[':id']=$encrypt->decrypt($bank_id[$i]);
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s and $exit_flag){$exit_flag=false;}		
					$i++;
			}
		
			//before unlisting list all
				$sql=$error=$s='';$placeholders=array();
				$sql="update visa_banks set listed=0";
				$error="Unable to list visa banks";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			//now unlist drugs
			if(isset($_POST['unlist'])){
				$n=count($_POST['unlist']);
				$bank_id=$_POST['unlist'];
				$i=0;
				while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update visa_banks set listed=1 where id=:id";
						$error="Unable to unlist visa banks";
						$placeholders[':id']=$encrypt->decrypt("$bank_id[$i]");
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$tx_result=false;}
			if($tx_result){$success_message=" VISA banks edited  ";}
			elseif(!$tx_result){$pdo->rollBack();;}	
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
	<?php include  'response.php'; ?>
		<form action="" method="post" name="" id="">
			<div class='grid-10 '><label for="user" class="label">Bank Name</label></div>
			<div class='grid-40 '><input type=text name=bank_name /></div> <!-- drug -->
			<div class='grid-15 '><label for="user" class="label">Percentage Charge</label></div>
			<div class='grid-10 '><input type=text name=percent_charge /></div><!-- sell_price -->
			<div class='grid-15'><span class=label>%</span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit"  value="Add Bank"/></div>
			<?php $token = form_token(); $_SESSION['token_vb_1'] = "$token";  ?>
		<input type="hidden" name="token_vb_1"  value="<?php echo $_SESSION['token_vb_1']; ?>" />
			<div class=clear></div>
			</form>
			
		
	
<?php
	//now show current visa banks
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from visa_banks order by name";
	$error="Unable to select visa banks";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='drugs'><caption>VISA enabled Banks</caption><thead>
		<tr><th class=presc_count></th><th class=presc_name>BANK NAME</th><th class=presc_price>PERCENTAGE CHARGE</th><th class=presc_del>UNLIST</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			$percentage_charge=html($row['percent_charge']);
			if($percentage_charge > 0){$percentage_charge=number_format($percentage_charge,2);}
			if($row['listed'] == 1){$checked=" checked ";}
			else{$checked="";}
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_bank[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td><td><input type=text name=old_charge[] class=input_in_table_cell value='$percentage_charge' /></td>
			<td><input type=checkbox name=unlist[] value='$val' $checked /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_vb_2'] = "$token";  
		echo "<input type=hidden name=token_vb_2  value='$_SESSION[token_vb_2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
