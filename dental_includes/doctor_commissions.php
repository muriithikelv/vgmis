<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,87)){exit;}
echo "<div class='grid_12 page_heading'>TREATMENT COMMISSIONS</div>";
?>
<div class=grid-container>
<?php 

//get results
if(isset($_POST['token_cr1']) and 	$_POST['token_cr1']!='' and $_POST['token_cr1']==$_SESSION['token_cr1']){
		$_SESSION['token_cr1']='';
		$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();
		$sql2=$error2=$s2='';$placeholders2=array();

		//check if doctor selected
		if(!$exit_flag and !isset($_POST['doc']) or $_POST['doc']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the doctor";
				$exit_flag=true;
		}
		
		//check if ptype selected
		if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the patient type";
				$exit_flag=true;
		}
		
		//check if date is selcted
		if(!$exit_flag and !isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']==''  ){	
				$result_class="error_response";
				$result_message="Please specify the date range for the search criteria";
				$exit_flag=true;
		}	
		
		
				
		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$var=$doctor=$doctor2=$insurer=$company=$balance='';
		$i=$total_cost=$total_paid=0;
		$doc_var=" all doctors ";
		$commission_reciver_filter=$doctor_filter = $doctor_filter2 = $ptype_filter= '';
		//doctor criteria
		if($_POST['doc']!='all'){
			$doc_id=$encrypt->decrypt($_POST['doc']);
			$doctor_criteria = $doctor2_criteria= " and b.created_by=:doc_id ";
			$placeholders[':doc_id']=$doc_id;
			$placeholders2[':doc_id']=$doc_id;
			
			$commission_reciver_filter_var = $doctor_filter_var = $doc_id;
			$commission_reciver_filter = " and commission_receiver=$commission_reciver_filter_var ";
			$doctor_filter = " and doc_id = $doctor_filter_var ";
			$doctor_filter2 = " and created_by = $doctor_filter_var "; 
			
		}
		
		//patient type criteria
		$ptype_filter_var='';
		if($_POST['ptype']!='all'){
			$ptype_id=$encrypt->decrypt($_POST['ptype']);
			$ptype = " and b.created_by=:doc_id ";
			
			
			$ptype_filter_var = $ptype_id;
			$ptype_filter = $ptype_filter_var;
			
		}
		
		
			//get sold prescriptions
			$invoices_array=array();
			$doctor_billed_array=$doctor_id_array=$doctor_name_array=$doctor_authorised_array=array();
			$treatment_procedure_id_array=array();
			$doctor_name_cash_array=$doctor_billed_cash_array	=$doctor_id_cash_array=array();
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT  `drug_id` , `when_added` ,  `cost` , quantity, `prescription_number`,pid,commission_receiver 
					FROM `prescriptions` WHERE `when_added` >=:from_date AND `when_added` <=:to_date AND pay_type=2 and commission_receiver > 0 $commission_reciver_filter   order by id ASC ";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			foreach($s1 as $row1 ){
				$invoice_cost=$billed_cost=$amount_paid=$doctor=$invoice_number=$authorised_cost='';
				$continue=false;
			//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer ,internal_patient,a.type
						from patient_details_a a 
						left join insurance_company c on a.type=c.id
						left join covered_company b on a.company_covered=b.id 
						 where pid=:pid and internal_patient=0 ";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
				foreach($s2 as $row2){
					if($ptype_filter_var != '' and $ptype_filter_var != $row2['type']){
						$continue=true;
						break;
					}
					
					$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
						$company=html("$row2[company_covered]");
						$insurer=html("$row2[insurer]");
						if($company!=''){$insurer="$insurer - $company";}
						else{$insurer="$insurer";}
						if($row2['internal_patient'] > 0){$continue=true;break;}
						//$cost=$invoice_cost;
						//$invoice_number=html("$row1[invoice_number]");
						//$invoice_id=html("$row1[id]");
						//$val=$encrypt->encrypt("$invoice_id");
						//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
						//$_SESSION['balance_invoice'][$invoice_id]=$balance;
				}//end s2
				if($continue){continue;}
				
				///now get drug name
				$sql3=$error3=$s3='';$placeholders3=array();	
				$sql3="SELECT name from drugs where id=:drug_id";
				$placeholders3[':drug_id']=$row1['drug_id'];
				$error3="Error: Unable to pt details from uniq ";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){$drug_name=html("$row3[name] - $row1[quantity]");}
					

				//get commsion receiver who raised invoice
				$doctor='';
				$sql4=$error3=$s3='';$placeholders3=array();	
				$sql4="SELECT first_name, middle_name, last_name,id FROM users where id=:user_id";
				$placeholders4[':user_id']=$row1['commission_receiver'];
				$error4="Error: Unable to pt details from uniq ";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
				foreach($s4 as $row4){
					$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
					$doctor_id=html($row1['commission_receiver']);
					
						//get  sum
						if(!isset($doctor_billed_array[$doctor_id])){$doctor_billed_array[$doctor_id]=$row1['cost'];}
						else{$doctor_billed_array[$doctor_id]= $doctor_billed_array[$doctor_id] + $row1['cost'];}
						
						//get doctor name
						if(!isset($doctor_name_array[$doctor_id])){$doctor_name_array[$doctor_id]="$doctor";}
						//get doctor array
						if(!in_array($doctor_id,$doctor_id_array)){$doctor_id_array[]=$doctor_id;}
					
				}
							
				$when_added=html("$row1[when_added]");			
					

				//get patient balance
				$enc_pid=$encrypt->encrypt($row1['pid']);
				$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
				$data=explode('#',"$result");
				$insurance_balance="$data[0]";
				$cash_balance="$data[1]";
				$points_balance="$data[2]";
				$invoices_array[]=array('when_added'=>"$when_added",  'doctor'=>"$doctor", 'patient_name'=>"$patient_name", 
											'insurer'=>"$insurer", 'billed_cost'=>$row1['cost'],'drug_name'=>"$drug_name",'pay_type'=>2,'doctor_id'=>$doctor_id,'insurance_balance'=>"$insurance_balance",'cash_balance'=>"$cash_balance",'points_balance'=>"$points_balance"
										 );
									 
			}//end s1
			
			//display prescriptions
			if(count($invoices_array) >0 ){
				$print_end_table=false;
				$count=$total_authorised_cost=$total_billed_cost=$total_paid=0;
				
				foreach($invoices_array as $row){
					
						if($row['pay_type'] != 2){continue;} //show only cash procedures
						
					
						if($count==0){
							$caption=strtoupper("CASH PRESCRIPTIONS BETWEEN  $from_date AND $to_date");
							$print_end_table=true;
							echo "<br><br>
									<table class='normal_table'><caption>$caption</caption><thead>
									<tr><th class=dc_invoice_in_count></th>
									<th class=dc_invoice_in_date>DATE</th>
									<th class=dc_invoice_in_doctor>COMMISSION RECEIVER</th>
									<th class=dc_invoice_in_patient>PATIENT NAME</th>
									<th class=dc_invoice_in_company>CORPORATE</th>
									<th class=dc_invoice_in_id> </th>
									<th class=dc_invoice_in_id>PRESCRIPTION</th>
									<th class=dc_invoice_in_cost>BILLED COST</th>
									<th class=dc_invoice_in_tray>BALANCE</th>
									</tr></thead><tbody>";	
						}
							$doctor=$row['doctor'];
							$when_added=html("$row[when_added]");
							$drug_name=html("$row[drug_name]");
							$billed_cost=html("$row[billed_cost]");
							$patient_name=html("$row[patient_name]");
							$insurer=html("$row[insurer]");
							//$val=$row['val'];
							$count++;
							$total_billed_cost = $total_billed_cost + $billed_cost;
							echo "<tr><td class=count>$count</td>
									<td>$when_added</td>
									<td>$doctor</td>
									<td>$patient_name</td>
									<td>$insurer</td>
							<td> </td><td>$drug_name </td>
							<td>";
							if($billed_cost > 0){echo number_format($billed_cost,2);}
							else{ echo $billed_cost;}
							echo "</td><td>$row[cash_balance]</td></tr>";
				}
				if($print_end_table){
					echo  "<tr class=total_background><td colspan=7>TOTAL</td><td>".number_format($total_billed_cost,2)."</td>
					<td ></td></tr>";
						echo "</tbody></table>";			
				}
				
				//show doctor percentages
				echo "
						<table class='normal_table'><caption>PERCENTAGE CONTRIBUTION FOR CASH PRESCRIPTIONS </caption><thead>
						<tr ><th class=dcp_count2></th>
						<th class=dcp_name2>COMMISSION RECEIVER</th>
						<th class=dcp_amount2>AMOUNT SOLD</th>
						<th class=dcp_percent2>PERCENATGE </th>
						</tr></thead><tbody>";	
				$i=0;
				$i2=1;
				$n=count($doctor_id_array);
				while($i < $n){
					$doc_id=$doctor_id_array[$i];
					$percent_billed=($doctor_billed_array[$doc_id] / $total_billed_cost ) * 100;
					$billed=html("$doctor_billed_array[$doc_id]");
					
					echo "<tr><td>$i2</td><td>$doctor_name_array[$doc_id]</td><td>".number_format($billed,2)."</td><td>".
						html(number_format($percent_billed,2))."%</td></tr>";
					$i++;
					$i2++;
				}
				echo "</tbody></table>";
			}
			
			
			
			
			//now get for invoiced procedured started
			//get procedures from treatment_procedure_notes first
			$invoices_array=array();
			$doctor_billed_array=$doctor_id_array=$doctor_name_array=$doctor_authorised_array=array();
			$treatment_procedure_id_array=array();
			$doctor_name_cash_array=$doctor_billed_cash_array	=$doctor_id_cash_array=array();
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT min( id ) , `treatment_procedure_id` , `when_added` , `doc_id` ,  `pid` 
					FROM `treatment_procedure_notes` WHERE `when_added` >=:from_date AND `when_added` <=:to_date $doctor_filter AND treatment_procedure_id NOT 
					IN (

					SELECT treatment_procedure_id
					FROM treatment_procedure_notes
					WHERE when_added <:from_date
					)
					GROUP BY `treatment_procedure_id` 
					ORDER BY `when_added`,`id` ASC ";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			foreach($s1 as $row1 ){
				$continue=false;
				$treatment_procedure_id_array[]=$row1['treatment_procedure_id'];
				$invoice_cost=$billed_cost=$amount_paid=$doctor=$invoice_number=$authorised_cost='';
				
				//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer ,internal_patient,a.type
						from patient_details_a a 
						left join insurance_company c on a.type=c.id
						left join covered_company b on a.company_covered=b.id 
						 where pid=:pid and internal_patient=0";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
				foreach($s2 as $row2){
					if($ptype_filter_var != '' and $ptype_filter_var != $row2['type']){
						$continue=true;
						break;
					}
					$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
						$company=html("$row2[company_covered]");
						$insurer=html("$row2[insurer]");
						if($company!=''){$insurer="$insurer - $company";}
						else{$insurer="$insurer";}
						if($row2['internal_patient'] > 0){$continue=true;break;}
						//$cost=$invoice_cost;
						//$invoice_number=html("$row1[invoice_number]");
						//$invoice_id=html("$row1[id]");
						//$val=$encrypt->encrypt("$invoice_id");
						//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
						//$_SESSION['balance_invoice'][$invoice_id]=$balance;
				}//end s2
				if($continue){continue;}
				
				///now get details of  cost
				$sql3=$error3=$s3='';$placeholders3=array();	
				$sql3="SELECT a.unauthorised_cost as billed_cost, a.authorised_cost 
						as authorised_cost,a.pay_type, a.invoice_number , b.name
						FROM tplan_procedure a, procedures b  WHERE a.treatment_procedure_id =:treatment_procedure_id and a.procedure_id=b.id";
				$placeholders3[':treatment_procedure_id']=$row1['treatment_procedure_id'];
				$error3="Error: Unable to pt details from uniq ";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){
					$procedure_name=html($row3['name']);
					$billed_cost=html($row3['billed_cost']);
					$pay_type=html($row3['pay_type']);
					//for cash guys
					if($row3['pay_type'] == 2){
					}
					
					//for points
					elseif($row3['pay_type'] == 3){
					}
					
					//for insured patients
					elseif($row3['pay_type'] == 1){
						//check if amount authorised is 0
						$authorised_cost=html($row3['authorised_cost']);
						$invoice_number=html($row3['invoice_number']);
					}
				}
					

				//get doctor who raised invoice
				$doctor='';
				$sql4=$error3=$s3='';$placeholders3=array();	
				$sql4="SELECT first_name, middle_name, last_name,id FROM users where id=:user_id";
				$placeholders4[':user_id']=$row1['doc_id'];
				$error4="Error: Unable to pt details from uniq ";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
				foreach($s4 as $row4){
					$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
					$doctor_id=html($row4['id']);
					if($pay_type==1){
						//get doctor billed sum
						if(!isset($doctor_billed_array[$doctor_id])){$doctor_billed_array[$doctor_id]=$billed_cost;}
						else{$doctor_billed_array[$doctor_id]= $doctor_billed_array[$doctor_id] + $billed_cost;}
						
						//get doctor authorised sum
						if(!isset($doctor_authorised_array[$doctor_id])){$doctor_authorised_array[$doctor_id]=$authorised_cost;}
						else{$doctor_authorised_array[$doctor_id]= $doctor_authorised_array[$doctor_id] + $authorised_cost;}								
						
						//get doctor name
						if(!isset($doctor_name_array[$doctor_id])){$doctor_name_array[$doctor_id]="$doctor";}
						//get doctor array
						if(!in_array($doctor_id,$doctor_id_array)){$doctor_id_array[]=$doctor_id;}
					}
					elseif($pay_type==2){ //for cash
						//get doctor billed sum
						if(!isset($doctor_billed_cash_array[$doctor_id])){$doctor_billed_cash_array[$doctor_id]=$billed_cost;}
						else{$doctor_billed_cash_array[$doctor_id]= $doctor_billed_cash_array[$doctor_id] + $billed_cost;}
						
												
						
						//get doctor name
						if(!isset($doctor_name_cash_array[$doctor_id])){$doctor_name_cash_array[$doctor_id]="$doctor";}
						//get doctor array
						if(!in_array($doctor_id,$doctor_id_cash_array)){$doctor_id_cash_array[]=$doctor_id;}
					}
				}
							
				$when_added=html("$row1[when_added]");			
					

				//get patient balance
				$enc_pid=$encrypt->encrypt($row1['pid']);
				$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
				$data=explode('#',"$result");
				$insurance_balance="$data[0]";
				$cash_balance="$data[1]";
				$points_balance="$data[2]";
				$invoices_array[]=array('when_added'=>"$when_added",  'doctor'=>"$doctor", 'patient_name'=>"$patient_name", 
											'insurer'=>"$insurer",'invoice_number'=>"$invoice_number", 'billed_cost'=>"$billed_cost",'authorised_cost'=>"$authorised_cost",'procedure_name'=>"$procedure_name",'pay_type'=>$pay_type,'doctor_id'=>$doctor_id,'insurance_balance'=>"$insurance_balance",'cash_balance'=>"$cash_balance",'points_balance'=>"$points_balance"
										 );
									 
			}//end s1
			
			
			//get procdues that weere finished first time without any notes e.g. xrays
			$sql1=$error1=$s1='';$placeholders1=array();	//$doctor_filter2 = " and created_by = $doctor_filter_var ";  $doctor_filter
			$sql1="SELECT `treatment_procedure_id` , date_procedure_added as when_added , created_by as doc_id ,  `pid` 
					FROM `tplan_procedure` WHERE `date_procedure_added` >=:from_date AND `date_procedure_added` <=:to_date  $doctor_filter2 and  status > 0 
					ORDER BY `treatment_procedure_id` ASC ";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			
			foreach($s1 as $row1 ){
				if(in_array($row1['treatment_procedure_id'],$treatment_procedure_id_array)){continue;}
				$continue=false;
				$invoice_cost=$billed_cost=$amount_paid=$doctor=$invoice_number=$authorised_cost='';

				//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer , internal_patient,a.type
						from patient_details_a a 
						left join insurance_company c on a.type=c.id
						left join covered_company b on a.company_covered=b.id 
						 where pid=:pid ";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
				foreach($s2 as $row2){
					if($ptype_filter_var != '' and $ptype_filter_var != $row2['type']){
						$continue=true;
						break;
					}
					$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
						$company=html("$row2[company_covered]");
						$insurer=html("$row2[insurer]");
						if($company!=''){$insurer="$insurer - $company";}
						else{$insurer="$insurer";}
						if($row2['internal_patient'] > 0){$continue=true;break;}
						//$cost=$invoice_cost;
						//$invoice_number=html("$row1[invoice_number]");
						//$invoice_id=html("$row1[id]");
						//$val=$encrypt->encrypt("$invoice_id");
						//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
						//$_SESSION['balance_invoice'][$invoice_id]=$balance;
				}//end s2
				if($continue){continue;}
				
				///now get details of  cost
				$sql3=$error3=$s3='';$placeholders3=array();	
				$sql3="SELECT a.unauthorised_cost as billed_cost, a.authorised_cost 
						as authorised_cost,a.pay_type, a.invoice_number , b.name
						FROM tplan_procedure a, procedures b  WHERE a.treatment_procedure_id =:treatment_procedure_id and a.procedure_id=b.id";
				$placeholders3[':treatment_procedure_id']=$row1['treatment_procedure_id'];
				$error3="Error: Unable to pt details from uniq ";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){
					$procedure_name=html("$row3[name] - $row1[treatment_procedure_id]");
					$billed_cost=html($row3['billed_cost']);
					$pay_type=html($row3['pay_type']);
					//for cash guys
					if($row3['pay_type'] == 2){
					}
					
					//for points
					elseif($row3['pay_type'] == 3){
					}
					
					//for insured patients
					elseif($row3['pay_type'] == 1){
						//check if amount authorised is 0
						$authorised_cost=html($row3['authorised_cost']);
						$invoice_number=html($row3['invoice_number']);
					}
				}
				

				//get doctor who did the procedure first time
				$doctor='';
				$sql4=$error3=$s3='';$placeholders3=array();	
				$sql4="SELECT first_name, middle_name, last_name,id FROM users where id=:user_id";
				$placeholders4[':user_id']=$row1['doc_id'];
				$error4="Error: Unable to pt details from uniq ";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
				foreach($s4 as $row4){
					$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
					$doctor_id=html($row4['id']);
					if($pay_type==1){
						//get doctor billed sum
						if(!isset($doctor_billed_array[$doctor_id])){$doctor_billed_array[$doctor_id]=$billed_cost;}
						else{$doctor_billed_array[$doctor_id]= $doctor_billed_array[$doctor_id] + $billed_cost;}
						
						//get doctor authorised sum
						if(!isset($doctor_authorised_array[$doctor_id])){$doctor_authorised_array[$doctor_id]=$authorised_cost;}
						else{$doctor_authorised_array[$doctor_id]= $doctor_authorised_array[$doctor_id] + $authorised_cost;}								
						
						//get doctor name
						if(!isset($doctor_name_array[$doctor_id])){$doctor_name_array[$doctor_id]="$doctor";}
						//get doctor array
						if(!in_array($doctor_id,$doctor_id_array)){$doctor_id_array[]=$doctor_id;}
					}
					elseif($pay_type==2){ //for cash
						//get doctor billed sum
						if(!isset($doctor_billed_cash_array[$doctor_id])){$doctor_billed_cash_array[$doctor_id]=$billed_cost;}
						else{$doctor_billed_cash_array[$doctor_id]= $doctor_billed_cash_array[$doctor_id] + $billed_cost;}
						
												
						
						//get doctor name
						if(!isset($doctor_name_cash_array[$doctor_id])){$doctor_name_cash_array[$doctor_id]="$doctor";}
						//get doctor array
						if(!in_array($doctor_id,$doctor_id_cash_array)){$doctor_id_cash_array[]=$doctor_id;}
					}
				}
							
				$when_added=html("$row1[when_added]");			
					

				
				//get patient balance
				$enc_pid=$encrypt->encrypt($row1['pid']);
				$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
				$data=explode('#',"$result");
				$insurance_balance="$data[0]";
				$cash_balance="$data[1]";
				$points_balance="$data[2]";
				$invoices_array[]=array('when_added'=>"$when_added",  'doctor'=>"$doctor", 'patient_name'=>"$patient_name", 
											'insurer'=>"$insurer",'invoice_number'=>"$invoice_number", 'billed_cost'=>"$billed_cost",'authorised_cost'=>"$authorised_cost",'procedure_name'=>"$procedure_name",'pay_type'=>$pay_type,'doctor_id'=>$doctor_id,'insurance_balance'=>"$insurance_balance",'cash_balance'=>"$cash_balance",'points_balance'=>"$points_balance"
										 );
									 
			}//end s1
			
			
			
			//for cash procedurws
			if(count($invoices_array) >0 ){
				$print_end_table=false;
				$count=$total_authorised_cost=$total_billed_cost=$total_paid=0;
				
				foreach($invoices_array as $row){
					
						if($row['pay_type'] != 2){continue;} //show only cash procedures
						
					
						if($count==0){
							$caption=strtoupper("CASH TREATMENTS BETWEEN  $from_date AND $to_date");
							$print_end_table=true;
							echo "<br><br>
									<table class='normal_table'><caption>$caption</caption><thead>
									<tr><th class=dc_invoice_in_count></th>
									<th class=dc_invoice_in_date>DATE</th>
									<th class=dc_invoice_in_doctor>DOCTOR</th>
									<th class=dc_invoice_in_patient>PATIENT NAME</th>
									<th class=dc_invoice_in_company>CORPORATE</th>
									<th class=dc_invoice_in_id> </th>
									<th class=dc_invoice_in_id>PROCEDURE</th>
									<th class=dc_invoice_in_cost>BILLED COST</th>
									<th class=dc_invoice_in_tray>BALANCE</th>
									</tr></thead><tbody>";	
						}
							$doctor=$row['doctor'];
							$when_added=html("$row[when_added]");
							$invoice_number=html("$row[invoice_number]");
							$procedure_name=html("$row[procedure_name]");
							$billed_cost=html("$row[billed_cost]");
							$patient_name=html("$row[patient_name]");
							$insurer=html("$row[insurer]");
							$authorised_cost=html("$row[authorised_cost]");
							//$val=$row['val'];
							$count++;
							$total_authorised_cost = $total_authorised_cost + $authorised_cost;
							$total_billed_cost = $total_billed_cost + $billed_cost;
							$empty='';
							if($invoice_number == ''){$empty='empty';}
							echo "<tr><td class=count>$count</td>
									<td>$when_added</td>
									<td>$doctor</td>
									<td>$patient_name</td>
									<td>$insurer</td>
							<td> </td><td>$procedure_name </td>
							<td>";
							if($billed_cost > 0){echo number_format($billed_cost,2);}
							else{ echo $billed_cost;}
							echo "</td><td>$row[cash_balance]</td></tr>";
				}
				if($print_end_table){
					echo  "<tr class=total_background><td colspan=7>TOTAL</td><td>".number_format($total_billed_cost,2)."</td>
					<td >".number_format($total_authorised_cost,2)."</td></tr>";
						echo "</tbody></table>";			
				}
				
				//show doctor percentages
				echo "
						<table class='normal_table'><caption>PERCENTAGE CONTRIBUTION FOR CASH PROCEDURES STARTED</caption><thead>
						<tr ><th class=dcp_count2></th>
						<th class=dcp_name2>DOCTOR</th>
						<th class=dcp_amount2>AMOUNT BILLED</th>
						<th class=dcp_percent2>PERCENATGE BILLED</th>
						</tr></thead><tbody>";	
				$i=0;
				$i2=1;
				$n=count($doctor_id_cash_array);
				while($i < $n){
					$doc_id=$doctor_id_cash_array[$i];
					$percent_billed=($doctor_billed_cash_array[$doc_id] / $total_billed_cost ) * 100;
					$billed=html("$doctor_billed_cash_array[$doc_id]");
					
					echo "<tr><td>$i2</td><td>$doctor_name_cash_array[$doc_id]</td><td>".number_format($billed,2)."</td><td>".
						html(number_format($percent_billed,2))."%</td></tr>";
					$i++;
					$i2++;
				}
				echo "</tbody></table>";
			}
			
			
			
			
			//for insured procredures
						//now get for invoices raised
			//get details from unique_inv_table first
			$invoices_array=array();
			$doctor_billed_array=$doctor_id_array=$doctor_name_array=$doctor_authorised_array=array();
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT id,pid,invoice_number,when_raised,added_by FROM unique_invoice_number_generator WHERE 
				when_raised >=:from_date AND when_raised <=:to_date";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			//echo "$_POST[from_date]--$_POST[to_date]--".$s1->rowCount();
			foreach($s1 as $row1 ){
				$invoice_cost=$billed_cost=$amount_paid=$doctor='';
				//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer 
						from patient_details_a a 
						left join covered_company b on a.company_covered=b.id 
						left join insurance_company c on a.type=c.id where pid=:pid and internal_patient=0";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						///now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) as billed_cost,sum( tplan_procedure.authorised_cost )
								as authorised_cost, ifnull( co_payment.amount, 0 ) as co_payment
								FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							//check if amount authorised is 0
							if($row3['authorised_cost'] > 0){
								$row3['authorised_cost'] = $row3['authorised_cost']- $row3['co_payment'];
								$authorised_cost=html($row3['authorised_cost']);
							}
							else{$authorised_cost=html($row3['authorised_cost']);}
							$billed_cost=html($row3['billed_cost']);
						}
						
						
						
						//get doctor who raised invoice
							$doctor='';
							$sql4=$error3=$s3='';$placeholders3=array();	
							$sql4="SELECT first_name, middle_name, last_name,id FROM users where id=:user_id";
							$placeholders4[':user_id']=$row1['added_by'];
							$error4="Error: Unable to pt details from uniq ";
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){
								$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));
								$doctor_id=html($row4['id']);
								//get doctor billed sum
								if(!isset($doctor_billed_array[$doctor_id])){$doctor_billed_array[$doctor_id]=$billed_cost;}
								else{$doctor_billed_array[$doctor_id]= $doctor_billed_array[$doctor_id] + $billed_cost;}
								
								//get doctor authorised sum
								if(!isset($doctor_authorised_array[$doctor_id])){$doctor_authorised_array[$doctor_id]=$authorised_cost;}
								else{$doctor_authorised_array[$doctor_id]= $doctor_authorised_array[$doctor_id] + $authorised_cost;}								
								
								//get doctor name
								if(!isset($doctor_name_array[$doctor_id])){$doctor_name_array[$doctor_id]="$doctor";}
								//get doctor array
								if(!in_array($doctor_id,$doctor_id_array)){$doctor_id_array[]=$doctor_id;}
							}
							
							$when_added=html("$row1[when_raised]");
							$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
							$company=html("$row2[company_covered]");
							$insurer=html("$row2[insurer]");
							if($company!=''){$insurer="$insurer - $company";}
							else{$insurer="$insurer";}
							//$cost=$invoice_cost;
							$invoice_number=html("$row1[invoice_number]");
							$invoice_id=html("$row1[id]");
							$val=$encrypt->encrypt("$invoice_id");
							//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
							//$_SESSION['balance_invoice'][$invoice_id]=$balance;
							$invoices_array[]=array('when_added'=>"$when_added",  'doctor'=>"$doctor", 'patient_name'=>"$patient_name", 
										'insurer'=>"$insurer",'invoice_number'=>"$invoice_number", 'billed_cost'=>"$billed_cost",'authorised_cost'=>"$authorised_cost",
									 'val'=>"$val");
					}//end s2
			}//end s1
			if(count($invoices_array) >0 ){
				$print_end_table=false;
				$count=$total_authorised_cost=$total_billed_cost=$total_paid=0;
				foreach($invoices_array as $row){
						
						if($count==0){
							$caption=strtoupper("INVOICES RAISED BETWEEN  $from_date AND $to_date");
							$print_end_table=true;
							echo "<br><br>
									<table class='normal_table'><caption>$caption</caption><thead>
									<tr><th class=dc_invoice_in_count></th>
									<th class=dc_invoice_in_date>DATE</th>
									<th class=dc_invoice_in_doctor>DOCTOR</th>
									<th class=dc_invoice_in_patient>PATIENT NAME</th>
									<th class=dc_invoice_in_company>CORPORATE</th>
									<th class=dc_invoice_in_id>INVOICE No.</th>
									<th class=dc_invoice_in_cost>BILLED COST</th>
									<th class=dc_invoice_in_tray>AUTHORISED COST</th>
									</tr></thead><tbody>";	
						}
							$doctor=$row['doctor'];
							$when_added=html("$row[when_added]");
							$invoice_number=html("$row[invoice_number]");
							$billed_cost=html("$row[billed_cost]");
							$patient_name=html("$row[patient_name]");
							$insurer=html("$row[insurer]");
							$authorised_cost=html("$row[authorised_cost]");
							$val=$row['val'];
							$count++;
							$total_authorised_cost = $total_authorised_cost + $authorised_cost;
							$total_billed_cost = $total_billed_cost + $billed_cost;
							echo "<tr><td class=count>$count</td>
									<td>$when_added</td>
									<td>$doctor</td>
									<td>$patient_name</td>
									<td>$insurer</td>
							<td><input type=button class='button_in_table_cell button_style invoice_no' value=$invoice_number  /></td>
							<td>";
							if($billed_cost > 0){echo number_format($billed_cost,2);}
							else{ echo $billed_cost;}
							echo "</td><td>";
							if($authorised_cost > 0){echo number_format($authorised_cost,2);}
							else{ echo $authorised_cost;}
							echo "</td></tr>";
				}
				if($print_end_table){
					echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td>
					<td >".number_format($total_authorised_cost,2)."</td></tr>";
						echo "</tbody></table>";			
				}
				
				//show doctor percentages
				echo "
						<table class='normal_table'><caption>PERCENTAGE CONTRIBUTION FOR INVOICES RAISED</caption><thead>
						<tr ><th class=dcp_count2></th>
						<th class=dcp_name2>DOCTOR</th>
						<th class=dcp_amount2>AMOUNT BILLED</th>
						<th class=dcp_percent2>PERCENATGE BILLED</th>
						<th class=dcp_amount2>AMOUNT AUTHORISED</th>
						<th class=dcp_percent2>PERCENATGE AUTHORISED</th>
						</tr></thead><tbody>";	
				$i=0;
				$i2=1;
				$n=count($doctor_id_array);
				while($i < $n){
					$doc_id=$doctor_id_array[$i];
					$percent_billed=($doctor_billed_array[$doc_id] / $total_billed_cost ) * 100;
					$billed=html("$doctor_billed_array[$doc_id]");
					
					//$percent_authorised=($doctor_authorised_array[$doc_id] / $total_authorised_cost ) * 100;
					//$percent_authorised2=($authorised / $billed ) * 100;
					$authorised=html("$doctor_authorised_array[$doc_id]");
					echo "<tr><td>$i2</td><td>$doctor_name_array[$doc_id]</td><td>".number_format($billed,2)."</td><td>".
						html(number_format($percent_billed,2))."%</td><td>";
						if($authorised > 0){
							echo number_format($authorised,2);
							$percent_authorised2=($authorised / $billed ) * 100;
						}
						else{ echo $authorised;$percent_authorised2=0;}
						echo "</td><td>".
						html(number_format($percent_authorised2,2))."%</td></tr>";
					$i++;
					$i2++;
				}
				echo "</tbody></table>";
			}
			
			/*
			if(count($invoices_array) >0 ){
				$print_end_table=false;
				$count=$total_authorised_cost=$total_billed_cost=$total_paid=0;
				
				foreach($invoices_array as $row){
					
						if($row['pay_type'] != 1){continue;} //show only insured procedures
						
					
						if($count==0){
							$caption=strtoupper("INSURED TREATMENTS BETWEEN  $from_date AND $to_date");
							$print_end_table=true;
							echo "<br><br>
									<table class='normal_table'><caption>$caption</caption><thead>
									<tr><th class=dc_invoice_in_count></th>
									<th class=dc_invoice_in_date>DATE</th>
									<th class=dc_invoice_in_doctor>DOCTOR</th>
									<th class=dc_invoice_in_patient>PATIENT NAME</th>
									<th class=dc_invoice_in_company>CORPORATE</th>
									<th class=dc_invoice_in_id>INVOICE No.</th>
									<th class=dc_invoice_in_id>PROCEDURE</th>
									<th class=dc_invoice_in_cost>BILLED COST</th>
									<th class=dc_invoice_in_tray>AUTHORISED COST</th>
									</tr></thead><tbody>";	
						}
							$doctor=$row['doctor'];
							$when_added=html("$row[when_added]");
							$invoice_number=html("$row[invoice_number]");
							$procedure_name=html("$row[procedure_name]");
							$billed_cost=html("$row[billed_cost]");
							$patient_name=html("$row[patient_name]");
							$insurer=html("$row[insurer]");
							$authorised_cost=html("$row[authorised_cost]");
							//$val=$row['val'];
							$count++;
							$total_authorised_cost = $total_authorised_cost + $authorised_cost;
							$total_billed_cost = $total_billed_cost + $billed_cost;
							$empty='';
							if($invoice_number == ''){$empty='empty';}
							echo "<tr><td class=count>$count</td>
									<td>$when_added</td>
									<td>$doctor</td>
									<td>$patient_name</td>
									<td>$insurer</td>
							<td>";
							if($invoice_number != ''){
							  echo "<input type=button class='button_in_table_cell button_style invoice_no' value=$invoice_number  />";
							}
							else{echo "Not Invoiced";}
							 echo " </td><td>$procedure_name </td>
							<td>";
							if($billed_cost > 0){echo number_format($billed_cost,2);}
							else{ echo $billed_cost;}
							echo "</td><td>";
							if($authorised_cost > 0){echo number_format($authorised_cost,2);}
							else{ echo $authorised_cost;}
							echo "</td></tr>";
				}
				if($print_end_table){
					echo  "<tr class=total_background><td colspan=7>TOTAL</td><td>".number_format($total_billed_cost,2)."</td>
					<td >".number_format($total_authorised_cost,2)."</td></tr>";
						echo "</tbody></table>";			
				}
				
				//show doctor percentages
				echo "
						<table class='normal_table'><caption>PERCENTAGE CONTRIBUTION FOR INSURED PROCEDURES STARTED</caption><thead>
						<tr ><th class=dcp_count2></th>
						<th class=dcp_name2>DOCTOR</th>
						<th class=dcp_amount2>AMOUNT BILLED</th>
						<th class=dcp_percent2>PERCENATGE BILLED</th>
						<th class=dcp_amount2>AMOUNT AUTHORISED</th>
						<th class=dcp_percent2>PERCENATGE AUTHORISED</th>
						</tr></thead><tbody>";	
				$i=0;
				$i2=1;
				$n=count($doctor_id_array);
				while($i < $n){
					$doc_id=$doctor_id_array[$i];
					$percent_billed=($doctor_billed_array[$doc_id] / $total_billed_cost ) * 100;
					$billed=html("$doctor_billed_array[$doc_id]");
					
					//$percent_authorised=($doctor_authorised_array[$doc_id] / $total_authorised_cost ) * 100;
					//$percent_authorised2=($authorised / $billed ) * 100;
					$authorised=html("$doctor_authorised_array[$doc_id]");
					echo "<tr><td>$i2</td><td>$doctor_name_array[$doc_id]</td><td>".number_format($billed,2)."</td><td>".
						html(number_format($percent_billed,2))."%</td><td>";
						if($authorised > 0){
							echo number_format($authorised,2);
							$percent_authorised2=($authorised / $billed ) * 100;
						}
						else{ echo $authorised;$percent_authorised2=0;}
						echo "</td><td>".
						html(number_format($percent_authorised2,2))."%</td></tr>";
					$i++;
					$i2++;
				}
				echo "</tbody></table>";
			}
			*/
			
			//else{echo "<label  class=label>There are no treatments for the selected criteria</label>";}
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
		
		
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
		<br>	
			
	<form action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_cr1'] = "$token";  ?>
					<input type="hidden" name="token_cr1"  value="<?php echo $_SESSION['token_cr1']; ?>" />
					
				<!--show doctor-->
				<div class='grid-15'><label for="" class="label">Select Doctor</label>
				</div>
				<div class='grid-25'><select name=doc>
					<option value='all'>ALL Doctors</option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,first_name, middle_name, last_name from users where user_type=1 order by first_name";
						$error = "Unable to get doctors";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name] "));
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
					?>
					</select>
				</div>	
				<div class=clear></div><br>		

				<!--show patient type-->
				<div class='grid-15'><label for="" class="label">Select Patient Type</label>
				</div>
				<div class='grid-25'><select name=ptype>
					<option value='all'>ALL</option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company order by name";
						$error = "Unable to get ptypes";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html("$row[name]");
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
					?>
					</select>
				</div>	
				<div class=clear></div><br>					
				
				<!--date range-->
				<div class='grid-15'><label for="" class="label">Select date range</label></div>
				<div class='grid-10'><input type=text name=from_date class=date_picker /></div>	
				<div class='grid-10'><label for="" class="label">To this date</label></div>
				<div class='grid-10'><input type=text name=to_date class=date_picker /></div>	
				
				<div class='grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>