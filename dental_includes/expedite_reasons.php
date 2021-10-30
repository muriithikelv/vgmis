<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,121)){exit;}
echo "<div class='grid_12 page_heading'>EXPEDITE REASONS</div>";


//add new reason
if( isset($_POST['token_vb_1']) and isset($_SESSION['token_vb_1']) and $_SESSION['token_vb_1']==$_POST['token_vb_1']){
			$_SESSION['token']='';
			//check thata the reason is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select reason from expedite_reasons where upper(reason)=:reason";
			$error="Unable to check expedite reasons";
			$placeholders[':reason']=strtoupper($_POST['reason']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
 		
			if($s->rowCount()>0){
				$name=html($_POST['reason']);
				$error_message=" Unable to add reason: $name, as it already exists";
			}
			elseif($_POST['reason']==''){$error_message=" No expedite reason has been specified. ";}
			else{
				//insert expedite reason
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into expedite_reasons set reason=:reason ";
				$error="Unable to add expedite reason";
				$placeholders[':reason']=$_POST['reason'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Expedite reason added ";}
					elseif(!$s){$error_message=" Unable to expedite reason ";}			
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
			<div class='grid-20 '><label for="user" class="label">Reason to expedite patient</label></div>
			<div class='grid-20 '><input type=text name='reason' /></div> <!-- drug -->
			 
			<div class='grid-5'> <input type="submit"  value="Submit"/></div>
			<?php $token = form_token(); $_SESSION['token_vb_1'] = "$token";  ?>
		<input type="hidden" name="token_vb_1"  value="<?php echo $_SESSION['token_vb_1']; ?>" />
			<div class=clear></div>
			</form>
			
		
	
<?php
	//now show current reasons
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from expedite_reasons order by reason";
	$error="Unable to select reasons";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='half_width'><caption>Reasons to expedite patients<caption><thead>
		<tr><th class=presc_count></th><th class=presc_name>REASON</th> <th class=presc_del>UNLIST</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$reason=html($row['reason']);
			if($row['unlist'] == 1){$checked=" checked ";}
			else{$checked="";}
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_reason[] class=input_in_table_cell value='$reason' />
			<input type=hidden name=ninye[] value='$val' /></td> 
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
