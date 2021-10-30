<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,33)){exit;}
echo "<div class='grid_12 page_heading'>LAB PAYMENTS</div>";
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


	//get finished work pending payment by technixian
	if(isset($_POST['technician']) and $_POST['technician']!='' and isset($_POST['token_lab_pay1']) and 	$_POST['token_lab_pay1']!='' 
	and $_POST['token_lab_pay1']==$_SESSION['token_lab_pay1']){ ?>

	<br>
	<?php
		$criteria_date='';
		$sql=$error1=$s='';$placeholders=array();
		if($_POST['technician']=='all'){
			$criteria='';
			$tech_name="all technicians";
		}
		elseif($_POST['technician']!='all'){
			$tech_id=$encrypt->decrypt($_POST['technician']);				
			$criteria=' and a.technician=:tech_id ';
			$placeholders[':tech_id']=$tech_id;	
			//get technician name
			//$sql=$error=$s='';$placeholders=array();
			$sql="select technician_name from lab_technicians where id=:tech_id";
			$error="Unable to get technician name";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			foreach($s as $row){$tech_name=html($row['technician_name']);}
			$sql=$error=$s='';
		}
		/*if($_POST['date_from']!='' and $_POST['date_to']!=''){
			$criteria_date=' and a.date_required >=:date_from and a.date_required <=:date_to ';
			$placeholders[':date_from']=$_POST['date_from'];	
			$placeholders[':date_to']=$_POST['date_to'];	
			
		}*/
		$sql="select a.when_added, a.lab_id, a.date_returned, a.amount, b.first_name, b.middle_name, b.last_name, c.first_name, c.middle_name, 
		c.last_name, d.technician_name from labs a, patient_details_a b, users c, lab_technicians d where d.id=a.technician and a.pid=b.pid and 
		a.doc_id=c.id  $criteria and date_returned is not null and a.amount > 0 and a.when_added >= '2013-01-01' order by a.lab_id";
		$error="Unable to get returned work";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$labs_array=$_SESSION['balance_lab']=array();
			foreach($s as $row){
				$continue_flag=false;
				//check if the lab is fully paid or not
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2 = "select sum(amount_paid) from lab_payments where lab_id=:lab_id";
				$error2 = "Unable to get lab payments";
				$placeholders2[':lab_id']=$row['lab_id'];
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				if($s2->rowCount() > 0){
					foreach($s2 as $row2){
						if($row2[0] == $row['amount']) {
							$continue_flag=true;}
						else{
						 $balance=$row['amount'] - $row2[0];
						 $balance=html($balance);
						}						
					}	
				}
				else{
					$balance=html($row['amount']);
				}
				if($continue_flag) continue;
				
				$when_added=html("$row[when_added]");
				$patient=html("$row[4] $row[5] $row[6]");
				$doctor=html("$row[7] $row[8] $row[9]");
				$technician=html("$row[technician_name]");
				$cost=html("$row[amount]");
				$date_returned=html("$row[date_returned]");
				$lab_id=html("$row[lab_id]");
				$val=$encrypt->encrypt($lab_id);
				//$_SESSION['balance_lab'][]=array("'$lab_id'"=>"$balance");
				$_SESSION['balance_lab'][$lab_id]=$balance;
				
				$labs_array[]=array('when_added'=>"$when_added",  'patient'=>"$patient", 'doctor'=>"$doctor", 'technician'=>"$technician", 'cost'=>"$cost",
								'date_returned'=>"$date_returned", 'lab_id'=>"$lab_id", 'val'=>"$val", 'balance'=>"$balance");
			}
			//now output the labs to be paid
			if(count($labs_array) > 0){ ?>
			<form action='' method='post' name='' id='' class='patient_form'>
				<div class='grid-15'><label for="" class="label">Total value of payment</label></div>
	<div class='grid-15'><input type=text name=total_amount  /></div>
	<div class='grid-15'><label for="" class="label">Receipt number</label></div>
	<div class='grid-15'><input type=text name=receipt_number   /></div>
	<div class=clear></div><br><?php
				$count=0;
				echo "<br><br>
				<table class='normal_table'><caption>Payments for returned lab work for $tech_name</caption><thead>
				<tr><th class=lab_in_count></th><th class=lab_in_id>LAB No.</th><th class=lab_in_patient>PATIENT NAME</th><th class=lab_in_doctor>REQUESTING DOCTOR</th>
				<th class=lab_in_technician>TECHNICIAN</th><th class=lab_in_date>REQUESTED<br> ON</th><th class=lab_in_date>DATE <br>RETURNED</th>
				<th class=lab_in_cost>COST</th><th class=lab_in_tray>BALANCE</th><th class=lab_in_finished>AMOUNT<BR>PAID</th>
				</tr></thead><tbody>";	
				$i=0;
				$n=count($labs_array);
				foreach($labs_array as $unpaid_lab_array){
					$count++;
					echo "<tr><td class=count>$count</td><td><input type=button class='button_in_table_cell button_style view_lab' value=$unpaid_lab_array[lab_id]  /></td>
					<td>$unpaid_lab_array[patient]</td><td>$unpaid_lab_array[doctor]</td><td>$unpaid_lab_array[technician]</td>
					<td>$unpaid_lab_array[when_added]</td><td>$unpaid_lab_array[date_returned]</td><td>".number_format($unpaid_lab_array['cost'],2)."</td><td>";
					//check to see if balance is full or partiall and show a link
					if($unpaid_lab_array['cost'] > $unpaid_lab_array['balance'] ){
						echo "<a href='?$unpaid_lab_array[val]' class='balance_payment link_style'>".number_format($unpaid_lab_array['balance'],2)."</a>";
					}
					else{echo number_format($unpaid_lab_array['balance'],2);}
					echo "</td><td><input type=text name=lab_paymnet[] /><input type=hidden name=ninye[] value=$unpaid_lab_array[val] /> </td></tr>";
					$i++;
				}
				echo "</tbody></table>";
				echo "<br>";
				$token = form_token(); $_SESSION['token_lab_pay2'] = "$token";  
				echo "<input type=hidden name=token_lab_pay2  value='$_SESSION[token_lab_pay2]' /><input type=submit class='put_right' value='Submit' /></form>";
			
			}
			else{echo "<label  class=label>There is no unpaid lab work for the selected criteria</label>";}
			}
		else{echo "<label  class=label>There is no unpaid lab work for the selected criteria</label>";}
		echo "<div id=view_lab></div>";
		exit;
	}	
	?>
			

			
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_lab_pay1'] = "$token";  ?>
	<input type="hidden" name="token_lab_pay1"  value="<?php echo $_SESSION['token_lab_pay1']; ?>" />
		
	<label for="" class="label">Select Technician</label></div>
	<div class='grid-25'><select class='input_in_table_cell add_user_action' name=technician><option></option>
			<option value='all'>All Technicians</option>
			<?php
				$sql=$error=$s='';$placeholders=array();
				$sql = "select id,technician_name from lab_technicians order by technician_name";
				$error = "Unable to list technicians";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				foreach($s as $row){
					$name=html("$row[technician_name]" );
					$val=$encrypt->encrypt(html($row['id']));							
					echo "<option value='$val'>$name</option>";
				}
			
			?>	
						</select></div>
						
	<!--<div class='grid-15'><label for="" class="label">(optional)From this date</label></div>
	<div class='grid-15'><input type=text name=date_from class='date_picker' /></div>
	<div class='grid-10'><label for="" class="label">To this date</label></div>
	<div class='grid-15'><input type=text name=date_to  class='date_picker' /></div>
	<div class=clear></div><br>-->
	<div class=' grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>