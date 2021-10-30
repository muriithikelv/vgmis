<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,70)){exit;}
echo "<div class='grid_12 page_heading'>APPOINTMENTS REPORT</div>"; ?>
<div class="grid-100 margin_top ">
<?php   
$exit_flag=false;
//show appointments
if( isset($_POST['token_apr1']) and isset($_SESSION['token_apr1']) and $_SESSION['token_apr1']==$_POST['token_apr1']){
	$_SESSION['token_apr1']=$date_criteria_registered=$date_criteria_unregistered=$registered_criteria=$unregistered_criteria='';
	$skip_registered=$skip_unregistered=false;
	$sql=$error=$s='';$placeholders=array();
	
	//echo "vvv $_POST[search_by_registered] and $_POST[registered_search] vvv";
	//date range
	if(!$exit_flag and isset($_POST['search_criteria_apr']) and $_POST['search_criteria_apr']=='date_range' and 
		isset($_POST['from_date']) and isset($_POST['to_date'])){
		if($_POST['to_date']=='' or $_POST['from_date']==''){
			$exit_flag=true;
			$message='Please specify the date range for the report';
			
		}
		else{
			$date_criteria_registered = " where registered_patient_appointments.appointment_date >=:from_date and 
			registered_patient_appointments.appointment_date <=:to_date";
			$placeholders[':from_date']=strtoupper($_POST['from_date']);
			$placeholders[':to_date']=strtoupper($_POST['to_date']);
			
			$date_criteria_unregistered = " where unregistered_patient_appointments.appointment_date >=:from_date and 
			unregistered_patient_appointments.appointment_date <=:to_date";
			$placeholders[':from_date']=strtoupper($_POST['from_date']);
			$placeholders[':to_date']=strtoupper($_POST['to_date']);
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			$caption=strtoupper("Appointments booked between $from_date and $to_date");
		}
	}
	
	//registered patient
	elseif(!$exit_flag and isset($_POST['search_criteria_apr']) and $_POST['search_criteria_apr']=='patient'
		and isset($_POST['patient_type_apr']) and $_POST['patient_type_apr']=='registered'
		and isset($_POST['search_by_registered']) and isset($_POST['registered_search'])and 
		isset($_POST['search_by_registered'])){
		if($_POST['registered_search']==''){
			$message='Registered patient not specified in search criteria';
			$exit_flag=true;
		} 
		if(!$exit_flag and $_POST['search_by_registered']==''){
			$message='Search by criteria for registered patient not specified';
			$exit_flag=true;
		}
		//for patient number
		if(!$exit_flag and $_POST['search_by_registered']=='patient_number'){
			$registered_criteria = " and patient_details_a.patient_number=:patient_number ";
			$placeholders[':patient_number']=$_POST['registered_search'];
			$skip_unregistered=true;
			//get the guys name for the caption
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="SELECT first_name, middle_name, last_name from patient_details_a where patient_number=:patient_number";
			$placeholders2[':patient_number']=$_POST['registered_search'];
			$error2="Unable to get patient name";
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			if($s2->rowCount()==0){
				$exit_flag=true;
				$message=' No such patient ';
			}
			else{
				foreach($s2 as $row2){
					$name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
				}
				$pnum=html($_POST['registered_search']);
				$caption=strtoupper("Appointments for $pnum - $name");	
			}
		}
		//by patient names
		elseif(!$exit_flag and $_POST['search_by_registered']=='first_name' or $_POST['search_by_registered']=='middle_name' or 
			$_POST['search_by_registered']=='last_name'){	
			$result=get_pt_name3($_POST['search_by_registered'],$_POST['registered_search'],$pdo,$encrypt,'token_apr1','search_by_registered','patient_number','registered_search');
			if($result=="2"){
				$result_class="error_response";
				$message="No such patient found";
				$exit_flag=true;
			}
			else{
				echo "$result";
				exit;
			}
			
		}
	}
	
	//unregistered patient
	elseif(!$exit_flag and isset($_POST['search_criteria_apr']) and $_POST['search_criteria_apr']=='patient'
		and isset($_POST['patient_type_apr']) and $_POST['patient_type_apr']=='unregistered'
		and isset($_POST['search_by_unregistered']) and isset($_POST['unregistered_search'])){
		if($_POST['unregistered_search']==''){
			$message='Unregistered patient not specified in search criteria';
			$exit_flag=true;
		} 
		if(!$exit_flag and $_POST['search_by_unregistered']==''){
			$message='Search by criteria not specified for unregistered patient search';
			$exit_flag=true;
		}
		//for patient number
		if(!$exit_flag and $_POST['search_by_unregistered']=='patient_number' ){
			$unregistered_patient_number=$encrypt->decrypt($_POST['unregistered_search']);
			$unregistered_criteria = " and unregistered_patients.id=:unregistered_patient_number ";
			$placeholders[':unregistered_patient_number']=$unregistered_patient_number;
			$skip_registered=true;
			//get the guys name for the caption
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="SELECT first_name, middle_name, last_name from unregistered_patients where id=:unregistered_patient_number";
			$placeholders2[':unregistered_patient_number']=$unregistered_patient_number;
			$error2="Unable to get unregisterd patient name";
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			foreach($s2 as $row2){
				$name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
			}
			$caption=strtoupper("Appointments for unregisterd patient $name");			
		}
		//by patient names
		elseif(!$exit_flag and $_POST['search_by_unregistered']=='first_name' or $_POST['search_by_unregistered']=='middle_name' or $_POST['search_by_unregistered']=='last_name'){	
			$result=get_pt_name4($_POST['search_by_unregistered'],$_POST['unregistered_search'],$pdo,$encrypt,'token_apr1','search_by_unregistered','patient_number','unregistered_search');
			if($result=="2"){
				$result_class="error_response";
				$message="No such patient found";
				$exit_flag=true;
			}
			else{
				echo "$result";
				exit;
			}
			
		}
	}	
	
	//get appointments for registerd patients
	if(!$exit_flag and !$skip_registered ){
	
		$appointment_array=array();
		$sql="select registered_patient_appointments.appointment_date,  registered_patient_appointments.treatment, registered_patient_appointments.shour, 
				registered_patient_appointments.smin, registered_patient_appointments.rank, registered_patient_appointments.status,
				registered_patient_appointments.am_pm,
			users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
			patient_details_a.first_name as ptf, patient_details_a.middle_name as ptm, 
			patient_details_a.last_name as ptl,patient_details_a.mobile_phone ,surgery_names.surgery_name,
			e.appointment_date as new_appointment_date ,registered_patient_appointments.smin,registered_patient_appointments.added_by
		from registered_patient_appointments join users on registered_patient_appointments.doc_id=users.id
		join patient_details_a on registered_patient_appointments.pid=patient_details_a.pid $registered_criteria 
		left join surgery_names on registered_patient_appointments.surgical_unit=surgery_names.surgery_id
		left join registered_patient_appointments as e on e.id=registered_patient_appointments.new_appointment_id
		$date_criteria_registered";
		$error="Unable to get registerd patients";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			//get person who added appointment
			$added_by='';
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select first_name, middle_name, last_name from users where id=:added_by";
			$error2="Unable to get user name";
			$placeholders2['added_by']=$row['added_by'];
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
			foreach($s2 as $row2){$added_by = ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));}		
				 
			$date=html($row['appointment_date']);
			$doctor=html("$row[docf] $row[docm] $row[docl]");
			$patient=html("$row[ptf] $row[ptm] $row[ptl]");
			$phone=html($row['mobile_phone']);
			$treatment=html($row['treatment']);
			$time=html("$row[shour]:$row[smin] $row[am_pm]");
			$status=html($row['status']);
			$rank=html($row['rank']);
			$new_appointment_date=html($row['new_appointment_date']);
			$smin=html($row['smin']);
			$surgery_name=html($row['surgery_name']);
			$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'status'=>"$status", 	'rank'=>"$rank" , 'registered'=>'yes',
				'new_appointment_date'=>"$new_appointment_date",'smin'=>"$smin",'surgery_name'=>"$surgery_name", 'added_by'=>"$added_by");
		}
	}
	//get appointments for un-registerd patients
	if(!$exit_flag){
		if(!$skip_unregistered){
			$sql="select unregistered_patient_appointments.appointment_date,  unregistered_patient_appointments.treatment, unregistered_patient_appointments.shour, 
					unregistered_patient_appointments.smin, unregistered_patient_appointments.rank, unregistered_patient_appointments.status,
					unregistered_patient_appointments.am_pm,
				users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
				concat(unregistered_patients.first_name,' ',unregistered_patients.middle_name,' ',unregistered_patients.last_name) as names, unregistered_patients.phone ,surgery_names.surgery_name,
				e.appointment_date as new_appointment_date, unregistered_patient_appointments.smin,unregistered_patient_appointments.added_by
			from unregistered_patient_appointments join users on unregistered_patient_appointments.doc_id=users.id
			join unregistered_patients on unregistered_patient_appointments.pid=unregistered_patients.id $unregistered_criteria
			left join surgery_names on unregistered_patient_appointments.surgical_unit=surgery_names.surgery_id
			left join unregistered_patient_appointments as e on e.id=unregistered_patient_appointments.new_appointment_id
			$date_criteria_unregistered";
			$error="Unable to get unregisterd patients";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){
				//get person who added appointment
				$added_by='';
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select first_name, middle_name, last_name from users where id=:added_by";
				$error2="Unable to get user name";
				$placeholders2['added_by']=$row['added_by'];
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
				foreach($s2 as $row2){$added_by = ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));}	
			
				$date=html($row['appointment_date']);
				$doctor=html("$row[docf] $row[docm] $row[docl]");
				$patient=html("$row[names]");
				$phone=html($row['phone']);
				$treatment=html($row['treatment']);
				$time=html("$row[shour]:$row[smin] $row[am_pm]");
				$status=html($row['status']);
				$rank=html($row['rank']);
				$new_appointment_date=html($row['new_appointment_date']);
				$smin=html($row['smin']);
				$surgery_name=html($row['surgery_name']);
				$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
					'time'=>"$time", 'status'=>"$status", 'smin'=>"$smin",	'rank'=>"$rank" , 'registered'=>'NO',
					'new_appointment_date'=>"$new_appointment_date",'surgery_name'=>"$surgery_name", 'added_by'=>"$added_by");
			}
		}
	}
	
	if(!$exit_flag ){
		if(count($appointment_array) > 0){
			foreach ($appointment_array as $key => $row) {
					$rank1[$key]  = $row['rank'];
					$smin1[$key]  = $row['smin'];
					$date1[$key]  = $row['date'];
			}
			// Sort the data with when_added
			array_multisort($date1, SORT_ASC, $rank1, SORT_ASC,$smin1, SORT_ASC, $appointment_array);
				
			echo "<div class='grid-15 row_highlight  suffix-85'>Unregistered Patient</div>
				<div class='grid-15 auto_appoint_higlight_background  suffix-85'>Auto Appointment</div>";
			echo "<table class='normal_table'><caption>$caption</caption><thead>
			<tr><th class=apr_count></th><th class=apr_date>DATE</th><th class=apr_doc>DOCTOR</th><th class=apr_surgery>DENTAL UNIT</th>
			<th class=apr_pt>PATIENT</th><th class=apr_phone>PHONE NO.</th>
			<th class=apr_time>TIME</th><th class=apr_status>STATUS</th><th class=apr_treatment>NEW DATE</th><th class=apr_added_by>ADDED BY</th></tr></thead><tbody>";
			$count=0;
			foreach($appointment_array as $row){
				$count++;
				$bgcolor='';
				$task='';
				if($row['treatment']!=''){$task="<br>$row[treatment]";}
				if($row['registered']=='NO'){$bgcolor='row_highlight';}
				if($row['treatment']=='6 month auto-appointment'){$bgcolor='auto_appoint_higlight_background';}
				echo "<tr class=$bgcolor ><td>$count</td><td>$row[date] </td><td>$row[doctor]</td><td>$row[surgery_name]</td><td>$row[patient]$task</td><td>$row[phone]</td>
				<td>$row[time]</td><td>$row[status]</td><td>$row[new_appointment_date]</td><td>$row[added_by]</td></tr>";
			}
			echo "</tbody></table>";
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no appointments for the selected search criteria</label></div><br>";			
		}
	}
}
if($exit_flag){echo "<div class=error_response>$message</div><br>";}
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_apr1'] = "$token";  ?>
		<input type="hidden" name="token_apr1"  value="<?php echo $_SESSION['token_apr1']; ?>" />
		<div class='grid-15'><label for="user" class="label">Generate report by</label></div>
		<div class='grid-10 '><select name=search_criteria_apr class=search_criteria_apr >
					<option></option>
					<option value=date_range>Date</option>
					<option value=patient>Patient</option>
				</select>
		</div>		
		<div class=clear></div><br>
		<!-- by date range-->
		<div class='grid-100 apr_date_range no_padding'>
			<div class='grid-25'><label for="user" class="label">View appointments between this date </label></div>
			<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
			<div class='grid-10'><label for="user" class="label">And this date</label></div>
			<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
			<div class='grid-10'><input type=submit  value=Submit /></div>
		</div>
		<!-- by individual -->
		<div class='grid-100 apr_individual no_padding'>
			<div class='grid-15'><label for="user" class="label">Search in</label></div>
			<div class='grid-20 '><select name=patient_type_apr class=patient_type_apr >
					<option></option>
					<option value=registered>Registered Patients</option>
					<option value=unregistered>Unregistered Patients</option>
				</select>
			</div>		
			<div class=clear></div><br>
			
			<!-- registered patients -->
			<div class='grid-100 registered_apr no_padding'>
				<div class='grid-15'><label for="user" class="label">Search by</label></div>
				<div class='grid-15 '><select name=search_by_registered />
						<option></option>
						<option value=patient_number>Patient Number</option>
						<option value=first_name>First Name</option>
						<option value=middle_name>Middle Name</option>
						<option value=last_name>Last Name</option>
					</select>
				</div>
				<div class='grid-10'><input type=text  name=registered_search /></div>
				<div class='grid-10'><input type=submit  value=Submit /></div>
			</div>
			
			<!-- unregistered patients -->
			<div class='grid-100 unregistered_apr no_padding'>
				<div class='grid-15'><label for="user" class="label">Search by</label></div>
				<div class='grid-10 '><select name=search_by_unregistered />
						<option></option>
						<option value=first_name>First Name</option>
						<option value=middle_name>Middle Name</option>
						<option value=last_name>Last Name</option>
					</select>
				</div>
				<div class='grid-10'><input type=text  name=unregistered_search /></div>
				<div class='grid-10'><input type=submit  value=Submit /></div>
			</div>			
		</div>
		
	</form>

</div>