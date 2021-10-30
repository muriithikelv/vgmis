<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,104)){exit;}
echo "<div class='grid_12 page_heading'>SURGERY REPORTS</div>";//check if this guy is a doctor
?>
<div class='grid-container completion_form'>
<?php
if(isset($_SESSION['token_sr1']) and isset($_POST['token_sr1']) and $_POST['token_sr1']==$_SESSION['token_sr1']){
	$_SESSION['token_sr1']='';
	$exit_flag=false;
	//check if date is set for range
		if(!$exit_flag and !isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' or $_POST['to_date']==''){
			echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";
			$exit_flag=true;
		}
		
	//check if serach by is set for 
		if(!$exit_flag and !isset($_POST['surgery_report'])  or $_POST['surgery_report']==''){
			echo "<div class='error_response'>Please specify the search criteria for the report</div>";
			$exit_flag=true;
		}
		
	//check if surgery unit is set  
		if(!$exit_flag and !isset($_POST['surgery_unit'])  or $_POST['surgery_unit']==''){
			echo "<div class='error_response'>Please specify the surgery unit for the report</div>";
			$exit_flag=true;
		}
		
		//get search type
		if(!$exit_flag){
			$report_type=$encrypt->decrypt("$_POST[surgery_report]");
		}
	//get patient waiting time
	if(!$exit_flag and $report_type==12){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
					date(b.time_allocated) as when_added,c.surgery_name, timediff(b.treatment_finish, b.time_allocated) as waiting_time,
					timestampdiff(minute, b.time_allocated ,b.treatment_finish) * points_per_min as points
				from patient_allocations as b join patient_details_a as a on  b.pid=a.pid and date(b.time_allocated) >=:from_date 
					and date(b.time_allocated) <=:to_date $suregry
				left join surgery_names as c on c.surgery_id=b.surgery_id 
				where date(b.treatment_finish) > '0000-00-00'  order by b.id
				";
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			foreach($s2 as $row2 ){
				$patient_name=html($row2['patient_name']);
				$patient_number=html($row2['patient_number']);
				$when_added=html($row2['when_added']);
				$points=number_format(html($row2['points']),2);
				$waiting_time=html($row2['waiting_time']);
				$surgery_unit=html($row2['surgery_name']);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("waiting time for patients allocation to $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("waiting time for patients between $from_date and $to_date");}
					echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=pwt_count></th>
					<th class=pwt_date>DATE</th><th class=pwt_name>PATIENT NAME</th><th class=pwt_pnum>PATIENT NUMBER</th>
					<th class=pwt_sname>SURGICAL UNIT</th><th class=pwt_time>WAITING TIME</th><th class=pwt_points>POINTS</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$i</td><td>$when_added</td><td><a class=' unstyled_link goto_pt_contact2' href=''  >$patient_name</a></td>
				<td><a class=' unstyled_link goto_tdone2' href=''  >$patient_number</a></td><td>$surgery_unit</td><td>$waiting_time</td>
				<td>$points</td></tr>";
			}
			echo "</tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";}
		exit;
	}
	//get patients who left
	elseif(!$exit_flag and $report_type==11){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
					b.time_allocated, b.discharge_time,c.surgery_name, timediff(b.discharge_time, b.time_allocated) as waiting_time,
					timestampdiff(minute, b.time_allocated ,b.discharge_time) * points_per_min as points,
					a.mobile_phone, a.biz_phone
				from patient_allocations as b join patient_details_a as a on  b.pid=a.pid and date(b.time_allocated) >=:from_date 
					and date(b.time_allocated) <=:to_date $suregry
				left join surgery_names as c on c.surgery_id=b.surgery_id 
				where b.patient_left=1  order by b.id
				";
				//where date(b.discharge_time) > '0000-00-00'  order by b.id
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			foreach($s2 as $row2 ){
				$patient_name=html($row2['patient_name']);
				$patient_number=html($row2['patient_number']);
				$time_in=html($row2['time_allocated']);
				$time_out=html($row2['discharge_time']);
				$points=number_format(html($row2['points']),2);
				$waiting_time=html($row2['waiting_time']);
				$surgery_unit=html($row2['surgery_name']);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("patients who left before treatment from $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("patients who left before treatment between $from_date and $to_date");}
					echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=pl_count></th>
					<th class=pl_name>PATIENT NAME</th><th class=pl_pnum>PATIENT NUMBER</th>
					<th class=pl_sname>SURGICAL UNIT</th><th class=pl_time>TIME ARRIVED</th><th class=pl_time>TIME LEFT</th>
					<th class=pl_wtime>WAITING TIME</th><th class=pl_points>POINTS</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$i</td><td>$patient_name</td><td>$patient_number</td><td>$surgery_unit</td>
				<td>$time_in</td><td>$time_out</td><td>$waiting_time</td><td>$points</td></tr>";
			}
			echo "</tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients who left before treatment for the selected search criteria</div>";}
		exit;
	}	
	//get number of patients seen
	elseif(!$exit_flag and $report_type==10){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select count(b.pid),c.surgery_name
					from patient_allocations as b  join surgery_names as c on c.surgery_id=b.surgery_id 
					and date(b.time_allocated) >=:from_date and date(b.time_allocated) <=:to_date $suregry
					where date(b.discharge_time) > '0000-00-00' and patient_left=0 group by b.surgery_id
				";
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			$total=0;
			foreach($s2 as $row2 ){
				$patients_seen=number_format(html($row2[0]));
				$surgery_unit=html($row2['surgery_name']);
				$total=$total + html($row2[0]);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("patients seen from $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("patients seen between $from_date and $to_date");}
					echo "<table class=half_width><caption>$caption</caption><thead><tr>
					<th class=ps_name>SURGICAL UNIT</th><th class=ps_seen>NUMBER SEEN</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$surgery_unit</td><td>$patients_seen</td></tr>";
			}
			echo "<tr class=total_background><td>TOTAL</td><td>".number_format($total)."</td></tr></tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";}
		exit;
	}	
	//get patient allocations
elseif(!$exit_flag and $report_type==17){
	$from_date=html("$_POST[from_date]");
	$to_date=html("$_POST[to_date]");
			
	$current_allocations=$allocation_array=$time_allocated_array=array();
	//get all allocations for registered 6,7,12
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.surgery_id, a.pid, sec_to_time(timestampdiff(minute, time_allocated , current_timestamp() ) * 60 ),
			concat(b.first_name,' ',b.middle_name,' ',b.last_name),patient_left, date(time_start_treatment), b.type, b.company_covered, time_allocated,  
			time_start_treatment,timediff(time_start_treatment, time_allocated) , timediff(now(), time_allocated), a.id, b.patient_number, a.discharge_time,
			a.pause_treatment, a.resume_treatment ,a.treatment_status, timediff(discharge_time, time_allocated),treatment_finish,
			timediff(treatment_finish, time_start_treatment),timediff(now(), time_start_treatment)
			from  patient_allocations a, patient_details_a b  where a.patient_type=1 and a.pid=b.pid  and 
			date(time_allocated)>=:from_date and date(time_allocated)<=:to_date order by a.id";
	$error="unable to get current allocations";
	$placeholders[':from_date']=$from_date;
	$placeholders[':to_date']=$to_date;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row)	{
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
			$sql3="select shour, smin,rank,am_pm ,first_name,middle_name, last_name from registered_patient_appointments a, users b where pid=:pid and 
			      appointment_date=curdate() and a.doc_id=b.id";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to get appointments in allocations";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			$appointment_time='';			
			foreach($s3 as $row3){
				$appointment_time="$row3[shour]:$row3[smin] $row3[am_pm]<br>Dr. $row3[first_name] $row3[middle_name] $row3[last_name]";
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
			
			$current_allocations[]=array('row_color'=>"$col_appoint", 'patient_number'=>$row['patient_number'],'patient_names'=>$row[3], 
				'waiting_time'=>"$waiting_time", 'appointment_time'=>"$appointment_time",'allocation_time'=>$row['time_allocated'],
				'patient_type'=>"$patient_type",'covered_company'=>"$covered_company",'balance'=>"$balance",'patient_left'=>$row['patient_left'],
				'treatment_status'=>"$treatment_status",'discharge_time'=>"$discharge_time",'surgery_id'=>$row['surgery_id'],'allocation_id'=>$row['id'],
				'treatment_finished'=>$row['treatment_finish'],'duration_finished'=>"$row[20]",'duration_ongoing'=>"$row[21]",
				'registered_patient'=>"1",'pid'=>$row['pid']
			);
			
			//$col$i[]=$r2[1];$col$i[]=$r2[2];$col$i[]=$r2[3];
	}

	//get all allocations for today for unregistered 
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.surgery_id, a.pid, sec_to_time(timestampdiff(minute, time_allocated , current_timestamp() ) * 60 ),
			concat(b.first_name,' ',b.middle_name,' ',b.last_name) as names,patient_left, date(time_start_treatment), null, null, time_allocated,  
			time_start_treatment,timediff(time_start_treatment, time_allocated) , timediff(now(), time_allocated), a.id, null, a.discharge_time,
			a.pause_treatment, a.resume_treatment ,a.treatment_status, timediff(discharge_time, time_allocated),treatment_finish,
			timediff(treatment_finish, time_start_treatment),timediff(now(), time_start_treatment)
			from  patient_allocations a, unregistered_patients b  where a.patient_type=0 and a.pid=b.id  and 
			date(time_allocated)>=:from_date and date(time_allocated)<=:to_date order by a.id";
				
	$placeholders[':from_date']=$from_date;
	$placeholders[':to_date']=$to_date;
	$error="unable to get current allocations";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row)	{
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
			$sql3="select shour, smin,rank,am_pm ,first_name,middle_name, last_name from unregistered_patient_appointments a, users b where pid=:pid and 
			      appointment_date=curdate() and a.doc_id=b.id";
			$placeholders3[':pid']=$row['pid'];
			$error3="unable to get appointments in allocations";
			$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
			$appointment_time='';			
			foreach($s3 as $row3){
				$appointment_time="$row3[shour]:$row3[smin] $row3[am_pm]<br>Dr. $row3[first_name] $row3[middle_name] $row3[last_name]";
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
			
			$current_allocations[]=array('row_color'=>"$col_appoint", 'patient_number'=>'Unregistered','patient_names'=>$row[3], 
				'waiting_time'=>"$waiting_time", 'appointment_time'=>"$appointment_time",'allocation_time'=>$row['time_allocated'],
				'patient_type'=>"$patient_type",'covered_company'=>"$covered_company",'balance'=>"$balance",'patient_left'=>$row['patient_left'],
				'treatment_status'=>"$treatment_status",'discharge_time'=>"$discharge_time",'surgery_id'=>$row['surgery_id'],'allocation_id'=>$row['id'],
				'treatment_finished'=>$row['treatment_finish'],'duration_finished'=>"$row[20]",'duration_ongoing'=>"$row[21]",
				'registered_patient'=>"0",'pid'=>$row['pid']
			);
			
			//$col$i[]=$r2[1];$col$i[]=$r2[2];$col$i[]=$r2[3];
	}	

	//now sort the array
	if(count($current_allocations) >0 ){
		array_multisort($time_allocated_array, SORT_ASC, $current_allocations);
		echo "<div class='grid-100 label'>Surgery allocations between $from_date and $to_date</div>";
	}
	else{
		echo "<div class='grid-100 label'>No patients were allocated to any surgical unit in the selected period</div>";
		exit;
	}
	
	//generate tokens for the forms 4
	$token = form_token(); $_SESSION['token_allocate4'] = "$token";  //this is for starting treatment forms
	$token = form_token(); $_SESSION['token_allocate5'] = "$token";  //this is for pausing treatment forms
	$token = form_token(); $_SESSION['token_allocate6'] = "$token";  //this is for resumnig treatment forms
	$token = form_token(); $_SESSION['token_allocate7'] = "$token";  //this si for finishing treatment
	$token = form_token(); $_SESSION['token_allocate8'] = "$token";  //this si for discharging treatment
	
	$total=0;
	//getting the current surgery chairs
	$sql=$error=$s='';$placeholders=array();
	$sql="select surgery_id, upper(surgery_name) from surgery_names order by surgery_name";
	$error="Unable to get surgery list";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
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
		
		if($s2->rowCount()>0){
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
			<th class='allocate_status_count'></th><th class='allocate_status_pid'>PATIENT No.</th>
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
					$registered_patient=html($allocate['registered_patient']);
					$appointment_pid=html($allocate['pid']);
					$patient_number=html($allocate['patient_number']);
					$patient_name=html($allocate['patient_names']);
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
						$styled_appointment="$data[0]<br>$data[1]";
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
					$discharge_time=html($allocate['discharge_time']);
					$duration_finished=html($allocate['duration_finished']);
					$duration_ongoing=html($allocate['duration_ongoing']);
					$val=$encrypt->encrypt(html($allocate['allocation_id']));
					$treatment_finished=html($allocate['treatment_finished']);
					$treatment_status=html($allocate['treatment_status']);

					if(userHasRole($pdo,20) or userHasRole($pdo,12)){$enc_pnum=$encrypt->encrypt("$patient_number");}
					
					echo "<tr class=$row_color ><td>$count</td>
					<td>
						<input type=hidden value='$enc_pnum' />
						<a class='link_color3 goto_tdone ' href=''>$patient_number</a>
					</td>
					<td>
						<input type=hidden value='$enc_pnum' />
						<a class='link_color3 goto_pt_contact' href=''>$patient_name</a>
					</td><td>$patient_type</td>
					<td>";
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
					}
					else{echo "";} //this is for an unregistered poatient
					echo "</td><td>$styled_appointment</td><td>$time_allocated</td><td>$waiting_time</td><td>$time_at_clinic</td>
					<td>";
					//show treatment status
					if($treatment_status == '0'){//untreated  and $discharge_time=='0000-00-00 00:00:00'
						echo "Untreated";				
						//check if the guy is a doctor and then show the form and !$surgery_in_use
						if($_SESSION['is_user_doctor'] == 1 and !$surgery_in_use){ ?>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate4"  value='<?php echo "$_SESSION[token_allocate4]"; ?>' />
								<input type="hidden" name="start_treatment"  value='<?php echo "$val"; ?>' />
								<input type=submit value='Start' />
							</form>	
							<?php
						}
					}
					elseif($treatment_status == '1' ){//treating
						
						echo "Treating<br>Duration $duration_ongoing";				
						//check if the guy is a doctor and then show the form
						if($_SESSION['is_user_doctor'] == 1  ){ ?>
							<br><br>
							<form action="" class='patient_form' method="post" name="" id="">
								<input type="hidden" name="token_allocate4"  value='<?php echo "$_SESSION[token_allocate4]"; ?>' />
								<input type="hidden" name="treatment_status"  value='<?php echo "$val"; ?>' />
								<select name=hold_finish class=hold_finish><option></option>
									<option value='hold'>Suspend</option>
									<option value='finish'>Finished</option>
								</select>
								<br><br>
								<input type=submit value='Submit' />
							</form>	
							<?php
						}
					}				
					elseif($treatment_status == '2'){//on hold
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
						echo "Finished<br>$treatment_finished<br>Duration $duration_finished";
					}
					else{echo "Patient Left";}
					echo "</td><td class=form_table>";
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
						
					echo "</td></tr>"; 
				}
				$total =$total +$count;
				echo "</tbody></table>";	
			}
		}	
	
	
	
	
	
	
	
	}
	echo "<div class=label>TOTAL NUMBER OF PATIENTS: $total</div>";	
	
		exit;
}	//end if for 17
}

?>


<form class='' action='' method="POST"  name="" id="">
	<div class='grid-15'>
		<?php $token = form_token(); $_SESSION['token_sr1'] = "$token";  ?>
		<input type="hidden" name="token_sr1"  value="<?php echo $_SESSION['token_sr1']; ?>" />
		<label for="" class="label">Search by</label>
	</div>
	<div class='grid-20'>
		<?php
			$sql=$error=$s='';$placeholders=array();
			$sql="select a.id, a.name from sub_menus a, sub_privileges b where b.user_id=:user_id and b.parent_menu_id=104
				and b.sub_menu_id=a.id";
			$error="Unable to get surgery report options";
			$placeholders[':user_id']=$_SESSION['id'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			echo "<select class=' ' name=surgery_report><option></option>";
			if($s->rowCount() > 0){
				
				foreach($s as $row){
					$name=html($row['name']);
					$id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$id'>$name</option>";
				}			
									
			}
			else{//check if this is a role
				$sql=$error=$s='';$placeholders=array();
				$sql="select a.id, a.name from sub_menus a, role_sub_privileges b , user_roles c where c.user_id=:user_id and 
				c.role_id=b.role_id and b.parent_menu_id=104 and and b.sub_menu_id=a.id";
				$error="Unable to iget surgery report options by role";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$name=html($row['name']);
					$id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$id'>$name</option>";
				}			
				
			}		
			echo "</select>";
		?>
	</div>
	<div class=clear></div><br>
		<div class='grid-15 label'>Select Surgery unit</div>
		<div class='grid-25 '><?php
			$sql=$error=$s='';$placeholders=array();
			$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
			$error="Unable to get surgery units";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			echo "<select class=' ' name=surgery_unit><option value='all' >All Surgery Units</option>";
				foreach($s as $row){
					$name=html($row['surgery_name']);
					$id=$encrypt->encrypt(html("$row[surgery_id]"));
					echo "<option value='$id'>$name</option>";
				}			
				echo "</select>";					
		?>
		</div>
	
	<div class=clear></div><br>
		<div class='grid-15 label'>Between this date</div>
		<div class='grid-10'><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10 label'>And this date</div>
		<div class='grid-10'><input type=text name=to_date class=date_picker /></div>
		<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Submit"  /></div>
	
	
</form>	 
</div>

