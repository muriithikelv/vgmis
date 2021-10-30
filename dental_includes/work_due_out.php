<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,30)){exit;}
echo "<div class='grid_12 page_heading'>LAB WORK DUE OUT</div>";
?>
<div class=grid-container>
<?php
//dispatch labs labs
if(isset($_POST['token_work_due_out2']) and $_POST['token_work_due_out2']!='' 
	and $_POST['token_work_due_out2']==$_SESSION['token_work_due_out2'] and isset($_POST['dispatch']) and $_POST['dispatch']!=''){
	$_SESSION['token_work_due_out2']='';
	$lab=$_POST['dispatch'];
	$n=count($lab);
	$i=0;
	try{
			$pdo->beginTransaction();
			while($i < $n){
				$sql=$error=$s='';$placeholders=array();
				$sql = "update labs set date_picked=now() where lab_id=:lab_id";
				$error = "Unable to dispatch lab";
				$placeholders[':lab_id']=$encrypt->decrypt($lab[$i]);		
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				$i++;
			}
			$tx_result = $pdo->commit();
			if($tx_result){echo "<div class='grid-100 feedback success_response'>Labs Dispatched</div>";}
			elseif(!$tx_result){echo "<div class='grid-100 feedback error_response'>Unable to dispatch labs</div>";}
		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		echo "<div class='grid-100 feedback error_response'>Unable to dispatch labs</div>";
		}
}
?>

<?php 
	//get work pending collection by technixian
	if(isset($_POST['technician']) and $_POST['technician']!='' and isset($_POST['token_work_due_out1']) and $_POST['token_work_due_out1']!='' 
	and $_POST['token_work_due_out1']==$_SESSION['token_work_due_out1']){ ?>
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_work_due_out1'] = "$token";  ?>
	<input type="hidden" name="token_work_due_out1"  value="<?php echo $_SESSION['token_work_due_out1']; ?>" />
		
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
	<div class='grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>	
	<?php
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

		$sql="select a.when_added, a.lab_id, a.date_required, a.amount, b.first_name, b.middle_name, b.last_name, c.first_name, c.middle_name, 
		c.last_name, d.technician_name from labs a, patient_details_a b, users c, lab_technicians d where d.id=a.technician and a.pid=b.pid 
		and a.doc_id=c.id  $criteria and date_picked is null  order by a.lab_id";
		$error="Unable to get work due out";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$count=0;
			echo "<br><br><form action='' method='post' name='' id='' class=''>
			<table class='normal_table'><caption>Lab work due out for $tech_name</caption><thead>
			<tr><th class=lab_out_count></th><th class=lab_out_id>LAB No.</th><th class=lab_out_patient>PATIENT NAME</th><th class=lab_out_doctor>REQUESTING DOCTOR</th>
			<th class=lab_out_date>REQUESTED ON</th><th class=lab_out_technician>TECHNICIAN</th><th class=lab_out_cost>COST</th>
			<th class=lab_out_date>DATE REQUIRED</th><th class=lab_out_dispatch>DISPATCH</th></tr></thead><tbody>";
			foreach($s as $row){
				$count++;
				$when_added=html("$row[when_added]");
				$patient=html("$row[4] $row[5] $row[6]");
				$doctor=html("$row[7] $row[8] $row[9]");
				$technician=html("$row[technician_name]");
				$cost=number_format(html("$row[amount]"),2);
				$date_required=html("$row[date_required]");
				$lab_id=html("$row[lab_id]");
				$val=$encrypt->encrypt($lab_id);//
				echo "<tr><td class=count>$count</td><td><input type=button class='button_in_table_cell button_style view_lab' value=$lab_id  /></td><td>$patient</td><td>$doctor</td><td>$when_added</td>
				<td>$technician</td><td>$cost</td><td>$date_required</td><td><input type=checkbox name=dispatch[] value='$val' /></td></tr>";

			}
			echo "</tbody></table>";
			echo "<br>";
			$token = form_token(); $_SESSION['token_work_due_out2'] = "$token";  
			echo "<input type=hidden name=token_work_due_out2  value='$_SESSION[token_work_due_out2]' /><input type=submit class='put_right' value='Submit' /></form>";
		}
		else{echo "<label  class=label>There is no work due out for the selected criteria</label>";}
		echo "<div id=view_lab></div>";
		exit;
	}	
	?>
			

			
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_work_due_out1'] = "$token";  ?>
	<input type="hidden" name="token_work_due_out1"  value="<?php echo $_SESSION['token_work_due_out1']; ?>" />
		
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
	<div class='grid-25'>	<input type="submit"  value="Submit"/></form></div>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>