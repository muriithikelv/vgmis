<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,66)){exit;}
echo "<div class='grid_12 page_heading'>INVOICE AUTHORISATION REPORT</div>";
?>
<div class=grid-container>
<?php 

//get results
if(isset($_POST['token_ir1']) and 	$_POST['token_ir1']!='' and $_POST['token_ir1']==$_SESSION['token_ir1']){
		$exit_flag=false;
		//check if search byis set
		if(!$exit_flag and !isset($_POST['sby']) or $_POST['sby']==''   ){	
				$result_class="error_response";
				$result_message="Please select the search criteria";
				$exit_flag=true;
		}	
		
		//check if doctor selected
		if(!$exit_flag and !isset($_POST['doc']) or $_POST['doc']=='' ){	
				$result_class="error_response";
				$result_message="Please specify a search criteria for the doctor";
				$exit_flag=true;
		}
		
		//check if insurer is selcted
		if(!$exit_flag and !isset($_POST['ptype']) or $_POST['ptype']==''   ){	
				$result_class="error_response";
				$result_message="Please select the insurer";
				$exit_flag=true;
		}	
		
		//check if company si selected
		if(!$exit_flag and !isset($_POST['covered_company']) or $_POST['covered_company']=='' ){	
				$result_class="error_response";
				$result_message="Please specify the company covered in the search criteria";
				$exit_flag=true;
		}

		//check if dates are selected
		if(!$exit_flag and (!isset($_POST['from_date']) or $_POST['from_date']==''  or !isset($_POST['to_date']) or $_POST['to_date']=='') ){	
				$result_class="error_response";
				$result_message="Please specify the date range for the report";
				$exit_flag=true;
		}	
		
		//prepare sql
		/*<option value="pre_unsent">Pre-Authorisation not sent</option>
						<option value="pre_sent">Pre-Authorisation sent</option>
						<option value="pre_sent_received">Pre-Authorisation sent and received</option>
						<option value="pre_sent_unreceived">Pre-Authorisation sent and not received</option>
						<option value="smart_unchecked">Samrtcard not checked</option>
						<option value="smart_checked">Smartcard checked</option>
						<option value="pre_declined">Pre-Authorisation Fully Declined</option>
						<option value="pre_partially_declined">Pre-Authorisation partially approved</option>
						<option value="smart_declined">Smartcard Fully Declined</option>
						<option value="smart_partially_declined">Smartcard partially approved</option>*/
		$from=html($_POST['from_date']);
		$to=html($_POST['to_date']);
		$comments_title="COMMENTS";
		$doctor=$insurer=$company='';
		$sql=$error=$s='';$placeholders=array();
		//doctor criteria
		if($_POST['doc']!='all'){
			$doc_id=$encrypt->decrypt($_POST['doc']);
			$doctor = " and users.id=:doc_id ";
			$placeholders[':doc_id']=$doc_id;
		}
		
		//insurer criteria
		if($_POST['ptype']!='all'){
			$insurer_id=$encrypt->decrypt($_POST['ptype']);
			$insurer = " and patient_details_a.type=:insurer_id ";
			$placeholders[':insurer_id']=$insurer_id;
		}
		
		//company criteria
		if($_POST['covered_company']!='all'){
			$company_id=$encrypt->decrypt($_POST['covered_company']);
			$company = " and patient_details_a.company_covered=:company_id ";
			$placeholders[':company_id']=$company_id;
		}
		
	
		//Pre-Authorisation not sent
		if($_POST['sby']=='pre_unsent' or $_POST['sby']=='pre_sent'  or $_POST['sby']=='pre_sent_received' or $_POST['sby']=='pre_sent_unreceived'
			or $_POST['sby']=='smart_unchecked' or $_POST['sby']=='smart_checked' or $_POST['sby']=='pre_declined' 
			or $_POST['sby']=='pre_partially_declined' or $_POST['sby']=='smart_declined' or $_POST['sby']=='smart_partially_declined'){
			$pre_unsent=$pre_sent=$pre_sent_received=$pre_sent_unreceived=$smart_unchecked=$pre_auth_yes=$smart_yes=$smart_checked=$left=$pre_declined='';
			$pre_partially_declined=$left_copayment=$having1=$smart_declined=$smart_partially_declined=$having2='';
			if($_POST['sby']=='pre_unsent'){
				$pre_unsent=" and invoice_authorisation.authorisation_sent is null ";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Invoices not sent for pre-authorisation between $from and $to";
				$no_data_message="There are no unauthorised invoices for the search criteria";
				$left=" left ";
			}
			elseif($_POST['sby']=='pre_sent'){
				$pre_sent=" and invoice_authorisation.authorisation_sent is not null ";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Invoices sent for pre-authorisation between $from and $to";
				$no_data_message="There are no pre-auth requests for the search criteria";
			}
			elseif($_POST['sby']=='pre_sent_received'){
				$pre_sent_received=" and invoice_authorisation.authorisation_received is not null ";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Invoices authorised between $from and $to";
				$no_data_message="There are authorised invoices for the search criteria";
			}
			elseif($_POST['sby']=='pre_sent_unreceived'){
				$comments_title="MEMBERSHIP<br>NUMBER";
				$pre_sent_unreceived=" and invoice_authorisation.authorisation_sent is not null and invoice_authorisation.authorisation_received is null ";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Invoices sent for pre-auth but still not authorised between $from and $to";
				$no_data_message="There are no invoices sent for pre-auth that have not yet been received";
			}
			if($_POST['sby']=='smart_unchecked'){
				$smart_unchecked=" and invoice_authorisation.smart_run is null ";
				$smart_yes = " and covered_company.smart_needed='YES' ";
				$caption="Invoices not checked for SMART between $from and $to";
				$no_data_message="There are no invoices for the search criteria";
				$left=" left ";
			}			
			if($_POST['sby']=='smart_checked'){
				$smart_checked=" and invoice_authorisation.smart_run is not null ";
				$smart_yes = " and covered_company.smart_needed='YES' ";
				$caption="Invoices  checked for SMART between $from and $to";
				$no_data_message="There are no invoices for the search criteria";
			}		
			if($_POST['sby']=='pre_declined'){
				$pre_declined=" and invoice_authorisation.amount_authorised = 0 ";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Declined authorisations between $from and $to";
				$no_data_message="There are no declined invoices for the search criteria";
			}		
			if($_POST['sby']=='pre_partially_declined'){
				$left_copayment = " left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number ";
				$having1 = " having invoice_cost >  invoice_authorisation.amount_authorised ";
				$pre_partially_declined = " ,sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) as invoice_cost";
				$pre_auth_yes = " and covered_company.pre_auth_needed='YES' ";
				$caption="Partially declined invoices between $from and $to";
				$no_data_message="There are no partially declined invoices for the search criteria";
			}			
			if($_POST['sby']=='smart_declined'){
				$smart_declined=" and invoice_authorisation.smart_amount = 0 ";
				$smart_yes = " and covered_company.smart_needed='YES' ";
				$caption="Invoices  with SMART value of zero shillings between $from and $to";
				$no_data_message="There are no invoices for the search criteria";
			}		
			if($_POST['sby']=='smart_partially_declined'){
				$left_copayment = " left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number ";
				$having2 = " having invoice_cost >  invoice_authorisation.smart_amount ";
				$smart_partially_declined = " ,sum( tplan_procedure.unauthorised_cost ) - ifnull( co_payment.amount, 0 ) as invoice_cost";
				$smart_yes = " and covered_company.smart_needed='YES' ";
				$caption="Partially approved SMART for invoices between $from and $to";
				$no_data_message="There are no invoices for the search criteria";
			}			
			
			$sql="select tplan_procedure.invoice_id,tplan_procedure.invoice_number, min(tplan_procedure.date_invoiced),  
				patient_details_a.last_name, patient_details_a.middle_name, patient_details_a.first_name, insurance_company.name , 
				covered_company.name, patient_details_a.type, patient_details_a.company_covered, covered_company.pre_auth_needed, 
				covered_company.smart_needed, 
				invoice_authorisation.authorisation_sent, invoice_authorisation.authorisation_received, invoice_authorisation.smart_run, 
				invoice_authorisation.amount_authorised, invoice_authorisation.comments,invoice_authorisation.smart_amount,  
				users.first_name,users.middle_name,users.last_name,patient_details_a.member_no  
				$pre_partially_declined $smart_partially_declined
				from tplan_procedure join  patient_details_a on patient_details_a.pid=tplan_procedure.pid $doctor $insurer $company
				join users on tplan_procedure.created_by=users.id
				join insurance_company on insurance_company.id=patient_details_a.type 
				join covered_company on patient_details_a.company_covered=covered_company.id $pre_auth_yes $smart_yes
				$left join invoice_authorisation on tplan_procedure.invoice_id=invoice_authorisation.invoice_id 
				$left_copayment
				where tplan_procedure.invoice_id > 0  and tplan_procedure.date_invoiced >=:from_date and 
				tplan_procedure.date_invoiced <=:to_date $pre_unsent $pre_sent $pre_sent_received $pre_sent_unreceived $smart_unchecked $smart_checked
				$pre_declined  $smart_declined
				group by tplan_procedure.invoice_id $having1 $having2 order by tplan_procedure.invoice_id";
				$error="Unable to get invoices not sent for pre-authorisation";
				
		}
		$placeholders[':from_date']=$_POST['from_date'];
		$placeholders[':to_date']=$_POST['to_date'];
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		$count=$s->rowCount();
		if($s->rowCount() > 0){
			$i=0;
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
				foreach($s3 as $row3){$billed_cost=html(number_format($row3['cost'],2));}
				
						//button_style button_in_table_cell <input type=button class='link_color invoice_no' value=$invoice_number />
				echo "<tr><td >$date</td><td >$pt_type</td><td >$pt_name</td><td >$doc</td>
							<td ><a href='#' class='link_color invoice_no_link'>$invoice_number</a></td>
							<td >$status</td><td >$pre_sent</td><td >$billed_cost</td><td >$pre_received</td><td >$smart</td><td >$comments</td>
							</tr>";
				$i++;
			}
			echo "</tbody></table>";
			exit;
		}
		else{
			$result_class='error_response';
			$result_message="$no_data_message";
		}
		
		
		


			
}	
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">
		
			<div class='grid-15 '><label for="" class="label">Search by</label></div>
				<div class='grid-25'>
					<?php $token = form_token(); $_SESSION['token_ir1'] = "$token";  ?>
					<input type="hidden" name="token_ir1"  value="<?php echo $_SESSION['token_ir1']; ?>" />
				
					<select  name=sby class=''><option></option>
						<option value="pre_unsent">Pre-Authorisation not sent</option>
						<option value="pre_sent">Pre-Authorisation sent</option>
						<option value="pre_sent_received">Pre-Authorisation sent and received</option>
						<option value="pre_sent_unreceived">Pre-Authorisation sent and not received</option>
						<option value="smart_unchecked">Samrtcard not checked</option>
						<option value="smart_checked">Smartcard checked</option>
						<option value="pre_declined">Pre-Authorisation Fully Declined</option>
						<option value="pre_partially_declined">Pre-Authorisation partially approved</option>
						<option value="smart_declined">Smartcard Fully Declined</option>
						<option value="smart_partially_declined">Smartcard partially approved</option>
					</select>
				</div>
				
				<!--show doctor-->
				<div class='grid-15'><label for="" class="label">Select Doctor</label>
				</div>
				<div class='grid-25'><select name=doc>
					<option value='all'>ALL Doctors</option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,first_name, middle_name, last_name from users where user_type=1 order by first_name";
						$error = "Unable to get doctors";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name] "));
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";
						}
						
					
					?>
					</select>
				</div>	
				<div class=clear></div><br>			
				
				<!--insurer-->
				<div class='grid-15'><label for="" class="label">Select Insurer</label>
					</div>
				<div class='grid-25'><select class=ptype2 name=ptype>
					<?php
						echo "<option value='all'>All Insurers</option>";
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
				<!--compnay covered-->
				<div class='grid-15 '><label for="" class="label">Company Covered</label></div>
				<div class='grid-25 '><select class='covered_company covered_company2' name=covered_company>
				<?php 
					echo "<option value='all'>All Companies</option>";
					/*if(isset($_SESSION['id']) and $_SESSION['id']!=''){
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
							
					}*/
				?>
				</select></div>		

				<!--</select></div>	-->
				<div class=clear></div><br>
				
				<!--date range-->
				<div class=' grid-15'><label for="" class="label">Invoices raised between</label></div>
				<div class=grid-25><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
				<div class=grid-25><input type=text name=to_date class=date_picker /></div>
	<!--</div>-->
				<div class=clear></div>
				<br>
				<div class='prefix-55 grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>