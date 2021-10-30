<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,108)){exit;}
echo "<div class='grid_12 page_heading'>INVOICE SEARCH</div>";
?>
<div class=grid-container>
<?php 
echo "<div class='feedback hide_element'></div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}

//this function will dispaly invoices for a given pid		
function show_invoice($pdo,$pid){
	//get pt names and patient number 
	$sql=$error=$s='';$placeholders=array();
	$sql="select first_name, middle_name, last_name, patient_number from patient_details_a where pid=:pid";
	$error="Unable to get invoices for patient in tdone";
	$placeholders[':pid']=$pid;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html(ucfirst("$row[first_name] $row[middle_name] $row[last_name] "));
		$patient_number=html($row['patient_number']);
	}
	
	$sql=$error=$s='';$placeholders=array();
	$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced),  
				 covered_company.pre_auth_needed, 
				covered_company.smart_needed, 
				invoice_authorisation.authorisation_sent, invoice_authorisation.authorisation_received, 
				invoice_authorisation.smart_run, 
				invoice_authorisation.amount_authorised, invoice_authorisation.comments,invoice_authorisation.smart_amount,  
				users.first_name,users.middle_name,users.last_name , 
				sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested, 
				sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_approved 
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid 
				join users on tplan_procedure.created_by=users.id
				join covered_company on patient_details_a.company_covered=covered_company.id 
				left join invoice_authorisation on tplan_procedure.invoice_id=invoice_authorisation.invoice_id 
				left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
				where tplan_procedure.invoice_id > 0  and tplan_procedure.pid=:pid 
				
				group by tplan_procedure.invoice_id  order by tplan_procedure.invoice_id";
	$error="Unable to get invoices for patient in tdone";
	$placeholders[':pid']=$pid;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$i=0;
	 $smart_amount2='';
	if($s->rowCount() > 0){
		foreach($s as $row){
			if($i==0){
				$caption="Invoices raised for patient: $patient_number - $patient_name";
				echo "<table class='normal_table'><caption>$caption</caption><thead>
							<tr>
							<th class=ar_inv>INVOICE No.</th>
							<th class=ar_date>DATE</th>
							<th class=ar_doc>DOCTOR</th>
							<th class=ar_inv>BILLED<br>COST</th>
							<th class=ar_inv>AUTHORISED<br>COST.</th>
							<th class=ar_status>STATUS</th>
							<th class=ar_pre_sent>PRE-AUTH<br>SENT</th>
							<th class=ar_pre_received>PRE-AUTH<br>RECEIVED</th>
							<th class=ar_smart>SMART<br>CHECKED</th>
							<th class=ar_comment>COMMENTS</th>
							</tr></thead><tbody>";
			
			}
			$invoice_num=html("$row[invoice_number]");
			$date=html("$row[2]");
			$doc=html("$row[first_name] $row[middle_name] $row[last_name]");
			$billed=html("$row[amount_requested]");
			if($billed!=''){$billed=number_format($billed,2);}
			$authorised=html("$row[amount_approved]");
			if($authorised!=''){$authorised=number_format($authorised,2);}
			$status=get_invoice_status($row['invoice_id'],$pdo);
			$pre_sent=html("$row[authorisation_sent]");
			$pre_receive=html("$row[authorisation_received]");
			$amount_authorised=html("$row[amount_authorised]");
			$smart_date=html("$row[smart_run]");
			$smart_amount=html("$row[smart_amount]");
			$comments=html("$row[comments]");
			$pre_auth_amount=$smart_amount='';
			if($pre_receive!='' and $amount_authorised!=''){$pre_auth_amount="<br>".number_format($amount_authorised,2);}
			if($smart_date!='' and $smart_amount!=''){$smart_amount2="<br>".number_format($smart_amount,2);}
			$pre_receive = "$pre_receive $pre_auth_amount";
			$smart_date ="$smart_date $smart_amount2";
			if($row['pre_auth_needed']!='YES'){}
			if($row['smart_needed']!='YES'){}
			
			//check if the entry is for an aliased invoice
			$is_invoice_aliased=0;
			$aliased='';
			if(  $row['invoice_id'] > 0){
					$is_invoice_aliased = is_invoice_id__alias($pdo,$row['invoice_id']);
					if($is_invoice_aliased == 1){$aliased="<br>Alias";}
			}
			
			echo "<tr><td ><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_num />$aliased</td><td >$date</td><td >$doc</td><td >$billed</td><td >$authorised</td><td >$status</td>";
				//check if pre-auth is need
				if($row['pre_auth_needed']!='YES'){echo "<td colspan=2>N/A</td>";}
				elseif($row['pre_auth_needed']=='YES'){echo "<td>$pre_sent</td><td>$pre_receive</td>";}
				//check if smart is needed
				if($row['smart_needed']!='YES'){echo "<td >N/A</td>";}
				elseif($row['smart_needed']=='YES'){echo "<td >$smart_date</td>";}
			echo "<td >$comments</td></tr>";
			$i++;
		}
		echo "</table>";
	}
	else{echo "<label class=label>This patient has no invoices</label>";}
	
	exit;
}


//this function will dispaly invoices for a given invoice number		
function show_invoice_by_invoice_number($pdo,$pid,$invoice_id,$invoice_number){
	//get pt names and patient number 
	$sql=$error=$s='';$placeholders=array();
	$sql="select first_name, middle_name, last_name, patient_number from patient_details_a where pid=:pid";
	$error="Unable to get invoices for patient in tdone";
	$placeholders[':pid']=$pid;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$patient_name=html(ucfirst("$row[first_name] $row[middle_name] $row[last_name] "));
		$patient_number=html($row['patient_number']);
	}
	
	$sql=$error=$s='';$placeholders=array();
	$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced),  
				 covered_company.pre_auth_needed, 
				covered_company.smart_needed, 
				invoice_authorisation.authorisation_sent, invoice_authorisation.authorisation_received, 
				invoice_authorisation.smart_run, 
				invoice_authorisation.amount_authorised, invoice_authorisation.comments,invoice_authorisation.smart_amount,  
				users.first_name,users.middle_name,users.last_name , 
				sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested, 
				sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_approved 
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid 
				join users on tplan_procedure.created_by=users.id
				join covered_company on patient_details_a.company_covered=covered_company.id 
				left join invoice_authorisation on tplan_procedure.invoice_id=invoice_authorisation.invoice_id 
				left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
				where tplan_procedure.invoice_id=:invoice_id				
				group by tplan_procedure.invoice_id  order by tplan_procedure.invoice_id";
	$error="Unable to get invoices for patient in tdone";
	$placeholders[':invoice_id']=$invoice_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$i=0;
	 $smart_amount2='';
	if($s->rowCount() > 0){
		foreach($s as $row){
			if($i==0){
				$caption="Invoice No. $invoice_number raised for patient: $patient_number - $patient_name";
				echo "<table class='normal_table'><caption>$caption</caption><thead>
							<tr>
							<th class=ar_inv>INVOICE No.</th>
							<th class=ar_date>DATE</th>
							<th class=ar_doc>DOCTOR</th>
							<th class=ar_inv>BILLED<br>COST</th>
							<th class=ar_inv>AUTHORISED<br>COST.</th>
							<th class=ar_status>STATUS</th>
							<th class=ar_pre_sent>PRE-AUTH<br>SENT</th>
							<th class=ar_pre_received>PRE-AUTH<br>RECEIVED</th>
							<th class=ar_smart>SMART<br>CHECKED</th>
							<th class=ar_comment>COMMENTS</th>
							</tr></thead><tbody>";
			
			}
			$invoice_num=html("$row[invoice_number]");
			$date=html("$row[2]");
			$doc=html("$row[first_name] $row[middle_name] $row[last_name]");
			$billed=html("$row[amount_requested]");
			if($billed!=''){$billed=number_format($billed,2);}
			$authorised=html("$row[amount_approved]");
			if($authorised!=''){$authorised=number_format($authorised,2);}
			$status=get_invoice_status($row['invoice_id'],$pdo);
			$pre_sent=html("$row[authorisation_sent]");
			$pre_receive=html("$row[authorisation_received]");
			$amount_authorised=html("$row[amount_authorised]");
			$smart_date=html("$row[smart_run]");
			$smart_amount=html("$row[smart_amount]");
			$comments=html("$row[comments]");
			$pre_auth_amount=$smart_amount='';
			if($pre_receive!='' and $amount_authorised!=''){$pre_auth_amount="<br>".number_format($amount_authorised,2);}
			if($smart_date!='' and $smart_amount!=''){$smart_amount2="<br>".number_format($smart_amount,2);}
			$pre_receive = "$pre_receive $pre_auth_amount";
			$smart_date ="$smart_date $smart_amount2";
			if($row['pre_auth_needed']!='YES'){}
			if($row['smart_needed']!='YES'){}
			
			//check if the entry is for an aliased invoice
			$is_invoice_aliased=0;
			$aliased='';
			if(  $row['invoice_id'] > 0){
					$is_invoice_aliased = is_invoice_id__alias($pdo,$row['invoice_id']);
					if($is_invoice_aliased == 1){$aliased="<br>Alias";}
			}
			
			echo "<tr><td ><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_num />$aliased</td><td >$date</td><td >$doc</td><td >$billed</td><td >$authorised</td><td >$status</td>";
				//check if pre-auth is need
				if($row['pre_auth_needed']!='YES'){echo "<td colspan=2>N/A</td>";}
				elseif($row['pre_auth_needed']=='YES'){echo "<td>$pre_sent</td><td>$pre_receive</td>";}
				//check if smart is needed
				if($row['smart_needed']!='YES'){echo "<td >N/A</td>";}
				elseif($row['smart_needed']=='YES'){echo "<td >$smart_date</td>";}
			echo "<td >$comments</td></tr>";
			$i++;
		}
		echo "</table>";
	}
	else{echo "<label class=label>This patient has no invoices</label>";}
	exit;
	
}

//get list of invoices
if(isset($_POST['token_is_1']) and 	$_POST['token_is_1']!='' and $_POST['token_is_1']==$_SESSION['token_is_1']){
		$exit_flag=false;
		$_SESSION['token_is_1']='';

		if(!$exit_flag and ($_POST['indv']=='inv_num'  or $_POST['indv']=='patient_number' or 
		$_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'  )){
			
			//check if serach criteriais set
			if(!$exit_flag and !isset($_POST['indv_crit']) or $_POST['indv_crit']==''   ){	
					$result_class="error_response";
					$result_message="Incorrect search criteria";
					$exit_flag=true;
			}
			
			//by invoice number
			if(!$exit_flag and $_POST['indv']=='inv_num'){
				//check if the invoice has a pid	
				$sql=$error=$s='';$placeholders=array();
				$sql="select pid , id ,invoice_number from unique_invoice_number_generator where invoice_number=:invoice_number";
				$error="Unable to get invoices for patient in tdone";
				$placeholders[':invoice_number']=$_POST['indv_crit'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){
					foreach($s as $row){
						$pid=html($row['pid']);
						$invoice_id=html($row['id']);
						$invoice_number=html($row['invoice_number']);
					}
					show_invoice_by_invoice_number($pdo,$pid,$invoice_id,$invoice_number);
				}
				else{
					$result_class="error_response";
					$result_message="No such invoice found";
					$exit_flag=true;
				}
			
			}
			
			//by patient names
			if(!$exit_flag and $_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'){	
				$result=get_pt_internal_and_external($_POST['indv'],$_POST['indv_crit'],$pdo,$encrypt,'token_is_1','indv','patient_number','indv_crit');
				if($result=="2"){
					$result_class="error_response";
					$result_message="No such patient found";
					$exit_flag=true;
				}
				else{
					echo "$result";
					exit;
				}
				
			}
			//by patient number
			if(!$exit_flag and $_POST['indv']=='patient_number'){	
					//get the bloody pid
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select pid from patient_details_a where patient_number=:patient_number";
					$placeholders2[':patient_number']=$_POST['indv_crit'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){$pid=$row2['pid'];}
						show_invoice($pdo,$pid);
					}
					else{
					
						$result_class="error_response";
						$var=html($_POST['indv_crit']);
						$result_message="Patient number $var does not exist or has no dispatched invoices";
						$exit_flag=true;
					}
			}			
		}
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
	<!--<div class='multiple_invoice'>-->
		<div class='grid-100 '>
				<div class='grid-15 '><label for="" class="label">Search for invoice by</label></div>
				<div class='grid-15'>
				<?php $token = form_token(); $_SESSION['token_is_1'] = "$token";  ?>
					<input type="hidden" name="token_is_1"  value="<?php echo $_SESSION['token_is_1']; ?>" />
					<select  name=indv class='edit_dispatch'><option></option>
						<option value='inv_num'>Invoice Number</option>
						<option value='patient_number'>Patient Number</option>
						<option value='first_name'>First Name</option>
						<option value='middle_name'>Middle Name</option>
						<option value='last_name'>Last Name</option>
					</select>
				</div>	
		<!--</div>-->
			<div class='no_padding serach_by_individual'>
				<div class='grid-10 '><input type=text name=indv_crit /></div>
				<div class='grid-10'>	<input type="submit"  value="Submit"/></div>
				<div class=clear></div>
				<br>
			</div>	<!-- end individual serach-->

	</form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>