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
if( isset($_POST['token_da1']) and isset($_SESSION['token_da1']) and $_SESSION['token_da1']==$_POST['token_da1']){
	$_SESSION['token_da1']=$date_criteria_registered=$date_criteria_unregistered=$registered_criteria=$unregistered_criteria='';
	$skip_registered=$skip_unregistered=$exit_flag=false;
	$sql=$error=$s='';$placeholders=array();
		//check dates
		if(!isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' and $_POST['to_date']==''){
			echo "<div class='error_response'>Please ensure that the date range is correctly specified</div>";
			$exit_flag=true;
		}	
	
	//date range
	if(!$exit_flag ){
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


	//get appointments for registerd patients
	if(!$exit_flag and !$skip_registered ){
		$appointment_array=array();
		$sql="select registered_patient_appointments.appointment_date,  registered_patient_appointments.treatment, registered_patient_appointments.shour, 
				registered_patient_appointments.smin, registered_patient_appointments.rank, registered_patient_appointments.status,
				registered_patient_appointments.am_pm,
			users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
			patient_details_a.first_name as ptf, patient_details_a.middle_name as ptm, 
			patient_details_a.last_name as ptl,patient_details_a.mobile_phone ,surgery_names.surgery_name,
			e.appointment_date as new_appointment_date ,registered_patient_appointments.smin ,registered_patient_appointments.id
		from registered_patient_appointments join users on registered_patient_appointments.doc_id=users.id
		join patient_details_a on registered_patient_appointments.pid=patient_details_a.pid  
		left join surgery_names on registered_patient_appointments.surgical_unit=surgery_names.surgery_id
		left join registered_patient_appointments as e on e.id=registered_patient_appointments.new_appointment_id
		$date_criteria_registered";
		$error="Unable to get registerd patients";
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
			$new_appointment_date=html($row['new_appointment_date']);
			$smin=html($row['smin']);
			$surgery_name=html($row['surgery_name']);
			$val=$encrypt->encrypt("registered#$row[id]");
			$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'status'=>"$status", 	'rank'=>"$rank" , 'registered'=>'yes','val'=>"$val",
				'new_appointment_date'=>"$new_appointment_date",'smin'=>"$smin",'surgery_name'=>"$surgery_name");
		}
	}
	//get appointments for un-registerd patients
	if(!$exit_flag){
		if(!$skip_unregistered){
			$sql="select unregistered_patient_appointments.appointment_date,  unregistered_patient_appointments.treatment, unregistered_patient_appointments.shour, 
					unregistered_patient_appointments.smin, unregistered_patient_appointments.rank, unregistered_patient_appointments.status,
					unregistered_patient_appointments.am_pm, unregistered_patient_appointments.pid,
				users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
				concat(unregistered_patients.first_name,' ',unregistered_patients.middle_name,' ',unregistered_patients.last_name) as names, unregistered_patients.phone ,surgery_names.surgery_name,
				e.appointment_date as new_appointment_date, unregistered_patient_appointments.smin, unregistered_patient_appointments.id
			from unregistered_patient_appointments join users on unregistered_patient_appointments.doc_id=users.id
			join unregistered_patients on unregistered_patient_appointments.pid=unregistered_patients.id 
			left join surgery_names on unregistered_patient_appointments.surgical_unit=surgery_names.surgery_id
			left join unregistered_patient_appointments as e on e.id=unregistered_patient_appointments.new_appointment_id
			$date_criteria_unregistered";
			$error="Unable to get unregisterd patients";
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
				$new_appointment_date=html($row['new_appointment_date']);
				$smin=html($row['smin']);
				$surgery_name=html($row['surgery_name']);
				$val=$encrypt->encrypt("unregistered#$row[id]#$row[pid]");
				$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
					'time'=>"$time", 'status'=>"$status", 'smin'=>"$smin",	'rank'=>"$rank" , 'registered'=>'NO','val'=>"$val",
					'new_appointment_date'=>"$new_appointment_date",'surgery_name'=>"$surgery_name");
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
			?>	
			<form class='' action="" method="POST" enctype="" name="" id="">
				<?php $token = form_token(); $_SESSION['token_da2'] = "$token";  ?>
				<input type="hidden" name="token_da2"  value="<?php echo $_SESSION['token_da2']; ?>" />
				<?php
			echo "<table class='normal_table'><caption>$caption</caption><thead>
			<tr><th class=apr_count></th><th class=apr_date>DATE</th><th class=apr_doc>DOCTOR</th><th class=apr_surgery>DENTAL UNIT</th>
			<th class=apr_pt>PATIENT</th><th class=apr_phone>PHONE NO.</th>
			<th class=apr_time>TIME</th><th class=apr_treatment>RE-APPOINTED DATE</th><th class=apr_status>DELELTE</th></tr></thead><tbody>";
			$count=0;
			foreach($appointment_array as $row){
				$count++;
				$bgcolor='';
				if($row['registered']=='NO'){$bgcolor='row_highlight';}
				echo "<tr class=$bgcolor ><td>$count</td><td>$row[date]</td><td>$row[doctor]</td><td>$row[surgery_name]</td><td>$row[patient]</td><td>$row[phone]</td>
				<td>$row[time]</td><td>$row[new_appointment_date]</td><td><input type=checkbox name=del_appointment[] value=$row[val] /></td></tr>";
			}
			echo "</tbody></table>
			<div class='grid-100'><input class=put_right type=submit  value=Submit /></div>
			</form>";
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no appointments for the selected search criteria</label></div><br>";			
		}
	}
}
//[erform actual deletion
if(isset($_SESSION['token_da2']) and isset($_POST['token_da2']) and $_POST['token_da2']==$_SESSION['token_da2']){
	$_SESSION['token_da2']='';
	$i=$n=0;
	if(isset($_POST['del_appointment'])){
		$appointment_id=$_POST['del_appointment'];
		$n=count($appointment_id);
		try{
				$pdo->beginTransaction();
					while($i < $n){
						$del_appointment_id=$encrypt->decrypt("$appointment_id[$i]");
						$data=explode('#',"$del_appointment_id");
						//registered patients
						if($data[0] == 'registered'){
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from registered_patient_appointments where id=:id";
							$error="Unable to get delete regsitered appointments";
							$placeholders[':id']=$data[1];
							$s = insert_sql($sql, $placeholders, $error, $pdo);	
						}
						//unregistered patients
						if($data[0] == 'unregistered'){
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from unregistered_patient_appointments where id=:id";
							$error="Unable to get delete unregsitered appointments";
							$placeholders[':id']=$data[1];
							$s = insert_sql($sql, $placeholders, $error, $pdo);	
							
							//delete the unregistered patient as well
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from unregistered_patients where id=:id";
							$error="Unable to get delete unregsitered patient after appointment";
							$placeholders[':id']=$data[2];
							$s = insert_sql($sql, $placeholders, $error, $pdo);								
						}						
						$i++;
					}
				
					$tx_result=$pdo->commit();
					if($tx_result ){echo "<div class='success_response'>Appointments deleted</div>";}
				
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		
		}
	}
	else{echo "<div class='error_response'>No appointment was selected for deletion</div>";}
		
		
}
if($exit_flag){echo "<div class=error_response>$message</div><br>";}
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_da1'] = "$token";  ?>
		<input type="hidden" name="token_da1"  value="<?php echo $_SESSION['token_da1']; ?>" />

		<!-- by date range-->
			<div class='grid-25'><label for="user" class="label">Select appointments between this date </label></div>
			<div class='grid-10 '><input type=text name=from_date class=date_picker_no_past /></div>
			<div class='grid-10'><label for="user" class="label">And this date</label></div>
			<div class='grid-10 '><input type=text name=to_date class=date_picker_no_past /></div>
			<div class='grid-10'><input type=submit  value=Submit /></div>
		

		
	</form>

</div>