<?php
	include_once  'magicquotes.inc.php'; 
	include_once   'db.inc.php'; 
	include_once   'helpers.inc.php'; 
	include_once   'access.inc.php'; 
	//include_once    '../dental_includes/phpmailer/class.phpmailer.php';
	include_once   'phpmailer/class.phpmailer.php';
	$mail = new PHPMailer_mine();
	$encrypt = new Encryption();
	date_default_timezone_set('Africa/Nairobi');
	
	//this will send email for appointments for a doc that has admin rights
	function get_admin_appointments($pdo, $user_id, $caption,$for_admin){
		 
		//get doc appoints
			//get appointments for registerd patients
			$appointment_array=array();
			$output_string='';
			$sql=$error=$s='';$placeholders=array();
			$sql="select registered_patient_appointments.appointment_date,  registered_patient_appointments.treatment, registered_patient_appointments.shour, 
					registered_patient_appointments.smin, registered_patient_appointments.rank, registered_patient_appointments.status,
					registered_patient_appointments.am_pm,	patient_details_a.first_name as ptf, patient_details_a.middle_name as ptm, 
				patient_details_a.last_name as ptl,patient_details_a.mobile_phone ,surgery_names.surgery_name
				from registered_patient_appointments join  patient_details_a on registered_patient_appointments.pid=patient_details_a.pid 
			left join surgery_names on registered_patient_appointments.surgical_unit=surgery_names.surgery_id
				where registered_patient_appointments.doc_id=:user_id and  registered_patient_appointments.appointment_date =:today
				and registered_patient_appointments.status='NOT SEEN'  ";
			$placeholders[':user_id']=$user_id;
			$placeholders[':today']=date('Y-m-d');
			$error="Unable to get registerd patients appointments";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$count=0;
			$body='';
			foreach($s as $row){
				$date=html($row['appointment_date']);
				$patient=html("$row[ptf] $row[ptm] $row[ptl]");
				$phone=html($row['mobile_phone']);
				$treatment=html($row['treatment']);
				$time=html("$row[shour]:$row[smin] $row[am_pm]");
				$surgery_name=html($row['surgery_name']);
				$rank=html($row['rank']);
				$smin=html($row['smin']);
				$appointment_array[]=array('date'=>"$date",  'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'rank'=>"$rank" , 'smin'=>"$smin",'surgery_name'=>"$surgery_name",'registered'=>'YES');
				//$body = "$body <tr><td>$count</td><td>$time</td><td>$patient</td><td>$treatment</td><td>$phone</td><td>$surgery_name</td></tr>";
				
			}
			
			//get appointments for unregisterd patients
			$sql=$error=$s='';$placeholders=array();
			$sql="select unregistered_patient_appointments.appointment_date,  unregistered_patient_appointments.treatment, unregistered_patient_appointments.shour, 
					unregistered_patient_appointments.smin, unregistered_patient_appointments.rank, unregistered_patient_appointments.status,
					unregistered_patient_appointments.am_pm,	unregistered_patients.first_name as ptf, unregistered_patients.middle_name as ptm, 
				unregistered_patients.last_name as ptl,unregistered_patients.phone as mobile_phone,surgery_names.surgery_name
				from unregistered_patient_appointments join  unregistered_patients on unregistered_patient_appointments.pid=unregistered_patients.id 
			left join surgery_names on unregistered_patient_appointments.surgical_unit=surgery_names.surgery_id
				where unregistered_patient_appointments.doc_id=:user_id and  unregistered_patient_appointments.appointment_date =:today
				and unregistered_patient_appointments.status='NOT SEEN' ";
			$placeholders[':user_id']=$user_id;
			$placeholders[':today']=date('Y-m-d');
			$error="Unable to get registerd patients appointments";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){
				$date=html($row['appointment_date']);
				$patient=html("$row[ptf] $row[ptm] $row[ptl]");
				$phone=html($row['mobile_phone']);
				$treatment=html($row['treatment']);
				$time=html("$row[shour]:$row[smin] $row[am_pm]");
				$surgery_name=html($row['surgery_name']);
				$rank=html($row['rank']);
				$smin=html($row['smin']);
				$appointment_array[]=array('date'=>"$date",  'patient'=>"$patient", 'phone'=>"$phone", 'treatment'=>"$treatment", 
				'time'=>"$time", 'rank'=>"$rank" , 'smin'=>"$smin",'surgery_name'=>"$surgery_name",'registered'=>'NO');
				//$body = "$body <tr><td>$count</td><td>$time</td><td>$patient</td><td>$treatment</td><td>$phone</td><td>$surgery_name</td></tr>";
				
			}
			 
			//sort array and prepare output for this doctor
			if(count($appointment_array) > 0){
				foreach ($appointment_array as $key => $row) {
						$rank1[$key]  = $row['rank'];
						$smin1[$key]  = $row['smin'];
						$date1[$key]  = $row['date'];
				}
				// Sort the data with when_added
				array_multisort($date1, SORT_ASC, $rank1, SORT_ASC,$smin1, SORT_ASC, $appointment_array);
					
				$caption=html("$caption ");
				$output_string="<table style='width: 100%; table-layout: fixed;'>
					<caption style='background: #D8D8D8;color: #1f232c; font-weight: bold; padding: 5px 2px;text-align: left;font-style: normal;'>$caption</caption><thead>
				<tr style='background: #121923;color: #ffffff; font-weight: bold; text-align: left;'><th style='width: 5%'></th><th style='width: 10%'>TIME</th><th style='width: 30%'>PATIENT</th><th style='width: 10% '>PHONE</th>
				<th style='width: 25%'>TREATMENT</th><th style='width: 20%'>SURGERY NAME x</th></tr></thead><tbody>";
				$count=0;
				foreach($appointment_array as $row){
					if($for_admin == ''){$row['phone']='';}
					$count++;
					$bgcolor='';
					if(($count % 2) > 0){$bgcolor=' background: #0D141C; ';}
					else{$bgcolor=' background: #121923; ';}
					if($row['registered']=='NO'){$bgcolor='row_highlight';}
					$output_string = "$output_string <tr style='$bgcolor color: #B0B3B6;text-align: left;'><td>$count</td><td>$row[time]</td><td>$row[patient]</td><td><tag>$row[phone]</tag></td><td>$row[treatment]</td>
							<td>$row[surgery_name]</td></tr>";
					
				}
				$output_string = "$output_string</tbody></table>";
				
			}
		//	if($output_string==''){echo "<br>empty";}
			//else {echo "<br>not empty";}
			
			return $output_string;
	
	}
	
		
		
	//this will get appointment time in the proposed date
	function get_appointment_time($pdo, $proposed_date, $pid, $doc_id, $month_interval){
		$appointments=array();
		//get appointments on that day first for registerd folks
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.last_name,a.middle_name, a.first_name,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
		from patient_details_a a, users b, registered_patient_appointments c where c.pid=a.pid and c.doc_id=b.id and c.appointment_date=:appointment_date";
		$placeholders[':appointment_date']=$proposed_date;
		$error="Unable to get registerd appointments";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$patient_name=html("$row[2] $row[1] $row[0]");
			$doctor_name=html("$row[3] $row[4] $row[5]");
			$treatment=html("$row[treatment]");
			$hour=html("$row[shour]");
			$min=html("$row[smin]");
			$rank=html("$row[rank]");
			$surgery=html("$row[surgical_unit]");
			$appointments[]=array('registered'=>'yes','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery");
		}
	
		//get appointments on that day first for unregisterd folks
		$sql=$error=$s='';$placeholders=array();
		$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as names,b.first_name,b.middle_name,b.last_name,c.treatment,c.shour,c.smin,c.rank,c.surgical_unit,c.am_pm
		from unregistered_patients a, users b, unregistered_patient_appointments c where c.pid=a.id and c.doc_id=b.id and c.appointment_date=:appointment_date";
		$placeholders[':appointment_date']=$proposed_date;
		$error="Unable to get un-registerd appointments";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$patient_name=html("$row[0]");
			$doctor_name=html("$row[1] $row[2] $row[3]");
			$treatment=html("$row[treatment]");
			$hour=html("$row[shour]");
			$min=html("$row[smin]");
			$rank=html("$row[rank]");
			$surgery=html("$row[surgical_unit]");
			$appointments[]=array('registered'=>'no','patient_name'=>"$patient_name", 'doctor_name'=>"$doctor_name",'hour'=>"$hour",'min'=>"$min",'rank'=>"$rank",'surgery'=>"$surgery");
		}
		
		//start by getting surgery names
		$sql=$error=$s='';$placeholders=array();
		$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
		$error="Unable to get surgery names";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$surgery_id_array[]=html("$row[surgery_id]");
			$surgery_name_array[]=html("$row[surgery_name]");
			$surgery_name=html("$row[surgery_name]");
			
		}
	
		//now get minute intervals
		$sql=$error=$s='';$placeholders=array();
		$sql="select minute_interval from appointment_minutes_interval";
		$error="Unable to get appointment interval";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		$minutes_interval_array='';
		foreach($s as $row){
			$minute_interval=html($row['minute_interval']);
			$intervals = 60 / $minute_interval;
		}	
		
		//get working hours for that day of the week
		$week_day=date("N", strtotime("$proposed_date"));
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select shour,rank from appointment_hours where work_day=:workday order by rank";
		$error2="Unable to get appointment hours";
		$placeholders2[':workday']=$week_day;
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		
		//now check if we have free time on proposed date
		$rank_array=array();
		$am_pm='';
		foreach($s2 as $row){
			$rank=html($row['rank']);
			$hour=html($row['shour']);
			if($rank < 12){$am_pm="AM";}
			else{$am_pm="PM";}
			$i=1;
			while($i <= $intervals){
				if($i==1){$minute="00";$minute_compare=0;}
				//now loop through the surgeries
				$n2=count($surgery_id_array);
				$i2=0;
				while($i2 < $n2){
					$appointment_exists =false;
					//check if appointment is in this surgery ,hour(rank) and minute
					foreach($appointments as $current_appointment){
						if($surgery_id_array[$i2] == $current_appointment['surgery'] and $current_appointment['rank'] == $rank and 
							$current_appointment['min'] == $minute_compare){
							$appointment_exists =true;
						}
					}
					if(!$appointment_exists){
						//insert new appointment
						$sql21=$error21=$s21='';$placeholders21=array();
						$sql21="insert into registered_patient_appointments set when_added=curdate(), doc_id=:doc_id, pid=:pid, treatment=:treatment,
							appointment_date=:appointment_date, shour=:shour, smin=:smin, rank=:rank, am_pm=:am_pm, surgical_unit=:surgical_unit";
						$error21="Unable to insert auto appointment";
						$placeholders21[':doc_id']=$doc_id;
						$placeholders21[':pid']=$pid;
						$placeholders21[':treatment']="$month_interval month auto-appointment";
						$placeholders21[':appointment_date']="$proposed_date";
						$placeholders21[':shour']=$hour;
						$placeholders21[':smin']=$minute;
						$placeholders21[':rank']=$rank;
						$placeholders21[':am_pm']=$am_pm;
						$placeholders21[':surgical_unit']=$surgery_id_array[$i2];
						$s21 = 	insert_sql($sql21, $placeholders21, $error21, $pdo);
						return true;
					}
					$i2++;
				}
				
				$minute=$minute + $minute_interval ;
				$minute_compare = $minute;
				if ($minute < 10){$minute="0$minute";}
				$i++;
			}
		}
		//if we reach here it means that all slots for the proposed day are taken so we return false and look for another date
		return false;
		
	}
	//this willc heck if day is a public holiday
	function check_working_day($pdo, $proposed_date, $pid, $doc_id, $month_interval){
		$data=explode('-',"$proposed_date");
		$month=$data[1];
		$day=$data[2]; 
		
		//check if this date is a public holiday
		$sql3=$error3=$s3='';$placeholders3=array();
		$sql3="select month_day from public_holidays where holiday_month=:month and month_day=:day";
		$placeholders3[':month']=$month;
		$placeholders3[':day']=$day;
		$error3="Unable to select public holidays";
		$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
		if($s3->rowCount() > 0){//this means that the proposed day is a public holiday so we add one day and check it
			$date=date_create("$proposed_date");
			date_add($date,date_interval_create_from_date_string("1 days"));
			$proposed_date =  date_format($date,"Y-m-d");
			check_working_day($pdo, $proposed_date, $pid, $doc_id,$month_interval);
			exit;
		}
		
		//check if proposed date is a working day
		$week_day=date("N", strtotime("$proposed_date"));
		//check if week day has any appointment
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select shour,rank from appointment_hours where work_day=:workday order by rank";
		$error2="Unable to get appointment hours";
		$placeholders2[':workday']=$week_day;
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		if($s2->rowCount() == 0){//this means that the proposed day is a non-working day so we add one and check again
			$date=date_create("$proposed_date");
			date_add($date,date_interval_create_from_date_string("1 days"));
			$proposed_date =  date_format($date,"Y-m-d");
			check_working_day($pdo, $proposed_date, $pid, $doc_id, $month_interval);
			exit;
		}	
			
		//get availlable time on proposed date
		$result=get_appointment_time($pdo, $proposed_date, $pid, $doc_id,$month_interval);
		if(!$result){//this means there are no free appointments so we go to next day
			$date=date_create("$proposed_date");
			date_add($date,date_interval_create_from_date_string("1 days"));
			$proposed_date =  date_format($date,"Y-m-d");
			check_working_day($pdo, $proposed_date, $pid, $doc_id, $month_interval);
			exit;
		}
		
		
	}
	
	//get number of months for auto appointment
	$month_interval='';
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select month_interval from auto_appoint_interval";
	$error2="Unable to get month interval for auto appointments";
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	foreach($s2 as $row){$month_interval=html($row['month_interval']);}
	if($month_interval==''){exit;}
	
	//for patients with no appointment start processiong
	$date=date_create(date('Y-m-d'));
	date_add($date,date_interval_create_from_date_string("$month_interval months"));
	$proposed_date =  date_format($date,"Y-m-d");
		
	//get guys who were seen yesterday and have no next appointment date
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.surgery_id, a.pid from patient_allocations a where date(a.discharge_time)= date_sub(curdate(), interval 1 day)";
	$error="Unable to get patients seen yesterday";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		//check if the patient has an appointment
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select pid from registered_patient_appointments where pid=:pid and appointment_date >=curdate()";
		$error2="Unable to check if seen patient has a future appointment";
		$placeholders2[':pid']=$row['pid'];
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		if($s2->rowCount() > 0) {//this will stop processiong for guys with future appointments
			continue; 
			
		}
		
		
		
		
		//get last doctor to see this pt from
		$doc_id='';
		$sql2=$error2=$s2='';$placeholders2=array();
		$sql2="select doc_id,max(id) from treatment_procedure_notes where pid=:pid group by pid";
		$error2="Unable to get last doctor to see patient in auto appointment";
		$placeholders2[':pid']=$row['pid'];
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		foreach($s2 as $row2){
			$doc_id=$row2['doc_id'];
		}
		
		//if there is no entry for any treatment procedure done then check the doctor who made the last treatment pln for the pt
		if($s2->rowCount() == 0){
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select created_by,max(treatment_procedure_id) from tplan_procedure where pid=:pid group by pid";
			$error2="Unable to get last doctor to see patient in auto appointment";
			$placeholders2[':pid']=$row['pid'];
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			foreach($s2 as $row2){
				//$doc_id=$row2['doc_id'];
				$doc_id=$row2['created_by'];
			}
		}
		
		if($doc_id!=''){check_working_day($pdo, $proposed_date, $row['pid'], $doc_id, $month_interval);}
		
	}
	
	//truncate surgary logins
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="truncate table surgery_logins";
	$error2="Unable to get empty surgery logins";
	$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
	
	//make remaining patients seen
	//get guys who are still undischarged
	$sql2=$error2=$s2='';$placeholders2=array();
	$sql2="select id,time_start_treatment,treatment_finish from patient_allocations where discharge_time='0000-00-00 00:00:00'";
	$error2="Unable to get undischarged patients";
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	foreach($s2 as $row2){
		$now=date('Y-m-d H:i:s');
		$sql3=$error3=$s3='';$placeholders3=array();
		$time_start_treatment=html($row2['time_start_treatment']);
		$treatment_finish=html($row2['treatment_finish']);
		if($row2['time_start_treatment'] == '0000-00-00 00:00:00'){$time_start_treatment="$now";}
		if($row2['treatment_finish'] == '0000-00-00 00:00:00'){$treatment_finish="$now";}
		$discharge_time="$now";
		$id=html($row2['id']);
		$sql3="update patient_allocations set 
			time_start_treatment=:time_start_treatment,
			treatment_finish=:treatment_finish,
			discharge_time=:discharge_time,
			treatment_status=3
			where id=:id
			";
		$placeholders3[':time_start_treatment']="$time_start_treatment";
		$placeholders3[':treatment_finish']="$treatment_finish";
		$placeholders3[':discharge_time']="$discharge_time";		
		$placeholders3[':id']=$id;		
		$error3="Unable to get undischarged patients";
		$s3 = 	insert_sql($sql3, $placeholders3, $error3, $pdo);
	}

	

	$_SESSION['id']=''; 
	
?>
