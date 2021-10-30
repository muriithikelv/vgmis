<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,90)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT LISTS</div>";
?>
<div class=grid-container>
<div class='grid-100 div_shower44'></div> 
<?php 

//get results
if(isset($_POST['token_pl1']) and 	$_POST['token_pl1']!='' and $_POST['token_pl1']==$_SESSION['token_pl1']){
		$_SESSION['token_pl1']='';
		$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();
		$sql2=$error2=$s2='';$placeholders2=array();
		$insurer=$company = $email_filter='';
		/*//check if search_criteria selected
		if(!$exit_flag and !isset($_POST['search_criteria']) or $_POST['search_criteria']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the report";
				$exit_flag=true;
		}*/
		
		//check if date is selcted
		if(!$exit_flag and !isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']==''  ){	
				$result_class="error_response";
				$result_message="Please specify the date range for the search criteria";
				$exit_flag=true;
		}	
		
		
				
		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);

		}
			//insurer criteria
			if(!$exit_flag and $_POST['ptype']!='all'){
				$insurer_id=$encrypt->decrypt($_POST['ptype']);
				$insurer = " and a.type=:insurer_id ";
				$placeholders[':insurer_id']=$insurer_id;
			}
			
			//company criteria
			if(!$exit_flag and  $_POST['covered_company']!='all'){
				$company_id=$encrypt->decrypt($_POST['covered_company']);
				$company = " and a.company_covered=:company_id ";
				$placeholders[':company_id']=$company_id;
			}

			//email criteria
			if(!$exit_flag and  $_POST['email_filter']!='all'){
				if($_POST['email_filter'] == 'email_only'){
					$email_filter = " and (a.email_address != '' or a.email_address_2 !='') ";
				}
				elseif($_POST['email_filter'] == 'no_email'){
					$email_filter = " and a.email_address ='' and  a.email_address_2 ='' ";
				}
				
			}
			
			//registerd patients 
		if(!$exit_flag and isset($_POST['summary_detail'])){
			//$sql=$error=$s='';$placeholders=array();
			
			//check if insurer is selcted
			if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
					$result_class="error_response";
					$result_message="Please select the patient type";
					$exit_flag=true;
			}	
			//check if report type is selcted
			if(!$exit_flag and !isset($_POST['summary_detail']) or $_POST['summary_detail']==''   ){	
					$result_class="error_response";
					$result_message="Please select the report type";
					$exit_flag=true;
			}				

			//summary report
			if(!$exit_flag and $_POST['summary_detail']=='summary'   ){	
				$sql="select count(c.pid),b.name as name,b.id from patient_details_b as c join patient_details_a as a  on c.pid=a.pid and 
					c.when_added >=:from_date and c.when_added <=:to_date $insurer $company $email_filter
				left join insurance_company as b on a.type=b.id 
				group by a.type order by name ";
				$error="Unable to get summary report of registered patients";
				$placeholders[':from_date']=$_POST['from_date'];
				$placeholders[':to_date']=$_POST['to_date'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					$count=$total=0;
					foreach($s as $row){
						if($count==0){
							if($_POST['ptype']=='all'){$for='';}
							else{$for=html($row['name']);}
							$caption="Patients regsitered $for between $from_date and $to_date";
							echo "<br><br><table class=normal_table><caption>$caption</caption><thead><tr><th class=srpc_count></th><th class=srpc_type>PATIENT TYPE</th>
							<th class=srpc_company>COMPANY COVERED</th><th class=srpc_pts>No. OF PATIENTS</th></tr></thead><tbody>";
					
						}
						$count++;
						$patient_type=html($row['name']);
						$sum=number_format(html($row[0]));
						$total=$total + html($row[0]);
						echo "<tr class=light_blue_background><td>$count</td><td colspan=2>$patient_type</td><td>$sum</td></tr>";
						//now get corparates under this patient type if any
						/*$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="SELECT a.sum_number, b.name, b.id FROM (sELECT count( pid ) AS sum_number, company_covered FROM 
							patient_details_a left join patient_details_b WHERE 
							patient_details_a.TYPE =:patient_type and patient_details_b.pid=patient_details_a.pid and 
							patient_details_b.when_added >=:from_date and patient_details_b.when_added <=:to_date
							group by company_covered  ) AS a 
							 
							LEFT JOIN covered_company AS b ON a.company_covered = b.id order by sum_number desc";
						$error2="Unable to get covered companies under summary patient registration report";
						$placeholders2[':patient_type']=$row['id'];
						$placeholders2[':from_date']=$_POST['from_date'];
						$placeholders2[':to_date']=$_POST['to_date'];
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						if($s2->rowCount() > 0){
							$i=0;
							foreach($s2 as $row2){
								$i++;
								$company=html($row2['name']);
								$sum_company=number_format(html($row2[0]));
								echo "<tr><td></td><td>$i</td><td>$company</td><td>$sum_company</td></tr>";
							}
						}*/
					}
					echo "<tr class=total_background><td  colspan=3>Total</td><td>".number_format($total)."</td></tr></tbody></table>";
				}
				else{echo "<label  class=label>There are no patients for the selected criteria</label>";}
				exit;
			}	
			//detail report
			elseif(!$exit_flag and $_POST['summary_detail']=='detailed'   ){	
				$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_names, e.name as company_covered,b.name as insurer,
						a.biz_phone,a.mobile_phone,a.email_address,a.email_address_2,c.address,c.pid,c.when_added,a.patient_number
					from patient_details_b as c join patient_details_a as a  on c.pid=a.pid and 
					c.when_added >=:from_date and c.when_added <=:to_date $insurer $company $email_filter
				left join insurance_company as b on a.type=b.id
				left join covered_company as e on e.id=a.company_covered
				order by  c.when_added";
				$error="Unable to get detailed report of registered patients";
				$placeholders[':from_date']=$_POST['from_date'];
				$placeholders[':to_date']=$_POST['to_date'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					?>	<div class='feedback hide_element'></div>
						<form class='patient_form' action="#send_email_on_same_page" target="_new" method="POST" enctype="" name="" id="">
							<?php $token = form_token(); $_SESSION['token_rpd1'] = "$token";  ?>
							<input type="hidden" name="token_rpd1"  value="<?php echo $_SESSION['token_rpd1']; ?>" />
					
					<?php
					$count=$total=0;
					$send_email_flag=false;
					foreach($s as $row){
						if($count==0){
							if($_POST['ptype']=='all'){$for='';}
							else{$for=html($row['insurer']);}
							$caption="Patients regsitered $for between $from_date and $to_date";
							echo "<br><br><table class=normal_table><caption>$caption</caption><thead><tr><th class=srpd_count></th>
							<th class=srpd_registered>DATE</th>
							<th class=srpd_pname>PATIENT</th>
							<th class=srpd_pnum>PATIENT<br>NUMBER</th>
							<th class=srpd_type>TYPE</th>
							<th class=srpd_mobile>MOBILE No.</th>
							<th class=srpd_biz_no>BUSINESS No.</th>
							<th class=srpd_email1>EMAIL 1</th>
							<th class=srpd_email2>EMAIL 2</th>
							<th class=srpd_address>ADDRESS</th>
							<th class=srpd_email>EMAIL</th>
							</tr></thead><tbody>";
					
						}
						$count++;
						$pid=html($row['pid']);
						$registered=html($row['when_added']);
						$patient_name=ucfirst(html($row['patient_names']));
						$patient_number=html($row['patient_number']);
						$patient_type=html("$row[insurer]");
						if($row['company_covered']!=''){$patient_type="$patient_type - ".html($row['company_covered']);}
						$patient_mobile=html($row['mobile_phone']);
						$patient_biz=html($row['biz_phone']);
						$patient_email1=html($row['email_address']);
						$patient_email2=html($row['email_address_2']);
						$patient_address=html($row['address']);
						
						echo "<tr ><td>$count</td><td>$registered</td><td>$patient_name</td><td>$patient_number</td><td>$patient_type</td>
						<td>$patient_mobile</td><td>$patient_biz</td><td>$patient_email1</td><td>$patient_email2</td><td>$patient_address</td>
						<td>";
							if($patient_email2 != '' or $patient_email1 != ''){
								$send_email_flag=true;
								$var=html("$patient_name@@$patient_number@@$patient_email1@@$patient_email2@@$pid");
								$var=$encrypt->encrypt("$var");
							echo "<input type=checkbox name='send_email[]' class=email_balance value=$var />";
							}
							else{echo "&nbsp;";}
						echo "</td></tr>";
					}
					echo "</tbody></table>";
					if($send_email_flag){
						$result='';
								$result = get_smtp_details($pdo,'credit_control',$encrypt);
								if ("$result" != '') {
											$data =explode('Resource id #',"$result");
											if(isset($data[1])){
												imap_close($result);
												echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
												echo "<div class='prefix-40 grid-10 label'>Email Subject</div><div class=grid-50 email_text'><input type=text name=email_subject /></div>";
												echo "<div class='prefix-40 grid-10 label'>Email Body</div><div class=grid-50 email_text'><textarea cols=50 rows=10 name=email_text></textarea></div>";
												echo "<div class='grid-100'><input type=submit class=put_right value='Send Email' /></form></div>";
												}
								else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
								}
								else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
					}	
				}
				else{echo "<label  class=label>There are no patients for the selected criteria</label>";}
				exit;
			}			
			
		}
		elseif(isset($_POST['procedure_done'])){//this is for last seen
				
			//procedure criteria
			$procedure=$procedure_name='';
			if(!$exit_flag and $_POST['procedure_done']!='all'){
				$procedure_id=$encrypt->decrypt($_POST['procedure_done']);
				$procedure = " and procedure_id=:procedure_id ";
				$placeholders[':procedure_id']=$procedure_id;
				//get procedure name
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select name from procedures where id=:procedure_id";
				$error2="Unable to get  procedure name";
				$placeholders2['procedure_id']=$procedure_id;
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
				foreach($s2 as $row2){$procedure_name=html(" for $row2[name]");}
			}
			if($procedure==''){
				$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_names, e.name as company_covered,b.name as insurer,
						a.biz_phone,a.mobile_phone,a.email_address,a.email_address_2,c.address,c.pid,f.when_added,a.patient_number,
						f.treatment_procedure_id,h.name as procedure_name, g.teeth, g.details
						from (select when_added , treatment_procedure_id,a.pid from treatment_procedure_notes  a 
								where id = (select max(id) from treatment_procedure_notes  where pid=a.pid  group by pid)
							) as f join tplan_procedure as g on f.treatment_procedure_id=g.treatment_procedure_id  
						and f.when_added >=:from_date and f.when_added <=:to_date
					 join patient_details_a as a  on g.pid=a.pid $insurer	
					join patient_details_b as c on c.pid=g.pid
                     join procedures as h on h.id=g.procedure_id                  
				left join insurance_company as b on a.type=b.id
				left join covered_company as e on e.id=a.company_covered
				
				order by  f.when_added";
			}
			else{
				$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_names, e.name as company_covered,b.name as insurer,
						a.biz_phone,a.mobile_phone,a.email_address,a.email_address_2,c.address,c.pid,f.when_added,a.patient_number,
						f.treatment_procedure_id,h.name as procedure_name, g.teeth, g.details
						from (select when_added , treatment_procedure_id,a.pid,a.procedure_id from treatment_procedure_notes  a
								where id = (select max(id) from treatment_procedure_notes  where pid=a.pid  and procedure_id=:procedure_id  group by pid)
							) as f join tplan_procedure as g on f.treatment_procedure_id=g.treatment_procedure_id   
						and f.when_added >=:from_date and f.when_added <=:to_date
					 join patient_details_a as a  on g.pid=a.pid $insurer	
					join patient_details_b as c on c.pid=g.pid
                     join procedures as h on h.id=g.procedure_id                               
				left join insurance_company as b on a.type=b.id
				left join covered_company as e on e.id=a.company_covered
				
				order by  f.when_added";			
			}
				$error="Unable to get last seen report";
				$placeholders[':from_date']=$_POST['from_date'];
				$placeholders[':to_date']=$_POST['to_date'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					?>	<div class='feedback hide_element'></div>
						<form class='patient_form' action="#send_email_on_same_page" target="_new" method="POST" enctype="" name="" id="">
							<?php $token = form_token(); $_SESSION['token_rpd1'] = "$token";  ?>
							<input type="hidden" name="token_rpd1"  value="<?php echo $_SESSION['token_rpd1']; ?>" />
					
					<?php
					$count=$total=0;
					foreach($s as $row){
						if($count==0){
							if($_POST['ptype']=='all'){$for='';}
							else{$for=html($row['insurer']);}
							$caption=strtoupper("$for patients last seen between $from_date and $to_date $procedure_name");
							echo "<br><br><table class=normal_table><caption>$caption</caption><thead><tr><th class=srpd_count></th>
							<th class=srpd_registered>DATE</th>
							<th class=srpd_pname>PATIENT</th>
							<th class=srpd_pnum>PATIENT<br>NUMBER</th>
							<th class=srpd_type>TREATMENT</th>
							<th class=srpd_mobile>MOBILE No.</th>
							<th class=srpd_biz_no>BUSINESS No.</th>
							<th class=srpd_email1>EMAIL 1</th>
							<th class=srpd_email2>EMAIL 2</th>
							<th class=srpd_address>ADDRESS</th>
							<th class=srpd_email>EMAIL</th>
							</tr></thead><tbody>";
					
						}
						$count++;
						$pid=html($row['pid']);
						$registered=html($row['when_added']);
						$patient_name=ucfirst(html($row['patient_names']));
						$patient_number=html($row['patient_number']);
						$patient_type=html("$row[insurer]");
						if($row['company_covered']!=''){$patient_type="$patient_type - ".html($row['company_covered']);}
						$treatment_done=html("$row[procedure_name] $row[teeth] $row[details] ");
						$patient_mobile=html($row['mobile_phone']);
						$patient_biz=html($row['biz_phone']);
						$patient_email1=html($row['email_address']);
						$patient_email2=html($row['email_address_2']);
						$patient_address=html($row['address']);
						$var=html("$patient_name@@$patient_number@@$patient_email1@@$patient_email2@@$pid");
						$var=$encrypt->encrypt("$var");
						echo "<tr ><td>$count</td><td>$registered</td><td>$patient_name</td><td>$patient_number</td><td>$treatment_done</td>
						<td>$patient_mobile</td><td>$patient_biz</td><td>$patient_email1</td><td>$patient_email2</td><td>$patient_address</td>
						<td><input type=checkbox name='send_email[]' class=email_balance value=$var /></td></tr>";
					}
					echo "</tbody></table><div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
					echo "<div class='prefix-40 grid-10 label'>Email Subject</div><div class=grid-50 email_text'><input type=text name=email_subject /></div>";
					echo "<div class='prefix-40 grid-10 label'>Email Body</div><div class=grid-50 email_text'><textarea cols=50 rows=10 name=email_text></textarea></div>";
					echo "<div class='grid-100'><input type=submit class=put_right value='Send Email' /></form></div>";
				}
				else{echo "<label  class=label>There are no patients for the selected criteria</label>";}
				exit;		
		}
exit;
}
	
//get results for points
elseif(isset($_POST['token_pl2']) and 	$_POST['token_pl2']!='' and $_POST['token_pl2']==$_SESSION['token_pl2']
	and  userHasRole($pdo,90)){
		$_SESSION['token_pl2']='';
		$exit_flag=false;
		$insurer= $email_filter='';
		$having=' having balance_left > 0 ';
		$exit_flag=false;//$greater_and_less=$less_than=$greater_than=$no_range=false;
		$criteria = ' points < 0 ';
		$caption="Patient's with loyalty points";
		$sql=$error=$s='';$placeholders=array();
		$sql2=$error2=$s2='';$placeholders2=array();
		
		//email criteria
		if(!$exit_flag and  $_POST['email_filter']!='all'){
			if($_POST['email_filter'] == 'email_only'){
				$email_filter = " and (a.email_address != '' or a.email_address_2 !='') ";
			}
			elseif($_POST['email_filter'] == 'no_email'){
				$email_filter = " and a.email_address='' and  a.email_address_2='' ";
			}
			
		}
			
		//check if balance amount is set
		if(!$exit_flag and ($_POST['balance_range']=='greater_than' or $_POST['balance_range']=='less_than') and 
			(!isset($_POST['balance']) or $_POST['balance']=='')){	
				$result_class="error_response";
				$result_message="Please specify the loyalty  points amount";
				$exit_flag=true;
		}	
		
		//check if ranges are set for range balance
		if(!$exit_flag and  $_POST['balance_range']=='range'  and 
			(!isset($_POST['balance_greater']) or $_POST['balance_greater']=='' or !isset($_POST['balance_less']) or $_POST['balance_less']=='')){	
				$result_class="error_response";
				$result_message="Please specify the loyalty points range";
				$exit_flag=true;
		}
		
		
		//set having criteria
		if(!$exit_flag and  $_POST['balance_range']=='range'){
			//changes signs coz point sare s tored are negatove numbers in db
			$criteria = ' points <=:greater_than1 and points >=:less_than1 ';
			$placeholders2[':greater_than1']=(-1) * $_POST['balance_greater'];
			$placeholders2[':less_than1']=(-1) * $_POST['balance_less'];
			$v1=html($_POST['balance_greater']);
			$v2=html($_POST['balance_less']);
			$caption="Patient's with loyalty points between ".number_format($v1,2)." and ".number_format($v2,2);
		}
		
		//greater than
		elseif(!$exit_flag and  $_POST['balance_range']=='greater_than'){
			$greater_than=true;
			$greater_than_amount=$_POST['balance'];
			//changes signs coz point sare s tored are negatove numbers in db
			$criteria = ' points <=:greater_than_amount1 ';
			$placeholders2[':greater_than_amount1']=(-1) * $_POST['balance'];
			$v=html($_POST['balance']);
			$caption="Patient's with loyalty points greater than ".number_format($v,2);
		}
		
		//less than
		elseif(!$exit_flag and  $_POST['balance_range']=='less_than'){
			$less_than=true;
			$less_than_amount=$_POST['balance'];
			//changes signs coz point sare s tored are negatove numbers in db
			$criteria = ' points < 0 and points >=:less_than_amount ';
			$placeholders2[':less_than_amount']=(-1) * $_POST['balance'];
			$v=html($_POST['balance']);
			$caption="Patient's with loyalty points below ".number_format($v,2);
		}
		
		//no range
		elseif(!$exit_flag and  $_POST['balance_range']=='no_range'){
			$no_range=true;
			//changes signs coz point sare s tored are negatove numbers in db
			$criteria = ' points < 0 ';
			$caption="Patient's with loyalty points ";
		}
		
		if(!$exit_flag){
				$i=$total=0;
				$invoices_array=$_SESSION['balance_invoice']=array();
					//now check if the pt is from the mentioned insuer
						
					$sql2="select patient_number,a.pid,first_name,middle_name,last_name,mobile_phone, biz_phone, email_address, 
							email_address_2,b.name as company_covered,c.name as insurer , points as balance
							from patient_details_a a 
							left join covered_company b on a.company_covered=b.id 
							left join insurance_company c on a.type=c.id join pt_balances d on a.pid=d.pid
							where $criteria $email_filter ";
					$error2="Error: Unable to pt details from uniq ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						$email_present=false;
						foreach($s2 as $row2){
								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$pnum=html("$row2[patient_number]");
								$company=html("$row2[company_covered]");
								$insurer=html("$row2[insurer]");
								$mobile_no=html("$row2[mobile_phone]");
								$biz_no=html("$row2[biz_phone]");
								$email1=html("$row2[email_address]");
								$email2=html("$row2[email_address_2]");
								$balance=(-1) * html("$row2[balance]");
								$pid=html("$row2[pid]");
								$pid_encrypt=$encrypt->encrypt("$pid");
								$var=html("$balance#$patient#$pnum#$email1#$email2#$pid");
								$var=$encrypt->encrypt("$var");
								$total=$total + $balance;
								if($company!=''){$company="$insurer - $company";}
								else{$company="$insurer";}
								
									if($i==0){ ?>
									<form class='' action="dental_b/" target="_new" method="POST" enctype="" name="" id="">
										<?php $token = form_token(); $_SESSION['token_cbr2'] = "$token";  ?>
										<input type="hidden" name="token_cbr2"  value="<?php echo $_SESSION['token_cbr2']; ?>" />
								
								<?php
										echo "<br><br>
										<table class='normal_table email_table'><caption>$caption</caption><thead>
										<tr><th class=cbr_count></th>
										<th class=cbr_pname>PATIENT</th>
										<th class=cbr_pnum>PATIENT No.</th>
										<th class=cbr_type>TYPE</th>
										<th class=cbr_mobile>MOBILE No.</th>
										<th class=cbr_biz>BUSINESS No.</th>
										<th class=cbr_email1>EMAIL 1</th>
										<th class=cbr_email2>EMAIL 2</th>
										<th class=cbr_balance>POINTS</th>
										<th class=cbr_send_email>EMAIL</th>
										</tr></thead><tbody>";						
									}
									
									$i++;
									$background_color='';
									if($email1=='' and $email2==''){$background_color='light_blue_background';}
									echo "<tr class=$background_color><td>$i</td><td>$patient</td><td>$pnum</td><td>$company</td><td>$mobile_no</td><td>$biz_no</td><td>$email1</td><td>$email2</td><td>
											<input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_points_redem'>".
											number_format($balance,2)."</a></td><td>";
											//check if email address i set
											if($email1 == '' and $email2==''){echo "&nbsp;";}
											else{
												$email_present=true;
												echo "<input type=checkbox name='send_email[]' class=email_balance value=$var />";
											}
									echo "</td></tr>";
						
						}//end s2
						if($i > 0 ){
							echo "<tr class=total_background><td colspan=8>TOTAL</td><td colspan=2>".number_format($total,2)."</td></tr></tbody></table>";
							if($email_present){
								$result='';
								$result = get_smtp_details($pdo,'credit_control',$encrypt);
								if ("$result" != '') {
											$data =explode('Resource id #',"$result");
											if(isset($data[1])){
												imap_close($result);
												//email doctor appointments
												//echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
												//echo "<div class='grid-100'><input type=submit class='put_right' value='Send Balance in Email' /></form></div>";
													echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
													echo "<div class='prefix-40 grid-10 label'>Email Subject</div><div class=grid-50 email_text'><input type=text name=email_subject /></div>";
													echo "<div class='prefix-40 grid-10 label'>Email Body</div><div class=grid-50 email_text'><textarea cols=50 rows=10 name=email_text></textarea></div>";
													echo "<div class='grid-100'><input type=submit class=put_right value='Send Email' /></form></div>";
												
											}
											else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
								}
								else{echo "<div class='grid-100 label put_right'>Unable to send emails. Please check email account credentials and internet connectivity</div>";}
							}
							//now output the labs to be paid
							exit;
						}
					}//end if
					else{echo "<label  class=label>There are no patients with loyalty points matching the selected criteria</label>";}
		}
exit;		
}


	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
		<br>	
			
	
		<?php $token = form_token(); $_SESSION['token_pl1'] = "$token";  ?>
		<?php $token = form_token(); $_SESSION['token_pl2'] = "$token";  ?>			
					
				<!--show doctor-->
				<div class='grid-20'><label for="" class="label">Select search criteria</label>
				</div>
				<div class='grid-25'><select name=search_criteria class=patient_list_report>
					<option ></option>
					<option value='registered'>Registered between a date range</option>
					<option value='redeem_points'>Patients with loyalty points</option>
					</select><!--<option value='last_seen'>Last seen between a date range</option>-->
					
				</div>	
				<div class=clear></div><br>	
				<!-- redeem points -->
								
				<div class='no_padding loyalty_div'>
											<form action="" method="POST" enctype="" name="" id="">
							<input type="hidden" name="token_pl2"  value="<?php echo $_SESSION['token_pl2']; ?>" />
					<div class=clear></div><br>
										<div class='grid-20'><label for="" class="label">Email filter</label>
					</div>
					<div class='grid-20'><select name=email_filter>
						<option value='all'>With/without Email</option>
						<option value='email_only'>With Email</option>
						<option value='no_email'>Without Email</option>
						</select>
					</div>
					<div class=clear></div><br>
					<div class='grid-20'><label for="" class="label">Select Points Range</label></div>
				<div class='grid-20'>
					<select  name=balance_range class='balance_range'>
						<option value="no_range">No Range</option>
						<option value="greater_than">Greater or equal to</option>
						<option value="less_than">Less or equal to</option>
						<option value="range">Greater than and less than</option>
					</select>
				</div>
					
					<div class='balance_input grid-10'><input type=text name=balance  /></div>
					
					<div class='balance_input_range grid-15'><label for="" class="label">Greater or equal to</label></div>
					<div class='balance_input_range grid-10'><input type=text name=balance_greater  /></div>
					<div class='balance_input_range grid-15'><label for="" class="label">But less or equal to</label></div>
					<div class='balance_input_range grid-10'><input type=text name=balance_less  /></div>
					<div class=clear></div>
					<br>
					
					<div class='prefix-20 grid-10'>	<input type="submit"  value="Submit"/>	</div>
					</form>
					<div class=clear></div>
				</div>
				
				<!-- end redeem points -->
				
				<!-- registerd -->
				<div class='no_padding registration_div'>
					<form action="" method="POST" enctype="" name="" id="">
							<input type="hidden" name="token_pl1"  value="<?php echo $_SESSION['token_pl1']; ?>" />
					<!--date range-->
					<div class='grid-20'><label for="" class="label">Registered between this date</label></div>
					<div class='grid-10'><input type=text name=from_date class=date_picker /></div>	
					<div class='grid-10'><label for="" class="label">And this date</label></div>
					<div class='grid-10'><input type=text name=to_date class=date_picker /></div>	
					<div class=clear></div><br>
					<!--insurer-->
					<div class='grid-20'><label for="" class="label">Select Patient Type</label>
						</div>
					<div class='grid-25'><select class=ptype2 name=ptype>
						<?php
							echo "<option value='all'>All Patient Types</option>";
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
							$error = "Unable to insurance companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>	
					<!--compnay covered-->
					<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
					<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company>
					<?php 
						echo "<option value='all'>All Companies</option>";
						/*if(isset($_SESSION['id']) and $_SESSION['id']!=''){
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from covered_company order by name";
							$error = "Unable to covered companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								//echo "<option value='$val'>$name</option>";
							}					
								//$val=$encrypt->encrypt("all");
								
						}*/
					?>
					</select>
					</div>	
					<div class=clear></div><br>
					<div class='grid-20'><label for="" class="label">Select report type</label>
					</div>
					<div class='grid-10'><select name=summary_detail>
						<option value='summary'>Summary</option>
						<option value='detailed'>Detailed</option>
						</select>
					</div>	
					<div class=clear></div><br>
										<div class='grid-20'><label for="" class="label">Email filter</label>
					</div>
					<div class='grid-20'><select name=email_filter>
						<option value='all'>With/without Email</option>
						<option value='email_only'>With Email</option>
						<option value='no_email'>Without Email</option>
						</select>
					</div>
					<div class=clear></div><br>
					<div class='prefix-20 grid-10'>	<input type="submit"  value="Submit"/></form>	</div>
				</div>	
				
				<!-- last seen -->
				<div class='no_padding last_seen_div'>
					<form action="" method="POST" enctype="" name="" id="">
							<input type="hidden" name="token_pl1"  value="<?php echo $_SESSION['token_pl1']; ?>" />
					<!--date range-->
					<div class='grid-20'><label for="" class="label">Last seen between this date</label></div>
					<div class='grid-10'><input type=text name=from_date class=date_picker /></div>	
					<div class='grid-10'><label for="" class="label">And this date</label></div>
					<div class='grid-10'><input type=text name=to_date class=date_picker /></div>	
					<div class=clear></div><br>
					<!--insurer-->
					<div class='grid-20'><label for="" class="label">Select treatment procedure</label>
						</div>
					<div class='grid-30'><select  name=procedure_done>
						<?php
							echo "<option value='all'>All Treatment Procedures</option>";
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from procedures order by name";
							$error = "Unable to get procedures";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>	
					<div class=clear></div><br>
					<!--insurer-->
					<div class='grid-20'><label for="" class="label">Select Patient Type</label>
						</div>
					<div class='grid-25'><select class=ptype2 name=ptype>
						<?php
							echo "<option value='all'>All Patient Types</option>";
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
							$error = "Unable to insurance companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";
							}
							
						
						?>
						</select>
					</div>	
					<!--compnay covered-->
					<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
					<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company>
					<?php 
						echo "<option value='all'>All Companies</option>";
						/*if(isset($_SESSION['id']) and $_SESSION['id']!=''){
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from covered_company order by name";
							$error = "Unable to covered companies";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								//echo "<option value='$val'>$name</option>";
							}					
								//$val=$encrypt->encrypt("all");
								
						}*/
					?>
					</select>
					</div>	
					<div class=clear></div><br>					
					<div class='prefix-20 grid-10'>	<input type="submit"  value="Submit"/></form>	</div>
				</div>
				

					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>