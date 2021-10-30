<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,105)){exit;}
echo "<div class='grid_12 page_heading'>NUMBER OF TREATMENT PROCEDURES DONE</div>";
?>
<div class=grid-container>
<?php
if(isset($_SESSION['token_ir1']) and isset($_POST['token_ir1']) and $_POST['token_ir1']==$_SESSION['token_ir1']){
	$exit_flag=false;
	$_SESSION['token_ir1']='';
	$total_sum_cash=$total_sum_invoice_in_period=$total_sum_out_of_period=0;
	$income_report=$encrypt->decrypt("$_POST[income_report]");
	
	//check if date is set for range
	if($income_report==3 or $income_report==4 or $income_report==5){
		if(!isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' or $_POST['to_date']==''){
			$message="Unable to generate report as date range was not properly selected";
			$error_class="error_response";
			$exit_flag=true;
		}
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
				if($pay_type==5){$transaction_header="VISA TRANSACTION NUMBER";}
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
		foreach($s as $row){
			$self_pay_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row['patient_number'] , 'pname'=>"$row[4] $row[5] $row[6]", 
											'created_by'=>"$row[7] $row[8] $row[9]", 'tx_details'=>$row['tx_number'] ,'amount'=>$row['amount'],
											'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],'pay_type'=>$row['name'],
											'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
			
		}
		
		//get self payments for points into array
		$sql=$error=$s='';$placeholders=array();
		$sql="select a.when_added, a.receipt_num, a.amount, a.tx_number , b.first_name, b.middle_name, b.last_name,
			c.first_name, c.middle_name, c.last_name ,d.name , b.patient_number , e.first_name, e.middle_name, e.last_name, 
			e.patient_number, a.id, b.internal_patient,a.pay_type
			from payments a join patient_details_a b on a.pid=b.pid and a.pay_type=10 and a.when_added >=:from_date	and a.when_added <=:to_date
			and a.invoice_id=0
			join patient_details_a e on e.pid=a.tx_number
			left join users c on a.created_by=c.id
			left join payment_types d on d.id=a.pay_type order by a.id";
		$error="Unable to get pay type report points";
		$placeholders[':from_date']=$_POST['from_date'];
		$placeholders[':to_date']=$_POST['to_date'];
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$self_pay_array[]=array('when_added'=>$row['when_added'], 'pid'=>$row[11] , 'pname'=>"$row[4] $row[5] $row[6]", 
											'created_by'=>"$row[7] $row[8] $row[9]", 'tx_details'=>"DEBITED: $row[15] - $row[12] $row[13] $row[14]" ,
											'amount'=>$row['amount'],'receipt_num'=>$row['receipt_num'],'id'=>$row['id'],
											'pay_type'=>$row['name'],'patient_type'=>$row['internal_patient'],'pay_type_id'=>$row['pay_type']);
			
		}
		
		//now sort by id
		// Obtain list of IDs for sorting
		foreach ($self_pay_array as $key => $row) {
			$id[$key]  = $row['id'];
		}
		
		// Sort the data with when_added
		array_multisort($id, SORT_ASC, $self_pay_array);

		$total=$total_points=$total_xray=$total_cadcam=$total_molars=$i=0;
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
			echo "<tr class=$bgcolor><td>$i</td><td>$date</td><td>$patient_number</td><td>$patient_name</td><td>$pay_type</td><td>$tx_details</td><td>$amount</td><td>$created_by</td><td>$receipt_num</td></tr>";
			
		}
		$total_molars = $total_cash + $total_mpesa + $total_visa + $total_cheque + $total_eft;
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
		echo "<tr><td colspan=6>TOTAL AMOUNT FOR CUSPID</td><td>".number_format($total_molars,2)."</td><td colspan=2></td></tr>";
		echo "<tr><td colspan=6>TOTAL AMOUNT FOR X-RAY REFERALS</td><td>".number_format($total_xray,2)."</td><td colspan=2></td></tr>";
		echo "<tr><td colspan=6>TOTAL AMOUNT FOR CADCAM REFERALS</td><td>".number_format($total_cadcam,2)."</td><td colspan=2></td></tr>";
		echo "<tr class=total_background><td colspan=6>TOTAL AMOUNT COLLECTED</td><td>".number_format(($total_molars + $total_xray + $total_cadcam),2)."</td><td colspan=2></td></tr></tbody></table>";
		$total_sum_cash = $total_molars + $total_xray + $total_cadcam;
		
		//now get invoices raised on that day
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.invoice_number, procedures.name, a.teeth, a.details, a.treatment_procedure_id, 
			a.date_invoiced, b.first_name, b.middle_name, b.last_name, b.patient_number, unauthorised_cost, 
			authorised_cost, covered_company.name, insurance_company.name, NULL , ifnull(f.sum_paid,0) as sum_paid, NULL , 
			NULL , 	NULL , NULL , NULL , NULL , NULL, b.internal_patient
				FROM tplan_procedure AS a
				JOIN patient_details_a AS b ON a.pid = b.pid
				AND a.pay_type =1
				AND date_invoiced >=:from_date
				AND date_invoiced <=:to_date
				AND a.invoice_id >0
				JOIN procedures ON a.procedure_id = procedures.id
				LEFT JOIN covered_company ON covered_company.id = b.company_covered
				LEFT JOIN insurance_company ON insurance_company.id = b.type
				LEFT JOIN (

				SELECT treatment_procedure_id, ifnull( sum( amount ) , 0 ) AS sum_paid
				FROM payments  where when_added >=:from_date
				AND when_added <=:to_date
				GROUP BY treatment_procedure_id
				) AS f ON f.treatment_procedure_id = a.treatment_procedure_id ";	
			
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$i=0;
		if($s->rowCount() > 0){
			if($income_report==3){
				$caption=strtoupper("TREATMENTS INVOICED BETWEEN $from_date and $to_date");
			}
			elseif($income_report==1){
				$caption="TREATMENTS INVOICED ON $from_date";
			}	
			 ?>
				
				<div class=income_report_invoices_table><div class=tplan_table_caption><?php echo "$caption"; ?></div>
				<div class=tplan_table_row2>
					<div class='irit_count white_text make_bold'></div><div class='irit_date_invoice white_text make_bold'>DATE<br>INVOICED</div>
					<div class='irit_pname white_text make_bold'>PATIENT</div><div class='irit_ptype white_text make_bold'>PATIENT TYPE</div>
					<div class='irit_inv_num white_text make_bold'>INVOICE<br>NUMBER</div><div class='irit_procedure white_text make_bold'>TREATMENT</div>
					<div class='irit_uncost white_text make_bold'>BILLED<br>COST</div><div class='irit_acost white_text make_bold'>AUTHORISED<br>COST</div>
					<div class='irit_paid white_text make_bold'>AMOUNT<br>PAID</div>
					<div class='irit_pay_details_hold white_text make_bold'>
						<div class=tplan_table_row2>
								<div class='irit_pay_date2 white_text make_bold'>PAYMENT<br>DATE</div>
								<div class='irit_pay_recipt2 white_text make_bold'>RECEIPT NUMBER</div>
							</div>	
						</div>
				</div>
				</div>
			<div class=income_report_invoices_table>
				<?php 
				$i=0; 
				$total_sum_paid=$total_authorised_cost=$total_biled_cost=0;
				foreach($s as $row){
					$i++;
					$date_invoiced=html($row['date_invoiced']);
					$pnum=html($row['patient_number']);
					$pname=html("$row[6] $row[7] $row[8]");
					$insurer=html("$row[13]");
					$company=html("$row[12]");
					if($company!=''){$ptype="$insurer - $company";}
					else{$ptype="$insurer";}
					$invoice_number=html($row['invoice_number']);
					$procedure_name=html("$row[1]");
					$teeth=html("$row[teeth]");
					$procedure_details=html("$row[details]");
					if($procedure_name=='X-Ray'){$treatment="$procedure_details $teeth";}
					else{$treatment="$procedure_name $teeth $procedure_details";}
					$billed_cost=html($row['unauthorised_cost']);
					$total_biled_cost=$total_biled_cost + $billed_cost;
					$authorised_cost=html($row['authorised_cost']);
					$total_authorised_cost=$total_authorised_cost + $authorised_cost;
					$sum_paid=html($row['sum_paid']);
					$total_sum_paid=$total_sum_paid + $sum_paid;
					$bgcolor='';
					if($row['internal_patient']==1){
						//for xray referral
						$bgcolor='blue_shade_background';
					}
					elseif($row['internal_patient']==2){
						//for cadcam referral
						$bgcolor='light_blue_background';
					}
					echo "<div class='tplan_table_row $bgcolor'>";
						echo "<div class=irit_count>$i</div>";//count
						echo "<div class=irit_date_invoice>$date_invoiced</div>";//date invoiced
						echo "<div class=irit_pname>$pname</div>";//patient name
						echo "<div class=irit_ptype>$ptype</div>";//patient type
						echo "<div class=irit_inv_num>$invoice_number</div>";//invoice number
						echo "<div class=irit_procedure>$treatment</div>";//procedure done
						echo "<div class=irit_uncost>".number_format($billed_cost,2)."</div>";//billed cost
						echo "<div class=irit_acost>";
							if($authorised_cost!=''){echo number_format($authorised_cost,2);}
							else {echo "&nbsp";}
						echo "</div>";//authorised cost
						echo "<div class=irit_paid>".number_format($sum_paid,2)."</div>";//sum amount paid
							//get payments for this treatment procedure
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT  amount,  tx_number, 	 receipt_num, when_added
								FROM payments where treatment_procedure_id=:treatment_procedure_id
								AND when_added >=:from_date 	AND when_added <=:to_date";	
							$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
							$placeholders2[':from_date']="$from_date";
							$placeholders2[':to_date']="$to_date";
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
								echo "<div class=irit_pay_details_hold>"; 	
									foreach($s2 as $row2){ 
										$payment_date=html($row2['when_added']);	
										$payment_transaction_details=html($row2['tx_number']);	
										$payment_amount=html($row2['amount']);	
										$receipt_num=html($row2['receipt_num']);	
										echo "<div class=tplan_table_row>";
											echo "<div class='irit_pay_date2'>$payment_date</div>";
											echo "<div class='irit_pay_recipt2'>$receipt_num</div>";
										echo "</div>";		
									}
								echo "</div>";
						echo "</div>";//	tplan_table_row
				}
			echo "</div>";//end income_report_invoices_table
			//show total
			?>
			<div class=income_report_invoices_table>
				<div class='tplan_table_row2 total_background'>
					<div class='irit_total1 make_bold'>TOTAL</div>
					<div class='irit_tot_bill make_bold'><?php echo number_format($total_biled_cost,2); ?></div>
					<div class='irit_tot_auth make_bold'><?php echo number_format($total_authorised_cost,2); ?></div>
					<div class='irit_tot_pay make_bold'><?php echo number_format($total_sum_paid,2); ?></div>
					<div class='irit_total11'></div>
				</div>
			</div>
			<?php
			$total_sum_invoice_in_period=$total_sum_paid;
		}
		
		//now get invoices raised before the selected period
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.invoice_number, procedures.name, a.teeth, a.details, a.treatment_procedure_id, 
			a.date_invoiced, b.first_name, b.middle_name, b.last_name, b.patient_number, unauthorised_cost, 
			authorised_cost, covered_company.name, insurance_company.name, NULL , ifnull(f.sum_paid,0) as sum_paid, NULL , 
			NULL , 	NULL , NULL , NULL , NULL , NULL, b.internal_patient
				FROM tplan_procedure AS a
				JOIN patient_details_a AS b ON a.pid = b.pid
				AND a.pay_type =1	AND date_invoiced <:from_date AND a.invoice_id >0
				JOIN procedures ON a.procedure_id = procedures.id
				JOIN (SELECT treatment_procedure_id, ifnull( sum( amount ) , 0 ) AS sum_paid
				   FROM payments  where when_added >=:from_date	   AND when_added <=:to_date
				     GROUP BY treatment_procedure_id) AS f ON f.treatment_procedure_id = a.treatment_procedure_id
				LEFT JOIN covered_company ON covered_company.id = b.company_covered
				LEFT JOIN insurance_company ON insurance_company.id = b.type
			";	
			
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$i=0;
		if($s->rowCount() > 0){
			if($income_report==3){
				$caption=strtoupper("TREATMENTS INVOICED BEfore $from_date but paid between $from_date and $to_date");
			}
			elseif($income_report==1){
				$caption=strtoupper("TREATMENTS INVOICED BEfore $from_date but paid on $from_date");
			}	
			 ?>
				<br><br>
				<div class=income_report_invoices_table><div class=tplan_table_caption><?php echo "$caption"; ?></div>
				<div class=tplan_table_row2>
					<div class='irit_count white_text make_bold'></div><div class='irit_date_invoice white_text make_bold'>DATE<br>INVOICED</div>
					<div class='irit_pname white_text make_bold'>PATIENT</div><div class='irit_ptype white_text make_bold'>PATIENT TYPE</div>
					<div class='irit_inv_num white_text make_bold'>INVOICE<br>NUMBER</div><div class='irit_procedure white_text make_bold'>TREATMENT</div>
					<div class='irit_uncost white_text make_bold'>BILLED<br>COST</div><div class='irit_acost white_text make_bold'>AUTHORISED<br>COST</div>
					<div class='irit_paid white_text make_bold'>AMOUNT<br>PAID</div>
					<div class='irit_pay_details_hold white_text make_bold'>
						<div class=tplan_table_row2>
								<div class='irit_pay_date2 white_text make_bold'>PAYMENT<br>DATE</div>
								<div class='irit_pay_recipt2 white_text make_bold'>RECEIPT NUMBER</div>
							</div>	
						</div>
				</div>
				</div>
			<div class=income_report_invoices_table>
				<?php 
				$i=0; 
				$total_sum_paid=$total_authorised_cost=$total_biled_cost=0;
				foreach($s as $row){
					$i++;
					$date_invoiced=html($row['date_invoiced']);
					$pnum=html($row['patient_number']);
					$pname=html("$row[6] $row[7] $row[8]");
					$insurer=html("$row[13]");
					$company=html("$row[12]");
					if($company!=''){$ptype="$insurer - $company";}
					else{$ptype="$insurer";}
					$invoice_number=html($row['invoice_number']);
					$procedure_name=html("$row[1]");
					$teeth=html("$row[teeth]");
					$procedure_details=html("$row[details]");
					if($procedure_name=='X-Ray'){$treatment="$procedure_details $teeth";}
					else{$treatment="$procedure_name $teeth $procedure_details";}
					$billed_cost=html($row['unauthorised_cost']);
					$total_biled_cost=$total_biled_cost + $billed_cost;
					$authorised_cost=html($row['authorised_cost']);
					$total_authorised_cost=$total_authorised_cost + $authorised_cost;
					$sum_paid=html($row['sum_paid']);
					$total_sum_paid=$total_sum_paid + $sum_paid;
					$bgcolor='';
					if($row['internal_patient']==1){
						//for xray referral
						$bgcolor='blue_shade_background';
					}
					elseif($row['internal_patient']==2){
						//for cadcam referral
						$bgcolor='light_blue_background';
					}
					echo "<div class='tplan_table_row $bgcolor'>";
						echo "<div class=irit_count>$i</div>";//count
						echo "<div class=irit_date_invoice>$date_invoiced</div>";//date invoiced
						echo "<div class=irit_pname>$pname</div>";//patient name
						echo "<div class=irit_ptype>$ptype</div>";//patient type
						echo "<div class=irit_inv_num>$invoice_number</div>";//invoice number
						echo "<div class=irit_procedure>$treatment</div>";//procedure done
						echo "<div class=irit_uncost>".number_format($billed_cost,2)."</div>";//billed cost
						echo "<div class=irit_acost>";
							if($authorised_cost!=''){echo number_format($authorised_cost,2);}
							else {echo "&nbsp";}
						echo "</div>";//authorised cost
						echo "<div class=irit_paid>".number_format($sum_paid,2)."</div>";//sum amount paid
							//get payments for this treatment procedure
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT  amount,  tx_number, 	 receipt_num, when_added
								FROM payments where treatment_procedure_id=:treatment_procedure_id
								AND when_added >=:from_date 	AND when_added <=:to_date";	
							$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
							$placeholders2[':from_date']="$from_date";
							$placeholders2[':to_date']="$to_date";
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
								echo "<div class=irit_pay_details_hold>"; 	
									foreach($s2 as $row2){ 
										$payment_date=html($row2['when_added']);	
										$payment_transaction_details=html($row2['tx_number']);	
										$payment_amount=html($row2['amount']);	
										$receipt_num=html($row2['receipt_num']);	
										echo "<div class=tplan_table_row>";
											echo "<div class='irit_pay_date2'>$payment_date</div>";
											echo "<div class='irit_pay_recipt2'>$receipt_num</div>";
										echo "</div>";		
									}
								echo "</div>";
						echo "</div>";//	tplan_table_row
				}
			echo "</div>";//end income_report_invoices_table
			//show total
			?>
			<div class=income_report_invoices_table>
				<div class='tplan_table_row2 total_background'>
					<div class='irit_total1 make_bold'>TOTAL</div>
					<div class='irit_tot_bill make_bold'><?php echo number_format($total_biled_cost,2); ?></div>
					<div class='irit_tot_auth make_bold'><?php echo number_format($total_authorised_cost,2); ?></div>
					<div class='irit_tot_pay make_bold'><?php echo number_format($total_sum_paid,2); ?></div>
					<div class='irit_total11'></div>
				</div>
			</div>
			<?php
			$total_sum_out_of_period=$total_sum_paid;
		}
		//gross total
		?>
		<!--gross total-->
		<br><br>
		<?php
			$header='';
			if($income_report==3){$header="GROSS TOTAL INCOME BETWEEN $from_date and $to_date : ";}
			elseif($income_report==1){$header="GROSS TOTAL INCOME ON $from_date : ";}	
		?>
		<div class='grid-100 no_padding make_bold total_background'>
			<div class='grid-50 alpha'><?php echo "$header"; ?></div>
			<div class='grid-50 omega'><?php echo number_format($total_sum_cash + $total_sum_invoice_in_period + $total_sum_out_of_period, 2); ?></div>
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
					$amount=number_format($cost,2);
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>".number_format($amount, 2)."</td></tr>";
					
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
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>".number_format($amount, 2)."</td></tr>";
					
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
			$caption="NONE INSURANCE PAYMENTS BETWEEN $from_date and $to_date";
		}
		elseif($income_report==2){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['from_date']);
			$caption="NONE INSURANCE PAYMENTS ON $from_date";
		}

		//get points
		$total_points=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0) from payments as a join patient_details_a as b on a.pid=b.pid and b.internal_patient=0
		where when_added >=:from_date and when_added <=:to_date and a.invoice_id=0 
		and a.pay_type=8";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_points=html($row[0]);
			echo "<div class='grid-50 make_bold'>TOTAL POINTS USED</DIV>
				<div class='grid-50 make_bold'>".number_format($total_points, 2)."</div>";
		}
		
		//xray referal
		$total_xray=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0) from payments as a join patient_details_a as b on a.pid=b.pid and b.internal_patient=1
		where when_added >=:from_date and when_added <=:to_date and a.invoice_id=0 
		and a.pay_type!=6 and a.pay_type!=7 and a.pay_type!=8 and a.pay_type!=10";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_xray=html($row[0]);
			echo "<div class='grid-50 make_bold'>TOTAL CASH FROM X-RAY REFERALS</DIV>
				<div class='grid-50 make_bold'>".number_format($total_xray, 2)."</div>";
		}
		
		//cadcam referal
		$total_cadcam=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0) from payments as a join patient_details_a as b on a.pid=b.pid and b.internal_patient=2
		where when_added >=:from_date and when_added <=:to_date and a.invoice_id=0 
		and a.pay_type!=6 and a.pay_type!=7 and a.pay_type!=8 and a.pay_type!=10";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_cadcam=html($row[0]);
			echo "<div class='grid-50 make_bold'>TOTAL CASH FROM CADCAM REFERALS</DIV>
				<div class='grid-50 make_bold'>".number_format($total_cadcam, 2)."</div>";
		}		
		
		//molars insternal cahs
		$total_molars_cash=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0) from payments as a join patient_details_a as b on a.pid=b.pid and b.internal_patient=0
		where when_added >=:from_date and when_added <=:to_date and a.invoice_id=0 
		and a.pay_type!=6 and a.pay_type!=7 and a.pay_type!=8 and a.pay_type!=10";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_molars_cash=html($row[0]);
			echo "<div class='grid-50 make_bold'>TOTAL CASH FROM CUSPID PATIENTS</DIV>
				<div class='grid-50 make_bold'>".number_format($total_molars_cash, 2)."</div>";
		}		
		
		//show total cash income
		echo "<div class='grid-50 make_bold total_background'>TOTAL CASH INCOME</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_molars_cash + $total_cadcam + $total_xray, 2)."</div>";
		
		echo "<br>";
		//total invoices raised in this period
		$total_unaoth_inv=$total_aoth_inv=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(unauthorised_cost), 0) , ifnull(sum(authorised_cost), 0) from tplan_procedure as a where
		a.date_invoiced >=:from_date and a.date_invoiced <=:to_date and a.invoice_id > 0";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_unaoth_inv=html($row[0]);
			$total_aoth_inv=html($row[1]);
			//unaothorised
			echo "<div class='grid-50 make_bold total_background'>TOTAL INVOICES BILLED</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_unaoth_inv, 2)."</div>";
			//authorised
			echo "<div class='grid-50 make_bold total_background'>TOTAL INVOICES AUTHORISED</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_aoth_inv, 2)."</div>";
		}
		
		//show invoices raised and paid in this period
		$total_inv_paid=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0)  from payments as a join tplan_procedure as b on a.treatment_procedure_id=b.treatment_procedure_id
			and b.date_invoiced >=:from_date and b.date_invoiced <=:to_date and a.invoice_id > 0 and 
			a.when_added >=:from_date and a.when_added <=:to_date";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_inv_paid=html($row[0]);
			//unaothorised
			echo "<div class='grid-50 make_bold total_background'>TOTAL INVOICES PAID</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_inv_paid, 2)."</div>";

		}
		
		//show invoices raised before this period but  paid in this period
		$total_raised_before_inv_paid=0;
		$sql=$error=$s='';$placeholders=array();
		$sql="select ifnull(sum(amount), 0)  from payments as a join tplan_procedure as b on a.treatment_procedure_id=b.treatment_procedure_id
			and b.date_invoiced <:from_date and a.invoice_id > 0 and 
			a.when_added >=:from_date and a.when_added <=:to_date";
		$error="Unable to get pay type report self";
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$total_raised_before_inv_paid=html($row[0]);
			//unaothorised
			echo "<div class='grid-50 make_bold total_background'>TOTAL RAISED BEFORE THIS PERIOD BUT PAID IN THIS PERIOD</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_raised_before_inv_paid, 2)."</div>";

		}		
		echo "<div class='grid-50 make_bold total_background'>GROSS TOTAL</DIV>
			<div class='grid-50 make_bold total_background'>".number_format($total_raised_before_inv_paid + $total_inv_paid +
			$total_molars_cash + $total_cadcam + $total_xray, 2)."</div>";
			
			
		//now get invoices raised on that day
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.invoice_number, procedures.name, a.teeth, a.details, a.treatment_procedure_id, 
			a.date_invoiced, b.first_name, b.middle_name, b.last_name, b.patient_number, unauthorised_cost, 
			authorised_cost, covered_company.name, insurance_company.name, NULL , ifnull(f.sum_paid,0) as sum_paid, NULL , 
			NULL , 	NULL , NULL , NULL , NULL , NULL, b.internal_patient
				FROM tplan_procedure AS a
				JOIN patient_details_a AS b ON a.pid = b.pid
				AND a.pay_type =1
				AND date_invoiced >=:from_date
				AND date_invoiced <=:to_date
				AND a.invoice_id >0
				JOIN procedures ON a.procedure_id = procedures.id
				LEFT JOIN covered_company ON covered_company.id = b.company_covered
				LEFT JOIN insurance_company ON insurance_company.id = b.type
				LEFT JOIN (

				SELECT treatment_procedure_id, ifnull( sum( amount ) , 0 ) AS sum_paid
				FROM payments  where when_added >=:from_date
				AND when_added <=:to_date
				GROUP BY treatment_procedure_id
				) AS f ON f.treatment_procedure_id = a.treatment_procedure_id ";	
			
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$i=0;
		if($s->rowCount() > 0){
			if($income_report==3){
				$caption=strtoupper("TREATMENTS INVOICED BETWEEN $from_date and $to_date");
			}
			elseif($income_report==1){
				$caption="TREATMENTS INVOICED ON $from_date";
			}	
			 ?>
				
				<div class=income_report_invoices_table><div class=tplan_table_caption><?php echo "$caption"; ?></div>
				<div class=tplan_table_row2>
					<div class='irit_count white_text make_bold'></div><div class='irit_date_invoice white_text make_bold'>DATE<br>INVOICED</div>
					<div class='irit_pname white_text make_bold'>PATIENT</div><div class='irit_ptype white_text make_bold'>PATIENT TYPE</div>
					<div class='irit_inv_num white_text make_bold'>INVOICE<br>NUMBER</div><div class='irit_procedure white_text make_bold'>TREATMENT</div>
					<div class='irit_uncost white_text make_bold'>BILLED<br>COST</div><div class='irit_acost white_text make_bold'>AUTHORISED<br>COST</div>
					<div class='irit_paid white_text make_bold'>AMOUNT<br>PAID</div>
					<div class='irit_pay_details_hold white_text make_bold'>
						<div class=tplan_table_row2>
								<div class='irit_pay_date2 white_text make_bold'>PAYMENT<br>DATE</div>
								<div class='irit_pay_recipt2 white_text make_bold'>RECEIPT NUMBER</div>
							</div>	
						</div>
				</div>
				</div>
			<div class=income_report_invoices_table>
				<?php 
				$i=0; 
				$total_sum_paid=$total_authorised_cost=$total_biled_cost=0;
				foreach($s as $row){
					$i++;
					$date_invoiced=html($row['date_invoiced']);
					$pnum=html($row['patient_number']);
					$pname=html("$row[6] $row[7] $row[8]");
					$insurer=html("$row[13]");
					$company=html("$row[12]");
					if($company!=''){$ptype="$insurer - $company";}
					else{$ptype="$insurer";}
					$invoice_number=html($row['invoice_number']);
					$procedure_name=html("$row[1]");
					$teeth=html("$row[teeth]");
					$procedure_details=html("$row[details]");
					if($procedure_name=='X-Ray'){$treatment="$procedure_details $teeth";}
					else{$treatment="$procedure_name $teeth $procedure_details";}
					$billed_cost=html($row['unauthorised_cost']);
					$total_biled_cost=$total_biled_cost + $billed_cost;
					$authorised_cost=html($row['authorised_cost']);
					$total_authorised_cost=$total_authorised_cost + $authorised_cost;
					$sum_paid=html($row['sum_paid']);
					$total_sum_paid=$total_sum_paid + $sum_paid;
					$bgcolor='';
					if($row['internal_patient']==1){
						//for xray referral
						$bgcolor='blue_shade_background';
					}
					elseif($row['internal_patient']==2){
						//for cadcam referral
						$bgcolor='light_blue_background';
					}
					echo "<div class='tplan_table_row $bgcolor'>";
						echo "<div class=irit_count>$i</div>";//count
						echo "<div class=irit_date_invoice>$date_invoiced</div>";//date invoiced
						echo "<div class=irit_pname>$pname</div>";//patient name
						echo "<div class=irit_ptype>$ptype</div>";//patient type
						echo "<div class=irit_inv_num>$invoice_number</div>";//invoice number
						echo "<div class=irit_procedure>$treatment</div>";//procedure done
						echo "<div class=irit_uncost>".number_format($billed_cost,2)."</div>";//billed cost
						echo "<div class=irit_acost>";
							if($authorised_cost!=''){echo number_format($authorised_cost,2);}
							else {echo "&nbsp";}
						echo "</div>";//authorised cost
						echo "<div class=irit_paid>".number_format($sum_paid,2)."</div>";//sum amount paid
							//get payments for this treatment procedure
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT  amount,  tx_number, 	 receipt_num, when_added
								FROM payments where treatment_procedure_id=:treatment_procedure_id
								AND when_added >=:from_date 	AND when_added <=:to_date";	
							$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
							$placeholders2[':from_date']="$from_date";
							$placeholders2[':to_date']="$to_date";
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
								echo "<div class=irit_pay_details_hold>"; 	
									foreach($s2 as $row2){ 
										$payment_date=html($row2['when_added']);	
										$payment_transaction_details=html($row2['tx_number']);	
										$payment_amount=html($row2['amount']);	
										$receipt_num=html($row2['receipt_num']);	
										echo "<div class=tplan_table_row>";
											echo "<div class='irit_pay_date2'>$payment_date</div>";
											echo "<div class='irit_pay_recipt2'>$receipt_num</div>";
										echo "</div>";		
									}
								echo "</div>";
						echo "</div>";//	tplan_table_row
				}
			echo "</div>";//end income_report_invoices_table
			//show total
			?>
			<div class=income_report_invoices_table>
				<div class='tplan_table_row2 total_background'>
					<div class='irit_total1 make_bold'>TOTAL</div>
					<div class='irit_tot_bill make_bold'><?php echo number_format($total_biled_cost,2); ?></div>
					<div class='irit_tot_auth make_bold'><?php echo number_format($total_authorised_cost,2); ?></div>
					<div class='irit_tot_pay make_bold'><?php echo number_format($total_sum_paid,2); ?></div>
					<div class='irit_total11'></div>
				</div>
			</div>
			<?php
			$total_sum_invoice_in_period=$total_sum_paid;
		}
		
		//now get invoices raised before the selected period
		$sql=$error=$s='';$placeholders=array();
		$sql="SELECT a.invoice_number, procedures.name, a.teeth, a.details, a.treatment_procedure_id, 
			a.date_invoiced, b.first_name, b.middle_name, b.last_name, b.patient_number, unauthorised_cost, 
			authorised_cost, covered_company.name, insurance_company.name, NULL , ifnull(f.sum_paid,0) as sum_paid, NULL , 
			NULL , 	NULL , NULL , NULL , NULL , NULL, b.internal_patient
				FROM tplan_procedure AS a
				JOIN patient_details_a AS b ON a.pid = b.pid
				AND a.pay_type =1	AND date_invoiced <:from_date AND a.invoice_id >0
				JOIN procedures ON a.procedure_id = procedures.id
				JOIN (SELECT treatment_procedure_id, ifnull( sum( amount ) , 0 ) AS sum_paid
				   FROM payments  where when_added >=:from_date	   AND when_added <=:to_date
				     GROUP BY treatment_procedure_id) AS f ON f.treatment_procedure_id = a.treatment_procedure_id
				LEFT JOIN covered_company ON covered_company.id = b.company_covered
				LEFT JOIN insurance_company ON insurance_company.id = b.type
			";	
			
		$placeholders[':from_date']="$from_date";
		$placeholders[':to_date']="$to_date";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$i=0;
		if($s->rowCount() > 0){
			if($income_report==3){
				$caption=strtoupper("TREATMENTS INVOICED BEfore $from_date but paid between $from_date and $to_date");
			}
			elseif($income_report==1){
				$caption=strtoupper("TREATMENTS INVOICED BEfore $from_date but paid on $from_date");
			}	
			 ?>
				<br><br>
				<div class=income_report_invoices_table><div class=tplan_table_caption><?php echo "$caption"; ?></div>
				<div class=tplan_table_row2>
					<div class='irit_count white_text make_bold'></div><div class='irit_date_invoice white_text make_bold'>DATE<br>INVOICED</div>
					<div class='irit_pname white_text make_bold'>PATIENT</div><div class='irit_ptype white_text make_bold'>PATIENT TYPE</div>
					<div class='irit_inv_num white_text make_bold'>INVOICE<br>NUMBER</div><div class='irit_procedure white_text make_bold'>TREATMENT</div>
					<div class='irit_uncost white_text make_bold'>BILLED<br>COST</div><div class='irit_acost white_text make_bold'>AUTHORISED<br>COST</div>
					<div class='irit_paid white_text make_bold'>AMOUNT<br>PAID</div>
					<div class='irit_pay_details_hold white_text make_bold'>
						<div class=tplan_table_row2>
								<div class='irit_pay_date2 white_text make_bold'>PAYMENT<br>DATE</div>
								<div class='irit_pay_recipt2 white_text make_bold'>RECEIPT NUMBER</div>
							</div>	
						</div>
				</div>
				</div>
			<div class=income_report_invoices_table>
				<?php 
				$i=0; 
				$total_sum_paid=$total_authorised_cost=$total_biled_cost=0;
				foreach($s as $row){
					$i++;
					$date_invoiced=html($row['date_invoiced']);
					$pnum=html($row['patient_number']);
					$pname=html("$row[6] $row[7] $row[8]");
					$insurer=html("$row[13]");
					$company=html("$row[12]");
					if($company!=''){$ptype="$insurer - $company";}
					else{$ptype="$insurer";}
					$invoice_number=html($row['invoice_number']);
					$procedure_name=html("$row[1]");
					$teeth=html("$row[teeth]");
					$procedure_details=html("$row[details]");
					if($procedure_name=='X-Ray'){$treatment="$procedure_details $teeth";}
					else{$treatment="$procedure_name $teeth $procedure_details";}
					$billed_cost=html($row['unauthorised_cost']);
					$total_biled_cost=$total_biled_cost + $billed_cost;
					$authorised_cost=html($row['authorised_cost']);
					$total_authorised_cost=$total_authorised_cost + $authorised_cost;
					$sum_paid=html($row['sum_paid']);
					$total_sum_paid=$total_sum_paid + $sum_paid;
					$bgcolor='';
					if($row['internal_patient']==1){
						//for xray referral
						$bgcolor='blue_shade_background';
					}
					elseif($row['internal_patient']==2){
						//for cadcam referral
						$bgcolor='light_blue_background';
					}
					echo "<div class='tplan_table_row $bgcolor'>";
						echo "<div class=irit_count>$i</div>";//count
						echo "<div class=irit_date_invoice>$date_invoiced</div>";//date invoiced
						echo "<div class=irit_pname>$pname</div>";//patient name
						echo "<div class=irit_ptype>$ptype</div>";//patient type
						echo "<div class=irit_inv_num>$invoice_number</div>";//invoice number
						echo "<div class=irit_procedure>$treatment</div>";//procedure done
						echo "<div class=irit_uncost>".number_format($billed_cost,2)."</div>";//billed cost
						echo "<div class=irit_acost>";
							if($authorised_cost!=''){echo number_format($authorised_cost,2);}
							else {echo "&nbsp";}
						echo "</div>";//authorised cost
						echo "<div class=irit_paid>".number_format($sum_paid,2)."</div>";//sum amount paid
							//get payments for this treatment procedure
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT  amount,  tx_number, 	 receipt_num, when_added
								FROM payments where treatment_procedure_id=:treatment_procedure_id
								AND when_added >=:from_date 	AND when_added <=:to_date";	
							$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
							$placeholders2[':from_date']="$from_date";
							$placeholders2[':to_date']="$to_date";
							$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
								echo "<div class=irit_pay_details_hold>"; 	
									foreach($s2 as $row2){ 
										$payment_date=html($row2['when_added']);	
										$payment_transaction_details=html($row2['tx_number']);	
										$payment_amount=html($row2['amount']);	
										$receipt_num=html($row2['receipt_num']);	
										echo "<div class=tplan_table_row>";
											echo "<div class='irit_pay_date2'>$payment_date</div>";
											echo "<div class='irit_pay_recipt2'>$receipt_num</div>";
										echo "</div>";		
									}
								echo "</div>";
						echo "</div>";//	tplan_table_row
				}
			echo "</div>";//end income_report_invoices_table
			//show total
			?>
			<div class=income_report_invoices_table>
				<div class='tplan_table_row2 total_background'>
					<div class='irit_total1 make_bold'>TOTAL</div>
					<div class='irit_tot_bill make_bold'><?php echo number_format($total_biled_cost,2); ?></div>
					<div class='irit_tot_auth make_bold'><?php echo number_format($total_authorised_cost,2); ?></div>
					<div class='irit_tot_pay make_bold'><?php echo number_format($total_sum_paid,2); ?></div>
					<div class='irit_total11'></div>
				</div>
			</div>
			<?php
			$total_sum_out_of_period=$total_sum_paid;
		}
		//gross total
		?>
		<!--gross total-->
		<br><br>
		<?php
			$header='';
			if($income_report==3){$header="GROSS TOTAL INCOME BETWEEN $from_date and $to_date : ";}
			elseif($income_report==1){$header="GROSS TOTAL INCOME ON $from_date : ";}	
		?>
		<div class='grid-100 no_padding make_bold total_background'>
			<div class='grid-50 alpha'><?php echo "$header"; ?></div>
			<div class='grid-50 omega'><?php echo number_format($total_sum_cash + $total_sum_invoice_in_period + $total_sum_out_of_period, 2); ?></div>
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
					$amount=number_format($cost,2);
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>".number_format($amount, 2)."</td></tr>";
					
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
					echo "<tr><td>$i</td><td>$date</td><td>$user_name</td><td>$expense_name</td><td>".number_format($amount, 2)."</td></tr>";
					
				}
				echo "<tr class='make_bold total_background'><td colspan=4>TOTAL NONE INCOME DEDUCTABLE EXPENSES</td><td>".number_format($total,2)."</td></tr></tbody></table>";
		}		
		exit;
		
	}	
}	


?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
		<div class='grid-100 '>
			<div class='grid-15 '><label for="" class="label">Search by</label>
					<?php $token = form_token(); $_SESSION['token_ir1'] = "$token";  ?>
					<input type="hidden" name="token_ir1"  value="<?php echo $_SESSION['token_ir1']; ?>" />
			</div>	
				<?php
					$sql=$error=$s='';$placeholders=array();
					$sql="select a.id, a.name from sub_menus a, sub_privileges b where b.parent_menu_id=105  and b.user_id=:user_id and b.sub_menu_id=a.id";
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
						$sql="select a.id, a.name from sub_menus a, role_sub_privileges b , user_roles c where b.parent_menu_id=105  and c.user_id=:user_id and 
						c.role_id=b.role_id and b.sub_menu_id=a.id";
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
				<div class='grid-10 date_criteria '><input type=text name=from_date class=date_picker /></div>
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