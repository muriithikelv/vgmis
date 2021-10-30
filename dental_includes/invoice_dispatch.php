<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,58)){exit;}
echo "<div class='grid_12 page_heading'>INVOICE DISPATCH</div>";
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

//dispatched invoices
if(isset($_POST['token_dis_2']) and 	$_POST['token_dis_2']!='' and $_POST['token_dis_2']==$_SESSION['token_dis_2']){
	$i=0;
	$invoice_id=$_POST['dispatch'];
	$n=count($invoice_id);
	if($n > 0){
		try{
				$pdo->beginTransaction();
					$var=$encrypt->decrypt($_POST['ninye']);
					$data=explode('#',$var);
					$data[1]=str_replace("Undispatched Invoices", "Treatments done ", $data[1]);				
					$caption=strtoupper(html("$data[1]"));
					//get a dispatch number
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into dispatched_invoices set
							insurer_id=:insurer_id,
							title=:title,
							when_added=now(),
							dispatched_by=:dispatched_by";
					$error="Unable to get dispatch number";
							$placeholders['insurer_id']=$data[0];
							$placeholders['title']="$data[1]";
							$placeholders['dispatched_by']=$_SESSION['id'];
									
					$id = 	get_insert_id($sql, $placeholders, $error, $pdo);	
					$dispacth_number="D$id-".date('m/y');

					//now update table with dispatch number
					$sql=$error=$s='';$placeholders=array();
					$sql="update dispatched_invoices set dispatch_number=:dispatch_number where id=:id";
					$error="Unable to  dispatch number";
							$placeholders['id']=$id;
							$placeholders['dispatch_number']="$dispacth_number";
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					
					//now update invoices
					while($i < $n){
						$sql=$error=$s='';$placeholders=array();
						$sql="update tplan_procedure set dispatch_number=:dispatch_number where invoice_id=:invoice_id";
						$error="Unable to update dispatch number";
								$placeholders['invoice_id']=$encrypt->decrypt("$invoice_id[$i]");
								$placeholders['dispatch_number']="$dispacth_number";
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						$i++;
					}
					$tx_result = $pdo->commit();
					if($tx_result){
						$result_class="success_response";
						$result_message="Invoices Dispatched";
						echo "<div class='$result_class'>$result_message</div>";
						//now show what to print
						/*$sql=$error=$s='';$placeholders=array();
						$sql="select patient_details_a.first_name, patient_details_a.middle_name, patient_details_a.last_name, 
							patient_details_a.patient_number , tplan_procedure.invoice_number, 	min(tplan_procedure.date_invoiced) as date_invoiced,
							sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised 
							from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
							left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
							group by invoice_id, dispatch_number
							having dispatch_number =:dispatch_number";
						$error="Unable to get dispatched invoices";
						$placeholders['dispatch_number']="$dispacth_number";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);
						*/
						
						$invoices_array=$_SESSION['balance_invoice']=array();
						$total=0;
						//get dispatched invoices
						$sql1=$error1=$s1='';$placeholders1=array();	
						$sql1="SELECT sum(authorised_cost) as authorised_cost,invoice_number,invoice_id,pid from tplan_procedure where 
							dispatch_number=:dispatch_number group by invoice_id";
						$placeholders1[':dispatch_number']="$dispacth_number";
						$error1="Error: Unable to date range uniq ";
						$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
						foreach($s1 as $row1 ){
							//get patient details
							$sql2=$error2=$s2='';$placeholders2=array();	
							$sql2="select first_name,middle_name,last_name,patient_number
									from patient_details_a where pid=:pid ";
							$placeholders2[':pid']=$row1['pid'];
							$error2="Error: Unable to pt details from uniq ";
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							foreach($s2 as $row2){
								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$patient_number=html($row2['patient_number']);
							}
							
							//now get co_payment if any
							$co_payment=0;
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT  ifnull( co_payment.amount, 0 ) AS co_payment
									FROM  co_payment where invoice_number =:invoice_id ";
							$placeholders3[':invoice_id']=$row1['invoice_id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$co_payment=html($row3['co_payment']);}
							
							//now get date invoiced
							$sql3=$error3=$s3='';$placeholders3=array();	
							$sql3="SELECT  when_raised from unique_invoice_number_generator where id =:invoice_id ";
							$placeholders3[':invoice_id']=$row1['invoice_id'];
							$error3="Error: Unable to pt details from uniq ";
							$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
							foreach($s3 as $row3){$date_raised=html($row3['when_raised']);}
							
							$invoice_number=html($row1['invoice_number']);
							$invoice_cost=$row1['authorised_cost'] - $co_payment;		//	if($row3['dispatch_number']==''){continue;}
							$invoices_array[]=array('date'=>"$date_raised",  'name'=>"$patient", 'file_no'=>"$patient_number", 
													'invoice_no'=>"$invoice_number",'cost'=>"$invoice_cost");
						}
								
						if(count($invoices_array) > 0){
							$dispatch_number=html("$dispacth_number");
							echo "<div class=clear></div>";
							echo "<div class='grid-100 '><input type=button class='button_style printment' value=Print /></div>";
							echo "<div class='no_padding grid-100'>	";
								echo "<div class='grid-100 label make_bold'>MOLARS DENTAL CLINIC</div><br>";
								echo "<div class='grid-100 label'>DISPATCH NUMBER: $dispacth_number <br> ".date('Y-m-d')."</div><br>";
								echo "<table class='normal_table bordered_table'><caption>$caption</caption><thead>
									<tr>
									<th class=invoice_in_date3>TREATMENT DATE</th>
									<th class=invoice_in_patient3>PATIENT NAME</th>
									<th class=invoice_in_company3>FILE No.</th>
									<th class=invoice_in_id3>INVOICE No.</th>
									<th class=invoice_in_cost3>COST</th>
									</tr></thead><tbody>";	
									foreach($invoices_array as $row){
										/*$date=html($row['date_invoiced']);
										$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
										$file_no=html($row['patient_number']);
										$invoice_no=html($row['invoice_number']);
										$cost=number_format(html($row['amount_authorised']),2);*/
										echo "<tr><td >$row[date]</td><td >$row[name]</td><td >$row[file_no]</td>
										<td >$row[invoice_no]</td><td >".number_format($row['cost'],2)."</td>
										</tr>";
										$total = $total + $row['cost'];
									}
									echo "<tr class=total_background><td colspan=4>TOTAL</td><td >".number_format($total,2)."</td>
										</tr>";
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
		//$message="bad#Unable to save patient disease details  ";
		}	
	}
	else{echo "<label  class=label>Nothing has been changed</label>";}
}

//get undispatched invoices
if(isset($_POST['token_dis_1']) and 	$_POST['token_dis_1']!='' and $_POST['token_dis_1']==$_SESSION['token_dis_1']){
		$exit_flag=false;
		//check if insurer is selcted
		if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
				$result_class="error_response";
				$result_message="Please select and insurer";
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
				$var=$encrypt->decrypt($_POST['ptype']);
				$insurer_id=$var;
				$placeholders2[':id']=$var;
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
			
			$invoices_array=$_SESSION['balance_invoice']=array();
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
				$sql2="select first_name,middle_name,last_name,patient_number
						from patient_details_a where pid=:pid and type=:type $corprate ";
				$placeholders2[':pid']=$row1['pid'];
				$placeholders2[':type']=$insurer_id;
				if($_POST['covered_company']!='all'){$placeholders2['company_covered']=$var2;}
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost,dispatch_number
								FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id group by invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
						//	if($row3['dispatch_number']==''){continue;}
							if($row3['cost'] > 0 and $row3['dispatch_number']==''){
							//echo "<br>$row3[dispatch_number]--";
								$invoice_cost=html($row3['cost']);
								$when_added=html("$row1[when_raised]");
								$patient=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
								$patient_number=html($row2['patient_number']);
								$invoice_number=html($row1['invoice_number']);
								$var=$encrypt->encrypt($row1['id']);
								$invoices_array[]=array('date'=>"$when_added",  'name'=>"$patient", 'file_no'=>"$patient_number", 
										'invoice_no'=>"$invoice_number",'cost'=>"$invoice_cost", 'var'=>"$var");
							}
						}
					}
				}
			}
			/*$sql="select tplan_procedure.invoice_id, min(tplan_procedure.date_invoiced) as date_invoiced, patient_details_a.type,
					tplan_procedure.invoice_number, tplan_procedure.dispatch_number as dis_num,
					sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised,
					patient_details_a.first_name, patient_details_a.middle_name,
					patient_details_a.last_name, patient_details_a.patient_number
					from tplan_procedure join patient_details_a on tplan_procedure.pid=patient_details_a.pid 
					left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
					where patient_details_a.type=:insurer_id $corprate and tplan_procedure.pay_type=1 
					group by invoice_id
					having amount_authorised > 0
					and (tplan_procedure.dispatch_number ='' or tplan_procedure.dispatch_number is null) and
					min(tplan_procedure.date_invoiced) >=:from_date and
					min(tplan_procedure.date_invoiced) <=:to_date";
			$placeholders['from_date']=$_POST['from_date'];
			$placeholders['to_date']=$_POST['to_date'];
			$placeholders['insurer_id']=$insurer_id;
			$error="Unable to get undispatched invoices";
			$s = select_sql($sql, $placeholders, $error, $pdo);	*/
			$from=html($_POST['from_date']);
			$to=html($_POST['to_date']);
			$caption="Undispatched Invoices for $insurer $comp_covered patients between $from and $to ";
		//echo "count is ".$s->rowCount();exit;
		
		//echo "count is ".$s->rowCount();exit;
		if(count($invoices_array) > 0){ ?>
				<form action="" method="POST" enctype="" name="" id=""><?php
					$var22=$encrypt->encrypt("$insurer_id#Undispatched Invoices for $insurer $comp_covered patients");
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
				foreach($invoices_array as $row){
					/*$date=html($row['date_invoiced']);
					$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
					$file_no=html($row['patient_number']);
					$invoice_no=html($row['invoice_number']);
					$cost=number_format(html($row['amount_authorised']),2);
					$var=$encrypt->encrypt($row['invoice_id']);
					*/echo "<tr><td class=count>$row[date]</td><td class=count>$row[name]</td><td class=count>$row[file_no]</td>
					<td class=count><input type=button class='button_style button_in_table_cell invoice_no' value=$row[invoice_no] />
					</td><td class=count>".number_format($row['cost'],2)."</td>
					<td class=count><input type=checkbox name=dispatch[] value=$row[var]</td></tr>";
					$total = $total + html($row['cost']);
				}
				echo "<tr><td class=make_bold colspan=4>TOTAL</td><td>".number_format($total,2)."</td><td>";
				//		$token = form_token(); $_SESSION['token_edis_4'] = "$token";  
				//echo "<input type=hidden name=token_edis_4  value='$_SESSION[token_edis_4]' />
				$token = form_token(); $_SESSION['token_dis_2'] = "$token";  
				echo "<input type=hidden name=token_dis_2  value='$_SESSION[token_dis_2]' />";
				echo "		<input type=submit class='button_style button_in_table_cell' value='Submit' /></form></td></tr>";
				echo "</tbody></table>";
				echo "<br>";
				
			
				
			}
			
		else{echo "<label  class=label>There is no undispatched invoice for the selected criteria or the invoices are still unauthorised</label>";}
		echo "<div id=view_lab></div>";
		exit;
	}
	else{
		echo "<div class='$result_class'>$result_message</div>";
	}
		
}	
	?>
			

			
	<form action="" method="POST" enctype="" name="" id="">
	<!--<div class='multiple_invoice'>-->
				<div class='grid-15'><label for="" class="label">Select Insurer</label>
					<?php $token = form_token(); $_SESSION['token_dis_1'] = "$token";  ?>
					<input type="hidden" name="token_dis_1"  value="<?php echo $_SESSION['token_dis_1']; ?>" />
				</div>
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
				<div class='grid-25 '><select class=covered_company name=covered_company><option></option>
				<?php 
					if(isset($_SESSION['id']) and $_SESSION['id']!=''){
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,name from covered_company order by name";
						$error = "Unable to covered companies";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['name']);
							$val=$encrypt->encrypt(html($row['id']));
							//echo "<option value='$val'>$name</option>";
						}					
							//$val=$encrypt->encrypt("all");
							echo "<option value='all'>ALL</option>";
					}
				?>
				</select></div>	
				<div class=clear></div><br>
				<div class=grid-15><label for="" class="label">Invoices raised between</label></div>
				<div class=grid-25><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-25><input type=text name=to_date class=date_picker /></div>
	<!--</div>-->
	<div class=clear></div>
	<br>
	<div class='prefix-15 grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
<?php 

  //get undispatched invoices for last 25 days
	$inv_count_array=array();
	//get details from unique_inv_table first
	$sql1=$error1=$s1='';$placeholders1=array();	
	$sql1="SELECT c.type, a.id from unique_invoice_number_generator a, tplan_procedure b, patient_details_a c
			where b.invoice_id=a.id and a.pid=c.pid and 
			a.when_raised > '2015-01-01' and a.when_raised < DATE_SUB(curdate(),INTERVAL 25 DAY)
			and   (b.dispatch_number is null or b.dispatch_number ='')
			group by a.id";
	$error1="Error: Unable to get invoices raised more than 25 days back";
	$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
	foreach($s1 as $row1 ){
		//now get invoice cost
		$sql3=$error3=$s3='';$placeholders3=array();	
		$sql3="SELECT sum( tplan_procedure.authorised_cost ) - ifnull( co_payment.amount, 0 ) AS cost,dispatch_number
				FROM tplan_procedure LEFT JOIN co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
				WHERE tplan_procedure.invoice_id =:invoice_id group by invoice_id";
		$placeholders3[':invoice_id']=$row1['id'];
		$error3="Error: Unable to pt details from uniq ";
		$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
		foreach($s3 as $row3){
		//	if($row3['dispatch_number']==''){continue;}
			if($row3['cost'] > 0 and $row3['dispatch_number']==''){
			 $inv_count_array[]=$row1['type'];
			}
		}
	}
	if(count($inv_count_array) > 0){
		$result=array_count_values($inv_count_array);
		$caption=strtoupper("Undispatched Invoices that were raised between 2015-01-01 and 25 days ago");
		echo "<table class='half_width move_a_bit'><caption>$caption</caption><thead>
		<tr><th class=ntf_count></th><th class=ntf_desc>INSURER</th><th class=ntf_action>No. OF INVOICES</th></tr>
		</thead><tbody>";
		$patient_type_array=$patient_type_name_array=array();
		$patient_type_name_array=$_SESSION['patient_type_name_array'];
		$patient_type_array=$_SESSION['patient_type_array'];
		$i=$total=0;//print_r($patient_type_array);print_r($patient_type_name_array);
		for($x = 0; $x < count($patient_type_array); $x++) {
			
			if (in_array($patient_type_array[$x], $inv_count_array)) {
				if($result[$patient_type_array[$x]] > 0){
					$i++;
					$number_of_inv=$result[$patient_type_array[$x]];
					echo "<tr><td>$i</td>
						<td>$patient_type_name_array[$x]</td>
						<td>$number_of_inv</td></tr>";
						$total = $total + $number_of_inv;
				}
			}
		}
		echo "<tr><td colspan=2>TOTAL NUMBER OF INVOICES</td><td>".number_format($total)."</td></tbody></tbale>"	;
	}	

?>	

</div>