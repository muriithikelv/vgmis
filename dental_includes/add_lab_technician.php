<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,26)){exit;}
echo "<div class='grid_12 page_heading'>LAB TECHNICIANS</div>";
$user=$user_name=$var='';



?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New Lab Technician' class=button_style id=add_new_lab_technician />
	<div  id="lab_technician_form_div" >
		<div class='feedback '></div>
		<form class='patient_form' action="" method="post" name="" id="">
			<div class='grid-20 alpha'><label for="user" class="label">Technician Name </label></div>
			<div class='grid-30'><input type=text name=tech_name /></div>
			<div class='grid-20'><label for="user" class="label"> Telephone </label></div>
			<div class='grid-30 omega'><input type=text name=telephone_no /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="user" class="label">Email Address </label></div>
			<div class='grid-30 suffix-50 omega'><input type=text name=email_address /></div>
			
			<?php $token = form_token(); $_SESSION['token_technician_1'] = "$token";  ?>
		<input type="hidden" name="token_technician_1"  value="<?php echo $_SESSION['token_technician_1']; ?>" />
			<div class='grid-30 prefix-20 suffix-50'>	<br><input type="submit"  value="Add Technician"/></div>
			<div class=clear></div>
			</form>
	</div>		
		
	
	<?php if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		}
	
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from lab_technicians order by technician_name";
	$error="Unable to select lab technicians";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id='' class='patient_form'><table class='normal_table'><caption>Lab Technicians</caption><thead>
		<tr><th class=ref_count></th><th class=ref_name>Technician Name</th><th class=ref_tel>Telephone Number</th><th class=ref_email>Email Address</th><th class=ref_del>Unlist</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$checked='';
			if($row['listed'] == 1){$checked=" checked ";}
			$name=html($row['technician_name']);
			$tel=html($row['telephone']);
			$email=html($row['email_address']);
			$val=$encrypt->encrypt(html($row['id']));//
			echo "<tr><td class=count>$count</td><td><input type=text name=old_tech[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td>
			<td><input type=text name=old_tel[] class=input_in_table_cell value='$tel' /></td>
			<td><input type=text name=old_email[] class=input_in_table_cell value='$email' /></td>
			<td><input type=checkbox name=del[] value='$val' $checked  /></td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_technician_2'] = "$token";  
		echo "<input type=hidden name=token_technician_2  value='$_SESSION[token_technician_2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
