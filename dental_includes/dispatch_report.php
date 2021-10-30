<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,65)){exit;}
echo "<div class='grid_12 page_heading'>INVOICE DISPATCH REPORT</div>";
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

//this function will dispaly a dispatch nite for editing		
function show_dispacth($pdo,$encrypt,$disp_num,$dipatcher_name,$title,$when_added,$insurer_id,$insurer_name){
//now show what to print
						$sql=$error=$s='';$placeholders=array();
						/*$sql="select patient_details_a.first_name, patient_details_a.middle_name, patient_details_a.last_name, 
							patient_details_a.patient_number , tplan_procedure.invoice_number, 	min(tplan_procedure.date_invoiced) as date_invoiced,
							sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised 
							from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
							left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
							group by invoice_id, dispatch_number
							having dispatch_number =:dispatch_number";
						$error="Unable to get dispatched invoices";
						$placeholders['dispatch_number']="$disp_num";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						*/
						$sql=" SELECT DISTINCT invoice_id FROM tplan_procedure WHERE dispatch_number =:dispatch_number";		
						$error="Unable to get dispatched invoices";
						$placeholders[':dispatch_number']="$disp_num";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						$invoices_array=array();
						$total=0;
						foreach($s as $row){
							//get invoice details such as pt name
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT b.first_name as first_name, b.middle_name as middle_name, b.last_name as last_name,
									b.patient_number as patient_number, invoice_number, when_raised as date_invoiced from
									unique_invoice_number_generator a join patient_details_a b on a.pid=b.pid
									where a.id=:invoice_id";
							$error2="Unable to get invoice details";
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){
								$date_invoiced=html($row2['date_invoiced']);
								$file_no=html($row2['patient_number']);
								$names=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
								$invoice_number=html($row2['invoice_number']);
							}
							
							//get invoice cost
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT sum(authorised_cost) from tplan_procedure where invoice_id=:invoice_id";
							$error2="Unable to get invoice cost";
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){$invoice_cost=html($row2[0]);}
							
							//get co_payment
							$invoice_copayment=0;
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT amount from co_payment where invoice_number=:invoice_id";
							$error2="Unable to get invoice co_payment";
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){$invoice_copayment=html($row2['amount']);}		
							
							$invoice_cost= $invoice_cost - $invoice_copayment;
							
							$cost=number_format($invoice_cost,2);
							//now put values in array
							$invoices_array[]=array('when_added'=>"$date_invoiced",  'names'=>"$names", 'file_no'=>"$file_no", 'invoice_number'=>"$invoice_number",
													'cost'=>"$cost");
							$total = $total + html($invoice_cost);
						}
						
						if(count($invoices_array) > 0){
							$dispatch_number=html("$disp_num");
							echo "<div class=clear></div>";
							echo "<div class='grid-100 '><input type=button class='button_style printment' value=Print /></div>";
							echo "<div class='no_padding grid-100'>	";
								echo "<div class='grid-100 label make_bold'>MOLARS DENTAL CLINIC</div><br>";
								echo "<div class='grid-100 label'>DISPATCH NUMBER: $disp_num <br> $when_added </div><br>";
								echo "<table class='normal_table bordered_table'><caption>$title</caption><thead>
									<tr>
									<th class=invoice_in_date3>TREATMENT DATE</th>
									<th class=invoice_in_patient3>PATIENT NAME</th>
									<th class=invoice_in_company3>FILE No.</th>
									<th class=invoice_in_id3>INVOICE No.</th>
									<th class=invoice_in_cost3>COST</th>
									</tr></thead><tbody>";	
									foreach($invoices_array as $row){
										$date=html($row['when_added']);
										$name=ucfirst(html("$row[names]"));
										$file_no=html($row['file_no']);
										$invoice_no=html($row['invoice_number']);
										$cost=html($row['cost']);
										echo "<tr><td >$date</td><td >$name</td><td >$file_no</td>
										<td >$invoice_no</td><td >$cost</td>
										</tr>";
									}
									echo "<tr><td colspan=4>TOTAL</td><td >".number_format($total,2)."</td>
										</tr>";
									echo "</tbody></table>";
									echo "<br>";
								echo "<div class='grid-100 label'>Prepared by: $_SESSION[logged_in_user_names]</div><br>";	
								echo "<div class='grid-100 label'>Received by: ........................</div><br>";	
							echo "</div>";
						}
						/*if($s->rowCount() > 0){
							$dispatch_number=html("$disp_num");
							echo "<div class=clear></div>";
							echo "<div class='grid-100 '><input type=button class='button_style printment' value=Print /></div>";
							echo "<div class='no_padding grid-100'>	";
								echo "<div class='grid-100 label make_bold'>MOLARS DENTAL CLINIC</div><br>";
								echo "<div class='grid-100 label'>DISPATCH NUMBER: $disp_num <br> ".date('Y-m-d')."</div><br>";
								echo "<table class='normal_table'><caption>$title</caption><thead>
									<tr>
									<th class=invoice_in_date3>TREATMENT DATE</th>
									<th class=invoice_in_patient3>PATIENT NAME</th>
									<th class=invoice_in_company3>FILE No.</th>
									<th class=invoice_in_id3>INVOICE No.</th>
									<th class=invoice_in_cost3>COST</th>
									</tr></thead><tbody>";	
									$total=0;
									foreach($s as $row){
										$date=html($row['date_invoiced']);
										$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
										$file_no=html($row['patient_number']);
										$invoice_no=html($row['invoice_number']);
										$cost=number_format(html($row['amount_authorised']),2);
										echo "<tr><td >$date</td><td >$name</td><td >$file_no</td>
										<td >$invoice_no</td><td >$cost</td>
										</tr>";
										$total = $total + $row['amount_authorised'];
									}
									echo "<tr><td colspan=4>TOTAL</td><td >".number_format($total,2)."</td>
										</tr>";
									echo "</tbody></table>";
									echo "<br>";
								echo "<div class='grid-100 label'>Prepared by: $_SESSION[logged_in_user_names]</div><br>";	
								echo "<div class='grid-100 label'>Received by: ........................</div><br>";	
							echo "</div>";
						}*/
}


//get undispatched invoices to add to dispatch note
if(isset($_POST['token_edis_3']) and 	$_POST['token_edis_3']!='' and $_POST['token_edis_3']==$_SESSION['token_edis_3']){
		$exit_flag=false;
		//check if insurer is selcted
		if(!$exit_flag and !isset($_POST['ninye']) or $_POST['ninye']==''   ){	
				$result_class="error_response";
				$result_message="Unable to complete search";
				$exit_flag=true;
		}	
		
		//check if corprate is selcted
		if(!$exit_flag and !isset($_POST['covered_company']) or $_POST['covered_company']==''   ){	
				$result_class="error_response";
				$result_message="Please select the company covered";
				$exit_flag=true;
		}	
		if(!$exit_flag){		
				//get insurance name
				$sql=$error=$s='';$placeholders=array();
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select name from insurance_company where id=:id";
				$error2="Unable to get insurance company";
				$var=$encrypt->decrypt($_POST['ninye']);
				$data=explode('#',"$var");
				$insurer_id=$data[0];
				$disp_num="$data[1]";
				$placeholders2[':id']=$insurer_id;
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){
					$insurer=html($row2['name']);
				}		
		
				//get covered compnay name
				$corprate=$comp_covered='';
				if($_POST['covered_company']!='all'){
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select name from covered_company where id=:id";
					$error2="Unable to get covered company";
					$var2=$encrypt->decrypt($_POST['covered_company']);
					$placeholders2[':id']=$var2;
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2){
						$comp_covered=html($row2['name']);
					}
					$corprate=' and patient_details_a.company_covered=:company_covered ';
					$placeholders['company_covered']=$var2;
					
				}			
			
			
			$sql="select tplan_procedure.invoice_id, min(tplan_procedure.date_invoiced) as date_invoiced, patient_details_a.type,
					tplan_procedure.invoice_number, tplan_procedure.dispatch_number as dis_num,
					sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised,
					patient_details_a.first_name, patient_details_a.middle_name,
					patient_details_a.last_name, patient_details_a.patient_number
					from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
					left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
					where patient_details_a.type=:insurer_id $corprate and tplan_procedure.pay_type=1 
					group by invoice_id
					having amount_authorised > 0
					and tplan_procedure.dispatch_number ='0' and
					min(tplan_procedure.date_invoiced) >=:from_date and
					min(tplan_procedure.date_invoiced) <=:to_date";
			$placeholders['from_date']=$_POST['from_date'];
			$placeholders['to_date']=$_POST['to_date'];
			$placeholders['insurer_id']=$insurer_id;
			$error="Unable to get undispatched invoices";
			$s = select_sql($sql, $placeholders, $error, $pdo);	
			$from=html($_POST['from_date']);
			$to=html($_POST['to_date']);
			$caption="Undispatched Invoices for $insurer $comp_covered patients between $from and $to ";
		//echo "count is ".$s->rowCount();exit;
		
		//echo "count is ".$s->rowCount();exit;
		if($s->rowCount() > 0){ ?>
				<form action="" method="POST" enctype="" name="" id=""><?php
					$var22=$encrypt->encrypt("$disp_num");
					echo "<input type=hidden name=ninye value=$var22 />";
				echo "<table class='normal_table'><caption>$caption</caption><thead>
				<tr>
				<th class=invoice_in_date2>TREATMENT DATE</th>
				<th class=invoice_in_patient2>PATIENT NAME</th>
				<th class=invoice_in_company2>FILE No.</th>
				<th class=invoice_in_id2>INVOICE No.</th>
				<th class=invoice_in_cost2>COST</th>
				<th class=invoice_in_tray2>DISPATCH</th>
				</tr></thead><tbody>";	
				$total=0;
				foreach($s as $row){
					$date=html($row['date_invoiced']);
					$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$file_no=html($row['patient_number']);
					$invoice_no=html($row['invoice_number']);
					$cost=number_format(html($row['amount_authorised']),2);
					$var=$encrypt->encrypt($row['invoice_id']);
					echo "<tr><td class=count>$date</td><td class=count>$name</td><td class=count>$file_no</td>
					<td class=count><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_no /></td><td class=count>$cost</td>
					<td class=count><input type=checkbox name=dispatch[] value=$var</td></tr>";
					$total = $total + html($row['amount_authorised']);
				}
				echo "<tr><td class=make_bold colspan=4>TOTAL</td><td>".number_format($total,2)."</td><td>";
						$token = form_token(); $_SESSION['token_edis_4'] = "$token";  
				echo "<input type=hidden name=token_edis_4  value='$_SESSION[token_edis_4]' />
						<input type=submit class='button_style button_in_table_cell' value='Add to $disp_num' /></form></td></tr>";
				echo "</tbody></table>";
				echo "<br>";
				
			
				
			}
			
		else{echo "<label  class=label>There is no undispatched invoices for the selected criteria</label>";}
		echo "<div id=view_lab></div>";
		exit;
	}
	else{
		echo "<div class='$result_class'>$result_message</div>";
	}
		
}



//dispatched invoices to distpacth note
if(isset($_POST['token_edis_4']) and 	$_POST['token_edis_4']!='' and $_POST['token_edis_4']==$_SESSION['token_edis_4']){
	$i=0;
	$invoice_id=$_POST['dispatch'];
	$n=count($invoice_id);
	if($n > 0){
		try{
				$pdo->beginTransaction();
					$dispatch_number=$encrypt->decrypt($_POST['ninye']);
					
					//get a dispatch number
					$sql=$error=$s='';$placeholders=array();
					$sql="update dispatched_invoices set
							when_added=now(),
							dispatched_by=:dispatched_by";
					$error="Unable to update dispatched invoices";
							$placeholders['dispatched_by']=$_SESSION['id'];
									
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					
					//now get dispatch title
					$sql=$error=$s='';$placeholders=array();
					$sql="select title from dispatched_invoices where dispatch_number=:dispatch_number";
					$error="Unable to get title";
					$placeholders['dispatch_number']="$dispatch_number";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);	
					foreach($s as $row){$caption=strtoupper(html("$row[title]"));}
					
					//now update invoices
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set dispatch_number=:dispatch_number where invoice_id=:invoice_id";
						$error="Unable to update dispatch number";
								$placeholders['invoice_id']=$encrypt->decrypt("$invoice_id[$i]");
								$placeholders['dispatch_number']="$dispatch_number";
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						$i++;
					}
					$tx_result = $pdo->commit();
					if($tx_result){
						$result_class="success_response";
						$result_message="Dispacth note edited";
						echo "<div class='$result_class'>$result_message</div>";
						//now show what to print
						$sql=$error=$s='';$placeholders=array();
						$sql="select patient_details_a.first_name, patient_details_a.middle_name, patient_details_a.last_name, 
							patient_details_a.patient_number , tplan_procedure.invoice_number, 	min(tplan_procedure.date_invoiced) as date_invoiced,
							sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised 
							from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
							left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
							group by invoice_id, dispatch_number
							having dispatch_number =:dispatch_number";
						$error="Unable to get dispatched invoices";
						$placeholders['dispatch_number']="$dispatch_number";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						
						
						if($s->rowCount() > 0){
							$dispatch_number=html("$dispatch_number");
							echo "<div class=clear></div>";
							echo "<div class='grid-100 '><input type=button class='button_style printment' value=Print /></div>";
							echo "<div class='no_padding grid-100'>	";
								echo "<div class='grid-100 label make_bold'>MOLARS DENTAL CLINIC</div><br>";
								echo "<div class='grid-100 label'>DISPATCH NUMBER: $dispatch_number <br> ".date('Y-m-d')."</div><br>";
								echo "<table class='normal_table'><caption>$caption</caption><thead>
									<tr>
									<th class=invoice_in_date3>TREATMENT DATE</th>
									<th class=invoice_in_patient3>PATIENT NAME</th>
									<th class=invoice_in_company3>FILE No.</th>
									<th class=invoice_in_id3>INVOICE No.</th>
									<th class=invoice_in_cost3>COST</th>
									</tr></thead><tbody>";	
									foreach($s as $row){
										$date=html($row['date_invoiced']);
										$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
										$file_no=html($row['patient_number']);
										$invoice_no=html($row['invoice_number']);
										$cost=number_format(html($row['amount_authorised']),2);
										echo "<tr><td >$date</td><td >$name</td><td >$file_no</td>
										<td >$invoice_no</td><td >$cost</td>
										</tr>";
									}
									echo "</tbody></table>";
									echo "<br>";
								echo "<div class='grid-100 label'>Prepared by: $_SESSION[logged_in_user_names]</div><br>";	
								echo "<div class='grid-100 label'>Received by: ........................</div><br>";	
							echo "</div>";
						}						
						exit;	
					}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$message="bad#Unable to save patient disease details  ";
		}	
	}
	else{echo "<label  class=label>Nothing has been changed</label>";}
}


//remove un-dispatched invoices
if(isset($_POST['token_edis_2']) and 	$_POST['token_edis_2']!='' and $_POST['token_edis_2']==$_SESSION['token_edis_2']){
	$i=0;
	$invoice_id=$_POST['undispatch'];
	$n=count($invoice_id);
	if($n > 0){
		try{
				$pdo->beginTransaction();
					//now update invoices
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set dispatch_number='0' where invoice_id=:invoice_id";
						$error="Unable to update dispatch number";
						$placeholders['invoice_id']=$encrypt->decrypt("$invoice_id[$i]");
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						$i++;
					}
					$tx_result = $pdo->commit();
					if($tx_result){
						$result_class="success_response";
						$result_message="Dispatch note edited";
						echo "<div class='$result_class'>$result_message</div>";
						exit;	
					}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		$result_class="error_response";
		$result_message="Unable to edit dispatch note";
		echo "<div class='$result_class'>$result_message</div>";
		exit;
		}	
	}
	else{echo "<label  class=label>Nothing has been changed</label>";}
}

//get dispatch note for dispaly
if(isset($_POST['token_dr1']) and 	$_POST['token_dr1']!='' and $_POST['token_dr1']==$_SESSION['token_dr1']){
		$exit_flag=false;
		
		//this is for viewing undispatched
		if(!$exit_flag and isset($_POST['sby']) and $_POST['sby']=='undispatched'){
			
			//check if insurer is selcted
			if(!$exit_flag and !isset($_POST['ptype2']) or $_POST['ptype2']==''   ){	
					$result_class="error_response";
					$result_message="Please select and insurer";
					$exit_flag=true;
			}	
			
			//check if dates are selected
			if(!$exit_flag and (!isset($_POST['from_date2']) or $_POST['from_date2']==''  or !isset($_POST['to_date2']) or $_POST['to_date2']=='') ){	
					$result_class="error_response";
					$result_message="Please specify the date range for the dispatch note";
					$exit_flag=true;
			}
			
			//get undispatch invoices
			if(!$exit_flag){
					$sql=$error=$s='';$placeholders=array();
					$from=html($_POST['from_date2']);
					$to=html($_POST['to_date2']);
					
					if($_POST['ptype2']!='all'){
						//get insurance name
						$sql=$error=$s='';$placeholders=array();
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name from insurance_company where id=:id";
						$error2="Unable to get insurance company";
						$var=$encrypt->decrypt($_POST['ptype2']);
						$insurer_id=$var;
						$placeholders2[':id']=$var;
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){
							$insurer=html($row2['name']);
						}
						$insurer_criteria =' and b.type=:insurer_id ';
						$placeholders['insurer_id']=$insurer_id;
						$caption="Undispatched Invoices for $insurer  patients between $from and $to ";
					
					}
					else{
						$caption="Undispatched Invoices for all insured patients between $from and $to ";
						$insurer_criteria = ''; 
					}
			
					//get covered compnay name
					$corprate=$comp_covered='';
					/*if($_POST['covered_company']!='all'){
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select name from covered_company where id=:id";
						$error2="Unable to get covered company";
						$var2=$encrypt->decrypt($_POST['covered_company']);
						$placeholders2[':id']=$var2;
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){
							$comp_covered=html($row2['name']);
						}
						$corprate=' and patient_details_a.company_covered=:company_covered ';
						$placeholders['company_covered']=$var2;
						
					}*/
					
					/*$sql2="select tplan_procedure.invoice_id, min(tplan_procedure.date_invoiced) as date_invoiced, patient_details_a.type,
					tplan_procedure.invoice_number, tplan_procedure.dispatch_number as dis_num,
					sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised,
					patient_details_a.first_name, patient_details_a.middle_name,
					patient_details_a.last_name, patient_details_a.patient_number
					from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
					left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
					where patient_details_a.type=:insurer_id $corprate and tplan_procedure.pay_type=1 
					group by invoice_id
					having amount_authorised > 0
					and tplan_procedure.dispatch_number ='' and
					min(tplan_procedure.date_invoiced) >=:from_date and
					min(tplan_procedure.date_invoiced) <=:to_date";*/
					$sql="select a.id as invoice_id, a.when_raised as date_invoiced, a.invoice_number, b.first_name, b.middle_name,
					b.last_name, b.patient_number, d.name AS insurer, c.name AS company_covered
					from  patient_details_a b join unique_invoice_number_generator a on a.pid=b.pid 
					$insurer_criteria $corprate 
					JOIN insurance_company d ON d.id = b.type
					LEFT JOIN covered_company c ON c.id = b.company_covered
					where a.when_raised >=:from_date and a.when_raised <=:to_date";
					$placeholders['from_date']=$_POST['from_date2'];
					$placeholders['to_date']=$_POST['to_date2'];
					
					$error="Unable to get undispatched invoices 1";
					$s = select_sql($sql, $placeholders, $error, $pdo);
					$invoices_array=array();
					$total=0;
					foreach($s as $row){
					//echo "<br>$row[invoice_number]";
						//now check if the selected invoices are dispatched or not
						$dispatched_check='';
						$sql=$error=$s='';$placeholders=array();
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select dispatch_number from tplan_procedure where invoice_id=:invoice_id limit 1";
						$error2="Unable to get disp num";
						$placeholders2[':invoice_id']=$row['invoice_id'];
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						foreach($s2 as $row2){$dispatched_check=html($row2['dispatch_number']);}

						if($dispatched_check!=''){continue;}//invoice already dispcthed
						elseif($dispatched_check==''){//undispatched invoice
							//get invoice cost
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT sum(authorised_cost) from tplan_procedure where invoice_id=:invoice_id";
							$error2="Unable to get invoice cost";
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){$invoice_cost=html($row2[0]);}
							if($invoice_cost=='' or $invoice_cost==0){continue;}
							
							//get co_payment
							$invoice_copayment=0;
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="SELECT amount from co_payment where invoice_number=:invoice_id";
							$error2="Unable to get invoice co_payment";
							$placeholders2[':invoice_id']=$row['invoice_id'];
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){$invoice_copayment=html($row2['amount']);}		
							
							//get insurer and company covered
							
							$invoice_cost= $invoice_cost - $invoice_copayment;
							$insurer=html($row['insurer']);
							$company_covered=html($row['company_covered']);
							$date_invoiced=html($row['date_invoiced']);
							$file_no=html($row['patient_number']);
							$names=html("$row[first_name] $row[middle_name] $row[last_name]");
							$invoice_number=html($row['invoice_number']);
							$cost=number_format($invoice_cost,2);
							$invoice_id=html($row['invoice_id']);
							//$var=$encrypt->encrypt($row['invoice_id']);
							
							$invoices_array[]=array('when_added'=>"$date_invoiced",  'names'=>"$names", 'file_no'=>"$file_no", 'invoice_number'=>"$invoice_number",
										'cost'=>"$cost", 'insurer'=>"$insurer", 'company_covered'=>"$company_covered");
							$total = $total + html($invoice_cost);
						}
						
					}/*
					$placeholders2['from_date']=$_POST['from_date2'];
					$placeholders2['to_date']=$_POST['to_date2'];
					$placeholders2['insurer_id']=$insurer_id;
					$error2="Unable to get undispatched invoices";
					$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	*/
					if(count($invoices_array) > 0){
						echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr>
						<th class=invoice_in_date3>TREATMENT DATE</th>
						<th class=invoice_in_patient3>PATIENT NAME</th>
						<th class=invoice_in_company3>FILE No.</th>
						<th class=invoice_in_insurer3>INSURER</th>
						<th class=invoice_in_id3>INVOICE No.</th>
						<th class=invoice_in_cost3>COST</th>
						</tr></thead><tbody>";	
						foreach($invoices_array as $row2){
							$date=html($row2['when_added']);
							$name=ucfirst(html("$row2[names]"));
							$file_no=html($row2['file_no']);
							$invoice_no=html($row2['invoice_number']);
							$cost=html($row2['cost']);
							if($row2['company_covered']!=''){$insurer="$row2[insurer] - $row2[company_covered]";}
							else{$insurer=$row2['insurer'];}
							
							//$var=$encrypt->encrypt($row2['invoice_id']);
							echo "<tr><td >$date</td><td >$name</td><td >$file_no</td><td >$insurer</td>
							<td ><input type=button class='button_style button_in_table_cell invoice_no' value=$invoice_no /></td>
							<td >$cost</td></tr>";
							
						}
						echo "<tr class=total_background ><td class='make_bold' colspan=5>TOTAL</td><td>".number_format($total,2)."</td></tr>";
						echo "</tbody></table>";
						echo "<br>";
						exit;
					}
					else{
					
						$result_class="error_response";
						//$var=html($_POST['indv_crit']);
						$result_message="There are  no authorised invoices pending dispatch";
						$exit_flag=true;
					}
			}
			
		}		
		
		
		//get dispatched by insurer
		if(!$exit_flag and $_POST['indv']=='ins'){
			
			//check if insurer is selcted
			if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
					$result_class="error_response";
					$result_message="Please select and insurer";
					$exit_flag=true;
			}	
			
			//check if dates are selected
			if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){	
					$result_class="error_response";
					$result_message="Please specify the date range for the dispatch note";
					$exit_flag=true;
			}
			
			//get dispatch notes
			if(!$exit_flag){	
					$from_date=html($_POST['from_date']);
					$to_date=html($_POST['to_date']);
					//check if that dispatch number exists
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select a.dispatch_number,a.title,a.when_added,b.first_name,b.middle_name,b.last_name ,c.name,a.insurer_id
						from dispatched_invoices a, users b , insurance_company c 
						where insurer_id=:insurer_id and a.when_added>=:from_date and a.when_added<=:to_date and a.dispatched_by=b.id 
						and a.insurer_id=c.id";
					$error2="Unable to get dispatched notes by insurance ";
					$placeholders2[':insurer_id']=$encrypt->decrypt($_POST['ptype']);
					$placeholders2[':from_date']=$_POST['from_date'];
					$placeholders2[':to_date']=$_POST['to_date'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						$i=0;
						$token = form_token(); $_SESSION['token_dr1'] = "$token";  
						foreach($s2 as $row2){
							$disp_num=html($row2['dispatch_number']);
							$dipatcher_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
							$title=html($row2['title']);
							$when_added=html($row2['when_added']);
							$insurer_id=html($row2['insurer_id']);
							$insurer_name=html($row2['name']);
							if($i==0){
								echo "<table class='normal_table'><caption>DISPATCH NOTES FOR $insurer_name BETWEEN $from_date and $to_date </caption><thead>
										<tr>
										<th class=ins_ed1_date>DISPATCH DATE</th>
										<th class=ins_ed1_dispnum>DISPATCH NUMBER</th>
										<th class=ins_ed1_title>DESCRIPTION</th>
										<th class=ins_ed1_creator>DISPATCHER</th>
										</tr></thead><tbody>";							
							}
							echo "<tr><td>$when_added</td><td>$disp_num</td><td>"; ?>
							<form action="" method="POST" enctype="" name="" id="">
								
								<input type="hidden" name="token_dr1"  value="<?php echo $_SESSION['token_dr1']; ?>" />
								<input type=hidden name=indv value=disp_num />
								<input type=hidden name=indv_crit value="<?php echo $disp_num; ?>" />
								<input type="submit" class='button_table_cell' value="<?php echo $title; ?>" />
							</form>
							<?php							
							echo "</td><td>$dipatcher_name</td></tr>";
							$i++;;
						}
						echo "</tbody></table>";
						exit;
					}
					else{
					
						$result_class="error_response";
						//$var=html($_POST['indv_crit']);
						$result_message="There are no dispatch notes for your search criteria";
						$exit_flag=true;
					}
			}
			
		}
		elseif(!$exit_flag and ($_POST['indv']=='inv_num' or $_POST['indv']=='disp_num' or $_POST['indv']=='patient_number' or 
		$_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'  )){
			
			//check if serach criteriais set
			if(!$exit_flag and !isset($_POST['indv_crit']) or $_POST['indv_crit']==''   ){	
					$result_class="error_response";
					$result_message="Incorrect search criteria";
					$exit_flag=true;
			}
			
			//by patient names
			if(!$exit_flag and $_POST['indv']=='first_name' or $_POST['indv']=='middle_name' or $_POST['indv']=='last_name'){	
				$result=get_pt_name2($_POST['indv'],$_POST['indv_crit'],$pdo,$encrypt,'token_dr1','indv','patient_number','indv_crit');
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
					$sql2="select a.dispatch_number,a.title,a.when_added,b.first_name,b.middle_name,b.last_name ,c.name,a.insurer_id,d.invoice_number,
							e.first_name as ptf, e.middle_name as ptm, e.last_name as ptl,e.patient_number
							from dispatched_invoices a, users b , insurance_company c , tplan_procedure d, patient_details_a e
							where a.dispatch_number=d.dispatch_number and a.dispatched_by=b.id and a.insurer_id=c.id and 
							e.patient_number=:patient_number and e.pid=d.pid group by d.invoice_number";
					$placeholders2[':patient_number']=$_POST['indv_crit'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						$i=0;
						$token = form_token(); $_SESSION['token_dr1'] = "$token";  
						foreach($s2 as $row2){
							$disp_num=html($row2['dispatch_number']);
							$dipatcher_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
							$patient_name=html("$row2[ptf] $row2[ptm] $row2[ptl]");
							$title=html($row2['title']);
							$when_added=html($row2['when_added']);
							$insurer_id=html($row2['insurer_id']);
							$insurer_name=html($row2['name']);
							$invoice_number=html($row2['invoice_number']);
							$patient_number=html($row2['patient_number']);
							if($i==0){
								echo "<table class='normal_table'><caption>DISPATCH NOTES FOR PATIENT: $patient_number - $patient_name </caption><thead>
										<tr>
										<th class=pt_ed1_inv>INVOICE</th>
										<th class=pt_ed1_date>DISPATCH DATE</th>
										<th class=pt_ed1_dispnum>DISPATCH NUMBER</th>
										<th class=pt_ed1_title>DESCRIPTION</th>
										<th class=pt_ed1_dispatcher>DISPATCHER</th>
										</tr></thead><tbody>";							
							}
							echo "<tr><td>$invoice_number</td><td>$when_added</td><td>$disp_num</td><td>"; ?>
							<form action="" method="POST" enctype="" name="" id="">
								
								<input type="hidden" name="token_dr1"  value="<?php echo $_SESSION['token_dr1']; ?>" />
								<input type=hidden name=indv value=disp_num />
								<input type=hidden name=indv_crit value="<?php echo $disp_num; ?>" />
								<input type="submit" class='button_table_cell' value="<?php echo $title; ?>" />
							</form>
							<?php							
							echo "</td><td>$dipatcher_name</td></tr>";
							$i++;;
						}
						echo "</tbody></table>";
						exit;
					}
					else{
					
						$result_class="error_response";
						$var=html($_POST['indv_crit']);
						$result_message="Patient number $var does not exist or has no dispatched invoices";
						$exit_flag=true;
					}
			}			
			
			//by invoice number
			if(!$exit_flag and $_POST['indv']=='inv_num'){	
				
					//check if that dispatch number exists
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select a.dispatch_number,a.title,a.when_added,b.first_name,b.middle_name,b.last_name ,c.name,a.insurer_id
							from dispatched_invoices a, users b , insurance_company c , tplan_procedure d
							where a.dispatch_number=d.dispatch_number and a.dispatched_by=b.id and a.insurer_id=c.id and 
							d.invoice_number=:invoice_number group by d.invoice_number";
					$placeholders2[':invoice_number']=$_POST['indv_crit'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							$disp_num=html($row2['dispatch_number']);
							$dipatcher_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
							$title=html($row2['title']);
							$when_added=html($row2['when_added']);
							$insurer_id=html($row2['insurer_id']);
							$insurer_name=html($row2['name']);
							show_dispacth($pdo,$encrypt,"$disp_num","$dipatcher_name","$title","$when_added",$insurer_id,"$insurer_name");
							exit;
						}
					}
					else{
					
						$result_class="error_response";
						$var=html($_POST['indv_crit']);
						$result_message="Invoice number $var does not exist or has not been dispatched";
						$exit_flag=true;
					}
			}			
			
			//dispatch_number
			if(!$exit_flag and $_POST['indv']=='disp_num'){	
				
					//check if that dispatch number exists
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select a.dispatch_number,a.title,a.when_added,b.first_name,b.middle_name,b.last_name ,c.name,a.insurer_id
						from dispatched_invoices a, users b , insurance_company c 
						where dispatch_number=:dispatch_number and a.dispatched_by=b.id and a.insurer_id=c.id";
					$error2="Unable to get dispatch number";
					$placeholders2[':dispatch_number']=$_POST['indv_crit'];
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					if($s2->rowCount() > 0){
						foreach($s2 as $row2){
							$disp_num=html($row2['dispatch_number']);
							$dipatcher_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
							$title=html($row2['title']);
							$when_added=html($row2['when_added']);
							$insurer_id=html($row2['insurer_id']);
							$insurer_name=html($row2['name']);
							show_dispacth($pdo,$encrypt,"$disp_num","$dipatcher_name","$title","$when_added",$insurer_id,"$insurer_name");
							exit;
						}
					}
					else{
					
						$result_class="error_response";
						$var=html($_POST['indv_crit']);
						$result_message="Dispatch number $var does not exist";
						$exit_flag=true;
					}
			}
		}

	
		
	
		
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
		<div class='grid-100 '>
			<div class='grid-15 '><label for="" class="label">Search by</label></div>
				<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_dr1'] = "$token";  ?>
					<input type="hidden" name="token_dr1"  value="<?php echo $_SESSION['token_dr1']; ?>" />
				
					<select  name=sby class='dispatch_r1'><option></option>
						<option value='dispatched'>Dispatched</option>
						<option value='undispatched'>Undispatched</option>
					</select>
				</div>
		</div>		
				<div class=clear></div><br>			
				
	<!--<div class='multiple_invoice'>-->
		<div class='grid-100 '>
			
				<div class='grid-15 sdispatched'><label for="" class="label">Search dispatch by</label></div>
				<div class='grid-15 sdispatched'>
					<select  name=indv class='edit_dispatch'><option></option>
						<option value='ins'>Insurer</option>
						<option value='inv_num'>Invoice Number</option>
						<option value='disp_num'>Dispatch Number</option>
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
			<div class='no_padding serach_by_ins'>
				
				<div class='grid-15'><label for="" class="label">Select Insurer</label>
					</div>
				<div class='grid-25'><select name=ptype><option>
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
						echo "<option value='all'>ALL</option>";
					
					?>
					</option></select>
				</div>	

				<!--</select></div>	-->
				<div class=clear></div><br>
				<div class=' grid-15'><label for="" class="label">Dispatched between</label></div>
				<div class=grid-15><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-15><input type=text name=to_date class=date_picker /></div>
	<!--</div>-->
				<div class=clear></div>
				<br>
				<div class='prefix-45 grid-10'>	<input type="submit"  value="Submit"/></div>
			</div>	
			<!-- this is for non dispatched -->
			<div class='no_padding serach_by_ins2'>
				
				<div class='grid-15'><label for="" class="label">Select Insurer</label>
					</div>
				<div class='grid-25'><select name=ptype2><option></option>
					<option value='all'>All Insurers</option>
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
						
					
					?>
					</select>
				</div>	

				<!--</select></div>	-->
				<div class=clear></div><br>
				<div class=' grid-15'><label for="" class="label">Invoices raised between</label></div>
				<div class=grid-15><input type=text name=from_date2 class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-15><input type=text name=to_date2 class=date_picker /></div>
	<!--</div>-->
				<div class=clear></div>
				<br>
				<div class='prefix-45 grid-10'>	<input type="submit"  value="Submit"/></div>
			</div>	
	</form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>