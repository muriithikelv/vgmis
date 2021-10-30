<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,49)){exit;}
echo "<div class='grid_12 page_heading'>INSURANCE PAYMENTS</div>";
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


	//get unpaid invoices
	if(isset($_POST['token_ins_pay1']) and 	$_POST['token_ins_pay1']!='' and $_POST['token_ins_pay1']==$_SESSION['token_ins_pay1']){ ?>
		
		
	<?php
		$_SESSION['token_ins_pay1']='';
		$ptype='';$insurer=' all ';
		$covered_company='';$comp_covered=$pnum=$date_criteria=$inv_num_criteria='';
		$pnum_search=$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();
		
		//check if serach criteriais set fro multiple
		if(!$exit_flag and isset($_POST['pay_mode']) and $_POST['pay_mode']=='multiple'){	
			//check if ptype is set
			if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
					$result_class="error_response";
					$result_message="Unable to search for invoices as insurer is not set";
					$exit_flag=true;
			}
			
			//check if covered_company is set
			if(!$exit_flag and !isset($_POST['covered_company']) or $_POST['covered_company']==''   ){	
					$result_class="error_response";
					$result_message="Unable to search for invoices as company covered is not set";
					$exit_flag=true;
			}

			//check if from date is set
			if(!$exit_flag and !isset($_POST['from_date']) or $_POST['from_date']==''   ){	
					$result_class="error_response";
					$result_message="Unable to search for invoices as date range is not set";
					$exit_flag=true;
			}			
		}
		//check if serach criteriais set for single patient serach
		//check if invoice umber is set
		elseif(!$exit_flag and $_POST['search_single']=='inv_num' and $_POST['search_single_input']==''   ){	
				$result_class="error_response";
				$result_message="Unable to search for invoices as no search invoice number is specified";
				$exit_flag=true;
		}
		
		//check if pnum is set
		elseif(!$exit_flag and $_POST['search_single']=='patient_id' and $_POST['search_single_input']==''   ){	
				$result_class="error_response";
				$result_message="Unable to search for invoices as no patient is specified";
				$exit_flag=true;
		}

		//check if name is set
		elseif(!$exit_flag and isset($_POST['search_single']) and ($_POST['search_single']=='first_name' 
			or $_POST['search_single']=='middle_name' or $_POST['search_single']=='last_name') and  $_POST['search_single_input']==''){	
				$result_class="error_response";
				$result_message="Unable to search for invoices as patient name is not specified";
				$exit_flag=true;
		}	

		//check if both search critetia and asearch value are specified
		elseif(!$exit_flag and $_POST['search_single']=='' and $_POST['search_single_input']==''   ){	
				$result_class="error_response";
				$result_message="Unable to search for invoices as no search criteria is specified";
				$exit_flag=true;
		}		
		
		
		
		if(!$exit_flag){
			//single invoice search
			$invoices_array=$_SESSION['balance_invoice']=array();
			if($_POST['search_single']=='inv_num' and $_POST['search_single_input']!=''){
				//$inv_num_criteria=" and tplan_procedure.invoice_number=:invoice_number ";
				//$placeholders['invoice_number']=$_POST['search_single_input'];
				//$var=html($_POST['search_single_input']);
				
				
				//get details from unique_inv_table first
				
				$sql1=$error1=$s1='';$placeholders1=array();	
				$sql1="SELECT * FROM unique_invoice_number_generator WHERE invoice_number=:invoice_number";
				$placeholders1[':invoice_number']=$_POST['search_single_input'];
				$error1="Error: Unable to date range uniq ";
				$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
				if($s1->rowCount() == 0){
					echo "<label  class=label>There is no such invoice</label>";
					exit;
				}
				foreach($s1 as $row1 ){
					//now get the pt 
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer 
						from patient_details_a a left join covered_company b 
						on a.company_covered=b.id 
						left join insurance_company c on a.type=c.id
						where pid=:pid";
					$placeholders2[':pid']=$row1['pid'];
					$error2="Error: Unable to pt details from uniq ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							//now get invoice cost
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost
									FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
									WHERE tplan_procedure.invoice_id =:invoice_id";
							$placeholders3[':invoice_id']=$row1['id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$invoice_cost=html($row3['cost']);}
							
							//now amount paid
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
							$placeholders3[':invoice_id']=$row1['id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$amount_paid=html($row3['amount']);}
							
							//check if fully paid
							if($amount_paid < $invoice_cost){
								//get doctor who raised invoice
								$doctor='';
								$sql4=$error3=$s3='';$placeholders3=array();	
								$sql4="SELECT first_name, middle_name, last_name FROM users where id=:user_id";
								$placeholders4[':user_id']=$row1['added_by'];
								$error4="Error: Unable to pt details from uniq ";
								$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
								foreach($s4 as $row4){$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));}
								
								$when_added=html("$row1[when_raised]");
								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$company=html("$row2[company_covered]");
								$insurer=html("$row2[insurer]");
								if($company!=''){$company="$insurer - $company";}
								else{$company="$insurer";}
								$cost=$invoice_cost;
								$balance=html($invoice_cost - $amount_paid);
								$invoice_number=html("$row1[invoice_number]");
								$caption="PAYMENT FOR INVOICE: $invoice_number";
								$invoice_id=html("$row1[id]");
								$val=$encrypt->encrypt("$invoice_id#$balance#$row1[pid]");
								//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
								$_SESSION['balance_invoice'][$invoice_id]=$balance;
								$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 
														'company'=>"$company", 'cost'=>"$cost",
										 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number",'val'=>"$val", 'balance'=>"$balance");
					
							}
							elseif($amount_paid == $invoice_cost){
								echo "<label  class=label>That invoice is already paid</label>";
								exit;
							}
						}//end s2

						
					}//end if
					
					
				}//end s1
				
			}
			
			//single patient search
			elseif($_POST['search_single']=='patient_id' and $_POST['search_single_input']!=''){
				$pnum=" and patient_details_a.patient_number=:patient_number ";
				$placeholders['patient_number']=$_POST['search_single_input'];
				$pnum_search=true;
				
				//get the pid first
				$sql1=$error1=$s1='';$placeholders1=array();	
				$sql1="SELECT pid  FROM patient_details_a WHERE patient_number=:patient_number";
				$placeholders1[':patient_number']=$_POST['search_single_input'];
				$error1="Error: Unable to date range uniq ";
				$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
				if($s1->rowCount() == 0){
					echo "<label  class=label>There is no patient</label>";
					exit;
				}
				foreach($s1 as $row1){$pid=$row1['pid'];}
				
				//get details from unique_inv_table first
				$sql1=$error1=$s1='';$placeholders1=array();	
				$sql1="SELECT * FROM unique_invoice_number_generator WHERE pid=:pid";
				$placeholders1[':pid']=$pid;
				$error1="Error: Unable to date range uniq ";
				$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
				if($s1->rowCount() == 0){
					echo "<label  class=label>There is no such invoice</label>";
					exit;
				}
				$all_paid = true;
				foreach($s1 as $row1 ){
					//now get the pt 
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer 
						from patient_details_a a left join covered_company b 
						on a.company_covered=b.id 
						left join insurance_company c on a.type=c.id
						where pid=:pid";
					$placeholders2[':pid']=$row1['pid'];
					$error2="Error: Unable to pt details from uniq ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							//now get invoice cost
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost
									FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
									WHERE tplan_procedure.invoice_id =:invoice_id";
							$placeholders3[':invoice_id']=$row1['id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$invoice_cost=html($row3['cost']);}
							
							//now amount paid
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
							$placeholders3[':invoice_id']=$row1['id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$amount_paid=html($row3['amount']);}
							
							//check if fully paid
							if($amount_paid < $invoice_cost){
								$all_paid = false;
								//get doctor who raised invoice
								$doctor='';
								$sql4=$error3=$s3='';$placeholders3=array();	
								$sql4="SELECT first_name, middle_name, last_name FROM users where id=:user_id";
								$placeholders4[':user_id']=$row1['added_by'];
								$error4="Error: Unable to pt details from uniq ";
								$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
								foreach($s4 as $row4){$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));}
								
								$when_added=html("$row1[when_raised]");
								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$company=html("$row2[company_covered]");
								$insurer=html("$row2[insurer]");
								if($company!=''){$company="$insurer - $company";}
								else{$company="$insurer";}
								$cost=$invoice_cost;
								$balance=html($invoice_cost - $amount_paid);
								$invoice_number=html("$row1[invoice_number]");
								$caption="PAYMENT FOR INVOICE:  $patient";
								$invoice_id=html("$row1[id]");
								$val=$encrypt->encrypt("$invoice_id#$balance#$row1[pid]");
								//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
								$_SESSION['balance_invoice'][$invoice_id]=$balance;
								$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 
														'company'=>"$company", 'cost'=>"$cost",
										 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number",'val'=>"$val", 'balance'=>"$balance");
					
							}
							elseif($amount_paid == $invoice_cost){
								
							}
						}//end s2

						
					}//end if
					
					
				}//end s1
				if($all_paid){
					echo "<label  class=label>All invoices for the selected patient are already paid</label>";
					exit;
				}
			}
			
			//by patient names
			elseif(isset($_POST['search_single']) and ($_POST['search_single']=='first_name' or $_POST['search_single']=='middle_name' 
				or $_POST['search_single']=='last_name')){
				$result=get_pt_name2($_POST['search_single'],$_POST['search_single_input'],$pdo,$encrypt,'token_ins_pay1','search_single','patient_id','search_single_input');
				if($result=="2"){
					echo "<label  class=label>There is no such patient</label>";
					exit;
				}
				else{
					echo "$result";
					exit;
				}
				
			}
			elseif(isset($_POST['pay_mode']) and $_POST['pay_mode']=='multiple'){
				$date_criteria=" and tplan_procedure.date_invoiced >=:from_date and   tplan_procedure.date_invoiced <=:to_date  ";
				$placeholders['from_date']=$_POST['from_date'];
				$placeholders['to_date']=$_POST['to_date'];
				$error="Unable to get unpaid invoices";
				$from=html($_POST['from_date']);
				$to=html($_POST['to_date']);
				
				
				//get insurer
				if($_POST['ptype']=='all'){$ptype='';$insurer=' all ';}
				elseif($_POST['ptype']!=''){
					$var=$encrypt->decrypt($_POST['ptype']);
					$ptype=" and patient_details_a.type=:insurer ";
					$placeholders['insurer']=$var;
					//get insurance name
					$insurer_id=$var;
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select name from insurance_company where id=:id";
					$error2="Unable to get insurance company";
					$placeholders2[':id']=$var;
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						$insurer=html($row2['name']);
					}		
				}
				
				//get covered compnay
				if($_POST['covered_company']=='all'){$covered_company='';$comp_covered='';}
				elseif($_POST['covered_company']!=''){
					$var2=$encrypt->decrypt($_POST['covered_company']);
					$covered_company=" and a.company_covered=:company ";
					$covered_company_id=$encrypt->decrypt($_POST['covered_company']);
					$placeholders['company']=$var2;
					//get covered compnay name
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select name from covered_company where id=:id";
					$error2="Unable to get covered company";
					$placeholders2[':id']=$var2;
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						$comp_covered=" - ".html($row2['name']);
					}
				}
				$caption="Unpaid Invoices for $insurer $comp_covered patients between $from and $to ";
					//get details from unique_inv_table first
					$sql1=$error1=$s1='';$placeholders1=array();	
					$sql1="SELECT * FROM unique_invoice_number_generator WHERE when_raised >=:from_date AND when_raised <=:to_date";
					$placeholders1[':from_date']=$_POST['from_date'];
					$placeholders1[':to_date']=$_POST['to_date'];
					$error1="Error: Unable to date range uniq ";
					$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
					foreach($s1 as $row1 ){
						//now check if the pt is from the mentioned insuer
						$sql2=$error2=$s2='';$placeholders2=array();	
						$sql2="select first_name,middle_name,last_name,name from patient_details_a a left join covered_company b 
							on a.company_covered=b.id where pid=:pid and type=:insurer $covered_company";
						if(isset($covered_company_id) and $covered_company_id > 0){
							$placeholders2[':company']=$covered_company_id;
						}
						$placeholders2[':pid']=$row1['pid'];
						$placeholders2[':insurer']=$insurer_id;
						$error2="Error: Unable to pt details from uniq ";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						if($s2->rowCount() > 0){
							foreach($s2 as $row2){
								//now get invoice cost
								$sql3=$error3=$s3='';$placeholders3=array();	
								$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost
										FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
										WHERE tplan_procedure.invoice_id =:invoice_id";
								$placeholders3[':invoice_id']=$row1['id'];
								$error3="Error: Unable to pt details from uniq ";
								$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
								foreach($s3 as $row3){$invoice_cost=html($row3['cost']);}
								
								if($invoice_cost <= 0){continue;}
								//now amount paid
								$sql3=$error3=$s3='';$placeholders3=array();	
								$sql3="SELECT sum( amount ) as amount FROM payments where invoice_id =:invoice_id";
								$placeholders3[':invoice_id']=$row1['id'];
								$error3="Error: Unable to pt details from uniq ";
								$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
								foreach($s3 as $row3){$amount_paid=html($row3['amount']);}
								
								//check if fully paid
								if($amount_paid < $invoice_cost){
									//get doctor who raised invoice
									$doctor='';
									$sql4=$error3=$s3='';$placeholders3=array();	
									$sql4="SELECT first_name, middle_name, last_name FROM users where id=:user_id";
									$placeholders4[':user_id']=$row1['added_by'];
									$error4="Error: Unable to pt details from uniq ";
									$s4 = 	select_sql($sql4, $placeholders4, $error4, $pdo);
									foreach($s4 as $row4){$doctor=ucfirst(html("$row4[first_name] $row4[middle_name] $row4[last_name]"));}
									
									$when_added=html("$row1[when_raised]");
									$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
									$company=html("$row2[name]");
									if($company!=''){$company="$insurer - $company";}
									else{$company="$insurer";}
									$cost=$invoice_cost;
									$balance=html($invoice_cost - $amount_paid);
									$invoice_number=html("$row1[invoice_number]");
									$invoice_id=html("$row1[id]");
									$val=$encrypt->encrypt("$invoice_id#$balance#$row1[pid]");
									//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
									$_SESSION['balance_invoice'][$invoice_id]=$balance;
									$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 
															'company'=>"$company", 'cost'=>"$cost",
											 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number",'val'=>"$val", 'balance'=>"$balance");
						
								}
							}//end s2

							
						}//end if
						
						
					}//end s1
				
			}
				/*$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, 
						sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost, 
						sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) - c.sum_paid  as balance,
						min(tplan_procedure.date_invoiced) as date_invoiced, users.first_name,users.last_name,users.middle_name,
						patient_details_a.first_name,patient_details_a.last_name,patient_details_a.middle_name,	insurance_company.name,
						covered_company.name ,tplan_procedure.pid 
						from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid and tplan_procedure.invoice_id > 0
							$pnum $inv_num_criteria
						join users on users.id=tplan_procedure.created_by
						left join covered_company on patient_details_a.company_covered=covered_company.id $covered_company
						left	join insurance_company on patient_details_a.type=insurance_company.id  $ptype
						left join (select invoice_id,sum(amount) as sum_paid from payments group by invoice_id) as c 
							on c.invoice_id=tplan_procedure.invoice_id
						left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
						where tplan_procedure.authorised_cost is not null $date_criteria
						group by tplan_procedure.invoice_id  
						having balance > 0
						order by tplan_procedure.invoice_id";
			$s = select_sql($sql, $placeholders, $error, $pdo);	*/
			

			
			//echo "count is ".$s->rowCount();exit;
			//if($s->rowCount() > 0){
			/*	$invoices_array=$_SESSION['balance_invoice']=array();
				$i=0;
				foreach($s as $row){
					$when_added=html("$row[date_invoiced]");
					$patient=ucfirst(html("$row[8] $row[10] $row[9]"));
					$doctor=ucfirst(html("$row[5] $row[7] $row[6]"));
					$insurer=html("$row[11]");
					$company=html("$row[12]");
					if($company!=''){$company="$insurer - $company";}
					else{$company="$insurer";}
					$cost=html($row['cost']);
					$balance=html($row['balance']);
					$invoice_number=html("$row[invoice_number]");
					$invoice_id=html("$row[invoice_id]");
					$val=$encrypt->encrypt("$invoice_id#$balance#$row[pid]");
					//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
					$_SESSION['balance_invoice'][$invoice_id]=$balance;
					if($i==0 and $pnum_search){$caption=strtoupper("Unpaid invoices for $patient");}
					
					$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 'company'=>"$company", 'cost'=>"$cost",
									 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number",'val'=>"$val", 'balance'=>"$balance");
				}*/
				//now output the labs to be paid
				if(count($invoices_array) > 0){ ?>
				<form action='' method='post' name='' id='' class='patient_form'>
			<div class=grid-10><label for="" class="label">Amount Paid</label></div>
			<div class='grid-10'><input type=text  id=total_payment name=amount /></div>	
			
			<div class='grid-10 prefix-5'><label for="" class="label">Payment Type</label></div>
			<div class='grid-10'><?php  
				$sql=$error=$s='';$placeholders=array();
				//$sql="select id,name from payment_types where id!=7 and id!=8 and id!=6 and id!=10 order by name";					
				$sql="select id,name from payment_types where id=9 or id=3 order by name";					
				$error="Unable to select payment types";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<select class='input_in_table_cell payment_type' name=non_ins_payment_type ><option></option>";
				foreach($s as $row){
					$name=html($row['name']);
					$id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$id'>$name</option>";
				}
				echo "</select>"; ?>
			</div>
			
			
			<div class='remove-inside-padding  '>
				<!-- cheque number-->
				<div class='cheque_number'>
					<div class='grid-15 prefix-5'><label for="" class="label">Cheque Number</label></div>
					<div class='grid-25'><input type=text name=cheque_number /></div>	
				</div>
				
				<!-- mpesa number-->
				<div class='mpesa_number'>
					<div class='grid-15 prefix-5'><label for="" class="label">Mpesa Tx. Number</label></div>
					<div class='grid-25'><input type=text name=mpesa_number /></div>	
				</div>

				<!-- visa number-->
				<div class='visa_number'>
					<div class='grid-15 prefix-5'><label for="" class="label">VISA Tx. Number</label></div>
					<div class='grid-25'><input type=text name=visa_number /></div>	
				</div>	

				<!-- eft  number-->
				<div class='eft_number'>
					<div class='grid-15 prefix-5'><label for="" class="label">EFT Tx. Number</label></div>
					<div class='grid-25'><input type=text name=eft_number /></div>	
				</div>	
				
				
			</div>
			
			<div class=clear></div><br>
			<?php
					$count=0;
					echo "<br><br>
					<table class='normal_table'><caption>$caption</caption><thead>
					<tr><th class=invoice_in_count></th>
					<th class=invoice_in_date>DATE</th>
					<th class=invoice_in_doctor>DOCTOR</th>
					<th class=invoice_in_patient>PATIENT NAME</th>
					<th class=invoice_in_company>CORPORATE</th>
					<th class=invoice_in_id>INVOICE No.</th>
					<th class=invoice_in_cost>COST</th>
					<th class=invoice_in_tray>BALANCE</th>
					<th class=invoice_in_finished>AMOUNT PAID</th>
					</tr></thead><tbody>";	
					$i=0;
					$n=count($invoices_array);
					$total_cost=$total_balance=0;
					foreach($invoices_array as $unpaid_invoice_array){
						$count++;
						//check if the entry is for an aliased invoice
						$is_invoice_aliased=0;
						$aliased='';
						if($unpaid_invoice_array['invoice_id'] > 0){
								$is_invoice_aliased = is_invoice_id__alias($pdo,$unpaid_invoice_array['invoice_id']);
								if($is_invoice_aliased == 1){$aliased="<br>Alias";}
						}

						$invoices_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 'company'=>"$company", 'cost'=>"$cost",
									 'invoice_id'=>"$invoice_id", 'invoice_number'=>"$invoice_number",'val'=>"$val", 'balance'=>"$balance");
						//<input type=button class='button_in_table_cell button_style invoice_no' value=$unpaid_invoice_array[invoice_number]  />
						echo "<tr><td class=count>$count</td>
								<td>$unpaid_invoice_array[when_added]</td>
								<td>$unpaid_invoice_array[doctor]</td>
								<td>$unpaid_invoice_array[patient]</td>
								<td>$unpaid_invoice_array[company]</td>
						<td>
						<a href='' class='invoice_no_link link_color'>$unpaid_invoice_array[invoice_number]</a>$aliased
						</td>
						<td>".number_format($unpaid_invoice_array['cost'],2)."</td><td>";
						//check to see if balance is full or partiall and show a link
						if($unpaid_invoice_array['cost'] > $unpaid_invoice_array['balance'] ){
							$val=$encrypt->encrypt("$unpaid_invoice_array[invoice_id]#$unpaid_invoice_array[invoice_number]");
							echo "<a href='?$val' class='balance_payment link_color'>".number_format($unpaid_invoice_array['balance'],2)."</a>";
							//echo "<a href='?$unpaid_invoice_array[val]' class='balance_payment link_style'>".number_format($unpaid_invoice_array['balance'],2)."</a>";
						}
						else{echo number_format($unpaid_invoice_array['balance'],2);}
						echo "</td><td><input class=invoice_payment_amount type=text name=invoice_payment[] /><input type=hidden name=ninye[] value=$unpaid_invoice_array[val] /> </td></tr>";
						$total_cost = $total_cost + $unpaid_invoice_array['cost'];
						$total_balance = $total_balance + $unpaid_invoice_array['balance'];
						$i++;
					}
					echo "<tr class=total_background><td colspan=6>TOTAL</td>
						<td>".number_format($total_cost,2)."</td><td>".number_format($total_balance,2)."</td><td>&nbsp</td></tr>";
					echo "</tbody></table>";
					echo "<br>";
					$token = form_token();
					//$token= "ba486d1a23c6090e262ce0a88df938cc1e5b753f";
					//$d1= date('Y-m-d-H-i)
					$_SESSION['token_inv_pay2'] = "$token";  
					
					echo "<input type=hidden name=token_inv_pay2  value='$_SESSION[token_inv_pay2]' /><input type=submit class='put_right' value='Submit' /></form>";
				
				}
				else{echo "<label  class=label>There is no unpaid invoices for the selected criteria</label>";}
				//}
			//else{echo "<label  class=label>There is no unpaid invoices for the selected criteria 2</label>";}
			echo "<div id=view_lab></div>";
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
	}	
	?>
			

			
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_ins_pay1'] = "$token";  ?>
	<input type="hidden" name="token_ins_pay1"  value="<?php echo $_SESSION['token_ins_pay1']; ?>" />
		
	<label for="" class="label">Select pay type</label></div>
	<div class='grid-25'><select class='input_in_table_cell pay_mode' name=pay_mode><option></option>
			<option value='single'>Single Invoice/Patient</option>
			<option value='multiple'>Multiple Invoices</option>
			</select></div>
						<div class=clear></div><br>
	<div class='single_invoice grid-100 grid-parent'>
		<div class=grid-15><label for="" class="label">Select search option</label></div>
		<div class='grid-25'>
			<select class='input_in_table_cell ' name=search_single><option></option>
				<option value='inv_num'>Invoice Number</option>
				<option value='patient_id'>Patient ID</option>
				<option value='first_name'>First Name</option>
				<option value='middle_name'>Middle Name</option>
				<option value='last_name'>Last Name</option>
			</select>
		</div>
		<div class=grid-15><input type=text name=search_single_input /></div>
	</div>
	<div class='multiple_invoice'>
				<div class='grid-15'><label for="" class="label">Select Insurer</label></div>
				<div class='grid-25'><select class=ptype2 name=ptype><option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from insurance_company where upper(name)!= 'CASH' order by name";
						$error = "Unable to insurance companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
						//echo "<option value='all'>ALL</option>";
					
					?>
					</option></select>
				</div>	
				<!--compnay covered-->
				<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
				<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company><option></option>
				<?php 
				/*	if(isset($_SESSION['id']) and $_SESSION['id']!=''){
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}					
							//$val=$encrypt->encrypt("all");
							echo "<option value='all'>ALL</option>";
					}*/
				?>
				</select></div>	
				<div class=clear></div><br>
				<div class=grid-15><label for="" class="label">Invoices raised between</label></div>
				<div class=grid-25><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-25><input type=text name=to_date class=date_picker /></div>
	</div>
	<div class=clear></div>
	<br>
	<div class='prefix-15 grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>