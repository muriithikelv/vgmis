<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,23)){exit;}
echo "<div class='grid_12 page_heading'>TREATMENT PROCDURES</div>";
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='good'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
			/*elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}*/
		}
	


?>
	<div class="grid-100 margin_top">
	<div class='feedback2 hide_element'></div>
	<?php //include  '../inventory_includes/response.php'; ?>
	<input type=button value='Add New Treatment Procedure' class=button_style id=add_new_treatment_procedure />
	<div  id=new_procedure_form_div >
		<div class='feedback hide_element'></div>
		<form action="" class=patient_form method="post" name="new_procedures_form" id="new_procedures_form">
			<?php $token = form_token(); $_SESSION['token_ep2'] = "$token";  ?>
			<input type="hidden" name="token_ep2"  value="<?php echo $_SESSION['token_ep2']; ?>" />
			<div class='grid-55 prefix-5 '><label for="" class="label">PROCEDURE NAME</label></div>
			<div class='grid-10'><label for="" class="label">TYPE</label></div>
			<div class='grid-10 '><label for="" class="label">COST</label></div>
			<div class='grid-20 grid-parent'><label for="" class="label">REQUIRES TOOTH SPECIFICATION<br>
				<div class='grid-50'>YES</div><div class='grid-50'>NO</div>
				</label>
			</div>
			<?php
			$i=1;
			$normal_type=$encrypt->encrypt(html('1'));
			$xray_type=$encrypt->encrypt(html('2'));
			while($i <= 8){
			echo "<div class='grid-100 grid-parent hover-row'>";
			echo "<div class=grid-5><label  class=label>$i</label></div>";
			echo "<div class='grid-55'><input type=text name='procedure_name$i'  /></div>";
			echo "<div class='grid-10'><select name=procedure_type$i >
					<option value='$normal_type'  >Normal</option>
					<option value='$xray_type' >X-Ray</option>
				</select></div>";
			echo "<div class='grid-10'><input type=text name='procedure_cost$i'  /></div>";
			echo "<div class='grid-10'><input type=radio name=tooth_specific$i value=yes /></div>";
			echo "<div class='grid-10'><input type=radio name=tooth_specific$i  value='no' /></div>";
			echo "</div>";
			echo "<div class=clear></div>";
			$i++;
			}
			?>
			<br><input  class=put_right type="submit"  value="Add Treatment Procedure"/>
			<div class=clear></div>
			</form>	
	
	
	</div>
	
	<?php
	//now show current insurance compmanies
	$sql=$error=$s='';$placeholders=array();
	$sql="select * from procedures where id!=1 and  id!=2 and id!=8 and id!=59 order by name";
	$error="Unable to select procedures done";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$count=0;
		echo "
		<br><br><form class=patient_form action='' method=post name='old_procedures_form' id='old_procedures_form'>";?>
			<?php $token = form_token(); $_SESSION['token_ep1'] = "$token";  ?>
		<input type="hidden" name="token_ep1"  value="<?php echo $_SESSION['token_ep1']; ?>" />
		<table class='procedures_table'><caption>Treatment Procedures</caption><thead>
		<tr><th rowspan=2 class=count></th><th rowspan=2 class=proc_border>PROCEDURE NAME</th><th rowspan=2 class=proc_type>TYPE</th>
		<th rowspan=2 class=proc_cost>COST</th><th colspan=2 class=''>REQUIRES TOOTH SPECIFICATION</th>
		<th rowspan=2 class='delete'>UNLIST</th></tr>
		<tr><th class=yes_border>YES</th><th class=no_border>NO</th></tr></thead><tbody>
		<?php 
		foreach($s as $row){
			$count++;
			$procedure=html($row['name']);
			$val=$encrypt->encrypt(html($row['id']));
			$all_teeth=html($row['all_teeth']);
			if($row['cost'] > 0){$cost=html($row['cost']);}
			else{$cost='';}
			$yes=$no='';
			if($all_teeth=="yes"){$yes=" checked ";}
			elseif($all_teeth=="no"){$no=" checked ";}	
			$checked='';
			if($row['listed']==1){$checked=' checked ';}
			$normal_type_selected=$xray_type_selected='';
			if($row['type']==1){$normal_type_selected=" selected ";}
			elseif($row['type']==2){$xray_type_selected=" selected ";}
			echo "<tr><td>$count</td><td><input type=text name=procedure_name$count value='$procedure' /></td>";
			echo "<td><select name=procedure_type$count >
					<option value='$normal_type' $normal_type_selected >Normal</option>
					<option value='$xray_type' $xray_type_selected>X-Ray</option>
				</select></td>
			<td><input type=text name=procedure_cost$count value='$cost' /></td><td><input type=radio name=tooth_specific$count value=yes $yes /></td>";
			echo "<td><input type=radio name=tooth_specific$count  value='no' $no /></td>
					<td><input type=checkbox name=delete_procedure$count value='delete' $checked /></td>";
			echo "<input type=hidden name=ninye$count value='$val' /></tr>";
			
		}
		echo "</tbody></table>";
		$nisiana=$encrypt->encrypt($count);
		echo "<input type=hidden name=nisiana value='$nisiana' />";
		echo "<input class=put_right type=submit  value='Submit Changes' /></form>";
	}
	//else{<span class='center_text'>There are no insured Companies}

?>
</div>
