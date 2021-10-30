<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,88)){exit;}
echo "<div class='grid_12 page_heading'>TREATMENT COMPLETION RATE</div>";
?>
<div class=grid-container>
<?php 

//get results
if(isset($_POST['token_tcr1']) and 	$_POST['token_tcr1']!='' and $_POST['token_tcr1']==$_SESSION['token_tcr1']){
		$_SESSION['token_tcr1']='';
		$exit_flag=false;
		//for detailed report
		if($_POST['rtype']=='detailed'){	
			$sql=$error=$s='';$placeholders=array();
			//check if doctor selected
			if(!$exit_flag and !isset($_POST['doc']) or $_POST['doc']=='' ){	
					$result_class="error_response";
					$result_message="Please specify a search criteria for the doctor";
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
			$doctor=$insurer=$company=$balance=$procedure='';
			$total_cost=$total_paid=0;
			$doc_var=" all doctors ";
			//doctor criteria
			if($_POST['doc']!='all'){
				$doc_id=$encrypt->decrypt($_POST['doc']);
				$doctor = " and b.created_by=:doc_id ";
				$placeholders[':doc_id']=$doc_id;
				
			}
			
			//procedure criteria
			if($_POST['procedure_name']!='all'){
				$procedure_id=$encrypt->decrypt($_POST['procedure_name']);
				$procedure = " and b.procedure_id=:procedure_id ";
				$placeholders[':procedure_id']=$procedure_id;
				
			}		
			
				$sql="select a.name, b.teeth, b.details, b.date_procedure_added,b.authorised_cost , b.unauthorised_cost,
					case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status ,
					 concat(c.first_name,' ',c.middle_name,' ',c.last_name) as
					patient_name, c.patient_number, concat(d.first_name,' ',d.middle_name,' ',d.last_name) as doctor_name, b.procedure_id, c.type,
					b.pay_type
					from tplan_procedure as b join procedures as a on b.procedure_in_alias_invoice=0 and b.procedure_id=a.id and b.date_procedure_added >=:from_date
						and b.date_procedure_added <=:to_date $procedure
					join patient_details_a as c on c.pid= b.pid and c.internal_patient=0
					join users as d on b.created_by=d.id $doctor
					";
				$placeholders[':from_date']=$_POST['from_date'];	
				$placeholders[':to_date']=$_POST['to_date'];
				$s = select_sql($sql, $placeholders, $error, $pdo);	
				//echo "count is ".$s->rowCount();exit;
				if($s->rowCount() > 0){
					//$treatment_array=array();
					$i=$total_not_authorised=$total_declined=$total_billed=$total_partiall_authorisation =0;
					$total_unstarted=$total_started=$total_done=$sum_unstarted=$sum_started=$sum_done =0;
					$var='';
					foreach($s as $row){
					//check if procedure was paid in points and get cash equivalent
					$bg_color=$billed_cost=$authorised_cost='';

						$when_added=html("$row[date_procedure_added]");
						$patient=ucfirst(html($row['patient_name']));
						$doctor=ucfirst(html($row['doctor_name']));
						$patient_number=html("$row[patient_number]");
						$procedure=html("$row[name]");
						$teeth=html("$row[teeth]");
						$details=html("$row[details]");
						$status=html("$row[status]");
						$billed_cost=html("$row[unauthorised_cost]");
						$authorised_cost=html("$row[authorised_cost]");
						if($row['pay_type']==3){
							$bg_color='light_blue_background';
							//get cash equivalent  from insurer price table first
							$sql3=$error3=$s3='';$placeholders3=array();
							$sql3="select  price from insurer_procedure_price where procedure_id=:procedure_id and insurer_id=:insurer_id";
							$error3="Unable to get procedure cost";
							$placeholders3[':procedure_id']=$row['procedure_id'];
							$placeholders3[':insurer_id']=$row['type'];
							$s3 = select_sql($sql3, $placeholders3, $error3, $pdo);
							if($s3->rowCount() > 0){foreach($s3 as $row3){$billed_cost=$authorised_cost=html($row3['price']);}}
							else{//check for cost in master table
							$sql31=$error31=$s31='';$placeholders31=array();
							$sql31="select  cost from procedures where id=:procedure_id";
							$error31="Unable to get procedure cost";
							$placeholders31[':procedure_id']=$row['procedure_id'];
							$s31 = select_sql($sql31, $placeholders31, $error31, $pdo);
							if($s31->rowCount() > 0){foreach($s31 as $row31){$billed_cost=$authorised_cost=html($row31['cost']);}}					
							}
						}
						$total_billed = $total_billed  + $billed_cost;
						
						$not_started=$partially_done=$done='';
						if($status=='Not Started'){
							$not_started='Not Started';
							$total_unstarted++;
							$sum_unstarted = $sum_unstarted  + $billed_cost;
						}
						elseif($status=='Partially Done'){
							$partially_done='Partially Done';
							$total_started++;
							$sum_started = $sum_started  + $billed_cost;	
						}
						elseif($status=='Done'){
							$done='Done';
							$total_done++;
							$sum_done = $sum_done  + $billed_cost;	
						}
						
						if($authorised_cost == ''){
							$auth_status='NO';
							$total_not_authorised = $total_not_authorised  + $billed_cost;
						}
						elseif($authorised_cost == $billed_cost){$auth_status='YES';}
						elseif($authorised_cost > 0 and $authorised_cost < $billed_cost){
							$auth_status=number_Format($authorised_cost,2);
							$total_partiall_authorisation = $total_partiall_authorisation  + ($billed_cost - $authorised_cost);
						}
						elseif($authorised_cost == 0){
							$auth_status='DECLINED';
							$total_declined = $total_declined  + $billed_cost;
						}
						
						if($procedure=='X-Ray'){$treatment="$details $teeth";}
						else{$treatment="$procedure $teeth $details";}
						$status=html("$row[status]");
						

						if($i==0){
							if($_POST['doc']!='all'){$doc_var=" Dr. $doctor ";}
							$caption=strtoupper("treatment completion rate for $doc_var between $from_date and $to_date");
							$var ="<br><br>
							<table class='normal_table'><caption>$caption</caption><thead>
							<tr ><th class=tcr1_ount></th>
							<th class=tcr1_doctor>DOCTOR</th><th class=tcr1_date>DATE</th>
							<th class=tcr1_patient>PATIENT NAME</th>
							<th class=tcr1_procedure>TREATMENT PROCEDURE</th>
							<th class=tcr1_billed>COST</th>
							<th class=tcr1_authorised>AUTHORISED</th>
							<th class=not_started_status>NOT STARTED</th>
							<th class='started_status'>STARTED</th>
							<th class='finished_status'>FINISHED</th>
							</tr>
							</tr></thead><tbody>";						
						}
						$i++;
						$var="$var<tr class=$bg_color><td>$i</td><td>$doctor</td><td>$when_added</td><td>$patient</td>
						<td>$treatment</td><td>";
							if($billed_cost > 0 ){$var="$var".number_format($billed_cost,2);}
							else{$var="$var ";}
						$var="$var</td><td>$auth_status</td>
						<td>$not_started</td><td>$partially_done</td><td>$done</td></tr>";
					}
					$var="$var<tr class=total_background><td colspan=5>TOTALS</td><td>".number_format($total_billed,2)."</td><td>&nbsp;</td>
					<td>".number_format($sum_unstarted,2)."</td><td>".number_format($sum_started,2)."</td>
					<td>".number_format($sum_done,2)."</td></tr></tbody></table>";
					$total_unstarted=($total_unstarted / $i) * 100;
					$total_started=($total_started / $i) * 100;
					$total_done=($total_done / $i) * 100;
					echo "<br>";
					echo "<table class='summary_treatment_rate'><tbody>
						<tr><td class=tcrs_t1>Total number of treatment procedures  planned by $doc_var between $from_date and $to_date</td>
							<td class=tcrs_t2>$i</td></tr>
						<tr><td class=tcrs_t1>Percentage not started</td>
							<td class=tcrs_t2>".number_format($total_unstarted,2)."%</td></tr>
						<tr><td class=tcrs_t1>Percentage partially done</td>
							<td class=tcrs_t2>".number_format($total_started,2)."%</td></tr>
						<tr><td class=tcrs_t1>Percentage done</td>
							<td class=tcrs_t2>".number_format($total_done,2)."%</td></tr></tbody></table>";
					echo "$var";
				}
				else{echo "<label  class=label>There are no treatments for the selected criteria</label>";}
				exit;
			}//end do if exit flag is not true
			if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
		}
		//for summary		
		elseif($_POST['rtype']=='summary'){
			$sql=$error=$s='';$placeholders=array();
			$exit_flag=false;
			//check if date is selcted
			if(!$exit_flag and !isset($_POST['from_date2']) or $_POST['from_date2']==''  or !isset($_POST['to_date2']) 
				or $_POST['to_date2']==''  ){	
					$result_class="error_response";
					$result_message="Please specify the date range for the search criteria";
					$exit_flag=true;
			}	
			
			//check if procedure
			if(!$exit_flag and !isset($_POST['procedure_name2']) or $_POST['procedure_name2']=='' ){	
					$result_class="error_response";
					$result_message="Please specify the treatment procedure for the search criteria";
					$exit_flag=true;
			}				
					
			if(!$exit_flag){
			$from_date=html($_POST['from_date2']);
			$to_date=html($_POST['to_date2']);
			}
			
			//procedure criteria
			$procedure='';
			if(!$exit_flag and $_POST['procedure_name2']!='all'){
				$procedure_id=$encrypt->decrypt($_POST['procedure_name2']);
				$procedure = " and a.procedure_id=:procedure_id ";
				$placeholders[':procedure_id']=$procedure_id;
				
			}
			if(!$exit_flag and $_POST['summarized_type']=='by_doc'){
				$doctor=$insurer=$company=$balance='';
				$doc_var=" all doctors ";
				//doctor criteria
				if(isset($_POST['doc2']) and $_POST['doc2']!='all'){
					$doc_id=$encrypt->decrypt($_POST['doc2']);
					$doctor = " and a.created_by=:doc_id ";
					$placeholders[':doc_id']=$doc_id;
					
				}
				//get number planned
				$s='';
				$sql="SELECT sum(a.number_done) as number_planned,b.name as procedure_name, a.procedure_id, c.first_name, c.middle_name,
						c.last_name, a.created_by from tplan_procedure a 
						join procedures b  on a.procedure_id=b.id and a.date_procedure_added >=:from_date and 
									a.date_procedure_added <=:to_date  $doctor $procedure
						join users c on c.id=a.created_by 
						group by a.created_by, a.procedure_id
					";	
				$error='unabel to get summaried treatments by procedure';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				$all_procedures_array=array();
				foreach($s as $row){
					$all_procedures_array["$row[created_by]$row[procedure_id]"]=array('procedure_name'=>html($row['procedure_name']),
					'doc_id'=>html($row['created_by']),
						'number_planned'=>html($row['number_planned']),'doctor'=>html("$row[first_name] $row[middle_name] $row[last_name]"));
				}
				
				//get partially done procedures
				$s='';
				$sql="SELECT sum(a.number_done) as number_partially_done, a.procedure_id,a.created_by
					from tplan_procedure a where a.date_procedure_added >=:from_date and a.date_procedure_added <=:to_date $doctor $procedure
					and status=1
					group by a.created_by, a.procedure_id
				";	
				$error='unabel to get summaried treatments by procedure that are partially done';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$all_procedures_array["$row[created_by]$row[procedure_id]"]['partially_done']=html($row['number_partially_done']);
				}
				
				//get done procedures
				$sql="SELECT sum(a.number_done) as number_finished, a.procedure_id,a.created_by
					from tplan_procedure a where a.date_procedure_added >=:from_date and a.date_procedure_added <=:to_date $doctor $procedure
					and status=2 group by a.created_by, a.procedure_id";	
				$error='unabel to get summaried treatments by procedure that are  done';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$all_procedures_array["$row[created_by]$row[procedure_id]"]['done']=html($row['number_finished']);
				}				
				
				function compare_lastname($a, $b) {
					return strnatcmp($a['doc_id'], $b['doc_id']); 
				} 
				// sort alphabetically by name 
				usort($all_procedures_array, 'compare_lastname');
				if(count($all_procedures_array) > 0){
					$i=0;
					foreach($all_procedures_array as $row){
						$procedure_name=html($row['procedure_name']);
						$doctor=ucfirst(html($row['doctor']));
						$planned=$not_started=$partially_done=$started_status_count=$done='';
						//number planned
						$planned=html($row['number_planned']);
						
						//partially done
						if(isset($row['partially_done'])){$partially_done=html($row['partially_done']);}
						else{$partially_done='';}
						
						//done
						if(isset($row['done'])){$done=html($row['done']);}
						else{$done='';}
						
						//not started
						if(($done + $partially_done) != $planned){$not_started = $planned - ($done + $partially_done);}
						if($i==0){
							if($_POST['procedure_name2']!='all'){$caption=strtoupper("$procedure_name done between $from_date and $to_date");}
							else{$caption=strtoupper("treatment procedures done between $from_date and $to_date");}
							echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=tpds_count2></th>
								<th class=tpds_doc_name2>DOCTOR</th><th class=tpds_pname2>TREATMENT PROCEDURE</th>
								<th class=tpds_planed2>NUMBER PLANNED</th>
								<th class=tpds_planed2>NUMBER NOT STARTED</th><th class=tpds_planed2>NUMBER STARTED</th>
								<th class=tpds_planed2>NUMBER FINISHED</th></tr></thead><tbody>";
						}
						$i++;
						echo "<tr><td>$i</td><td>$doctor</td><td>$procedure_name</td><td>$planned</td><td>$not_started</td><td>$partially_done</td><td>$done</td></tr>";
					}
					echo "</tbody></table>";
					exit;
				}
				else{echo "<div class='grid-100 error_response'>No procedures were done for the selected search criteria</div>";}
				
			}
			elseif(!$exit_flag and $_POST['summarized_type']=='by_procedure'){
				//get number planned
				$sql="SELECT sum(a.number_done) as number_planned,b.name as procedure_name, a.procedure_id
						from tplan_procedure a join procedures b  
						on a.procedure_id=b.id and a.date_procedure_added >=:from_date and a.date_procedure_added <=:to_date $procedure
						group by a.procedure_id
					";	
				$error='unabel to get summaried treatments by procedure';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				$all_procedures_array=array();
				foreach($s as $row){
					$all_procedures_array["$row[procedure_id]"]=array('procedure_name'=>html($row['procedure_name']),
						'number_planned'=>html($row['number_planned']));
				}
				
				//get partially done procedures
				$sql="SELECT sum(a.number_done) as number_partially_done, a.procedure_id
					from tplan_procedure a where a.date_procedure_added >=:from_date and a.date_procedure_added <=:to_date $procedure
					and status=1
					group by a.procedure_id
				";	
				$error='unabel to get summaried treatments by procedure that are partially done';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$all_procedures_array["$row[procedure_id]"]['partially_done']=html($row['number_partially_done']);
				}
				
				//get done procedures
				$sql="SELECT sum(a.number_done) as number_finished, a.procedure_id
					from tplan_procedure a where a.date_procedure_added >=:from_date and a.date_procedure_added <=:to_date $procedure
					and status=2 group by a.procedure_id";	
				$error='unabel to get summaried treatments by procedure that are  done';
				$placeholders[':from_date']=$_POST['from_date2'];	
				$placeholders[':to_date']=$_POST['to_date2'];
				$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$all_procedures_array["$row[procedure_id]"]['done']=html($row['number_finished']);
				}				
				
				//exit;
				if(count($all_procedures_array) > 0){
					$i=0;
					foreach($all_procedures_array as $row){
						$procedure_name=html($row['procedure_name']);
						$planned=$not_started=$partially_done=$started_status_count=$done='';
						//number planned
						$planned=html($row['number_planned']);
						
						//partially done
						if(isset($row['partially_done'])){$partially_done=html($row['partially_done']);}
						else{$partially_done='';}
						
						//done
						if(isset($row['done'])){$done=html($row['done']);}
						else{$done='';}
						
						//not started
						if(($done + $partially_done) != $planned){$not_started = $planned - ($done + $partially_done);}
						/*if($row['sum_planned']!=''){$planned=number_format(html($row['sum_planned']));}
						if($row['not_started_count']!=''){$not_started=number_format(html($row['not_started_count']));}
						if($row['started_status_count']!=''){$partially_done=number_format(html($row['started_status_count']));}
						if($row['done_count']!=''){$done=number_format(html($row['done_count']));}*/
						if($i==0){
							if($_POST['procedure_name2']!='all'){$caption=strtoupper("$procedure_name done between $from_date and $to_date");}
							else{$caption=strtoupper("treatment procedures done between $from_date and $to_date");}
							echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=tpds_count></th>
								<th class=tpds_pname>TREATMENT PROCEDURE</th><th class=tpds_planed>NUMBER PLANNED</th>
								<th class=tpds_planed>NUMBER NOT STARTED</th><th class=tpds_planed>NUMBER STARTED</th>
								<th class=tpds_planed>NUMBER FINISHED</th></tr></thead><tbody>";
						}
						$i++;
						echo "<tr><td>$i</td><td>$procedure_name</td><td>$planned</td><td>$not_started</td><td>$partially_done</td><td>$done</td></tr>";
					}
					echo "</tbody></table>";
					exit;
				}
				else{echo "<div class='grid-100 error_response'>No procedures were done for the selected search criteria</div>";}
			}
				
		
		}
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
		<br>	
			<?php $token = form_token(); $_SESSION['token_tcr1'] = "$token";  ?>
	<form action="" method="POST" enctype="" name="" id="">
		
					<input type="hidden" name="token_tcr1"  value="<?php echo $_SESSION['token_tcr1']; ?>" />
				
				<!--select report criteria-->				
				<div class='grid-15'><label for="" class="label">Select Report Type</label>
				</div>
				<div class='grid-25'><select name=rtype class='rtype1'>
					<option ></option>
					<option value='detailed'>Detailed Report</option>
					<option value='summary'>Summarized Report</option>
					</select>
				</div>	
				<div class=clear></div><br>
				<!-- group summary report -->
				<div class='grid-100 summarized_sel1 no_padding' >
					<div class='grid-15'><label for="" class="label">Select Group Criteria</label>
					</div>
					<div class='grid-25'><select class=summarized_type name=summarized_type>
						<option ></option>
						<option value='by_doc'>Group by doctor</option>
						<option value='by_procedure'>Group by procedure</option>
						</select>
					</div>
				</div>
				
				<!-- show summary report report -->
				<div class='grid-100 summary_td no_padding' >
					<!--show doctor-->
					<div class='clear '></div><br>
					<div class='grid-15 summary_td_doc'><label for="" class="label">Select Doctor</label>
					</div>
					<div class='grid-25 summary_td_doc'><select name=doc2>
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
						
					<div class='clear summary_td_doc'></div><br>
					<!--show procedures-->
					<div class='grid-15 '><label for="" class="label">Select Procedure</label>
					</div>
					<div class='grid-25 '><select name=procedure_name2>
						<option value='all'>ALL Procedures</option>
						<?php
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from procedures order by name";
							$error = "Unable to get procedures";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html("$row[name] ");
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>
					<div class=clear></div><br>
					<!--date range-->
					<div class='grid-25'><label for="" class="label">Treatments planned from this date</label></div>
					<div class='grid-10'><input type=text name=from_date2 class=date_picker /></div>	
					<div class='grid-10'><label for="" class="label">To this date</label></div>
					<div class='grid-10'><input type=text name=to_date2 class=date_picker /></div>	
					<div class='grid-10'>	<input type="submit"  value="Submit"/></div>
				</div>
				
				<!-- show detailed report -->
				<div class='grid-100 detailed_td no_padding' >
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
					<!--show procedures-->
					<div class='grid-15'><label for="" class="label">Select Procedure</label>
					</div>
					<div class='grid-25'><select name=procedure_name>
						<option value='all'>ALL Procedures</option>
						<?php
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from procedures order by name";
							$error = "Unable to get procedures";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html("$row[name] ");
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>
					<div class=clear></div><br>
					<!--date range-->
					<div class='grid-25'><label for="" class="label">Treatments planned from this date</label></div>
					<div class='grid-10'><input type=text name=from_date class=date_picker /></div>	
					<div class='grid-10'><label for="" class="label">To this date</label></div>
					<div class='grid-10'><input type=text name=to_date class=date_picker /></div>
					<div class='grid-10'>	<input type="submit"  value="Submit"/></div>					
				</div>
				

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>