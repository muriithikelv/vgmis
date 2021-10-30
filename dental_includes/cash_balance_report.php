<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,85)){exit;}
echo "<div class='grid_12 page_heading'>CASH BALANCES REPORT</div>";
?>
<div class=grid-container>
<div class='grid-100 div_shower44'></div> 
<div class='grid-container cash_balance_content'>

<?php 

//send email


if(isset($_POST['token_cbr2']) and 	$_POST['token_cbr2']!='' and $_POST['token_cbr2']==$_SESSION['token_cbr2']){

		$_SESSION['token_cbr2']='';
		$i=0;
		$data=$_POST['send_email'];
		$n=count($data);
		//echo "sending";
		while($i < $n){
		//echo "$i --";
			//$var=html("$balance#$patient#$pnum#$email1#$email2");
			$result1=$encrypt->decrypt("$data[$i]");
			$result=explode('#',"$result1");
			$balance=number_format($result[0],2);
			$patient_name="$result[1]";
			$patient_no="$result[2]";
			$email1="$result[3]";
			$email2="$result[4]";
			$pid="$result[5]";
			$smtp_host='mail.molars.co.ke';
			$smtp_username='molars';
			$smtp_password='uO1ynN79m2';
			$from_email_address='test@molars.co.ke';
			$from_name='test user';
			$to_email_address="$email1";
			$to_email_name="$patient_name";
			$subject='Molars Dental Clinic - Balance Statement';
			
$pdf = new FPDF('p','mm',array(200,500));
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'Hello World!');
$pdf->Output();
ob_end_flush();
			/*$output="<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>$patient_name Balance Statement</title>
<style type='text/css' media='screen'>
body {
	background-color: #CCCCCC;
	margin: 0;
	padding: 0;
}
thead{
	background-color: #0F161E;
	
}

thead th {
    color: #FFFFFF;
    font-weight: 400;
    padding: 10px 2px;
	 border: 1px solid #15212F;
	 padding: 2px 2px;
	 font-weight: bold;
}
caption, .caption{

color: #1F232C;
background: linear-gradient(to bottom, #E9E9E9 0px, #CCCCCC 100%) repeat scroll 0 0 transparent;
padding: 5px 2px;
 font-weight: bold;
}
tr{background-color: #121923; color: #B0B3B6;}
#statement_tb tr th + th  +th,
#statement_tb tr td  +td  + td, .bal_ins{
    background-color: #A0D1E0;
	color: black;
}	
#statement_tb tr th + th  +th + th + th,
#statement_tb tr td  +td  + td + td + td, #statement_tb  #self_bal1,#statement_tb   #self_bal2{
    background-color: #93B3B7;
}
#statement_tb tr th + th  +th + th + th +th +th,
#statement_tb tr td  +td  + td + td + td +td +td,#statement_tb  #points_bal1,#statement_tb   #points_bal2{
    background-color: #53A3C2;
}
#statement_tb tr:hover  td ,#statement_tb tr:hover #self_bal2 ,#statement_tb tr:hover #points_bal2,#statement_tb tr:hover #self_bal1
,#statement_tb tr:hover #points_bal1{background-color: #D8D8D8;
	position: relative;
	color: black;
}
#statement_tb td { 
	border-collapse: separate;
    border-spacing: 0.5px;
}

#statement_tb #totals2{font-weight: bold;}
.normal_table{
width: 809px;
table-layout: fixed;
}
.normal_table tbody tr td{line-height: 1.5em;}
td.st_date{width: 66px;}
td.st_deb, td.st_cred, th.st_deb, th.st_cred{width: 88px;}
td.st_tx{width: 215px;}
tr.intro{background-color: none;}
</style>
</head>
<body><table>
				<tbody>
					<table><tbody><tr><td><img src='../dental_includes/dental-images/profile/molars_icon.png' </td><td>Molars Dental Clinic<br>3rd Flr Electricity House<br>Harambee Avenue City Centre<br>Phone: 020 242 8104 ,  0751 856 900<br>
						Email: $from_email_address<br>Website: www.molars.co.ke	<td></tr></tbody></table>
					<tr class=intro><td>Dear $patient_name,<br>You have an outstanding balance of $balance as shown in your statement below.<br>
					Please make arrangements to clear the same to enable us serve you better.<br>
					For any enquiry please don't hesitate to contact us.<br>
					Regards,<br>
					Molars Dental Clinic</td></tr>
					<tr><td>".email_pt_statement($pdo,$pid,$encrypt)."</td></tr>
				</tbody>
			</table>
			</body></html>";
				$body="$output";
					
		if($email1!=''){
			$to_email_address="$email1";
			send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid);
		}
		if($email2!=''){
			$to_email_address="$email2";
			send_email($mail, $smtp_host, $smtp_username, $smtp_password, $from_email_address, $from_name, $to_email_address,$to_email_name, $subject, $body, $pid);
		}		*/
			$i++;
		}

}


//get results
if(isset($_POST['token_cbr1']) and 	$_POST['token_cbr1']!='' and $_POST['token_cbr1']==$_SESSION['token_cbr1']){
		$_SESSION['token_cbr1']='';
		$exit_flag=false;
		$insurer='';
		$having=' having balance_left > 0 ';
		$exit_flag=$greater_and_less=$less_than=$greater_than=$no_range=false;
		$caption="Patient's with cash balance";
		$sql=$error=$s='';$placeholders=array();
		//check if balance amount is set
		if(!$exit_flag and ($_POST['balance_range']=='greater_than' or $_POST['balance_range']=='less_than') and 
			(!isset($_POST['balance']) or $_POST['balance']=='')){	
				$result_class="error_response";
				$result_message="Please specify the balance amount";
				$exit_flag=true;
		}	
		
		//check if ranges are set for range balance
		if(!$exit_flag and  $_POST['balance_range']=='range'  and 
			(!isset($_POST['balance_greater']) or $_POST['balance_greater']=='' or !isset($_POST['balance_less']) or $_POST['balance_less']=='')){	
				$result_class="error_response";
				$result_message="Please specify the balance range";
				$exit_flag=true;
		}
		
		//ptypr criteria
		if(!$exit_flag and  $_POST['ptype']!='all'){
			$insurer_id=$encrypt->decrypt($_POST['ptype']);
			$insurer = " and patient_details_a.type=:insurer_id ";
			$placeholders[':insurer_id']=$insurer_id;
		}
		
		//set having criteria
		if(!$exit_flag and  $_POST['balance_range']=='range'){
			$greater_and_less=true;
			$greater_than1=$_POST['balance_greater'];
			$less_than1=$_POST['balance_less'];
			/*$having = " having balance_left >=:balance_greater and  balance_left <=:balance_less ";
			$placeholders[':balance_greater']=$_POST['balance_greater'];
			$placeholders[':balance_less']=$_POST['balance_less'];*/
			$v1=html($_POST['balance_greater']);
			$v2=html($_POST['balance_less']);
			$caption="Patient's with cash balance between ".number_format($v1,2)." and ".number_format($v2,2);
		}
		
		//greater than
		elseif(!$exit_flag and  $_POST['balance_range']=='greater_than'){
			$greater_than=true;
			$greater_than_amount=$_POST['balance'];/*
			$having = " having balance_left >=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$v=html($_POST['balance']);
			$caption="Patient's with cash balance greater than ".number_format($v,2);
		}
		
		//less than
		elseif(!$exit_flag and  $_POST['balance_range']=='less_than'){
			$less_than=true;
			$less_than_amount=$_POST['balance'];
			/*$having = " having balance_left >0 and balance_left <=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$v=html($_POST['balance']);
			$caption="Patient's with cash balance below ".number_format($v,2);
		}
		
		//no range
		elseif(!$exit_flag and  $_POST['balance_range']=='no_range'){
			$no_range=true;
			//$less_than_amount=$_POST['balance'];
			/*$having = " having balance_left >0 and balance_left <=:balance";
			$placeholders[':balance']=$_POST['balance'];*/
			$caption="Patient's with cash balance ";
		}
		
		if(!$exit_flag){
			/*$sql="select patient_details_a.pid,patient_details_a.first_name, patient_details_a.middle_name, patient_details_a.last_name, patient_details_a.mobile_phone,
					patient_details_a.biz_phone, patient_details_a.email_address, patient_details_a.email_address_2, insurance_company.name,
					patient_details_a.patient_number ,b.sum_cost,c.sum_paid,d.credit_transfered,
					(ifnull(b.sum_cost,0) - ifnull(c.sum_paid,0) + ifnull(d.credit_transfered,0)) as balance_left
					from patient_details_a join (select sum(authorised_cost) as sum_cost,pid from tplan_procedure where pay_type = 2 
								and status > 0 group by pid) as b on b.pid=patient_details_a.pid $insurer
					left join insurance_company on patient_details_a.type=insurance_company.id  
					left join (select pid,sum(amount) as sum_paid from payments where pay_type!=7 and pay_type!=8 group by pid) as c 
										on c.pid=patient_details_a.pid
					left join (select tx_number,sum(amount) as credit_transfered from payments where pay_type=10  group by tx_number) as d 
										on d.tx_number=patient_details_a.pid
					$having order by patient_details_a.email_address, patient_details_a.email_address_2
					";
				$error="unable to get cash balance";					
				$s = select_sql($sql, $placeholders, $error, $pdo);	*/
				//echo "count is ".$s->rowCount();exit;
				
				$invoices_array=$_SESSION['balance_invoice']=array();
					//now check if the pt is from the mentioned insuer
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select patient_number,pid,first_name,middle_name,last_name,mobile_phone, biz_phone, email_address, 
							email_address_2,b.name as company_covered,c.name as insurer 
							from patient_details_a a 
							left join covered_company b on a.company_covered=b.id 
							left join insurance_company c on a.type=c.id ";
					$error2="Error: Unable to pt details from uniq ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							$total_cost=$cash_cost2=$cash_cost1=0;
							//now get sum of cash treatments that have been started
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( tplan_procedure.authorised_cost )  AS cost FROM tplan_procedure 
									WHERE tplan_procedure.pid =:pid and pay_type=2 and status > 0";
							$placeholders3[':pid']=$row2['pid'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$cash_cost1=html($row3['cost']);}
							
							//now get sum of invoices where patient will pay the difference
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( unauthorised_cost - authorised_cost ) as cost fROM tplan_procedure
									WHERE pid =:pid and pay_type=1";
							$placeholders3[':pid']=$row2['pid'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$cash_cost2=html($row3['cost']);}							
							
							$total_cost=$cash_cost2  + $cash_cost1;
							
							//now amount paid
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( amount ) as amount FROM payments where pid=:pid and invoice_id=0";
							$placeholders3[':pid']=$row2['pid'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$amount_paid=html($row3['amount']);}
							
							//check if fully paid
							$endelea=false;
							$balance=$total_cost - $amount_paid;
							if($balance <= 0){continue;}
							if($no_range){$endelea=true;}
							elseif($greater_than and ($balance >= $greater_than_amount)){$endelea=true;}
							elseif($less_than and ($balance <=  $less_than_amount)){$endelea=true;}
							elseif($greater_and_less and $balance >= $greater_than1 and $balance <= $less_than1 ){$endelea=true;}
							if($endelea){

								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$pnum=html("$row2[patient_number]");
								$company=html("$row2[company_covered]");
								$insurer=html("$row2[insurer]");
								$mobile_no=html("$row2[mobile_phone]");
								$biz_no=html("$row2[biz_phone]");
								$email1=html("$row2[email_address]");
								$email2=html("$row2[email_address_2]");
								$balance=html("$balance");
								$pid=html("$row2[pid]");
								if($company!=''){$company="$insurer - $company";}
								else{$company="$insurer";}
								$invoices_array[]=array('patient'=>"$patient",  'pnum'=>"$pnum", 'company'=>"$company", 
										'mobile_no'=>"$mobile_no",'biz_no'=>"$biz_no", 'email1'=>"$email1",'email2'=>"$email2",
									 'balance'=>"$balance", 'pid'=>"$pid");
						
								}
							
						}//end s2

						
					}//end if
					
					
				
				if(count($invoices_array) > 0){
					?>
						<form class='' action="dental_b/" target="_new" method="POST" enctype="" name="" id="">
							<?php $token = form_token(); $_SESSION['token_cbr2'] = "$token";  ?>
							<input type="hidden" name="token_cbr2"  value="<?php echo $_SESSION['token_cbr2']; ?>" />
					
					<?php
					$i=$total=0;
					foreach($invoices_array as $row){
						if($i==0){
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
							<th class=cbr_balance>BALANCE</th>
							<th class=cbr_send_email>EMAIL</th>
							</tr></thead><tbody>";						
						}
						$i++;
						$patient=ucfirst(html("$row[patient]"));
						$pnum=html("$row[pnum]");
						$type=html("$row[company]");
						$mobile=html("$row[mobile_no]");
						$biz=html("$row[biz_no]");
						$email1=html("$row[email1]");
						$email2=html("$row[email2]");
						$balance=html("$row[balance]");
						$pid=html($row['pid']);
						$pid_encrypt=$encrypt->encrypt("$pid");
						$var=html("$balance#$patient#$pnum#$email1#$email2#$pid");
						$var=$encrypt->encrypt("$var");
						$total=$total + $balance;
						$background_color='';
						if($email1=='' and $email2==''){$background_color='light_blue_background';}
						echo "<tr class=$background_color><td>$i</td><td>$patient</td><td>$pnum</td><td>$type</td><td>$mobile</td><td>$biz</td><td>$email1</td><td>$email2</td><td>
							<input type=hidden value='$pid_encrypt' /><a href='' class='link_color pt_statement_a'>".
							number_format($balance,2)."</a></td><td><input type=checkbox name='send_email[]' class=email_balance value=$var /></td></tr>";
					}
					echo "<tr class=total_background><td colspan=8>TOTAL</td><td colspan=2>".number_format($total,2)."</td></tr></tbody></table>";
					echo "<div class='grid-100'><input type=button class='button_style check_all put_right check_all_email' value='Check All' /></div><br>";
					echo "<div class='grid-100'><input type=submit class=put_right value='Send Balance in Email' /></form></div>";
					//now output the labs to be paid
					exit;
					
				}
				else{echo "<label  class=label>There is no cash balances that match the selected criteria</label>";}
		}	
}
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form class='patient_form' action="cash_balance_report"  method="POST" enctype="" name="" id="">
		
			<div class='grid-15 '><label for="" class="label">Select Patient Type</label></div>
				<div class='grid-25'>
					<?php $token = form_token(); $_SESSION['token_cbr1'] = "$token";  ?>
					<input type="hidden" name="token_cbr1"  value="<?php echo $_SESSION['token_cbr1']; ?>" />
					<?php 
						echo "<select name=ptype>";
						echo "<option value='all'>All Patient Types</option>";
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company  order by name";
						$error = "Unable to get insurance companies";
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
				<div class='grid-15'><label for="" class="label">Select Balance Range</label></div>
				<div class='grid-20'>
					<select  name=balance_range class='balance_range'>
						<option value="no_range">No Range</option>
						<option value="greater_than">Greater or equal to</option>
						<option value="less_than">Less or equal to</option>
						<option value="range">Greater than and less than</option>
					</select>
				</div>
				
				<div class='balance_input grid-15'><label for="" class="label">Balance Amount</label></div>
				<div class='balance_input grid-10'><input type=text name=balance  /></div>
				
				<div class='balance_input_range grid-25'><label for="" class="label">Balance amount Greater or equal to</label></div>
				<div class='balance_input_range grid-10'><input type=text name=balance_greater  /></div>
				<div class='balance_input_range grid-15'><label for="" class="label">But less or equal to</label></div>
				<div class='balance_input_range grid-10'><input type=text name=balance_less  /></div>
				<div class=clear></div>
				<br>
				<div class='prefix-15 grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>