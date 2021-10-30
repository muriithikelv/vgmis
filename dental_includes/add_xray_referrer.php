<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,27)){exit;}
echo "<div class='grid_12 page_heading'>X-RAY REFERRERS</div>";
$user=$user_name=$var='';



?>
	<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
	<input type=button value='Add New X-ray Referrer' class=button_style id=add_new_xray_referrer />
	<div  id="xray_refeffer_form_div" >
		<div class='feedback '></div>
		<form class='patient_form' action="" method="post" name="" id="">
			<div class='grid-20 alpha'><label for="user" class="label">Technician Name </label></div>
			<div class='grid-30'><input type=text name=ref_name /></div>
			<div class='grid-20'><label for="user" class="label"> Telephone </label></div>
			<div class='grid-30 omega'><input type=text name=telephone_no /></div>
			<div class=clear></div><br>
			<div class='grid-20 alpha'><label for="user" class="label">Email Address </label></div>
			<div class='grid-30 suffix-50 omega'><input type=text name=email_address /></div>
			
			<?php $token = form_token(); $_SESSION['token_xray_ref_1'] = "$token";  ?>
		<input type="hidden" name="token_xray_ref_1"  value="<?php echo $_SESSION['token_xray_ref_1']; ?>" />
			<div class='grid-30 prefix-20 suffix-50'>	<br><input type="submit"  value="Add Referrer"/></div>
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
	$sql="select * from xray_refering_doc order by referrer_name";
	$error="Unable to select xray referrers";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "<br><br><form action='' method='post' name='' id='' class='patient_form'><table class='normal_table'><caption>X-ray Referrers</caption><thead>
		<tr><th class=ref_count></th><th class=ref_name>Referrer's Name</th><th class=ref_tel>Telephone Number</th><th class=ref_email>Email Address</th><th class=ref_del>Listed</th></tr></thead><tbody>";
		foreach($s as $row){
			$count++;
			$checked='';
			if($row['listed'] == 1){$checked="NO";}
			else{$checked="YES";}
			$name=html($row['referrer_name']);
			$tel=html($row['telephone']);
			$email=html($row['email_address']);
			$val=$encrypt->encrypt(html($row['id']));//
			/*echo "<tr><td class=count>$count</td><td><input type=text name=old_ref[] class=input_in_table_cell value='$name' />
			<input type=hidden name=ninye[] value='$val' /></td>
			<td><input type=text name=old_tel[] class=input_in_table_cell value='$tel' /></td>
			<td><input type=text name=old_email[] class=input_in_table_cell value='$email' /></td>
			<td><input type=checkbox name=del[] value='$val' $checked /></td></tr>";*/
			echo "<tr><td class=count>$count</td><td>
			<input type=button  class='wrap_word_in_button button_in_table_cell button_style edit_xray_referer' value='$name'  />
				<input type=hidden name='ninye' value='$val' />
			</td>
			<td>$tel</td><td>$email</td><td>$checked</td></tr>";
		}
		echo "</tbody></table>";
		echo "<br>";
		$token = form_token(); $_SESSION['token_xray_ref_2'] = "$token";  
		echo "<input type=hidden name=token_xray_ref_2  value='$_SESSION[token_xray_ref_2]' /><input type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
<div class=grid-100 id=edit_ins_cover>
	<div id='edit_xray_ref_inner'></div>
</div>
