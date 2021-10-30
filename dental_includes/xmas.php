<?php

/*
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
					$sql2="SELECT a.pid, first_name, middle_name, last_name, email_address, email_address_2, dob
							FROM patient_details_a a, patient_details_b b WHERE a.pid = b.pid AND a.pid NOT IN ( SELECT pid
							FROM balance_email_log WHERE message_type =3 ) ";
						
						/*$sql2="SELECT a.pid, first_name, middle_name, last_name, email_address, email_address_2, dob
							FROM patient_details_a a, patient_details_b b WHERE a.pid = b.pid  ";*/
					$error2="Unable to get all pts";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					$pts=$s2->rowCount();
					$pts_count=0;
					foreach($s2 as $row2){
						 
						if($row2['email_address'] == '' ){continue;}
						$pts_count++;
						echo "sending $pts_count/$pts $row2[email_address] ";
						$email_address = $row2['email_address'];
						$email_address_2 = $row2['email_address_2'];
						$pid = $row2['pid'];
						
							 
							
							
						
								
									//check email format
									$email1_valid = filter_var($email_address, FILTER_VALIDATE_EMAIL);
									$email2_valid = filter_var($email_address_2, FILTER_VALIDATE_EMAIL);
									if(!$email1_valid and !$email2_valid){continue;}
									
									//determin email to use
									if($email1_valid){$mail_address = $email1_valid;}
									elseif($email2_valid){$mail_address = $email2_valid;}
									
								
									
									if( $mail_address!=''){
										$output = "Greetings,<br><img src='cid:xmas_message' alt='Christmas message' ><br>Contact lines: 0751-856900/ 020 2428104
													email: <a href='mailto:info@molars.co.ke'>info@molars.co.ke</a>";
										//echo "$output";
										 
										echo "<br>".date('h:i:s');
										$result = send_email($mail , $mail_address ,'', "SEASON'S GREETINGS", $output, '');
										if("$result" == "good"){
											//log emial in db
											$sql2=$error2=$s2='';$placeholders2=array();
											$sql2="insert into balance_email_log set user_id=0, when_sent=now(), email_sent_to=:email_sent_to, pid=:pid, message_type=3 ";
											$placeholders2[':email_sent_to']="$mail_address";
											$placeholders2[':pid']=$pid;
											$error2="Unable to get all pts";
											$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
										}
										echo "<br>".date('h:i:s');
									}
								
									 
							
							 
						
	 
					
					}
					exit;
				}
	}
	$_SESSION['id']='';
	echo "done";
	?>