<?php
/*if(!isset($_SESSION))
{
session_start();
}*/




if(!userIsLoggedIn() or !userHasRole($pdo,92)){exit;}
echo "<div class='grid_12 page_heading'>RE-PRINT  PRESCRIPTIONS </div>";
?>
<div class='grid-container completion_form'>
	
	<div class='feedback hide_element'></div>
	<?php //include  '../../dental_includes/response.php'; 
			//$_SESSION['tab_name']="#self_payments";
			 include '../dental_includes/search_for_patient_no_session.php';
			 //echo "pid2 is $_SESSION[pid2] and pid is $_SESSION[pid]";

			 

if(isset($pid) and $pid!=''){
	//get previous prescriptions
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details, c.first_name, c.middle_name, c.last_name , a.prescription_id , a.prescription_number
	from drugs b, prescriptions a, users c
		where b.id=a.drug_id and c.id=a.created_by and a.pid=:pid order by a.prescription_id desc";
	$error="Unable to check if pre-auth is needed";
	$placeholders[':pid']=$pid_clean;
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	if($s->rowCount()>0){
		
		echo "<div class='grid-100 no_padding dialog_with_tab'></div><table class='normal_table'><caption>Prescription Drugs for $first_name $middle_name $last_name - $patient_number</caption><thead>
		<tr><th class=presc_date>DATE PRESCRIBED</th><th class=presc_number>PRESCRIPTION NUMBER</th>
		<th class=presc_name>PRESCRIPTION</th>
		<th class=presc_doc>PRESCRIBING DOCTOR</th></tr></thead><tbody>";
			$drug='';$prescription_id='';
		foreach($s as $row){
			
			if($prescription_id==''){$prescription_id=html($row['prescription_id']);}
			else{
				//check if it has changed or not so that we print it
				if($prescription_id!=$row['prescription_id']){
					echo "<tr><td>$date</td><td><input type=button class='button_style show_prescription' value=$prescription_number /></td><td>$drug</td><td>$doctor</td></tr>";
					$prescription_id=html($row['prescription_id']);
					$drug='';
				}
				
			}
			if($drug==''){$drug=html("$row[name]  $row[details]");}
			elseif($drug!=''){$drug="$drug <br>".html("$row[name]  $row[details]");}
			$doctor=html("$row[first_name] $row[middle_name] $row[last_name]");
			$date=html($row['when_added']);
			$prescription_number=html("$row[prescription_number]");
			
		}
		echo "<tr><td>$date</td><td><input type=button class='button_style show_prescription' value=$prescription_number /></td><td>$drug</td><td>$doctor</td></tr>";
		echo "</tbody></table>";
	}
	else{echo "<label class=label>This patient does not have any prescription records</label>";}
}
?>
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>