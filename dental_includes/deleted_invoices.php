<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,103)){exit;}
echo "<div class='grid_12 page_heading'>DELETED INVOICES</div>";
?>
<div class=grid-container>
<?php 

//get results
if(isset($_POST['token_dir1']) and 	$_POST['token_dir1']!='' and $_POST['token_dir1']==$_SESSION['token_dir1']){
		$_SESSION['token_dir1']='';
		$exit_flag=false;
		$insurer=' all ';
		$covered_company='';$comp_covered=$pnum=$date_criteria=$inv_num_criteria='';
		$pnum_search=$exit_flag=false;
		$sql=$error=$s='';$placeholders=array();
		
		
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
		
				
		if(!$exit_flag){
		$from_date=html($_POST['from_date']);
		$to_date=html($_POST['to_date']);
		$doctor=$insurer=$company=$balance='';
		$total_cost=$total_paid=0;
				
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
			
		
				$sql="select deleted_invoices.invoice_id,deleted_invoices.invoice_number, min(deleted_invoices.date_invoiced) as date_invoiced,  
					 concat(users.first_name,' ',users.middle_name,' ',users.last_name) as doctor ,patient_details_a.patient_number,
					  concat(patient_details_a.first_name,' ',patient_details_a.middle_name,' ',patient_details_a.last_name) as patient_name ,
					sum( deleted_invoices.unauthorised_cost ) - ifnull( deleted_co_payment.amount, 0 ) AS amount_requested, 
					sum( deleted_invoices.authorised_cost ) - ifnull( deleted_co_payment.amount, 0 ) AS amount_approved ,
					insurance_company.name as insurer, covered_company.name as corprate, deleted_invoices.when_deleted,
					 concat(users_2.first_name,' ',users_2.middle_name,' ',users_2.last_name) as deleted_by
					from deleted_invoices join  patient_details_a on deleted_invoices.when_deleted >=:from_date and 
						deleted_invoices.when_deleted <=:to_date and patient_details_a.pid=deleted_invoices.pid  $insurer $company
					join users on deleted_invoices.created_by=users.id
					join users as users_2 on deleted_invoices.deleter=users_2.id
					left join insurance_company on patient_details_a.type=insurance_company.id
					left join covered_company on patient_details_a.company_covered=covered_company.id
					left join deleted_co_payment on deleted_invoices.invoice_id=deleted_co_payment.invoice_number
					where deleted_invoices.invoice_id > 0 
					group by deleted_invoices.invoice_id  order by deleted_invoices.invoice_id";
				
				
			$placeholders[':from_date']=$_POST['from_date'];	
			$placeholders[':to_date']=$_POST['to_date'];
			$s = select_sql($sql, $placeholders, $error, $pdo);	
			if($s->rowCount() > 0){ 
				$i=0;
				echo "<div class='grid-100 div_shower44'></div>";
				foreach($s as $row ){
					$patient_name=html($row['patient_name']);
					$deleted_by=html($row['deleted_by']);
					$date_deleted=html($row['when_deleted']);
					$patient_number=html($row['patient_number']);
					$doctor=html($row['doctor']);
					$date_invoiced=html($row['date_invoiced']);
					$billed=html("$row[amount_requested]");
					if($billed!=''){$billed=number_format($billed,2);}
					$authorised=html("$row[amount_approved]");
					if($authorised!=''){$authorised=number_format($authorised,2);}
					$invoice_number2=html($row['invoice_number']);
					$insurer=html($row['insurer']);
					$corprate=html($row['corprate']);
					if($corprate!=''){$insurer="$insurer - $corprate";}
					if($i==0){
						if($_POST['ptype']!='all'){$caption=strtoupper("$insurer invoices deleted between $from_date and $to_date");}
						else{$caption=strtoupper("invoices deleted between $from_date and $to_date");}
						echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=dir_count></th>
						<th class=dir_date>DATE<br>INVOICED</th><th class=dir_doc>INVOICED BY</th><th class=dir_pname>PATIENT NAME</th><th class=dir_pnum>PATIENT<br>NUMBER</th>
						<th class=dir_ptype>PATIENT TYPE</th><th class=dir_invoice>INVOICE NUMBER</th><th class=dir_billed>BILLED COST</th>
						<th class=dir_authorised>AUTHORISED COST</th><th class=dir_date>DATE<br>DELETED</th>
						<th class=dir_deleter>DELETED BY</th></tr></thead><tbody>";
					}
					$i++;
					echo "<tr><td>$i</td><td>$date_invoiced</td><td>$doctor</td><td>$patient_name</td><td>$patient_number</td>
					<td>$insurer</td><td><input type=button class='button_style button_in_table_cell invoice_no_deleted' value='$invoice_number2'  /></td><td>$billed</td><td>$authorised</td><td>$date_deleted</td><td>$deleted_by</td>
					</tr>";
				}
				echo "</tbody></table><br>";
			}
			else{ echo "<div class='error_response'>There are no deleted invoices for the selected search criteria</div>";}
			exit;
		}//end do if exit flag is not true
		if($exit_flag){echo "<div class=$result_class>$result_message</div><br>";}
		
		
}	
	?>
			
			
	<form action="" method="POST" enctype="" name="" id="">

				<!--insurer-->
				<div class='grid-20'><label for="" class="label">Select Insurer</label>
					<?php $token = form_token(); $_SESSION['token_dir1'] = "$token";  ?>
					<input type="hidden" name="token_dir1"  value="<?php echo $_SESSION['token_dir1']; ?>" />
				
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
				<div class=' grid-20'><label for="" class="label">Invoices deleted between</label></div>
				<div class=grid-25><input type=text name=from_date class=date_picker /></div>
				<div class=grid-15><label for="" class="label">And</label></div>
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