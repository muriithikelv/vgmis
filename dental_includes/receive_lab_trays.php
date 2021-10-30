<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,35)){exit;}
echo "<div class='grid_12 page_heading'>RECEIVE LAB TRAYS</div>";
?>
<div class=grid-container>
<?php 
//receive labs
if(isset($_POST['token_trays_due_in2']) and $_POST['token_trays_due_in2']!='' and $_POST['token_trays_due_in2']==$_SESSION['token_trays_due_in2'] ){
	$_SESSION['token_trays_due_in2']='';
	try{
			$pdo->beginTransaction();
			// receive trays
			$tray=$_POST['trays'];
			$n=count($tray);
			$i=0;			
			while($i < $n){
				$sql=$error=$s='';$placeholders=array();
				$sql = "update lab_trays set date_returned=now() where id=:id";
				$error = "Unable to receive lab trays";
				$placeholders[':id']=$encrypt->decrypt($tray[$i]);		
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				$i++;
			}			
			
			$tx_result = $pdo->commit();
			if($tx_result){echo "<div class='grid-100 feedback success_response'>Trays Received</div>";}
			elseif(!$tx_result){echo "<div class='grid-100 feedback error_response'>Unable to receive trays</div>";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		echo "<div class='grid-100 feedback error_response'>Unable to receive trays</div>";
		}
}


	//get work trays with technician 
	if(isset($_POST['technician']) and $_POST['technician']!='' and isset($_POST['token_trays_due_in1']) and $_POST['token_trays_due_in1']!='' 
	and $_POST['token_trays_due_in1']==$_SESSION['token_trays_due_in1']){ ?>
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_trays_due_in1'] = "$token";  ?>
	<input type="hidden" name="token_trays_due_in1"  value="<?php echo $_SESSION['token_trays_due_in1']; ?>" />
		
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
						<div class=clear></div><br>

	<div class='prefix-15 grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
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
			
		}*///a.date_returned is not null and
		$sql="select a.when_added, a.lab_id, a.date_required, a.amount, b.first_name, b.middle_name, b.last_name, c.first_name, c.middle_name, 
		c.last_name, d.technician_name, a.date_returned from labs a, patient_details_a b, users c, lab_technicians d, lab_trays e where d.id=a.technician and a.pid=b.pid and 
		a.doc_id=c.id  $criteria  and a.lab_id=e.lab_id and a.date_returned is not null and  e.date_returned is null  group by a.lab_id order by a.lab_id ";
		$error="Unable to get work due out";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$count=0;
			echo "<br><br><form action='' method='post' name='' id='' class=''>
			<table class='normal_table'><caption>Trays with $tech_name for finished work</caption><thead>
			<tr><th class=lab_in_count></th><th class=lab_in_id>LAB No.</th><th class=lab_in_patient>PATIENT NAME</th><th class=lab_in_doctor>REQUESTING DOCTOR</th>
			<th class=lab_in_date>REQUESTED<br> ON</th><th class=lab_in_technician>TECHNICIAN</th><th class=lab_in_cost>COST</th>
			<th class=lab_in_date>DATE <br>REQUIRED</th><th class=lab_in_finished>DATE RETURNED</th><th class=lab_in_tray>TRAYS RETURNED</th>
			</tr></thead><tbody>";
			foreach($s as $row){
				$count++;
				$when_added=html("$row[when_added]");
				$patient=html("$row[4] $row[5] $row[6]");
				$doctor=html("$row[7] $row[8] $row[9]");
				$technician=html("$row[technician_name]");
				$cost=number_format(html("$row[amount]"),2);
				$date_required=html("$row[date_required]");
				$date_returned=html("$row[date_returned]");
				$lab_id=html("$row[lab_id]");
				$val=$encrypt->encrypt($lab_id);//
				echo "<tr><td class=count>$count</td><td><input type=button class='button_in_table_cell button_style view_lab' value=$lab_id  /></td><td>$patient</td><td>$doctor</td><td>$when_added</td>
				<td>$technician</td><td>$cost</td><td>$date_required</td><td>$date_returned</td><td>";
				//get trays if nay
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2 = "select id,tray_number,date_returned from lab_trays where lab_id=:lab_id";
				$error2 = "Unable to list of trays";
				$placeholders2[':lab_id']=$lab_id;	
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
				if($s2->rowCount()>0){
					echo "<table class='normal_table'><thead><tr><th class=tray_no>TRAY<br>No.</th>
					<th class=tray_date>RETURNED</th></tr></thead><tbody>";
					foreach($s2 as $row2){
						$tray_num=html("$row2[tray_number]" );
						$val2=$encrypt->encrypt(html($row2['id']));				
						if($row2['date_returned']!=''){$returned=html("$row2[date_returned]" );}
						
						elseif($row2['date_returned']==''){$returned="<input type=checkbox name=trays[] value='$val2' />" ;}
									
						echo "<tr><td>$tray_num</td><td>$returned</td></tr>";
					}
					echo "</tbody></table>";
				}
				echo "</td></tr>";

			}
			echo "</tbody></table>";
			echo "<br>";
			$token = form_token(); $_SESSION['token_trays_due_in2'] = "$token";  
			echo "<input type=hidden name=token_trays_due_in2  value='$_SESSION[token_trays_due_in2]' /><input type=submit class='put_right' value='Submit' /></form>";
		}
		else{echo "<label  class=label>There is no finished labs with unreceived trays for the selected criteria</label>";}
		echo "<div id=view_lab></div>";
		exit;
	}	
	?>
			

			
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_trays_due_in1'] = "$token";  ?>
	<input type="hidden" name="token_trays_due_in1"  value="<?php echo $_SESSION['token_trays_due_in1']; ?>" />
		
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
						<div class=clear></div><br>

	<div class='prefix-15 grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>