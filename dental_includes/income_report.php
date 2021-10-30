<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,77)){exit;}
echo "<div class='grid_12 page_heading'>INCOME REPORT</div>";
?>
<div class=grid-container>
<?php
if(isset($_SESSION['token_ir1']) and isset($_POST['token_ir1']) and $_POST['token_ir1']==$_SESSION['token_ir1']){
	$exit_flag=false;
	$_SESSION['token_ir1']='';
	$total_sum_cash=$total_sum_invoice_in_period=$total_sum_out_of_period=0;
	$income_report=$encrypt->decrypt("$_POST[income_report]");
	
	//check if password is set
	if(!isset($_POST['user_pass']) or $_POST['user_pass']=='' ){
			$message="Please specify your password to generate the report";
			$error_class="error_response";
			$exit_flag=true;
	} 
	
	//check if password is correct
	if(!$exit_flag and isset($_POST['user_pass']) and $_POST['user_pass']!='' ){
			$password = hash_hmac('sha1', $_POST['user_pass'], $salt);
			$sql=$error=$s='';$placeholders=array();
			$sql = "select id from users where password = :password and id = :user_id ";
			$placeholders[':user_id'] = $_SESSION['id'];
			$placeholders[':password'] = "$password";
			$error = "Unable to verify user";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount()!=1){
				$message="Incorrect password";
				$error_class="error_response";
				$exit_flag=true;
			}	
	}	
	
	//check if date is set for range
	if(!$exit_flag and $income_report==3 or $income_report==4 or $income_report==5){
		if(!isset($_POST['from_date1']) or !isset($_POST['to_date']) or $_POST['from_date1']=='' or $_POST['to_date']==''){
			$message="Unable to generate report as date range was not properly selected";
			$error_class="error_response";
			$exit_flag=true;
		}
		$_POST['from_date']=$_POST['from_date1'];
	} 
	//check if date is set for single day
	elseif(!$exit_flag and ($income_report==1 or $income_report==2)){
		if(!isset($_POST['from_date']) or $_POST['from_date']==''){
			$message="Unable to generate report as the date was not properly selected";
			$error_class="error_response";
			$exit_flag=true;
		}
	}
	//check if pay type is set
	if(!$exit_flag and $income_report==5){
		if(!isset($_POST['pay_type']) or $_POST['pay_type']==''){
			$message="Unable to generate report as pay type was not properly selected";
			$error_class="error_response";
			$exit_flag=true;
		}
	}
	
	//payment type report
	if(!$exit_flag and $income_report == 5){
		$pay_type=$encrypt->decrypt("$_POST[pay_type]");
		//echo "$pay_type is";
		$sql=$error=$s='';$placeholders=array();
		/*select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number from payments a, patient_details_a b, users c, payment_types d
			where a.pay_type=:pay_type and a.when_added >=:from_date	and a.when_added <=:to_date and a.pid=b.pid and 
			a.created_by=c.id and d.id=a.pay_type*/
		if($pay_type!=10){
			$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
				c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number 
				from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=:pay_type and a.when_added >=:from_date	and a.when_added <=:to_date
				left join users c on a.created_by=c.id
				left join payment_types d on d.id=a.pay_type order by a.id";
			$error="Unable to get pay type report";
			$placeholders[':pay_type']=$pay_type;
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
		}
		//this is for credit transfer
		elseif($pay_type==10){
			$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
				c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number , e.first_name, e.middle_name, e.last_name, e.patient_number
				from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=:pay_type and a.when_added >=:from_date	and a.when_added <=:to_date
				join patient_details_a e on e.pid=a.tx_number
				left join users c on a.created_by=c.id
				left join payment_types d on d.id=a.pay_type order by a.id";
			$error="Unable to get pay type report";
			$placeholders[':pay_type']=$pay_type;
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
		}
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			$i=0;
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			if($pay_type==3 or $pay_type==4 or $pay_type==5 or $pay_type==6 or $pay_type==9 ){
				if($pay_type==3){$transaction_header="CHEQUE NUMBER";}
				if($pay_type==4){$transaction_header="MPESA TRANSACTION NUMBER";}
				if($pay_type==5){
					$transaction_header="VISA TRANSACTION NUMBER";
					$total_patient_payment=$total_charge_amount=$count1=0;
					//get banks first
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select name,percent_charge,id from visa_banks order by name";
					$error2="Unable to get bank list";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					echo "<table class='normal_table'><caption>VISA TRANSACTION BANK CHARGES BETWEEN $from_date AND $to_date</caption><thead>
					<tr><th class=vbc_count></th>
					<th class=vbc_bank>BANK</th>
					<th class=vbc_charge>Tx. CHARGE</th>
					<th class=vbc_pay_amount>PATIENT PAYMENT</th>
					<th class=vbc_charge_amount>Tx. CHARGE AMOUNT</th>
					<th class=vbc_credit>CREDIT AMOUNT</th>
					</tr></thead><tbody>";	
					foreach($s2 as $row2){
						$count1++;
						$bank_name=html($row2['name']);
						$charge=html($row2['percent_charge']);
						//now get payments for each bank
						$sql21=$error21=$s21='';$placeholders21=array();
						$sql21="select sum(a.amount) as amount
							from payments a where a.bank_id=:bank_id and a.pay_type=5 and
							a.when_added >=:from_date	and a.when_added <=:to_date
							";
						$error21="Unable to get visa pay bank amount";
						$placeholders21[':bank_id']=$row2['id'];
						$placeholders21[':from_date']="$from_date";
						$placeholders21[':to_date']="$to_date";
						$s21 = 	select_sql($sql21, $placeholders21, $error21, $pdo);
						foreach($s21 as $row21){
							$amount=html($row21['amount']);
							if($amount > 0){
								$commision=$charge * $amount / 100;
								echo "<tr><td>$count1</td><td>$bank_name</td><td>$charge %</td><td>".number_format($amount,2)."</td>
								<td>".number_format($commision,2)."</td><td>".number_format($amount - $commision,2)."</td></tr>";
								$total_patient_payment=$total_patient_payment + $amount;
								$total_charge_amount=$total_charge_amount + $commision;
								//$total_credit=$total_credit + $amount - $commision;
							}
						}
						$count1++;
					}
					echo "<tr class=total_background><td colspan=3>TOTAL</td><td>".number_format($total_patient_payment,2)."</td>
								<td>".number_format($total_charge_amount,2)."</td><td>".number_format($total_patient_payment - $total_charge_amount,2)."</td></tr>";
								
					echo "</table<br>";
					//get summary by bank showing commision

				}
				if($pay_type==6){$transaction_header="WAIVE COMMENTS";}
				if($pay_type==9){$transaction_header="EFT NUMBER";}
				if($pay_type==10){$transaction_header="DEBITED PATIENT";}
				$total=0;
				foreach($s as $row){
					if($i==0){
						$pay_type_name=html($row['name']);
						$caption=strtoupper("$pay_type_name payments between $from_date and $to_date");
						echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr><th class=irp_count></th><th class=irp_date>DATE</th><th class=irp_pnum>PATIENT No.</th><th class=irp_pname>PATIENT NAME</th>
						<th class=irp_tx>$transaction_header</th><th class=irp_amount>AMOUNT</th><th class=irp_receiver>RECEIVED BY</th>
						<th class=irp_receipt>RECEIPT NUMBER</th></tr>
						</thead><tbody>";
						
					}
					$i++;
					$patient_number=html($row['patient_number']);
					$date=html($row['when_added']);
					$patient_name=ucfirst(html("$row[4] $row[5] $row[6]"));
					$created_by=ucfirst(html("$row[7] $row[8] $row[9]"));
					$receipt_num=html($row['receipt_num']);
					$amount=html($row['amount']);
					$tx_number=html($row['tx_number']);
					$total = $total + $amount;
					$amount=number_format($amount,2);
					echo "<tr><td>$i</td><td>$date</td><td>$patient_number</td><td>$patient_name</td><td>$tx_number</td><td>$amount</td><td>$created_by</td><td>$receipt_num</td></tr>";
					
				}
				echo "<tr><td colspan=5>TOTAL</td><td>".number_format($total,2)."</td><td colspan=2></td></tr></tbody></table>";
				exit;
			}
			//now show for points and cash
			elseif($pay_type==2 or $pay_type==8){
				$total=0;
				foreach($s as $row){
					if($i==0){
						$pay_type_name=html($row['name']);
						$caption=strtoupper("$pay_type_name payments between $from_date and $to_date");
						echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr><th class=irp_count2></th><th class=irp_date2>DATE</th><th class=irp_pnum2>PATIENT No.</th><th class=irp_pname2>PATIENT NAME</th>
						<th class=irp_amount2>AMOUNT</th><th class=irp_receiver2>RECEIVED BY</th>
						<th class=irp_receipt2>RECEIPT NUMBER</th></tr>
						</thead><tbody>";
						
					}
					$i++;
					$patient_number=html($row['patient_number']);
					$date=html($row['when_added']);
					$patient_name=ucfirst(html("$row[4] $row[5] $row[6]"));
					$created_by=ucfirst(html("$row[7] $row[8] $row[9]"));
					$receipt_num=html($row['receipt_num']);
					$amount=html($row['amount']);
					$total = $total + $amount;
					$amount=number_format($amount,2);
					echo "<tr><td>$i</td><td>$date</td><td>$patient_number</td><td>$patient_name</td><td>$amount</td><td>$created_by</td><td>$receipt_num</td></tr>";
					
				}
				echo "<tr><td colspan=4>TOTAL</td><td>".number_format($total,2)."</td><td colspan=2></td></tr></tbody></table>";
				exit;
			}	
			//now show for credit transfer
			elseif($pay_type==10){
				$total=0;
				foreach($s as $row){
					if($i==0){
						$pay_type_name=html($row['name']);
						$caption=strtoupper("$pay_type_name payments between $from_date and $to_date");
					echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr><th class=irp_count3></th><th class=irp_date3>DATE</th><th class=irp_pnum3>PATIENT No.</th><th class=irp_pname3>PATIENT NAME</th>
						<th class=irp_db_pnum3>DEBITED PATIENT No.</th><th class=irp_db_name3>DEBITED PATIENT NAME</th>
						<th class=irp_amount3>AMOUNT</th><th class=irp_receiver3>TRANSFERED BY</th>
						<th class=irp_receipt3>RECEIPT NUMBER</th></tr>
						</thead><tbody>";
						
					}
					$i++;
					$patient_number=html($row[11]);
					$date=html($row['when_added']);
					$patient_name=ucfirst(html("$row[4] $row[5] $row[6]"));
					$created_by=ucfirst(html("$row[7] $row[8] $row[9]"));
					$debit_name=ucfirst(html("$row[12] $row[13] $row[14]"));
					$debit_pt=html($row[15]);
					$receipt_num=html($row['receipt_num']);
					$amount=html($row['amount']);
					$total = $total + $amount;
					$amount=number_format($amount,2);
					echo "<tr><td>$i</td><td>$date</td><td>$patient_number</td><td>$patient_name</td><td>$debit_pt</td><td>$debit_name</td><td>$amount</td><td>$created_by</td><td>$receipt_num</td></tr>";
					
				}
				echo "<tr><td colspan=6>TOTAL</td><td>".number_format($total,2)."</td><td colspan=2></td></tr></tbody></table>";
				exit;
			}			
		}
		else{echo "<div class='grid-100 label'>There are no payment records for the selected search criteria</div><br>";}
					
	}
	
	//date range and single date detail  report
	elseif(!$exit_flag and $income_report==1 or $income_report==3 ){
		if($income_report==3 ){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			$caption="NONE INSURANCE PAYMENTS BETWEEN $from_date and $to_date";
		}
		elseif($income_report==1){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['from_date']);
			$caption="NONE INSURANCE PAYMENTS ON $from_date";
		}		
		//get self payments for non points into array
		$self_pay_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number ,a.id, b.internal_patient, a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.when_added >=:from_date and a.when_added <=:to_date
			and a.invoice_id=0 and a.pay_type!=10
			left join users c on a.created_by=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		//$count=0;
		foreach($s as $row){
		//	$count++;
		//	echo "<br>$row[patient_number]--$row[name]--$row[amount]--$row[receipt_num]";
			$self_pay_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row['patient_number'] , 'pname'=>"$row[4] $row[5] $row[6]", 
											'created_by'=>"$row[7] $row[8] $row[9]", 'tx_details'=>$row['tx_number'] ,'amount'=>$row['amount'],
											'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],'pay_type'=>$row['name'],
											'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
			
		}
		
		
		//get deleted payments that match above criteria and show them as income
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number ,a.id, b.internal_patient, a.pay_type,a.when_deleted,a.deleter
			from deleted_payments a join patient_details_a b on a.pid=b.pid  and a.when_added >=:from_date and a.when_added <=:to_date
			and a.invoice_id=0 and a.pay_type!=10
			left join users c on a.created_by=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		//$count=0;
		foreach($s as $row){
			//this date is from when delete are included in income report sot hat the income report remains constant
			if($row['when_deleted'] < '2017-02-13'){continue;}
		//	$count++;
		//	echo "<br>$row[patient_number]--$row[name]--$row[amount]--$row[receipt_num]";
			$self_pay_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row['patient_number'] , 'pname'=>"$row[4] $row[5] $row[6]", 
											'created_by'=>"$row[7] $row[8] $row[9]", 'tx_details'=>$row['tx_number'] ,'amount'=>$row['amount'],
											'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],'pay_type'=>$row['name'],
											'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);

		}
		
		
		//get credit notes 
		$credit_note_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number ,a.id, b.internal_patient, a.pay_type,a.when_deleted,a.deleter
			from deleted_payments a join patient_details_a b on a.pid=b.pid  and a.when_deleted >=:from_date and a.when_deleted <=:to_date
			and a.invoice_id=0 and a.pay_type!=10
			left join users c on a.deleter=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		//$count=0;
		foreach($s as $row){
			//this date is from when delete are included in income report sot hat the income report remains constant
			if($row['when_deleted'] < '2017-02-13'){continue;}
			//credit note will only appear on the date of the dleetion and not he day of the payment
				/*
				//get names of deleter	
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select first_name,middle_name, last_name from users where id=:deleter_id";
				$error2="Unable to get pay type report self";
				$placeholders2[':deleter_id']=$row['deleter'];
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){$deleter=ucfirst(html("$row2[first_name]  $row2[middle_name]  $row2[last_name]"));}
				*/
				//this will go to credit note table
				$credit_note_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row['patient_number'] , 'pname'=>"$row[4] $row[5] $row[6]", 
												'deleter'=>"$row[7] $row[8] $row[9]", 'tx_details'=>$row['tx_number'] ,'amount'=>$row['amount'],
												'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],'pay_type'=>$row['name'],
												'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type'],'when_deleted'=>$row['when_deleted']);
			
		}
		
		
		
		
		
		
	//	echo "---$count--";
	//	exit;
		//get self payments for points into array
		$sql=$error=$s='';$placeholders=array();
		/*$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number , e.first_name, e.middle_name, e.last_name, 
			e.patient_number, a.id, b.internal_patient,a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=10 and a.when_added >=:from_date	and a.when_added <=:to_date
			and a.invoice_id=0
			join patient_details_a e on e.pid=a.tx_number
			left join users c on a.created_by=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";*/
		$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number , 
			 a.id, b.internal_patient,a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=10 and a.when_added >=:from_date	and a.when_added <=:to_date
			and a.invoice_id=0
			left join users c on a.created_by=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report points";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$self_pay_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row[11] , 'pname'=>"$row[4] $row[5] $row[6]", 
											'created_by'=>"$row[7] $row[8] $row[9]", 'tx_details'=>"DEBITED: $row[15] - $row[12] $row[13] $row[14]" ,
											'amount'=>$row['amount'],'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],
											'pay_type'=>$row['name'],'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
			
		}
		
		if(count($self_pay_array) > 0){
			//now sort by id
			// Obtain list of IDs for sorting
			foreach ($self_pay_array as $key => $row) {
				$id[$key]  = $row['id'];
			}
			
			// Sort the data with when_added
			array_multisort($id, SORT_ASC, $self_pay_array);
			
			$total_sum_invoice_in_period=$total_sum_out_of_period=$total=$total_points=$total_xray=$total_cadcam=$total_molars=$i=0;
			$total_cash=$total_mpesa=$total_visa=$total_cheque=$total_eft=$total_waive=$total_credit_transfer=0;
			foreach($self_pay_array as $row){
				if($i==0){
					$caption=strtoupper("$caption");
				echo "<table class='normal_table'><caption>$caption</caption><thead>
					<tr><th class=irp_count4></th><th class=irp_date4>DATE</th><th class=irp_pnum4>PATIENT No.</th><th class=irp_pname4>PATIENT NAME</th>
					<th class=irp_pay_type4>PAYMENT TYPE</th><th class=irp_tx4>TRANSACTION No./DETAILS</th>
					<th class=irp_amount4>AMOUNT</th><th class=irp_receiver4>RECEIVED BY</th>
					<th class=irp_receipt4>RECEIPT NUMBER</th></tr>
					</thead><tbody>";
					
				}
				$i++;
				$date=html($row['when_added']);
				$patient_number=html($row['pid']);
				$patient_name=ucfirst(html("$row[pname]"));
				$created_by=ucfirst(html("$row[created_by]"));
				$tx_details=html("$row[tx_details]");
				$amount=html($row['amount']);
				$receipt_num=html($row['receipt_num']);
				$pay_type=html($row['pay_type']);
				//points
				if($row['pay_type_id']==8){$total_points = $total_points + $amount;}
				//waive
				elseif($row['pay_type_id']==6){$total_waive = $total_waive + $amount;}
				//cash
				elseif($row['pay_type_id']==2){$total_cash = $total_cash + $amount;}
				//mpesa
				elseif($row['pay_type_id']==4){$total_mpesa = $total_mpesa + $amount;}
				//visa
				elseif($row['pay_type_id']==5){$total_visa = $total_visa + $amount;}
				//cheque
				elseif($row['pay_type_id']==3){$total_cheque = $total_cheque + $amount;}
				//eft
				elseif($row['pay_type_id']==9){$total_eft = $total_eft + $amount;}
				//credit transfer
				elseif($row['pay_type_id']==10){$total_credit_transfer = $total_credit_transfer + $amount;}
				
				//xray ref
				if($row['patient_type']==1){$total_xray = $total_xray + $amount;}
				//cadcam
				elseif($row['patient_type']==2){$total_cadcam = $total_cadcam + $amount;}
				//molars pt
				//elseif($row['patient_type']==0){$total_molars = $total_molars + $amount;}
				$unformatted_amount=$amount;
				$amount=number_format($amount,2);
				$bgcolor='';
				if($row['patient_type']==1){
					//for xray referral
					$bgcolor='blue_shade_background';
				}
				elseif($row['patient_type']==2){
					//for cadcam referral
					$bgcolor='light_blue_background';
				}
				else{
					//for molars internal patient
					$total_molars = $total_molars + $unformatted_amount;
				}
				echo "<tr class=$bgcolor><td>$i</td><td>$date</td><td>$patient_number</td><td>$patient_name</td><td>$pay_type</td><td>$tx_details</td><td>$amount</td><td>$created_by</td><td>$receipt_num</td></tr>";
				
			}
			//$total_molars = $total_cash + $total_mpesa + $total_visa + $total_cheque + $total_eft;
			//show sub totals for different pay types
			echo "<tr><td colspan=6>TOTAL POINTS  </td><td>".number_format($total_points,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CASH  </td><td>".number_format($total_cash,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CHEQUES  </td><td>".number_format($total_cheque,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL MPESA  </td><td>".number_format($total_mpesa,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL VISA  </td><td>".number_format($total_visa,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL WAIVES  </td><td>".number_format($total_waive,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL EFT  </td><td>".number_format($total_eft,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CREDIT TRANSFER  </td><td>".number_format($total_credit_transfer,2)."</td><td colspan=2></td></tr>";
			
			//show total for patient category
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR CUSPID </td><td>".number_format($total_molars,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR X-RAY REFERALS</td><td>".number_format($total_xray,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR CADCAM REFERALS</td><td>".number_format($total_cadcam,2)."</td><td colspan=2></td></tr>";
			echo "<tr class=total_background><td colspan=6>TOTAL AMOUNT COLLECTED</td><td>".number_format(($total_molars + $total_xray + $total_cadcam),2)."</td><td colspan=2></td></tr></tbody></table>";
			$total_sum_cash = $total_molars + $total_xray + $total_cadcam;
		}	
		
		//credit note table
		//this will go to credit note table
		if(count($credit_note_array) > 0){
			//now sort by id
			// Obtain list of IDs for sorting
			foreach ($credit_note_array as $key => $row) {
				$id[$key]  = $row['id'];
			}
			
			// Sort the data with when_added
			array_multisort($id, SORT_ASC, $self_pay_array);
			
			$total_sum_invoice_in_period=$total_sum_out_of_period=$total=$total_points=$total_xray=$total_cadcam=$total_molars=$i=0;
			$total_cash=$total_mpesa=$total_visa=$total_cheque=$total_eft=$total_waive=$total_credit_transfer=0;
			foreach($credit_note_array as $row){
				if($i==0){
					$caption=strtoupper("CREDIT NOTES");
				echo "<table class='normal_table'><caption>$caption</caption><thead>
					<tr><th class=irp_count4></th><th class=irp_date4>DATE<br>DELETED</th><th class=irp_pnum4>PAYMENT<br>DATE</th><th class=irp_pname4>PATIENT NAME</th>
					<th class=irp_pay_type4>PAYMENT TYPE</th><th class=irp_tx4>TRANSACTION No./DETAILS</th>
					<th class=irp_amount4>AMOUNT</th><th class=irp_receiver4>DELETED BY</th>
					<th class=irp_receipt4>RECEIPT NUMBER</th></tr>
					</thead><tbody>";
					
				}
				$i++;
				$date=html($row['when_added']);
				$when_deleted=html($row['when_deleted']);
				$patient_number=html($row['pid']);
				$patient_name=ucfirst(html("$row[pname]"));
				$deleter=ucfirst(html("$row[deleter]"));
				$tx_details=html("$row[tx_details]");
				$amount=html($row['amount']);
				$receipt_num=html($row['receipt_num']);
				$pay_type=html($row['pay_type']);
				/*//points
				if($row['pay_type_id']==8){$total_points = $total_points + $amount;}
				//waive
				elseif($row['pay_type_id']==6){$total_waive = $total_waive + $amount;}
				//cash
				elseif($row['pay_type_id']==2){$total_cash = $total_cash + $amount;}
				//mpesa
				elseif($row['pay_type_id']==4){$total_mpesa = $total_mpesa + $amount;}
				//visa
				elseif($row['pay_type_id']==5){$total_visa = $total_visa + $amount;}
				//cheque
				elseif($row['pay_type_id']==3){$total_cheque = $total_cheque + $amount;}
				//eft
				elseif($row['pay_type_id']==9){$total_eft = $total_eft + $amount;}
				//credit transfer
				elseif($row['pay_type_id']==10){$total_credit_transfer = $total_credit_transfer + $amount;}
				
				//xray ref
				if($row['patient_type']==1){$total_xray = $total_xray + $amount;}
				//cadcam
				elseif($row['patient_type']==2){$total_cadcam = $total_cadcam + $amount;}
				//molars pt
				//elseif($row['patient_type']==0){$total_molars = $total_molars + $amount;}*/
				$unformatted_amount=$amount;
				$amount=number_format($amount,2);
				$bgcolor='';
				if($row['patient_type']==1){
					//for xray referral
					$bgcolor='blue_shade_background';
				}
				elseif($row['patient_type']==2){
					//for cadcam referral
					$bgcolor='light_blue_background';
				}
				else{
					//for molars internal patient
					$total_molars = $total_molars + $unformatted_amount;
				}
				echo "<tr class=$bgcolor><td>$i</td><td>$when_deleted</td><td>$date</td><td>$patient_name - $patient_number</td><td>$pay_type</td><td>$tx_details</td><td>$amount</td><td>$deleter</td><td>$receipt_num</td></tr>";
				
			}
			echo "</tbody></table>";
			/*//$total_molars = $total_cash + $total_mpesa + $total_visa + $total_cheque + $total_eft;
			//show sub totals for different pay types
			echo "<tr><td colspan=6>TOTAL POINTS  </td><td>".number_format($total_points,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CASH  </td><td>".number_format($total_cash,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CHEQUES  </td><td>".number_format($total_cheque,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL MPESA  </td><td>".number_format($total_mpesa,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL VISA  </td><td>".number_format($total_visa,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL WAIVES  </td><td>".number_format($total_waive,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL EFT  </td><td>".number_format($total_eft,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL CREDIT TRANSFER  </td><td>".number_format($total_credit_transfer,2)."</td><td colspan=2></td></tr>";
			
			//show total for patient category
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR MOLARS </td><td>".number_format($total_molars,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR X-RAY REFERALS</td><td>".number_format($total_xray,2)."</td><td colspan=2></td></tr>";
			echo "<tr><td colspan=6>TOTAL AMOUNT FOR CADCAM REFERALS</td><td>".number_format($total_cadcam,2)."</td><td colspan=2></td></tr>";
			echo "<tr class=total_background><td colspan=6>TOTAL AMOUNT COLLECTED</td><td>".number_format(($total_molars + $total_xray + $total_cadcam),2)."</td><td colspan=2></td></tr></tbody></table>";
			$total_sum_cash = $total_molars + $total_xray + $total_cadcam;*/
		}
		
		
		//now get invoices raised on that day
		
			//get details from unique_inv_table first
			$invoices_array=array();
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT id,pid,invoice_number,when_raised,added_by FROM unique_invoice_number_generator WHERE 
				when_raised >=:from_date AND when_raised <=:to_date";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			//echo "$_POST[from_date]--$_POST[to_date]--".$s1->rowCount();
			foreach($s1 as $row1 ){
				$invoice_cost=$billed_cost=$amount_paid=$doctor='';
				//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer 
						from patient_details_a a 
						left join covered_company b on a.company_covered=b.id 
						left join insurance_company c on a.type=c.id where pid=:pid";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) as billed_cost,sum( tplan_procedure.authorised_cost )
								as authorised_cost, ifnull( co_payment.amount, 0 ) as co_payment
								FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							//check if amount authorised is 0
							if($row3['authorised_cost'] > 0){
								$row3['authorised_cost'] = $row3['authorised_cost']- $row3['co_payment'];
								$authorised_cost=html($row3['authorised_cost']);
							}
							else{$authorised_cost=html($row3['authorised_cost']);}
							$billed_cost=$row3['billed_cost'] -  $row3['co_payment'];
							$billed_cost=html($billed_cost);
						}
						
						//now amount paid
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$paid=html($row3['amount']);}
						
						//get doctor who raised invoice
							$doctor='';
							$sql4=$error3=$s3='';$placeholders3=array();	
							$sql4="SELECT first_name, middle_name, last_name FROM users where id=:user_id";
							$placeholders4[':user_id']=$row1['added_by'];
							$error4="Error: Unable to pt details from uniq ";
							$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
							foreach($s4 as $row4){$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));}
							
							if($paid==''){$paid=0;}
							$balance=$authorised_cost - $paid;
							$when_added=html("$row1[when_raised]");
							$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
							$company=html("$row2[company_covered]");
							$insurer=html("$row2[insurer]");
							$insurer_only=html("$row2[insurer]");
							if($company!=''){$insurer="$insurer - $company";}
							else{$insurer="$insurer";}
							//$cost=$invoice_cost;
							$invoice_number=html("$row1[invoice_number]");
							$invoice_id=html("$row1[id]");
							$val=$encrypt->encrypt("$invoice_id");
							//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
							//$_SESSION['balance_invoice'][$invoice_id]=$balance;
							$invoices_array[]=array('when_added'=>"$when_added",  'doctor'=>"$doctor", 'patient_name'=>"$patient_name", 
										'insurer'=>"$insurer",'invoice_number'=>"$invoice_number", 'billed_cost'=>"$billed_cost",'authorised_cost'=>"$authorised_cost",
									 'balance'=>"$balance", 'paid'=>"$paid",'val'=>"$val", 'balance'=>"$balance",'insurer_only'=>"$insurer_only");
						
						
					}//end s2

					
				//}//end if
				
				
			}//end s1
			$print_end_table=false;
			$count=$total_authorised_cost=$total_billed_cost=$total_paid=0;
			$insurer_name_summary_array=$insurer_count_summary_array=$insurer_billed_cost_summary_array=$insurer_authorised_cost_summary_array=array();
			foreach($invoices_array as $row){
					
					if($count==0){
						if($income_report==3){
							$caption=strtoupper("TREATMENTS INVOICED BETWEEN $from_date and $to_date");
						}
						elseif($income_report==1){
							$caption="TREATMENTS INVOICED ON $from_date";
						}	
						$print_end_table=true;
						echo "<br><br>
								<table class='normal_table'><caption>$caption</caption><thead>
								<tr><th class=invoice_in_count></th>
								<th class=invoice_in_date>DATE</th>
								<th class=invoice_in_doctor>DOCTOR</th>
								<th class=invoice_in_patient>PATIENT NAME</th>
								<th class=invoice_in_company>CORPORATE</th>
								<th class=invoice_in_id>INVOICE No.</th>
								<th class=invoice_in_cost>BILLED COST</th>
								<th class=invoice_in_tray>AUTHORISED COST</th>
								<th class=invoice_in_finished>AMOUNT PAID</th>
								</tr></thead><tbody>";	
					}
						$doctor=$row['doctor'];
						$when_added=html("$row[when_added]");
						$invoice_number=html("$row[invoice_number]");
						$billed_cost=html("$row[billed_cost]");
						$patient_name=html("$row[patient_name]");
						$insurer=html("$row[insurer]");
						$authorised_cost=html("$row[authorised_cost]");
						$paid=html("$row[paid]");
						$val=$row['val'];
						$count++;
						$total_authorised_cost = $total_authorised_cost + $authorised_cost;
						$total_billed_cost = $total_billed_cost + $billed_cost;
						$total_paid = $total_paid + $paid;
						echo "<tr><td class=count>$count</td>
								<td>$when_added</td>
								<td>$doctor</td>
								<td>$patient_name</td>
								<td>$insurer</td>
						<td><input type=button class='button_in_table_cell button_style invoice_no' value=$invoice_number  /></td>
						<td>";
							if($billed_cost==''){echo "$billed_cost";}
							else{echo number_format($billed_cost,2);}
							echo "</td><td>";
							if($authorised_cost==''){echo "$authorised_cost";}
							else{echo number_format($authorised_cost,2);}
							echo "</td><td>";
						//check to see if balance is full or partiall and show a link
						if($balance > 0 and $paid > 0){
							echo "<a href='?$val' class='balance_payment link_style'>".number_format($paid,2)."</a>";
						}
						else{echo number_format($paid,2);}
						echo "</td></tr>";
						
						//group invoices by insurer
						if(!in_array("$row[insurer_only]",$insurer_name_summary_array)){
							$insurer_name_summary_array[]="$row[insurer_only]";
							//count number of invoices
							$insurer_count_summary_array[] = 1;
							//add billed cost
							if($billed_cost != ''){$insurer_billed_cost_summary_array[]=$billed_cost;}
							else{$insurer_billed_cost_summary_array[]=0;}
							//add authorised cost
							if($authorised_cost != ''){$insurer_authorised_cost_summary_array[]=$authorised_cost;}
							else{$insurer_authorised_cost_summary_array[]=0;}
						}
						else{
							$key = array_search("$row[insurer_only]", $insurer_name_summary_array); 
							//count number of invoices
							$insurer_count_summary_array[$key]++;
							//add billed cost
							if($billed_cost != ''){
								$insurer_billed_cost_summary_array[$key] = $insurer_billed_cost_summary_array[$key] + $billed_cost;
							}
							//add authorised cost
							if($authorised_cost != ''){
								$insurer_authorised_cost_summary_array[$key] = $insurer_authorised_cost_summary_array[$key] + $authorised_cost;
							}
						}
			}
			if($print_end_table){
				echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td >".number_format($total_authorised_cost,2)."</td><td>".number_format($total_paid,2)."</td></tr>";
					echo "</tbody></table>";
				$total_sum_invoice_in_period=$total_paid;
			}
			
			//print summary count for invoices raised per insurer
			if(count($insurer_name_summary_array) > 0){
				$i=$i2=$sum_invoice_count=$sum_invoice_billed=$sum_invoice_authorised= 0;
				while($i < count($insurer_name_summary_array)){
					$i2++;
					if($i==0){
						echo "<table class=normal_table><caption>SUMMARY OF $caption<caption><thead>
						<tr><th class=inc_sum_count></th><th class=inc_sum_ins>INSURER</th><th class=inc_sum_num>INVOICES RAISED</th>
						<th class=inc_sum_bil>TOTAL BILLED COST</th><th class=inc_sum_auth>TOTAL AUTHORISED COST</th></tr></thead><tbody>";
					}
					
					echo "<tr><td>$i2</td><td>$insurer_name_summary_array[$i]</td><td>$insurer_count_summary_array[$i]</td><td>";
					if($insurer_billed_cost_summary_array[$i] > 0){echo number_format($insurer_billed_cost_summary_array[$i],2);}
					else {echo '';}
					echo "</td><td>";
					if($insurer_authorised_cost_summary_array[$i] > 0 ){echo number_format($insurer_authorised_cost_summary_array[$i],2);}
					else {echo '';}
					echo "</td></tr>";
					$sum_invoice_count = $sum_invoice_count +  $insurer_count_summary_array[$i];
					$sum_invoice_billed = $sum_invoice_billed + $insurer_billed_cost_summary_array[$i];
					$sum_invoice_authorised = $sum_invoice_authorised + $insurer_authorised_cost_summary_array[$i];
					$i++;
				}
				$sum_invoice_billed = number_format($sum_invoice_billed ,2);
				$sum_invoice_authorised = number_format($sum_invoice_authorised ,2);
				echo "<tr class=total_background><td colspan=2>TOTAL</td><td>$sum_invoice_count</td><td>$sum_invoice_billed</td>
				<td>$sum_invoice_authorised</td></tr></tbody></table>";
			}
			
		
		//end modify
		
		//now get invoices raised before the selected period

		//get invoices raised before  this period but paid in this period
			//start modify
			//get details from payments and patient_details
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT sum(amount) as amount_paid, concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, invoice_id, 
					insurance_company.name as insurer_name,	covered_company.name  as company_covered
					  FROM payments b join patient_details_a a on b.pid=a.pid
					left join covered_company on a.company_covered=covered_company.id 
						left	join insurance_company on a.type=insurance_company.id  
					WHERE b.when_added>=:from_date  and b.when_added<=:to_date and b.invoice_id > 0 group by invoice_id";	
			$placeholders[':from_date']="$from_date";
			$placeholders[':to_date']="$to_date";
			$error="356";
			$s = select_sql($sql, $placeholders, $error, $pdo);	
			$print_end_table=false;
			$count=$total_authorised_cost=$total_billed_cost=$total_paid=$paid=0;
			foreach($s as $row){
						
					//get details from tplan procedure
					$sql1=$error1=$s1='';$placeholders1=array();
					$sql1="select tplan_procedure.invoice_number, 
							sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost, 
							sum( tplan_procedure.unauthorised_cost ) as billed_cost,
							min(tplan_procedure.date_invoiced) as date_invoiced, users.first_name,users.last_name,users.middle_name
							 from tplan_procedure   
							join users on users.id=tplan_procedure.created_by 
							left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number where invoice_id=:invoice_id
						
							group by tplan_procedure.invoice_id  
						  	";	//	and date_invoiced <:from_date
					$placeholders1[':invoice_id']=$row['invoice_id'];
					//$placeholders1[':from_date']="$from_date";
					$error1="356";
					$s1 = select_sql($sql1, $placeholders1, $error1, $pdo);
					$patient_name=$insurer='';
					foreach($s1 as $row1){
						$authorised_cost=html($row1['cost']);
						$doctor=ucfirst(html("$row1[4] $row1[6] $row1[5]"));
						$when_added=html("$row1[date_invoiced]");
						$invoice_number=html("$row1[invoice_number]");
						$billed_cost=html("$row1[billed_cost]");
					}
					
					//get balance details
					/*$balance=0;
					$sql1=$error1=$s1='';$placeholders1=array();
					$sql1="select sum(amount) as sum_paid from payments where invoice_id=:invoice_id";	
					$placeholders1[':invoice_id']=$row['invoice_id'];
					$error1="356";
					$s1 = select_sql($sql1, $placeholders1, $error1, $pdo);
					foreach($s1 as $row1){
						$paid=html($row1['sum_paid']);
						$balance=$authorised_cost - $paid;
					}
					if($paid==''){$paid=0;}*/
					if($count==0){
						$print_end_table=true;
						if($income_report==3){
							$caption=strtoupper("TREATMENTS INVOICED BEFORE $from_date BUT PAID BETWEEN $from_date and $to_date");
						}
						elseif($income_report==1){
							$caption=strtoupper("TREATMENTS INVOICED BEfore $from_date but paid on $from_date");
						}
						echo "<br><br>
								<table class='normal_table'><caption>$caption</caption><thead>
								<tr><th class=invoice_in_count></th>
								<th class=invoice_in_date>DATE</th>
								<th class=invoice_in_doctor>DOCTOR</th>
								<th class=invoice_in_patient>PATIENT NAME</th>
								<th class=invoice_in_company>CORPORATE</th>
								<th class=invoice_in_id>INVOICE No.</th>
								<th class=invoice_in_cost>BILLED COST</th>
								<th class=invoice_in_tray>AUTHORISED COST</th>
								<th class=invoice_in_finished>AMOUNT PAID</th>
								</tr></thead><tbody>";	
					}
						$count++;
						$paid=html($row['amount_paid']);
						$patient_name=ucfirst(html("$row[patient_name]"));
						$insurer=html("$row[insurer_name] - $row[company_covered]");
						$val=$encrypt->encrypt("$row[invoice_id]");
					
						$total_authorised_cost = $total_authorised_cost + $authorised_cost;
						$total_billed_cost = $total_billed_cost + $billed_cost;
						$total_paid = $total_paid + $paid;
						echo "<tr><td class=count>$count</td>
								<td>$when_added</td>
								<td>$doctor</td>
								<td>$patient_name</td>
								<td>$insurer</td>
						<td><input type=button class='button_in_table_cell button_style invoice_no' value=$invoice_number  /></td>
						<td>".number_format($billed_cost,2)."</td><td>".number_format($authorised_cost,2)."</td><td>";
						//check to see if balance is full or partiall and show a link
						//if($balance > 0 and $paid > 0){
							//echo "<a href='?$val' class='balance_payment link_style'>".number_format($paid,2)."</a>";
						//}
						//else{
						echo number_format($paid,2);
						//}
						echo "</td></tr>";
			}
			if($print_end_table){
				echo  "<tr class=total_background><td colspan=6>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td >".number_format($total_authorised_cost,2)."</td><td>".number_format($total_paid,2)."</td></tr>";
					echo "</tbody></table>";
				$total_sum_out_of_period=$total_paid;
			}
			//end modify
		?>
		<!--gross total-->
		
		<?php
			//gross total
			$header='';
			if($income_report==3){$header="GROSS TOTAL INCOME BETWEEN $from_date and $to_date : ";}
			elseif($income_report==1){$header="GROSS TOTAL INCOME ON $from_date : ";}	
		?>
		<div class='grid-100 no_padding make_bold total_background'>
			<div class='grid-50 alpha'><?php echo "$header ".number_format($total_sum_cash + $total_sum_invoice_in_period + $total_sum_out_of_period, 2);; ?></div>
			<div class='grid-50 omega'><?php   ?></div>
		</div>
		<?php
		
		//show expenses deducted from cash income
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT  a.when_added, b.first_name, b.middle_name, b.last_name , a.cost, c.name
				from expenses as a join users as b on a.deducted_from_income=1 and a.when_added >=:from_date AND a.when_added <=:to_date and b.id=a.added_by
				join expense_types as c on c.id=a.expense_type order by a.id";	
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			echo "<br><br>";
			$i=0;
			if($income_report==3){
				$caption=strtoupper("INCOME dedcutable expenses incurred between $from_date and $to_date");
			}
			if($income_report==1){
				$caption=strtoupper("INCOME dedcutable expenses incurred on $from_date");
			}
				$total=0;
				foreach($s as $row){
					if($i==0){
						echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr><th class=irde_count></th><th class=irde_date>DATE</th><th class=irde__user>RECORDED BY</th>
						<th class=irde_expense>EXPENSE NAME</th><th class=irde_amount>COST</th></tr>
						</thead><tbody>";
						
					}
					$i++;
					$date=html($row['when_added']);
					$user_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$expense_name=ucfirst(html("$row[name]"));
					$cost=html($row['cost']);
					$total = $total + $cost;
					$amount=number_format($cost,2);//".number_format($amount, 2)."
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>$amount</td></tr>";
					
				}
				echo "<tr class='make_bold'><td colspan=4>TOTAL DEDUCTABLE EXPENSES</td><td>".number_format($total,2)."</td></tr>
					<tr class='total_background make_bold'><td colspan=4>TOTAL CASH INCOME - EXPENSES</td><td>".number_format($total_sum_cash - $total,2)."</td></tr></tbody></table>";
		}	

		//show expenses not deducted from cash income
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT  a.when_added, b.first_name, b.middle_name, b.last_name , a.cost, c.name
				from expenses as a join users as b on a.deducted_from_income=0 and a.when_added >=:from_date AND a.when_added <=:to_date and b.id=a.added_by
				join expense_types as c on c.id=a.expense_type order by a.id";	
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			echo "<br><br>";
			$i=0;
			if($income_report==3){
				$caption=strtoupper("none INCOME dedcutable expenses incurred between $from_date and $to_date");
			}
			elseif($income_report==1){
				$caption=strtoupper("none INCOME dedcutable expenses incurred on $from_date");
			}
				$total=0;
				foreach($s as $row){
					if($i==0){
						echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr><th class=irde_count></th><th class=irde_date>DATE</th><th class=irde__user>RECORDED BY</th>
						<th class=irde_expense>EXPENSE NAME</th><th class=irde_amount>COST</th></tr>
						</thead><tbody>";
						
					}
					$i++;
					$date=html($row['when_added']);
					$user_name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$expense_name=ucfirst(html("$row[name]"));
					$cost=html($row['cost']);
					$total = $total + $cost;
					$amount=number_format($cost,2);
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>$amount</td></tr>";
					
				}
				echo "<tr class='make_bold total_background'><td colspan=4>TOTAL NONE INCOME DEDUCTABLE EXPENSES</td><td>".number_format($total,2)."</td></tr></tbody></table>";
		}		
		exit;
		
	}
	
	//date range and single date summary  report
	elseif(!$exit_flag and $income_report==2 or $income_report==4 ){
		if($income_report==4 ){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			//$caption="NONE INSURANCE PAYMENTS BETWEEN $from_date and $to_date";
			$caption="PAYMENTS AND EXPENSES BETWEEN $from_date and $to_date";
		}
		elseif($income_report==2){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['from_date']);
			//$caption="NONE INSURANCE PAYMENTS ON $from_date";
			$caption="PAYMENTS AND EXPENSES ON $from_date";
		}
		//start of new mrthod
		//get self payments for non points into array
		$self_pay_array=array();
		$sql=$error=$s='';$placeholders=array();
		$sql="select  a.amount,d.name ,  b.internal_patient, a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.when_added >=:from_date and a.when_added <=:to_date
			and a.invoice_id=0 and a.pay_type!=10
			left join payment_types d on d.id=a.pay_type ";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$self_pay_array[]=array( 'amount'=>$row['amount'],'pay_type'=>$row['name'],
			'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
		}
		
		//get self payments for points into array
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.amount,d.name , 	 b.internal_patient,a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=10 and a.when_added >=:from_date	and a.when_added <=:to_date
			and a.invoice_id=0
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report points";
		$placeholders[':from_date']=$_POST['from_date'];
		$placeholders[':to_date']=$_POST['to_date'];
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$self_pay_array[]=array( 'amount'=>$row['amount'],'pay_type'=>$row['name'],
			'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
		}
		
		$total_sum_invoice_in_period=$total_sum_out_of_period=$total=$total_points=$total_xray=$total_cadcam=$total_molars=$i=0;
		$total_cash=$total_mpesa=$total_visa=$total_cheque=$total_eft=$total_waive=$total_credit_transfer=0;
		foreach($self_pay_array as $row){
			$amount=html($row['amount']);
			$pay_type=html($row['pay_type']);
			//points
			if($row['pay_type_id']==8){$total_points = $total_points + $amount;}
			//waive
			elseif($row['pay_type_id']==6){$total_waive = $total_waive + $amount;}
			//cash
			elseif($row['pay_type_id']==2){$total_cash = $total_cash + $amount;}
			//mpesa
			elseif($row['pay_type_id']==4){$total_mpesa = $total_mpesa + $amount;}
			//visa
			elseif($row['pay_type_id']==5){$total_visa = $total_visa + $amount;}
			//cheque
			elseif($row['pay_type_id']==3){$total_cheque = $total_cheque + $amount;}
			//eft
			elseif($row['pay_type_id']==9){$total_eft = $total_eft + $amount;}
			//credit transfer
			elseif($row['pay_type_id']==10){$total_credit_transfer = $total_credit_transfer + $amount;}
			
			//xray ref
			if($row['patient_type']==1){$total_xray = $total_xray + $amount;}
			//cadcam
			elseif($row['patient_type']==2){$total_cadcam = $total_cadcam + $amount;}
			//molars pt
			//elseif($row['patient_type']==0){$total_molars = $total_molars + $amount;}
			$unformatted_amount=$amount;
			$amount=number_format($amount,2);
			$bgcolor='';
			if($row['patient_type']==0){
				//for molars internal patient
				$total_molars = $total_molars + $unformatted_amount;
			}
		}
		//end of new method
		echo "<table class='half_width'><caption>$caption</caption><tbody>";
		//show sub totals for different pay types
		echo "<tr><td class=ic_1>TOTAL POINTS  </td><td class=ic_2>".number_format($total_points,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL CASH  </td><td class=ic_2>".number_format($total_cash,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL CHEQUES  </td><td class=ic_2>".number_format($total_cheque,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL MPESA  </td><td class=ic_2>".number_format($total_mpesa,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL VISA  </td><td class=ic_2>".number_format($total_visa,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL WAIVES  </td><td class=ic_2>".number_format($total_waive,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL EFT  </td><td class=ic_2>".number_format($total_eft,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL CREDIT TRANSFER  </td><td class=ic_2>".number_format($total_credit_transfer,2)."</td></tr>";
		
		//show total for patient category
		echo "<tr><td class=ic_1>TOTAL AMOUNT FOR CUSPID </td><td class=ic_2>".number_format($total_molars,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL AMOUNT FOR X-RAY REFERALS</td><td class=ic_2>".number_format($total_xray,2)."</td></tr>";
		echo "<tr><td class=ic_1>TOTAL AMOUNT FOR CADCAM REFERALS</td><td class=ic_2>".number_format($total_cadcam,2)."</td></tr>";
		echo "<tr class=total_background><td class=ic_1>TOTAL AMOUNT COLLECTED</td><td class=ic_2>".number_format(($total_molars + $total_xray + $total_cadcam),2)."</td></tr>";
		$total_non_ins_pay=$total_molars + $total_xray + $total_cadcam;		
		//get points
		
		echo "<br>";
		//total invoices raised in this period
		
						//get details from unique_inv_table first
			$invoices_array=array();
			$total_authorised_cost =$total_billed_cost = $total_paid = 0;
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT id FROM unique_invoice_number_generator WHERE 
				when_raised >=:from_date AND when_raised <=:to_date";
			$placeholders1[':from_date']="$from_date";
			$placeholders1[':to_date']="$to_date";
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			//echo "$_POST[from_date]--$_POST[to_date]--".$s1->rowCount();
			foreach($s1 as $row1 ){
				$invoice_cost=$billed_cost=$amount_paid=$doctor='';
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) as billed_cost,sum( tplan_procedure.authorised_cost )
								as authorised_cost, ifnull( co_payment.amount, 0 ) as co_payment
								FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							//check if amount authorised is 0
							if($row3['authorised_cost'] > 0){
								$row3['authorised_cost'] = $row3['authorised_cost']- $row3['co_payment'];
								$authorised_cost=html($row3['authorised_cost']);
							}
							else{$authorised_cost=html($row3['authorised_cost']);}
							$billed_cost=$row3['billed_cost'] - $row3['co_payment'];
							$billed_cost=html($billed_cost);
						}
						
						//now amount paid
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){$paid=html($row3['amount']);}
						
						$total_authorised_cost = $total_authorised_cost + $authorised_cost;
						$total_billed_cost = $total_billed_cost + $billed_cost;
						$total_paid = $total_paid + $paid;
			}//end s1
			$print_end_table=false;
			
			
			echo "<tr><td class=ic_1>INVOICES RAISED IN THIS PERIOD: BILLED COST</td><td class=ic_2>".number_format($total_billed_cost,2)."</td></tr>";
			echo "<tr><td class=ic_1>INVOICES RAISED IN THIS PERIOD: AUTHORISED COST</td><td class=ic_2>".number_format($total_authorised_cost,2)."</td></tr>";
			echo "<tr><td class=ic_1>INVOICES RAISED IN THIS PERIOD: AMOUNT PAID</td><td class=ic_2>".number_format($total_paid,2)."</td></tr>";
			echo "<tr><td class=ic_1>INVOICES RAISED IN THIS PERIOD: AMOUNT DUE</td><td class=ic_2>".number_format($total_authorised_cost - $total_paid,2)."</td></tr>";
			$total_ins_pay=$total_paid;
		//end new method 2

		//START NEW METHOD 3
			//get invoices raised before  this period but paid in this period
			//start modify
			//get details from payments and patient_details
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT sum(amount) as amount_paid,  invoice_id
					  FROM payments b 
					WHERE b.when_added>=:from_date  and b.when_added<=:to_date and b.invoice_id > 0 group by invoice_id";	
			$placeholders[':from_date']="$from_date";
			$placeholders[':to_date']="$to_date";
			$error="356";
			$s = select_sql($sql, $placeholders, $error, $pdo);	
			$print_end_table=false;
			$count=$total_authorised_cost=$total_billed_cost=$total_paid=$paid=0;
			foreach($s as $row){
						
					//get details from tplan procedure
					$sql1=$error1=$s1='';$placeholders1=array();
					$sql1="select tplan_procedure.invoice_number, min(date_invoiced)
							 from tplan_procedure where  
							 invoice_id=:invoice_id
							and date_invoiced <:from_date
							group by tplan_procedure.invoice_id  
						  	";	
					$placeholders1[':invoice_id']=$row['invoice_id'];
					$placeholders1[':from_date']="$from_date";
					$error1="356";
					$s1 = select_sql($sql1, $placeholders1, $error1, $pdo);
					$patient_name=$insurer='';
					foreach($s1 as $row1){
						$total_paid = $total_paid + html($row['amount_paid']);
					}
			}
			echo "<tr ><td class=ic_1>INVOICES RAISED BEFORE THIS PERIOD BUT PAID IN THIS PERIOD</td><td class=ic_2>".number_format($total_paid,2)."</td></tr>";
			echo "<tr class=total_background><td class=ic_1>GROSS TOTAL</td><td class=ic_2>".number_format($total_paid + $total_ins_pay + $total_non_ins_pay,2)."</td></tr>";
			
		//end new method 3
		//show invoices raised before this period but  paid in this period

		//now get invoices raised before the selected period

		//gross total
		?>

		<?php
		
		//show expenses deducted from cash income
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT   sum(a.cost) as cost
				from expenses as a where a.deducted_from_income=1 and a.when_added >=:from_date AND a.when_added <=:to_date 
				";	
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$income_decuted_expenses=0;
		foreach($s as $row){
			$income_decuted_expenses=html($row['cost']);
		}
		if($income_decuted_expenses==''){$income_decuted_expenses=0;}
		echo "<tr ><td class=ic_1>INCOME DEDCUTABLE EXPENSES IN THIS PERIOD</td><td class=ic_2>".number_format($income_decuted_expenses,2)."</td></tr>";
		echo "<tr class=total_background><td class=ic_1>TOTAL CASH INCOME - EXPENSES</td><td class=ic_2>".number_format($total_non_ins_pay - $income_decuted_expenses,2)."</td></tr>";
			
		//show expenses not deducted from cash income
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT  sum(a.cost) as cost
				from expenses as a where a.deducted_from_income=0 and a.when_added >=:from_date AND a.when_added <=:to_date 
				";	
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$none_income_decuted_expenses=0;
		foreach($s as $row){
			$none_income_decuted_expenses=html($row['cost']);
		}
		if($none_income_decuted_expenses==''){$none_income_decuted_expenses=0;}
		echo "<tr ><td class=ic_1>OTHER EXPENSES NOT DEDUCTED FROM INCOME IN THIS PERIOD</td><td class=ic_2>".number_format($none_income_decuted_expenses,2)."</td></tr>";
		echo "</table>";
		
		exit;
		
	}	
	if(isset($error_class) and $error_class!='' and isset($message) and $message!='' ){
		echo "<div class=$error_class>$message</div>";
	}
}	


?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
		<div class='grid-100 '>
			<div class='grid-15 '><label for="" class="label">Password</label></div>
			<div class='grid-10 '><input type="password" name="user_pass"   /></div>
			<div class=clear></div><br>
			<div class='grid-15 '><label for="" class="label">Search by</label>
					<?php $token = form_token(); $_SESSION['token_ir1'] = "$token";  ?>
					<input type="hidden" name="token_ir1"  value="<?php echo $_SESSION['token_ir1']; ?>" />
			</div>	
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.id, a.name from sub_menus a, sub_privileges b where b.parent_menu_id=77 and b.user_id=:user_id and b.sub_menu_id=a.id";
					$error="Unable to incmoe menus";
					$placeholders[':user_id']=$_SESSION['id'];
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					if($s->rowCount() > 0){
						echo "<div class='grid-20'><select class=' income_report_criteria' name=income_report><option></option>";
						foreach($s as $row){
							$name=html($row['name']);
							$id=$encrypt->encrypt(html($row['id']));
							echo "<option value='$id'>$name</option>";
						}			
						echo "</select></div>";					
					}
					else{//check if this is a role
						$sql=$error=$s='';$placeholders=array();
						$sql="select a.id, a.name from sub_menus a, role_sub_privileges b , user_roles c where b.parent_menu_id=77
							and c.user_id=:user_id and 	c.role_id=b.role_id and b.sub_menu_id=a.id";
						$error="Unable to incmoe menus";
						$placeholders[':user_id']=$_SESSION['id'];
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						echo "<div class='grid-20'><select class='income_report_criteria ' name=income_report><option></option>";
						foreach($s as $row){
							$name=html($row['name']);
							$id=$encrypt->encrypt(html($row['id']));
							echo "<option value='$id'>$name</option>";
						}			
						echo "</select></div>";
					}

				?>	
				<div class="clear pay_type_criteria"></div>
				<div class="grid-100 pay_type_criteria"><br></div>
				<div class='grid-15 pay_type_criteria'><label for="" class="label">Select Pay Type</label></div>
				<?php
						//get pay types
						$sql=$error=$s='';$placeholders=array();
						$sql="select id, name from payment_types  where id!=7";
						$error="Unable to get payment types";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						echo "<div class='grid-20 pay_type_criteria'><select  name=pay_type><option></option>";
						foreach($s as $row){
							$name=html($row['name']);
							$id=$encrypt->encrypt(html($row['id']));
							echo "<option value='$id'>$name</option>";
						}			
						echo "</select></div>";
					

				?>				
				
				<div class='grid-10 date_criteria '><label for="" class="label">From this date</label></div>
				<div class='grid-10 date_criteria '><input type=text name=from_date1 class=date_picker /></div>
				<div class='grid-10 date_criteria'><label for="" class="label">To this date</label></div>
				<div class='grid-10 date_criteria'><input type=text name=to_date class=date_picker /></div>
				<div class='grid-5 date_criteria'><input type=submit value=Submit /></div>
				
				<div class='grid-10  single_date'><label for="" class="label">On this date</label></div>
				<div class='grid-10  single_date'><input type=text name=from_date class=date_picker /></div>
				<div class='grid-5 single_date'><input type=submit value=Submit /></div>
				
				<div class=clear></div><br>			
				

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>