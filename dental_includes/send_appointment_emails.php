<?php
include_once  'automatic_checkup_appointment.php';
	
$result='';
	$result = get_smtp_details($pdo,'info',$encrypt);
	if ("$result" != '') {
				$data =explode('Resource id #',"$result");
				if(isset($data[1])){
					imap_close($result);
					//email doctor appointments
					//get list of doctors 
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select id, first_name, middle_name, last_name, email_address from users where status='active' and email_address!='' and user_type=1";
					$error2="Unable to get doc list";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						//check if the guy has admin role
						$user_id=html($row2['id']);
						$user_name=html(ucfirst("$row2[first_name] $row2[middle_name] $row2[last_name]"));
						$email_address=html($row2['email_address']);
						$today=date('Y-m-d');
						$output='';
						$_SESSION['id']=$user_id;	
						if(userHasRole($pdo,109)){
							//get this docs appointments
							//$caption=html("Dr. $user_name appointments for $today");
							//$output = get_admin_appointments($pdo, $user_id, $caption);
							
							//get list of users with no email address but are active
							$sql21=$error21=$s21='';$placeholders21=array();
							$sql21="select id, first_name, middle_name, last_name, email_address from users where status='active'  and user_type=1";
							$error21="Unable to get doc list";
							$s21 = 	select_sql($sql21, $placeholders21, $error21, $pdo);
							foreach($s21 as $row21){
								$user_id1=html($row21['id']);
								$user_name1=html(ucfirst("$row21[first_name] $row21[middle_name] $row21[last_name]"));
								$caption=html("Dr. $user_name1 appointments for $today ");
								$temp_output='';
								//$temp_output=get_admin_appointments($pdo, $user_id1, $caption);
								//if($temp_output!=''){$output = "$output <br><br>". get_admin_appointments($pdo, $user_id1, $caption);}
								if($output == ''){ $output = get_admin_appointments($pdo, $user_id1, $caption,'admin');}
								else{$output = "$output ". get_admin_appointments($pdo, $user_id1, $caption,'admin');}
							}
							/*
							//get list of users that are locked
							$sql21=$error21=$s21='';$placeholders21=array();
							$sql21="select id, first_name, middle_name, last_name, email_address from users where status='locked'  and user_type=1";
							$error21="Unable to get doc list";
							$s21 = 	select_sql($sql21, $placeholders21, $error21, $pdo);
							foreach($s21 as $row21){
								$user_id1=html($row21['id']);
								$user_name1=html(ucfirst("$row21[first_name] $row21[middle_name] $row21[last_name]"));
								$caption=html("Dr. $user_name1 appointments for $today -- This Dr. has a locked account");
								$temp_output='';
								$temp_output=get_admin_appointments($pdo, $user_id1, $caption);
								if($temp_output!=''){$output = "$output <br><br>". get_admin_appointments($pdo, $user_id1, $caption);}
							}
							
							echo "<br>nnnnnnnnnnnnnnnnnnnnnnnnnnnnn<br>$output<br>"; */
						}
						else{
							//get this docs appointments
							$caption=html("Dr. $user_name appointments for $today");
							$temp_output='';
							//	$temp_output=get_admin_appointments($pdo, $user_id, $caption);
							//	if($temp_output!=''){$output = "$output <br><br>". get_admin_appointments($pdo, $user_id, $caption);}
							$output =  get_admin_appointments($pdo, $user_id, $caption,'');
						}
						
						//now send email
						if($output!=''){
							$subject=html("Appointments for $today");
							
								
							
							 
							//echo "starting";
							$result = send_email($mail , $email_address ,$user_name, $subject, $output, '');
							//echo "result is $result xxxx";
							/*if ($result != 'good'){
								$w1 =1;
								$w2=2;
							 while ($w1 <  $w2){
								 echo "repeating for $email_address";
								 sleep(60);
								 $result2 = send_email($mail , $email_address ,$user_name, $subject, $output, '');
								 if ($result2 == 'good'){break;}
							 }
							}*/
							
							
							
						}
					
					}
				}
	}
	$_SESSION['id']='';
	echo "done";
	?>