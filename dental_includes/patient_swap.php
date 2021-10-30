<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,41)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT INSURER SWAP</div>";
?>
<div class=grid-container>
<?php 
echo "<div class='feedback hide_element'></div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}

//perform swap for single patient

if(isset($_POST['token_swap_2']) and 	$_POST['token_swap_2']!='' and $_POST['token_swap_2']==$_SESSION['token_swap_2']){
		$exit_flag=false;
		if(isset($_POST['token_swap_2a']) and $_POST['token_swap_2a']!=''){
			$old_pid=$encrypt->decrypt($_POST['token_swap_2a']);
	
		//check patient type
		if(!$exit_flag and $_POST['ptype']==''){
			//{echo "<div class='$result_class'>$result_message</div>";}
			$result_class='error_response';
			$result_message="The patient type was not set";
			$exit_flag=true;
		}
		if(!$exit_flag and $_POST['ptype']!=''){
			$ptype=html($encrypt->decrypt($_POST['ptype']));//echo "<br>$ptype is ";exit;
			if(!$exit_flag and !in_array($ptype, $_SESSION['patient_type_array'])){
				
				$result_class='error_response';
				$result_message="The patient type was not set";
				$exit_flag=true;
				$message="somebody tried to input $ptype as a patient type into patient details";
				log_security($pdo,$message);
				//$message="bad#Unable to save details as patient type is not specified. ";
			}	
		}
		
		//check if not cahs then covered compnay must be set
		if(!$exit_flag and $ptype!=3 and (!isset($_POST['covered_company']) or $_POST['covered_company']=='')){
			$result_class='error_response';
			$result_message="$ptype The insured company was not set";
			$exit_flag=true;
		}
		
		//check covered compnaycovered_company
		$company_covered='';
		if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){
			$company_covered=html($encrypt->decrypt($_POST['covered_company']));
		}
		if(!$exit_flag and isset($_POST['covered_company']) and $_POST['covered_company']!=''){
			
			if(!in_array($company_covered,$_SESSION['covered_company_array'])){
				
				$result_class='error_response';
				$result_message="The insured company was not set";
				$exit_flag=true;
				$message="somebody tried to input $company_covered as a covered compnay into patient details";
				log_security($pdo,$message);
				//$message="bad#Unable to save details as covered company  is not correctly specified. ";
			}	
		}	
		if(!$exit_flag and $ptype==3){
			$_POST['mem_no']=$company_covered='';
		} 
			if(!$exit_flag ){
				try{
				$pdo->beginTransaction();
					//select patient_details_a
					$sql=$error=$s='';$placeholders=array();
					$sql="select * from patient_details_a where pid=:pid";
					$error="Unable to get patient details";
					$placeholders[':pid']=$old_pid;
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					if($s->rowCount()== 1){
						//get patient ID
						$year=date('y');
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select max(pnum) from pnum_generator where year=:year";
						$error2="Unable to get max pnum for year $year";
						$placeholders2[':year']="$year";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
						if($s2->rowCount() > 0){foreach($s2 as $row2){$pnum=$row2[0] + 1;}}
						else{$pnum=1;}
						$pid="$pnum/$year";

						//insert that pid into pnum generator
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="insert into pnum_generator set pnum=:pnum,  year=:year";
						$error2="Unable to insert max pnum for year $year";
						$placeholders2[':year']="$year";
						$placeholders2[':pnum']=$pnum;
						$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
						
						//get patient deatails_a for insertion
						foreach($s as $row){
							$old_patient_number=html($row['patient_number']);
							//now insert into patient_details_a
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into patient_details_a set last_name=:last_name, 
								middle_name=:middle_name, first_name=:first_name, mobile_phone=:mobile_phone,
									biz_phone=:biz_phone, type=:type, patient_number=:patient_number, 
									member_no=:member_no, company_covered=:company_covered,
									family_id=:family_id, family_title=:family_title,
									insurance_cover_role=:insurance_cover_role,pnum=:pnum,
									year=:year,email_address=:email_address, 
									email_address_2=:email_address_2, internal_patient=:internal_patient";
							$error2="Unable to swap patient 1";
							$placeholders2[':last_name']=$row['last_name'];
							$placeholders2[':middle_name']=$row['middle_name'];
							$placeholders2[':first_name']=$row['first_name'];
							$placeholders2[':mobile_phone']=$row['mobile_phone'];
							$placeholders2[':biz_phone']=$row['biz_phone'];
							$placeholders2[':type']=$ptype;
							$placeholders2[':patient_number']="$pid";
							$placeholders2[':member_no']=$_POST['mem_no'];
							if($ptype == 3){$placeholders2[':company_covered']=267;}
							else{$placeholders2[':company_covered']=$company_covered;}
							$placeholders2[':family_id']=$row['family_id'];
							$placeholders2[':family_title']=$row['family_title'];
							$placeholders2[':insurance_cover_role']=$row['insurance_cover_role'];
							$placeholders2[':pnum']=$pnum;
							$placeholders2[':year']="$year";
							$placeholders2[':email_address']=$row['email_address'];
							//$placeholders2[':insured']=$row['insured'];
							$placeholders2[':email_address_2']=$row['email_address_2'];
							$placeholders2[':internal_patient']=$row['internal_patient'];
							$id = get_insert_id($sql2, $placeholders2, $error2, $pdo);	
						}	
						
						//select patient_details_b
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select * from patient_details_b where pid=:pid";
						$error2="Unable to get patient details 2";
						$placeholders2[':pid']=$old_pid;
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){
							//now insert into patient_details_b
							$sql3=$error3=$s3='';$placeholders3=array();
							$sql3="insert into patient_details_b set id_number=:id_number, address=:address,
								city=:city, occupation=:occupation,weight=:weight, dob=:dob, referee=:referee,
									em_contact=:em_contact,em_relationship=:em_relationship, em_phone=:em_phone,
									behalf_name=:behalf_name, behalf_relationship=:behalf_relationship, 
									when_added=:when_added,	gender=:gender,	photo_path=:photo_path, pid=:pid,tag=:tag
									 ";
							$error3="Unable to add patient new patient";
							$placeholders3[':id_number']=$row2['id_number'];
							$placeholders3[':address']=$row2['address'];
							$placeholders3[':city']=$row2['city'];
							$placeholders3[':occupation']=$row2['occupation'];
							$placeholders3[':weight']=$row2['weight'];
							$placeholders3[':dob']=$row2['dob'];
							$placeholders3[':referee']=$row2['referee'];
							$placeholders3[':em_contact']=$row2['em_contact'];
							$placeholders3[':em_relationship']=$row2['em_relationship'];
							$placeholders3[':em_phone']=$row2['em_phone'];
							$placeholders3[':behalf_name']=$row2['behalf_name'];
							$placeholders3[':behalf_relationship']=$row2['behalf_relationship'];
							$placeholders3[':when_added']=date('Y-m-d');
							$placeholders3[':gender']=$row2['gender'];
							$placeholders3[':photo_path']=$row2['photo_path'];
							$placeholders3[':pid']=$id;
							$placeholders3[':tag']=$row2['tag'];
							$s3 = 	insert_sql($sql3, $placeholders3, $error3, $pdo);	
						}
									
						//insert into patient swap table
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="insert into swapped_patients set old_pid=:old_pid, new_pid=:new_pid, changed_by=:changed_by ,
								when_added=now(), old_patient_number=:old_patient_number, new_patient_number=:new_patient_number";
						$error2="Unable to record patient swap";
						$placeholders2[':old_pid']=$old_pid;
						$placeholders2[':new_pid']=$id;
						$placeholders2[':old_patient_number']="$old_patient_number";
						$placeholders2[':new_patient_number']="$pid";
						$placeholders2[':changed_by']=$_SESSION['id'];
						$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);	
					}
					else{echo "<div class='error_response'>No such patient</div>";}
					
					$tx_result = $pdo->commit();
					if($tx_result){echo "<div class='success_response'>Patient $old_patient_number has been swapped by patient $pid</div>";}				
				}
				catch (PDOException $e)
				{
					$pdo->rollBack();
					echo "<div class='error_response'>Unable to perform patient swap</div>";
				}	
					
			}
		
		}
}	
	
//get details to be swapped
if(isset($_POST['token_swap_1']) and 	$_POST['token_swap_1']!='' and $_POST['token_swap_1']==$_SESSION['token_swap_1']){
		$exit_flag=false;
		
		if(!$exit_flag and $_POST['indv']=='ins'){
			
			//check if insurer is selcted
			if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
					$result_class="error_response";
					$result_message="Please select and insurer";
					$exit_flag=true;
			}	
			
			//check if dates are selected
			if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){	
					$result_class="error_response";
					$result_message="Please specify the date range for the dispatch note";
					$exit_flag=true;
			}
			
			//get dispatch notes
			if(!$exit_flag){	
					$from_date=html($_POST['from_date']);
					$to_date=html($_POST['to_date']);
					//check if that dispatch number exists
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select a.dispatch_number,a.title,a.when_added,b.first_name,b.middle_name,b.last_name ,c.name,a.insurer_id
						from dispatched_invoices a, users b , insurance_company c 
						where insurer_id=:insurer_id and a.when_added>=:from_date and a.when_added<=:to_date and a.dispatched_by=b.id 
						and a.insurer_id=c.id";
					$error2="Unable to get dispatched notes by insurance ";
					$placeholders2[':insurer_id']=$encrypt->decrypt($_POST['ptype']);
					$placeholders2[':from_date']=$_POST['from_date'];
					$placeholders2[':to_date']=$_POST['to_date'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						$i=0;
						$token = form_token(); $_SESSION['token_edis_1'] = "$token";  
						foreach($s2 as $row2){
							$disp_num=html($row2['dispatch_number']);
							$dipatcher_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
							$title=html($row2['title']);
							$when_added=html($row2['when_added']);
							$insurer_id=html($row2['insurer_id']);
							$insurer_name=html($row2['name']);
							if($i==0){
								echo "<table class='normal_table'><caption>DISPATCH NOTES FOR $insurer_name BETWEEN $from_date and $to_date </caption><thead>
										<tr>
										<th class=ins_ed1_date>DISPATCH DATE</th>
										<th class=ins_ed1_dispnum>DISPATCH NUMBER</th>
										<th class=ins_ed1_title>DESCRIPTION</th>
										<th class=ins_ed1_creator>DISPATCHER</th>
										</tr></thead><tbody>";							
							}
							echo "<tr><td>$when_added</td><td>$disp_num</td><td>"; ?>
							<form action="" method="POST" enctype="" name="" id="">
								
								<input type="hidden" name="token_edis_1"  value="<?php echo $_SESSION['token_edis_1']; ?>" />
								<input type=hidden name=indv value=disp_num />
								<input type=hidden name=indv_crit value="<?php echo $disp_num; ?>" />
								<input type="submit" class='button_table_cell' value="<?php echo $title; ?>" />
							</form>
							<?php							
							echo "</td><td>$dipatcher_name</td></tr>";
							$i++;;
						}
						echo "</tbody></table>";
						exit;
					}
					else{
					
						$result_class="error_response";
						//$var=html($_POST['indv_crit']);
						$result_message="There are no dispatch notes for your search criteria";
						$exit_flag=true;
					}
			}
			
		}
		elseif(!$exit_flag and ($_POST['indv']=='patient_number' or 
		$_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'  )){
			
			//check if serach criteriais set
			if(!$exit_flag and !isset($_POST['indv_crit']) or $_POST['indv_crit']==''   ){	
					$result_class="error_response";
					$result_message="Incorrect search criteria";
					$exit_flag=true;
			}
			
			//by patient names
			if(!$exit_flag and $_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'){	
				$result=get_pt_name2($_POST['indv'],$_POST['indv_crit'],$pdo,$encrypt,'token_swap_1','indv','patient_number','indv_crit');
				if($result=="2"){
					$result_class="error_response";
					$result_message="No such patient found";
					$exit_flag=true;
				}
				else{
					echo "$result";
					exit;
				}
				
			}
			//by patient number
			if(!$exit_flag and $_POST['indv']=='patient_number'){
							
				//check if this patient is already swapped
				$result = check_if_swapped($pdo,'patient_number',$_POST['indv_crit']);
				if($result!='good'){echo "<div class='error_response'>$result</div>";exit;}
			
				$sql=$error=$s='';$placeholders=array();	
				$sql="select * from patient_details_a where patient_number=:patient_number";
				$placeholders[':patient_number']=$_POST['indv_crit'];
				$error="Error: Unable to get patient details ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()>0){
					foreach($s as $row){
						$last_name=ucfirst(html($row['last_name']));
						$middle_name=ucfirst(html($row['middle_name']));
						$first_name=ucfirst(html($row['first_name']));
						$type=html($row['type']);
						$patient_number=html($row['patient_number']);
						$pid_clean=html($row['pid']);
						$pid=$encrypt->encrypt(html($row['pid']));
						
						$member_no=html($row['member_no']);
						$company_covered=html($row['company_covered']);
						$family_id=html($row['family_id']);
						$family_title=html($row['family_title']);
						$insurance_cover_role=html($row['insurance_cover_role']);
						
					}
					//get company_covered_name and type_name
					$company_covered_name=$type_name='';
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from covered_company where id=:covered_company";
					$placeholders2[':covered_company']=$company_covered;
					$error2="Error: Unable to get covered company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$company_covered_name=html($row2['name']);}
					
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from insurance_company where id=:type";
					$placeholders2[':type']=$type;
					$error2="Error: Unable to get insurance company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$type_name=html($row2['name']);}	
					
					$result = show_pt_statement_brief($pdo,$encrypt->encrypt("$pid_clean"),$encrypt);
					$data=explode('#',"$result");
					echo "<table>
						<thead>
						<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
						<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
						<tbody><td>$patient_number</td><td>$first_name</td><td>$middle_name</td><td>$last_name</td>
						<td>$type_name</td><td>$company_covered_name</td><td>$data[0]</td><td>$data[1]</td><td>$data[2]</td><td>limit</td><td>expiry</td></tbody></table>";
					//show swap button
					?>
					<form action=""  method="POST"   id="">
						<fieldset><legend>Patient Type</legend>
			
								<div class='grid-50 grid-parent'>	
									<!--patient type-->
									<div class='grid-30'><label for="" class="label">Patient Type</label></div>
									<div class='grid-70'><select class=ptype name=ptype><option>
										<?php
											$sql2=$error2=$s2='';$placeholders2=array();
											$sql2 = "select id,name from insurance_company order by name";
											$error2 = "Unable to insurance companies";
											$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
											foreach($s2 as $row2){
												$name=html($row2['name']);
												$val=$encrypt->encrypt(html($row2['id']));
												if($type==$row2['id']){echo "<option value='$val' selected>$name</option>";}
												else{echo "<option value='$val'>$name</option>";}
											}
										
										?>
										</option></select>
									</div>
									<div class=clear></div>	<br>
									<!--compnay covered-->
									<div class='grid-30 alpha'><label for="" class="label">Company Covered</label></div>
									<div class='grid-70 omega'><select class='covered_company undisable_covered_company' name=covered_company><option></option><!-- -->
									<?php 
											$sql2=$error2=$s2='';$placeholders2=array();
											$sql2 = "select id,name from covered_company order by name";
											$error2 = "Unable to covered companies";
											$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
											foreach($s2 as $row2){
												$name=html($row2['name']);
												$val=$encrypt->encrypt(html($row2['id']));
												if($company_covered==$row2['id']){echo "<option value='$val' selected>$name</option>";}
												else{echo "<option value='$val'>$name</option>";}
											}					
										
										
									?>
									</select></div>
									<div class=clear></div>	<br>
									<!--membership number-->
									<div class='grid-30'><label for="" class="label">Membership Number</label></div>
									<div class='grid-70'><input type=text name=mem_no value='<?php echo $member_no; ?>' /></div>				
									<div class=clear></div><br>
								</div>
								<div class='grid-50 grid-parent' id='family_div' >	
									<?php
										//check if guy has family
											//if($family_id >= 1 ){
												//get_pt_family_memebrs($pdo, $pid, $encrypt);
										//	}
										
									?>
								</div>
						
						</fieldset>	
					
						<?php $token = form_token(); $_SESSION['token_swap_2'] = "$token";  ?>
						<input type="hidden" name="token_swap_2"  value="<?php echo $_SESSION['token_swap_2']; ?>" />
						<input type="hidden" name="token_swap_2a"  value="<?php echo $pid; ?>" />
						<input type="submit"  value="Swap Patient" />
					</form>
					<?php
					exit;
				}
				else{ echo "<div class='error_response'>No such patient</div>";}
			
					
			}			
			
			

		}

	

		
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
	<!--<div class='multiple_invoice'>-->
		<div class='grid-100 '>
				<div class='grid-15 '><label for="" class="label">Select swap option</label></div>
				<div class='grid-15'>
					<select  name=crit1 class='swap1'><option></option>
						<!--<option value='was ins'>Corprate -- coming soon</option>-->
						<option value='indv'>Individual</option>
						</select>
				</div>	
				<div class=clear></div><br>
		
			<div class='no_padding serach_by_individual1'>
				<div class='grid-15 '><label for="" class="label">Search for patient by</label></div>
				<div class='grid-15'>
					<select  name=indv class='edit_dispatch'><option></option>
						<option value='patient_number'>Patient Number</option>
						<option value='first_name'>First Name</option>
						<option value='middle_name'>Middle Name</option>
						<option value='last_name'>Last Name</option>
					</select>
				</div>
			</div>	
			<div class='no_padding serach_by_individual'>
				<div class='grid-10 '><input type=text name=indv_crit /></div>
				<div class='grid-10'>	<input type="submit"  value="Submit"/></div>
				<div class=clear></div>
				<br>
			</div>	<!-- end individual serach-->
			<div class='no_padding serach_by_ins'>
				
				<div class='grid-15'><label for="" class="label">Select Insurer</label>
					<?php $token = form_token(); $_SESSION['token_swap_1'] = "$token";  ?>
					<input type="hidden" name="token_swap_1"  value="<?php echo $_SESSION['token_swap_1']; ?>" />
				</div>
				<div class='grid-25'><select name=ptype><option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
						$error = "Unable to insurance companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
						echo "<option value='all'>ALL</option>";
					
					?>
					</option></select>
				</div>	
				<!--compnay covered-->
				<!--<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
				<div class='grid-25 '><select class=covered_company name=covered_company><option></option>-->
				<?php 
				/*	if(isset($_SESSION['id']) and $_SESSION['id']!=''){
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							//echo "<option value='$val'>$name</option>";
						}					
							//$val=$encrypt->encrypt("all");
							echo "<option value='all'>ALL</option>";
					}*/
				?>
				<!--</select></div>	-->
				<div class=clear></div><br>
				<div class=' grid-15'><label for="" class="label">Dispatched between</label></div>
				<div class=grid-15><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-15><input type=text name=to_date class=date_picker /></div>
	<!--</div>-->
				<div class=clear></div>
				<br>
				<div class='prefix-45 grid-10'>	<input type="submit"  value="Submit"/></div>
			</div>	
	</form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>