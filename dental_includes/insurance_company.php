<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,9)){exit;}
echo "<div class='grid_12 page_heading'>INSURANCE COMPANIES</div>";
$user=$user_name=$var='';

//add insurance compnay
if( isset($_POST['ins_name']) and $_POST['ins_name']!='' and $_SESSION['token']==$_POST['token']){
			$_SESSION['token']='';
			//check thata the compnay is not entered twice
			$sql=$error=$s='';$placeholders=array();
			$sql="select name from insurance_company where upper(name)=:name";
			$error="Unable to get insurance company";
			$placeholders[':name']=strtoupper($_POST['ins_name']);
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount()>0){
				$name=html($_POST['ins_name']);
				$error_message=" Unable to add Insurance Company $name as it already exists";
			}
			else{
				//insert insurance value
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into insurance_company set name=:name";
				$error="Unable to add insurance company";
				$placeholders[':name']=$_POST['ins_name'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				if($s){$success_message=" Insurance Company added ";}
					elseif(!$s){$error_message=" Unable to add Insurance Company ";}			
			}
	get_patient_types($pdo);		
}

//edit insurance compnay
if( isset($_POST['old_ins']) and $_POST['old_ins']!='' and $_SESSION['token2']==$_POST['token2']){
	$_SESSION['token2']='';
	//save entries
	$n=count($_POST['ninye']);
	$ins_id=$_POST['ninye'];
	$ins_name=$_POST['old_ins'];
	$old_invoice_daily_limit = $_POST['old_invoice_daily_limit'];
	if(isset($_POST['old_added_days'])){
		$added_days_new=$_POST['old_added_days'];
		$added_start_day=$_POST['old_start_date'];
	}
	$i=0;
	$exit_flag=true;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					//check if max limt is integer
					if($old_invoice_daily_limit[$i] != ''){
						$old_invoice_daily_limit[$i]=str_replace(",", "", $old_invoice_daily_limit[$i]);
						if(!ctype_digit($old_invoice_daily_limit[$i])){
							$var=html("$old_invoice_daily_limit[$i]");
							$error_message=" Unable to save details as Daily max invoice amount  $var is not a valid integer. ";
							$exit_flag=true;
							break;
						}
					}
					
					$sql=$error=$s='';$placeholders=array();
					#no added days
					if(!isset($_POST['old_added_days'])){
						$sql="update insurance_company set name=:name,invoice_daily_limit=:invoice_daily_limit where id=:id";
						
					}
					elseif(isset($_POST['old_added_days']) ){
						
						/*if( $added_start_day[$i]=='' and $added_days_new[$i]=='')
						{
							$i++;
							continue;
						}*/
						
						if($added_days_new[$i] > 0 and "$added_start_day[$i]" == ''){
							$var2=html("$ins_name[$i]");
							$error_message="   Please specify a start date for $var2 post dated invoices  ";
							$exit_flag=true;
							break;
						}
						
						if(($added_days_new[$i] =='' or $added_days_new[$i] <= 0) and "$added_start_day[$i]" != ''){
							$var2=html("$ins_name[$i]");
							$error_message="   Please specify number of post date days for $var2 ";
							$exit_flag=true;
							break;
						}
						
						//check date format
						if("$added_start_day[$i]"!=''){
							$date='';
							$date=explode('-',"$added_start_day[$i]");
							if(!checkdate( $date[1],$date[2],$date[0] )){
								$dob=html("$added_start_day[$i]");
								$exit_flag=true;
								$message="$dob is not a valid date";
								break;
							}
						}
		
							
						
						$sql="update insurance_company set name=:name , invoice_daily_limit=:invoice_daily_limit, added_days=:added_days, start_date=:start_date where id=:id";
						$placeholders[':added_days']="$added_days_new[$i]";
						$placeholders[':invoice_daily_limit']=$old_invoice_daily_limit[$i];
						$placeholders[':start_date']="$added_start_day[$i]";
					}
					$error="Unable to edit insurance company name";
					$placeholders[':name']="$ins_name[$i]";
					$placeholders[':id']=$encrypt->decrypt($ins_id[$i]);
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
						$sql="update insurance_company set listed=1 where id=:id";
						$error="Unable to delete insurance company name";
						$placeholders[':id']=$encrypt->decrypt($ins_id[$i]);
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	//first chck if the compnay has patients
						if(!$s and $exit_flag){$exit_flag=false;}	
						$i++;
				}	
			}
			
			if($exit_flag){$tx_result = $pdo->commit();}
			elseif(!$exit_flag){$tx_result=false;}
			if($tx_result){$success_message=" Insurance Companies Edited  ";}
			elseif(!$tx_result){$error_message="   Unable to edit Insurance Companies  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$error_message="   Unable to edit Insurance Companies   ";
	}
		
}
?>
	<div class="grid-100 margin_top"><div id=ins_price_ed class=grid-100></div>
	<?php include  'response.php'; ?>
		<form action="" method="post" name="" id="">
			<div class='grid-25 alpha'><label for="user" class="label"> Insurance Company </label></div><div class='grid-75 omega'><input type=text name=ins_name /></div>
			<?php $token = form_token(); $_SESSION['token'] = "$token";  ?>
		<input type="hidden" name="token"  value="<?php echo $_SESSION['token']; ?>" />
			<div class='grid-75 prefix-25'>	<br><input type="submit"  value="Add Insurer"/></div>
			<div class=clear></div>
			</form>
			
		
	
<?php
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from insurance_company order by name";
	$error="Unable to select insurance companies";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id=''><table class='insurance_companydd'><caption>Insurance Companies</caption><thead>
		<tr><th class=count22></th><th class=name22>Insurance Company Name</th><th class=inv_limit>Daily Max<br>Inv Amount</th><th class=day_col>";	
		if(userHasRole($pdo,119)){echo "Days to Post<br>Date Invoice";}
		else{echo "&nbsp";}
		echo "</th><th class=start_day_col>";	
		if(userHasRole($pdo,119)){echo "Post Date Invoice<br>From This Date";}
		else{echo "&nbsp";}
		echo "</th><th class=del22>Unlist</th><th class=ed22>Edit Prices</th><th class=ep22>Procedures Not Covered</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$name=html($row['name']);
			if($row['invoice_daily_limit'] == 0){$row['invoice_daily_limit']='';}
			$invoice_daily_limit=html($row['invoice_daily_limit']);
			$added_days=html($row['added_days']);
			if($added_days == 0){$added_days='';}
			$added_start_day=html($row['start_date']);
			if($added_start_day == '0000-00-00'){$added_start_day='';}
			$val=$encrypt->encrypt(html($row['id']));//
			$checked='';
			if($row['listed']==1){$checked = ' checked ';}
			echo "<tr><td class=count>$count</td><td><input type=text name=old_ins[] class=input_in_table_cell value='$name' />
			</td><td><input type=text name=old_invoice_daily_limit[] class=input_in_table_cell value='$invoice_daily_limit' /><td>";
			if(userHasRole($pdo,119)){echo "<input type=text name=old_added_days[] class=input_in_table_cell value='$added_days' />";}
			else{echo "&nbsp";}
			echo "</td><td>";
			if(userHasRole($pdo,119)){echo "<input type=text name=old_start_date[] class='date_picker input_in_table_cell' value='$added_start_day' />";}
			else{echo "&nbsp";}
			echo "</td><td><input type=checkbox name=del[] value='$val' $checked /></td>
			<td><input type=hidden name=ninye[] value='$val' /><input type=button class='button_in_table_cell button_style ins_price_edit' value='Edit Price' /></td>
			<td><input type=hidden name=ninye2[] value='$val' /><input type=button class='button_in_table_cell button_style ins_procedure_edit' value='Edit Procedure' /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token2'] = "$token";  
		echo "<input type=hidden name=token2  value='$_SESSION[token2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
