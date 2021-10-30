<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/

include_once   'encryption.php';
include_once   'num_to_word_converter_class.php';


function userIsLoggedIn()
{
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
	   ?>
<script type="text/javascript">
localStorage.time_out='<div class=error_response>No activity within 15 minutes please log in again</div>';
window.location = window.location.href;
</script>
		<?php
	
		exit;	
} 
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp	

	
  if (isset($_SESSION['id']) and $_SESSION['id'] != '')
  {
	return true;
  }
  elseif (!isset($_SESSION['id']) or $_SESSION['id'] == '')
  {
	return false;
  }
 }



function userHasRole($pdo,$role)
{
 // include 'db.inc.php';

			$sql=$error=$s='';$placeholders=array();		
			$sql="select user_id from privileges where user_id=:user_id and menu_id=:menu_id";
			$error="Error: Unable to get user privileges";
			$placeholders[':user_id']=$_SESSION['id'];	
			$placeholders[':menu_id']=$role;	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	

  if ($s->rowCount() > 0)
  {
    return TRUE;
  }
  else
  {
	//check if the user has a role with this privilege
			$sql=$error=$s='';$placeholders=array();		
			$sql="select user_id from user_roles a, role_privileges b where a.user_id=:user_id and a.role_id=b.role_id and b.menu_id=:menu_id ";
			$error="Error: Unable to get user roles";
			$placeholders[':user_id']=$_SESSION['id'];	
			$placeholders[':menu_id']=$role;	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			  if ($s->rowCount() > 0)
			  {
				return TRUE;
			  }
			else {    return FALSE;}
  }
}

function userHasSubRole($pdo,$role)
{
 // include 'db.inc.php';

			$sql=$error=$s='';$placeholders=array();		
			$sql="select user_id from sub_privileges where user_id=:user_id and sub_menu_id=:menu_id";
			$error="Error: Unable to get user sub privileges";
			$placeholders[':user_id']=$_SESSION['id'];	
			$placeholders[':menu_id']=$role;	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	

  if ($s->rowCount() > 0)
  {
    return TRUE;
  }
  else
  {
	//check if the user has a role with this privilege
			$sql=$error=$s='';$placeholders=array();		
			$sql="select user_id from user_roles a, role_sub_privileges b where a.user_id=:user_id and a.role_id=b.role_id and b.sub_menu_id=:menu_id ";
			$error="Error: Unable to get user sub roles";
			$placeholders[':user_id']=$_SESSION['id'];	
			$placeholders[':menu_id']=$role;	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	
			  if ($s->rowCount() > 0)
			  {
				return TRUE;
			  }
			else {    return FALSE;}
  }
}

function userLocked($pdo) {// include 'db.inc.php';

			$sql=$error=$s='';$placeholders=array();		
			$sql="select status from users where id=:user_id and status='locked'";
			$error="Error: Unable to get user privileges";
			$placeholders[':user_id']=$_SESSION['id'];	
			//$placeholders[':menu_id']=$role;	
			$s = 	select_sql($sql, $placeholders, $error, $pdo);	

  if ($s->rowCount() > 0)
  {
    return TRUE;
  }
  else
  {
    return FALSE;
  }
}


# Log a user Out
function logOut(){
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy();
	
}

# Session Logout after in activity
function sessionX(){
    $logLength = 900; # time in seconds :: 1800 = 30 minutes
    $ctime = strtotime("now"); # Create a time from a string
    # If no session time is created, create one
    if(!isset($_SESSION['sessionX'])){ 
        # create session time
        $_SESSION['sessionX'] = $ctime; 
    }else{
        # Check if they have exceded the time limit of inactivity
        if(((strtotime("now") - $_SESSION['sessionX']) > $logLength) && userIsLoggedIn()){
            # If exceded the time, log the user out
            logOut();
            # Redirect to login page to log back in
			$error_message=" Your session has expired, please log in to continue.";
            header("Location: http://localhost/");
            exit;
        }else{
            # If they have not exceded the time limit of inactivity, keep them logged in
            $_SESSION['sessionX'] = $ctime;
        }
    }
}

//this will get what labs for a patient are with technician
function get_labs_with_technician($pdo, $pid){
		$sql="select a.when_added, a.lab_id, a.date_required, a.amount, b.first_name, b.middle_name, 
				b.last_name, c.first_name, c.middle_name, c.last_name, d.technician_name 
				from labs a, patient_details_a b, users c, lab_technicians d 
				where a.pid=:pid and d.id=a.technician and a.pid=b.pid and a.doc_id=c.id and date_returned is null  order by a.lab_id";
		$error="Unable to get work due out";
		$placeholders[':pid']=$pid;	
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$count=0;
			echo "<br><table class='normal_table'><caption>LABS THAT ARE WITH TECHNICIANS</caption><thead>
			<tr><th class=lab_in_count></th><th class=lab_in_id>LAB No.</th><th class=lab_in_patient2>PATIENT NAME</th><th class=lab_in_doctor2>REQUESTING DOCTOR</th>
			<th class=lab_in_date>REQUESTED<br> ON</th><th class=lab_in_technician2>TECHNICIAN</th><th class=lab_in_cost>COST</th>
			<th class=lab_in_date>DATE <br>REQUIRED</th><th class=lab_in_tray>TRAYS RETURNED</th>
			</tr></thead><tbody>";
			foreach($s as $row){
				$count++;
				$when_added=html("$row[when_added]");
				$patient=html("$row[4] $row[5] $row[6]");
				$doctor=html("$row[7] $row[8] $row[9]");
				$technician=html("$row[technician_name]");
				$cost=number_format(html("$row[amount]"),2);
				$date_required=html("$row[date_required]");
				$lab_id=html("$row[lab_id]");
				
				echo "<tr><td class=count>$count</td><td><input type=button class='button_in_table_cell button_style view_lab' value=$lab_id  /></td><td>$patient</td><td>$doctor</td><td>$when_added</td>
				<td>$technician</td><td>$cost</td><td>$date_required</td><td>";
				//get trays if nay
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2 = "select id,tray_number,date_returned from lab_trays where lab_id=:lab_id";
				$error2 = "Unable to list of trays";
				$placeholders2[':lab_id']=$lab_id;	
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
				if($s2->rowCount()>0){
					echo "<table class='normal_table'><thead><tr><th class=tray_no>TRAY<br>No.</th>
					<th class=tray_date>RETURNED</th></tr></thead><tbody>";
					foreach($s2 as $row2){
						$tray_num=html("$row2[tray_number]" );
						//$val2=$encrypt->encrypt(html($row2['id']));				
						if($row2['date_returned']!=''){$returned=html("$row2[date_returned]" );}
						
						elseif($row2['date_returned']==''){$returned="NO" ;}
									
						echo "<tr><td>$tray_num</td><td>$returned</td></tr>";
					}
					echo "</tbody></table>";
				}
				echo "</td></tr>";

			}
			echo "</tbody></table>";
		}
		//else{echo "<label  class=label>There is no work due out for the selected criteria</label>";}
		echo "<div id=view_lab></div>";
}

//this will show finished lab work that has not yet been dispatched to patient
function undispatched_finished_lab_work($pdo,$pid,$encrypt){
		$sql="select a.when_added, a.lab_id, a.date_required, a.amount, b.first_name, b.middle_name, b.last_name, 
			c.first_name, c.middle_name, c.last_name, d.technician_name, a.date_returned 
			from labs a, patient_details_a b, users c, lab_technicians d 
			where a.pid=:pid and d.id=a.technician and a.pid=b.pid and a.doc_id=c.id  and  a.date_returned is not null 
			and a.date_lab_given_to_patient is null  order by a.lab_id ";
		$error="Unable to get finished lab work that has not been given to patient";
		$placeholders[':pid']=$pid;	
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			$count=0;
			echo "<br><br><form action='#dispatched_lab_work_from_tdone' method='post' name='' id='' class='patient_form'>
			<table class='normal_table'><caption>Finished lab work yet to be given to patient</caption><thead>
			<tr><th class=lab_in_count></th><th class=lab_in_id>LAB No.</th><th class=lab_in_patient>PATIENT NAME</th><th class=lab_in_doctor>REQUESTING DOCTOR</th>
			<th class=lab_in_date>REQUESTED<br> ON</th><th class=lab_in_technician>TECHNICIAN</th><th class=lab_in_cost>COST</th>
			<th class=lab_in_date>DATE <br>REQUIRED</th><th class=lab_in_tray>DATE RETURNED</th><th class=lab_in_finished>DISPATCH</th>
			</tr></thead><tbody>";
			foreach($s as $row){
				$count++;
				$when_added=html("$row[when_added]");
				$patient=html("$row[4] $row[5] $row[6]");
				$doctor=html("$row[7] $row[8] $row[9]");
				$technician=html("$row[technician_name]");
				$cost=number_format(html("$row[amount]"),2);
				$date_required=html("$row[date_required]");
				$date_returned=html("$row[date_returned]");
				$lab_id=html("$row[lab_id]");
				$val=$encrypt->encrypt($lab_id);//
				echo "<tr><td class=count>$count</td><td><input type=button class='button_in_table_cell button_style view_lab' value=$lab_id  /></td><td>$patient</td><td>$doctor</td><td>$when_added</td>
				<td>$technician</td><td>$cost</td><td>$date_required</td><td>$date_returned</td><td><input type=checkbox name=dispatched[] value='$val' />
				</td></tr>";

			}
			echo "</tbody></table>";
			$token = form_token(); $_SESSION['token_patient_work2'] = "$token";  
			echo "<input type=hidden name=token_patient_work2  value='$_SESSION[token_patient_work2]' /><input type=submit class='put_right' value='Submit' /></form><br>";
		}
		else{echo "<label  class=label>There is no finished lab work to be dispatched to patient</label>";}
		echo "<div id=view_lab></div>";
}

//this will display form for lab prescription
function lab_prescription($pid,$encrypt,$pdo,$action){
	if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
			if($_SESSION['result_class']=='success_response'){
				$data=explode('#',"$_SESSION[result_message]");
				echo "<div class='feedback $_SESSION[result_class]'>$data[0]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';
				//show lab for printing
				display_lab($pdo, $data[1]);
				exit;
			}

		}
		if(isset($pid) and $pid!=''){
		
		$bleach_tray=$encrypt->encrypt(".035 Std. Bleach Tray");
		$bruxers=$encrypt->encrypt(".060 Bruxers");
		
		$articulated=$encrypt->encrypt("articulated");
		$non_articulated=$encrypt->encrypt("non-articulated");
		
		$standard=$encrypt->encrypt("standard");
		$full_denture=$encrypt->encrypt("full_denture");
		?>
	<fieldset><legend>Lab Details</legend>
	<div class='feedback hide_element'></div>
		<form action='<?php echo "$action" ?>' method="POST"  name="" id="" class='patient_form'>
			<!--first name-->
				<?php $token = form_token(); $_SESSION['token_add_lab'] = "$token"; 
					echo "<input type='hidden' name='token_ninye'  value='$pid' />";	
					?>
				<input type="hidden" name="token_add_lab"  value="<?php echo $_SESSION['token_add_lab']; ?>" />			
				<!--docotr-->
				<div class='grid-15'><label for="" class="label">Select Technician</label></div>
				<div class='grid-45'>
					<select name=technician><option></option>
					<?php
						$sql=$error=$s='';$placeholders=array();
						$sql = "select id,technician_name from lab_technicians where listed=0 order by technician_name";
						$error = "Unable to list technicians";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['technician_name']);
							$val=$encrypt->encrypt(html($row['id']));
							echo "<option value='$val'>$name</option>";						
						}


					?>				
					</select>
				</div>
				<div class=clear></div>
				<br>
				
				<!--BLEACHING  TRAYS -->
				<div class='grid-25 grid-parent'>
					<div class='grid-100 '><label class="small_heading">BLEACHING  TRAYS</label></div>
					<div class=grid-100>
						<input name="bleach" value='<?php echo "$bleach_tray"; ?>' type="radio" /><label class="label">.035 Std. Bleach Tray</label>
					</div>
					<div class=grid-100>
						<input name="bleach" value='<?php echo "$bruxers"; ?>' type="radio" /><label class="label">.060 Bruxers</label>
					</div>
				</div>
				
				<!--NIGHT GUARDS -->
				<div class='grid-25 grid-parent'>
					<div class='grid-100 small_heading'>NIGHT GUARDS</div>
					<div class=grid-100>
						<input name="night" value='<?php echo "$articulated"; ?>' type="radio" /><label class="label">Articulated</label>
					</div>
					<div class=grid-100>
						<input name="night" value='<?php echo "$non_articulated"; ?>' type="radio" /><label class="label">Non-Articulated</label>
					</div>
				</div>
				
				<!--FLUORIDE  TRAYS -->
				<div class='grid-25 grid-parent'>
					<div class='grid-100 small_heading'>FLUORIDE  TRAYS</div>
					<div class=grid-100>
						<input name="fluoride" value='<?php echo "$standard"; ?>' type="checkbox" /><label class="label">Standard</label>
					</div>
				</div>
				
				<!--MOUTH GUARDS -->
				<div class='grid-25 grid-parent'>
					<div class='grid-100 small_heading'>MOUTH GUARDS</div>
					<div class=grid-100>
						<input name="mouth" value='<?php echo "$articulated"; ?>' type="radio" /><label class="label">Articulated</label>
					</div>
					<div class=grid-100>
						<input name="mouth" value='<?php echo "$non_articulated"; ?>' type="radio" /><label class="label">Non-Articulated</label>
					</div>
				</div>
				<div class=clear></div>
				<br>
				
				<div class='grid-15'><label for="" class="label">Trays</label></div>
				<div class='grid-85'>
					<input  type=textbox name="trays[]" size="3">
					<input  type=textbox name="trays[]" size="3">
					<input  type=textbox name="trays[]" size="3">
					<input  type=textbox name="trays[]" size="3">
					<input  type=textbox name="trays[]" size="3">
				</div>
				<div class=clear></div><br>
				<div class='grid-45 suffix-5 '><!--this is for crowns-->
					<div class='grid-100 tplan_table_caption'>Crowns</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="crowns"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 2x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 8){
									$number="2$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 4x
								<div class='teeth_body'>
								<?php
								$i2=8;
								while($i2 >= 1){
									$number="4$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 3x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 8){
									$number="3$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div> <!-- end crowns parent div-->  	

				<div class='grid-45 prefix-5 '><!--this is for bridge-->
					<div class='grid-100 tplan_table_caption'>Bridge</div>
					<div class='grid-100 teeth_body '>
						<div class='teeth_row'>
							<div class='hover  teeth_heading_cell'>Upper Right - 1x
								<div class='teeth_body'>
								<?php
								$i2=8;
								$teeth_specified="bridge"."[]";
								while($i2 >= 1){
									$number="1$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover teeth_heading_cell'>Upper Left - 2x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 8){
									$number="2$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>
						<!-- second row -->
						<div class='teeth_row'>
							<div class='hover  no_padding teeth_heading_cell'>Lower Right - 4x
								<div class='teeth_body'>
								<?php
								$i2=8;
								while($i2 >= 1){
									$number="4$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2--;
								}	?>
								</div>
							</div>
							<div class='hover  no_padding teeth_heading_cell'>Lower Left - 3x
								<div class='teeth_body'>
								<?php
								$i2=1;
								while($i2 <= 8){
									$number="3$i2";
									$checked=$highlight='';
									$name="tooth$number";
									//echo "<td>$number<br><input type=checkbox name=teeth_specified[] value=$_SESSION[$name] /></td>";
									echo "<div class='hover-row tooth_number $highlight'>$number<br><input $checked class=tooth_checkbox type=checkbox name=$teeth_specified value=$_SESSION[$name] /></div>";
									$i2++;
								}	?>
								</div>
							</div>							
						</div>						
					
					</div>						
				</div> <!-- end bridge parent div-->  	
				<div class=clear></div><br>
				<!--ortho-->
				<div class='grid-45 suffix-5 remove_left_padding'>
					<div class=grid-100><label class="label">Ortho</label></div>
					<div class='grid-100'><textarea   rows='' name=ortho ></textarea></div>
				</div>

				<!--Post core-->
				<div class='grid-45 prefix-5 remove_left_padding'>
					<div class=grid-100><label class="label">Post Core</label></div>
					<div class='grid-100'><textarea   rows='' name=postcore ></textarea></div>
				</div>		
				<div class=clear></div><br>
				<!--denture and shaed-->
				<div class='grid-45 suffix-5 remove_left_padding'>

						<div class=grid-30><label class="label">Full Denture</label></div>
						<div class=grid-70><input type=checkbox name=full_denture value='<?php echo "$full_denture"; ?>' /></div>

						<br>
						<div class=grid-30><label class="label">Partial Denture</label></div>
						<div class=grid-70><input type=text name=partial_denture /></div>
						<div class=clear></div><br>
						<div class=grid-30><label class="label">Shade</label></div>
						<div class='grid-70'><textarea   rows='' name=shade ></textarea></div>
				
				</div>

				<!--description-->
				<div class='grid-45 prefix-5 remove_left_padding'>
					<div class=grid-100><label class="label">Description of service requested</label></div>
					<div class='grid-100'><textarea   rows='' name=description ></textarea></div>
					<div class=clear></div><br>
					<div class=grid-10><label class="label">Cost</label></div>
					<div class=grid-30><input type=text name=amount /></div>

					<div class=grid-25><label class="label">Date Required</label></div>
					<div class=grid-35><input type=text name=date_required  class=date_picker_no_past /></div>
				</div>	
				<div class=clear></div><br>
				<div class=grid-100><input class='put_right' type=submit value=Submit /></form></div>
	</fieldset>			
		
		
	<?php	}
}
//this will dipsly a lab
function display_lab($pdo, $lab_id){
		$lab_id=html($lab_id);
		$sql=$error1=$s='';$placeholders=array();
		$sql="select a.when_added, a.lab_id, a.bleach, a.night, a.fluoride, a.mouth, a.description, a.shade, a.crowns, a.bridge, a.ortho,
		a.post_core, a.full_denture, a.partial_denture, a.date_required, a.amount, b.first_name, b.middle_name, b.last_name, c.first_name, c.middle_name, 
		c.last_name, d.technician_name ,b.patient_number
		from labs a, patient_details_a b, users c, lab_technicians d where d.id=a.technician and a.pid=b.pid and 
		a.doc_id=c.id  and a.lab_id=:lab_id";
		
		$error="Unable to get lab";
		$placeholders[':lab_id']=$lab_id;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		foreach($s as $row){
			$when_added=html("$row[when_added]");
			$bleach=html("$row[bleach]");
			$night=html("$row[night]");
			$fluoride=html("$row[fluoride]");
			$mouth=html("$row[mouth]");
			$description=html("$row[description]");
			$shade=html("$row[shade]");
			$crowns=html("$row[crowns]");
			$bridge=html("$row[bridge]");
			$ortho=html("$row[ortho]");
			$post_core=html("$row[post_core]");
			$full_denture=html("$row[full_denture]");
			$partial_denture=html("$row[partial_denture]");
			$date_required=html("$row[date_required]");
			$amount=number_format(html("$row[amount]"),2);
			$patient=html("$row[16] $row[17] $row[18]");
			$doctor=html("$row[19] $row[20] $row[21]");
			$technician=html("$row[technician_name]"); 
			$patient_number=html("$row[patient_number]");  ?>
			<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>
				<div class='grid-100 no_padding majina_receipt'>
				<!--lab number-->
				<div class='grid-15 lab_left_colum1'><label for="" class="label">Lab Number:</label></div>
				<div class='grid-30 lab_left_colum2'><label for="" class="label2"><?php echo "$lab_id"; ?></label></div>
				<!--date requested-->
				<div class='prefix-10 grid-15 lab_left_colum3'><label for="" class="label">Date Requested:</label></div>
				<div class='grid-30 lab_left_colum4'><label for="" class="label2"><?php echo "$when_added"; ?></label></div>	
				<div class=clear></div>	
				<!--patient-->
				<div class='grid-15  lab_left_colum1'><label for="" class="label">Patient Name:</label></div>
				<div class='grid-30  lab_left_colum2'><label for="" class="label2"><?php echo "$patient"; ?></label></div>
				<!--patient number-->
				<div class='prefix-10 grid-15  lab_left_colum3'><label for="" class="label">Patient Number:</label></div>
				<div class='grid-30  lab_left_colum4'><label for="" class="label2"><?php echo "$patient_number"; ?></label></div>	
				<div class=clear></div>					
				<!--doctor-->
				<div class='grid-15   lab_left_colum1'><label for="" class="label">Requesting Doctor:</label></div>
				<div class='grid-30  lab_left_colum2'><label for="" class="label2"><?php echo "$doctor"; ?></label></div>
				<!--technician-->
				<div class='prefix-10 grid-15  lab_left_colum3'><label for="" class="label">Technician:</label></div>
				<div class='grid-30  lab_left_colum4'><label for="" class="label2"><?php echo "$technician"; ?></label></div>	
				<div class=clear></div><br>
					<!--BLEACHING  TRAYS -->
					<div class='grid-25 grid-parent   lab_left_colum1'>
						<div class='grid-100  '><label class="label">BLEACHING  TRAYS</label></div>
						<div class=grid-100><label for="" class="label2"><?php echo "$bleach"; ?></label></div>
					</div>
					
					<!--NIGHT GUARDS -->
					<div class='grid-25 grid-parent  lab_left_colum2'>
						<div class='grid-100 label'>NIGHT GUARDS</div>
						<div class=grid-100><div class=grid-100><label for="" class="label2"><?php echo "$night"; ?></label></div></div>
					</div>
					
					<!--FLUORIDE  TRAYS -->
					<div class='grid-25 grid-parent  lab_left_colum3'>
						<div class='grid-100 label'>FLUORIDE  TRAYS</div>
						<div class=grid-100><div class=grid-100><label for="" class="label2"><?php echo "$fluoride"; ?></label></div></div>
					</div>
					
					<!--MOUTH GUARDS -->
					<div class='grid-25 grid-parent  lab_left_colum4'>
						<div class='grid-100 label'>MOUTH GUARDS</div>
						<div class=grid-100><div class=grid-100><label for="" class="label2"><?php echo "$mouth"; ?></label></div></div>
					</div>
					<div class=clear></div>
					<br>
					<!--trays-->
					<?php
						$trays='';
						$sql2=$error2=$s2='';$placeholders2=array();
						$sql2 = "select tray_number from lab_trays where lab_id=:lab_id";
						$error2 = "Unable to get trays for lab";
						$placeholders2[':lab_id']=$lab_id;
						$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);	
						foreach($s2 as $row2){
							$val=html($row2['tray_number']);
							if($trays==''){$trays="$val";}
							else{$trays="$trays, $val";}						
						}					
					?>
					<div class='grid-15  lab_left_colum1'><label for="" class="label">Trays:</label></div>
					<div class='grid-20  lab_left_colum2'><label for="" class="label2"><?php echo "$trays"; ?></label></div>
					<div class=clear></div><br>
					<!--crowns-->
					<div class='grid-15   lab_left_colum1'><label for="" class="label">Crowns:</label></div>
					<div class='grid-35   lab_left_colum2'><label for="" class="label2"><?php echo "$crowns"; ?></label></div>
					<!--bridge-->
					<div class='grid-15  lab_left_colum3'><label for="" class="label">Bridge:</label></div>
					<div class='grid-35   lab_left_colum4'><label for="" class="label2"><?php echo "$bridge"; ?></label></div>
					<div class=clear></div>
					<!--ortho-->
					<div class='grid-45 suffix-5 remove_left_padding lab_half_column'>
						<div class='grid-100 lab_left_column'><label class="label">Ortho:</label></div>
						<div class='grid-100'><label for="" class="label2"><?php echo "$ortho"; ?></label></div>
					</div>

					<!--Post core-->
					<div class='grid-45 prefix-5 remove_left_padding lab_half_column'>
						<div class=grid-100><label class="label">Post Core</label></div>
						<div class='grid-100'><label for="" class="label2"><?php echo "$post_core"; ?></label></div>
					</div>				
					<div class=clear></div><br>
					<!--denture and shaed-->
					<div class='grid-45 suffix-5 remove_left_padding lab_half_column'>

							<div class='grid-30  lab_left_column'><label class="label">Full Denture</label></div>
							<div class=grid-70><label for="" class="label2"><?php echo "$full_denture"; ?></label></div>

							<br>
							<div class='grid-30  lab_left_column'><label class="label">Partial Denture</label></div>
							<div class=grid-70><label for="" class="label2"><?php echo "$partial_denture"; ?></label></div>
							<div class=clear></div><br>
							<div class='grid-30  lab_left_column'><label class="label">Shade</label></div>
							<div class='grid-70'><label for="" class="label2"><?php echo "$shade"; ?></label></div>
					
					</div>	
					<!--description-->
					<div class='grid-45 prefix-5 remove_left_padding lab_half_column'>
						<div class=grid-100><label class="label">Description of service requested</label></div>
						<div class='grid-100'><label for="" class="label2"><?php echo "$description"; ?></label></div>
						<div class=clear></div><br>
						<div class=grid-10><label class="label">Cost</label></div>
						<div class=grid-30><label for="" class="label2"><?php echo "$amount"; ?></label></div>
						<div class=clear></div><br>
						<div class=grid-25><label class="label">Date Required</label></div>
						<div class=grid-35><label for="" class="label2"><?php echo "$date_required"; ?></label></div>
					</div>	
			</div>	
					<?php
		}

}
# Session Logout after in activity from dental_b
function sessionX2(){
	
    $logLength = 1800; # time in seconds :: 1800 = 30 minutes
    $ctime = strtotime("now"); # Create a time from a string
    # If no session time is created, create one
    if(!isset($_SESSION['sessionX'])){ 
        # create session time
        $_SESSION['sessionX'] = $ctime; 
    }else{
        # Check if they have exceded the time limit of inactivity
        if(((strtotime("now") - $_SESSION['sessionX']) > $logLength) && userIsLoggedIn()){
            # If exceded the time, log the user out
            logOut();
            # Redirect to login page to log back in
			$error_message=" Your session has expired, please log in to continue.";
            header("Location: ../");
            exit;
        }else{
            # If they have not exceded the time limit of inactivity, keep them logged in
            $_SESSION['sessionX'] = $ctime;
        }
    }

}
//this will check if invoice edit tokens have expired in 15 min
function check_invoice_edit_tokens($pdo){
	$sql=$error=$s='';$placeholders=array();		
	$sql="delete from invoice_edit_token where (unix_timestamp() - unix_timestamp(when_added)) >= 900";
	$error="Error: Unable to delete old invoice tokens";
	$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
}

//this will generate form token centrally
function form_token(){
	return sha1(uniqid(rand(), TRUE));
}

//this will validate input as an integer or decimal
function check_numeric($var,$pdo){
			//check if var is integer 
			if(!ctype_digit($var)){
				//check if it has only 2 decimal places
				$data=explode('.',$var);
				if ( count($data) != 2 ){return false;}
				elseif ( !ctype_digit($data[0]) or !ctype_digit($data[1]) ){return false;}
			}
			else {return true;}
}

//perform case insensitive in_array
function in_array_case_insensitive($needle, $haystack) 
{
 return in_array( strtolower($needle), array_map('strtolower', $haystack) );
}

function log_security($pdo,$message){
	$message=html("$message");
	//echo "$message";
	/*$sql=$error=$s='';$placeholders=array();		
	$sql="select status from users where id=:user_id and status='locked'";
	$error="Error: Unable to get user privileges";
	$placeholders[':user_id']=$_SESSION['id'];	
	//$placeholders[':menu_id']=$role;	
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	*/
}

//this will get list of patient types
function get_patient_types($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=array();		
	$sql="select id from insurance_company";
	$error="Error: Unable to get list of patient types";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	$_SESSION['patient_type_array']=array();
	foreach($s as $row){$_SESSION['patient_type_array'][]=$row['id'];}
}

//this will check password complexity
function password_complexity($password) {
	$return=true;
	if (strlen($password) < 8)
	    {
	       $return=false;
		   $_SESSION['password_complexity_error']='Password too short, it should be at least 8 characyters long';
	    }
	    if (!preg_match("/[a-z]/", $password) and !preg_match("/[A-Z]/", $password))
	    {
	       $return=false;
		   $_SESSION['password_complexity_error']='Password should contain upper and lower case letters'; 
	       	        
	    }
	    if (!preg_match("/[0-9]/", $password))
	    {
	       $return=false;
		   $_SESSION['password_complexity_error']='Password should contain ate least one digit';
	    }
	    //if (preg_match("/.[!,@,#,$,%,^,&,*,?,_,~,-,�,(,)]/", $pwd))
		if (!preg_match( '/[^A-Za-z0-9]+/', $password))
	    {
	        $return=false;
			$_SESSION['password_complexity_error']='Password should contain at least one none alphanumeric character';
	    }
	return $return;
}

//this will get list of covered companies
function get_covered_company($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=array();		
	$sql="select id from covered_company";
	$error="Error: Unable to get list of covered companies";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	$_SESSION['covered_company_array']=array();
	foreach($s as $row){$_SESSION['covered_company_array'][]=$row['id'];}
}

//this willprint a receipt
function print_receipt($pdo,$pay_id_enc, $encrypt){
	$pay_id=$encrypt->decrypt("$pay_id_enc");
	//get receipt details
	$sql=$error=$s='';$placeholders=array();		
	$sql="select a.when_added, a.receipt_num, a.amount, a.pay_type,a.tx_number,a.balance,b.first_name, b.middle_name, b.last_name,
			b.patient_number ,c.name, a.points_balance
		from payments as a join patient_details_a as b on a.pid=b.pid
		left join payment_types as c on c.id=a.pay_type
		where a.id=:id";
	$error="Error: Unable to get receipt details";
	$placeholders[':id']=$pay_id;
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		foreach($s as $row){
			$pay_type=strtoupper(html($row['name']));
			$receipt_number=strtoupper(html($row['receipt_num']));
			$date=html($row['when_added']);
			$name=strtoupper(html("$row[first_name] $row[middle_name] $row[last_name]"));
			$file_no=html($row['patient_number']);
			$amount=html($row['amount']);
			$balance=strtoupper(html($row['balance']));
			$points_balance=html($row['points_balance']);
			//get amount in words
			try
				{
				$text =  strtoupper(convert_number($amount));
				}
			catch(Exception $e)
				{
				echo $e->getMessage();
				}
	
			echo "<div class=grid-100><input class='button_style printment' type='button' value='Print'></div>";
			echo "<div class='grid-100 no_padding majina_receipt'>";
				echo "<div class='grid-15 small_heading receip_left_column'>RECEIPT NUMBER:</div><div class='grid-65 label receip_middle_column'>$receipt_number</div>
				<div class='grid-20 label receip_right_column'>$date</div>";
				echo "<div class=clear></div><br>";
				echo "<div class='grid-15  small_heading receip_left_column'>PATIENT NAME:</div><div class='grid-75 label receip_middle_column'>$name</div>";
				echo "<div class=clear></div><br>";
				echo "<div class='grid-15  small_heading receip_left_column'>PATIENT NUMBER:</div><div class='grid-75 label receip_middle_column'>$file_no</div>";
				echo "<div class=clear></div><br>";
				//for none point payments
				if($row['pay_type']!=8){
					echo "<div class='grid-85 prefix-15 label'><span class='make_italic'>THE SUM OF KENYA SHILLINGS </span>$text<br>
							BEING PAYMENT OF PROFESSIONAL DENTAL SERVICES</div>";
					echo "<div class=clear></div><br>";
					echo "<div class='grid-15 label receip_left_column'>KSHS ".number_format($amount,2)."</div><div class='grid-65 label receip_middle_column'><span class='make_italic'>WITH THANKS<br>FOR MOLARS DENTAL PRACTICE</div>
					<div class='grid-20 label receip_right_column'>....................................<br>SIGN/STAMP</div>";
					echo "<div class=clear></div><br>";
					echo "<div class='grid-50 label'>$balance </div>";
					echo "<div class='grid-50 label'>$points_balance </div>";
				}
				//for point ayments
				elseif($row['pay_type']==8){
					echo "<div class='grid-100 prefix-15 label'><span class='make_italic'>THE SUM OF LOYALTY POINTS </span>$text<br>
							BEING PAYMENT OF PROFESSIONAL DENTAL SERVICES</div>";
					echo "<div class=clear></div><br>";
					echo "<div class='grid-10 label'>POINTS ".number_format($amount,2)."</div><div class='grid-70 label'><span class='make_italic'>WITH THANKS<br>FOR MOLARS DENTAL PRACTICE </div>
					<div class='grid-20 label'>....................................<br>SIGN/STAMP</div>";
					echo "<div class=clear></div><br>";
					echo "<div class=grid-100><label class=label>$balance</label></div>";
				}
			
			echo "</div>";
		}
	}
	else{echo "<label class=label>There are no details for the receipt number</label>";}

}



//this is for dispalying an invoice
function display_invoice($pdo,$invoice_disp_num){
	$invoice_number="$invoice_disp_num";
	//echo "fff $invoice_number";
	//get pt name, insurer
	$sql=$error=$s='';$placeholders=array();
	$sql="SELECT tplan_procedure.invoice_id, min( tplan_procedure.date_invoiced ) , patient_details_a.last_name, patient_details_a.middle_name, 
			patient_details_a.first_name, insurance_company.name, patient_details_a.member_no, patient_details_a.patient_number, 
			covered_company.name, covered_company.pre_auth_needed, covered_company.smart_needed
			FROM tplan_procedure	JOIN patient_details_a ON patient_details_a.pid = tplan_procedure.pid AND tplan_procedure.invoice_number =:invoice_number
			left JOIN insurance_company ON insurance_company.id = patient_details_a.type
			left JOIN covered_company ON patient_details_a.company_covered = covered_company.id
			GROUP BY tplan_procedure.invoice_id";
	$placeholders[':invoice_number']="$invoice_disp_num";
	$error="Unable to invoice details 1";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$company_name=html("$row[8]");
		if($company_name!=''){$company_name=" - $company_name";}
		$insurer_name=html("$row[5]");
		$invoice_id=html("$row[0]");
		$pt_name=html("$row[first_name] $row[middle_name] $row[last_name]");
		$file_no=html($row['patient_number']);
		$member_no=html($row['member_no']);
		$date_raised=html("$row[1]");
	}
	
	?>
	<div class='grid-100'><input type=button class='button_style printment' value=Print /></div>
	<div class='grid-100 no_padding '> <?php
		echo "<div class='grid-30 prefix-70  right_float'><label class=label>INVOICE NO: $invoice_number <br> DATE: $date_raised </label></div>";
		echo "<div class=clear></div></br>";
		echo "<div class='grid-100   majina'><label class=label>PATIENT NAME: $pt_name <br>FILE NO: $file_no <br>CORPORATE: $insurer_name $company_name<br>MEMBER NO:$member_no</label></div><br><br>";
		echo "<div class='invoice_view_table'>";
			//now show procedures done	
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT procedures.name, tplan_procedure.teeth, tplan_procedure.details, tplan_procedure.authorised_cost, tplan_procedure.unauthorised_cost 
				FROM tplan_procedure	JOIN procedures ON procedures.id = tplan_procedure.procedure_id AND tplan_procedure.invoice_id =:invoice_id
				";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 2";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$total =0;
			foreach($s as $row){
				$procedure=html("$row[name]");
				$teeth=html("$row[teeth]");
				$details=html("$row[details]");
				if($details != ''){$details="<br>$details";}
				$unauthorised_cost=html("$row[unauthorised_cost]");
				$authorised_cost=html("$row[authorised_cost]");
				if($authorised_cost==''){$authorised_cost=$unauthorised_cost;}
				if($procedure == 'X-Ray'){echo "<div class=invoice_view_row><div class='inv_view_80 '>$details $teeth</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				else{echo "<div class=invoice_view_row><div class='inv_view_80 '>$procedure $teeth $details</div><div class='inv_view_20 '>".number_format($authorised_cost,2)."</div></div>";}
				$total = $total  + $authorised_cost;
			}

			//now show co-payment
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT amount from co_payment where invoice_number=:invoice_id";
			$placeholders[':invoice_id']=$invoice_id;
			$error="Unable to invoice details 3";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			$co_payment =0;
			foreach($s as $row){
				$amount=html("$row[amount]");
				echo "<div class=invoice_view_row><div class='inv_view_80 '>CO-PAYMENT</div><div class='inv_view_20 '>(".number_format($amount,2).")</div></div>";
				$total = $total  - $amount;
			}
			
			//now show total
			echo "<div class=invoice_view_row><div class='inv_view_80 total_cost'>TOTAL COST</div><div class='inv_view_20 cost_view'>".number_format($total,2)."</div></div>";
		echo "</div>";
	echo "</div>";		
}

//this will get list of cities or towns
function get_cities($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=array();		
	$sql="select id from cities";
	$error="Error: Unable to get list of cities";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){$_SESSION['cities_array'][]=$row['id'];}
}

//this will get list of relationships for patients
function get_relationships($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=array();		
	$sql="select id from patient_relationships";
	$error="Error: Unable to get list of patient relationships";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){$_SESSION['relationship_array'][]=$row['id'];}
}

//this will get list of referees
function get_referee($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=array();		
	$sql="select id from patient_referrer";
	$error="Error: Unable to get list of patient referrers";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){$_SESSION['referee_array'][]=$row['id'];}
}

//this will get list of expense types
function get_expense_types($pdo) {// include 'db.inc.php';
	$sql=$error=$s='';$placeholders=$_SESSION['expense_type_array']=array();		
	$sql="select id from expense_types where deleted=0";
	$error="Error: Unable to get list of expense types";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){$_SESSION['expense_type_array'][]=$row['id'];}
}

//this will get patient completion details
function get_patient_completion($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	//get patient details a
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid, b.comments, b.significant, b.management from 
		patient_details_a a, patient_completion b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from patient_completion where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get patient completion details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		$_SESSION['comments']=html($row['comments']);
		$_SESSION['significant']=html($row['significant']);
		$_SESSION['management']=html($row['management']);
		$_SESSION['pid']=html($row['pid']);
	}
}

//this will clear medical details of the patient
function clear_medical_patient() {// include 'db.inc.php';

$_SESSION['when_added']=$_SESSION['care']=$_SESSION['ldate']=$_SESSION['counter']='';
$_SESSION['good_health']=$_SESSION['change']=$_SESSION['tb']=$_SESSION['persistent']=$_SESSION['cblood']=$_SESSION['care_yes_no']='no';

$_SESSION['pname_m']=$_SESSION['pphone_m']=$_SESSION['paddress']=$_SESSION['illness']='';
$_SESSION['treatment']=$_SESSION['substance_yes_no']=$_SESSION['illnes_yes_no']=$_SESSION['medicine']=$_SESSION['diet']=$_SESSION['alcoholic']=$_SESSION['adependent']='no';


$_SESSION['prescribed']=$_SESSION['natural']=$_SESSION['l24']=$_SESSION['lmonth']=$_SESSION['ndrinks']=$_SESSION['nyrs']='';

$_SESSION['substances']=$_SESSION['frequency']=$_SESSION['years']='';
$_SESSION['tobacco']=$_SESSION['stoping']=$_SESSION['lenses']=$_SESSION['bgroup']=$_SESSION['anaethesia']=$_SESSION['Asprin']=$_SESSION['penicilin']='no';
$_SESSION['sedatives']=$_SESSION['sulfa']=$_SESSION['codeine']=$_SESSION['latex']=$_SESSION['iodine']=$_SESSION['hay']=$_SESSION['animals']='no';
$_SESSION['food']=$_SESSION['other']='no';
$_SESSION['food_specify']=$_SESSION['other_specify']='';
}

//this will get medical details of the patient
function get_patient_medical($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	//get patient details a
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid, b.when_added, b.good_health, b.change, b.tb, b.persistent, b.cblood, b.care_yes_no, b.care, b.ldate, b.pname, b.pphone,
 b.paddress, b.illnes_yes_no, b.illness, b.medicine, b.prescribed, b.counter1,b.natural1, b.diet, b.alcoholic, b.l24, b.lmonth, 
 b.ndrinks, b.nyrs, b.adependent, b.treatment, b.substance_yes_no, b.substances, b.frequency, b.years, b.tobacco, 
 b.stoping, b.lenses, b.bgroup, b.anaethesia, b.Asprin, b.penicilin, b.sedatives, b.sulfa, b.codeine, b.latex, b.iodine,
 b.hay, b.animals, b.food, b.food_specify, b.other, b.other_specify, b.type  from 
		patient_details_a a, patient_medical b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from patient_medical where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get female patient details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){

		$_SESSION['pid']=html($row['pid']);
		$_SESSION['good_health']=html($row['good_health']);
		$_SESSION['change']=html($row['change1']);
		$_SESSION['tb']=html($row['tb']);
		$_SESSION['persistent']=html($row['persistent']);
		$_SESSION['cblood']=html($row['cblood']);
		$_SESSION['care_yes_no']=html($row['care_yes_no']);
		$_SESSION['care']=html($row['care']);
		$_SESSION['ldate']=html($row['ldate']);
		$_SESSION['pname_m']=html($row['pname']);
		$_SESSION['pphone_m']=html($row['pphone']);
		$_SESSION['paddress']=html($row['paddress']);
		$_SESSION['illnes_yes_no']=html($row['illnes_yes_no']);
		$_SESSION['illness']=html($row['illness']);
		$_SESSION['medicine']=html($row['medicine']);
		$_SESSION['counter']=html($row['counter']);
		$_SESSION['prescribed']=html($row['prescribed']);
		$_SESSION['natural']=html($row['natural1']);
		$_SESSION['diet']=html($row['diet']);
		$_SESSION['alcoholic']=html($row['alcoholic']);
		$_SESSION['l24']=html($row['l24']);
		$_SESSION['lmonth']=html($row['lmonth']);
		$_SESSION['ndrinks']=html($row['ndrinks']);
		$_SESSION['nyrs']=html($row['nyrs']);
		$_SESSION['adependent']=html($row['adependent']);
		$_SESSION['treatment']=html($row['treatment']);
		$_SESSION['substance_yes_no']=html($row['substance_yes_no']);
		$_SESSION['substances']=html($row['substances']);
		$_SESSION['frequency']=html($row['frequency']);
		$_SESSION['years']=html($row['years']);
		$_SESSION['tobacco']=html($row['tobacco']);
		$_SESSION['stoping']=html($row['stoping']);
		$_SESSION['lenses']=html($row['lenses']);
		$_SESSION['bgroup']=html($row['bgroup']);
		$_SESSION['anaethesia']=html($row['anaethesia']);
		$_SESSION['Asprin']=html($row['Asprin']);
		$_SESSION['penicilin']=html($row['penicilin']);
		$_SESSION['sedatives']=html($row['sedatives']);
		$_SESSION['sulfa']=html($row['sulfa']);
		$_SESSION['codeine']=html($row['codeine']);
		$_SESSION['latex']=html($row['latex']);
		$_SESSION['iodine']=html($row['iodine']);
		$_SESSION['hay']=html($row['hay']);
		$_SESSION['animals']=html($row['animals']);
		$_SESSION['food']=html($row['food']);
		$_SESSION['food_specify']=html($row['food_specify']);
		$_SESSION['other']=html($row['other']);
		$_SESSION['other_specify']=html($row['other_specify']);
		$_SESSION['type']=html($row['type']);
		$_SESSION['counter']=html($row['counter']);
	}
}

//this will get patient dental information
function clear_patient_dental() {
$_SESSION['gums_bleed']=$_SESSION['sensitive_teeth']=$_SESSION['periodontal']='no';
$_SESSION['braces']=$_SESSION['aches']=$_SESSION['removeable']='no';
$_SESSION['prev_ye_no']= $_SESSION['prev']= $_SESSION['curr']= $_SESSION['last_dental']='';
$_SESSION['last_xray']= $_SESSION['done1']= $_SESSION['appearance']= $_SESSION['history_complain']= $_SESSION['medical_history']= $_SESSION['chief_complain']=  $_SESSION['when_added']='';
}

//this will clear patient on examination
function clear_patient_examination() {
$_SESSION['swelling']=$_SESSION['lymph']=$_SESSION['pocket']=$_SESSION['bone']=$_SESSION['ging']=$_SESSION['per']=$_SESSION['ulcers']='no';
$_SESSION['swell_specify']=$_SESSION['lymph_specify']=$_SESSION['lips']=$_SESSION['other']=$_SESSION['oh']='';
$_SESSION['uspecify']=$_SESSION['pockspec']=$_SESSION['bspecify']='';
$_SESSION['pspecify']=$_SESSION['dentition']='';
$_SESSION['adult_missing']=$_SESSION['adult_occlusal']=$_SESSION['adult_docclusal']=$_SESSION['adult_gic']=$_SESSION['adult_roots']=$_SESSION['pedo_gic']=array();
$_SESSION['adult_mocclusal']=$_SESSION['adult_root']=$_SESSION['adult_cervical']=$_SESSION['adult_crown']=$_SESSION['adult_implant']=array();
$_SESSION['adult_danturv']=$_SESSION['adult_bridge']=$_SESSION['adult_rcanal']=$_SESSION['adult_amalgam']=$_SESSION['adult_composite']=$_SESSION['pedo_missing_teeth']=array();
$_SESSION['orth']=$_SESSION['otherprob']=$_SESSION['doc_id']=$_SESSION['patient_id']=$_SESSION['when_added']='';
$_SESSION['photo_path']=$_SESSION['mixed_missing_teeth']=$_SESSION['mixed_roots']=$_SESSION['mixed_occlusal']='';
$_SESSION['mixed_distal_occlusal']=$_SESSION['mixed_mesial_occlusal']=$_SESSION['mixed_root_carious']=$_SESSION['mixed_cervical']='';
$_SESSION['mixed_crown']=$_SESSION['mixed_implant']=$_SESSION['mixed_denture']=$_SESSION['mixed_bridge']=$_SESSION['mixed_root_canal']='';
$_SESSION['mixed_amalgam']=$_SESSION['mixed_composite']=$_SESSION['mixed_gic']='';
$_SESSION['pedo_roots']=$_SESSION['pedo_occlusal']=$_SESSION['pedo_distal_occlusal']=$_SESSION['pedo_mesial_occlusal']=$_SESSION['pedo_root_carious']=array();
$_SESSION['pedo_cervical']=$_SESSION['pedo_crown']=$_SESSION['pedo_implant']=$_SESSION['pedo_denture']=$_SESSION['pedo_bridge']=array();
$_SESSION['pedo_root_canal']=$_SESSION['pedo_amalgam']=$_SESSION['pedo_composite']= array();
}

//this will get patient examination information
function get_patient_examination($pdo,$criteria,$patient_number) {
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid, b.swelling,b.swell_specify,b.lymph,b.lymph_specify,b.lips,b.other,b.oh,b.ulcers,b.uspecify,b.pocket,b.pockspec,b.bone,b.bspecify,b.ging,
		b.per,b.pspecify,b.dentition,b.adult_missing,b.adult_occlusal,b.adult_docclusal,b.adult_mocclusal,b.adult_root,b.adult_cervical,b.adult_crown,
		b.adult_implant,b.adult_danturv,b.adult_bridge,b.adult_rcanal,b.adult_amalgam,b.adult_composite,b.adult_gic,b.orth,b.otherprob,b.doc_id,
		b.patient_id,b.when_added,b.photo_path,b.adult_roots,b.mixed_missing_teeth,b.mixed_roots,b.mixed_occlusal,b.mixed_distal_occlusal,
		b.mixed_mesial_occlusal,b.mixed_root_carious,b.mixed_cervical,b.mixed_crown,b.mixed_implant,b.mixed_denture,b.mixed_bridge,b.mixed_root_canal,
		b.mixed_amalgam,b.mixed_composite,b.mixed_gic,b.pedo_missing_teeth,b.pedo_gic,b.pedo_roots,b.pedo_occlusal,b.pedo_distal_occlusal,
		b.pedo_mesial_occlusal,b.pedo_root_carious,b.pedo_cervical,b.pedo_crown,b.pedo_implant,b.pedo_denture,b.pedo_bridge,b.pedo_root_canal,
		b.pedo_amalgam,b.pedo_composite from 
		patient_details_a a, on_examination b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from on_examination where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get patient examination details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		$_SESSION['swelling']=html($row['swelling']);
		$_SESSION['swell_specify']=html($row['swell_specify']);
		$_SESSION['lymph']=html($row['lymph']);
		$_SESSION['lymph_specify']=html($row['lymph_specify']);
		$_SESSION['lips']=html($row['lips']);
		$_SESSION['other']=html($row['other']);
		$_SESSION['oh']=html($row['oh']);
		$_SESSION['ulcers']=html($row['ulcers']);
		$_SESSION['uspecify']=html($row['uspecify']);
		$_SESSION['pocket']=html($row['pocket']);
		$_SESSION['pockspec']=html($row['pockspec']);
		$_SESSION['bone']=html($row['bone']);
		$_SESSION['bspecify']=html($row['bspecify']);
		$_SESSION['ging']=html($row['ging']);
		$_SESSION['per']=html($row['per']);
		$_SESSION['pspecify']=html($row['pspecify']);
		$_SESSION['dentition']=html($row['dentition']);

		$_SESSION['adult_missing']=explode(',',html($row['adult_missing']));
		$_SESSION['adult_occlusal']=explode(',',html($row['adult_occlusal']));
		$_SESSION['adult_docclusal']=explode(',',html($row['adult_docclusal']));
		$_SESSION['adult_mocclusal']=explode(',',html($row['adult_mocclusal']));
		$_SESSION['adult_root']=explode(',',html($row['adult_root']));
		$_SESSION['adult_cervical']=explode(',',html($row['adult_cervical']));
		$_SESSION['adult_crown']=explode(',',html($row['adult_crown']));
		$_SESSION['adult_implant']=explode(',',html($row['adult_implant']));
		$_SESSION['adult_danturv']=explode(',',html($row['adult_danturv']));
		$_SESSION['adult_bridge']=explode(',',html($row['adult_bridge']));
		$_SESSION['adult_rcanal']=explode(',',html($row['adult_rcanal']));
		$_SESSION['adult_amalgam']=explode(',',html($row['adult_amalgam']));
		$_SESSION['adult_composite']=explode(',',html($row['adult_composite']));
		$_SESSION['adult_gic']=explode(',',html($row['adult_gic']));
		$_SESSION['adult_roots']=explode(',',html($row['adult_roots']));

		$_SESSION['orth']=html($row['orth']);
		$_SESSION['otherprob']=html($row['otherprob']);

		$_SESSION['pid']=html($row['pid']);


		$_SESSION['mixed_missing_teeth']=html($row['mixed_missing_teeth']);
		$_SESSION['mixed_roots']=html($row['mixed_roots']);
		$_SESSION['mixed_occlusal']=html($row['mixed_occlusal']);
		$_SESSION['mixed_distal_occlusal']=html($row['mixed_distal_occlusal']);
		$_SESSION['mixed_mesial_occlusal']=html($row['mixed_mesial_occlusal']);
		$_SESSION['mixed_root_carious']=html($row['mixed_root_carious']);
		$_SESSION['mixed_cervical']=html($row['mixed_cervical']);
		$_SESSION['mixed_crown']=html($row['mixed_crown']);
		$_SESSION['mixed_implant']=html($row['mixed_implant']);
		$_SESSION['mixed_denture']=html($row['mixed_denture']);
		$_SESSION['mixed_bridge']=html($row['mixed_bridge']);
		$_SESSION['mixed_root_canal']=html($row['mixed_root_canal']);
		$_SESSION['mixed_amalgam']=html($row['mixed_amalgam']);
		$_SESSION['mixed_composite']=html($row['mixed_composite']);
		$_SESSION['mixed_gic']=html($row['mixed_gic']);

		$_SESSION['pedo_missing_teeth']=explode(',',html($row['pedo_missing_teeth']));
		$_SESSION['pedo_gic']=explode(',',html($row['pedo_gic']));
		$_SESSION['pedo_roots']=explode(',',html($row['pedo_roots']));
		$_SESSION['pedo_occlusal']=explode(',',html($row['pedo_occlusal']));
		$_SESSION['pedo_distal_occlusal']=explode(',',html($row['pedo_distal_occlusal']));
		$_SESSION['pedo_mesial_occlusal']=explode(',',html($row['pedo_mesial_occlusal']));
		$_SESSION['pedo_root_carious']=explode(',',html($row['pedo_root_carious']));
		$_SESSION['pedo_cervical']=explode(',',html($row['pedo_cervical']));
		$_SESSION['pedo_crown']=explode(',',html($row['pedo_crown']));
		$_SESSION['pedo_implant']=explode(',',html($row['pedo_implant']));
		$_SESSION['pedo_denture']=explode(',',html($row['pedo_denture']));
		$_SESSION['pedo_bridge']=explode(',',html($row['pedo_bridge']));
		$_SESSION['pedo_root_canal']=explode(',',html($row['pedo_root_canal']));
		$_SESSION['pedo_amalgam']=explode(',',html($row['pedo_amalgam']));
		$_SESSION['pedo_composite']=explode(',',html($row['pedo_composite']));
	}
}

//this will get patient dental information
function get_patient_dental($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	//get patient details a
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid, b.gums_bleed, b.sensitive_teeth, b.periodontal, b.braces, b.aches, b.removeable,
	b.prev_ye_no, b.prev, b.curr, b.last_dental, b.last_xray, b.done1, b.appearance,b.history_complain,
	b.medical_history,b.chief_complain, b.when_added, from 
		patient_details_a a, patient_dental b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from patient_dental where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get patient dental details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		$_SESSION['gums_bleed']=html($row['gums_bleed']);
		$_SESSION['sensitive_teeth']=html($row['sensitive_teeth']);
		$_SESSION['periodontal']=html($row['periodontal']);
		$_SESSION['braces']=html($row['braces']);
		$_SESSION['aches']=html($row['aches']);
		$_SESSION['removeable']=html($row['removeable']);
		$_SESSION['prev_ye_no']=html($row['prev_ye_no']);
		$_SESSION['prev']=html($row['prev']);
		$_SESSION['curr']=html($row['curr']);
		$_SESSION['last_dental']=html($row['last_dental']);
		$_SESSION['last_xray']=html($row['last_xray']);
		$_SESSION['done1']=html($row['done1']);
		$_SESSION['appearance']=html($row['appearance']);
		$_SESSION['history_complain']=html($row['history_complain']);
		$_SESSION['medical_history']=html($row['medical_history']);
		$_SESSION['chief_complain']=html($row['chief_complain']);
		$_SESSION['pid']=html($row['pid']);
		$_SESSION['when_added']=html($row['when_added']);
	}
}

//this will get female patient details
function get_female_patient($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	//get patient details a
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid, b.when_added, b.pregnant,b.nursing,b.control,
	b.pjoint,b.pwhen,b.complication,b.antibiotics,b.dose,b.pname,b.pphone from 
		patient_details_a a, patient_women b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from patient_women where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get female patient details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		$_SESSION['when_added']=html($row['when_added']);
		$_SESSION['pregnant']=html($row['pregnant']);
		$_SESSION['nursing']=html($row['nursing']);
		$_SESSION['control']=html($row['control']);
		$_SESSION['pjoint']=html($row['pjoint']);
		$_SESSION['pwhen']=html($row['pwhen']);
		$_SESSION['complication']=html($row['complication']);
		$_SESSION['antibiotics']=html($row['antibiotics']);
		$_SESSION['dose']=html($row['dose']);
		$_SESSION['pname']=html($row['pname']);
		$_SESSION['pphone']=html($row['pphone']);
		$_SESSION['pid']=html($row['pid']);
	}
}

//this will clear female patient details
function clear_female_patient() {// include 'db.inc.php';
		$_SESSION['when_added']=$_SESSION['pwhen']=$_SESSION['dose']=$_SESSION['pname']= $_SESSION['pphone']= '';
		$_SESSION['pregnant']=$_SESSION['nursing']=$_SESSION['control']=$_SESSION['pjoint']=$_SESSION['complication']=$_SESSION['antibiotics']='no';

}

//this will get patient disease details
function get_patient_disease($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	//get patient disease
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select a.pid,   bleeding,   aids,   anaemia,   arthritis,   rarthritis,   asthma,   transfusion,   tdate,   cancer,
  chronic,   diarea,   cardio_disease,   angina ,   arteriosclerosis ,   hvalves ,   cinsuff ,   cocclus ,   dhvalve ,   hattack ,   hmurmur ,
  blood_pressure ,   inborn ,   prolapse ,   pacemaker ,   rhdisease ,   drug,   diab1 ,   diabetes,   dry,   eating,   especify ,   epilepsy, 
  faint,   reflux,   glaucoma,   hemophilia,   hepatitis,   recurent,   rtype ,   kidney,   low-blood,   malnutrition,   migrain,   night_sweat,
  mental,   mspecify ,   neuro,   nspecify ,   osteoporosis,   swollen,   rproblems,   emphysema ,   headaches,   wloss,   std,   sinus,   sleep, 
  sores,   stroke,   systematic,   thyroid,   tb,   ulcers,   urination,   other text,    when_added,   other_yes_no from 
		patient_details_a a, patient_disease b where a.patient_number=:patient_number and a.pid=b.pid";}
	elseif($criteria=="pid"){$sql="select * from patient_disease where pid=:patient_number";}
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get patient disease details ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){

		$_SESSION['bleeding']=html($row['bleeding']);
		$_SESSION['aids']=html($row['aids']);
		$_SESSION['anaemia']=html($row['anaemia']);
		$_SESSION['arthritis']=html($row['arthritis']);
		$_SESSION['rarthritis']=html($row['rarthritis']);
		$_SESSION['asthma']=html($row['asthma']);
		$_SESSION['transfusion']=html($row['transfusion']);
		$_SESSION['tdate']=html($row['tdate']);
		$_SESSION['cancer']=html($row['cancer']);
		$_SESSION['chronic']=html($row['chronic']);
		$_SESSION['diarea']=html($row['diarea']);
		$_SESSION['cardio_disease']=html($row['cardio_disease']);
		$_SESSION['angina']=html($row['angina']);
		$_SESSION['arteriosclerosis']=html($row['arteriosclerosis']);
		$_SESSION['hvalves']=html($row['hvalves']);
		$_SESSION['cinsuff']=html($row['cinsuff']);
		$_SESSION['cocclus']=html($row['cocclus']);
		$_SESSION['dhvalve']=html($row['dhvalve']);
		$_SESSION['hattack']=html($row['hattack']);
		$_SESSION['hmurmur']=html($row['hmurmur']);
		$_SESSION['blood_pressure']=html($row['blood_pressure']);
		$_SESSION['inborn']=html($row['inborn']);
		$_SESSION['prolapse']=html($row['prolapse']);
		$_SESSION['pacemaker']=html($row['pacemaker']);
		$_SESSION['rhdisease']=html($row['rhdisease']);

		$_SESSION['drug']=html($row['drug']);
		$_SESSION['diab1']=html($row['diab1']);
		$_SESSION['diabetes']=html($row['diabetes']);
		$_SESSION['dry']=html($row['dry']);
		$_SESSION['eating']=html($row['eating']);
		$_SESSION['especify']=html($row['especify']);
		$_SESSION['epilepsy']=html($row['epilepsy']);
		$_SESSION['faint']=html($row['faint']);
		$_SESSION['reflux']=html($row['reflux']);
		$_SESSION['glaucoma']=html($row['glaucoma']);
		$_SESSION['hemophilia']=html($row['hemophilia']);
		$_SESSION['hepatitis']=html($row['hepatitis']);
		$_SESSION['recurent']=html($row['recurent']);
		$_SESSION['rtype']=html($row['rtype']);
		$_SESSION['kidney']=html($row['kidney']);
		$_SESSION['low_blood']=html($row['low_blood']);
		$_SESSION['malnutrition']=html($row['malnutrition']);
		$_SESSION['migrain']=html($row['migrain']);
		$_SESSION['night_sweat']=html($row['night_sweat']);
		$_SESSION['mental']=html($row['mental']);
		$_SESSION['mspecify']=html($row['mspecify']);

		$_SESSION['neuro']=html($row['neuro']);
		$_SESSION['nspecify']=html($row['nspecify']);
		$_SESSION['osteoporosis']=html($row['osteoporosis']);
		$_SESSION['swollen']=html($row['swollen']);
		$_SESSION['rproblems']=html($row['rproblems']);
		$_SESSION['emphysema']=html($row['emphysema']);
		$_SESSION['headaches']=html($row['headaches']);
		$_SESSION['wloss']=html($row['wloss']);
		$_SESSION['std']=html($row['std']);
		$_SESSION['sinus']=html($row['sinus']);
		$_SESSION['sleep']=html($row['sleep']);
		$_SESSION['sores']=html($row['sores']);
		$_SESSION['stroke']=html($row['stroke']);
		$_SESSION['systematic']=html($row['systematic']);
		$_SESSION['thyroid']=html($row['thyroid']);
		$_SESSION['tb']=html($row['tb']);
		$_SESSION['ulcers']=html($row['ulcers']);
		$_SESSION['urination']=html($row['urination']);
		$_SESSION['other']=html($row['other']);
		$_SESSION['pid']=html($row['pid']);
	}
}

//this will unset patient disease from session variables
function clear_patient_disease() {
 $_SESSION['bleeding']= $_SESSION['aids']= $_SESSION['anaemia']= $_SESSION['arthritis']= $_SESSION['rarthritis']= $_SESSION['asthma']='no';
 $_SESSION['transfusion']= $_SESSION['cancer']= $_SESSION['chronic']= $_SESSION['diarea']= $_SESSION['cardio_disease']=  $_SESSION['eating']='no';
 $_SESSION['tdate']= $_SESSION['angina']= $_SESSION['arteriosclerosis']= $_SESSION['hvalves']= $_SESSION['cinsuff']= $_SESSION['cocclus']= $_SESSION['dhvalve']= '';
 $_SESSION['hattack']= $_SESSION['hmurmur']= $_SESSION['blood_pressure']= $_SESSION['inborn']= $_SESSION['prolapse']= $_SESSION['pacemaker']= '';
 $_SESSION['rhdisease']=$_SESSION['diabetes']= $_SESSION['especify']=  $_SESSION['rtype']=$_SESSION['other']='';
  $_SESSION['mspecify']=$_SESSION['nspecify']= $_SESSION['emphysema']='';
 $_SESSION['epilepsy']= $_SESSION['faint']= $_SESSION['reflux']= $_SESSION['glaucoma']= $_SESSION['hemophilia']= $_SESSION['hepatitis']= 'no';
 $_SESSION['recurent']= $_SESSION['kidney']= $_SESSION['low_blood']= $_SESSION['malnutrition']= $_SESSION['migrain']= $_SESSION['diab1']='no';
 $_SESSION['night_sweat']= $_SESSION['mental']=  $_SESSION['neuro']= $_SESSION['osteoporosis']= $_SESSION['swollen']= $_SESSION['rproblems']= 'no';
  $_SESSION['headaches']= $_SESSION['wloss']= $_SESSION['std']= $_SESSION['sinus']= $_SESSION['urination']=  $_SESSION['drug']=$_SESSION['dry']= 'no';
 $_SESSION['sleep']= $_SESSION['sores']= $_SESSION['stroke']= $_SESSION['systematic']= $_SESSION['thyroid']= $_SESSION['tb']= $_SESSION['ulcers']= 'no';
  $_SESSION['when_added']= $_SESSION['other_yes_no']= '';
		
}

//this will unset patient contacts from session variables
function clear_patient_completion() {
		$_SESSION['comments']='';
		$_SESSION['significant']='';
		$_SESSION['management']='';
		
}
		
//this will unset patient contacts from session variables
function clear_patient() {
		$_SESSION['last_name']='';$_SESSION['middle_name']='';$_SESSION['first_name']='';	$_SESSION['mobile_phone']='';
		$_SESSION['biz_phone']='';$_SESSION['type']='';$_SESSION['patient_number']='';$_SESSION['pid']='';
		$_SESSION['member_no']='';$_SESSION['company_covered']='';$_SESSION['family_id']='';$_SESSION['family_title']='';
		$_SESSION['insurance_cover_role']=$_SESSION['company_covered_name']=$_SESSION['type_name']='';
		$_SESSION['id_number']=$_SESSION['address']=$_SESSION['city']=$_SESSION['insurance_mismatch_error']='';
		$_SESSION['occupation']=$_SESSION['weight']=$_SESSION['dob']=$_SESSION['referee']='';
		$_SESSION['referee_phone']=$_SESSION['referee_email']=$_SESSION['em_contact']=$_SESSION['em_relationship']='';
		$_SESSION['em_phone']=$_SESSION['behalf_name']=$_SESSION['behalf_relationship']=$_SESSION['when_added']='';
		$_SESSION['gender']=$_SESSION['photo_path']=$_SESSION['email_address']=$_SESSION['email_address_2']='';
}

function show_family_group_members($pdo, $family_id, $encrypt, $action){
	$family_id=$encrypt->decrypt("$family_id");
	//get family name
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.name,a.id from family_group a where a.id=:family_id";
	$error="Unable to get  family name";
	$placeholders['family_id']=$family_id;
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	if($s->rowCount() > 0){
		foreach($s as $row){
			$family_name=html($row['name']);
		//	$family_id=html($row['id']);
		}
		//get other family members
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select last_name, middle_name, first_name, patient_number, pid ,b.name 
				from patient_details_a a, patient_relationships b 
				where family_id=:family_id and a.family_title=b.id";
			$error2="Unable to get  family group memebers";
			$placeholders2['family_id']=$family_id;
			$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	

			//if($s2->rowCount()>0){
				if(  $action=='add_member'){//for adding new member
					echo "inakwatafamily<table class='normal_table'><caption>FAMILY GROUP: $family_name </caption>
					<thead><tr><th class=fm_pt_name>MEMBER NAME</th><th class=fm_pt_rel>RELATIONSHIP</th>
					<th class=fm_pt_file>FILE No.</th></tr></thead><tbody>";
					foreach($s2 as $row2){
						$name=ucfirst(html("$row2[first_name]  $row2[middle_name]  $row2[last_name]"));
						$file_no=html($row2['patient_number']);
						$var=$encrypt->encrypt($row2['pid']);
						$relationship=html($row2['name']);
						echo "<tr><td>$name</td><td>$relationship</td><td>$file_no</td></tr>";
					}
					echo "</tbody></table>"; ?>
					<form action="" method="POST"  name="" id="" class='patient_form'>
					<?php 
						$token = form_token(); $_SESSION['token_ptf_b'] = "$token"; 
						$fid=$encrypt->encrypt("$family_id");
					?>
					<div class=clear></div>
					<div class='grid-15'>
						<input type="hidden" name="token_ptf_b"  value="<?php echo $_SESSION['token_ptf_b']; ?>" />
						<input type="hidden" name="ninye"  value='<?php echo "$fid"; ?>' />
						<label for="" class="label">Relationship</label>
					</div>
					<div class='grid-15'><select name=family_title><option></option>
						<?php
							$sql=$error=$s='';$placeholders=array();
							$sql = "select id,name from patient_relationships order by name";
							$error = "Unable to list patient relationships";
							$s = 	select_sql($sql, $placeholders, $error, $pdo);	
							foreach($s as $row){
								$name=html($row['name']);
								$val=$encrypt->encrypt(html($row['id']));
								echo "<option value='$val'>$name</option>";						
							}
						
						?>				
						</select>
					</div>
					<div class='grid-10 '><input type=submit value='Add to group' /></form></div> 
					<?php
				}
				else{//for just showing members new member
					echo "inakwatafamily<table class='normal_table'><caption>FAMILY GROUP: $family_name </caption>
					<thead><tr><th class=fm_pt_name>MEMBER NAME</th><th class=fm_pt_rel>RELATIONSHIP</th>
					<th class=fm_pt_file>FILE No.</th></tr></thead><tbody>";
					foreach($s2 as $row2){
						$name=ucfirst(html("$row2[first_name]  $row2[middle_name]  $row2[last_name]"));
						$file_no=html($row2['patient_number']);
						$var=$encrypt->encrypt($row2['pid']);
						$relationship=html($row2['name']);
						echo "<tr><td>$name</td><td>$relationship</td><td>$file_no</td></tr>";
					}
					echo "</tbody></table>";
				}
				
			//}
			//else{echo "inakwatafamily<label class=label>Family Group, $family_name has no members.</label>";}
	}
	/*else{
		echo "<div class=grid-100><input type=button class='new_family button_style' value='Add to Family Group' /></div>";
	}*/
}

function get_pt_family_memebrs_for_credit_transfer($pdo, $pid, $encrypt){
	$pid_enc="$pid";
	$pid=$encrypt->decrypt("$pid");
	//get family name
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.name,a.id from family_group a, patient_details_a b where b.pid=:pid and a.id=b.family_id";
	$error="Unable to get pt family name";
	$placeholders['pid']=$pid;
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	if($s->rowCount() > 0){
		foreach($s as $row){
			$family_name=html($row['name']);
			$family_id=html($row['id']);
		}
		//get other family members
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select last_name, middle_name, first_name, patient_number, pid ,b.name 
				from patient_details_a a, patient_relationships b 
				where family_id=:family_id and a.family_title=b.id";
			$error2="Unable to get  family group memebers";
			$placeholders2['family_id']=$family_id;
			$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	

			echo "<table class='normal_table'><caption>FAMILY GROUP: $family_name </caption>
			<thead><tr><th class=fm_pt_name2>MEMBER NAME</th><th class=fm_pt_rel2>RELATIONSHIP</th>
			<th class=fm_pt_file2>FILE No.</th><th class=fm_pt_bal2>BALANCE</th><th class=fm_pt_del2>SELECT</th></tr></thead><tbody>";
			foreach($s2 as $row2){
				if($pid == $row2['pid']){continue;}
				$name=ucfirst(html("$row2[first_name]  $row2[middle_name]  $row2[last_name]"));
				$file_no=html($row2['patient_number']);
				
				$relationship=html($row2['name']);
				//get balance for this guy
				$data=show_pt_statement_brief($pdo,$encrypt->encrypt("$row2[pid]"),$encrypt);
				$bal= explode('#',"$data");
				$amount=str_replace(",", "", $bal[1]);
				$var=$encrypt->encrypt("$row2[pid]#$amount");
				echo "<tr><td>$name</td><td>$relationship</td><td>$file_no</td><td>$bal[1]</td><td>";
				if($amount < 0){ echo "<input type=radio name='cred_family_mem' value=$var />";}
				else{echo "&nbsp;";}
				echo "</td></tr>";
			}
			echo "</tbody></table>";			
	}
	else{
		echo "<label class=label>This patient is not part of any family group</label>";
	}
}

function get_pt_family_memebrs($pdo, $pid, $encrypt){
	$pid=$encrypt->decrypt("$pid");
	//get family name
	$sql=$error1=$s='';$placeholders=array();
	$sql="select a.name,a.id from family_group a, patient_details_a b where b.pid=:pid and a.id=b.family_id";
	$error="Unable to get pt family name";
	$placeholders['pid']=$pid;
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	if($s->rowCount() > 0){
		foreach($s as $row){
			$family_name=html($row['name']);
			$family_id=html($row['id']);
		}
		//get other family members
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select last_name, middle_name, first_name, patient_number, pid ,b.name 
				from patient_details_a a, patient_relationships b 
				where family_id=:family_id and a.family_title=b.id";
			$error2="Unable to get  family group memebers";
			$placeholders2['family_id']=$family_id;
			$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);	

			echo "<table class='normal_table'><caption>FAMILY GROUP: $family_name </caption>
			<thead><tr><th class=fm_pt_name>MEMBER NAME</th><th class=fm_pt_rel>RELATIONSHIP</th>
			<th class=fm_pt_file>FILE No.</th><th class=fm_pt_del>REMOVE</th></tr></thead><tbody>";
			foreach($s2 as $row2){
				$name=ucfirst(html("$row2[first_name]  $row2[middle_name]  $row2[last_name]"));
				$file_no=html($row2['patient_number']);
				$var=$encrypt->encrypt($row2['pid']);
				$relationship=html($row2['name']);
				echo "<tr><td>$name</td><td>$relationship</td><td>$file_no</td><td><input type=checkbox name='del_family_mem[]' value=$var /></td></tr>";
			}
			echo "</tbody></table>";			
	}
	else{
		echo "<div class=grid-100><input type=button class='new_family button_style' value='Add to Family Group' /></div>";
	}
}

//this will get a patient's contact details
function get_patient($pdo,$criteria,$patient_number) {// include 'db.inc.php';
	
	//get patient details a
	$sql=$error=$s='';$placeholders=array();	
	if($criteria=="patient_number"){$sql="select * from patient_details_a where patient_number=:patient_number and internal_patient=0";}
	elseif($criteria=="pid"){$sql="select * from patient_details_a where pid=:patient_number and internal_patient=0";}
	elseif($criteria=="mobile_number"){$sql="select * from patient_details_a where mobile_phone=:patient_number and internal_patient=0";}	
	elseif($criteria=="business_number"){$sql="select * from patient_details_a where biz_phone=:patient_number and internal_patient=0";}	
	$placeholders[':patient_number']="$patient_number";
	$error="Error: Unable to get patient details a";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount()>0){
	foreach($s as $row){
		$_SESSION['last_name']=ucfirst(html($row['last_name']));
		$_SESSION['middle_name']=ucfirst(html($row['middle_name']));
		$_SESSION['first_name']=ucfirst(html($row['first_name']));
		$_SESSION['mobile_phone']=html($row['mobile_phone']);
		$_SESSION['biz_phone']=html($row['biz_phone']);
		$_SESSION['type']=html($row['type']);
		$_SESSION['patient_number']=html($row['patient_number']);
		
		$_SESSION['pid']=html($row['pid']);
		
		$_SESSION['member_no']=html($row['member_no']);
		$_SESSION['company_covered']=html($row['company_covered']);
		$_SESSION['family_id']=html($row['family_id']);
		$_SESSION['family_title']=html($row['family_title']);
		$_SESSION['insurance_cover_role']=html($row['insurance_cover_role']);
		$_SESSION['email_address']=html($row['email_address']);
		$_SESSION['email_address_2']=html($row['email_address_2']);
	}
	//check if the type and company are insured or not
	$_SESSION['cover_limit']=$_SESSION['expiry_date']=$_SESSION['company_covered_name']=$_SESSION['type_name']='';
	$_SESSION['insured']='NO';
	$sql=$error=$s='';$placeholders=array();	
	$sql="select insured , cover_limit, end_cover,name from covered_company where id=:covered_company";
	$placeholders[':covered_company']=$_SESSION['company_covered'];
	$error="Error: Unable to get covered company name ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row ){
		$_SESSION['company_covered_name']=html($row['name']);
		$_SESSION['insured']=html($row['insured']);
		if($row['insured']=='YES'){
			if($row['cover_limit']==0){$_SESSION['cover_limit']='';}
			else{$_SESSION['cover_limit']=number_format(html($row['cover_limit']),2);}
			$_SESSION['expiry_date']=html($row['end_cover']);
		}
	}	
	
				/*$_SESSION['insured']='NO';
				//get company_covered_name and type_name
				$_SESSION['company_covered_name']=$_SESSION['type_name']='';
				$sql=$error=$s='';$placeholders=array();	
				$sql="select name, insured from covered_company where id=:covered_company";
				$placeholders[':covered_company']=$_SESSION['company_covered'];
				$error="Error: Unable to get covered company name ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row ){
					$_SESSION['company_covered_name']=html($row['name']);
					$_SESSION['insured']=html($row['insured']);
				}*/
				
				$sql=$error=$s='';$placeholders=array();	
				$sql="select name from insurance_company where id=:type";
				$placeholders[':type']=$_SESSION['type'];
				$error="Error: Unable to get insurance company name ";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row ){$_SESSION['type_name']=html($row['name']);}	

				//check if this company_covered is correctly insured
					$_SESSION['insurance_mismatch_error']='';
				/*if(isset($_SESSION['company_covered']) and $_SESSION['company_covered']!=''){
					$sql=$error=$s='';$placeholders=array();	
					$sql="select insurer_id, b.name from covered_company a ,insurance_company b where a.id=:covered_company 
						and a.insurer_id=b.id";
					$placeholders[':covered_company']=$_SESSION['company_covered'];
					$error="Error: Unable to verify  covered company insurer ";
					$s = 	select_sql($sql, $placeholders, $error, $pdo);
					/*if($s->rowCount() > 0){
						foreach($s as $row ){
							//echo "x$row[insurer_id]";
							if($row['insurer_id']!=$_SESSION['type']){
								$insurer_name=html($row['name']);
								$_SESSION['insurance_mismatch_error']="This patient's profile shows the insurer as $_SESSION[type_name] and the corporate as $_SESSION[company_covered_name], but that corporate is insured by  $insurer_name. Please fix this mismatch to proceed";
							}
						}
					}
					else{
					
						//$_SESSION['insurance_mismatch_error']="This patient's profile shows the insurer as $_SESSION[type_name] and the corporate as $_SESSION[company_covered_name], but that corporate is not insured. Please fix this mismatch to proceed";
							
					}
							$found_insurer=$insurer_name='';
						foreach($s as $row ){
							$insurer_name=html($row['name']);
							$found_insurer=$row['insurer_id'];
						}
							//echo "x$row[insurer_id]";
							if($found_insurer!=$_SESSION['type']){
								if($_SESSION['type_name']==''){$_SESSION['type_name']=' not set ';}
								if($insurer_name==''){
									
									$_SESSION['insurance_mismatch_error']="This patient's profile shows the insurer is 
										$_SESSION[type_name] and the corporate is $_SESSION[company_covered_name], but 
											that corporate is not insured. Please fix this mismatch to proceed";
									}
								else{
									$_SESSION['insurance_mismatch_error']="This patient's profile shows the insurer is 
										$_SESSION[type_name] and the corporate is $_SESSION[company_covered_name], 
										but that corporate is insured by  $insurer_name. Please fix this mismatch to proceed";
								}
								if($_SESSION['type_name']==' not set '){$_SESSION['type_name']='';}
								//$_SESSION['insurance_mismatch_error']="This patient's profile shows the insurer as $_SESSION[type_name] and the corporate as $_SESSION[company_covered_name], but that corporate is insured by  $insurer_name. Please fix this mismatch to proceed";
							}
				}*/

	//get patient details b
	$sql=$error=$s='';$placeholders=array();		
	$sql="select * from patient_details_b where pid=:pid";
	$placeholders[':pid']=$_SESSION['pid'];
	$error="Error: Unable to get patient details b";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);	
	foreach($s as $row){
		$_SESSION['id_number']=html($row['id_number']);
		$_SESSION['address']=html($row['address']);
		$_SESSION['city']=html($row['city']);
		$_SESSION['occupation']=html($row['occupation']);
		$_SESSION['weight']=html($row['weight']);
		$_SESSION['dob']=html($row['dob']);
		$_SESSION['referee']=html($row['referee']);
		$_SESSION['referee_phone']=html($row['referee_phone']);
		$_SESSION['referee_email']=html($row['referee_email']);
		$_SESSION['em_contact']=html($row['em_contact']);
		$_SESSION['em_relationship']=html($row['em_relationship']);
		$_SESSION['em_phone']=html($row['em_phone']);
		$_SESSION['behalf_name']=html($row['behalf_name']);
		$_SESSION['behalf_relationship']=html($row['behalf_relationship']);
		$_SESSION['when_added']=html($row['when_added']);
		$_SESSION['gender']=html($row['gender']);
		$_SESSION['photo_path']=html($row['photo_path']);

		}
		
		//put pid in different session variable depending on where search is made from
	/*	if($_SESSION['tab_name'] == "#self_payments"){
			$_SESSION['pid2']=$_SESSION['pid'];
			$_SESSION['pid']='';
		}*/
		
		
	//	echo "bad#xxx";
		//check if this is only applicable to female patients
		if($_SESSION['gender']!='FEMALE' and $_SESSION['tab_name']="#female-patients"){
		//	return "bad#The patient searched for is MALE and therefore cannot have details in this section";
		}
	}
	else{$_SESSION['no_patient_found']="No such patient";$_SESSION['pid']='';}
}

//get photo extension
 function getExtension($str) {
         $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
 }
 
 
 
 
//upload photo 
function upload_photo($image_array){
	$_FILES['file']=$image_array;
	define ("MAX_SIZE","2048");
	error_reporting(0);
	$image = $_FILES["file"]["name"];
	$uploadedfile = $_FILES['file']['tmp_name'];

		if ($image) 
		{
		
			$filename = stripslashes($_FILES['file']['name']);
			$extension = getExtension($filename);
			$extension = strtolower($extension);
			
	 if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) 
			{
			
				//$error='Unknown Image extension $extension';
				//exit;
				return "ERRORsplitterUnknown Image extension $extension";
			}
			else
			{

	 $size=filesize($_FILES['file']['tmp_name']);


	if ($size > MAX_SIZE*1024)
	{
		//$error='You have exceeded the size limit of 400kb! ';
		//$exit;
		return "ERRORsplitterYou have exceeded the size limit of 2048kb! ";
	}
	if (!is_uploaded_file($_FILES['file']['tmp_name']) )
	{
	//$error = "Could not save file as $filename!";
	return "ERRORsplitterCould not save file as $filename! ";
	//exit();
	}

	if($extension=="jpg" || $extension=="jpeg" )
	{
	$uploadedfile = $_FILES['file']['tmp_name'];
	$src = imagecreatefromjpeg($uploadedfile);

	}
	else if($extension=="png")
	{
	$uploadedfile = $_FILES['file']['tmp_name'];
	$src = imagecreatefrompng($uploadedfile);

	}
	else 
	{
	$src = imagecreatefromgif($uploadedfile);
	}

	//echo $scr;

	list($width,$height)=getimagesize($uploadedfile);


	$newwidth=96;
	$newheight=96;
	$tmp=imagecreatetruecolor($newwidth,$newheight);

	//echo "105--$extension";
	$extension = ".$extension";
	imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
	//$last=sha1(md5(time() . $_SERVER['REMOTE_ADDR'] ));
	$last=form_token();
	$filename = "./profile/$last" .  $extension;

	$result = imagejpeg($tmp,$filename,100);
	//$result = rename("$filename", "/dental-images/profile/$last");
	imagedestroy($src);
	imagedestroy($tmp);
	return "GOODsplitter$filename";
	//echo "ndani filename ni $filename";
	}}
}


//this will show the pt balance for insurance, self, points
function show_pt_statement_brief($pdo,$pid,$encrypt){
	$pid=$encrypt->decrypt("$pid");
	
	//echo "pid is $pid";
	$transaction_array=array();
	//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
	//get payments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, a.receipt_num,a.amount,b.name,a.tx_number,a.invoice_id from payments a, payment_types b
	where a.pay_type=b.id and a.pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"Payment: $row[name] $row[receipt_num]", 'amount_value'=>$row['amount'], 
											'invoice_id'=>$row['invoice_id'], 'tx_type'=>1,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row['name']);
							
	}
	
	//get treatments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.date_procedure_added, a.teeth, a.details,a.unauthorised_cost,a.authorised_cost,a.invoice_number, 
	case a.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type,c.name,
			case a.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status, a.invoice_id,
			a.status as status_number, a.pay_type as pay_type_number
		from tplan_procedure a, procedures c
	where  a.pid=:pid and a.procedure_id=c.id";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient treatments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		//if pay type is cash check if it has been started
		if($row['status_number']==0 and $row['pay_type_number']==2 ){continue;} 
		$transaction_array[]=array('when_added'=>$row['date_procedure_added'], 'description'=>"$row[7] $row[teeth] $row[details] $row[8]", 'amount_value'=>'', 
											'invoice_id'=>$row['invoice_id'],'tx_type'=>2,'unauthorised_cost'=>$row['unauthorised_cost'],
											'authorised_cost'=>$row['authorised_cost'],'payment_type'=>"$row[6]");
							
	}	
	
	//get prescriptions
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details,c.name,a.cost,a.prescription_number
		from prescriptions a, drugs b, payment_types c
	where a.pay_type=c.id and a.pid=:pid and a.drug_id=b.id and a.pay_type=2";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient prescriptions ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"$row[2] $row[details]", 'amount_value'=>$row['cost'], 
											'invoice_id'=>'','tx_type'=>3,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row[3]);
							
	}	
	
	//get points earned
	$sql=$error=$s='';$placeholders=array();
	$sql="select date(time_allocated), TIMEDIFF( discharge_time, time_allocated ), points_per_min from patient_allocations
	where discharge_time!='0000-00-00 00:00:00' and pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to patient points ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$data=explode(':',"$row[1]");
		$points=(($data[0] * 60) + $data[1]) * $row['points_per_min'];
		$transaction_array[]=array('when_added'=>$row[0], 'description'=>"Loyalty Points", 'amount_value'=>$points, 
											'invoice_id'=>'','tx_type'=>4,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
							
	}
	
	//get moeny trasnfered to another family memeber
	//get credit transfered
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name, a.middle_name, a.last_name , b.amount, b.when_added from patient_details_a a, credit_transfer b where
			a.pid=b.receiver_pid and b.donor_pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to credit transfers ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"Credit Transfer to $row[first_name] $row[middle_name] $row[last_name]",
		'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>5,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
							
	}
	
	//get co-payment amount
	$sql=$error=$s='';$placeholders=array();
	//$sql="select a.amount, b.invoice_number , max(b.date_invoiced) as date_invoiced from co_payment as a join tplan_procedure as b on a.invoice_number=b.invoice_id and b.pid=:pid";
	$sql= "SELECT a.amount, b.invoice_number, b.when_raised AS date_invoiced FROM co_payment AS a
		JOIN unique_invoice_number_generator AS b ON a.invoice_number = b.id
		AND b.pid =:pid ";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get co_payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$transaction_array[]=array('when_added'=>$row['date_invoiced'], 'description'=>"Co-payment for invoice $row[invoice_number]",
		'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>6,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
	}
	
	if(count($transaction_array)==0){
		$_SESSION['ins_bal']=$_SESSION['self_bal']=$_SESSION['points_bal']=0;
		return "0#0#0";
	}
					//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
					$ins_debit=$ins_credit=$self_debit=$self_credit=$points_credit=$points_debit=0;
					foreach($transaction_array as $row){
						$date=html($row['when_added']);
						$description=html($row['description']);
						$amount_value=html($row['amount_value']);
						$invoice_id=html($row['invoice_id']);
						$tx_type=html($row['tx_type']);
						$unauthorised_cost=html($row['unauthorised_cost']);
						$authorised_cost=html($row['authorised_cost']);
						$payment_type=html($row['payment_type']);
						//payments
						if($tx_type==1){						
							//check if it is insurance payment
							if($invoice_id!=0){$ins_credit = $ins_credit + $amount_value;}
							elseif($invoice_id==0){
								//check if points or self
								if($payment_type!='Points'){$self_credit = $self_credit + $amount_value;}
								//elseif($payment_type=='Points'){$points_credit = $points_credit + $amount_value;}
							}
						}
						
						//treatments
						if($tx_type==2){
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance' and $invoice_id == 0){continue;}
													
							//check if it is insurance payment
							if($payment_type=='Insurance'){
								//check if authorised cost==unauthorised_cost
								if($authorised_cost==''){//$ins_debit = $ins_debit + $unauthorised_cost;
								}
								elseif($unauthorised_cost!=$authorised_cost){
									$ins_debit = $ins_debit + $authorised_cost;
									$self_debit = $self_debit + $unauthorised_cost - $authorised_cost;
								}
								elseif($unauthorised_cost==$authorised_cost){$ins_debit = $ins_debit + $authorised_cost;}
							}
							elseif($payment_type=='Self'){$self_debit = $self_debit + $authorised_cost;}
							elseif($payment_type=='Points'){$points_debit = $points_debit + $authorised_cost;}
						}
						
						//prescription
						if($tx_type==3){$self_debit = $self_debit + $amount_value;}

						//points
						if($tx_type==4){$points_credit = $points_credit + $amount_value;}
						
						//credit trasnfered
						if($tx_type==5){$self_debit = $self_debit + $amount_value;}	

						//co-payment
						if($tx_type==6){						
							$self_debit = $self_debit + $amount_value;
							$ins_credit = $ins_credit + $amount_value;
						}							
					}
					$ins_bal= $ins_debit -$ins_credit ;
					$self_bal= $self_debit - $self_credit;
					$points_bal= $points_debit - $points_credit;
					
					if($ins_bal!=''){$ins_bal=number_format($ins_bal,2);}
					if($self_bal!=''){$self_bal=number_format($self_bal,2);}
					if($points_bal!=''){$points_bal=number_format($points_bal,2);}
					
					$_SESSION['ins_bal']="$ins_bal";
					$_SESSION['self_bal']="$self_bal";
					$_SESSION['points_bal']="$points_bal";
					return "$ins_bal#$self_bal#$points_bal";
	
	
	
}

//this will check if the patient can pay treatment with points in t.plan and examination
function pay_treatment_in_points($pdo,$pid,$amount,$procedure_id){
	$message="good";
	
	//get points earned
	$points_used=$points_earned=0;
	$points_cost='';
	$sql=$error=$s='';$placeholders=array();
	$sql="select  TIMEDIFF( discharge_time, time_allocated ), points_per_min from patient_allocations
	where discharge_time!='0000-00-00 00:00:00' and pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to patient points ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$data=explode(':',"$row[0]");
		$points_earned=(($data[0] * 60) + $data[1]) * $row['points_per_min'];
							
	}
	
	//points used so far
	$sql=$error=$s='';$placeholders=array();
	$sql="select  sum(authorised_cost) from tplan_procedure where pid=:pid and pay_type=3 group by pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to patient points used";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$points_used=html($row[0]);
							
	}
	
	if(($points_earned - $points_used) < $amount){
		$message= "bad#Unable to save treatment plan as loyalty points used  exceed the availlable balance. ";
	}
	
	//get procedure name
	if($message=='good'){
		$sql=$error=$s='';$placeholders=array();
		$sql="select   name from  procedures  where id=:procedure_id";
		$placeholders[':procedure_id']=$procedure_id;
		$error="Error: Unable to procedure name";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			foreach($s as $row){$name=html($row['name']);}
		}
		else{
			$message= "bad#A treatment procedure has not been found. ";
		}
	}
	
	//check if points are enough for the treatment
	if($message=='good'){
		$sql=$error=$s='';$placeholders=array();
		$sql="select  points, name from procedures_in_points_scheme a, procedures b where procedure_id=:procedure_id
			and b.id=procedure_id";
		$placeholders[':procedure_id']=$procedure_id;
		$error="Error: Unable to procedure points";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			foreach($s as $row){
				$points_cost=html($row['points']);		
				$name=html($row['name']);
			}
			if($points_cost != $amount){
				$amount=html("$amount");
				$message= "bad#Unable to save treatment plan as $name costs $points_cost loyalty points but $amount points are specified. ";
			}
		}
	else{$message= "bad#Procedure $name is not in the loyalty points program. ";}
	}
	
	return "$message";


}

//thsi will get smtp details
function get_smtp_details($pdo){
		$sql=$error=$s='';$placeholders=array();
		$sql="select  * from smtp_credentials";
		$error="Error: Unable to get smtp details";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$_SESSION['smtp_host']=html($row['smtp_host']);
			$_SESSION['smtp_username']=html($row['smtp_user_name']);
			$_SESSION['smtp_password']=html($row['smtp_password']);
			$_SESSION['smtp_from_email_address']=html($row['email_address']);
			$_SESSION['smtp_from_name']=html($row['from_name']);
		}
}

//thsi will get user notifications
function get_user_notifications($pdo){
	$notifications=array();
	$actions=$desc=array();
	$sql=$error=$s='';$placeholders=array();		
	//if user is approver/doctor then show only the follow up request that he started
	$user_type=0;
	$user_criteria=' and b.follow_up_date <= curdate() and b.pending=0  ';
	if(userHasSubRole($pdo,9)){
		$user_type=1;
		$placeholders['created_by']=$_SESSION['id'];
		$user_criteria= " and b.created_by=:created_by and b.follow_up_date < curdate()";
	}			
	
	//GET ANY PENDING follow ups			
	$sql="select b.id from follow_ups b  where b.status=0 $user_criteria ";					
	$error="Unable to get follows up due today";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$notifications[] = "You have ".$s->rowCount() ." follow ups pending today ";
		$actions[]= '?id=follow-ups';
		$desc[]='Check Follow Ups';
	}
	
	//get pending reminders
	$sql=$error=$s='';$placeholders=array();
	$sql="select id from reminders where created_by=:created_by and approved='no' and reminder_date <= now()";
	$error="Unable to select reminders";
	$placeholders[':created_by']=$_SESSION['id'];
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount() > 0){
		$notifications[] = "You have ".$s->rowCount() ." reminders pending today ";
		$actions[]= '?id=reminders';
		$desc[]='Check Reminders';
	}
	
	//show notifications
	if(count($notifications) > 0){
		$caption=strtoupper("User Notifications");
		echo "<table class='half_width move_a_bit'><caption>$caption</caption><thead>
		<tr><th class=ntf_count></th><th class=ntf_desc>NOTIFICATION</th><th class=ntf_action>ACTION</th></tr>
		</thead><tbody>";
		$n=count($notifications);
		$i=0;
		$count=1;
		while($i < $n){
			echo "<tr><td>$count</td><td>$notifications[$i]</td><td><a href=$actions[$i]>$desc[$i]</a></td></tr>";
			$i++;
			$count++;
		}
		echo "</tbody></table>";
	}
}

function send_email($mail , $to_email_address ,$to_email_name, $subject, $body, $pid){

	//$mail->SMTPDebug = 1;
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = $_SESSION['smtp_host'];  // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = $_SESSION['smtp_username'];                            // SMTP username
	$mail->Password = $_SESSION['smtp_password'];                           // SMTP password
	//$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

	$mail->From = $_SESSION['smtp_from_email_address'];
	$mail->FromName = $_SESSION['smtp_from_name'];
	if($to_email_address!='')	
	{
		if(phpMailer::ValidateAddress($to_email_address)) {
			$mail->addAddress("$to_email_address", "$to_email_name");  // Add a recipient
		}
		else{return "$to_email_address is not a valid email address";}
	}
	
	/*if($to_email_address2!='') {
		if(phpMailer::ValidateAddress($to_email_address2)) {
			$mail->addAddress("$to_email_address2", "$to_email_name");  // Add a recipient
		}
		else{return "$to_email_address2 is not a valid email address";}
	}*/
	//$mail->addAddress('ellen@example.com');               // Name is optional
	//$mail->addReplyTo('test@molars.co.ke', 'test user');
	//$mail->addCC('cc@example.com');
	//$mail->addBCC('bcc@example.com');

	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	$mail->isHTML(true);                                  // Set email format to HTML

	$mail->Subject = "$subject";
	$mail->Body    = "$body";
	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	if(!$mail->send()) {
	   	// Clear all addresses and attachments for next loop

		return 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
	   exit;
	}
	// Clear all addresses and attachments for next loop
    $mail->ClearAllRecipients();
	$mail->ClearReplyTos();
	$mail->ClearAttachments();
	return 'good';

}

//this will create bstatement for email
function email_pt_statement($pdo,$pid,$encrypt){
	//$pid=$encrypt->decrypt("$pid");
	//echo "pid is $pid";
	$transaction_array=array();
	$ceil_total=0;
	//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
	//type 5 is credit transfered
	//get payments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, a.receipt_num,a.amount,b.name,a.tx_number,a.invoice_id from payments a, payment_types b
	where a.pay_type=b.id and a.pid=:pid and a.pay_type!=8";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$donor_name='';
		//check if credit transfer and get donors name
		if($row['name']=='Credit Transfer'){
			//GET DONORS NAME
			$sql2=$error2=$s2='';$placeholders2=array();
			$sql2="select first_name, middle_name, last_name , patient_number from patient_details_a where pid=:pid";
			$placeholders2[':pid']=$row['tx_number'];
			$error2="Error: Unable to get donor patient details ";
			$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
			foreach($s2 as $row2){$donor_name=html("from $row2[first_name] $row2[middle_name] $row2[last_name] - $row2[patient_number]");}
		}
		$ceil_var=ceil(strlen("Payment: $row[name] $row[receipt_num] $donor_name") / 38);
		$transaction_array[]=array('when_added'=>"$row[when_added]end$row[tx_number]", 'description'=>"Payment: $row[name] $row[receipt_num] $donor_name", 'amount_value'=>$row['amount'], 
											'invoice_id'=>$row['invoice_id'], 'tx_type'=>1,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row['name'],'ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;					
	}
	
	//get treatments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.date_procedure_added, a.teeth, a.details,a.unauthorised_cost,a.authorised_cost,a.invoice_number, 
	case a.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type,c.name,
			case a.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status, a.invoice_id
		from tplan_procedure a, procedures c
	where  a.pid=:pid and a.procedure_id=c.id";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient treatments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$inv_num='';
		if($row['invoice_number']!=''){
			$inv_num=" - $row[invoice_number]";
		}
		//format x-ray
		if($row[7] == 'X-Ray'){$treatment="$row[details] $row[teeth]";}
		else{$treatment="$row[7] $row[teeth] $row[details]";}
		$ceil_var=ceil(strlen("$treatment $row[8] $inv_num") / 38);
		$transaction_array[]=array('when_added'=>$row['date_procedure_added'], 'description'=>"$treatment $row[8] $inv_num", 'amount_value'=>'', 
											'invoice_id'=>$row['invoice_id'],'tx_type'=>2,'unauthorised_cost'=>$row['unauthorised_cost'],
											'authorised_cost'=>$row['authorised_cost'],'payment_type'=>"$row[6]",'ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;							
	}	
	
	//get prescriptions
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details,c.name,a.cost,a.prescription_number
		from prescriptions a, drugs b, payment_types c
	where a.pay_type=c.id and a.pid=:pid and a.drug_id=b.id and a.pay_type=2";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient prescriptions ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$ceil_var=ceil(strlen("$row[2] $row[details]") / 38);
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"$row[2] $row[details]", 'amount_value'=>$row['cost'], 
											'invoice_id'=>'','tx_type'=>3,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row[3],'ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;							
	}	
	
	//get points earned
	$sql=$error=$s='';$placeholders=array();
	$sql="select date(time_allocated), TIMEDIFF( discharge_time, time_allocated ), points_per_min from patient_allocations
	where discharge_time!='0000-00-00 00:00:00' and pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to patient points ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$data=explode(':',"$row[1]");
		$points=(($data[0] * 60) + $data[1]) * $row['points_per_min'];
		$ceil_var=ceil(strlen("Loyalty Points") / 38);
		$transaction_array[]=array('when_added'=>$row[0], 'description'=>"Loyalty Points", 'amount_value'=>$points, 
											'invoice_id'=>'','tx_type'=>4,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'','ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;							
	}

	//get credit transfered
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name, a.middle_name, a.last_name , a.patient_number, b.amount, b.when_added from patient_details_a a, credit_transfer b where
			a.pid=b.receiver_pid and b.donor_pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to credit transfers ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$ceil_var=ceil(strlen("Credit Transfer to $row[first_name] $row[middle_name] $row[last_name] - $row[patient_number]") / 38);
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"Credit Transfer to $row[first_name] $row[middle_name] $row[last_name] - $row[patient_number]",
		'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>5,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'','ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;							
	}
	
	
	//get co-payment amount
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.amount, b.invoice_number , max(b.date_invoiced) as date_invoiced from co_payment as a join tplan_procedure as b on a.invoice_number=b.invoice_id and b.pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get co_payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['amount'] > 0){
		//echo "-- $row[amount]--  $row[invoice_number]  -- $row[2] --";
		$ceil_var=ceil(strlen("Co-payment for invoice $row[invoice_number]") / 38);
			$transaction_array[]=array('when_added'=>$row['date_invoiced'], 'description'=>"Co-payment for invoice $row[invoice_number]",
			'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>6,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'','ceil_var'=>$ceil_var);
		$ceil_total = $ceil_total + $ceil_var;		
		}
	}
	
	if(count($transaction_array)==0){return;}
	//sort array
	// Obtain a list of columns
	foreach ($transaction_array as $key => $row) {
		$when_added[$key]  = $row['when_added'];
		//$edition[$key] = $row['edition'];
	}

	// Sort the data with when_added
	array_multisort($when_added, SORT_ASC, $transaction_array);
	//append array with total value as last value
	$transaction_array[]=array('when_added'=>'last', 'description'=>'last','amount_value'=>'last','invoice_id'=>'last','tx_type'=>'last',
		'unauthorised_cost'=>'last','authorised_cost'=>'last','payment_type'=>'last','ceil_var'=>$ceil_total);
		
	return $transaction_array;
	exit;
	if(count($transaction_array)>0){
					$var='';
					$var="$var<table class='normal_table statement_tb' id=statement_tb><thead><tr><th  bgcolor='#121923' color='#B0B3B6' class=st_date>DATE</th>";
					$var="$var<th bgcolor='#121923' color='#B0B3B6' class=st_tx>TRANSACTION DESCRIPTION</th>";
					$var="$var<th class=st_deb>INSURANCE DEBIT</th><th class=st_cred>INSURANCE CREDIT</th>";
					$var="$var<th class=st_deb>SELF DEBIT</th><th class=st_cred>SELF CREDIT</th>";
					$var="$var<th class=st_deb>POINTS DEBIT</th><th class=st_cred>POINTS CREDIT</th><tr></thead><tbody>";
					//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
					$ins_debit=$ins_credit=$self_debit=$self_credit=$points_credit=$points_debit=0;
					foreach($transaction_array as $row){
						$date=html($row['when_added']);
						$description=html($row['description']);
						$amount_value=html($row['amount_value']);
						$invoice_id=html($row['invoice_id']);
						$tx_type=html($row['tx_type']);
						$unauthorised_cost=html($row['unauthorised_cost']);
						$authorised_cost=html($row['authorised_cost']);
						$payment_type=html($row['payment_type']);
						//payments
						if($tx_type==1){
							$data=explode('end',"$date");
							
							$donor_name='';
							//check if credit transfer and get donors name
							if($payment_type=='Credit Transfer'){
								//GET DONORS NAME
								$sql="select first_name, middle_name, last_name , patient_number from patient_details_a where pid=:pid";
								$placeholders[':pid']=$data[1];
								$error="Error: Unable to get donor patient details ";
								$s = 	select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){$donor_name=html("from $row[first_name] $row[middle_name] $row[last_name] - $row[patient_number]");}
							}
							
							$var="$var<tr><td bgcolor='#121923' color='#B0B3B6'>$data[0]</td><td bgcolor='#121923' color='#B0B3B6'>$description $donor_name</td>";
							//check if it is insurance payment
							if($invoice_id!=0){
								$var="$var <td bgcolor='#A0D1E0' color='#000000' width='88px' font-weight='700'>&nbsp;</td><td  bgcolor='#A0D1E0' color='#000000' width='88px' font-weight='700'>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								$ins_credit = $ins_credit + $amount_value;
							}
							elseif($invoice_id==0){
								//check if points or self
								if($payment_type!='Points'){
									$var="$var <tdbgcolor='#A0D1E0' color='#000000' width='88px' font-weight='700'>&nbsp;</td><tdbgcolor='#A0D1E0' color='#000000' width='88px' font-weight='700'>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$self_credit = $self_credit + $amount_value;
								}
								/*elseif($payment_type=='Points'){
									echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td></tr>";
									$points_debit = $points_debit + $authorised_cost;
								}*/
							}
						}
						
						//treatments
						if($tx_type==2){
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance' and $invoice_id == 0){continue;}
							$var="$var <tr><td bgcolor='#121923' color='#B0B3B6' >$date</td><td bgcolor='#121923' color='#B0B3B6' >$description</td>";
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance'){
								//check if authorised cost==unauthorised_cost
								if($authorised_cost==''){
									$var="$var <td  bgcolor=#A0D1E0; color=#000000;>Un-authorised</td><td  bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									//$ins_debit = $ins_debit + $unauthorised_cost;
								}
								elseif($unauthorised_cost!=$authorised_cost){
									$var="$var <td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>".number_format(($unauthorised_cost - $authorised_cost),2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$ins_debit = $ins_debit + $authorised_cost;
									$self_debit = $self_debit + $unauthorised_cost - $authorised_cost;
								}
								elseif($unauthorised_cost==$authorised_cost){
									$var="$var <td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$ins_debit = $ins_debit + $authorised_cost;
								}
							}
							elseif($payment_type=='Self'){
								$var="$var <td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								$self_debit = $self_debit + $authorised_cost;
							}
							elseif($payment_type=='Points'){
								$var="$var <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td></tr>";
								$points_debit = $points_debit + $authorised_cost;
							}
						}
						
						//prescription
						if($tx_type==3){						
							$var="$var <tr><td bgcolor='#121923' color='#B0B3B6' >$date</td><td bgcolor='#121923' color='#B0B3B6' >$description</td>";
							$var="$var <td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
						}
		
						//points
						if($tx_type==4){						
							$var="$var <tr><td bgcolor='#121923' color='#B0B3B6' >$date</td><td bgcolor='#121923' color='#B0B3B6' >$description</td>";
							$var="$var <td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td></tr>";
							$points_credit = $points_credit + $amount_value;
						}
						
						//credit trasnfered
						if($tx_type==5){						
							$var="$var <tr><td bgcolor='#121923' color='#B0B3B6' >$date</td><td bgcolor='#121923' color='#B0B3B6' >$description</td>";
							$var="$var <td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
						}						
						
						//co-payment
						if($tx_type==6){						
							$var="$var <tr><td bgcolor='#121923' color='#B0B3B6' >$date</td><td bgcolor='#121923' color='#B0B3B6' >$description</td>";
							$var="$var <td bgcolor=#A0D1E0; color=#000000;>&nbsp;</td><td bgcolor=#A0D1E0; color=#000000;>".number_format($amount_value,2)."</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
							$ins_credit = $ins_credit + $amount_value;
						}							
					}
					$var="$var <tr class='totals'><td  bgcolor='#121923' color='#B0B3B6' colspan=2>TOTALS</td><td class=bal_ins>".number_format($ins_debit,2)."</td><td>".number_format($ins_credit,2)."</td>
					<td id=self_bal1>".number_format($self_debit,2)."</td><td>".number_format($self_credit,2)."</td>
					<td id=points_bal1>".number_format($points_debit,2)."</td><td>".number_format($points_credit,2)."</td></tr>";
					$ins_bal= $ins_debit -$ins_credit ;
					$self_bal= $self_debit - $self_credit;
					$points_bal= $points_debit - $points_credit;
					
					if($ins_bal!=''){$ins_bal=number_format($ins_bal,2);}
					if($self_bal!=''){$self_bal=number_format($self_bal,2);}
					if($points_bal!=''){$points_bal=number_format($points_bal,2);}
					
					$var="$var <tr id='totals2'><td  bgcolor='#121923' color='#B0B3B6' colspan=2>BALANCE</td><td colspan=2 class=bal_ins>$ins_bal</td><td colspan=2 id=self_bal2>$self_bal</td><td colspan=2 id=points_bal2>$points_bal</td></tr>";
					$var="$var </tbody></table>";	
					return "$var"; 
	}
	
	
}

	//get treatment done
	function get_treatments_done($pdo, $pid, $encrypt){
		$pid_clean=$encrypt->decrypt("$pid");
		//get the patients names
		$sql=$error=$s='';$placeholders=array();
		$sql="select first_name,middle_name,last_name, patient_number from patient_details_a where pid=:pid ";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get patient names for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		
		foreach($s as $row){
						$last_name=ucfirst(html($row['last_name']));
						$middle_name=ucfirst(html($row['middle_name']));
						$first_name=ucfirst(html($row['first_name']));
						$patient_number=html($row['patient_number']);
						
					}
		//get procedures for this treatment plan
		$sql=$error=$s='';$placeholders=array();
		$sql="select b.treatment_procedure_id, a.name, b.teeth, b.details ,invoice_number, unauthorised_cost, authorised_cost, quotation_number,
		null,
		case b.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status ,
		case b.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type, b.date_procedure_added,
		concat(c.first_name,' ',c.middle_name,' ',c.last_name) as doctor_name
		from tplan_procedure as b join procedures as a on b.procedure_id=a.id 
		left join users as c on c.id=b.created_by where b.pid=:pid order by b.treatment_procedure_id";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get treatments for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount()>0){  
				
				echo "<table class='normal_table ecr1'><caption>Treatments Done for $first_name $middle_name $last_name - $patient_number</caption><thead><th class='treat_procedure_date3'>DATE<br>PLANNED</th>
					<th class=treat_planned_by3>PLANNED BY</th>
					<th class=treat_procedure3>PROCEDURE</th>
					<th class=treat_payment_method3>PAYMENT<br>METHOD</th><th class=treat_unaothorised_cost3>COST</th>
					<th class=treat_auothorised_cost3>AUTHORISED<br>COST</th>
					<th class='treat_notes_cell3 td_div_holder'>
						<div class='tplan_table 100_height'>
							<div class='tplan_table_row2'>
								<div class='treat_date 100_height'>DATE</div>
								<div class='treat_doctor'>DOCTOR</div>
								<div class='treat_notes'>NOTE</div>
							</div>
						</div>
					</th>
					<th class=treat_status3>STATUS</th></thead><tbody>";
			$i1=0;
			foreach($s as $row){
				$i1++;
				echo "<tr class=has_css_div>";?>
					<td><?php htmlout($row['date_procedure_added']);?> </td>
					<td><?php ucfirst(htmlout("$row[doctor_name]"));?></td>
					<td><?php 
							if($row['name']=='X-Ray'){htmlout("$row[details] $row[teeth]");}
							else {
								htmlout("$row[name] $row[teeth]");
								if ($row['details']!=''){echo "<br>";htmlout($row['details']); }
							}
					?></td>
					<td><?php htmlout($row['pay_type']);  ?> </td>
					<td><?php htmlout(number_format($row['unauthorised_cost'],2));  ?> </td>
					<td><?php
						if($row['pay_type']!='Insurance'){echo "N/A";}  
						elseif($row['authorised_cost']==''){echo "Un-Authorised";}
						elseif($row['authorised_cost']!=''){htmlout(number_format($row['authorised_cost']));}
							//echo $row['authorised_cost'];}
					?> </td>
					
					 <?php
					//now show the procedure doctore notes
					$sql2=$error2=$s2='';$placeholders2=array();
					$sql2="select b.when_added, concat(a.first_name,' ',a.middle_name,' ',a.last_name) as user_name1, b.notes from treatment_procedure_notes b, users a where b.treatment_procedure_id=:treatment_procedure_id
						   and b.doc_id=a.id order by b.id";
					$placeholders2[':treatment_procedure_id']=$row['treatment_procedure_id'];
					$error2="Unable to get unfinished  procedure doctor notes";
					$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);					
					echo "<td class=td_div_holder>";	
						$i2=0;
						foreach($s2 as $row2){ 
							$date1=html($row2['when_added']);
							$user_name=ucfirst(html($row2['user_name1']));
							$notes=html($row2['notes']);
							$i2_class='';
							if($i2==0){$i2_class='no_top_border';}
							
							echo "<div class='tplan_table'>
									<div class='tplan_table_row2 div_in_td_1'>
										<div class='treat_date $i2_class'>$date1</div>
										<div class='treat_doctor $i2_class'>$user_name</div>
										<div class='treat_notes $i2_class'>$notes</div>
									</div>
								</div>";	
							$i2++;
						}
					?>
					</td>	
					<td><?php htmlout($row['status']);?></td>

					</tr><!-- end tplan_table_row -->
			<?php }		
			echo "</tbody></table>";
		
		} 
		//look for older swapped patient number
		$sql=$error=$s='';$placeholders=array();
		$sql="select old_pid from swapped_patients where new_pid=:pid ";
		$placeholders[':pid']=$pid_clean;
		$error="Unable to get old patient number for patient";
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		if($s->rowCount() > 0){
			foreach($s as $row){$pid_clean=html($row['old_pid']);}		
			get_treatments_done($pdo, $pid_clean, $encrypt);
		}
	}


//this will show the pt statement
function show_pt_statement($pdo,$pid,$encrypt){
	$pid=$encrypt->decrypt("$pid");
	//echo "pid is $pid";
	$transaction_array=array();
	//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
	//type 5 is credit transfered
	//get payments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, a.receipt_num,a.amount,b.name,a.tx_number,a.invoice_id from payments a, payment_types b
	where a.pay_type=b.id and a.pid=:pid and a.pay_type!=8";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		
		$transaction_array[]=array('when_added'=>"$row[when_added]end$row[tx_number]", 'description'=>"Payment: $row[name] $row[receipt_num]", 'amount_value'=>$row['amount'], 
											'invoice_id'=>$row['invoice_id'], 'tx_type'=>1,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row['name']);
							
	}
	
	//get treatments
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.date_procedure_added, a.teeth, a.details,a.unauthorised_cost,a.authorised_cost,a.invoice_number, 
	case a.pay_type when '1' then 'Insurance' when '2' then 'Self' when '3' then 'Points'	end as pay_type,c.name,
			case a.status when '0' then 'Not Started' when '1' then 'Partially Done' when '2' then 'Done'	end as status, a.invoice_id,
			a.status as status_number, a.pay_type as pay_type_number
		from tplan_procedure a, procedures c
	where  a.pid=:pid and a.procedure_id=c.id";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient treatments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		//if pay type is cash check if it has been started
		if($row['status_number']==0 and $row['pay_type_number']==2 ){continue;} 
		$inv_num='';
		if($row['invoice_number']!=''){
			$inv_num=" - $row[invoice_number]";
		}
		//format x-ray
		if($row[7] == 'X-Ray'){$treatment="$row[details] $row[teeth]";}
		else{$treatment="$row[7] $row[teeth] $row[details]";}
		$transaction_array[]=array('when_added'=>$row['date_procedure_added'], 'description'=>"$treatment $row[8] $inv_num", 'amount_value'=>'', 
											'invoice_id'=>$row['invoice_id'],'tx_type'=>2,'unauthorised_cost'=>$row['unauthorised_cost'],
											'authorised_cost'=>$row['authorised_cost'],'payment_type'=>"$row[6]");
							
	}	
	
	//get prescriptions
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.when_added, b.name, a.details,c.name,a.cost,a.prescription_number
		from prescriptions a, drugs b, payment_types c
	where a.pay_type=c.id and a.pid=:pid and a.drug_id=b.id and a.pay_type=2";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get patient prescriptions ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"$row[2] $row[details]", 'amount_value'=>$row['cost'], 
											'invoice_id'=>'','tx_type'=>3,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>$row[3]);
							
	}	
	
	//get points earned
	$sql=$error=$s='';$placeholders=array();
	$sql="select date(time_allocated), TIMEDIFF( discharge_time, time_allocated ), points_per_min from patient_allocations
	where discharge_time!='0000-00-00 00:00:00' and pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to patient points ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		$data=explode(':',"$row[1]");
		$points=(($data[0] * 60) + $data[1]) * $row['points_per_min'];
		$transaction_array[]=array('when_added'=>$row[0], 'description'=>"Loyalty Points", 'amount_value'=>$points, 
											'invoice_id'=>'','tx_type'=>4,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
							
	}

	//get credit transfered
	$sql=$error=$s='';$placeholders=array();
	$sql="select a.first_name, a.middle_name, a.last_name , a.patient_number, b.amount, b.when_added from patient_details_a a, credit_transfer b where
			a.pid=b.receiver_pid and b.donor_pid=:pid";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to credit transfers ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		
		$transaction_array[]=array('when_added'=>$row['when_added'], 'description'=>"Credit Transfer to $row[first_name] $row[middle_name] $row[last_name] - $row[patient_number]",
		'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>5,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
							
	}
	
	
	//get co-payment amount
	$sql=$error=$s='';$placeholders=array();
	//$sql="select a.amount, b.invoice_number , max(b.date_invoiced) as date_invoiced from co_payment as a 
	//join tplan_procedure as b on a.invoice_number=b.invoice_id and b.pid=:pid";
	$sql= "SELECT a.amount, b.invoice_number, b.when_raised AS date_invoiced FROM co_payment AS a
		JOIN unique_invoice_number_generator AS b ON a.invoice_number = b.id
		AND b.pid =:pid ";
	$placeholders[':pid']=$pid;
	$error="Error: Unable to get co_payments ";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	foreach($s as $row){
		if($row['amount'] > 0){
		//echo "-- $row[amount]--  $row[invoice_number]  -- $row[2] --";
			$transaction_array[]=array('when_added'=>$row['date_invoiced'], 'description'=>"Co-payment for invoice $row[invoice_number]",
			'amount_value'=>$row['amount'],'invoice_id'=>'','tx_type'=>6,'unauthorised_cost'=>'','authorised_cost'=>'','payment_type'=>'');
		}
	}
	
	if(count($transaction_array)==0){return;}
	//sort array
	// Obtain a list of columns
	foreach ($transaction_array as $key => $row) {
		$when_added[$key]  = $row['when_added'];
		//$edition[$key] = $row['edition'];
	}

	// Sort the data with when_added
	array_multisort($when_added, SORT_ASC, $transaction_array);

	if(count($transaction_array)>0){
	
					echo "<table class='normal_table statement_tb' id=statement_tb>	
		<col />
		<col />
		<col />
		<col />
		<col />
		<col />
		<col />
		<col />
	
<thead>
					<tr><th class=st_date>DATE</th><th class=st_tx>TRANSACTION DESCRIPTION</th>
					<th class=st_deb>INSURANCE DEBIT</th><th class=st_cred>INSURANCE CREDIT</th>
					<th class=st_deb>SELF DEBIT</th><th class=st_cred>SELF CREDIT</th>
					<th class=st_deb>POINTS DEBIT</th><th class=st_cred>POINTS CREDIT</th>
					<tr></thead><tbody>";
					//tx_type 1 is payment made, 2 is treatment done, 3 is prescription made, 4 is loyalty point earned
					$ins_debit=$ins_credit=$self_debit=$self_credit=$points_credit=$points_debit=0;
					foreach($transaction_array as $row){
						$date=html($row['when_added']);
						$description=html($row['description']);
						$amount_value=html($row['amount_value']);
						$invoice_id=html($row['invoice_id']);
						$tx_type=html($row['tx_type']);
						$unauthorised_cost=html($row['unauthorised_cost']);
						$authorised_cost=html($row['authorised_cost']);
						$payment_type=html($row['payment_type']);
						//payments
						if($tx_type==1){
							$data=explode('end',"$date");
							
							$donor_name='';
							//check if credit transfer and get donors name
							if($payment_type=='Credit Transfer'){
								//GET DONORS NAME
								$sql="select first_name, middle_name, last_name , patient_number from patient_details_a where pid=:pid";
								$placeholders[':pid']=$data[1];
								$error="Error: Unable to get donor patient details ";
								$s = 	select_sql($sql, $placeholders, $error, $pdo);
								foreach($s as $row){$donor_name=html("from $row[first_name] $row[middle_name] $row[last_name] - $row[patient_number]");}
							}
							
							echo "<tr><td>$data[0]</td><td>$description $donor_name</td>";
							//check if it is insurance payment
							if($invoice_id!=0){
								echo "<td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								$ins_credit = $ins_credit + $amount_value;
							}
							elseif($invoice_id==0){
								//check if points or self
								if($payment_type!='Points'){
									echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$self_credit = $self_credit + $amount_value;
								}
								/*elseif($payment_type=='Points'){
									echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td></tr>";
									$points_debit = $points_debit + $authorised_cost;
								}*/
							}
						}
						
						//treatments
						if($tx_type==2){
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance' and $invoice_id == 0){continue;}
							echo "<tr><td>$date</td><td>$description</td>";
							//check if it is insurance payment and treatment is invoiced
							if($payment_type=='Insurance'){
								//check if authorised cost==unauthorised_cost
								if($authorised_cost==''){
									echo "<td >Un-authorised</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									//$ins_debit = $ins_debit + $unauthorised_cost;
								}
								elseif($unauthorised_cost!=$authorised_cost){
									echo "<td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>".number_format(($unauthorised_cost - $authorised_cost),2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$ins_debit = $ins_debit + $authorised_cost;
									$self_debit = $self_debit + $unauthorised_cost - $authorised_cost;
								}
								elseif($unauthorised_cost==$authorised_cost){
									echo "<td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
									$ins_debit = $ins_debit + $authorised_cost;
								}
							}
							elseif($payment_type=='Self'){
								echo "<td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
								$self_debit = $self_debit + $authorised_cost;
							}
							elseif($payment_type=='Points'){
								echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($authorised_cost,2)."</td><td>&nbsp;</td></tr>";
								$points_debit = $points_debit + $authorised_cost;
							}
						}
						
						//prescription
						if($tx_type==3){						
							echo "<tr><td>$date</td><td>$description</td>";
							echo "<td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
						}
		
						//points
						if($tx_type==4){						
							echo "<tr><td>$date</td><td>$description</td>";
							echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td></tr>";
							$points_credit = $points_credit + $amount_value;
						}
						
						//credit trasnfered
						if($tx_type==5){						
							echo "<tr><td>$date</td><td>$description</td>";
							echo "<td>&nbsp;</td><td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
						}						
						
						//co-payment
						if($tx_type==6){						
							echo "<tr><td>$date</td><td>$description</td>";
							echo "<td>&nbsp;</td><td>".number_format($amount_value,2)."</td><td>".number_format($amount_value,2)."</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
							$self_debit = $self_debit + $amount_value;
							$ins_credit = $ins_credit + $amount_value;
						}							
					}
					echo "<tr class='totals'><td colspan=2>TOTALS</td><td class=bal_ins>".number_format($ins_debit,2)."</td><td>".number_format($ins_credit,2)."</td>
					<td id=self_bal1>".number_format($self_debit,2)."</td><td>".number_format($self_credit,2)."</td>
					<td id=points_bal1>".number_format($points_debit,2)."</td><td>".number_format($points_credit,2)."</td></tr>";
					$ins_bal= $ins_debit -$ins_credit ;
					$self_bal= $self_debit - $self_credit;
					$points_bal= $points_debit - $points_credit;
					
					if($ins_bal!=''){$ins_bal=number_format($ins_bal,2);}
					if($self_bal!=''){$self_bal=number_format($self_bal,2);}
					if($points_bal!=''){$points_bal=number_format($points_bal,2);}
					
					echo "<tr id='totals2'><td colspan=2>BALANCE</td><td colspan=2 class=bal_ins>$ins_bal</td><td colspan=2 id=self_bal2>$self_bal</td><td colspan=2 id=points_bal2>$points_bal</td></tr>";
					echo "</tbody></table>";	
	}
	
	
}

//this will show the basic patient details without the option to search afresh
function get_patient_basics($pdo,$pid,$encrypt){
			$sql="select * from patient_details_a where pid=:pid";
			$placeholders[':pid']=$pid;
			$error="Error: Unable to get patient details ";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount()>0){
				foreach($s as $row){
					$last_name=ucfirst(html($row['last_name']));
					$middle_name=ucfirst(html($row['middle_name']));
					$first_name=ucfirst(html($row['first_name']));
					$type=html($row['type']);
					$patient_number=html($row['patient_number']);
					$pid_clean=html($row['pid']);
					$pid=$encrypt->encrypt(html($row['pid']));
					
					$member_no=html($row['member_no']);
					$company_covered=html($row['company_covered']);
					$family_id=html($row['family_id']);
					$family_title=html($row['family_title']);
					$insurance_cover_role=html($row['insurance_cover_role']);
					
				}
					//get company_covered_name and type_name
					$company_covered_name=$type_name='';
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from covered_company where id=:covered_company";
					$placeholders2[':covered_company']=$company_covered;
					$error2="Error: Unable to get covered company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$company_covered_name=html($row2['name']);}
					
					$sql2=$error2=$s2='';$placeholders2=array();	
					$sql2="select name from insurance_company where id=:type";
					$placeholders2[':type']=$type;
					$error2="Error: Unable to get insurance company name ";
					$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
					foreach($s2 as $row2 ){$type_name=html($row2['name']);}					

if(isset($pid) and $pid!=''){
	$result = show_pt_statement_brief($pdo,$encrypt->encrypt("$pid_clean"),$encrypt);
	$data=explode('#',"$result");
echo "<table>
	<thead>
	<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
	<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
	<tbody><td class=spt>$patient_number</td><td>$first_name</td><td>$middle_name</td><td>$last_name</td>
	<td>$type_name</td><td>$company_covered_name</td><td>$data[0]</td><td>$data[1]</td><td>$data[2]</td><td>limit</td><td>expiry</td></tbody></table>";
	//if($_SESSION['insurance_mismatch_error'] != ''){echo "<div class='error_response'>$_SESSION[insurance_mismatch_error]</div>";}
}			
			}
			else{ echo "<div class='error_response'>No such patient</div>";}

}


	function get_patient_balance($pdo,$pid){
return "balance";
}

	function dont_recalculate_patient_balance($pdo,$pid,$encrypt){
	echo "<table>
	<thead>
	<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
	<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
	<tbody><td>$_SESSION[patient_number]</td><td>$_SESSION[first_name]</td><td>$_SESSION[middle_name]</td><td>$_SESSION[last_name]</td>
	<td>$_SESSION[type_name]</td><td>$_SESSION[company_covered_name]</td><td>$_SESSION[ins_bal]</td><td>$_SESSION[self_bal]</td><td>$_SESSION[points_bal]</td>
	<td>$_SESSION[cover_limit]</td><td>$_SESSION[expiry_date]</td></tbody></table>";
	if($_SESSION['insurance_mismatch_error'] != ''){echo "<div class='error_response'>$_SESSION[insurance_mismatch_error]</div>";}
}

function show_patient_balance($pdo,$pid,$encrypt){
	//$result = show_pt_statement_brief($pdo,$encrypt->encrypt("$pid"),$encrypt);
	//$data=explode('#',"$result");
	$pid_bal="pid_$pid";
		if(isset($_SESSION["$pid_bal"])){
			foreach($_SESSION["$pid_bal"] as $row_bal){
				$data[0]=$row_bal['insurance'];
				$data[1]=$row_bal['cash'];
				$data[2]=$row_bal['points'];
			}
		}
		elseif(!isset($_SESSION["$pid_bal"])){
			$_SESSION["$pid_bal"]=array();
			$result=show_pt_statement_brief($pdo,$encrypt->encrypt("$pid"),$encrypt);
			$data=explode('#',"$result");
			$_SESSION["$pid_bal"][]=array('insurance'=>"$data[0]", 'cash'=>"$data[1]", 'points'=>"$data[2]");
		}
	echo "<table>
	<thead>
	<tr><th>Patient Number</th><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Patient Type</th><th>Company Covered</th>
	<th>INSURANCE BALANCE</th><th>SELF BALANCE</th><th>POINTS BALANCE</th><th>cover limit</th><th>cover expiry</th></tr></thead>
	<tbody><td>$_SESSION[patient_number]</td><td>$_SESSION[first_name]</td><td>$_SESSION[middle_name]</td><td>$_SESSION[last_name]</td>
	<td>$_SESSION[type_name]</td><td>$_SESSION[company_covered_name]</td><td>$data[0]</td><td>$data[1]</td><td>$data[2]</td>
	<td>$_SESSION[cover_limit]</td><td>$_SESSION[expiry_date]</td></tbody></table>";
	if($_SESSION['insurance_mismatch_error'] != ''){echo "<div class='error_response'>$_SESSION[insurance_mismatch_error]</div>";}
}
function show_submit($pdo,$value,$class){
	if($value==''){
		$value='Submit';
		echo "<input class='$class' type=submit value=$value name='Submit' />";
	}
	elseif($value=='swapped'){
		echo "<div class=error_response>Swapped patients can't be edited</div>";
	}	
}


//check if this patient is already swapped
function check_if_swapped($pdo,$criteria,$old_pid){
	//echo "$criteria,$old_pid";
	if($criteria=='pid' and $old_pid!=''){
			$sql=$error=$s='';$placeholders=array();
			$sql="select * from swapped_patients where old_pid=:old_pid";
			$placeholders[':old_pid']=$old_pid;
			$error="Error: Unable to check patient swap ";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount()>0){
				foreach($s as $row){
					$old_pnum=html($row['old_patient_number']);
					$new_pnum=html($row['new_patient_number']);
					return "Patient $old_pnum has been swapped by patient $new_pnum";
				}
			}
			else { return "good";}
	}
	elseif($criteria=='patient_number' and $old_pid!=''){
			$sql=$error=$s='';$placeholders=array();
			$sql="select * from swapped_patients where old_patient_number=:old_patient_number";
			$placeholders[':old_patient_number']="$old_pid";
			$error="Error: Unable to check patient swap ";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount()>0){
				foreach($s as $row){
					$old_pnum=html($row['old_patient_number']);
					$new_pnum=html($row['new_patient_number']);
					return "Patient $old_pnum has been swapped by patient $new_pnum";
				}
			}
			else { return "good";}
	}
}
			
function show_teeth(){
	$encrypt = new Encryption();
	$_SESSION['meno_yote']=array();
	//this is for adult
		$i=1;
		while($i <= 8){
			$number="1$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
		//	echo "<br>$number is $_SESSION[$number]";
			$i++;
		}
		//this is for 2x
		$i=1;
		while($i <= 8){
			$number="2$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}
		//this is for 3x
		$i=1;
		while($i <= 8){
			$number="3$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}
		//this is for 4x
		$i=1;
		while($i <= 8){
			$number="4$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}	
	
	//this is for pedo
		$i=1;
		while($i <= 5){
			$number="5$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
		//	echo "<br>$number is $_SESSION[$number]";
			$i++;
		}
		//this is for 6x
		$i=1;
		while($i <= 5){
			$number="6$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}
		//this is for 7x
		$i=1;
		while($i <= 5){
			$number="7$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}
		//this is for 8x
		$i=1;
		while($i <= 5){
			$number="8$i";
			$_SESSION['meno_yote'][]="$number";
			$_SESSION["tooth$number"]=$encrypt->encrypt("$number");
			$i++;
		}	


}

function get_xray_types($pdo){
	//create associative array of xrays based on their ids		
	$sql=$error=$s='';$placeholders=array();
	$sql="select id,name from procedures where type=2";
	$error="Unable to get xrays types";
	$s = select_sql($sql, $placeholders, $error, $pdo);		
	foreach($s as $row){
		$id=html($row['id']);
		$name=html($row['name']);
		$_SESSION['xray_names_array'][$id]="$name";
	}
	//print_r(
}



//check if procedure exisst
function check_if_procedure_exists($pdo){
	$sql=$error=$s='';$placeholders=array();
	$sql="select id from procedures";
	$error="Unable to get procedures";
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	$_SESSION['procedures_array']=array();
	foreach($s as $row){
		$_SESSION['procedures_array'][]=html($row['id']);
	}
}

//get technician exisst
function get_technician_exists($pdo){
	$sql=$error=$s='';$placeholders=array();
	$sql="select id from lab_technicians";
	$error="Unable to get technicians";
	$s = select_sql($sql, $placeholders, $error, $pdo);	
	$_SESSION['technician_array']=array();
	foreach($s as $row){
		$_SESSION['technician_array'][]=html($row['id']);
	}
}

//check if a ptient exists
function check_if_patient_exists($search_by,$search_criteria,$pdo,$encrypt){
	//this will do patient seraches and return pid and names or not found
	if($search_by!='' and $search_criteria!=''){
		/*and isset($_POST['token_search_patient2']) and 
		isset($_SESSION['token_search_patient2']) and $_POST['token_search_patient2']==$_SESSION['token_search_patient2'])*/
		//get patient details 
		//echo "ddddd -- $_POST[search_by] --  $_POST[search_ciretia]";exit;
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();	
		if($criteria=="patient_number"){
			$sql="select first_name, middle_name, last_name, pid, patient_number,b.name from patient_details_a a left join 
				insurance_company b on a.type=b.id where patient_number=:patient_number and internal_patient=0 ";
			$placeholders[':patient_number']=$search_criteria;
		}
		elseif($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, pid, patient_number , b.name from patient_details_a a
				left join insurance_company b on a.type=b.id where upper($criteria) like :criteria  and internal_patient=0 ";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		//elseif($criteria=="pid"){$sql="select * from patient_details_a where pid=:patient_number";}
		
		$error="Error: Unable to get patient details for patient search";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() == 1){
			foreach($s as $row){
				$_SESSION['searched_patient_last_name']=ucfirst(html($row['last_name']));
				$_SESSION['searched_patient_middle_name']=ucfirst(html($row['middle_name']));
				$_SESSION['searched_patient_first_name']=ucfirst(html($row['first_name']));
				$_SESSION['searched_patient_patient_number']=html($row['patient_number']);
				$_SESSION['searched_patient_pid']=html($row['pid']);
			}
			//echo "good#<label class=label>Patient Names: $_SESSION[searched_patient_first_name] $_SESSION[searched_patient_middle_name] $_SESSION[searched_patient_last_name]</label>";
			return "1#$_SESSION[searched_patient_pid]";
		}
		else if($s->rowCount() > 1){
			//show table with mutile results
			echo "wagonjwawengi<table class='normal_table'><caption>Patient Search Results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th>
			<th class='patient_result_middle_name'>MIDDLE NAME</th><th class='patient_result_last_name'>LAST NAME</th>
			<th class='patient_result_number'>PATIENT NUMBER</th><th class='patient_type'>PATIENT TYPE</th>
			<th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			foreach($s as $row){	
				$first_name=ucfirst(html($row['first_name']));
				$middle_name=ucfirst(html($row['middle_name']));
				$last_name=ucfirst(html($row['last_name']));
				$pid=html($row['pid']);
				$patient_number=html($row['patient_number']);
				$val=$encrypt->encrypt("$pid");
				$type=html($row['name']);
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td></td><td>$patient_number</td>
						<td>$type</td><td><input type=hidden class=ninye value=$val />
						<input type=button class='button_style selected_pt' value=Select /></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return 2;}
	}
}

//check if a family group name exists
function check_if_family_group_exists($search_by,$search_criteria,$pdo,$encrypt){
	if($search_by!='' and $search_criteria!=''){
		/*and isset($_POST['token_search_patient2']) and 
		isset($_SESSION['token_search_patient2']) and $_POST['token_search_patient2']==$_SESSION['token_search_patient2'])*/
		//get patient details 
		//echo "ddddd -- $_POST[search_by] --  $_POST[search_ciretia]";exit;
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		//serach by family name
		if($criteria=="group_name"){
			$sql="select id,name from family_group where  upper(name) like :criteria";
			$placeholders[':criteria']=strtoupper("%$search_criteria%");
		}
		elseif($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select a.id,a.name from patient_details_a b, family_group a  where a.id=b.family_id and upper($criteria) like :criteria";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		elseif($criteria=="patient_number"){
			$sql="select a.id, a.name from patient_details_a b, family_group a  where a.id=b.family_id and a.patient_number=:patient_number";
			$placeholders[':patient_number']=$search_criteria;
		}
		
		$error="Error: Unable to get family group name";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() == 1){
			foreach($s as $row){return "1#$row[id]";}
		}
		else if($s->rowCount() > 1){
			//show table with mutile results
			echo "familymbinge<table class='normal_table'><caption>Family Group Search Results</caption><thead>
			<tr><th class='fmgs_name'>FAMILY GROUP NAME</th>
			<th class='fmgs_sel'>SELECT GROUP</th></tr>
			</thead><tbody>";
			foreach($s as $row){	
				$name=ucfirst(html($row['name']));
				$val=$encrypt->encrypt($row['id']);
				echo "<tr><td>$name</td><td><input type=hidden class=ninye value=$val />
						<input type=button class='button_style selected_pt' value=Select /></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return 2;}
	}
}

//search for pt by first_name,middle_name,last_name
function get_pt_name($search_by,$search_criteria,$pdo,$encrypt){
	if($search_by!='' and $search_criteria!=''){
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		if($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, patient_number, pid , b.name from patient_details_a a left join
				insurance_company b on a.type=b.id 	where upper($criteria) like :criteria and internal_patient=0 order by pid desc";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		
		$error="Error: Unable to get patient by name";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//show table with mutile results
			echo "muwaumbinge<table class='normal_table'><caption>Patient search results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th><th class='patient_result_middle_name'>MIDDLE NAME</th>
				<th class='patient_result_last_name'>LAST NAME</th><th class='patient_result_number'>PATIENT NUMBER</th>
				<th class='patient_type'>PATIENT TYPE</th><th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			foreach($s as $row){	
				$first_name=ucfirst(html("$row[first_name]"));
				$middle_name=ucfirst(html("$row[middle_name]"));
				$last_name=ucfirst(html("$row[last_name]"));
				$val=$encrypt->encrypt(html($row['pid']));
				$file_no=html("$row[patient_number]");
				$type=html($row['name']);
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td><td>$file_no</td><td>$type</td>";
						//<td><input type=hidden class=ninye value=$val />
						//<input type=button class='button_style selected_pt2' value=Select /></td></tr>";
				echo "<td>	<form class='search_form2a' action=$_SESSION[tab_name] method=POST  name='' id=''>
							<input type=hidden name=token_search_patient  value=$_SESSION[token_search_patient]  />
							<input type=hidden value='patient_number' name=search_by />
							<input type=hidden value=$file_no name=search_ciretia />
							<input type=submit class=selected_pt2 value=Select />
						</form></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{$_SESSION['no_patient_found']="No such patient";$_SESSION['pid']=''; }//return 2;}
	}
}

//search for pt by first_name,middle_name,last_name outside of patients menu e.g. in edit dispatches this will 
function get_pt_internal_and_external($search_by,$search_criteria,$pdo,$encrypt,$token_name,$hidden_name1,$hidden_val1,$hidden_name2){
	if($search_by!='' and $search_criteria!=''){
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		if($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, patient_number, pid, b.name from patient_details_a a left join
				insurance_company b on a.type=b.id where upper($criteria) like :criteria  order by pid desc";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		//echo "-- $sql --";
		$error="Error: Unable to get patient by name ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//show table with mutile results
			echo "<table class='normal_table'><caption>Patient search results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th><th class='patient_result_middle_name'>MIDDLE NAME</th>
				<th class='patient_result_last_name'>LAST NAME</th><th class='patient_result_number'>PATIENT NUMBER</th>
				<th class='patient_type'>PATIENT TYPE</th><th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			$token = form_token(); $_SESSION["$token_name"] = "$token";  
			foreach($s as $row){	
				$first_name=ucfirst(html("$row[first_name]"));
				$middle_name=ucfirst(html("$row[middle_name]"));
				$last_name=ucfirst(html("$row[last_name]"));
				$val=$encrypt->encrypt(html($row['pid']));
				$file_no=html("$row[patient_number]");
				$type=html($row['name']);
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td><td>$file_no</td><td>$type</td>";
						//<td><input type=hidden class=ninye value=$val />
						//<input type=button class='button_style selected_pt2' value=Select /></td></tr>";
				echo "<td>	<form class='' action='' method=POST  name='' id=''> 
							<input type='hidden' name=$token_name  value=$_SESSION[$token_name] />
								<input type=hidden name=$hidden_name1 value=$hidden_val1 />
								<input type=hidden name=$hidden_name2 value=$file_no />
								<input type='submit' class='button_table_cell' value=Select />
						</form></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return "2";}
	}
}

//search for pt by first_name,middle_name,last_name outside of patients menu e.g. in edit dispatches this will 
function get_pt_name2($search_by,$search_criteria,$pdo,$encrypt,$token_name,$hidden_name1,$hidden_val1,$hidden_name2){
	if($search_by!='' and $search_criteria!=''){
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		if($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, patient_number, pid , b.name 
				from patient_details_a a left join 	insurance_company b on a.type=b.id 
				where upper($criteria) like :criteria and internal_patient=0 order by pid desc";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		//echo "-- $sql --";
		$error="Error: Unable to get patient by name ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//show table with mutile results
			echo "<table class='normal_table'><caption>Patient search results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th><th class='patient_result_middle_name'>MIDDLE NAME</th>
				<th class='patient_result_last_name'>LAST NAME</th><th class='patient_result_number'>PATIENT NUMBER</th>
				<th class='patient_type'>PATIENT TYPE</th><th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			$token = form_token(); $_SESSION["$token_name"] = "$token";  
			foreach($s as $row){	
				$first_name=ucfirst(html("$row[first_name]"));
				$middle_name=ucfirst(html("$row[middle_name]"));
				$last_name=ucfirst(html("$row[last_name]"));
				$val=$encrypt->encrypt(html($row['pid']));
				$file_no=html("$row[patient_number]");
				$type=html("$row[name]");
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td><td>$file_no</td><td>$type</td>";
						//<td><input type=hidden class=ninye value=$val />
						//<input type=button class='button_style selected_pt2' value=Select /></td></tr>";
				echo "<td>	<form class='' action='' method=POST  name='' id=''> 
							<input type='hidden' name=$token_name  value=$_SESSION[$token_name] />
								<input type=hidden name=$hidden_name1 value=$hidden_val1 />
								<input type=hidden name=$hidden_name2 value=$file_no />
								<input type='submit' class='button_table_cell' value=Select />
						</form></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return "2";}
	}
}

//search for unregistered pt by first_name,middle_name,last_name 
function get_pt_name4($search_by,$search_criteria,$pdo,$encrypt,$token_name,$hidden_name1,$hidden_val1,$hidden_name2){
	if($search_by!='' and $search_criteria!=''){
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		if($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, id from unregistered_patients 
			where upper($criteria) like :criteria order by id desc";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		//echo "-- $sql --";
		$error="Error: Unable to get unregisterd appointment patient by name ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//show table with mutile results
			echo "<table class='normal_table'><caption>Unregistered appointment patient search results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th><th class='patient_result_middle_name'>MIDDLE NAME</th>
				<th class='patient_result_last_name'>LAST NAME</th>
				<th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			$token = form_token(); $_SESSION["$token_name"] = "$token";  
			foreach($s as $row){	
				$first_name=ucfirst(html("$row[first_name]"));
				$middle_name=ucfirst(html("$row[middle_name]"));
				$last_name=ucfirst(html("$row[last_name]"));
				$val=$encrypt->encrypt(html($row['id']));
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td>";
						//<td><input type=hidden class=ninye value=$val />
						//<input type=button class='button_style selected_pt2' value=Select /></td></tr>";
				echo "<td>	<form class='' action='' method=POST  name='' id=''> 
							<input type='hidden' name=$token_name  value=$_SESSION[$token_name] />
								<input type=hidden name=$hidden_name1 value=$hidden_val1 />
								<input type=hidden name=$hidden_name2 value=$val />
								<input type='submit' class='button_table_cell' value=Select />
						</form></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return "2";}
	}
}


//search for pt by first_name,middle_name,last_name outside of patients menu e.g. appointment search it will return interal
//molars patients
function get_pt_name3($search_by,$search_criteria,$pdo,$encrypt,$token_name,$hidden_name1,$hidden_val1,$hidden_name2){
	if($search_by!='' and $search_criteria!=''){
		$criteria=html($search_by);
		$sql=$error=$s='';$placeholders=array();
		if($criteria=="first_name" or $criteria=="middle_name" or $criteria=="last_name"  ){
			$sql="select first_name, middle_name, last_name, patient_number, pid , b.name from patient_details_a a left join
				insurance_company b on a.type=b.id where internal_patient=0 and upper($criteria) like :criteria order by pid desc";
			$placeholders[':criteria']=strtoupper("$search_criteria%");
		}
		//echo "-- $sql --";
		$error="Error: Unable to get patient by name ";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		if($s->rowCount() > 0){
			//show table with mutile results
			echo "<table class='normal_table'><caption>Patient search results</caption><thead>
			<tr><th class='patient_result_first_name'>FIRST NAME</th><th class='patient_result_middle_name'>MIDDLE NAME</th>
				<th class='patient_result_last_name'>LAST NAME</th><th class='patient_result_number'>PATIENT NUMBER</th>
				<th class='patient_type'>PATIENT TYPE</th><th class='patient_result_select'>SELECT PATIENT</th></tr>
			</thead><tbody>";
			$token = form_token(); $_SESSION["$token_name"] = "$token";  
			foreach($s as $row){	
				$first_name=ucfirst(html("$row[first_name]"));
				$middle_name=ucfirst(html("$row[middle_name]"));
				$last_name=ucfirst(html("$row[last_name]"));
				$val=$encrypt->encrypt(html($row['pid']));
				$file_no=html("$row[patient_number]");
				$type=html("$row[type]");
				echo "<tr><td>$first_name</td><td>$middle_name</td><td>$last_name</td><td>$file_no</td><td>$type</td>";
						//<td><input type=hidden class=ninye value=$val />
						//<input type=button class='button_style selected_pt2' value=Select /></td></tr>";
				echo "<td>	<form class='' action='' method=POST  name='' id=''> 
							<input type='hidden' name=$token_name  value=$_SESSION[$token_name] />
								<input type=hidden name=$hidden_name1 value=$hidden_val1 />
								<input type=hidden name=$hidden_name2 value=$file_no />
								<input type='submit' class='button_table_cell' value=Select />
						</form></td></tr>";
			}
			echo "</tbody></table>";
			exit;//return "many";
			
		}
		else{return "2";}
	}
}
//this will get the status of the invoice wether paid, dispatched ...
function get_invoice_status($invoice_id,$pdo){
	if($invoice_id!=''){
		$invoice_id=html($invoice_id);
		//check if paid
			//get amount paid
			$sql=$error=$s='';$placeholders=array();
			$sql="select sum(amount) from payments where invoice_id =:invoice_id group by invoice_id";
			$error="Error: Unable to get amount paid for invoice ";
			$placeholders[':invoice_id']=$invoice_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){
				foreach($s as $row){$paid_sum=html($row[0]);}
				
				//get cost of invoice
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select sum(tplan_procedure.authorised_cost) - ifnull(co_payment.amount, 0) as amount_authorised 
						from tplan_procedure left join co_payment on tplan_procedure.invoice_id=co_payment.invoice_number
						where invoice_id =:invoice_id group by invoice_id";
				$error2="Error: Unable to get invoice cost ";
				$placeholders2[':invoice_id']=$invoice_id;
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){$cost=html($row2[0]);}
				
				if($cost == $paid_sum){return "Paid";}
				elseif($cost > $paid_sum and $paid_sum > 0){return "Partially Paid";}
			}
		
		//check if dispatched
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT dispatch_number FROM tplan_procedure WHERE invoice_id =:invoice_id  GROUP BY invoice_id";
			$error="Error: Unable to get dispacth for invoice ";
			$placeholders[':invoice_id']=$invoice_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){
				foreach($s as $row){
					if($row['dispatch_number'] != ''){return "Dispatched ".html($row['dispatch_number']);}
				}
			}
			
		//check if smart run and  if pre-auth received or requested
			$sql=$error=$s='';$placeholders=array();
			$sql="SELECT authorisation_sent, authorisation_received, smart_run FROM invoice_authorisation WHERE invoice_id =:invoice_id";
			$error="Error: Unable to get authorisation status for invoice ";
			$placeholders[':invoice_id']=$invoice_id;
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){
				$smart_run=$pre_auth='';
				foreach($s as $row){
					if($row['authorisation_sent'] != ''){$pre_auth="Pre-auth sent";}
					if($row['authorisation_received'] != ''){$pre_auth="Authorised";}
					if($row['smart_run'] != ''){$smart_run="SMART checked";}
					return "$pre_auth $smart_run";
				}
			}
		}		
}

//this will record the login time for the user
function record_login($pdo){
	if($_SESSION['id']!=''){
		//echo "user_id id is $user_id --";
		//check if login record exists for that day
			$sql=$error=$s='';$placeholders=array();
			$sql="select id from login_times where user_id=:user_id and date(login_time)=curdate()";
			$error="Error: Unable to get last login time ";
			$placeholders[':user_id']=$_SESSION['id'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){}
			else{
				//insert login time
				$sql=$error=$s='';$placeholders=array();
				$sql="insert into login_times set user_id=:user_id , login_time=now()";
				$error="Error: Unable to update login time ";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			//	echo " user_id id is $user_id --";
			}
		}		
}

//this will record the logout time for the user
function record_logout($pdo){
	if($_SESSION['id']!=''){
				//insert logout time
				$sql=$error=$s='';$placeholders=array();
				$sql="update login_times set logout_time=now() where user_id=:user_id and date(login_time)=curdate()";
				$error="Error: Unable to update logout time ";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);
			
		}		
}

