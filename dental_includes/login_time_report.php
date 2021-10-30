<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,71)){exit;}
echo "<div class='grid_12 page_heading'>USER LOGIN TIME REPORT</div>"; ?>
<div class="grid-100 margin_top ">
<?php   
//show appointments
if( isset($_POST['token_ltr1']) and isset($_SESSION['token_ltr1']) and $_SESSION['token_ltr1']==$_POST['token_ltr1']){
	$_SESSION['token_ltr1']='';
	$exit_flag=false;
	//check if user is set
	if(!$exit_flag and !isset($_POST['user']) or 	$_POST['user']==''){
		$error_class='error_response';
		$message="No search user specified. ";
		$exit_flag=true;
	}
	//check if dates are set
	if(!$exit_flag and !isset($_POST['from_date']) or 	$_POST['from_date']=='' or !isset($_POST['to_date']) or $_POST['to_date']==''){
		$error_class='error_response';
		$message="Please specify the date range. ";
		$exit_flag=true;
	}
	if(!$exit_flag){
		//get report
		$sql=$error=$s=$group_id='';$placeholders=array();
		$user_criteria='';
		if($_POST['user']!='all'){
			$user_id=$encrypt->decrypt("$_POST[user]");
			$user_criteria = " and users.id=:user_id";	
			$placeholders[':user_id']=$user_id;
		}

		
		
		$sql="select users.first_name, users.middle_name, users.last_name, login_times.login_time, login_times.logout_time ,
		 sec_to_time( UNIX_TIMESTAMP( login_times.logout_time ) - UNIX_TIMESTAMP( login_times.login_time ) ) as duration
			from users join login_times on login_times.user_id=users.id  $user_criteria
			where date(login_times.login_time)>=:from_date and date(login_times.login_time)<=:to_date
			group by date(login_times.login_time) , login_times.user_id";
		$error="Unable to generate block used group number";
		$placeholders[':from_date']=$_POST['from_date'];
		$placeholders[':to_date']=$_POST['to_date'];
		$s = 	select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$from_date=html($_POST['from_date']);
			$to_date=html($_POST['to_date']);
			$count=$hours=$minutes=$seconds=0;
			$for_user='';
			foreach($s as $row){
				$name=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
				if($_POST['user'] != 'all') {$for_user="for $name";}
				$login=html($row['login_time']);
				$logout=html($row['logout_time']);
				$duartion=html($row['duration']);
				if($count==0){
					echo "<table class='normal_table'><caption>Login times $for_user between $from_date and $to_date</caption><thead>
			              <tr><th class='ltr_count'></th><th class=ltr_name>DATE</th><th class=ltr_date>LOGIN TIME</th><th class=ltr_date>LOGOUT TIME</th>
						  <th class=ltr_duration>DURATION</th>
						  </tr></thead><tbody>";
				}
				$count++;
				echo "<tr ><td>$count</td><td>$name</td><td>$login</td><td>$logout</td><td>$duartion</td></tr>";
				$datax=explode(':',"$duartion");
				$hours = $hours + $datax[0];
				$minutes = $minutes + $datax[1];
				$seconds = $seconds + $datax[2];
			}
			//echo "<tr ><td colspan=4>TOTAL</td><td>$hours:$minutes:$seconds</td></tr>";
			$seconds_f = $seconds % 60;
			$minutes = $minutes + floor($seconds / 60);
			$minutes_f = $minutes % 60;
			$hours = $hours + floor($minutes / 60);
			
			echo "<tr class=total_background><td colspan=4>TOTAL</td><td> $hours:$minutes_f:$seconds_f</td></tr>";
			echo "</tbody></table>";
			exit;
		}
		else{
			echo "<div class=grid-100><label class=label>There are no login records for the selected criteria</label></div>";
			echo "<br><br>";
		}	
	
	}
}
if(isset($error_class) and $error_class!='' and isset($message) and $message!=''){echo "<div class='grid-100 $error_class'>$message</div>";}
		
?>	
	<form class='' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_ltr1'] = "$token";  ?>
		<input type="hidden" name="token_ltr1"  value="<?php echo $_SESSION['token_ltr1']; ?>" />
		<div class=grid-15><label class=label>Select User</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, first_name, middle_name, last_name from users order by first_name";
				$error2="Unable to get users";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-30'><select name=user ><option></option>";
					foreach($s2 as $row2){
						$name=html("$row2[first_name] $row2[middle_name] $row2[last_name] ");
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
					echo "<option value='all'>ALL Users</opiton>";
				echo "</select></div>"; ?>
		<div class=clear></div><br>	
		<div class='grid-15'><label for="user" class="label">Logged in from this date </label></div>
		<div class='grid-10 '><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10'><label for="user" class="label">to this date</label></div>
		<div class='grid-10 '><input type=text name=to_date class=date_picker /></div>
		
		
		<div class=clear></div><br>	
		<div class='prefix-35 grid-10'><input type=submit  value=Submit /></div>
	</form>

</div>