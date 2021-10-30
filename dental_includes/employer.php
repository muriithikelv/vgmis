<?php
/*if(!isset($_SESSION))
{
session_start();
}*/

$_SESSION['pid']='';
if(!userIsLoggedIn() or !userHasRole($pdo,10)){exit;}
echo "<div class='grid_12 page_heading'>INSURED COMPANIES </div>";


//this will insert new company
if( isset($_POST['employer_name']) and $_POST['employer_name']!='' and $_SESSION['token2'] == $_POST['token2']){
	$_SESSION['token2']='';
	//save entries
	$i=0;
	$n=1;
	
	//$insured_yes_no=html($_POST['insured_yes_no']);
	$insured_yes_no=$_POST['insured_yes_no'];
	$comp_name=html($_POST['employer_name']);
	$ins_id[$i]=$_POST['ins_name'];
	//$ins_id=$_POST['ins_name'];
	$pre_auth_needed[$i]=$_POST['pre_auth'];
	$smart_needed[$i]=$_POST['smart_check'];
	$co_pay_type[$i]=$_POST['co_pay'];
	$co_pay_val[$i]=$_POST['co_pay_value'];
	$start_cover[$i]=$_POST['start_date'];
	$end_cover[$i]=$_POST['end_date'];
	$cover_type[$i]=$_POST['cover_type'];
	$cover_limit[$i]=$_POST['cover_limit'];
	
	$exit_flag=false;
	try{
		$pdo->beginTransaction();	
			while($i < $n){
					//echo "$insured_yes_no -- $insured_yes_no[$i]";exit;
					//check in insurer is set
					$insured_yes_no[$i]=html("$insured_yes_no[$i]");
					if($insured_yes_no[$i]=='YES' and $ins_id[$i]==''){$error_message = " This patient type is insured but no insurer has been specified";
														$exit_flag=true;
														break;
						}
					//now check cover limit
					
					if(isset($cover_limit[$i]) and $cover_limit[$i]!=''){
						if($cover_limit[$i]!='UNLIMITED'){
							$cover_limit[$i]=str_replace(",", "", "$cover_limit[$i]");
							if( !ctype_digit($cover_limit[$i])){
								//check if it has only 2 decimal places
								$data=explode('.',$cover_limit[$i]);
								if ( count($data) != 2 ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$error_message=" Unable to save changes as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
								elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
									$cover_limit[$i]=html("$cover_limit[$i]");
									$error_message=" Unable to save changes as $cover_limit[$i] is not a valid number ";
									$exit_flag=true;
									break;
								}
							}
						}
					}	
					else{$cover_limit[$i]='';}

					//now check start and end date
					$data=explode("-",$start_cover[$i]);
					if(isset($start_cover[$i]) and $start_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$start_cover[$i]=html("$start_cover[$i]");
							$error_message=" Unable to save changes as $start_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}
					$data=explode("-",$end_cover[$i]);
					if(isset($end_cover[$i]) and $end_cover[$i]!='' and !checkdate($data[1], $data[2], $data[0])){
							$end_cover[$i]=html("$end_cover[$i]");
							$error_message=" Unable to save changes as $end_cover[$i] is not a valid date ";
							$exit_flag=true;
							break;
						}					
					//this will ensure that insurance is not empty
					/*if($ins_id[$i]==''){
						//check if pre-auth is set
						$error_message = " Unable to add new corprate as no insurer has been specified";
														$exit_flag=true;
														break;
						
					}*/
					
				//ensure all fields are correctly set
					if($ins_id[$i]==''){
						//check if pre-auth is set
						if($pre_auth_needed[$i]=='YES'){$error_message = " Unable to add new employer, Pre-Auth needed has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if smart is set
						if($smart_needed[$i]=='YES'){$error_message = " Unable to add new employer, Smart Check Needed  has been set to YES for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!=''){		$co_pay=html("$co_pay_type[$i]");
														$error_message = " Unable to add new employer, Co-Pay Type has been set to $co_pay for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if co_pay_val is set
						if($co_pay_val[$i]!=''){$co_pay_amount=html("$co_pay_val[$i]");
												$error_message = " Unable to add new employer, Co-Pay Value has been set to $co_pay_amount for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if start_cover is set
						if($start_cover[$i]!=''){$start=html("$start_cover[$i]");
						$error_message = " Unable to add new employer, Start Cover has been set to $start for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]!=''){$end=html("$end_cover[$i]");
						$error_message = " Unable to add new employer, End Cover has been set to $end for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover_type is set
						if($cover_type[$i]!=''){$cover_t=html("$cover_type[$i]");
													$error_message = " Unable to add new employer, Cover Type has been set to $cover_t for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]!=''){$cover_l=html("$cover_limit[$i]");
													$error_message = " Unable to add new employer, Cover Limit has been set to $cover_l for $comp_name but
														this company has no insurer set";
														$exit_flag=true;
														break;
						}
						
					}
					//this ios for when insurer is specified
					elseif($ins_id[$i]!='' and $encrypt->decrypt("$ins_id[$i]")!=3){//i.e not cash
						//check if pre-auth is set
						if($pre_auth_needed[$i]==''){$error_message = " Unable to add new corprate, Pre-Auth needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message="an attempt has been made to make pre-auth needed empty for $comp_name in table covered_company";
														log_security($pdo,$message);
														break;
						}
						//check if smart is set
						if($smart_needed[$i]==''){$error_message = " Unable to add new corprate, Smart Check needed has not been set  for $comp_name yet
														the company is insured";
														$exit_flag=true;
														$message="an attempt has been made to make smart check run needed empty for $comp_name in table covered_company";
														log_security($pdo,$message);
														break;
						}
						//check if co_pay is set
						if($co_pay_type[$i]!='' and $co_pay_val[$i]==''){		$co_pay=html("$co_pay_type[$i]");
														$error_message = " Unable to add new corprate, Co-Pay Type has been set to $co_pay for $comp_name but
														but no corresponding value has been set";
														$exit_flag=true;
														break;
						}
						//check if co_value is set
						if($co_pay_type[$i]=='' and $co_pay_val[$i]!=''){		$co_pay_amount=html("$co_pay_val[$i]");
														$error_message = " Unable to add new corprate, Co-Pay Value  has been set to $co_pay_amount for $comp_name but
														but no corresponding Co-Pay Type  has been set";
														$exit_flag=true;
														break;
						}						
						//check if start_cover is set
						if($start_cover[$i]==''){$start=html("$start_cover[$i]");
						$error_message = " Unable to add new corprate, as Start Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						//check if end cover is set
						if($end_cover[$i]==''){$end=html("$end_cover[$i]");
						$error_message = " Unable to add new corprate, as End Cover date has not been set  for $comp_name though the company is insured";
														$exit_flag=true;
														break;
						}
						if($end_cover[$i] < $start_cover[$i]){$end=html("$end_cover[$i]");$start=html("$start_cover[$i]");
						$error_message = " Unable to add new corprate, the end cover date of $end is before the start cover date of $start  for $comp_name.";
														$exit_flag=true;
														break;
						}						
						//check if cover_type is set
						if($cover_type[$i]==''){$cover_t=html("$cover_type[$i]");
													$error_message = " Unable to add new corprate, as Cover Type has not been set for $comp_name.";
														$exit_flag=true;
														break;
						}
						//check if cover limit
						if($cover_limit[$i]==''){$cover_l=html("$cover_limit[$i]");
													$error_message = " Unable to add new corprate, as Cover Limit has not been set  for $comp_name";
														$exit_flag=true;
														break;
						}
						
					}					
					// start by validating input
					//check i fvalue for co_pay is valid number
					//remove commas if they were used for formating
					$co_pay_val[$i]=str_replace(",", "", "$co_pay_val[$i]");
					if(isset($co_pay_val[$i]) and $co_pay_val[$i]!='' and !ctype_digit($co_pay_val[$i])){
						//check if it has only 2 decimal places
						$data=explode('.',$co_pay_val[$i]);
						if ( count($data) != 2 ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$error_message=" Unable to add new corprate as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
						elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){
							$co_pay_val[$i]=html("$co_pay_val[$i]");
							$error_message=" Unable to add new corprate as $co_pay_val[$i] is not a valid number ";
							$exit_flag=true;
							break;
						}
					}



					
	
					if(isset($ins_id[$i]) and $ins_id[$i]!=''){
						//decrypt insurance compnay id and check that exist
						//echo "$ins_id[$i]--".$encrypt->decrypt("$ins_id[$i]");
						$ins_id[$i]=$encrypt->decrypt("$ins_id[$i]");
						//echo "xxxx--$ins_id[$i]";
						$sql=$error=$s='';$placeholders=array();
						$sql="select id from insurance_company where id=:id";
						$error="Unable to check if insurance company exists";
						$placeholders[':id']=$ins_id[$i];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						if((0 + $s->rowCount()) ==  0){
									$error_message=" Unable to add new corprate, this error has been logged";
									//call function to log this activity
									$message="an update of $ins_id[$i] was attemped into covered_company table for column insurer_id";
									log_security($pdo,$message);
									$exit_flag=true;
									break;
						}					
					}
					
					//check if similar record already exixts
					$sql=$error=$s='';$placeholders=array();
					$sql="select name from covered_company where upper(name)=:name";
					$error="Unable to edit insured companies";
					$placeholders[':name']=strtoupper("$comp_name");
					$s = 	select_sql($sql, $placeholders, $error, $pdo);	
					if($s->rowCount() > 0){
									$error_message=" Unable to add new corprate, as $comp_name already exists";
									$exit_flag=true;
									break;					
					}
					
					//set insurer to 0 if the patient type is not insured
					if($insured_yes_no[$i]=='NO'){$ins_id[$i]=0;}
					
					//now insert new company
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into covered_company set  name=:name, insurer_id=:ins_id, 	co_pay_type=:co_pay_type ,	value=:value ,	pre_auth_needed=:pre_auth,
						smart_needed=:smart_needed, 	start_cover=:start_cover, 	end_cover=:end_cover, 	cover_type=:cover_type,
						cover_limit=:cover_limit, insured=:insured_yes_no ,suspended_cover='No', suspended_reason=''";
					$error="Unable to edit insured companies";
					$placeholders[':ins_id']=$ins_id[$i];
					$placeholders[':insured_yes_no']="$insured_yes_no[$i]";
					$placeholders[':co_pay_type']="$co_pay_type[$i]";
					$placeholders[':value']="$co_pay_val[$i]";
					$placeholders[':pre_auth']="$pre_auth_needed[$i]";
					$placeholders[':smart_needed']="$smart_needed[$i]";
					$placeholders[':start_cover']="$start_cover[$i]";
					$placeholders[':end_cover']="$end_cover[$i]";
					$placeholders[':cover_limit']="$cover_limit[$i]";
					$placeholders[':cover_type']="$cover_type[$i]";
					$placeholders[':name']="$comp_name";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					if(!$s ){break;$error="Unable to add new employer";}		
					$i++;
			}
		
			
			if(!$exit_flag){$tx_result = $pdo->commit();}
			elseif($exit_flag){$tx_result=false;$pdo->rollBack();}
			if($tx_result){$success_message=" New corprate $comp_name has been added  ";}
			//elseif(!$tx_result){$error_message="   Unable to edit Insured Companies  ";}	
		//$tx_result = $pdo->commit();
		}
	catch (PDOException $e)
	{
	$pdo->rollBack();
	$error_message="   Unable to add new corprate   ";
	}
	get_covered_company($pdo);	
}



?>
<div class='feedback hide_element'></div>
	<div class=' put_center margin-top' id='employer_insurance_page_loader'>
		<span ><label for="" class="label">Loading... </label><br>
			<img class='page_loader_spinner' src="dental_jquery/ajax-loader-new.gif" />
		</span>
	</div>
	<div class="grid-100 employer_form_div   margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='New Corprate' class=button_style id=add_new_patient_employer />
	
	<div  id=employer_form_div >
		<div class='feedback_dialog '></div>
		<form action="new_employer_action" method="post" name="employer_form" id="" class='dialog_form'>
			<div class='grid-20 alpha'><label for="" class="label">Corprate Name</label></div>
			<div class='grid-30'><input type=text name=employer_name id=employer_name /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insured</label></div>
			<div class='grid-30'><select id=insured_yes_no name="insured_yes_no[]" ><option value='NO'>NO</option>
														<option value='YES'>YES</option>
														
								</select></div>
			<div class=' grid-20'><label for="" class="label"> Insurer</label></div>
			<div class=' grid-30 omega'><?php 
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select name,id from insurance_company where listed=0 order by name";
				$error2="Unable to get insurer";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
					echo "<select name=ins_name id=ins_name class='insurer_input input_in_table_cell' ><option></option>";
					foreach($s2 as $row2){
						$insurer=html($row2['name']);
						$val=$encrypt->encrypt(html($row2['id']));
						echo "<option value='$val'>$insurer</option>"; 
					}
					echo "</select>";			
			
			?></div>
			<div class=clear></div>
			<br>
			<div class='grid-20 alpha'><label for="" class="label"> Pre-Authorisation Needed</label></div>
			<div class='grid-30'><select class='insurer_input' id=pre_auth name=pre_auth><option value='NO'>NO</option>
														<option value='YES'>YES</option>
														
								</select></div>
			<div class='grid-20 '><label for="" class="label"> Smart Card Check Needed</label></div>
			<div class='grid-30 omega'><select class='insurer_input' id=smart_check name=smart_check><option value='NO'>NO</option>
														<option value='YES'>YES</option>
														
								</select></div>
			<div class=clear></div><br>					
			<div class='grid-20 alpha'><label for="" class="label"> Co-Pay Type</label></div>
			<div class='grid-30'><select class='insurer_input' id=co_pay name=co_pay><option></option>
														<option value='PERCENTAGE'>PERCENTAGE</option>
														<option value='CASH'>CASH</option>
								</select></div>
			<div class='grid-20'><label for="" class="label"> Value</label></div>
			<div class='grid-30 omega'><input class='insurer_input' type=text id=co_pay_value name=co_pay_value title="For percentage, value should be between 0 and 100 withiut the % sign" /></div>								

			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insurance valid from</label></div>
			<div class='grid-30'><input type=text id=start_date class='insurer_input date_picker_no_past' name=start_date  /></div>	
			<div class='grid-20'><label for="" class="label">Until this date</label></div>
			<div class='grid-30 omega'><input class='insurer_input date_picker_no_past' id=end_date type=text name=end_date  /></div>	

			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="" class="label">Insurance cover type</label></div>
			<div class='grid-30'><select id=cover_type name=cover_type class='insurer_input input_in_table_cell'>
									<option></option>
								<option value='Family' >Family</option>
								<option value='Individual'>Individual</option>
								</select>
			</div>	
			<div class='grid-20'><label for="" class="label">Cover Limit(KES)</label></div>
			<div class='grid-30 omega'>
				<input  class='insurer_input' id=cover_limit type=text name=cover_limit  />
				<br><label for="" class="label">If the cover is unlimited, then type "UNLIMITED" in the field above.</label> 
				</div>	
			
			<?php $token = form_token(); $_SESSION['token2'] = "$token";  ?>
		<input type="hidden" name="token2"  value="<?php echo $_SESSION['token2']; ?>" />
			<div class='grid-30 prefix-70'>	<br><input type="submit"  value="Add Patient Employer"/></div>
			<div class=clear></div>
			</form>	
	
	
	</div>
	
	<?php
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from covered_company  order by name ";
	$error="Unable to select covered companies";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br>";// ?>
			
		
			
			<table class='normal_table header-fixed '><caption>Corporates</caption><thead>
			<tr><th class=count></th><th class=emp_name>Employer</th><th class=insured>Insured</th><th class=ins_name>Patient Type</th><th class=pre_auth>PRE<br>Auth.<br>Needed</th>
			<th class=smart_run>Smart<br>Check<br>Needed</th><th class=co_pay>Co-Pay<br>Type</th><th class=val>Value</th><th class=daily_max_limit>Daily Max<br>Inv Amount</th>
			<th class=start_date>Date<br>Cover<br>Begins</th><th class=end_date>Date<br>Cover<br>Ends</th>
			<th class=cover_type>Cover<br>Type</th><th class=cover_limit>Cover<br>Limit</th><th class=procedures>Procedures<br>Not<br>Insured</th></tr></thead>
			
			</table>
		
		<?php echo"	
		<table class='normal_table replace_header'><caption>Corporates</caption><thead>
		<tr><th class=count></th><th class=emp_name>Employer</th><th class=insured>Insured</th><th class=ins_name>Patient Type</th><th class=pre_auth>PRE<br>Auth.<br>Needed</th>
		<th class=smart_run>Smart<br>Check<br>Needed</th><th class=co_pay>Co-Pay<br>Type</th><th class=val>Value</th><th class=daily_max_limit>Daily Max<br>Inv Amount</th>
		<th class=start_date>Date<br>Cover<br>Begins</th><th class=end_date>Date<br>Cover<br>Ends</th>
		<th class=cover_type>Cover<br>Type</th><th class=cover_limit>Cover<br>Limit</th><th class=procedures>Procedures<br>Not<br>Insured</th></tr>
		
		</thead>
		<tbody>";
			//get insurer list
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select name,id from insurance_company order by name";
			$error2="Unable to get insurer";
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
			if($s2->rowCount()>0){
				//echo "<select name=old_ins[] class='input_in_table_cell old_ins' ><option></option>";
				$insurer=$val2=$insurer_id=array();
				foreach($s2 as $row2){
					$insurer[]=html($row2['name']);
					$insurer_id[]=html($row2['id']);
					//$val2[]=$encrypt->encrypt(html($row2['id']));
					//$val2=html($row2['id']);
					
					//if($row['insurer_id'] == $row2['id']) {	echo "<option value='$val2' selected>$insurer</option>"; }
					//else {	echo "<option value='$val2'>$insurer</option>"; }
				}
				//echo "</select>";
			}		
		foreach($s as $row){
			$count++;
			$insured_yes_no=html($row['insured']);
			$name=html($row['name']);
			$emp_id=html($row['id']);
			$val=$encrypt->encrypt("$emp_id");
			//$val=$row['id'];
			$co_pay_val=html($row['value']);
			$start_cover=html($row['start_cover']);
			$end_cover=html($row['end_cover']);
			$cover_type=html($row['cover_type']);
			$cover_limit=html($row['cover_limit']);
			if($row['invoice_daily_limit'] == 0){$row['invoice_daily_limit']='';}
			$invoice_daily_limit=html($row['invoice_daily_limit']);
			
			//$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=button  class='wrap_word_in_button button_in_table_cell button_style edit_corporate' value='$name'  />
				<input type=hidden name='ninye[]' value='$val' />
			</td><td>$insured_yes_no</td><td>";
			$n=count($insurer);
			if($n > 0 ){
				$i=0;
				while($i < $n){
					if($row['insurer_id'] == $insurer_id["$i"]) {	echo "$insurer[$i]"; }
					$i++;
				}
			}
			else{echo "&nbsp;";}
			
			echo "</td><td>";htmlout($row['pre_auth_needed']);//get pre-auth
			echo "</td><td>";htmlout($row['smart_needed']);	//get smart run		
			echo "</td><td>";htmlout($row['co_pay_type']);//get co-pay-type
			echo "</td><td>$co_pay_val</td><td >$invoice_daily_limit</td>
			<td>$start_cover</td>
			<td>$end_cover</td>
			<td>";htmlout($row['cover_type']);
			echo "</td><td>$cover_limit</td><td>";
			if($row['insurer_id']!=0){
				echo "<input type=button  class='button_in_table_cell button_style edit_corporate_cover' value='Edit'  />";
				echo "<input type=hidden name='ninye[]' value='$val' />";
			}
			else{echo "&nbsp;";}//<input type=hidden name='ninye[]' value='$val' />
			echo "</td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		//echo "<input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
<div class=grid-100 id=edit_ins_cover>
	<div class='feedback_dialog '></div>
	<div id='edit_ins_cover_inner '></div>
</div>