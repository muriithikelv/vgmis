<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,98)){exit;}
echo "<div class='grid_12 page_heading'>CREDIT NOTE</div>";//check if this guy is a doctor
?>
<div class='grid-container completion_form'>
<?php
function get_for_pid($pdo, $pid, $from_date, $to_date, $encrypt){
	//get payments to be dleeted
	$sql2=$error2=$s2='';$placeholders2=array();
	if($pid!=''){
		$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, b.when_added,
			b.receipt_num,b.amount, c.name , b.id, b.invoice_id
			from payments as b join patient_details_a as a on b.pid=:pid and b.pid=a.pid
			left join payment_types as c on c.id=b.pay_type order by b.id";
		$placeholders2[':pid']=$pid;
		$error2="Error: Unable to get payments for patient ";
	}
	elseif($from_date!='' and $to_date!=''){
		$from_date=html("$from_date");
		$to_date=html("$to_date");
		$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, b.when_added,
			b.receipt_num,b.amount, c.name , b.id, b.invoice_id
			from payments as b join patient_details_a as a on  b.pid=a.pid and b.when_added >=:from_date and b.when_added <=:to_date 
			left join payment_types as c on c.id=b.pay_type order by b.id
			";
		$placeholders2[':from_date']=$from_date;
		$placeholders2[':to_date']=$to_date;
		$error2="Error: Unable to get payments for date range ";
	}	
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	$i=$total=0;
	if($s2->rowCount() > 0){ ?>
		<form class='' action='' method="POST"  name="" id="">
			<?php $token = form_token(); $_SESSION['token_delp2'] = "$token";  ?>
			<input type="hidden" name="token_delp2"  value="<?php echo $_SESSION['token_delp2']; ?>" />
		<?php
		foreach($s2 as $row2 ){
			$patient_name=html($row2['patient_name']);
			$patient_number=html($row2['patient_number']);
			$payment_date=html($row2['when_added']);
			$amount=number_format(html($row2['amount']),2);
			
			$receipt_number=html($row2['receipt_num']);
			$payment_type=html($row2['name']);
			if($row2['invoice_id'] > 0){$payment_type="Insurance - $payment_type";}
			$val=$encrypt->encrypt("$row2[id]");
			if($i==0){
				if($pid!=''){$caption=strtoupper("payments made by $patient_name - $patient_number");}
				elseif($from_date!='' and $to_date!=''){$caption=strtoupper("payments made between $from_date and $to_date");}
				echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=dp_count></th>
				<th class=dp_date>PAYMENT DATE</th><th class=dp_name>PATIENT NAME</th><th class=dp_pnum>PATIENT NUMBER</th>
				<th class=dp_ptype>PAYMENT TYPE</th><th class=dp_receipt>RECEIPT NUMBER</th><th class=dp_amount>AMOUNT PAID</th><th class=dp_del>SELECT</th></tr></thead><tbody>";
			}
			$i++;
			echo "<tr><td>$i</td><td>$payment_date</td><td>$patient_name</td><td>$patient_number</td><td>$payment_type</td><td>$receipt_number</td>
			<td>$amount</td><td><input type=checkbox name=del_receipt[] value=$val /></td></tr>";
		}
		echo "</tbody></table><br>";
		echo "<div class='grid-100'><input class='put_right' type=submit value=Submit  /></div></form>";
	}
	else{ echo "<div class='error_response'>There are no payments for the selected search criteria</div>";}
	exit;
}
if(isset($_SESSION['token_delp1']) and isset($_POST['token_delp1']) and $_POST['token_delp1']==$_SESSION['token_delp1']){
	$_SESSION['token_delp1']='';
	if($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'  or $_POST['search_by']=='patient_number' ){
			$criteria=$_POST['search_by'];
			$patient_number=$_POST['search_ciretia'];
			$sql=$error=$s='';$placeholders=array();	
			if($criteria=="patient_number"){$sql="select pid from patient_details_a where patient_number=:patient_number ";}
			elseif($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'){	
				$result=get_pt_name2($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt,'token_delp1','search_by','patient_number','search_ciretia');
				if($result=="2"){echo "<div class='error_response'>No such patient</div>";}
				else{
					echo "$result";
					exit;
				}
				
			}
			if($sql!=''){
				$placeholders[':patient_number']="$patient_number";
				$error="Error: Unable to get pid";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()>0){
					foreach($s as $row){$pid=html($row['pid']);}
					get_for_pid($pdo, $pid,'','', $encrypt);
				}
				else{ echo "<div class='error_response'>No such patient</div>";}
			}		
	}
	elseif($_POST['search_by']=='date_range'){
		//check dates
		if(!isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' and $_POST['to_date']==''){
			echo "<div class='error_response'>Please ensure that the date range is correctly specified</div>";
		}
		else{
			get_for_pid($pdo, '',$_POST['from_date'],$_POST['to_date'], $encrypt);
			exit;
		}
	} 
	
}
//[erform actual deletion
if(isset($_SESSION['token_delp2']) and isset($_POST['token_delp2']) and $_POST['token_delp2']==$_SESSION['token_delp2']){
	$_SESSION['token_delp2']='';
	$i=$n=0;
	if(isset($_POST['del_receipt'])){
		$pay_id=$_POST['del_receipt'];
		$n=count($pay_id);
		try{
				$pdo->beginTransaction();
					while($i < $n){
						$del_pay_id=$encrypt->decrypt("$pay_id[$i]");
						//copy record
						$sql=$error=$s='';$placeholders=array();
						$sql="select * from payments where id=:id";
						$error="Unable to get payments for deletion";
						$placeholders[':id']=$del_pay_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							//insert into deletion table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into deleted_payments
							set when_added=:when_added,
							receipt_num=:receipt_num,
							amount=:amount,
							pay_type=:pay_type,
							pid=:pid,
							tx_number=:tx_number,
							invoice_id=:invoice_id,
							receipt_num_id=:receipt_num_id,
							created_by=:created_by,
							balance=:balance,
							deleter=:deleter,
							when_deleted=now(),
							id=:id";
							$error2="Unable to delete  payments 1";
							$placeholders2[':when_added']=$row['when_added'];
							$placeholders2[':receipt_num']=$row['receipt_num'];
							$placeholders2[':amount']=$row['amount'];
							$placeholders2[':pay_type']=$row['pay_type'];
							$placeholders2[':pid']=$row['pid'];
							$placeholders2[':tx_number']=$row['tx_number'];
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$placeholders2[':receipt_num_id']=$row['receipt_num_id'];
							$placeholders2[':created_by']=$row['created_by'];
							$placeholders2[':balance']=$row['balance'];
							$placeholders2[':deleter']=$_SESSION['id'];
							$placeholders2[':id']=$row['id'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
							
							
						}
						
						//now dleete the payment
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from payments where id=:id";
						$error="Unable to get delete payments for deletion";
						$placeholders[':id']=$del_pay_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);				
						
						//update pt_balances
						$pid_encrypt2=$encrypt->encrypt($row['pid']);
						$result=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
						
							
						$i++;
					}
				if($s){
					$tx_result=$pdo->commit();
					if($tx_result ){echo "<div class='success_response'>Payments  deleted</div>";}
				}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		
		}
	}
	else{echo "<div class='error_response'>No Payment was selected for deletion</div>";}
		
		
}
?>


<form class='' action='' method="POST"  name="" id="">
	<div class='grid-10'>
		<?php $token = form_token(); $_SESSION['token_delp1'] = "$token";  ?>
		<input type="hidden" name="token_delp1"  value="<?php echo $_SESSION['token_delp1']; ?>" />
		<label for="" class="label">Search by</label>
	</div>
	<div class='grid-15'>
		<select name=search_by class=src ><option></option>
			<option value=patient_number>Patient Number</option>
			<option value=first_name>First Name</option>
			<option value=middle_name>Middle Name</option>
			<option value=last_name>Last Name</option>
			<option value=date_range>Date Range</option>
		</select>
	</div>
	<div class='grid-60 search_by_patient '>
		<div class='grid-25'><input type=text name=search_ciretia  /></div>
		<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Submit"  /></div>
	</div>
	<div class=clear></div>
	<div class='grid-100 search_by_date no_padding'>
		<br>
		<div class='grid-25 label'>Payments made between this date</div>
		<div class='grid-10'><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10 label'>And this date</div>
		<div class='grid-10'><input type=text name=to_date class=date_picker /></div>
		<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Submit"  /></div>
	</div>	
	
</form>	 
</div>

