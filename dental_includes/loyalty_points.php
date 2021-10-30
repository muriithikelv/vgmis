<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,42)){exit;}
echo "<div class='grid_12 page_heading'>LOYALTY POINTS</div>"; ?>
<div class="grid-100 margin_top">
<?php
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']!='bad'){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}
		/*	elseif($_SESSION['result_class']=='bad'){
				echo "<div class='feedback hide_element'></div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
			}*/
		}
echo "<div class='feedback hide_element'></div>";		
//get current points per time
$sql=$error=$s='';$placeholders=array();
$sql="select points from points_per_time";
$error="Unable to select points per time";
$s = 	select_sql($sql, $placeholders, $error, $pdo);
foreach($s as $row){$points_per_time=html($row['points']);}	
?>
	<fieldset><legend>Add Procedure to Points Scheme</legend>
		<form action="" method="post" name="" class='patient_form' id="">
		<?php $token = form_token(); $_SESSION['token_loyal1'] = "$token";  ?>
		<input type="hidden" name="token_loyal1"  value="<?php echo $_SESSION['token_loyal1']; ?>" />
			
		<!--define points per minute-->
		<div class='grid-15'><label for="" class="label">Points per minute</label></div>
		<div class='grid-10'><input type=text name=points_per_time value="<?php echo $points_per_time; ?>" /></div>
		<div class='grid-70'><label for="" class="label">(A value must be specified, if you don't want points to be awarded then please type the number zero )</label></div>
		<div class=clear></div><br>
		
		<!--define procedures in points scheme-->
		<div class='grid-15'><label for="" class="label">Select procedure</label></div>
		<div class='grid-50'>
				<?php
				//get procedures that have not yet been added to points scheme
				$sql=$error=$s='';$placeholders=array();
				$sql="select name,id from procedures a  where a.id not in (select procedure_id from procedures_in_points_scheme) order by name";
				$error="Unable to select procedures not in points scheme";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				echo "<select class=input_in_table_cell name=procedure_added ><option></option>";
				foreach($s as $row){
					$procedure_name=html($row['name']);
					$procedure_id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$procedure_id'>$procedure_name</option>";
				}			
				echo "</select>";
			?>
		</div>
		
		<!--define number of points for procedure-->
		<div class='grid-15'><label for="" class="label">Procedure Points</label></div>
		<div class='grid-10'><input type=text name=points_per_procedure /></div>
		<div class=clear></div><br>
		<div class='grid-10 prefix-15'><input type=submit  value='submit' /></form></div>
	</fieldset>
		<div class=clear></div>
			<br><br>
			
			<!--now show procedures already in points scheme-->
			<?php 
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select a.name,b.id,b.points from procedures a, procedures_in_points_scheme b where a.id=b.procedure_id order by name";
				$error2="Unable to get procedures in points scheme";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
				
					if($s2->rowCount()>0 or $s3->rowCount()>0){
						$token = form_token(); $_SESSION['token_loyal2'] = "$token";  ?>
						<form action="" class='patient_form' method="post" name="" id="">
						<input type="hidden" name="token_loyal2"  value="<?php echo $_SESSION['token_loyal2']; ?>" />
						<?php
						echo "<table class='normal_table'><caption>Procedures covered by points scheme</caption><thead>
						<th class='loyal_procedure'>PROCEDURE NAME</th>
						<th class='loyal_points'>NUMBER OF POINTS</th>
						<th class='loyal_remove'>REMOVE FROM POINTS SCHEME</th>
						</thead><tbody>";
						foreach($s2 as $row2){
							$procedure_name=html($row2['name']);
							$val=$encrypt->encrypt(html($row2['id']));
							$points=html($row2['points']);
							echo "<tr><td>$procedure_name</td><td><input type=hidden name='ninye[]' value=$val />
									<input type=text name=old_points[] value=$points /></td><td><input type=checkbox name=remove_procedure[] value=$val /></td></tr>"; 
						}
						
						echo "</table><div class=grid-100><input type=submit class='put_right'  value='Submit' /></div></form>";			
					}?>
</div>