<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,57)){exit;}
echo "<div class='grid_12 page_heading'>INVOICE AUTHORISATION</div>"; ?>
<div class="grid-100 margin_top">
<?php
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}

?>
	
<div class='edit_inv_div feedback hide_element'></div>
			
	<!--now show procedures already in points scheme-->
	<?php 
		if(isset($_POST['token_inv_auth1']) and isset($_SESSION['token_inv_auth1']) and $_POST['token_inv_auth1']==$_SESSION['token_inv_auth1'] )
		{
		$_SESSION['token_inv_auth1']='';
		$sql2=$error2=$s2='';$placeholders2=array();
		/*$sql2="select a.invoice_id,a.invoice_number, min(a.date_invoiced), b.last_name, b.middle_name, b.first_name, c.name , d.name,
		b.type, 
				b.company_covered, d.pre_auth_needed, d.smart_needed
				from tplan_procedure a,
               patient_details_a b , insurance_company c , covered_company d where a.invoice_id > 0 and b.pid=a.pid and c.id=b.type and b.company_covered=d.id
			    and a.date_invoiced >= :from_date and a.date_invoiced <=:to_date group by a.invoice_id order by a.invoice_id";
		*/
		/*$sql2="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced), 
				patient_details_a.last_name, patient_details_a.middle_name, patient_details_a.first_name, insurance_company.name , 
				covered_company.name, patient_details_a.type, patient_details_a.company_covered, covered_company.pre_auth_needed, 
				covered_company.smart_needed, sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid 
				join insurance_company on insurance_company.id=patient_details_a.type 
				join covered_company on patient_details_a.company_covered=covered_company.id 
				left join co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
				where tplan_procedure.invoice_id > 0  and tplan_procedure.date_invoiced >=:from_date and 
				tplan_procedure.date_invoiced <=:to_date group by tplan_procedure.invoice_id order by tplan_procedure.invoice_id";*/
		/*$sql2="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced) as min_date, 
				patient_details_a.last_name, patient_details_a.middle_name, patient_details_a.first_name, insurance_company.name , 
				covered_company.name, patient_details_a.type, patient_details_a.company_covered, covered_company.pre_auth_needed, 
				covered_company.smart_needed, sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) AS amount_requested
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid 
				join insurance_company on insurance_company.id=patient_details_a.type 
				join covered_company on patient_details_a.company_covered=covered_company.id 
				left join co_payment ON tplan_procedure.invoice_id = co_payment.invoice_number
				where tplan_procedure.invoice_id > 0   group by tplan_procedure.invoice_id having min_date>=:from_date
				and min_date<=:to_date order by tplan_procedure.invoice_id";
				
		$error2="Unable to get invocies raised for authorisation period";
		$placeholders2[':from_date']=$_POST['from_date'];
		$placeholders2[':to_date']=$_POST['to_date'];
		*/
			$invoices_array=array();
			$sql1=$error1=$s1='';$placeholders1=array();	
			$sql1="SELECT id,pid,invoice_number,when_raised FROM unique_invoice_number_generator WHERE 
				when_raised >=:from_date AND when_raised <=:to_date";
			$placeholders1[':from_date']=$_POST['from_date'];
			$placeholders1[':to_date']=$_POST['to_date'];
			$error1="Error: Unable to date range uniq ";
			$s1 = 	select_sql($sql1, $placeholders1, $error1, $pdo);
			//echo "$_POST[from_date]--$_POST[to_date]--".$s1->rowCount();
			foreach($s1 as $row1 ){
				$invoice_cost=$billed_cost=$amount_paid=$doctor='';
				//now get pt details
				$sql2=$error2=$s2='';$placeholders2=array();	
				$sql2="select first_name,middle_name,last_name,b.name as company_covered,c.name as insurer,
						b.smart_needed,b.pre_auth_needed
						from patient_details_a a 
						left join covered_company b on a.company_covered=b.id 
						left join insurance_company c on a.type=c.id where pid=:pid";
				$placeholders2[':pid']=$row1['pid'];
				$error2="Error: Unable to pt details from uniq ";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				//if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						$smart_needed=$pre_auth_needed='';
						$smart_needed=html("$row2[smart_needed]");
						$pre_auth_needed=html("$row2[pre_auth_needed]");
						
						//now get invoice cost
						$sql3=$error3=$s3='';$placeholders3=array();	
						$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) as billed_cost
								, ifnull( co_payment.amount, 0 ) as co_payment
								FROM tplan_procedure  LEFT JOIN co_payment ON 
								tplan_procedure.invoice_id = co_payment.invoice_number
								WHERE tplan_procedure.invoice_id =:invoice_id";
						$placeholders3[':invoice_id']=$row1['id'];
						$error3="Error: Unable to pt details from uniq ";
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
						foreach($s3 as $row3){
							$billed_cost=$row3['billed_cost'] - $row3['co_payment'];
							$billed_cost=html("$billed_cost");
							if($billed_cost > 0){$billed_cost=number_format($billed_cost,2);}
						}
						
						//now pre-auth details
						//check if the invoice has any authorisation record
						$sql3=$error3=$s3='';$placeholders3=array();
						$sql3="select authorisation_sent, authorisation_received, smart_run, amount_authorised, comments,id,invoice_id,smart_amount from invoice_authorisation 
								where invoice_id=:invoice_id";
						$error3="Unable to get invocies raised for authorisation period";
						$placeholders3[':invoice_id']=$row1['id'];
						$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);	
						$authorisation_sent=$authorisation_received=$amount_authorised=$comments=$smart_run=$smart_amount='';
						foreach($s3 as $row3){
							$authorisation_sent=html($row3['authorisation_sent']);
							$authorisation_received=html($row3['authorisation_received']);
							$smart_run=html($row3['smart_run']);
							$amount_authorised=html($row3['amount_authorised']);
							if($amount_authorised > 0){
								$amount_authorised=number_format($amount_authorised,2);
							}
							$comments=html($row3['comments']);
							$smart_amount=html($row3['smart_amount']);
							if($smart_amount > 0){
								$smart_amount=number_format($smart_amount,2);
							}
						}
							
							$when_added=html("$row1[when_raised]");
							$pid1=html("$row1[pid]");
							$patient_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
							$company=html("$row2[company_covered]");
							$insurer=html("$row2[insurer]");
							if($company!=''){$insurer="$insurer - $company";}
							else{$insurer="$insurer";}
							$invoice_number=html("$row1[invoice_number]");
							$invoice_id=html("$row1[id]");
							$val=$encrypt->encrypt("$invoice_id");
							$invoices_array[]=array('when_added'=>"$when_added",  'patient_name'=>"$patient_name", 
										'insurer'=>"$insurer",'invoice_number'=>"$invoice_number", 'billed_cost'=>"$billed_cost"
									 ,'val'=>"$val",'authorisation_sent'=>"$authorisation_sent",'authorisation_received'=>"$authorisation_received",
									 'smart_run'=>"$smart_run",'amount_authorised'=>"$amount_authorised",'comments'=>"$comments",
									 'smart_amount'=>"$smart_amount",'smart_needed'=>"$smart_needed",'pre_auth_needed'=>"$pre_auth_needed",'pid1'=>"$pid1");
									 
					}//end s2
			}//end s1
	//	$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
			if(count($invoices_array) > 0){ //if($s2->rowCount()>0){
				$token = form_token(); $_SESSION['token_inv_auth2'] = "$token";  //patient_form_td ?>
				<form action="" class='patient_form' method="post" name="" id="">
				<input type="hidden" name="token_inv_auth2"  value="<?php echo $_SESSION['token_inv_auth2']; ?>" />
				<?php
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				echo "<table class='normal_table'><caption>AUTHORISATION FOR INVOICES RAISED BETWEEN $from_date AND $to_date</caption><thead>
				<th class='inv_auth_date'>DATE</th><th class='inv_auth_pt'>PATIENT</th><th class='inv_auth_type'>PATIENT TYPE</th>
				<th class='inv_auth_inv'>INVOICE No.</th><th class='inv_auth_pay'>COST</th>
				<th class='inv_auth_sent'>PRE-AUTH<BR>SENT</th><th class='inv_auth_received'>AMOUNT<BR>AUTHORISED</th>
				<th class='inv_auth_comment'>COMMENTS RECEIVED</th><th class='inv_auth_smart'>SMART CARD<BR>CHECKED</th>
				</thead><tbody>";
				foreach($invoices_array as $row2){ //foreach($s2 as $row2){
					//if not pre-auth or smart is needed then skip
					if($row2['pre_auth_needed']=='NO' and $row2['smart_needed']=='NO'){continue;}
					$pre_auth_received1=$smart_received1='NO';
					//check if pre auth has been run
					if($row2['pre_auth_needed']=='YES' and $row2['authorisation_received']==''){$pre_auth_received1='NO';}
					elseif($row2['pre_auth_needed']=='YES' and $row2['authorisation_received']!=''){$pre_auth_received1='YES';}
					
					//check if smart has been run
					if($row2['smart_needed']=='YES' and $row2['smart_run']==''){$smart_received1='NO';}
					elseif($row2['smart_needed']=='YES' and $row2['smart_run']!=''){$smart_received1='YES';}
					
					$authorisations_needed=$encrypt->encrypt("$row2[pre_auth_needed]#$row2[smart_needed]#$pre_auth_received1#$smart_received1#$row2[pid1]");
					/*//check if the invoice has any authorisation record
					$sql3=$error3=$s3='';$placeholders3=array();
					$sql3="select authorisation_sent, authorisation_received, smart_run, amount_authorised, comments,id,invoice_id,smart_amount from invoice_authorisation 
							where invoice_id=:invoice_id";
					$error3="Unable to get invocies raised for authorisation period";
					$placeholders3[':invoice_id']=$row2['invoice_id'];
					$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);	
					$authorisation_sent=$authorisation_received=$amount_authorised=$comments=$smart_run='';
					foreach($s3 as $row3){
						$authorisation_sent=html($row3['authorisation_sent']);
						$authorisation_received=html($row3['authorisation_received']);
						$smart_run=html($row3['smart_run']);
						$amount_authorised=html($row3['amount_authorised']);
						$comments=html($row3['comments']);
						$id=$encrypt->encrypt($row3['invoice_id']);
						$smart_amount=html($row3['smart_amount']);
					}*
					$date=html($row2[2]);
					$pt_name=html("$row2[first_name] $row2[middle_name] $row2[last_name]");
					$pt_type=html("$row2[6] - $row2[7]");
					$invoice_number=html($row2['invoice_number']);
					$invoice_id=$encrypt->encrypt(html($row2['invoice_id']));
					$cost=number_format(html($row2[12]),2);
					echo "<tr><td>$date</td><td>$pt_name</td><td>$pt_type</td><td>$invoice_number</td><td>$cost</td>";
					*/
					echo "<tr><td>$row2[when_added]</td><td>$row2[patient_name]</td><td>$row2[insurer]</td><td>$row2[invoice_number]</td><td>$row2[billed_cost]</td>";
					//check if pre-auth is needed
					if($row2['pre_auth_needed']=='NO'){echo "<td colspan=3>N/A</td>";}
					elseif($row2['pre_auth_needed']=='YES'){
						//check if pre-auth was sent
						if($row2['authorisation_sent']==''){
							echo "<td><input type=checkbox value='$row2[val]' name=authorisation_sent[] /></td>";
							echo "<td colspan=2>&nbsp;</td>";
						}
						elseif($row2['authorisation_sent']!=''){
							echo "<td>$row2[authorisation_sent]</td>";
							//check if authorisation was received
							if($row2['authorisation_received']==''){
								echo "<td><input type=hidden name=ninye[] value='$row2[val]' /><input type=text name=authorisation_received[] />
								<input type=hidden name=authorisations_needed1[] value='$authorisations_needed' /></td>";
								//this will 
								echo "<td><textarea width=100% name=comments[]></textarea></td>";
							}
							elseif($row2['authorisation_received']!=''){
								echo "<td>$row2[authorisation_received] <br> $row2[amount_authorised]</td>";
								echo "<td>$row2[comments]</td>";
							}
						}						
						
					}
					//check if smart is needed
					if($row2['smart_needed']=='NO'){echo "<td>N/A</td>";}
					elseif($row2['smart_needed']=='YES'){
						//check if smartcard was sent
						if($row2['smart_run']==''){
							echo "<td><input type=hidden name=ninye_smart[] value='$row2[val]' />
								<input type=hidden name=authorisations_needed2[] value='$authorisations_needed' />
								<input type=text name=smart_run[] /></td>";
							//<td><input type=checkbox value='$invoice_id' name=smart_run[] /></td>";
						}
						elseif($row2['smart_run']!=''){
							if(isset($row2['smart_amount']) and $row2['smart_amount']!=''){
							//$smart_amount=number_format($smart_amount,2);
							}
							//$smart_amount=number_format($smart_amount,2);
							echo "<td>$row2[smart_run]<br>$row2[smart_amount]</td>";
						}						
						
					}
					echo "</tr>"; 
				}
				echo "<tr><td colspan=8>&nbsp;</td><td><input type=submit value=Submit /></td></tr>";
				echo "</table>";			
			}
			else{echo "No invoices were raised in this period";}
			exit;
		}	
			?>
	
		<form action="" method="post" name="" class='' id="">
		<?php $token = form_token(); $_SESSION['token_inv_auth1'] = "$token";  ?>
		<input type="hidden" name="token_inv_auth1"  value="<?php echo $_SESSION['token_inv_auth1']; ?>" />
			

		
		<div class='grid-25'><label for="" class="label">Select Invoices rasied from this date:</label></div>
		<div class='grid-10'><input type=text name=from_date class='date_picker' /></div>
		<div class='grid-10'><label for="" class="label">To this date:</label></div>
		<div class='grid-10'><input type=text name=to_date class='date_picker' /></div>
		

		
		<div class='grid-10 prefix-5'><input type=submit  value='submit' /></form></div>

		<div class=clear></div>
			<br><br>
<?php 
  //show invoices that pre-auth request has not been received after 3 days
  	$comments_title="MEMBERSHIP<br>NUMBER";
	$caption="Invoices sent for pre-auth but still not authorised between 2014-10-01 and 3 days ago";
	$pre_sent_unreceived=" and invoice_authorisation.authorisation_sent is not null and invoice_authorisation.authorisation_received is null ";
	$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
	$sql=$error=$s='';$placeholders=array();
	$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced),  
		patient_details_a.last_name, patient_details_a.middle_name, patient_details_a.first_name, insurance_company.name , 
		covered_company.name, patient_details_a.type, patient_details_a.company_covered, covered_company.pre_auth_needed, 
		covered_company.smart_needed, 
		invoice_authorisation.authorisation_sent, invoice_authorisation.authorisation_received, invoice_authorisation.smart_run, 
		invoice_authorisation.amount_authorised, invoice_authorisation.comments,invoice_authorisation.smart_amount,  
		users.first_name,users.middle_name,users.last_name,patient_details_a.member_no  
		
		from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid 
		join users on tplan_procedure.created_by=users.id
		join insurance_company on insurance_company.id=patient_details_a.type 
		join covered_company on patient_details_a.company_covered=covered_company.id $pre_auth_yes
		 join invoice_authorisation on tplan_procedure.invoice_id=invoice_authorisation.invoice_id 
		
		where tplan_procedure.invoice_id > 0  and 
		invoice_authorisation.authorisation_sent < DATE_SUB(curdate(),INTERVAL 3 DAY) and 
		invoice_authorisation.authorisation_sent > '2014-10-01'
		$pre_sent_unreceived  
		  
		group by tplan_procedure.invoice_id  order by patient_details_a.type";
		$error="Unable to get invoices not sent for pre-authorisation";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			$count=$s->rowCount();
		$caption="Invoices sent for pre-auth but still not authorised between 2014-10-01 and 3 days ago. No. of invoices $count";	
		if($s->rowCount() > 0){
			$i=$total_billed_cost=0;
			foreach($s as $row){
				//if($row['authorisation_sent']!=''){continue;}
				if($i == 0){
					echo "<table class='normal_table'><caption>$caption</caption><thead>
						<tr>
						<th class=ar_date>DATE</th>
						<th class=ar_insurer>INSURER</th>
						<th class=ar_pt>PATIENT</th>
						<th class=ar_doc>DOCTOR</th>
						<th class=ar_inv>INVOICE No.</th>
						<th class=ar_status>STATUS</th>
						<th class=ar_pre_sent>PRE-AUTH<br>SENT</th>
						<th class=ar_billed_cost>BILLED<br>COST</th>
						<th class=ar_pre_received>PRE-AUTH<br>RECEIVED</th>
						<th class=ar_smart>SMART<br>CHECKED</th>
						<th class=ar_comment>$comments_title</th>
						</tr></thead><tbody>";
				}
				$date=html($row[2]);
				$pt_type=html("$row[6] - $row[7]");
				$pt_name=ucfirst(html("$row[3] $row[4] $row[5]"));
				$doc=ucfirst(html("$row[18] $row[19] $row[20]"));
				$invoice_number=html($row['invoice_number']);
				$status=get_invoice_status($row['invoice_id'],$pdo);
				$pre_sent=html("$row[authorisation_sent]");
				//$pre_sent=html("$row[12]");
				$pre_auth_amount=$smart_amount='';
				if($row['authorisation_received']!='' and $row['amount_authorised']!=''){$pre_auth_amount="<br>".number_format(html($row['amount_authorised']),2);}
				if($row['smart_run']!='' and $row['smart_amount']!=''){$smart_amount="<br>".number_format(html($row['smart_amount']),2);}
				$pre_received=html("$row[authorisation_received] ")."$pre_auth_amount";
				$smart=html("$row[smart_run] ")."$smart_amount";
				if($comments_title == "MEMBERSHIP<br>NUMBER"){$comments=html("$row[member_no]");}
				else{$comments=html("$row[comments]");}
				
				//$invoice_no=$encrypt->encrypt(html($row['invoice_id']));
				
				//get billed cost
				$billed_cost="0.00";
				$sql3=$error3=$s3='';$placeholders3=array();	
				$sql3="SELECT sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 )  AS cost
						FROM tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
						WHERE tplan_procedure.invoice_id =:invoice_id";
				$placeholders3[':invoice_id']=$row['invoice_id'];
				$error3="Error: Unable to pt details from uniq ";
				$s3 = 	select_sql($sql3, $placeholders3, $error3, $pdo);
				foreach($s3 as $row3){
				   $billed_cost=html(number_format($row3['cost'],2));
				   $total_billed_cost=$total_billed_cost + html($row3['cost']);
				}
				
						//button_style button_in_table_cell <input type=button class='link_color invoice_no' value=$invoice_number />
				echo "<tr><td >$date</td><td >$pt_type</td><td >$pt_name</td><td >$doc</td>
							<td ><a href='#' class='link_color invoice_no_link'>$invoice_number</a></td>
							<td >$status</td><td >$pre_sent</td><td >$billed_cost</td><td >$pre_received</td><td >$smart</td><td >$comments</td>
							</tr>";
				$i++;
			}
			echo "<tr class='total_background'><td colspan=7>TOTAL</td><td>".number_format($total_billed_cost,2)."</td><td colspan=3>&nbsp;</td></tr></tbody></table>";
			exit;
		}			

?>			
</div>