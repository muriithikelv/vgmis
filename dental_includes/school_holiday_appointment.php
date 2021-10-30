<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,122)){exit;}
echo "<div class='grid_12 page_heading'>SCHOOL HOLIDAY APPOINTMENTS</div>"; ?>
<div class="grid-100 margin_top ">
<div class='feedback hide_element'></div>
<?php  
//this will insert new appointment reminder
if(isset($_POST['token_appt_rem']) and isset($_SESSION['token_appt_rem']) and
$_POST['token_appt_rem']==$_SESSION['token_appt_rem'] ){
	$_SESSION['token_appt_rem']='';
	//echo "bad#dddd";
	$exit_flag=false;
	$email_address='';
	//check if doctor is set
	if(!isset($_POST['doctor']) or $_POST['doctor']==''){
		$exit_flag=true;
		$message="bad#Please specify the doctor for the appointment";
	}


	if(!$exit_flag and (!isset($_POST['selected_patient']) or $_POST['selected_patient']=='')){
		$result  = check_if_patient_exists($_POST['search_by'], $_POST['search_ciretia'],$pdo,$encrypt);
		$data = explode('#',$result);
		$result=$data[0];
		if(isset($data[1])){$searched_patient_pid=$data[1];}
	}

	//check if selectefd  patient is set
	if(!$exit_flag and isset($_POST['selected_patient']) and $_POST['selected_patient']!=''){
		$searched_patient_pid=$encrypt->decrypt($_POST['selected_patient']);
		$result=1;
		//echo "kk";
	}


	//check if the registered patient has been swapped
	if(!$exit_flag and isset($searched_patient_pid) and $searched_patient_pid!=''){	
		$resultx = check_if_swapped($pdo,'pid',$searched_patient_pid);
		if($resultx!='good'){
			$exit_flag=true;
			$message="bad#$resultx and cannot be edited.";
		}
	}
	
	
	//insert regiesterd patient appointment
	if(!$exit_flag and $result==1){  

			
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into school_holiday_appointment_reminders set when_added=now(), 
					doctor=:doc_id,
					pid=:pid,
					holiday_id=:holiday_id,
					task=:task,
					added_by=:added_by";
			$error="Unable to get add appointment";
			$placeholders[':doc_id']=$encrypt->decrypt($_POST['doctor']);
			$placeholders[':pid']=$searched_patient_pid;
			$placeholders[':added_by']=$_SESSION['id'];
			$placeholders[':task']=$_POST['holiday_task'];
			$placeholders[':holiday_id']=$encrypt->decrypt($_POST['holiday_period']);
			$s = insert_sql($sql, $placeholders, $error, $pdo);	
			if($s){$message = "good#Appointment reminder saved";}
			else{$message = "bad#Unable to save appointment reminder";}
		
		
	}
	elseif(!$exit_flag and $result == 2){$message= "bad#No such patient";}
	
		$data=explode('#',"$message");
		echo "$message";
	 
}
 
//this will cancel appointment reminders
if(isset($_POST['token_eap2']) and isset($_SESSION['token_eap2']) and
$_POST['token_eap2']==$_SESSION['token_eap2'] ){
	$_SESSION['token_eap2']='';
	
	$n=count($_POST['status']);
	$status=$_POST['status'];
	$i=0;
	$message='';
	//echo "ffffffffff -- $n";
	while($i < $n){
		//echo "$i --- $status[$i]<br>";
		if("$status[$i]" != ''){
			$data=explode('toa',"$status[$i]");
			if(count($data) ==2){
				//echo "ndai<br>$status[$i]<br>";
				$data2=$encrypt->decrypt("$data[1]");
				//echo "$data2<br>";
				$data=explode('#',"$data2");
				//$cancel=$encrypt->encrypt("cancel#$row[reminder_id]#$row[pid]");
				if($data[0] == "cancel"){
					echo "yes<br>";
					$sql=$error=$s='';$placeholders=array();
					$sql="update school_holiday_appointment_reminders set status=-1 where id=:holiday_reminder_id";
					$error="Unable to cancel appointment reminder";
					$placeholders[':holiday_reminder_id']=$data[1];
					$s = insert_sql($sql, $placeholders, $error, $pdo);	
					$message="<div class='success_response'>Changes saved</div>";
				}
			}
		}
		$i++;
	}
	
		echo "$message";
		exit;
	 
} 
 
//show appointments for editing 
if( isset($_POST['token_eap1']) and isset($_SESSION['token_eap1']) and $_SESSION['token_eap1']==$_POST['token_eap1']){
	$_SESSION['token_eap1']='';
	$current_date=date('Y-m-d');
	if(true){
		$holiday_id=$encrypt->decrypt($_POST['holiday_period']);
		
		//get holiday appointments for the period slected  holiday_period
		$appointment_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added,  a.task,a.id,a.pid, a.doctor as doctor_id ,b.first_name, b.middle_name, b.last_name, 
				
			c.first_name as ptf, c.middle_name as ptm, c.last_name as ptl,c.mobile_phone 
			
		from school_holiday_appointment_reminders a join users b on a.doctor=b.id
		join patient_details_a c on a.pid=c.pid
		 where a.status=0 and a.holiday_id=:holiday_id order by a.id";
		$error="Unable to get school holiday appts";
		$placeholders[':holiday_id']=$holiday_id;
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$date=html($row['when_added']);
			$doctor=html("$row[first_name] $row[middle_name] $row[last_name]");
			$patient=html("$row[ptf] $row[ptm] $row[ptl]");
			$phone=html($row['mobile_phone']);
			$task=html($row['task']);
			$reminder_id=html($row['id']);
			$pid=html($row['pid']);
			$doctor_id=html($row['doctor_id']);
			$appointment_array[]=array('date'=>"$date", 'doctor'=>"$doctor", 'patient'=>"$patient", 'phone'=>"$phone", 'task'=>"$task", 'reminder_id'=>"$reminder_id",'pid'=>"$pid",'doctor_id'=>$doctor_id);
		}
		

		if(count($appointment_array) > 0){
			
			//get holiday notify_date
			$sql=$error=$s='';$placeholders=array();
			$sql="select description, notify_date from school_holiday_description where id=:holiday_id  ";
			$error="Unable to get school holiday appts";
			$placeholders[':holiday_id']=$holiday_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			
			foreach($s as $row){
				$holiday_name=html($row['description']);
				$notify_date=html($row['notify_date']);
			}
			
			 ?>
			<form action="" method="POST"  name="" id="" class="">
				<?php $token = form_token(); $_SESSION['token_eap2'] = "$token"; ?>
				<input type="hidden" name="token_eap2"  value="<?php echo $_SESSION['token_eap2']; ?>" />	
		
			<?php
			echo "<div class='grid-100 div_shower44'></div>";
			echo "<table class='normal_table'><caption>$holiday_name appointments pending creation</caption><thead>
			<tr><th class=apr_count></th><th class=apr_date>DATE</th><th class=apr_doc>DOCTOR</th><th class=apr_pt>PATIENT</th><th class=apr_phone>PHONE NO.</th>
			<th class=apr_time>TASK</th><th class=apr_status>ACTION</th><th class=apr_treatment>APPOINTMENT</th></tr></thead><tbody>";
			$count=0;
			foreach($appointment_array as $row){
				$count++;
				$bgcolor='';
				$cancel=$encrypt->encrypt("cancel#$row[reminder_id]#$row[pid]");
				$create_appointment=$encrypt->encrypt("create_appointment#$row[reminder_id]#$row[pid]#$row[doctor_id]#$row[task]");
		 
				echo "<tr class=$bgcolor ><td>$count</td><td>$row[date]</td><td>$row[doctor]</td><td>$row[patient]</td><td>$row[phone]</td>
				<td>$row[task]</td><td>";
					$seen=$not_seen=$re_appointed='';
					 
						echo "<select class=set_appointment_status2 name=status[]>
								<option></option>
								<option value='tengeneza$create_appointment' >Create Appointment</option>
								<option value='toa$cancel'   >Cancel Reminder</option>
								 
						</select>";
					
					echo "</td><td class=appoint_td></td></tr>";
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
			</form>
			<?php
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no appointments for the selected date criteria</label></div><br>";			
		}
	}
		
}


?>	<div class='grid-100 '>
		<div class='grid-15 appointment_type_div'><label for="" class="label">Select Action</label></div>
		<div class='grid-20 appointment_type_div'><select class='appointment_type'>
			<option></option>
			<option value='create_appointent_reminder'>Create appointment reminder</option>
			<option value='view_appointment_reminder'>View pending reminders</option>
		</select></div>
	</div>
	
	<div class=clear></div><br>
	
	<div class='grid-100 create_appointent_reminder'>
		<!-- show school holiday options -->
		<form action="create_school_holiday_remider" method="POST"  name="" id="" class="patient_form">
			
		<div class='grid-15'>
				<?php $token = form_token(); $_SESSION['token_appt_rem'] = "$token";  ?>
				<input type="hidden" name="token_appt_rem"  value="<?php echo $_SESSION['token_appt_rem']; ?>" />
				<label for="" class="label">Search Patient by</label>
			</div>
			<div class='grid-20'>
				<select name=search_by><option></option>
					<option value=patient_number>Patient Number</option>
					<option value=first_name>First Name</option>
					<option value=middle_name>Middle Name</option>
					<option value=last_name>Last Name</option>
				</select>
			</div>
			<div class='grid-15'><input type=text name=search_ciretia  /></div>
			
			<?php
				//select doctor
				$sql=$error=$s='';$placeholders=array();
				$sql="select id,first_name, middle_name,last_name from users where user_type=1 and status='active'";
				$error="Unable to get list of doctors";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<div class='grid-10'><label for='' class='label'>Select Doctor</label></div>";
				echo "<div class='grid-20'><select class=appointment_doctor name=doctor><option></option>";
					foreach($s as $row){
						$doctor_name=html("$row[first_name] $row[middle_name] $row[last_name]");
						$doc_id=$encrypt->encrypt("$row[id]");
						echo "<option value='$doc_id'>$doctor_name</option>";
					}
				echo "</select></div>";
			?>
			<div class=clear></div></br>
		<div class='grid-15 '><label for="" class="label">Holiday Period </label></div>
		<div class='grid-20 '>
			
			 
				<?php  /*
					$mgonjwa = $encrypt->encrypt($_SESSION['pid']);
					$daktari = $encrypt->encrypt($_SESSION['id']);
					echo "<input type='hidden' name='token_ninye' id='token_ninye' value='$mgonjwa' />";
					echo "<input type='hidden' name='token_ninye2' id='token_ninye2' value='$daktari' />";
					*/
				?>
				
			<?php 
				$sql=$error=$s='';$placeholders=array();
				$sql="select id, description from school_holiday_description where notify_date > curdate() order by notify_date";
				$error="Unable to get school holidays";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<select name='holiday_period'><option></option>";
				foreach($s as $row){
					$val=$encrypt->encrypt($row['id']);
					$holiday_period=html($row['description']);
					echo "<option value='$val'>$holiday_period</option>";
				}
				echo "</select>";
			?>
		</div>
		<div class=clear></div></br>
		<div class='grid-15 '><label for="" class="label">Task </label></div>
		<div class='grid-15 '><textarea    rows='2' name=holiday_task ></textarea></div>
		<div class=clear></div></br>
		<div class='grid-5 prefix-15'><input type=submit value='Submit' /></div>	
		</form>
	</div>
	
	<div class='grid-100 view_appointment_reminder'>
		<form class='' action="" method="POST" enctype="" name="" id="">
			<?php $token = form_token(); $_SESSION['token_eap1'] = "$token";  ?>
			<input type="hidden" name="token_eap1"  value="<?php echo $_SESSION['token_eap1']; ?>" />
			<div class='grid-15'><label for="user" class="label">Select School Holiday</label></div>
			<div class='grid-20 '>
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql="select id, description from school_holiday_description  order by notify_date";
					$error="Unable to get school holidays";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					echo "<select name='holiday_period'><option></option>";
					foreach($s as $row){
						$val=$encrypt->encrypt($row['id']);
						$holiday_period=html($row['description']);
						echo "<option value='$val'>$holiday_period</option>";
					}
					echo "</select>";
				?>
			
			</div>
			
			
			
			
			<div class='grid-10'><input type=submit  value=Submit /></div>
		</form>
	</div>
</div>