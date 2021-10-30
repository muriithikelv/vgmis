<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,46)){exit;}
echo "<div class='grid_12 page_heading'>WORKING HOURS</div>";

//edit working hours
if( isset($_SESSION['token_workin1']) and isset($_POST['token_workin1']) and $_SESSION['token_workin1']==$_POST['token_workin1']){
	//check if appointment interval is numeric
	$exit_flag=false;
	if(!isset($_POST['appointment_interval']) or $_POST['appointment_interval']=='' ){
		$exit_flag=true;
		echo "<div class=' error_response'>Appointment interval must be specified</div>";	
	}
	if(!$exit_flag and !ctype_digit($encrypt->decrypt("$_POST[appointment_interval]"))){
		$var=html($_POST['appointment_interval']);
		$exit_flag=true;
		echo "<div class=' error_response'>Appointment interval, $var specified is not a valid number</div>";
	}

	//check if appointment interval is set and is valid numeral
	if(!$exit_flag and isset($_POST['auto_appoint']) and $_POST['auto_appoint']!=''){
		if(!ctype_digit($_POST['auto_appoint'])){
				$exit_flag=true;
				$bad_interval=html($_POST['auto_appoint']);
				echo "<div class=' error_response'>Your auto appointment interval of: $bad_interval is not valid</div>";
		}
	}
	//now do checks for  new public holidays
	if(!$exit_flag and isset($_POST['new_holiday_month']) and $_POST['new_holiday_month']!='' and isset($_POST['new_month_day']) and $_POST['new_month_day']!=''
		and isset($_POST['new_holiday_name']) and $_POST['new_holiday_name']!=''){
		$new_month=$encrypt->decrypt("$_POST[new_holiday_month]");
		//check if new month is valid
		if($new_month != 1 and $new_month != 2 and $new_month != 3 and $new_month != 4 and $new_month != 5 and $new_month != 6 and $new_month != 7 and 
			$new_month != 8 and $new_month != 9 and $new_month != 10 and $new_month != 11 and $new_month != 12){
				$exit_flag=true;
				echo "<div class=' error_response'>Please select a valid month for the public holiday</div>";
		}
		//check if the month_day is numeric
		if(!$exit_flag){
			if($new_month == 1){$bad_month = ' January ';}
			elseif($new_month == 2){$bad_month = ' February ';}
			elseif($new_month == 3){$bad_month = ' March ';}
			elseif($new_month == 4){$bad_month = ' April ';}
			elseif($new_month == 5){$bad_month = ' May';}
			elseif($new_month == 6){$bad_month = ' June ';}
			elseif($new_month == 7){$bad_month = ' July ';}
			elseif($new_month == 8){$bad_month = ' August ';}
			elseif($new_month == 9){$bad_month = ' September ';}
			elseif($new_month == 10){$bad_month = ' October ';}
			elseif($new_month == 11){$bad_month = ' November ';}
			elseif($new_month == 12){$bad_month = ' December ';}
			if(!ctype_digit($_POST['new_month_day'])){
				$exit_flag=true;
				$bad_day=html($_POST['new_month_day']);
				echo "<div class=' error_response'>Your holiday date of: $bad_month $bad_day is not valid</div>";
			}
		}
		if(!$exit_flag){
			$year=date('Y');
			$year2=$year + 1;
			
			//now check if date is valid
			if(!checkdate($new_month, $_POST['new_month_day'], $year)){
				//check if date is valid the following year
				if(!checkdate($new_month, $_POST['new_month_day'], $year2)){
					$exit_flag=true;
					$bad_day=html("$_POST[new_month_day]");
					echo "<div class=' error_response'>Your holiday date of: $bad_month $bad_day is not valid</div>";
				}
			}
		} 

	}	
	
	//do checks for school holidays
	if(!$exit_flag and ($_POST['new_description']!='' or $_POST['new_notify_date']!='')){
		if($_POST['new_description']==''){
			$exit_flag=true;
			echo "<div class=' error_response'>School holiday description missing</div>";
		}
		if($_POST['new_notify_date']==''){
			$exit_flag=true;
			echo "<div class=' error_response'>School holiday notification date missing</div>";
		}
		//check if notification date is valid format
		if(!$exit_flag ){
			$data=explode('-',$_POST['new_notify_date']);
			if(!checkdate($data[1], $data[2], $data[0])){
					$exit_flag=true;
					$bad_day=html("$_POST[new_notify_date]");
					echo "<div class=' error_response'>School holiday notification date of:  $bad_day is not valid</div>";
			}
			//or $_POST['new_notify_date']=='')
		}
	}
	
	if(!$exit_flag){	
		try{
			$pdo->beginTransaction();
				//delete current hours
				$sql=$error=$s='';$placeholders=array();
				$sql="truncate table appointment_hours";
				$error="Unable to delete appointment hours";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			
				//delete current appointment intervals
				$sql=$error=$s='';$placeholders=array();
				$sql="truncate table appointment_minutes_interval";
				$error="Unable to delete appointment intervals";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				
				
				
				//delete auto appoint monthly interval
				$sql=$error=$s='';$placeholders=array();
				$sql="truncate table auto_appoint_interval";
				$error="Unable to delete monthly checkup interval period";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);					
				
				//update monthly checkup  interval;
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into auto_appoint_interval set month_interval=:interval";
				$error="Unable to add amonthly checkup  interval";
				$placeholders[':interval']=$_POST['auto_appoint'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
				
				//update minutes interval;
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into appointment_minutes_interval set minute_interval=:interval";
				$error="Unable to add appointment intervals";
				$placeholders[':interval']=$encrypt->decrypt("$_POST[appointment_interval]");
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);		

				//now set hours
				$hour=$_POST['appointment_hour'];
				$n=count($hour);
				$i=0;
				while($i < $n){
					$data=explode('#',$encrypt->decrypt("$hour[$i]"));
					$sql=$error=$s='';$placeholders=array();
					$sql="insert into appointment_hours set shour=:shour, rank=:rank, work_day=:work_day, hour_identifier=:hour_identifier";
					$error="Unable to edit appointment hours";
					$placeholders[':shour']="$data[0]";
					$placeholders[':rank']=$data[1];
					$placeholders[':work_day']=$data[2];
					$placeholders[':hour_identifier']=$data[3];
					$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					$i++;
				}
				
				//edit existing holidays
				if(isset($_POST['matuko_nimeana']) and $_POST['matuko_nimeana']!=''){ 
					//get count
					$count2 = $encrypt->decrypt($_POST['matuko_nimeana']);
					$i2a=1;
					while($i2a < $count2){
						//delete first
						if(isset($_POST["delete_holiday$i2a"]) and $_POST["delete_holiday$i2a"]!=''){
							$holiday_id = $encrypt->decrypt($_POST["delete_holiday$i2a"]);
							$sql=$error=$s='';$placeholders=array();
							$sql="delete from public_holidays where id=:holiday_id ";
							$error="Unable to delete public holiday";
							$placeholders[':holiday_id']=$holiday_id;
							$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
							$i2a++;
							continue;
						}
						//then edit
						$holiday_id = $encrypt->decrypt($_POST["ituko$i2a"]);
						$sql=$error=$s='';$placeholders=array();
						$sql="update public_holidays set holiday_month=:holiday_month, description=:description, month_day=:month_day where id=:holiday_id";
						$error="Unable to add new public holiday";
						$placeholders[':holiday_month']= $encrypt->decrypt($_POST["holiday_month$i2a"]);
						$placeholders[':month_day']=$_POST["month_day$i2a"];
						$placeholders[':description']=$_POST["holiday_name$i2a"];
						$placeholders[':holiday_id']=$holiday_id;
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						
						$i2a++;
						
					}
				}
				
				//now add new public holidays
				if(isset($_POST['new_holiday_month']) and $_POST['new_holiday_month']!='' and isset($_POST['new_month_day']) and $_POST['new_month_day']!=''
					and isset($_POST['new_holiday_name']) and $_POST['new_holiday_name']!=''){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into public_holidays set holiday_month=:holiday_month, description=:description, month_day=:month_day ";
						$error="Unable to add new public holiday";
						$placeholders[':holiday_month']=$new_month;
						$placeholders[':description']=$_POST['new_holiday_name'];
						$placeholders[':month_day']=$_POST['new_month_day'];
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					
				}
				
				//edit existing school holidays
				if(isset($_POST['matuko_nimeana2']) and $_POST['matuko_nimeana2']!=''){ 
					//get count
					$count2 = $encrypt->decrypt($_POST['matuko_nimeana2']);
					$i2a=1;
					while($i2a < $count2){
						//then edit
						$holiday_id = $encrypt->decrypt($_POST["ituko$i2a"]);
						$sql=$error=$s='';$placeholders=array();
						$sql="update school_holiday_description set description=:description, notify_date=:notify_date where id=:holiday_id";
						$error="Unable to     school holiday";
						$placeholders[':description']=$_POST["description$i2a"];
						$placeholders[':notify_date']=$_POST["notify_date$i2a"];
						$placeholders[':holiday_id']=$holiday_id;
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
						
						$i2a++;
						
					}
				}
				
				
				//now add new school holidays
				if($_POST['new_notify_date']!='' and $_POST['new_description']!=''  ){
						$sql=$error=$s='';$placeholders=array();
						$sql="insert into school_holiday_description set description=:description, notify_date=:notify_date ";
						$placeholders[':notify_date']=$_POST['new_notify_date'];
						$placeholders[':description']=$_POST['new_description'];
						$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
					
				}				
			
				$tx_result = $pdo->commit();
				if($tx_result){echo "<div class='success_response'>Changes saved</div>";}

		}
		catch (PDOException $e)
		{
		$pdo->rollBack();
		echo "<div class=' error_response'>Unable to save changes</div>";
		}
	}
}

//get appointment hours
$sql=$error=$s='';$placeholders=array();
$sql="select hour_identifier from appointment_hours";
$error="Unable to get appointment hours";
$s = 	select_sql($sql, $placeholders, $error, $pdo);
$hour_identifier_array=array();
foreach($s as $row){$hour_identifier_array[]=html($row['hour_identifier']);}	

//get appointment interval value
$sql=$error=$s='';$placeholders=array();
$sql="select minute_interval from appointment_minutes_interval";
$error="Unable to get appointment interval";
$s = 	select_sql($sql, $placeholders, $error, $pdo);
$minutes_interval_array=array();
foreach($s as $row){$minute_interval_array[]=html($row['minute_interval']);}	

//get auto appointment interval for checkup
$sql=$error=$s='';$placeholders=array();
$sql="select month_interval from auto_appoint_interval";
$error="Unable to get month interval for auto appointment";
$s = 	select_sql($sql, $placeholders, $error, $pdo);
$month_interval='';
foreach($s as $row){$month_interval=html($row['month_interval']);}	
if($month_interval==0){$month_interval='';}
?>
<div class="grid-100 margin_top">
	<?php include  'response.php'; ?>
		<fieldset><legend>Hours of operation</legend>
		<form class='' action="" method="post" name="" id="">
			<table class=working_days><caption>Working Hours</caption><thead><th class=work_day>Monday</th><th class=work_day>Tuesday</th><th class=work_day>Wednesday</th>
				<th class=work_day>Thursday</th><th class=work_day>Friday</th><th class=work_day>Saturday</th><th class=work_day>Sunday</th></thead>
				<tbody>
					<?php
						$i=0;
						$i2=1;
						$hr_disp=5;
						$day=1;//1 to 7 for mon to sun
						$day_rank=5;
						$night_rank=13;
						$am_pm_counter=0;
						while ($i < 168){
							if(($i % 14) == 0){
								if($i==0){echo "<tr>";}
								else {echo "</tr><tr>";}
								$day_rank++;
								//$night_rank++;
								$am_pm_counter++;
								//$hr++;
								$hr_disp++;
								if($hr_disp == 13){$hr_disp=1;}
								if($hr_disp < 10){$hr_disp="0$hr_disp";}
								//check if day is sunday and set to onday
								if($day == 8){$day=1;}
								
								//check if day rank is 13 and reset to 1
								//if($day_rank == 13){$day_rank=1;}
								if($day_rank <= 12){$night_rank=$day_rank + 12;}
								elseif($day_rank > 12){$night_rank=$day_rank - 12;}
								//check if night rank is 25 and reset to 13
								//if($night_rank == 25){$night_rank=13;}
								
								//check if am or pm
								if($am_pm_counter <= 6){$am='AM';$pm='PM';}
								elseif($am_pm_counter > 6 ){$am='PM';$pm='AM';}
							}
							
							
								$i++;
								//$hour=$encrypt->encrypt("$hr_disp#$day_rank#$day");
								$checked='';
								if(in_array($i2,$hour_identifier_array)){$checked=" checked ";}
								$hour=$encrypt->encrypt("$hr_disp#$day_rank#$day#$i2");
								echo "<td><div class=grid-50><input type=checkbox  name=appointment_hour[] $checked value='$hour' /><label  class='label'>$hr_disp$am</label></div>";
								//$hour=$encrypt->encrypt("$hr_disp#$night_rank#$day");
								$i2++;
								$checked='';
								if(in_array($i2,$hour_identifier_array)){$checked=" checked ";}
								$hour=$encrypt->encrypt("$hr_disp#$night_rank#$day#$i2");
								echo "<div class=grid-50><input type=checkbox  name=appointment_hour[]  $checked  value='$hour' /><label  class='label'>$hr_disp$pm</label></div></td>";
								$day++;
							$i++;
							$i2++;
						}
					?>
				</tbody>
			
			</table>


			<div class=clear></div>	
			<?php $token = form_token(); $_SESSION['token_workin1'] = "$token";  ?>
			<input type="hidden" name="token_workin1"  value="<?php echo $_SESSION['token_workin1']; ?>" />
			
			</fieldset>
			<!-- this is for definifng working weekdays -->
			<fieldset><legend>Appointment intervals</legend>
				<?php
						$checked='';
						if(in_array("05",$minute_interval_array)){$checked=" checked ";}
						$minute=$encrypt->encrypt("05");echo "<div class='grid-10'><input type=radio  $checked name=appointment_interval value='$minute' /><label  class='label'>5 minutes</label></div>";	
						$checked='';
						if(in_array("10",$minute_interval_array)){$checked=" checked ";}
						$minute=$encrypt->encrypt("10");echo "<div class='grid-10'><input type=radio  $checked name=appointment_interval value='$minute' /><label  class='label'>10 minutes</label></div>";	
						$checked='';
						if(in_array("15",$minute_interval_array)){$checked=" checked ";}
						$minute=$encrypt->encrypt("15");echo "<div class='grid-10'><input type=radio  $checked name=appointment_interval value='$minute' /><label  class='label'>15 minutes</label></div>";	
						$checked='';
						if(in_array("20",$minute_interval_array)){$checked=" checked ";}
						$minute=$encrypt->encrypt("20");echo "<div class='grid-10'><input type=radio  $checked name=appointment_interval value='$minute' /><label  class='label'>20 minutes</label></div>";
						$checked='';
						if(in_array("30",$minute_interval_array)){$checked=" checked ";}
						$minute=$encrypt->encrypt("30");echo "<div class='grid-10'><input type=radio  $checked name=appointment_interval value='$minute' /><label  class='label'>30 minutes</label></div>";								
				
				?>
				<div class=clear></div>
			</fieldset>
			
			<!-- this is for definifng public holiday -->
			<fieldset><legend>Public holidays</legend>
				<?php
					//now select public holidays
					$sql=$error=$s='';$placeholders=array();
					$sql="select id,holiday_month, month_day, description from public_holidays order by holiday_month, month_day";
					$error="Unable to select public holidays";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					
						$count=1;
						echo "<table class='public_holidays'><caption>Public Holidays</caption><thead>
						<tr><th class=phol_count></th><th class=phol_month>MONTH</th><th class=phol_day>MONTH DAY</th><th class=phol_desc>HOLIDAY NAME</th>
						<th class=phol_del>DELETE</th></tr></thead><tbody>";
						$val1=$encrypt->encrypt('1');
							$val2=$encrypt->encrypt('2');
							$val3=$encrypt->encrypt('3');
							$val4=$encrypt->encrypt('4');
							$val5=$encrypt->encrypt('5');
							$val6=$encrypt->encrypt('6');
							$val7=$encrypt->encrypt('7');
							$val8=$encrypt->encrypt('8');
							$val9=$encrypt->encrypt('9');
							$val10=$encrypt->encrypt('10');
							$val11=$encrypt->encrypt('11');
							$val12=$encrypt->encrypt('12');
						foreach($s as $row){
							
							$month=html($row['holiday_month']);
							$day=html($row['month_day']);
							$description=html($row['description']);
							$january=$february=$march=$april=$may=$june=$july=$august=$september=$october=$november=$december='';
							if($month == 1){$january = ' selected ';}
							elseif($month == 2){$february = ' selected ';}
							elseif($month == 3){$march = ' selected ';}
							elseif($month == 4){$april = ' selected ';}
							elseif($month == 5){$may = ' selected ';}
							elseif($month == 6){$june = ' selected ';}
							elseif($month == 7){$july = ' selected ';}
							elseif($month == 8){$august = ' selected ';}
							elseif($month == 9){$september = ' selected ';}
							elseif($month == 10){$october = ' selected ';}
							elseif($month == 11){$november = ' selected ';}
							elseif($month == 12){$december = ' selected ';}
							$val=$encrypt->encrypt($row['id']);
							echo "<tr><td >$count <input type=hidden name=ituko$count value='$val' /></td><td><select name=holiday_month$count>
								<option value='$val1' $january >January</option>
								<option value='$val2' $february >February</option>
								<option value='$val3' $march >March</option>
								<option value='$val4' $april >April</option>
								<option value='$val5' $may >May</option>
								<option value='$val6' $june >June</option>
								<option value='$val7' $july >July</option>
								<option value='$val8' $august >August</option>
								<option value='$val9' $september >September</option>
								<option value='$val10' $october >October</option>
								<option value='$val11' $november >November</option>
								<option value='$val12' $december >December</option>
							</select>
							</td><td><input type=text name=month_day$count class=input_in_table_cell value='$day' /></td>
							<td><input type=text name=holiday_name$count value='$description'  /></td><td><input type=checkbox name=delete_holiday$count value='$val'  /></td></tr>";
							$count++;
						}
						//now show empty slot for adding a new holiday
						echo "<tr><td >$count</td><td><select name=new_holiday_month>
								<option></option>
								<option value='$val1'  >January</option>
								<option value='$val2'  >February</option>
								<option value='$val3'  >March</option>
								<option value='$val4'  >April</option>
								<option value='$val5'  >May</option>
								<option value='$val6'  >June</option>
								<option value='$val7'  >July</option>
								<option value='$val8'  >August</option>
								<option value='$val9'  >September</option>
								<option value='$val10'  >October</option>
								<option value='$val11'  >November</option>
								<option value='$val12'  >December</option>
							</select>
							</td><td><input type=text name=new_month_day class=input_in_table_cell  /></td>
							<td><input type=text name=new_holiday_name   /></td><td>&nbsp;</td></tr>";
						echo "</tbody></table>";
						$val_count=$encrypt->encrypt("$count");
						echo "<br><input type=hidden name=matuko_nimeana value='$val_count' />";
						
									
				?>
			</fieldset>
			<!-- this is for definifng the auto recall checkup period for patients who don't have a next appointment -->
			<fieldset><legend>Automatic Appointment for Checkup </legend>
				<div class=clear></div>
				<div class='grid-25 label'>Auto appoint patient for checkup after</div>
				<div class='grid-5 label'><input value='<?php echo $month_interval; ?>' type=text name=auto_appoint size=3 /></div>
				<div class='grid-5 label'>months</div>
			</fieldset>	
			
			<!-- this is for definifng school holiday -->
			<fieldset><legend>School holidays</legend>
				<?php
					//now select school holidays
					$sql=$error=$s='';$placeholders=array();
					$sql="select id,description, notify_date from school_holiday_description order by notify_date";
					$error="Unable to select school holidays";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					
						$count=1;
						echo "<table class='public_holidays'><caption>School Holidays</caption><thead>
						<tr><th class=phol_count></th><th class= >HOLIDAY</th><th class= >NOTIFICATION DATE</th></tr></thead><tbody>";
						foreach($s as $row){
							
							$description=html($row['description']);
							$notify_date=html($row['notify_date']);
							$val=$encrypt->encrypt($row['id']);
								 
							echo "<tr><td >$count <input type=hidden name=ituko$count value='$val' /></td> <td><input type=text name=description$count class=input_in_table_cell value='$description' /></td>
							<td><input type=text name=notify_date$count value='$notify_date' class='date_picker' /></td></tr>";
							$count++;
						}
						//now show empty slot for adding a new holiday
						echo "<tr><td >$count</td><td><input type=text name=new_description class=input_in_table_cell  /></td>
							<td><input type=text name=new_notify_date class='date_picker'  /></td></tr>";
						echo "</tbody></table>";
						$val_count=$encrypt->encrypt("$count");
						echo "<br><input type=hidden name=matuko_nimeana2 value='$val_count' />";
						
									
				?>
			</fieldset>
			<div class='grid-10'><input type="submit"  value="Submit"/></div>
			
			</form>
			
	
</div>
