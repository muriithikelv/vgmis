<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,120)){exit;}
echo "<div class='grid_12 page_heading'>TREATMENT PLAN VISITS REPORT</div>";
?>
<div class=grid-container>
<div class='grid-100 div_shower44'></div> 
<div class='grid-container cash_balance_content'>

<?php 

//approve tplans with more than 5 visits
if(isset($_POST['approved'])){
	$i=0;
	$n=count($_POST['approved']);
	$approved=$_POST['approved'];
	while($i < $n){
		$tplan_visit_id=$encrypt->decrypt($approved[$i]);
		$sql2=$error2=$s2='';$placeholders2=array();	
		$sql2="update tplan_visits set cleared_by_admin=0 where id=:id";
		$error2="Error: Unable to pt details from uniq ";
		$placeholders2[':id']=$tplan_visit_id;
		$s2 = 	insert_sql($sql2, $placeholders2, $error2, $pdo);
		$i++;
	}
	if($s2){echo "<div class='grid-100 success_response'>Changes Saved</div>";}
}

//get results
if(isset($_POST['report_type']) and 	($_POST['report_type']=='exceeding_5' or $_POST['report_type']=='unfinished')){
	//exceeding 5
	if($_POST['report_type']=='exceeding_5'){
		$sql2=$error2=$s2='';$placeholders2=array();	
		$sql2="SELECT a.first_name, a.middle_name, a.last_name, b.name AS ptype, c.name AS corporate, d.id, d.visits_planned, d.visits_remaining, d.tplan_id, e.first_name as doc_fname, e.middle_name as doc_mname, e.last_name as doc_lname,f.when_added
			FROM tplan_visits d
			JOIN tplan_id_generator f ON d.tplan_id = f.tplan_id
			JOIN users e ON e.id = f.created_by
			JOIN patient_details_a a ON d.pid = a.pid
			LEFT JOIN insurance_company b ON a.type = b.id
			LEFT JOIN covered_company c ON a.company_covered = c.id
			WHERE d.cleared_by_admin =1";
		$error2="Error: Unable to pt details from uniq ";
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		if($s2->rowCount() > 0){
			echo "<form class='' action=''  method='POST' enctype='' name='' id=''><table class=normal_table><caption>Unapproved treatment plans with more than 5 planned visits</caption><thead><tr><th>Date</th><th>Doctor</th><th>Tplan ID</th><th>PATIENT NAME</th><th>PATIENT TYPE</th><th>PLANNED<br>VISITS</th><th>REMAINING<br>VISITS</th><th>APPROVE</th></tr></thead><tbody>";
			foreach($s2 as $row2){
				$date=html("$row2[when_added]");
				$doctor=ucfirst(html("$row2[doc_fname] $row2[doc_mname] $row2[doc_lname]"));
				$tplan_id=html("$row2[tplan_id]");
				$pt_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
				$pt_type=html("$row2[ptype] - $row2[corporate]");
				$tplan_visit_id=$encrypt->encrypt($row2['id']);
				$visits_planned=html("$row2[visits_planned]");
				$visits_remaining=html("$row2[visits_remaining]");
				
				echo "<tr><td>$date</td><td>$doctor</td><td><a class='link_color tplan_history' href=''>$tplan_id</a></td><td>$pt_name</td><td>$pt_type</td><td>$visits_planned</td><td>$visits_remaining</td><td><input type=checkbox name=approved[] value='$tplan_visit_id' /></td></tr>";
			}
			echo "</table>";
			echo "<div class='prefix-90 grid-10'><input type=submit value=Submit /></div>";
			exit;
		}
		else{echo "<div class='grid-100 error_response'>There are no unapproved treatments plans with more than 5 planned visits</div>";}
	}
	
	//visits finished but tplan not done
	if($_POST['report_type']=='unfinished'){
		$sql2=$error2=$s2='';$placeholders2=array();	
		$sql2="SELECT a.first_name, a.middle_name, a.last_name, b.name AS ptype, c.name AS corporate, d.id, d.visits_planned, d.visits_remaining, d.tplan_id, e.first_name as doc_fname, e.middle_name as doc_mname, e.last_name as doc_lname,f.when_added
			FROM tplan_visits d
			JOIN tplan_id_generator f ON d.tplan_id = f.tplan_id
			JOIN users e ON e.id = f.created_by
			JOIN patient_details_a a ON d.pid = a.pid
			LEFT JOIN insurance_company b ON a.type = b.id
			LEFT JOIN covered_company c ON a.company_covered = c.id
			WHERE d.cleared_by_admin =1";
		$error2="Error: Unable to pt details from uniq ";
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		if($s2->rowCount() > 0){
			echo "<form class='' action=''  method='POST' enctype='' name='' id=''><table class=normal_table><caption>Unapproved treatment plans with more than 5 planned visits</caption><thead><tr><th>Date</th><th>Doctor</th><th>Tplan ID</th><th>PATIENT NAME</th><th>PATIENT TYPE</th><th>PLANNED<br>VISITS</th><th>REMAINING<br>VISITS</th><th>APPROVE</th></tr></thead><tbody>";
			foreach($s2 as $row2){
				$date=html("$row2[when_added]");
				$doctor=ucfirst(html("$row2[doc_fname] $row2[doc_mname] $row2[doc_lname]"));
				$tplan_id=html("$row2[tplan_id]");
				$pt_name=ucfirst(html("$row2[first_name] $row2[middle_name] $row2[last_name]"));
				$pt_type=html("$row2[ptype] - $row2[corporate]");
				$tplan_visit_id=$encrypt->encrypt($row2['id']);
				$visits_planned=html("$row2[visits_planned]");
				$visits_remaining=html("$row2[visits_remaining]");
				
				echo "<tr><td>$date</td><td>$doctor</td><td><a class='link_color treatment_history' href=''>$tplan_id</a></td><td>$pt_name</td><td>$pt_type</td><td>$visits_planned</td><td>$visits_remaining</td><td><input type=checkbox name=approved[] value='$tplan_visit_id' /></td></tr>";
			}
			echo "</table>";
			echo "<div class='prefix-90 grid-10'><input type=submit value=Submit /></div>";
			exit;
		}
		else{echo "<div class='grid-100 error_response'>There are no unapproved treatments plans with more than 5 planned visits</div>";}
	}
			
	
			
}
if(isset($result_class) and isset($result_message)){echo "<div class='$result_class'>$result_message</div>";}
	?>
			
			
	<form class='' action=""  method="POST" enctype="" name="" id="">

				<div class=clear></div><br>
				<div class='grid-15'><label for="" class="label">Select Report Type</label></div>
				<div class='grid-25'>
					<select  name=report_type class=''>
						<option value="exceeding_5">Tplan exceeding 5 planned visits</option>
						<!--<option value="greater_than">Tplan not finished after planned  visits</option>-->
					</select>
				</div>
				
				
				<div class='prefix-15 grid-10'>	<input type="submit"  value="Submit"/></div>

	</form>					
	<div class=clear></div>
	<br>
	
<div class=clear></div>
	

</div>