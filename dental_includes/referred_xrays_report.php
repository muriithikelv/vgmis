<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,83)){exit;}
echo "<div class='grid_12 page_heading'>EXTERNAL PATIENT X-RAYS</div>"; ?>
<div class="grid-100 margin_top ">
<?php   
//show appointments
if( isset($_POST['token_rxr']) and isset($_SESSION['token_rxr']) and $_SESSION['token_rxr']==$_POST['token_rxr']){
	$_SESSION['token_rxr']='';
	$exit_flag=$skip_cash=$skip_insured=false;
	//check if docotr is set
	if(!$exit_flag and !isset($_POST['doctor']) or 	$_POST['doctor']==''){
		$error_class='error_response';
		$message="Please specify the referring doctor to search by. ";
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
				c.referrer_name, d.teeth, d.details, d.date_procedure_added ,d.authorised_cost , e.name, f.receipt_num
				from tplan_procedure as d join patient_details_a as a on d.pid=a.pid and a.internal_patient=1 
				join patient_details_b as b on a.pid=b.pid $referrer_criteria
				join procedures as e on e.id=d.procedure_id 
				left join  xray_refering_doc as c on c.id=b.referee
				left join  payments as f on f.pid=a.pid
				where date_procedure_added>=:from_date and date_procedure_added<=:to_date
				and d.pay_type=2";
			$error="Unable to generate cash xray referals";
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			$cash_count=$s->rowCount();
			$count=$total_cash=0;
			if($s->rowCount() > 0){
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				$count=$total_cash=0;
				$for_user='';
				foreach($s as $row){
					$name=ucfirst(html("$row[patient_name]"));
					if($_POST['doctor'] == 'all') {}
					if($count==0){
						echo "<table class='normal_table'><caption>CASH X-Rays done for external patients between $from_date and $to_date</caption><thead>
							  <tr><th class='xrr_count'></th><th class=xrr_date>DATE</th><th class=xrr_pname>PATIENT</th><th class=xrr_pno>PATIENT No.</th>
							  <th class=xrr_ref>REFERRER</th><th class=xrr_xray>X-RAY</th><th class=xrr_rec_num>RECEIPT No.</th><th class=xrr_cost>COST</th></tr></thead><tbody>";
					}
					$count++;
					$patient=ucfirst(html($row['patient_name']));
					$referrer=ucfirst(html($row['referrer_name']));
					$xray=html("$row[name] $row[details] $row[teeth]");
					$cost=ucfirst(html($row['authorised_cost']));
					$date=ucfirst(html($row['date_procedure_added']));
					$patient_no=ucfirst(html($row['patient_number']));
					$receipt_num=html($row['receipt_num']);
					echo "<tr ><td>$count</td><td>$date</td><td>$patient</td><td>$patient_no</td><td>$referrer</td><td>$xray</td><td>$receipt_num</td><td>".number_format($cost,2)."</td></tr>";
					$total_cash= $total_cash  + $cost;
				}
				echo "<tr class=total_background><td colspan=7>TOTAL FOR CASH X-RAY REFERALS</td><td>".number_format($total_cash,2)."</td></tr>";
				echo "</tbody></table>";
			}
			if($skip_insured) exit;
		}//end for cash
		
		//invoice payments
		if(!$skip_insured and !$exit_flag){
			$sql="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, e.name as insurer,
				f.name as company_covered, 	c.referrer_name, d.teeth, d.details, d.date_procedure_added ,d.authorised_cost , 
				d.invoice_number, g.name as procedure_name
				from tplan_procedure as d join patient_details_a as a on d.pid=a.pid and a.internal_patient=1 
				join patient_details_b as b on a.pid=b.pid $referrer_criteria
				join procedures as g on g.id=d.procedure_id 
				left join insurance_company as e on e.id=a.type
				left join covered_company as f on f.id=a.company_covered
				left join xray_refering_doc as c on c.id=b.referee
				where date_procedure_added>=:from_date and date_procedure_added<=:to_date
				and pay_type=1 and invoice_id > 0 and authorised_cost > 0";
			$error="Unable to generate insured xray referals";
			$placeholders[':from_date']=$_POST['from_date'];
			$placeholders[':to_date']=$_POST['to_date'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			$insured_count=$s->rowCount();
			$count=$total_insured=0;
			if($s->rowCount() > 0){
				$from_date=html($_POST['from_date']);
				$to_date=html($_POST['to_date']);
				$count=$total_insured=0;
				$for_user='';
				foreach($s as $row){
					$name=ucfirst(html("$row[patient_name]"));
					if($_POST['doctor'] == 'all') {}
					if($count==0){
						echo "<table class='normal_table'><caption>INSURED X-Rays done for external patients between $from_date and $to_date</caption><thead>
							  <tr><th class='xrr_count2'></th><th class=xrr_date2>DATE</th><th class=xrr_pname2>PATIENT</th><th class=xrr_pno2>PATIENT No.</th>
							  <th class=xrr_insurer2>INSURER</th><th class=xrr_ref2>REFERRER</th><th class=xrr_xray2>X-RAY</th>
							  <th class=xrr_inv2>INVOICE<th class=xrr_cost2>COST</th></th></tr></thead><tbody>";
					}
					$count++;
					$patient=ucfirst(html($row['patient_name']));
					$referrer=ucfirst(html($row['referrer_name']));
					$xray=html("$row[procedure_name] $row[details] $row[teeth]");
					$cost=html($row['authorised_cost']);
					$date=html($row['date_procedure_added']);
					$patient_no=html($row['patient_number']);
					$insurer=html($row['insurer']);
					$company=html($row['company_covered']);
					$invoice_number=html($row['invoice_number']);
					if($company != ''){$insurer="$insurer - $company";}
					echo "<tr ><td>$count</td><td>$date</td><td>$patient</td><td>$patient_no</td><td>$insurer</td><td>$referrer</td><td>$xray</td>
						<td>$invoice_number</td><td>".number_format($cost,2)."</td>
							</tr>";
					$total_insured= $total_insured  + $cost;
				}
				echo "<tr class=total_background><td colspan=8>TOTAL FOR INSURED X-RAY REFERALS</td><td>".number_format($total_insured,2)."</td></tr>";
				if(!$skip_cash  ){
					echo "<tr class=total_background><td colspan=8>TOTAL FOR ALL X-RAY REFERALS</td><td>".number_format($total_insured + $total_cash,2)."</td></tr>";
				}
				echo "</tbody></table>";
				exit;
			}
		}//end for invoice
	
		if($cash_count==0 and $insured_count==0){
			echo "<div class=grid-100><label class=label>There are no X-Ray referral records for the selected criteria</label></div>";
			echo "<br><br>";
		}	
	
	}
	exit;
}
if(isset($error_class) and $error_class!='' and isset($message) and $message!=''){echo "<div class='grid-100 $error_class'>$message</div>";}
		
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_rxr'] = "$token";  ?>
		<input type="hidden" name="token_rxr"  value="<?php echo $_SESSION['token_rxr']; ?>" />
		<div class=grid-15><label class=label>Select referring doctor</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, referrer_name from xray_refering_doc order by referrer_name";
				$error2="Unable to get x-ray referrers";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-30'><select name=doctor >";
					echo "<option value='all'>All Doctors</opiton>";
					foreach($s2 as $row2){
						$name=ucfirst(html("$row2[referrer_name]"));
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
		<div class='grid-15'><label for="user" class="label">X-Rays from this date </label></div>
		<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10'><label for="user" class="label">to this date</label></div>
		<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
		
		
		
		<div class='grid-10'><input type=submit  value=Submit /></div>
	</form>

</div>