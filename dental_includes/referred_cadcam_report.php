<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,84)){exit;}
echo "<div class='grid_12 page_heading'>EXTERNAL PATIENT CADCAM</div>"; ?>
<div class="grid-100 margin_top ">
<?php   
//show appointments
if( isset($_POST['token_rcr']) and isset($_SESSION['token_rcr']) and $_SESSION['token_rcr']==$_POST['token_rcr']){
	$_SESSION['token_rcr']='';
	$exit_flag=$skip_cash=$skip_insured=false;
	//check if docotr is set
	if(!$exit_flag and !isset($_POST['doctor']) or 	$_POST['doctor']==''){
		$error_class='error_response';
		$message="Please specify the referrer to search by. ";
		$exit_flag=true;
	}
	//check if pay type is set
	if(!$exit_flag and !isset($_POST['pay_type']) or 	$_POST['pay_type']==''){
		$error_class='error_response';
		$message="Please specify the pay type to search by. ";
		$exit_flag=true;
	}	
	//check if dates are set
	if(!$exit_flag and !isset($_POST['from_date']) or 	$_POST['from_date']=='' or !isset($_POST['to_date']) or $_POST['to_date']==''){
		$error_class='error_response';
		$message="Please specify the date range to search by. ";
		$exit_flag=true;
	}
	if(!$exit_flag){
		//get report
		$sql=$error=$s=$group_id='';$placeholders=array();
		$referrer_criteria='';
		if($_POST['doctor']!='all'){
			$referrer_id=$encrypt->decrypt("$_POST[doctor]");
			$referrer_criteria = " and b.referee=:referrer ";	
			$placeholders[':referrer']=$referrer_id;
		}
		
		//pay type
		if($_POST['pay_type']=='insured'){$skip_cash=true;}
		elseif($_POST['pay_type']=='cash'){$skip_insured=true;}
		
		$cash_count=$insured_count=0;
			
		
		//self payments
		if(!$skip_cash and !$exit_flag){
			$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
				c.name as referrer_name,  d.details, d.date_procedure_added ,d.authorised_cost 
				from tplan_procedure as d join patient_details_a as a on d.pid=a.pid and a.internal_patient=2 
				join patient_details_b as b on a.pid=b.pid $referrer_criteria
				left join cadcam_referrer as c on c.id=b.referee
				where date_procedure_added>=:from_date and date_procedure_added<=:to_date
				and pay_type=2";
			$error="Unable to generate cash cadcam referals";
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			$cash_count=$s->rowCount();
			if($s->rowCount() > 0){
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				$count=$total_cash=0;
				$for_user='';
				foreach($s as $row){
					$name=ucfirst(html("$row[patient_name]"));
					if($_POST['doctor'] == 'all') {}
					if($count==0){
				/*	echo "<div class=tplan_table><div class=tplan_table_caption>CASH CADCAMs done for external patients between $from_date and $to_date</div>
						<div class=tplan_table_row2>
							<div class='xrr_date  make_bold white_text'>DATE</div>
							<div class='xrr_pname make_bold white_text'>PATIENT</div><div class='xrr_pno make_bold white_text'>PATIENT NO.</div>
							<div class='xrr_ref make_bold white_text'>REFERRER</div>
								<div class='xrr_shade make_bold white_text make_bold'>shade</div>
									<div class='xrr_quantity make_bold white_text make_bold'>quantity</div>
							<div class='xrr_cost make_bold white_text'>COST</div>
							
							
						</div>
						</div>
						<div class=tplan_table>
						";*/
					
						echo "<table class='normal_table ecr1'><caption>CASH CADCAMs done for external patients between $from_date and $to_date</caption><thead>
							  <tr><th class='xrr_count'></th><th class=xrr_date>DATE</th><th class=xrr_pname>PATIENT</th><th class=xrr_pno>PATIENT No.</th>
							  <th class=xrr_ref>REFERRER</th><th class='xrr_xray td_div_holder'><div class='tplan_table'>
									<div class='tplan_table_row2'>
										<div class='xrr_shade xrr_shade2'>SHADE</div>
										<div class='xrr_quantity xrr_shade2'>QUANTITY</div>
									</div>
								</div></th><th class=xrr_cost>COST</th></tr></thead><tbody>";
						
					}
					$count++;
					$patient=ucfirst(html($row['patient_name']));
					$referrer=ucfirst(html($row['referrer_name']));
					$cost=ucfirst(html($row['authorised_cost']));
					$date=ucfirst(html($row['date_procedure_added']));
					$patient_no=ucfirst(html($row['patient_number']));
					/*echo "<div class=tplan_table_row>";
						echo "<div class='xrr_date'>$date</div>
							<div class='xrr_pname'>$patient</div><div class='xrr_pno'>$patient_no</div>
							<div class='xrr_ref'>$referrer</div>";
							//get shades used
							$sql2=$error2=$s2='';$placeholders2=array();
							$sql2="select a.name, b.quantity from cadcam_types as a join blocks_stock_out as b on
									b.group_number=:group_number and b.block_id=a.id";
							$placeholders2['group_number']=$row['details']	;	
							$error2="unable to get shades used";
							$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
							if($s){}
							echo "<div class=tplan_procedure_row>";	
							foreach($s2 as $row2){
								$shade=html($row2['name']);
								$quantity=html($row2['quantity']);
								echo "<div class=tplan_table_row>
										<div class='xrr_shade xrr_shade2'>$shade</div>
										<div class='xrr_quantity xrr_quantity2'>$quantity</div>
									</div>
									";
							}
							echo "</div>";
							echo "<div class='xrr_cost'>".number_format($cost,2)."</div>";
					echo "</div>";//end tplan_table_row */
					echo "<tr class=has_css_div><td>$count</td><td>$date</td><td>$patient</td><td>$patient_no</td><td>$referrer</td><td class=td_div_holder>";
						//get shades used
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select a.name, b.quantity from cadcam_types as a join blocks_stock_out as b on
								b.group_number=:group_number and b.block_id=a.id";
						$placeholders2['group_number']=$row['details']	;	
						$error2="unable to get shades used";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						if($s){}
						$i2=0;
						foreach($s2 as $row2){
							$shade=html($row2['name']);
							$quantity=html($row2['quantity']);
							$i2_class='';
							if($i2==0){$i2_class='no_top_border';}
							$i2++;
							echo "<div class='tplan_table'>
									<div class='tplan_table_row'>
										<div class='xrr_shade $i2_class'>$shade</div>
										<div class='xrr_quantity $i2_class'>$quantity</div>
									</div>
								</div>";
						}
					echo "</td><td>".number_format($cost,2)."</td></tr>";
					$total_cash= $total_cash  + $cost;
				}
			//	echo "</div>"; // for the  table
				echo "<tr class=total_background><td colspan=6>TOTAL FOR CASH CADCAM REFERALS</td><td>".number_format($total_cash,2)."</td></tr>";
				echo "</tbody></table>";
			}
			if($skip_insured) exit;
		}//end for cash
		
		//invoice payments
		if(!$skip_insured and !$exit_flag){
			$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
				c.name as referrer_name,  d.details, d.date_procedure_added ,d.authorised_cost , e.name as insurer,
				f.name as company_covered,d.invoice_number
				from tplan_procedure as d join patient_details_a as a on d.pid=a.pid and a.internal_patient=2 
				join patient_details_b as b on a.pid=b.pid $referrer_criteria
				left join cadcam_referrer as c on c.id=b.referee
				left join insurance_company as e on e.id=a.type
				left join covered_company as f on f.id=a.company_covered
				where date_procedure_added>=:from_date and date_procedure_added<=:to_date
				and pay_type=1 and invoice_id > 0 and authorised_cost > 0";
			$error="Unable to generate insured cadcam referals";
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			$insured_count=$s->rowCount();
			if($s->rowCount() > 0){
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				$count=$total_insured=0;
				$for_user='';
				foreach($s as $row){
					$name=ucfirst(html("$row[patient_name]"));
					if($_POST['doctor'] == 'all') {}
					if($count==0){
						echo "<table class='normal_table ecr1'><caption>INSURED CADCAMs done for external patients between $from_date and $to_date</caption><thead>
							  <tr><th class='xrr_count22'></th><th class=xrr_date22>DATE</th><th class=xrr_pname22>PATIENT</th>
							  <th class=xrr_pno22>PATIENT No.</th>
							  <th class=xrr_insurer22>INSURER</th><th class=xrr_ref22>REFERRER</th>
							  <th class='xrr_xray22 td_div_holder'><div class='tplan_table'>
									<div class='tplan_table_row2'>
										<div class='xrr_shade xrr_shade2'>SHADE</div>
										<div class='xrr_quantity xrr_shade2'>QUANTITY</div>
									</div>
								</div></th>
							  <th class=xrr_inv22>INVOICE<th class=xrr_cost22>COST</th></th></tr></thead><tbody>";
					}
					$count++;
					$patient=ucfirst(html($row['patient_name']));
					$referrer=ucfirst(html($row['referrer_name']));
					//$xray=html("$row[details] $row[teeth]");
					$cost=html($row['authorised_cost']);
					$date=html($row['date_procedure_added']);
					$patient_no=html($row['patient_number']);
					$insurer=html($row['insurer']);
					$company=html($row['company_covered']);
					$invoice_number=html($row['invoice_number']);
					if($company != ''){$insurer="$insurer - $company";}
					echo "<tr class=has_css_div><td>$count</td><td>$date</td><td>$patient</td><td>$patient_no</td><td>$insurer</td><td>$referrer</td>
					<td class=td_div_holder>";
						//get shades used
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2="select a.name, b.quantity from cadcam_types as a join blocks_stock_out as b on
								b.group_number=:group_number and b.block_id=a.id";
						$placeholders2['group_number']=$row['details']	;	
						$error2="unable to get shades used";
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
						if($s){}
						$i2=0;
						foreach($s2 as $row2){
							$shade=html($row2['name']);
							$quantity=html($row2['quantity']);
							$i2_class='';
							if($i2==0){$i2_class='no_top_border';}
							$i2++;
							echo "<div class='tplan_table'>
									<div class='tplan_table_row'>
										<div class='xrr_shade $i2_class'>$shade</div>
										<div class='xrr_quantity $i2_class'>$quantity</div>
									</div>
								</div>";
						}
					echo "</td><td>$invoice_number</td><td>".number_format($cost,2)."</td></tr>";
					$total_insured= $total_insured  + $cost;
				}
				echo "<tr class=total_background><td colspan=8>TOTAL FOR INSURED CADCAM REFERALS</td><td>".number_format($total_insured,2)."</td></tr>";
				if(!$skip_cash){
					echo "<tr class=total_background><td colspan=8>TOTAL FOR ALL CADCAM REFERALS</td><td>".number_format($total_insured + $total_cash,2)."</td></tr>";
				}
				echo "</tbody></table>";
				exit;
			}
		}//end for invoice
	
		if($cash_count==0 and $insured_count==0){
			echo "<div class=grid-100><label class=label>There are no CADCAM referral records for the selected criteria</label></div>";
			echo "<br><br>";
		}	
	
	}
	exit;
}
if(isset($error_class) and $error_class!='' and isset($message) and $message!=''){echo "<div class='grid-100 $error_class'>$message</div>";}
		
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_rcr'] = "$token";  ?>
		<input type="hidden" name="token_rcr"  value="<?php echo $_SESSION['token_rcr']; ?>" />
		<div class=grid-15><label class=label>Select referring doctor</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_referrer order by name";
				$error2="Unable to get cadcam referrers";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-30'><select name=doctor >";
					echo "<option value='all'>All Referrers</opiton>";
					foreach($s2 as $row2){
						$name=ucfirst(html("$row2[name]"));
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
					
				echo "</select></div>"; ?>
		<!-- pay type-->
		<div class=grid-10><label class=label>Select pay type</label></div>
		<div class='grid-15'><select name=pay_type >
			<option value='all'>Insured & Cash</option>
			<option value=insured>Insured</option>
			<option value=cash>Cash</option>
			</select>
		</div>
		<div class=clear></div><br>
		<div class='grid-15'><label for="user" class="label">CADCAM from this date </label></div>
		<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10'><label for="user" class="label">to this date</label></div>
		<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
		
		
		
		<div class='grid-10'><input type=submit  value=Submit /></div>
	</form>

</div>