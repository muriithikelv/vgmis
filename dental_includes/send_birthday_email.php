<?php

/*

"C:\Program Files\VertrigoServ_230\Php\php.exe"
-f "C:\Program Files\VertrigoServ_230\dental_includes\send_birthday_email.php"




"C:\Program Files\VertrigoServ_230\Php\php.exe"
-f "C:\Program Files\VertrigoServ_230\dental_includes\send_patient_appointment_email.php"


include_once  '../dental_includes/magicquotes.inc.php'; 
include_once   '../dental_includes/db.inc.php'; 
include_once   '../dental_includes/helpers.inc.php'; 
include_once   '../dental_includes/access.inc.php'; 
//include_once    '../dental_includes/phpmailer/class.phpmailer.php';
include_once   '../dental_includes/phpmailer/class.phpmailer.php';*/
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
					//get list of pts 
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select a.pid,first_name, middle_name, last_name, email_address, email_address_2, dob from patient_details_a a, patient_details_b b where a.pid=b.pid ";
					$error2="Unable to get all pts";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						
						$birthday = $row2['dob'];
						$first_name = $row2['first_name'];
						$middle_name = $row2['middle_name'];
						$last_name = $row2['last_name'];
						$email_address = $row2['email_address'];
						$email_address_2 = $row2['email_address_2'];
						$pid = $row2['pid'];
						//if( $pid != 23624){continue;}
						//echo "$birthday";
						
						//check if dob is valid date format
						$test_arr  = explode('-', "$birthday");
						if (count($test_arr) == 3) {
							 
							$year=$test_arr[0];
							$month=$test_arr[1];
							$day=$test_arr[2];
							
							if (checkdate($month, $day, $year)) {
								//check if guy was born today
								if(date('m-d') == substr($birthday,5,5)){
									//check email format
									$email1_valid = filter_var($email_address, FILTER_VALIDATE_EMAIL);
									$email2_valid = filter_var($email_address_2, FILTER_VALIDATE_EMAIL);
									if(!$email1_valid and !$email2_valid){continue;}
									
									//determin email to use
									if($email1_valid){$mail_address = $email1_valid;}
									elseif($email2_valid){$mail_address = $email2_valid;}
									
									//determine name to use
									if($first_name != ''){$name=ucfirst($first_name);}
									elseif($middle_name != ''){$name=ucfirst($middle_name);}
									elseif($last_name != ''){$name=ucfirst($last_name);}
									
									if($name!='' and $mail_address!=''){
										$output = "Dear $name,<br><br><img src='cid:birthday_message' alt='Birthday message' ><br><br>
													Regards,<br>
													Molars Dental Practice<br>
													Tel: 0751856900<br>
													Email: <a href='mailto:info@molars.co.ke'>info@molars.co.ke</a><br>
													Website: <a href='http://www.molars.co.ke'>www.molars.co.ke</a><br>
													Electricity House 3rd Floor, Harambee Ave<br>";
										//echo "$output";
										 
										echo "<br>".date('h:i:s');
										$result = send_email($mail , $mail_address ,$name, 'HAPPY BIRTHDAY', $output, '');
										if("$result" == "good"){
											//log emial in db
											$sql2=$error2=$s2='';$placeholders2=array();
											$sql2="insert into balance_email_log set user_id=0, when_sent=now(), email_sent_to=:email_sent_to, pid=:pid, message_type=2 ";
											$placeholders2[':email_sent_to']="$mail_address";
											$placeholders2[':pid']=$pid;
											$error2="Unable to get all pts";
											$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
										}
										echo "<br>".date('h:i:s');
									}
								}
									 
							}
							 
						}
	 
					
					}
					exit;
				}
	}
	$_SESSION['id']='';
	echo "done";
	?>