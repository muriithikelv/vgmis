<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,80)){exit;}
echo "<div class='grid_12 page_heading'>EDIT APPOINTMENTS</div>"; ?>
<div class="grid-100 margin_top ">
<div class='feedback hide_element'></div>
<?php   
//show appointments for editing 
if( isset($_POST['token_eap1']) and isset($_SESSION['token_eap1']) and $_SESSION['token_eap1']==$_POST['token_eap1']){
	$_SESSION['token_eap1']='';
	$current_date=date('Y-m-d');
	//echo "date is $current_date  echo 'date_default_timezone_set: ' ". date_default_timezone_get();
	//if date is in past
	/*if($_POST['from_date'] < "$current_date"){
		echo "<div class='grid-100 error_response'>Appointments in the past cannot be edited. Only today's and future appointments are editable.</div>";
	}*/
	//else date is today or in the past
	//if($_POST['from_date'] >= "$current_date"){
	if(true){
		//get appointments for registerd patients
		$appointment_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select registered_patient_appointments.appointment_date,  registered_patient_appointments.treatment, registered_patient_appointments.shour, 
				registered_patient_appointments.smin, registered_patient_appointments.rank, registered_patient_appointments.status,
				registered_patient_appointments.am_pm,
			users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
			patient_details_a.first_name as ptf, patient_details_a.middle_name as ptm, patient_details_a.last_name as ptl,patient_details_a.mobile_phone ,
			surgery_names.surgery_name, registered_patient_appointments.new_appointment_id, registered_patient_appointments.id,
			registered_patient_appointments.pid,e.appointment_date as new_appointment_date
		from registered_patient_appointments join users on registered_patient_appointments.doc_id=users.id
		join patient_details_a on registered_patient_appointments.pid=patient_details_a.pid
		left join surgery_names on registered_patient_appointments.surgical_unit=surgery_names.surgery_id
		left join registered_patient_appointments as e on e.id=registered_patient_appointments.new_appointment_id
		where registered_patient_appointments.appointment_date >=:from_date and registered_patient_appointments.appointment_date <=:to_date";
		$error="Unable to get registerd patients";
		$placeholders[':from_date']=strtoupper($_POST['from_date']);
		$placeholders[':to_date']=strtoupper($_POST['to_date']);
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$date=html($row['appointment_date']);
			$doctor=html("$row[docf] $row[docm] $row[docl]");
			$patient=html("$row[ptf] $row[ptm] $row[ptl]");
			$phone=html($row['mobile_phone']);
			$treatment=html($row['treatment']);
			$time=html("$row[shour]:$row[smin] $row[am_pm]");
			$status=html($row['status']);
			$rank=html($row['rank']);
			$new_appointment_id=html($row['new_appointment_id']);
			$appointment_id=html($row['id']);
			$smin=html($row['smin']);
			$pid=html($row['pid']);
			$new_appointment_date=html($row['new_appointment_date']);
			$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'status'=>"$status", 	'rank'=>"$rank" , 'registered'=>'yes', 'new_appointment_id'=>"new_appointment_id",
				'appointment_id'=>"$appointment_id",'smin'=>"$smin",'pid'=>"$pid",'new_appointment_date'=>"$new_appointment_date");
		}
		
		//get appointments for un-registerd patients
		$sql=$error=$s='';$placeholders=array();
		$sql="select unregistered_patient_appointments.appointment_date,  unregistered_patient_appointments.treatment, unregistered_patient_appointments.shour, 
				unregistered_patient_appointments.smin, unregistered_patient_appointments.rank, unregistered_patient_appointments.status,
				unregistered_patient_appointments.am_pm,
			users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
			concat(unregistered_patients.first_name,' ',unregistered_patients.middle_name,' ',unregistered_patients.last_name) as names, unregistered_patients.phone ,surgery_names.surgery_name, unregistered_patient_appointments.new_appointment_id,
			unregistered_patient_appointments.id,unregistered_patient_appointments.pid,e.appointment_date as new_appointment_date
		from unregistered_patient_appointments join users on unregistered_patient_appointments.doc_id=users.id
		join unregistered_patients on unregistered_patient_appointments.pid=unregistered_patients.id
		left join surgery_names on unregistered_patient_appointments.surgical_unit=surgery_names.surgery_id
		left join unregistered_patient_appointments as e on e.id=unregistered_patient_appointments.new_appointment_id
		where unregistered_patient_appointments.appointment_date >=:from_date and unregistered_patient_appointments.appointment_date <=:to_date";
		$error="Unable to get unregisterd patients";
		$placeholders[':from_date']=strtoupper($_POST['from_date']);
		$placeholders[':to_date']=strtoupper($_POST['to_date']);
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$date=html($row['appointment_date']);
			$doctor=html("$row[docf] $row[docm] $row[docl]");
			$patient=html("$row[names]");
			$phone=html($row['phone']);
			$treatment=html($row['treatment']);
			$time=html("$row[shour]:$row[smin] $row[am_pm]");
			$status=html($row['status']);
			$rank=html($row['rank']);
			$new_appointment_id=html($row['new_appointment_id']);
			$appointment_id=html($row['id']);
			$smin=html($row['smin']);
			$pid=html($row['pid']);
			$new_appointment_date=html($row['new_appointment_date']);
			$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'status'=>"$status", 	'rank'=>"$rank" , 'registered'=>'NO', 'new_appointment_id'=>"new_appointment_id",
				'appointment_id'=>"$appointment_id",'smin'=>"$smin",'pid'=>"$pid",'new_appointment_date'=>"$new_appointment_date");
		}
		//print_r($appointment_array);

		//echo "ddddddddddddddddd<br>";
		//print_r($appointment_array);
	//echo "count is ".count($appointment_array);exit;	
		if(count($appointment_array) > 0){
			
			foreach ($appointment_array as $key => $row) {
				$rank1[$key]  = $row['rank'];
				$smin1[$key]  = $row['smin'];
			}

			// Sort the data with when_added
			array_multisort($rank1, SORT_ASC,$smin1, SORT_ASC, $appointment_array);
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']); ?>
			<form action="" method="POST"  name="" id="" class="patient_form2">
				<?php $token = form_token(); $_SESSION['token_eap2'] = "$token"; ?>
				<input type="hidden" name="token_eap2"  value="<?php echo $_SESSION['token_eap2']; ?>" />	
		
			<?php
			echo "<table class='normal_table'><caption>Appointments booked between $from_date and $to_date</caption><thead>
			<tr><th class=apr_count></th><th class=apr_date>DATE</th><th class=apr_doc>DOCTOR</th><th class=apr_pt>PATIENT</th><th class=apr_phone>PHONE NO.</th>
			<th class=apr_time>TIME</th><th class=apr_status>STATUS</th><th class=apr_treatment>RE-APPOINTED DATE</th></tr></thead><tbody>";
			$count=0;
			foreach($appointment_array as $row){
				$count++;
				$bgcolor='';
				$seen_val=$encrypt->encrypt("SEEN#$row[appointment_id]#$row[registered]#$row[pid]");
				$not_seen_val=$encrypt->encrypt("NOT SEEN#$row[appointment_id]#$row[registered]#$row[pid]");
				$re_appoint_val=$encrypt->encrypt("RE-APPOINTED#$row[appointment_id]#$row[registered]#$row[pid]");
				if($row['registered']=='NO'){$bgcolor='row_highlight';}
				echo "<tr class=$bgcolor ><td>$count</td><td>$row[date]</td><td>$row[doctor]</td><td>$row[patient]</td><td>$row[phone]</td>
				<td>$row[time]</td><td>";
					$seen=$not_seen=$re_appointed='';
					/*
					if($row['status']=='SEEN'){ echo "SEEN"; }//$seen= " selected ";}
					elseif($row['status']=='NOT SEEN'){ $not_seen= " selected ";}
					if($row['status']=='RE-APPOINTED'){ echo "RE-APPOINTED"; }//$re_appointed= " selected ";}
					echo "<select class=set_appointment_status name=status[]>
							<option value='$not_seen_val' $not_seen >NOT SEEN</option>
							<option value='$re_appoint_val'  $re_appointed >RE-APPOINTED</option>
							<option value='$seen_val' $seen >SEEN</option>
					</select>";
					*/
					if($row['status']=='SEEN'){ echo "SEEN"; }//$seen= " selected ";}
					elseif($row['status']=='RE-APPOINTED'){ echo "RE-APPOINTED"; }//$re_appointed= " selected ";}
					elseif($row['status']=='NOT SEEN'){ $not_seen= " selected ";
						echo "<select class=set_appointment_status name=status[]>
								<option value='$not_seen_val' $not_seen >NOT SEEN</option>
								<option value='$re_appoint_val'  $re_appointed >RE-APPOINTED</option>
								<option value='$seen_val' $seen >SEEN</option>
						</select>";
					}
					echo "</td><td class=re_appoint_td>$row[new_appointment_date]</td></tr>";
			}
			echo "</tbody></table>";
			echo "<div class='get_width grid-100'><input class='put_right' type=submit value=Submit /></div>"; ?>
			<div class='re_appoint_div'>	
				<div class='grid-25'><label for="" class="label">Select new appointment Date</label></div>
				<div class='grid-10'><input type=text id=appointment_date class='date_picker_no_past appointment_date_date2'/></div>
				<div class=clear></div><br>
				<div class='grid-100' id=appointment_divr1 ></div>
				<div class='grid-100' id=appointment_divr2 ></div>
			</div>	
			
			<?php
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no appointments for the selected date criteria</label></div><br>";			
		}
	}
		
}


?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_eap1'] = "$token";  ?>
		<input type="hidden" name="token_eap1"  value="<?php echo $_SESSION['token_eap1']; ?>" />
		<div class='grid-25'><label for="user" class="label">Edit appointments between this date </label></div>
		<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10'><label for="user" class="label">And this date</label></div>
		<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
		
		
		<div class=clear></div><br>	
		<div class='prefix-45 grid-10'><input type=submit  value=Submit /></div>
	</form>

</div>