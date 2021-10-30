<?php
/*if(!isset($_SESSION))
{
session_start();
}*/

if(!userIsLoggedIn() or !userHasRole($pdo,107)){exit;}
echo "<div class='grid_12 page_heading'>PATIENT NOTES</div>";


?>
<div class='grid-container completion_form'>
<div class='feedback hide_element'></div>	
	<?php //include  '../../dental_includes/response.php'; 
			//$_SESSION['tab_name']="#self_payments";
			 include '../dental_includes/search_for_patient_no_session.php';
			 //echo "pid2 is $_SESSION[pid2] and pid is $_SESSION[pid]";

			 

if(isset($patient_number) and $patient_number!=''){ ?>
	<form action="patient_notes" method="POST"  name="" id="" class="patient_form">
				<?php $token = form_token(); $_SESSION['token_pn1'] = "$token"; 
						$patient_number_enc=$encrypt->encrypt("$patient_number");
						echo "<input type='hidden' name='token_ninye' id='token_ninye' value='$patient_number_enc' />";
						
						$names=$encrypt->encrypt("$first_name $middle_name $last_name");
						echo "<input type='hidden' name='token_ninye2' id='token_ninye2' value='$names' />";
					?>
				<input type="hidden" name="token_pn1"  value="<?php echo $_SESSION['token_pn1']; ?>" />
			
	<div class='grid-15 '><label for="" class="label">Select Patient Title</label></div>
	<div class='grid-20'><select   name=patient_title>
						<option></option>
						<option value='Mr'>Mr</option>
						<option value='Mrs'>Mrs</option>
						<option value='Miss'>Miss</option>
						</select>
	</div>
	<div class=clear></div><br>
	<!-- select note type -->
	<div class='grid-15 '><label for="" class="label">Select Note Type</label></div>
	<div class='grid-20'><select  class='note_type'  name=review_type>
						<option></option>
						<option value='review_date'>Review Date</option>
						<option value='sick_off'>Sick Off</option>
						</select>
	</div>
				<div class='grid-10 date_criteria '><label for="" class="label ">From this date</label></div>
				<div class='grid-10 date_criteria '><input type=text name=from_date1 class=date_picker_no_past /></div>
				<div class='grid-10 date_criteria'><label for="" class="label">To this date</label></div>
				<div class='grid-10 date_criteria'><input type=text name=to_date class=date_picker_no_past /></div>
				<div class='grid-5 date_criteria'><input type=submit value=Submit /></div>
				
				<div class='grid-10  single_date'><label for="" class="label">On this date</label></div>
				<div class='grid-10  single_date'><input type=text name=from_date class=date_picker_no_past /></div>
				<div class='grid-5 single_date'><input type=submit value=Submit /></div>
	</form>					
	<div class=clear></div>
	<br>

	
<?php
	}
?>
</div>

