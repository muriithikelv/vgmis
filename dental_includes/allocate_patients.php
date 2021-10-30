<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,48)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT ALLOCATION</div>"; ?>
<div class="grid-100 margin_top">

<?php
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			elseif($_SESSION['result_class']=='bad'){
			//	echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
	}
	else{echo "<div class='feedback hide_element'></div>";}
		?>
	<!--select action type-->	
	<div class='grid-15'><label class=label>Select Action</label></div>
	<div class=grid-20><select class='allocate_action'><option></option>
													  <option value='add'>Add patient to waiting list</option>
													  <option value='edit'>Edit waiting list</option>
						</select>
	</div>
	<div class=clear></div><br>

	<!--this is for editing the waiting list-->	
	<div id='edit_waiting_list' class='grid-100 grid-parent'>
		<form action="" method="post" name="" class='patient_form search_patient_2 check_selected_patient' id="">
			<fieldset><legend>Edit Waiting List</legend>
				<?php 
					$show_surgery=true;
					$token = form_token(); $_SESSION['token_allocate3'] = "$token";  ?>
				<input type="hidden" name="token_allocate3"  value="<?php echo $_SESSION['token_allocate3']; ?>" />
				
				<!--select action type-->	
				<div class='grid-15'><label class=label>Select Action</label></div>
				<div class=grid-25><select class=edit_type name=edit_type><option></option>
																  <option value='patient_left'>Patient has left</option>
																  <option value='change_surgery'>Allocate patient to different surgery</option>
																  <option value='remove_patient'>Remove patient from waiting list</option>
									</select>
				</div>
		
				<!-- select the patient from waiting list -->
				<?php
					$names_array=$unseen_array=array();
					//get registerd patints
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.id,concat(b.first_name,' ',b.middle_name,' ',b.last_name) from  patient_allocations a, patient_details_a b  
							where a.patient_type=1 and a.pid=b.pid  and 	(date(time_allocated)=curdate() or date(time_start_treatment)='0000-00-00')
							and patient_left=0 and  date(time_start_treatment)='0000-00-00' ";					
					$error="Unable to select registered patients unseen in waiting list";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$names_array[]=html($row[1]);
						$name=html($row[1]);
						$id=$encrypt->encrypt(html($row['id']));
						$unseen_array[]=array('allocation_id'=>"$id", 'patient_names'=>"$name");
					}	
					
					//get unregisterd patints
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.id,concat(b.first_name,' ',b.middle_name,' ',b.last_name) as names from  patient_allocations a, unregistered_patients b  
							where a.patient_type=0 and a.pid=b.id  and 	(date(time_allocated)=curdate() or date(time_start_treatment)='0000-00-00')
							and patient_left=0 and  date(time_start_treatment)='0000-00-00' ";					
					$error="Unable to select unregistered patients unseen in waiting list";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						$names_array[]=html($row['names']);
						$name=html($row['names']);
						$id=$encrypt->encrypt(html($row['id']));
						$unseen_array[]=array('allocation_id'=>"$id", 'patient_names'=>"$name");
					}
					
					//sort the array based on name
					array_multisort($names_array, SORT_ASC, $unseen_array);
					if(count($unseen_array) > 0){
						echo "<div class='grid-15'><label class=label>Select Patient</label></div>
						<div class='grid-30'><select class=input_in_table_cell name=allocated_patient ><option></option>";
						foreach($unseen_array as $unseen){
							echo "<option value=$unseen[allocation_id]>$unseen[patient_names]</option>";
						}
						echo "</select></div>";
						
				
					}
					else{
						$show_surgery=false;
						echo "<div class='prefix-30 grid-30'><lable class=label>There are no patients left in waiting list</label></div>";
					}
				?>	
				<div class=clear></div><br>
				<div id='show_surgery' class='grid-100 grid-parent'>
					
					<!-- select a surgery -->
					<?php
						if($show_surgery){
							$sql=$error=$s='';$placeholders=array();
							$sql="select surgery_name,surgery_id from surgery_names order by surgery_name";
							$error="Unable to select surgerirs";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);
							echo "<div class='grid-15'><label class=label>Surgery Name</label></div>
								<div class='grid-15'><select class=input_in_table_cell name=allocate_surgery ><option></option>";
							foreach($s as $row){
								$name=html($row['surgery_name']);
								$id=$encrypt->encrypt(html($row['surgery_id']));
								echo "<option value='$id'>$name</option>";
							}			
							echo "</select></div>";
							//echo "<div class=clear></div><br><div class='grid-10 prefix-15'><input type=submit  value=Submit /></div>";
						}
					?>	
				</div>
					<?php
					if($show_surgery){
						echo "<div class=clear></div><br><div class='grid-10 prefix-15'><input type=submit  value=Submit /></div>";
					}
					?>


				
		</fieldset>
		</form>	
	</div>
	<div class=patient_form_container>		
	<!--this is for adding a patient to the waiting list-->	
	<div id='add_to_waiting_list' class='grid-100 grid-parent'>
		<form action="" method="post" name="" class='patient_form search_patient_2 check_selected_patient' id="">
		<fieldset><legend>Add Patient To Waiting List</legend>
				<?php 
					$show_submit="true";
					$token = form_token(); $_SESSION['token_allocate1'] = "$token";  ?>
				<input type="hidden" name="token_allocate1"  value="<?php echo $_SESSION['token_allocate1']; ?>" />
				
				<!-- select a surgery -->
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql="select surgery_name,surgery_id from surgery_names order by surgery_name";
					$error="Unable to select surgerirs";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					echo "<div class='grid-15'><label class=label>Surgery Name</label></div>
						<div class='grid-15'><select class=input_in_table_cell name=allocate_surgery ><option></option>";
					foreach($s as $row){
						$name=html($row['surgery_name']);
						$id=$encrypt->encrypt(html($row['surgery_id']));
						echo "<option value='$id'>$name</option>";
					}			
					echo "</select></div>";
				?>	
				
				<!--select the patient type-->	
				<div class='grid-10'><label class=label>Patient Type</label></div>
				<div class=grid-15><select class='allocate_patient_type' name=patient_type ><option></option>
																  <option value='registered'>Registered</option>
																  <option value='unregistered'>Un-registered</option>
									</select>
				</div>
				<div class=clear></div><br>
				
				<!--show registerd patitne-->
				<div id='allocate_registered' class='grid-100 grid-parent'>
					<?php 
						include 'search_for_patient_2.php';
						echo "<div class=clear></div><br>";
						
						//expedite
						 echo "<div class='grid-15'><label class=label>Expedite Patient</label></div>  
								<div class='grid-40 '>";
									$sql=$error=$s='';$placeholders=array();
									$sql="select reason,id from expedite_reasons where unlist=0 order by reason";
									$error="Unable to select expedite reasons";
									$s = 	select_sql($sql, $placeholders, $error, $pdo);
									echo "<select class=input_in_table_cell name=expedite_reason_registered ><option>NO</option>";
									foreach($s as $row){
										$reason=html($row['reason']);
										$id=html($row['id']);
										echo "<option value='$id'>$reason</option>";
									}			
									echo "</select></div>";
								
								
								 
						echo "<div class=clear></div><br>
								<div class='grid-10 prefix-15'><input type=submit  value='submit' /></div>";
					?>
					
				</div>
				
				<!--show unregisterd patitne-->
				<div id='allocate_unregistered' class='grid-100 grid-parent'>
					<?php
					//get unregisterd patients who were to come today from unregistered appointments table
					$sql=$error=$s='';$placeholders=array();
					$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names,a.id from unregistered_patients a, unregistered_patient_appointments b where a.id=b.pid and b.appointment_date=curdate()
						  and a.id not in(select pid from patient_allocations where date(time_allocated)=curdate() and patient_type=0)";
						  //patient type zero is for unregisterd patients and 1 is for registerd patients
					$error="Unable to get unregisterd patient appointments";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					if($s->rowCount() > 0  ){
						echo "<div class='grid-15'><label class=label>Select Patient</label></div>
								<div class=grid-40><select name=unregistered_patient ><option></option>";
						foreach($s as $row){
							$pid=$encrypt->encrypt($row['id']);
							$name=html($row['names']);
							echo "<option value=$pid>$name</option>";
						}		
						echo "</select></div>";
					}
					else{
						$show_submit="false";
						echo "<div class='grid-40 prefix-15'>
							<label class=label>There are no more unregistered patients with appointments today<label></div>";
					} 
										//echo "show submit is $show_submit";
					if($show_submit == "true"){
						//expedite
						 echo "<div class=clear></div><br>
							<div class='grid-15'><label class=label>Expedite Patient</label></div>  
							<div class='grid-40 '>";
								$sql=$error=$s='';$placeholders=array();
								$sql="select reason,id from expedite_reasons where unlist=0 order by reason";
								$error="Unable to select expedite reasons";
								$s = 	select_sql($sql, $placeholders, $error, $pdo);
								echo "<select class=input_in_table_cell name=expedite_reason_unregistered ><option>NO</option>";
								foreach($s as $row){
									$reason=html($row['reason']);
									$id=html($row['id']);
									echo "<option value='$id'>$reason</option>";
								}			
								echo "</select></div>";
									
						echo "<div class=clear></div><br>
								<div class='grid-10 prefix-15'><input type=submit  value='submit' /></div>";
				} 
					?>
				
				</div><!-- end allocate_unregistered div -->
				
		</fieldset>
		</form>	
	</div>
	
	</div>
					
		


		<div class=clear></div>
			<br><br>
			
<!--show current allocation status-->
<?php 
	$current_allocations=$allocation_array=$time_allocated_array=$majina=array();
	//get all allocations for today for registered 6,7,12
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.surgery_id, a.pid, sec_to_time(timestampdiff(minute, time_allocated , current_timestamp() ) * 60 ),
			concat(b.first_name,' ',b.middle_name,' ',b.last_name),patient_left, date(time_start_treatment), b.type, b.company_covered, time_allocated,  
			time_start_treatment,timediff(time_start_treatment, time_allocated) , timediff(now(), time_allocated), a.id, b.patient_number, a.discharge_time,
			a.pause_treatment, a.resume_treatment ,a.treatment_status, timediff(discharge_time, time_allocated),treatment_finish,
			timediff(treatment_finish, time_start_treatment),timediff(now(), time_start_treatment),b.card_issued,a.previous_allocation,a.visit,a.added_by,a.expedite
			from  patient_allocations a, patient_details_a b  where a.patient_type=1 and a.pid=b.pid  and 
			(date(time_allocated)=curdate()   or  date(discharge_time)='0000-00-00') order by a.id";
	$error="unable to get current allocations";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row)	{
			//check if this is a new patient or not
			$new_patient='yes';
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="SELECT tplan_id FROM tplan_id_generator WHERE pid=:pid AND when_added < curdate()";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to check if this is a new pt or not";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			if($s3->rowCount() > 0){$new_patient='no';}
			else{ //check if the guy was swapped
				$sql3=$error3=$s3='';$placeholders3=array();
				$sql3="select old_pid from swapped_patients where old_pid=:pid";
				$placeholders3[':pid']=$row['pid'];
				$error3="unable to check if this is pt was swapped or not";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				if($s3->rowCount() >0){$new_patient='no';}
			}
			
			//get person who made the allocation
			$added_by='';
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="SELECT first_name, middle_name, last_name FROM users where id=:added_by";
			$placeholders3[':added_by']=$row['added_by'];
			$error3="unable to get person who added the allocation";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			foreach($s3 as $row3){$added_by=ucfirst(html("$row3[first_name] $row3[middle_name] $row3[last_name]"));}
			
			//get reason for expediting
			$expedite='';
			if($row['expedite'] > 0){
				$sql3=$error3=$s3='';$placeholders3=array();
				$sql3="SELECT reason from expedite_reasons where id=:id";
				$placeholders3[':id']=$row['expedite'];
				$error3="unable to get expedite reason";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){$expedite=html("$row3[reason]");}
			}
			
			
			//this array will be used to sort the array
			$time_allocated_array[]=$row['time_allocated'];
			//$pt_count++;
			$appointment_time='No Appt.';
			$appoint_minutes=$minutes_now=$alloc_minutes=$col_appoint=$patient_type=$covered_company=$discharge_time='';
			//get time allocated in miutes
			$t_alloc=explode(' ',$row['time_allocated']);
			$t_alloc2=explode(':', $t_alloc[1]);
			$alloc_minutes=$t_alloc2[0]*60 + $t_alloc2[1];
			
			//enable dispaly in 12hr format
			if($t_alloc2[0] > 12) {$allocation_time = $t_alloc2[0] - 12 .":$t_alloc2[1]:$t_alloc2[2]";}
			elseif($t_alloc2[0] <= 12) {$allocation_time=$t_alloc[1];}	
			
			//get current time in minutes and compare with appointmen tim
			date_default_timezone_set('Africa/Nairobi');
			$t=date('h:i:a');
			$t_now=explode(':',$t);
			if($t_now[2]=='am' or $t_now[0]==12) {$minutes_now=($t_now[0]*60) + ($t_now[1]);}
			elseif($t_now[2]=='pm' and  $t_now[0]!=12) {$minutes_now=(($t_now[0]+12)*60) + ($t_now[1]);}
			
			//get if the patient had an appointment for this day
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="select shour, smin,rank,am_pm ,first_name,middle_name, last_name, treatment from registered_patient_appointments a, users b where pid=:pid and 
			      appointment_date=curdate() and a.doc_id=b.id";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to get appointments in allocations";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			$appointment_time='';			
			foreach($s3 as $row3){
				$apt_t2=html($row3['treatment']);
				$ap_t='';
				if($apt_t2 != ''){$ap_t="<br>$apt_t2";}
				$appointment_time="$row3[shour]:$row3[smin] $row3[am_pm]<br>Dr. $row3[first_name] $row3[middle_name] $row3[last_name] $ap_t";
				$appoint_minutes=($row3['rank']*60) + $row3['smin'];	
					$col_appoint='';
					//check if appointment time is passed
					if($row[5]=='0000-00-00'){//if patient has not been seen
						$tdif = $minutes_now - $appoint_minutes;
						if($tdif > 10 and $alloc_minutes <= $appoint_minutes) {$col_appoint='red_class';}
					}
					elseif($row[5]!='0000-00-00'){//if patient has  been seen
						//get minutes when treatment started
						$t_alloc=explode(' ',$row[9]);
						$t_alloc2=explode(':', $t_alloc[1]);
						$start_minutes=$t_alloc2[0]*60 + $t_alloc2[1];			
						$tdif = $start_minutes - $appoint_minutes;
						if($tdif > 10 and $alloc_minutes <= $appoint_minutes) {$col_appoint='red_class';}
					}
					
					//check if the patient arrived after appointment time
					if($appoint_minutes < $alloc_minutes) {$col_appoint='yellow_class';}				
			
			
			}
			


			//check if patient has left
			//if( $row['patient_left']==1){$patient_status="Patient<br>Left";}
			
			

			//get waiting time
			if ($row[5]=='0000-00-00' and $row['discharge_time']=='0000-00-00 00:00:00') {$waiting_time=$row[11];}
				elseif ($row[5]=='0000-00-00' and $row['discharge_time']!='0000-00-00 00:00:00') {$waiting_time=$row[18];}
				elseif($row[5]!='0000-00-00') {$waiting_time=$row[10];} 
			
			//get insurer type
			if($row['type']!=''){
				$sql4=$error4=$s4='';$placeholders4=array();
				$sql4="select name from insurance_company where id=:id";
				$placeholders4[':id']=$row['type'];
				$error4="unable to get patient type";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);			
				foreach($s4 as $row4){$patient_type=$row4['name'];}
			}
			
			//get compnay covered
			if($row['company_covered']!=''){
				$sql4=$error4=$s4='';$placeholders4=array();
				$sql4="select name from covered_company where id=:id";
				$placeholders4[':id']=$row['company_covered'];
				$error4="unable to get covered_company";
				$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);			
				foreach($s4 as $row4){$covered_company=$row4['name'];}
			}
			
			//get patient balance
			$balance=get_patient_balance($pdo,$row['pid']);

			
			//split discharge timestamp
			$t_discharge=explode(' ',$row['discharge_time']);
			
			//split pause treatment timestamp
			$t_pause=explode(' ',$row['pause_treatment']);
			
			//split resume treatment timestamp
			$t_resume=explode(' ',$row['resume_treatment']);
			
			//set treatment status
			if($row['patient_left']==1){$treatment_status="Patient left";}
			elseif($row['treatment_finish']!='0000-00-00 00:00:00'){$treatment_status="Finished";}
			else{$treatment_status=$row['treatment_status'];}
			
			//set discrgae status
			if($row['discharge_time']!='0000-00-00 00:00:00'){$discharge_time=$row['discharge_time'];}
			else{$discharge_time="";}
			
			//card issued
			$card_issued='';
			if($row['card_issued']=='YES'){$card_issued='Y';}
			elseif($row['card_issued']=='NO'){$card_issued='N';}
			$majina[]="$row[3]";
			$current_allocations[]=array('row_color'=>"$col_appoint", 'patient_number'=>$row['patient_number'],'patient_names'=>$row[3], 
				'waiting_time'=>"$waiting_time", 'appointment_time'=>"$appointment_time",'allocation_time'=>$row['time_allocated'],
				'patient_type'=>"$patient_type",'covered_company'=>"$covered_company",'balance'=>"$balance",'patient_left'=>$row['patient_left'],
				'treatment_status'=>"$treatment_status",'discharge_time'=>"$discharge_time",'surgery_id'=>$row['surgery_id'],'allocation_id'=>$row['id'],
				'treatment_finished'=>$row['treatment_finish'],'duration_finished'=>"$row[20]",'duration_ongoing'=>"$row[21]",
				'registered_patient'=>"1",'pid'=>$row['pid'],'treatment_status_number'=>$row['treatment_status'],
				'new_patient'=>"$new_patient",'card_issued'=>"$card_issued",'previous_allocation'=>$row['previous_allocation'],'visit'=>$row['visit'],'added_by'=>"$added_by", 'expedite'=>"$expedite"
			);
			
			//$col$i[]=$r2[1];$col$i[]=$r2[2];$col$i[]=$r2[3];
	}

	//get all allocations for today for unregistered 
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.surgery_id, a.pid, sec_to_time(timestampdiff(minute, time_allocated , current_timestamp() ) * 60 ),
			concat(b.first_name,' ',b.middle_name,' ',b.last_name) as names,patient_left, date(time_start_treatment), null, null, time_allocated,  
			time_start_treatment,timediff(time_start_treatment, time_allocated) , timediff(now(), time_allocated), a.id, null, a.discharge_time,
			a.pause_treatment, a.resume_treatment ,a.treatment_status, timediff(discharge_time, time_allocated),treatment_finish,
			timediff(treatment_finish, time_start_treatment),timediff(now(), time_start_treatment),a.previous_allocation,a.visit,a.added_by,a.expedite
			from  patient_allocations a, unregistered_patients b  where a.patient_type=0 and a.pid=b.id  and 
			(date(time_allocated)=curdate() or date(time_start_treatment)='0000-00-00' or  date(discharge_time)='0000-00-00' ) order by a.id";
				
	$error="unable to get current allocations";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row)	{
			//no need for below since if the guy is unregistered he is new and will have no records
			/*//check if this is a new patient or not
			$new_patient='yes';
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="SELECT tplan_id FROM tplan_id_generator WHERE pid=:pid AND when_added < curdate()";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to check if this is a new pt or not";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			if($s3->rowCount() > 0){$new_patient='no';}
			else{ //check if the guy was swapped
				$sql3=$error3=$s3='';$placeholders3=array();
				$sql3="select old_pid from swapped_patients where old_pid=:pid";
				$placeholders3[':pid']=$row['pid'];
				$error3="unable to check if this is pt was swapped or not";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				if($s3->rowCount() >0){$new_patient='no';}
			}*/
			
			//get person who made the allocation
			$added_by='';
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="SELECT first_name, middle_name, last_name FROM users where id=:added_by";
			$placeholders3[':added_by']=$row['added_by'];
			$error3="unable to get person who added the allocation";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			foreach($s3 as $row3){$added_by=ucfirst(html("$row3[first_name] $row3[middle_name] $row3[last_name]"));}
			
			//get reason for expediting
			$expedite='';
			if($row['expedite'] > 0){
				$sql3=$error3=$s3='';$placeholders3=array();
				$sql3="SELECT reason from expedite_reasons where id=:id";
				$placeholders3[':id']=$row['expedite'];
				$error3="unable to get expedite reason";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){$expedite=html("$row3[reason]");}
			}
			
			$new_patient='yes';
			
			//this array will be used to sort the array
			$time_allocated_array[]=$row['time_allocated'];
			//$pt_count++;
			$appointment_time='No Appt.';
			$appoint_minutes=$minutes_now=$balance=$alloc_minutes=$col_appoint=$patient_type=$covered_company=$dicharge_time='';
			//get time allocated in miutes
			$t_alloc=explode(' ',$row['time_allocated']);
			$t_alloc2=explode(':', $t_alloc[1]);
			$alloc_minutes=$t_alloc2[0]*60 + $t_alloc2[1];
			
			//enable dispaly in 12hr format
			if($t_alloc2[0] > 12) {$allocation_time = $t_alloc2[0] - 12 .":$t_alloc2[1]:$t_alloc2[2]";}
			elseif($t_alloc2[0] <= 12) {$allocation_time=$t_alloc[1];}	
			
			//get current time in minutes and compare with appointmen tim
			date_default_timezone_set('Africa/Nairobi');
			$t=date('h:i:a');
			$t_now=explode(':',$t);
			if($t_now[2]=='am' or $t_now[0]==12) {$minutes_now=($t_now[0]*60) + ($t_now[1]);}
			elseif($t_now[2]=='pm' and  $t_now[0]!=12) {$minutes_now=(($t_now[0]+12)*60) + ($t_now[1]);}
			
			//get if the patient had an appointment for this day
			$sql3=$error3=$s3='';$placeholders3=array();
			$sql3="select shour, smin,rank,am_pm ,first_name,middle_name, last_name,treatment from unregistered_patient_appointments a, users b where pid=:pid and 
			      appointment_date=curdate() and a.doc_id=b.id";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to get appointments in allocations";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			$appointment_time='';			
			foreach($s3 as $row3){
				$ap_t2 = html($row3['treatment']);
				$ap_t='';
				if($ap_t2 != ''){$ap_t="<br> $ap_t2";}
				$appointment_time="$row3[shour]:$row3[smin] $row3[am_pm]<br>Dr. $row3[first_name] $row3[middle_name] $row3[last_name] $ap_t";
				$appoint_minutes=($row3['rank']*60) + $row3['smin'];	
					$col_appoint='';
					//check if appointment time is passed
					if($row[5]=='0000-00-00'){//if patient has not been seen
						$tdif = $minutes_now - $appoint_minutes;
						if($tdif > 10 and $alloc_minutes <= $appoint_minutes) {$col_appoint='red_class';}
					}
					elseif($row[5]!='0000-00-00'){//if patient has  been seen
						//get minutes when treatment started
						$t_alloc=explode(' ',$row[9]);
						$t_alloc2=explode(':', $t_alloc[1]);
						$start_minutes=$t_alloc2[0]*60 + $t_alloc2[1];			
						$tdif = $start_minutes - $appoint_minutes;
						if($tdif > 10 and $alloc_minutes <= $appoint_minutes) {$col_appoint='red_class';}
					}
					
					//check if the patient arrived after appointment time
					if($appoint_minutes < $alloc_minutes) {$col_appoint='yellow_class';}				
			
			
			}
			


			//check if patient has left
		//	if( $row['patient_left']==1){$patient_status="Patient<br>Left";}
			
			

			//get waiting time
			if ($row[5]=='0000-00-00' and $row['discharge_time']=='0000-00-00 00:00:00') {$waiting_time=$row[11];}
				elseif ($row[5]=='0000-00-00' and $row['discharge_time']!='0000-00-00 00:00:00') {$waiting_time=$row[18];}
				elseif($row[5]!='0000-00-00') {$waiting_time=$row[10];} 

			


			
			//split discharge timestamp
			$t_discharge=explode(' ',$row['discharge_time']);
			
			//split pause treatment timestamp
			$t_pause=explode(' ',$row['pause_treatment']);
			
			//split resume treatment timestamp
			$t_resume=explode(' ',$row['resume_treatment']);
			
			//set treatment status
			if($row['patient_left']==1){$treatment_status="Patient left";}
			elseif($row['treatment_finish']!='0000-00-00 00:00:00'){$treatment_status="Finished";}
			else{$treatment_status=$row['treatment_status'];}
			
			//set discrgae status
			if($row['discharge_time']!='0000-00-00 00:00:00'){$discharge_time=$row['discharge_time'];}
			else{$discharge_time="";}
			/*elseif ($row[5]=='0000-00-00'){$status="Untreated";}
			elseif ($row[5]!='0000-00-00' and  $t_pause[0]=='0000-00-00'){$status="Treating";}
			elseif ($row[5]!='0000-00-00' and $t_resume[0] >= $t_pause[0]){$status="Treating";}
			elseif ($row[5]!='0000-00-00' and $t_resume[0] < $t_pause[0]){$status="On hold";}*/
			$majina[]="$row[3]";
			$current_allocations[]=array('row_color'=>"$col_appoint", 'patient_number'=>'Unregistered','patient_names'=>$row[3], 
				'waiting_time'=>"$waiting_time", 'appointment_time'=>"$appointment_time",'allocation_time'=>$row['time_allocated'],
				'patient_type'=>"$patient_type",'covered_company'=>"$covered_company",'balance'=>"$balance",'patient_left'=>$row['patient_left'],
				'treatment_status'=>"$treatment_status",'discharge_time'=>"$discharge_time",'surgery_id'=>$row['surgery_id'],'allocation_id'=>$row['id'],
				'treatment_finished'=>$row['treatment_finish'],'duration_finished'=>"$row[20]",'duration_ongoing'=>"$row[21]",
				'registered_patient'=>"0",'pid'=>$row['pid'],'treatment_status_number'=>$row['treatment_status'],
				'new_patient'=>"$new_patient",'card_issued'=>'N','previous_allocation'=>$row['previous_allocation'],'visit'=>$row['visit'],'added_by'=>"$added_by",'expedite'=>"$expedite"
			);
			
			//$col$i[]=$r2[1];$col$i[]=$r2[2];$col$i[]=$r2[3];
	}	//$time_allocated_array[]=$row['time_allocated'];
	
	//now sort the array
	if(count($current_allocations) >0 ){array_multisort($time_allocated_array, SORT_ASC, $current_allocations);}
	
	//generate tokens for the forms 4
	$token = form_token(); $_SESSION['token_allocate4'] = "$token";  //this is for starting treatment forms
	$token = form_token(); $_SESSION['token_allocate5'] = "$token";  //this is for pausing treatment forms
	$token = form_token(); $_SESSION['token_allocate6'] = "$token";  //this is for resumnig treatment forms
	$token = form_token(); $_SESSION['token_allocate7'] = "$token";  //this si for finishing treatment
	$token = form_token(); $_SESSION['token_allocate8'] = "$token";  //this si for discharging treatment
	
	$untreated=$left=$finished_treatment=$treating=$on_hold=$total=$new_patient_count=0;
	//getting the current surgery chairs
	$sql=$error=$s='';$placeholders=array();
	$sql="select surgery_id, upper(surgery_name) from surgery_names order by surgery_name";
	$error="Unable to get surgery list";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	echo "<div class='grid-100 position_relative'><br>";
	foreach($s as $row){
		//get other surgeries
		$sql8=$error8=$s8='';$placeholders8=array();
		$sql8="select surgery_id, upper(surgery_name) as surgery_name from surgery_names  where surgery_id!=:surgery_id order by surgery_name";
		$error8="Unable to get surgery list";
		$placeholders8[':surgery_id']=$row['surgery_id'];
		$s8 = 	select_sql($sql8, $placeholders8, $error8, $pdo);
		$transfer_list='';
		foreach($s8 as $row8){
			$val8=$encrypt->encrypt(html($row8['surgery_id']));
			$surgery_name=html($row8['surgery_name']);
			$transfer_list="$transfer_list <option value='$val8'>$surgery_name</option>";
			
		}				
		
		//echo "xxx $transfer_list xxx";						
		$surgery_in_use=false;
		//check if this surgery is in use surgery_in_use
		$sql5=$error5=$s5='';$placeholders5=array();
		$sql5="select id from patient_allocations where treatment_status=1 and surgery_id=:surgery_id and 
		treatment_finish='0000-00-00 00:00:00'";
		$error5="Unable to get ongoing treatment in surgery";
		$placeholders5[':surgery_id']=$row['surgery_id'];
		$s5 = 	select_sql($sql5, $placeholders5, $error5, $pdo);	
		if($s5->rowCount() > 0){$surgery_in_use=true;}
		else{$surgery_in_use=false;}
		
		//if($s2->rowCount()>0){
			$surgery_name=html($row[1]);
			//get doctor logged in
			$sql51=$error51=$s51='';$placeholders51=array();
			$sql51="select concat(a.first_name,' ',a.middle_name,' ',a.last_name), max(b.id) from users a, surgery_logins b 
					where a.id=b.user_id and b.surgery_id=:surgery_id";
			$error51="Unable to get ongoing treatment in surgery";
			$placeholders51[':surgery_id']=$row['surgery_id'];
			$s51 = 	select_sql($sql51, $placeholders51, $error51, $pdo);
			foreach($s51 as $row51){
				$docname=ucfirst(html("$row51[0]"));
				if($docname!=''){$docname=" -- Dr. $docname is working here ";}
			}
			
			echo "<table class='normal_table allocations'><caption  >$surgery_name $docname</caption><thead>
			<th class='allocate_status_count'></th><th class='allocate_status_card'></th><th class='allocate_status_pid'>PATIENT No.</th>
			<th class='allocate_status_name'>PATIENT NAME</th><th class='allocate_status_ptype'>PATIENT TYPE</th>
			<th class='allocate_status_balance'>BALANCE</th><th class='allocate_status_appoint'>APPOINTMENT</th>
			<th class='allocate_status_allocated'>TIME ALLOCATED</th><th class='allocate_status_wait'>WAITING</th>
			<th class='allocate_status_total_wait'>TIME AT CLINIC</th><th class='allocate_status_tstatus'>STATUS</th>
			<th class='allocate_status_discharge'>DISCHARGED</th>
			</thead><tbody>";
			$i=$count=0;
			$n=count($current_allocations);
			
		
			if($n > 0){
				foreach($current_allocations as $allocate){
					if($row['surgery_id']!=$allocate['surgery_id']){continue;}
					$count++;
					$card_issued=html($allocate['card_issued']);
					$registered_patient=html($allocate['registered_patient']);
					$appointment_pid=html($allocate['pid']);
					$patient_number=html($allocate['patient_number']);
					$patient_name=html($allocate['patient_names']);
					$added_by="<br>".html($allocate['added_by']);
					
					if($allocate['covered_company']!=''){$patient_type=html("$allocate[patient_type] - $allocate[covered_company]");}
					elseif($allocate['covered_company']==''){$patient_type=html("$allocate[patient_type]");}
					$balance=html($allocate['balance']);
					$row_color=html($allocate['row_color']);
					$appointment_time=$allocate['appointment_time'];
					$styled_appointment='';
					if($appointment_time!=''){
						$data=explode('<br>',$appointment_time);
						$data[0]=html("$data[0]");
						$data[1]=html("$data[1]");
						$tr='';
						if(isset($data[2]) and $data[2]!=''){$tr="<br>$data[2]";}
						$styled_appointment="$data[0]<br>$data[1]$tr";
					}
					$time_allocated=html($allocate['allocation_time']);
					$waiting_time=html($allocate['waiting_time']);
					//get time spent at clinic
					if($allocate['discharge_time']!=''){
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select  timediff('$allocate[discharge_time]', '$time_allocated' )";
						$error2="Unable to get total time spent at clinic";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){$time_at_clinic=html($row2[0]);}
					}
					elseif($allocate['discharge_time']==''){
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select  timediff(now(), '$time_allocated' )";
						$error2="Unable to get total time spent at clinic";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){$time_at_clinic=html($row2[0]);}
					}
					if($allocate['previous_allocation'] > 0){
						$var_previous_allocation_id=$allocate['previous_allocation'];
						$var_time='00:00:00#';
						$var12='';
						while( true){
							$var1=get_previous_time_at_clinic($var_previous_allocation_id,$var_time,$pdo);
							//echo "$i2 $var1";
							$data1=explode('$$',"$var1");
							//$data2=explode('#',"$data1[1]");
							$var12=" $data1[1]";
							if($data1[0]==0){
								$data=explode('#',"$data1[1]");
								//add new waiting time to previous time
								$sql1a=$error1a=$s1a='';$placeholders1a=array();
								$sql1a="SELECT time(addtime('$data[0]','$time_at_clinic')); ";
								$error1a="Unable to get total time spent at previous surgerries";
								$s1a = 	select_sql($sql1a, $placeholders1a, $error1a, $pdo);
								foreach($s1a as $row1a){$time_at_clinic="$row1a[0]<br>$surgery_name $time_at_clinic <br> $data[1]";} 
								break;
							}
							elseif($data1[0]>0){
								$var_previous_allocation_id=$data1[0];
								$var_time="$data1[1]";
							}
							
						}
					 
					// $data1=explode('#',"$var1");
					//echo "ss $var1 ss";
					// $time_at_clinic = "$time_at_clinic xx $var12";
					}
					
					$discharge_time=html($allocate['discharge_time']);
					$duration_finished=html($allocate['duration_finished']);
					$duration_ongoing=html($allocate['duration_ongoing']);
					$val=$encrypt->encrypt(html($allocate['allocation_id']));
					$treatment_finished=html($allocate['treatment_finished']);
					$treatment_status=html($allocate['treatment_status']);
					$expedite_reason=html($allocate['expedite']);
					$red_background=''; //this class will highlight cases that need to be expedited
					if($expedite_reason != '' and ($treatment_status == '0' or $treatment_status == '1' or $treatment_status == '2')){$red_background = ' red_background';}
					$new_patient=html($allocate['new_patient']);
					$visit=html($allocate['visit']);
					if($visit!=''){$visit="<br>$visit";}
					$untreated_background_color=$new_patient_color='';
					if($allocate['treatment_status_number'] == 0){$untreated_background_color="untreated_patients $row_color";}
					if($new_patient == 'yes'){$new_patient_color=" new_patient_color $row_color";}
					if(userHasRole($pdo,20) or userHasRole($pdo,12)){$enc_pnum=$encrypt->encrypt("$patient_number");}
					if($new_patient=='yes'){$new_patient_count++;}
					echo "<tr class=' $row_color ' >";
					//patient number
					if($new_patient_color!=''){	
						echo "<td class='$new_patient_color count_class'>$count</td><td class='$new_patient_color '>$card_issued</td>
						<td  class='$new_patient_color alloc_link_color'>";
						if(userHasRole($pdo,20)){//for treatment done
							
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color2 goto_tdone $new_patient_color'>$patient_number</a>";
						}
						else {echo "$patient_number";}
					}
					elseif($untreated_background_color!=''){	
						echo "<td class='$untreated_background_color count_class'>$count</td><td class='$untreated_background_color '>$card_issued</td>
							<td  class='$untreated_background_color alloc_link_color'>";
						if(userHasRole($pdo,20)){//for treatment done
							
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color2 goto_tdone $untreated_background_color'>$patient_number</a>";
						}
						else {echo "$patient_number";}
					}
					else{	
						echo "<td class='count_class'>$count</td><td class=''>$card_issued</td><td  class='alloc_link_color'>";
						if(userHasRole($pdo,20)){//for treatment done
							
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color2 goto_tdone '>  $patient_number</a>";
						}
						else {echo "$patient_number";}
					}
					
					//patient name
					if($new_patient_color!=''){	
						echo "</td><td class=' $new_patient_color '>";
						if(userHasRole($pdo,12)){//for patient contacts
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color2 goto_pt_contact  $new_patient_color'>$patient_name</a>";
						}
						else {echo "$patient_name";}
					}
					elseif($untreated_background_color!=''){	
						echo "</td><td class=' $untreated_background_color '>";
						if(userHasRole($pdo,12)){//for patient contacts
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color2 goto_pt_contact  $untreated_background_color'>$patient_name</a>";
						}
						else {echo "$patient_name";}
					}
					else{	
						echo "</td><td class=' $ '>";
						if(userHasRole($pdo,12)){//for patient contacts
							echo "<input type=hidden value='$enc_pnum' />";
							echo "<a href='' class='link_color3 goto_pt_contact  '>$patient_name</a>";
						}
						else {echo "$patient_name";}
					}

					echo "</td><td  class='$new_patient_color $untreated_background_color '>$patient_type</td>
					<td class='$new_patient_color $untreated_background_color'>";
					$visits='';
					if($registered_patient==1){
						$pid_bal="pid_$appointment_pid";
						//if(isset($_SESSION[$appointment_pid])){unset($_SESSION["$appointment_pid"]);}
						//this is a molars registerd pt
						if(isset($_SESSION["$pid_bal"])){
							foreach($_SESSION["$pid_bal"] as $row_bal){
								echo "I: $row_bal[insurance]
								<br>C: $row_bal[cash]
								<br>P: $row_bal[points]";
							}
						}
						elseif(!isset($_SESSION["$pid_bal"])){
							$_SESSION["$pid_bal"]=array();
							$enc_pid=$encrypt->encrypt("$appointment_pid");
							$result=show_pt_statement_brief($pdo,$enc_pid,$encrypt);
							$data=explode('#',"$result");
							$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
							foreach($_SESSION["$pid_bal"] as $row_bal){
								echo "I: $row_bal[insurance]
								<br>C: $row_bal[cash]
								<br>P: $row_bal[points]";
							}
						}
						//check if the pt has any pending planned visits
						/*$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select  coalesce(sum(visits_planned),0) as visits_planeed, coalesce(sum(visits_remaining),0) as visits_remaining from tplan_visits where pid=:pid and visits_remaining >0";
						$error2="Unable to pending visits";
						$placeholders2[':pid']=$appointment_pid;
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){
							if($row2['visits_planeed']>0 and $row2['visits_remaining']>0){
								$var=$row2['visits_planeed'] - $row2['visits_remaining'] + 1;
								$visits=html("Visit $var of $row2[visits_planeed]");
								$visits="<br>$visits";
							}
						}*/
					}
					else{echo "";} //this is for an unregistered poatient
					
					
					echo "</td><td class='$new_patient_color $untreated_background_color'>$styled_appointment $visit</td>
					<td class='$new_patient_color $untreated_background_color'>$time_allocated $added_by</td><td class='$new_patient_color $untreated_background_color'>$waiting_time</td>
					<td class='$new_patient_color $untreated_background_color'>$time_at_clinic</td><td  class='$new_patient_color $untreated_background_color'>";
					//show treatment status 
					if($treatment_status == '0'){//untreated  and $discharge_time=='0000-00-00 00:00:00'
						$untreated++;
						echo "Untreated";				
						//check if the guy is a doctor and then show the form and !$surgery_in_use
						if($_SESSION['is_user_doctor'] == 1 ){ ?>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate4"  value='<?php echo "$_SESSION[token_allocate4]"; ?>' />
								<input type="hidden" name="start_treatment"  value='<?php echo "$val"; ?>' />
								<input type=submit value='Start' />
							</form>	
							<?php
						}
					}
					elseif($treatment_status == '1' ){//treating
						$treating++;
						echo "Treating<br>Duration $duration_ongoing";				
						//check if the guy is a doctor and then show the form
						if($_SESSION['is_user_doctor'] == 1  ){ ?>
							<br><br>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate4"  value='<?php echo "$_SESSION[token_allocate4]"; ?>' />
								<input type="hidden" name="treatment_status"  value='<?php echo "$val"; ?>' />
								<select name=hold_finish class=hold_finish><option></option>
									<option value='hold'>Suspend</option>
									<option value='transfer'>Change Surgery</option>
									<option value='finish'>Finished</option>
								</select>
								<br><br>
								<div class=show_transfer_surgeries>
								    Select Surgery
									<select name=transfer_list><option></option>
									<?php
										echo "$transfer_list";
									?>
									</select>
								</div>
								<br>
								<input type=submit value='Submit' />
							</form>	
							<?php
						}
					}				
					elseif($treatment_status == '2'){//on hold
						$on_hold++;
						echo "On hold<br>Duration $duration_ongoing";				
						//check if the guy is a doctor and then show the form
						if($_SESSION['is_user_doctor'] == 1  and !$surgery_in_use){ ?>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate4"  value='<?php echo "$_SESSION[token_allocate4]"; ?>' />
								<input type="hidden" name="resume_treatment"  value='<?php echo "$val"; ?>' />
								<input type=submit value='Resume' />
							</form>	
							<?php
							
						}
					}
					elseif($treatment_status=='Finished'){
						$finished_treatment++;
						echo "Finished<br>$treatment_finished<br>Duration $duration_finished";
					}
					else{
						$left++;
						echo "Patient Left";
					}
					echo "</td><td  class='$new_patient_color $untreated_background_color $red_background form_table'>";
						if($discharge_time != ''){echo "$discharge_time";}
						elseif($discharge_time == '' and $treatment_finished!='0000-00-00 00:00:00' ){
						
							?>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate8"  value='<?php echo "$_SESSION[token_allocate8]"; ?>' />
								<input type="hidden" name="discharge_patient"  value='<?php echo "$val"; ?>' />
								
								
								<input type=submit value='Discharge' />
							</form>	
							<?php					
						}
						//display expedite reason if any
						elseif($treatment_status=='0' or $treatment_status=='1' or $treatment_status=='2'){
							if($expedite_reason != ''){$expedite_reason="Expedite: $expedite_reason";}
							echo "$expedite_reason";
						}
						
					echo "</td></tr>"; 
				}
				$total =$total +$count;
				echo "</tbody></table>";	
			}
		//}	
	}
	$total2=count(array_unique($majina, SORT_REGULAR));
	echo "<div class='grid-100 label show_on_top'>
		<div class='grid-20 no_padding'>NUMBER OF PATIENTS $total2</div>  
		<div class='grid-15 new_pts'>NEW PATIENTS $new_patient_count</div>
		<div class=grid-10>UNTREATED $untreated</div>
		<div class=grid-10>ON HOLD $on_hold</div>
		<div class=grid-10>TREATING $treating</div>
		<div class=grid-10>TREATED $finished_treatment</div>
		<div class=grid-10>LEFT $left</div>
	</div>";
	echo "</div>";//position_relative
	
	
?>
</div>