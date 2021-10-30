<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,99)){exit;}
echo "<div class='grid_12 page_heading'>DELETE INVOICE</div>";
?>
<div class=grid-container>
<?php 

//this function will dispaly invoices to be deleted		
function get_invoice_for_deletion($pdo, $pid,$invoice_number, $encrypt){
	//get invoices to be dleeted
	$sql2=$error2=$s2='';$placeholders2=array();
	if($pid!=''){
		$sql2="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced) as date_invoiced,  
					 concat(users.first_name,' ',users.middle_name,' ',users.last_name) as doctor ,patient_details_a.patient_number,
					  concat(patient_details_a.first_name,' ',patient_details_a.middle_name,' ',patient_details_a.last_name) as patient_name ,
					sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested, 
					sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_approved 
					from tplan_procedure join  patient_details_a on tplan_procedure.pid =:pid and patient_details_a.pid=tplan_procedure.pid 
					join users on tplan_procedure.created_by=users.id
					left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
					where tplan_procedure.invoice_id > 0 
					group by tplan_procedure.invoice_id  order by tplan_procedure.invoice_id";
		$error2="Unable to get invoices for patient  ";
		$placeholders2[':pid']=$pid;
	}
	elseif($invoice_number!=''){
		$invoice_number=html("$invoice_number");
		$sql2="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced) as date_invoiced,  
					 concat(users.first_name,' ',users.middle_name,' ',users.last_name) as doctor ,patient_details_a.patient_number,
					  concat(patient_details_a.first_name,' ',patient_details_a.middle_name,' ',patient_details_a.last_name) as patient_name ,
					sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested, 
					sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_approved 
					from tplan_procedure join  patient_details_a on tplan_procedure.invoice_number =:invoice_number and patient_details_a.pid=tplan_procedure.pid 
					join users on tplan_procedure.created_by=users.id
					left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
					where tplan_procedure.invoice_id > 0 
					group by tplan_procedure.invoice_id  order by tplan_procedure.invoice_id";
		$error2="Unable to get invoices by invoice number ";
		$placeholders2[':invoice_number']=$invoice_number;
	}	
	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
	$i=$total=0;
	if($s2->rowCount() > 0){ ?>
		<form class='' action='' method="POST"  name="" id="">
			<?php $token = form_token(); $_SESSION['token_di2'] = "$token";  ?>
			<input type="hidden" name="token_di2"  value="<?php echo $_SESSION['token_di2']; ?>" />
		<?php
		foreach($s2 as $row2 ){
			$patient_name=html($row2['patient_name']);
			$patient_number=html($row2['patient_number']);
			$doctor=html($row2['doctor']);
			$date_invoiced=html($row2['date_invoiced']);
			$billed=html("$row2[amount_requested]");
			if($billed!=''){$billed=number_format($billed,2);}
			$authorised=html("$row2[amount_approved]");
			if($authorised!=''){$authorised=number_format($authorised,2);}
			$status=get_invoice_status($row2['invoice_id'],$pdo);
			$invoice_number2=html($row2['invoice_number']);
			//dispcth ceheck criteria
			$dispatched=false;
			if($status!=''){
				$data=explode('Dispatched',"$status");
				if(count($data)==2){$dispatched=true;}
			}
			if($status == 'Paid' or $status == 'Partially Paid' or $dispatched == true ){
				$checkbox="$status";
			}
			else{
				$val=$encrypt->encrypt("$row2[invoice_id]");
				$checkbox="<input type=checkbox name=del_invoice[] value=$val />";
			}
			
			if($i==0){
				if($pid!=''){$caption=strtoupper("Invoices for $patient_name - $patient_number");}
				elseif($invoice_number!=''){$caption=strtoupper("invoice number: $invoice_number");}
				echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=di_count></th>
				<th class=di_date>DATE INVOICED</th><th class=di_doc>INVOICED BY</th><th class=di_pname>PATIENT NAME</th><th class=di_pnum>PATIENT No.</th>
				<th class=di_invoice>INVOICE NUMBER</th><th class=di_billed>BILLED COST</th><th class=di_authorised>AUTHORISED COST</th><th class=di_del>DELETE</th></tr></thead><tbody>";
			}
			$i++;
			echo "<tr><td>$i</td><td>$date_invoiced</td><td>$doctor</td><td>$patient_name</td><td>$patient_number</td>
			<td><input type=button class='button_in_table_cell invoice_no button_style' value='$invoice_number2' /></td>
			<td>$billed</td><td>$authorised</td><td>$checkbox</td></tr>";
		}
		echo "</tbody></table><br>";
		echo "<div class='grid-100'><input class='put_right' type=submit value=Delete  /></div></form>";
	}
	else{ echo "<div class='error_response'>There are no invoices for the selected search criteria</div>";}
	exit;
}

//get records that need to be deleted
if(isset($_POST['token_di1']) and 	$_POST['token_di1']!='' and $_POST['token_di1']==$_SESSION['token_di1']){
		$_SESSION['token_di1']='';
		$exit_flag=false;

		if(!$exit_flag and ($_POST['indv']=='inv_num'  or $_POST['indv']=='patient_number' or 
		$_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'  )){
			
			//check if serach criteriais set
			if(!$exit_flag and !isset($_POST['indv_crit']) or $_POST['indv_crit']==''   ){	
					$result_class="error_response";
					$result_message="Incorrect search criteria";
					$exit_flag=true;
			}
			
			//by patient names
			if(!$exit_flag and $_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'){	
				$result=get_pt_internal_and_external($_POST['indv'],$_POST['indv_crit'],$pdo,$encrypt,'token_di1','indv','patient_number','indv_crit');
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
				
					//check if that pt has any dispacthed  invoices
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select pid from patient_details_a where patient_number=:patient_number ";
					$placeholders2[':patient_number']=$_POST['indv_crit'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							$pid=html($row2['pid']);
							get_invoice_for_deletion($pdo, $pid,'', $encrypt);
						}
					}
					else{ echo "<div class='error_response'>No such patient</div>";}
					
			}			
			
			//by invoice number
			if(!$exit_flag and $_POST['indv']=='inv_num'){	
				get_invoice_for_deletion($pdo, '',$_POST['indv_crit'], $encrypt);
			}			
			

		}
}
//perform actual deletion
if(isset($_SESSION['token_di2']) and isset($_POST['token_di2']) and $_POST['token_di2']==$_SESSION['token_di2']){
	$_SESSION['token_di2']='';
	$i=$n=0;
	if(isset($_POST['del_invoice'])){
		$invoice_id=$_POST['del_invoice'];
		$n=count($invoice_id);
		try{
				$pdo->beginTransaction();
					while($i < $n){
						$del_invoice_id=$encrypt->decrypt("$invoice_id[$i]");
						//copy record
						$sql=$error=$s='';$placeholders=array();
						$sql="select * from tplan_procedure where invoice_id=:invoice_id";
						$error="Unable to get invocies for deletion";
						$placeholders[':invoice_id']=$del_invoice_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							//insert into deletion table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into deleted_invoices
							set tplan_id=:tplan_id,
							procedure_id=:procedure_id,
							teeth=:teeth,
							details=:details,
							unauthorised_cost=:unauthorised_cost,
							treatment_procedure_id=:treatment_procedure_id,
							invoice_number=:invoice_number,
							pay_type=:pay_type,
							status=:status,
							authorised_cost=:authorised_cost,
							date_invoiced=:date_invoiced,
							date_procedure_added=:date_procedure_added,
							number_done=:number_done,
							created_by=:created_by,
							pid=:pid,
							invoice_id=:invoice_id,
							deleter=:deleter,
							when_deleted=now()";
							$error2="Unable to delete  invocies 1";
							$placeholders2[':tplan_id']=$row['tplan_id'];
							$placeholders2[':procedure_id']=$row['procedure_id'];
							$placeholders2[':teeth']=$row['teeth'];
							$placeholders2[':details']=$row['details'];
							$placeholders2[':unauthorised_cost']=$row['unauthorised_cost'];
							$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
							$placeholders2[':invoice_number']=$row['invoice_number'];
							$placeholders2[':pay_type']=$row['pay_type'];
							$placeholders2[':status']=$row['status'];
							$placeholders2[':authorised_cost']=$row['authorised_cost'];
							$placeholders2[':date_invoiced']=$row['date_invoiced'];
							$placeholders2[':date_procedure_added']=$row['date_procedure_added'];
							$placeholders2[':number_done']=$row['number_done'];
							$placeholders2[':created_by']=$row['created_by'];
							$placeholders2[':pid']=$row['pid'];
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$placeholders2[':deleter']=$_SESSION['id'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
							
							$pid=$row['pid'];
						}
						
						//copy co_payment for this invoice
						$sql=$error=$s='';$placeholders=array();
						$sql="select * from co_payment where invoice_number=:invoice_id";
						$error="Unable to get invocie co_payment for deletion";
						$placeholders[':invoice_id']=$del_invoice_id;
						$s = select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							//insert into deletion table
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="insert into deleted_co_payment
							set invoice_number=:invoice_number,
							amount=:amount,
							id=:id";
							$error2="Unable to delete  co_payment  1";
							$placeholders2[':invoice_number']=$row['invoice_number'];
							$placeholders2[':amount']=$row['amount'];
							$placeholders2[':id']=$row['id'];
							$s2 = insert_sql($sql2, $placeholders2, $error2, $pdo);	
						}
						
						//now dleete the invoice
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set invoice_number=null, authorised_cost=null, date_invoiced=null,
								invoice_id=0 where invoice_id=:invoice_id";
						$error="Unable to get delete invoices for deletion";
						$placeholders[':invoice_id']=$del_invoice_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);	

						//delete any co-payment
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from co_payment where invoice_number=:invoice_id";
						$error="Unable to  delete co_payment for the invoice";
						$placeholders[':invoice_id']=$del_invoice_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);	
						
						//delete unique_invoice_number_generator
						$sql=$error=$s='';$placeholders=array();
						$sql="delete from unique_invoice_number_generator where id=:invoice_id";
						$error="Unable to  delete unique_invoice_number_generator for the invoice";
						$placeholders[':invoice_id']=$del_invoice_id;
						$s = insert_sql($sql, $placeholders, $error, $pdo);	
						
						//update pt balances
						$pid_encrypt2=$encrypt->encrypt($pid);
						$result=show_pt_statement_brief($pdo,$pid_encrypt2,$encrypt);
						$i++;
					}
				
					$tx_result=$pdo->commit();
					if($tx_result ){echo "<div class='success_response'>Invoices deleted</div>";}
				
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		
		}
	}
	else{echo "<div class='error_response'>No Invoice was selected for deletion</div>";}
		
		
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_di1'] = "$token";  ?>
		<input type="hidden" name="token_di1"  value="<?php echo $_SESSION['token_di1']; ?>" />
				
	<!--<div class='multiple_invoice'>-->
		<div class='grid-100 '>
				<div class='grid-15 '><label for="" class="label">Search for invoice by</label></div>
				<div class='grid-15'>
					<select  name=indv class=''><option></option>
						<option value='inv_num'>Invoice Number</option>
						<option value='patient_number'>Patient Number</option>
						<option value='first_name'>First Name</option>
						<option value='middle_name'>Middle Name</option>
						<option value='last_name'>Last Name</option>
					</select>
				</div>	
		<!--</div>-->
			
				<div class='grid-15 '><input type=text name=indv_crit /></div>
				<div class='grid-10'>	<input type="submit"  value="Submit"/></div>
				<div class=clear></div>
				<br>
			

	</form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>