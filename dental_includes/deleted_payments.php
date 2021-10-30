<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,102)){exit;}
echo "<div class='grid_12 page_heading'>DELETED PAYMENTS</div>";
?>
<div class=grid-container>
<?php 

//get results
if(isset($_POST['token_dpr1']) and 	$_POST['token_dpr1']!='' and $_POST['token_dpr1']==$_SESSION['token_dpr1']){
		$_SESSION['token_dpr1']='';
		$exit_flag=false;
		$insurer=' all ';
		$covered_company='';$comp_covered=$pnum=$date_criteria=$inv_num_criteria='';
		$pnum_search=$exit_flag=false;
		$sql2=$error2=$s2='';$placeholders2=array();
		

		//check if dates are selected
		if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){	
				$result_class="error_response";
				$result_message="Please specify the date range for the report";
				$exit_flag=true;
		}	
		
				
		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$doctor=$insurer=$company=$balance='';
		$total_cost=$total_paid=0;
				
			$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, b.when_added,
			b.receipt_num,b.amount, c.name , b.id, b.invoice_id,
			concat(d.first_name,' ',d.middle_name,' ',d.last_name) as deleted_by, b.when_deleted
			from deleted_payments as b join patient_details_a as a on  b.pid=a.pid and b.when_added >=:from_date and b.when_added <=:to_date 
			join users as d on b.deleter=d.id
			left join payment_types as c on c.id=b.pay_type order by b.id
			";
				
			$placeholders2[':from_date']=$_POST['from_date'];	
			$placeholders2[':to_date']=$_POST['to_date'];
			$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	
			$i=$total=0;
			if($s2->rowCount() > 0){ 
				foreach($s2 as $row2 ){
					$patient_name=html($row2['patient_name']);
					$patient_number=html($row2['patient_number']);
					$payment_date=html($row2['when_added']);
					$amount=number_format(html($row2['amount']),2);
					
					$receipt_number=html($row2['receipt_num']);
					$payment_type=html($row2['name']);
					if($row2['invoice_id'] > 0){$payment_type="Insurance - $payment_type";}
					$date_deleted=html($row2['when_deleted']);
					$deleted_by=html($row2['deleted_by']);
					if($i==0){
						$caption=strtoupper("payments deleted between $from_date and $to_date");
						echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=dpr_count></th>
						<th class=dpr_date>PAYMENT DATE</th><th class=dpr_name>PATIENT NAME</th><th class=dpr_pnum>PATIENT NUMBER</th>
						<th class=dpr_ptype>PAYMENT TYPE</th><th class=dpr_receipt>RECEIPT NUMBER</th><th class=dpr_amount>AMOUNT PAID</th>
						<th class=dpr_deleter>DELETED BY</th><th class=dpr_date>DATE DELETED</th></tr></thead><tbody>";
					}
					$i++;
					echo "<tr><td>$i</td><td>$payment_date</td><td>$patient_name</td><td>$patient_number</td><td>$payment_type</td><td>$receipt_number</td>
					<td>$amount</td><td>$deleted_by</td><td>$date_deleted</td></tr>";
				}
				echo "</tbody></table><br>";
			}
			else{ echo "<div class='error_response'>There are no deleted payments for the selected search criteria</div>";}
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
		
		
}	
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">

				<!--date range-->
				<div class=' grid-20'>
				<?php $token = form_token(); $_SESSION['token_dpr1'] = "$token";  ?>
					<input type="hidden" name="token_dpr1"  value="<?php echo $_SESSION['token_dpr1']; ?>" />
				
				<label for="" class="label">Payments deleted between</label></div>
				<div class=grid-25><input type=text name=from_date class=date_picker /></div>
				<div class=grid-5><label for="" class="label">And</label></div>
				<div class=grid-25><input type=text name=to_date class=date_picker /></div>
	<!--</div>-->
				<div class=clear></div>
				<br>
				<div class='prefix-60 grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>