<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,73)){exit;}
echo "<div class='grid_12 page_heading'>SWAPPED PATIENTS</div>";

if(isset($_POST['spr_token'])  and  isset($_SESSION['spr_token']) and $_POST['spr_token']==$_SESSION['spr_token']){
			$_SESSION['spr_token']='';
			$sql=$error=$s='';$placeholders=array();	
			$sql="select a.first_name, a.middle_name, a.last_name,b.old_patient_number, b.new_patient_number, c.first_name, c.middle_name,
					c.last_name, b.when_added from patient_details_a as a join swapped_patients as b on a.pid=b.new_pid 
					join users as c on c.id=b.changed_by 
					where b.when_added>=:from_date and b.when_added<=:to_date";
			
				$placeholders[':from_date']=$_POST['from_date'];
				$placeholders[':to_date']=$_POST['to_date'];
				$error="Error: Unable to get patient details a";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount()>0){
					$from_date=html($_POST['from_date']);
					$to_date=html($_POST['to_date']);
					echo "<table class='normal_table'><caption>Swapped patients between $from_date and $to_date</caption><thead>
					<tr><th class='spr_count'></th><th class='spr_name'>PATIENT NAME</th><th class='spr_pnum_old'>OLD PATIENT NUMBER</th>
					<th class='spr_pnum_new'>NEW PATIENT NUMBER</th>
					<th class='spr_changer'>CHANGED BY</th><th class='spr_date'>DATE CHANGED</th></tr></thead><tbody>";	
					$i=0;
					foreach($s as $row){
						$i++;
						$name=ucfirst(html("$row[0] $row[1] $row[2] "));
						$old_pnum=html($row['old_patient_number']);
						$new_pnum=html($row['new_patient_number']);
						$changer=ucfirst(html("$row[5] $row[6] $row[7] "));
						$date=html($row['when_added']);
						echo "<tr><td>$i</td><td>$name</td><td>$old_pnum</td><td>$new_pnum</td><td>$changer</td><td>$date</td></tr>";
					}
					echo "</tbody></table>";
					exit;
				}
				else{ echo "<div class='grid-100 label'>There are no swapped patients for the selected period</div>";}
						
}
?>
<div class='grid-container '>
	<div class='feedback hide_element'></div>
	<form class='' action='' method="POST"  name="" id="">
		<div class='grid-25'>
			<?php $token = form_token(); $_SESSION['spr_token'] = "$token";  ?>
			<input type="hidden" name="spr_token"  value="<?php echo $_SESSION['spr_token']; ?>" />
			<label for="" class="label">Patients swapped between this date</label>
		</div>
		<div class='grid-10'><input type=text class=date_picker name=from_date /></div>
		<div class='grid-10'><label for="" class="label">And this date</label></div>
		<div class='grid-10'><input type=text class=date_picker name=to_date /></div>
		<div class='grid-10'><input type=submit value="Submit"  /></div>
		
	</form>
<div class=clear></div><br>


	
			
</div>

<div  class="show_loader prefix-30 grid-40 suffix-30">
Loading <img src="dental_jquery/ajax-loader.gif" />
</div>