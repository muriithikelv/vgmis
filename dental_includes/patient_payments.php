<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,72)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT PAYMENTS</div>";

if(isset($_POST['search_by']) and $_POST['search_by']!='' and isset($_POST['pp_token']) and 
			isset($_SESSION['pp_token']) and $_POST['pp_token']==$_SESSION['pp_token']){
			$receipt_criteria = '';
			$criteria=$_POST['search_by'];
			$patient_number=$_POST['search_ciretia'];
			//get patient details a
			$sql=$error=$s='';$placeholders=array();	
			if($criteria=="patient_number"){$sql="select * from patient_details_a where patient_number=:patient_number";}
			elseif($criteria=="receipt_number"){
				$sql="select a.first_name,a.middle_name,a.last_name,a.company_covered,a.type,a.pid,a.patient_number,a.member_no,a.family_id,a.family_title,
					a.insurance_cover_role from patient_details_a as a join payments as b on a.pid=b.pid and b.receipt_num=:patient_number";
					
			}
			elseif($criteria=="pid"){$sql="select * from patient_details_a where pid=:patient_number";}
						//by patient names
			elseif($_POST['search_by']=='first_name' or $_POST['search_by']=='middle_name' or $_POST['search_by']=='last_name'){	
				//$result=get_pt_name2($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt,'pp_token','search_by','patient_number','search_ciretia');
				$result=get_pt_internal_and_external($_POST['search_by'],$_POST['search_ciretia'],$pdo,$encrypt,'pp_token','search_by','patient_number','search_ciretia');
				
				if($result=="2"){echo "<div class='error_response'>No such patient</div>";}
				else{
					echo " $result";
					exit;
				}
				
			}
			if($sql!=''){
				$placeholders[':patient_number']="$patient_number";
				$error="Error: Unable to get patient details a";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()>0){
					foreach($s as $row){
						$last_name=ucfirst(html($row['last_name']));
						$middle_name=ucfirst(html($row['middle_name']));
						$first_name=ucfirst(html($row['first_name']));
						$type=html($row['type']);
						$patient_number=html($row['patient_number']);
						$pid_clean=html($row['pid']);
						$pid=$encrypt->encrypt(html($row['pid']));
						
						$member_no=html($row['member_no']);
						$company_covered=html($row['company_covered']);
						$family_id=html($row['family_id']);
						$family_title=html($row['family_title']);
						$insurance_cover_role=html($row['insurance_cover_role']);
						
					}
					//get company_covered_name and type_name
					$company_covered_name=$type_name='';
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from covered_company where id=:covered_company";
					$placeholders2[':covered_company']=$company_covered;
					$error2="Error: Unable to get covered company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$company_covered_name=html($row2['name']);}
					
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from insurance_company where id=:type";
					$placeholders2[':type']=$type;
					$error2="Error: Unable to get insurance company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$type_name=html($row2['name']);}	
				}
				else{ echo "<div class='error_response'>No such patient</div>";}
			}			
}
?>
<div class='grid-container '>
	<div class='feedback hide_element'></div>
	<form class='' action='' method="POST"  name="" id="">
		<div class='grid-15'>
			<?php $token = form_token(); $_SESSION['pp_token'] = "$token";  ?>
			<input type="hidden" name="pp_token"  value="<?php echo $_SESSION['pp_token']; ?>" />
			<label for="" class="label">Search Patient by</label>
		</div>
		<div class='grid-15'>
			<select name=search_by><option></option>
				<option value=patient_number>Patient Number</option>
				<option value=first_name>First Name</option>
				<option value=middle_name>Middle Name</option>
				<option value=last_name>Last Name</option>
				<option value=receipt_number>Receipt Number</option>
			</select>
		</div>
		<div class='grid-25'><input type=text name=search_ciretia  /></div>
		<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Find"  /></div>
		
	</form>
<div class=clear></div><br>
<?php

if(isset($pid) and $pid!=''){
		$result = show_pt_statement_brief($pdo,$encrypt->encrypt("$pid_clean"),$encrypt);
		$data=explode('#',"$result");
	echo "<table>
		<thead>
		<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
		<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
		<tbody><td>$patient_number</td><td>$first_name</td><td>$middle_name</td><td>$last_name</td>
		<td>$type_name</td><td>$company_covered_name</td><td>$data[0]</td><td>$data[1]</td><td>$data[2]</td><td>limit</td><td>expiry</td></tbody></table>";
		//if($_SESSION['insurance_mismatch_error'] != ''){echo "<div class='error_response'>$_SESSION[insurance_mismatch_error]</div>";}

		//get payments if any for this pid
		$sql=$error1=$s='';$placeholders=array();
		$sql="select a.when_added,a.id,a.invoice_id, a.receipt_num, a.amount, a.pay_type,a.tx_number,a.balance,b.first_name, b.middle_name, b.last_name,b.patient_number ,
		c.name
		from payments as a join patient_details_a as b on a.pid=b.pid
		left join payment_types as c on c.id=a.pay_type
		where a.pid=:pid";
		if($criteria=="receipt_number"){
			$sql="select a.when_added,a.id,a.invoice_id, a.receipt_num, a.amount, a.pay_type,a.tx_number,a.balance,b.first_name, b.middle_name, 
					b.last_name,b.patient_number ,	c.name
					from payments as a join patient_details_a as b on a.pid=b.pid and a.receipt_num=:receipt_num
					left join payment_types as c on c.id=a.pay_type
					where a.pid=:pid";
					$placeholders[':receipt_num']=$_POST['search_ciretia'];
		}
		$error="Unable to get payments";
		$placeholders[':pid']=$pid_clean;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){
			$i=0;
			foreach($s as $row){
				$pay_id=$encrypt->encrypt($row['id']);
				$pay_type=strtoupper(html($row['name']));
				$tx_no=html($row['tx_number']);
				$receipt_number=strtoupper(html($row['receipt_num']));
				$date=html($row['when_added']);
				$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
				$file_no=html($row['patient_number']);
				$amount=number_format(html($row['amount']),2);
				$balance=strtoupper(html($row['balance']));
				//check if insurance pay mode
				if($row['invoice_id'] > 0){$pay_mode='Insurance';}
				elseif($row['invoice_id'] == 0){$pay_mode='Self';}
				if($i==0){
					echo "<table class='normal_table'><caption>Payments for patient number: $file_no - $name</caption><thead>
					<tr><th class='pp_count'></th><th class='pp_date'>DATE</th><th class='pp_mode'>PAY MODE</th><th class='pp_type'>PAY TYPE</th>
					<th class='pp_amount'>AMOUNT</th><th class='pp_receipt'>RECEIPT No.</th></tr></thead><tbody>";	
				}
				$i++;
				echo "<tr><td>$i</td><td>$date</td><td>$pay_mode</td><td>$pay_type $tx_no</td><td>$amount</td>
					<td><input type=hidden value=$pay_id /><input type=button class='button_style button_in_table_cell reprint_receipt' value=$receipt_number /></td></tr>";
			}
			echo "</tbody></table>";
		}
		else{echo "<label class=label>There are no payment records for the patient</label>";}
	}//end pid if	 
	?>
	
			
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>