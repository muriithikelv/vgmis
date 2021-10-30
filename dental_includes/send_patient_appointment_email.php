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
	
$result='';
	$result = get_smtp_details($pdo,'info',$encrypt);
	if ("$result" != '') {
				$data =explode('Resource id #',"$result");
				if(isset($data[1])){
					imap_close($result);
					
					//get tomorrows date
					$datetime = new DateTime('tomorrow');
					$apointment_date = $datetime->format('Y-m-d');
					$dayofweek = date('l', strtotime($apointment_date));

					 
					//get appointments for registerd patients
					$sql=$error=$s='';$placeholders=array();
					$appointment_array=array();
					$sql="select registered_patient_appointments.appointment_date,  registered_patient_appointments.treatment, registered_patient_appointments.shour, 
							registered_patient_appointments.smin, registered_patient_appointments.rank, registered_patient_appointments.status,
							registered_patient_appointments.am_pm,
						users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
						patient_details_a.first_name as ptf, patient_details_a.middle_name as ptm, 
						patient_details_a.last_name as ptl,patient_details_a.mobile_phone ,surgery_names.surgery_name,
						e.appointment_date as new_appointment_date ,registered_patient_appointments.smin, patient_details_a.email_address, patient_details_a.email_address_2,patient_details_a.pid
					from registered_patient_appointments join users on registered_patient_appointments.doc_id=users.id
					join patient_details_a on registered_patient_appointments.pid=patient_details_a.pid  
					left join surgery_names on registered_patient_appointments.surgical_unit=surgery_names.surgery_id
					left join registered_patient_appointments as e on e.id=registered_patient_appointments.new_appointment_id
					where 	registered_patient_appointments.appointment_date =:apointment_date and registered_patient_appointments.status = 'NOT SEEN'";
					$error="Unable to get registerd patients";
					$placeholders[':apointment_date']="$apointment_date";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						/*$date=html($row['appointment_date']);
						$doctor=html("$row[docf] $row[docm] $row[docl]");
						$patient=html("$row[ptf] $row[ptm] $row[ptl]");
						$phone=html($row['mobile_phone']);
						$treatment=html($row['treatment']);
						$time=html("$row[shour]:$row[smin] $row[am_pm]");
						$status=html($row['status']);
						$rank=html($row['rank']);
						$new_appointment_date=html($row['new_appointment_date']);
						$smin=html($row['smin']);
						$surgery_name=html($row['surgery_name']);*/
						$email_address=$row['email_address'];
						$email_address_2=$row['email_address_2'];
						$name=ucfirst(html($row['ptf']));
						$time=html("$row[shour]:$row[smin] $row[am_pm]");
						$pid=html($row['pid']);	
						
						//check email format
						$email1_valid = filter_var($email_address, FILTER_VALIDATE_EMAIL);
						$email2_valid = filter_var($email_address_2, FILTER_VALIDATE_EMAIL);
						if(!$email1_valid and !$email2_valid){continue;}
						
						//determin email to use
						if($email1_valid){$mail_address = $email1_valid;}
						elseif($email2_valid){$mail_address = $email2_valid;}
									
						$appointment_array[]=array('name'=>"$name", 'time'=>"$time", 'mail_address'=>"$mail_address", 'pid'=>$pid);			


					}
						
					//get apointments for unregistered	
					$sql=$error=$s='';$placeholders=array();
					$sql="select unregistered_patient_appointments.appointment_date,  unregistered_patient_appointments.treatment, unregistered_patient_appointments.shour, 
					unregistered_patient_appointments.smin, unregistered_patient_appointments.rank, unregistered_patient_appointments.status,
					unregistered_patient_appointments.am_pm,
					users.first_name as docf, users.middle_name as docm, users.last_name as docl, 
					unregistered_patients.first_name, unregistered_patients.phone ,surgery_names.surgery_name,unregistered_patients.email_address ,
					e.appointment_date as new_appointment_date, unregistered_patient_appointments.smin
					from unregistered_patient_appointments join users on unregistered_patient_appointments.doc_id=users.id
					join unregistered_patients on unregistered_patient_appointments.pid=unregistered_patients.id  
					left join surgery_names on unregistered_patient_appointments.surgical_unit=surgery_names.surgery_id
					left join unregistered_patient_appointments as e on e.id=unregistered_patient_appointments.new_appointment_id
					where unregistered_patient_appointments.appointment_date =:apointment_date and unregistered_patient_appointments.status = 'NOT SEEN'";
					$error="Unable to get unregisterd patients";
					$placeholders[':apointment_date']="$apointment_date";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					foreach($s as $row){
						/*$date=html($row['appointment_date']);
						$doctor=html("$row[docf] $row[docm] $row[docl]");
						$patient=html("$row[names]");
						$phone=html($row['phone']);
						$treatment=html($row['treatment']);
						$time=html("$row[shour]:$row[smin] $row[am_pm]");
						$status=html($row['status']);
						$rank=html($row['rank']);
						$new_appointment_date=html($row['new_appointment_date']);
						$smin=html($row['smin']);
						$surgery_name=html($row['surgery_name']);*/
						$email_address=$row['email_address'];
						$name=ucfirst(html($row['first_name']));
						$time=html("$row[shour]:$row[smin] $row[am_pm]");
						$pid=0;	
						
						//check email format
						$email1_valid = filter_var($email_address, FILTER_VALIDATE_EMAIL);
						if(!$email1_valid ){continue;}
						
						$mail_address = $email1_valid; 
									
						$appointment_array[]=array('name'=>"$name", 'time'=>"$time", 'mail_address'=>"$mail_address", 'pid'=>$pid);	
						
					}
					
					//now send emails
					foreach($appointment_array as $row){
						$name=$row['name'];
						$time=$row['time'];
						$mail_address=$row['mail_address'];
						$pid=$row['pid'];
						
						 
						if("$dayofweek" == "Monday" or "$dayofweek" == "Tuesday" or "$dayofweek" == "Wednesday" or "$dayofweek" == "Friday"  ){
							$output = "Hello $name,<br><br>Kindly note that you have an appointment with your dentist tomorrow at $time			.<br>Please confirm.<br><br>
										Kind regards,<br>
										Molars Dental Practice<br>
										Tel: 0751856900<br>
										Email: <a href='mailto:info@molars.co.ke'>info@molars.co.ke</a><br>
										Website: <a href='http://www.molars.co.ke'>www.molars.co.ke</a><br>
										Electricity House 3rd Floor, Harambee Ave<br>";

						}
						elseif("$dayofweek" == "Saturday" or "$dayofweek" == "Sunday"   ){
							$output = "Hello $name,<br><br>Kindly note your dentist will be expecting you tomorrow. Please note its not an appointment but a first come first served. <br><br>
										Kind regards,<br>
										Molars Dental Practice<br>
										Tel: 0751856900<br>
										Email: <a href='mailto:info@molars.co.ke'>info@molars.co.ke</a><br>
										Website: <a href='http://www.molars.co.ke'>www.molars.co.ke</a><br>
										Electricity House 3rd Floor, Harambee Ave<br>";

						}
						elseif("$dayofweek" == "Thursday"  ){
							$output = "Hello $name,<br><br>Hello, Kindly note you have an appointment with your dentist tomorrow. Please note the clinic starts operations from 10:00am and we kindly ask you to come from 10:00 on wards. Please confirm thank you<br><br>
										Kind regards,<br>
										Molars Dental Practice<br>
										Tel: 0751856900<br>
										Email: <a href='mailto:info@molars.co.ke'>info@molars.co.ke</a><br>
										Website: <a href='http://www.molars.co.ke'>www.molars.co.ke</a><br>
										Electricity House 3rd Floor, Harambee Ave<br>";

						}
						
						echo "<br>".date('h:i:s');
						$result = send_email($mail , $mail_address ,$name, 'Molars Dental Practice Appointment', $output, '');
						echo "<br>".date('h:i:s');
						if("$result" == "good"){
							//log emial in db
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into balance_email_log set user_id=0, when_sent=now(), email_sent_to=:email_sent_to, pid=:pid, message_type=1 ";
							$placeholders2[':email_sent_to']="$mail_address";
							$placeholders2[':pid']=$pid;
							$error2="Unable to get all pts";
							$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
						}
					}

					exit;
				}
	}
	$_SESSION['id']='';
	echo "done";
	?>