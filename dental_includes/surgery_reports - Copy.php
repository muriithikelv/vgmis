<?php
/*
if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,104)){exit;}
echo "<div class='grid_12 page_heading'>SURGERY REPORTS</div>";//check if this guy is a doctor
?>
<div class='grid-container completion_form'>
<?php
if(isset($_SESSION['token_sr1']) and isset($_POST['token_sr1']) and $_POST['token_sr1']==$_SESSION['token_sr1']){
	$_SESSION['token_sr1']='';
	$exit_flag=false;
	//check if date is set for range
		if(!$exit_flag and !isset($_POST['from_date']) or !isset($_POST['to_date']) or $_POST['from_date']=='' or $_POST['to_date']==''){
			echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";
			$exit_flag=true;
		}
		
	//check if serach by is set for 
		if(!$exit_flag and !isset($_POST['surgery_report'])  or $_POST['surgery_report']==''){
			echo "<div class='error_response'>Please specify the search criteria for the report</div>";
			$exit_flag=true;
		}
		
	//check if surgery unit is set  
		if(!$exit_flag and !isset($_POST['surgery_unit'])  or $_POST['surgery_unit']==''){
			echo "<div class='error_response'>Please specify the surgery unit for the report</div>";
			$exit_flag=true;
		}
		
		//get search type
		if(!$exit_flag){
			$report_type=$encrypt->decrypt("$_POST[surgery_report]");
		}
	//get patient waiting time
	if(!$exit_flag and $report_type==12){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
					date(b.time_allocated) as when_added,c.surgery_name, timediff(b.treatment_finish, b.time_allocated) as waiting_time,
					timestampdiff(minute, b.time_allocated ,b.treatment_finish) * points_per_min as points
				from patient_allocations as b join patient_details_a as a on  b.pid=a.pid and date(b.time_allocated) >=:from_date 
					and date(b.time_allocated) <=:to_date $suregry
				left join surgery_names as c on c.surgery_id=b.surgery_id 
				where date(b.treatment_finish) > '0000-00-00'  order by b.id
				";
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			foreach($s2 as $row2 ){
				$patient_name=html($row2['patient_name']);
				$patient_number=html($row2['patient_number']);
				$when_added=html($row2['when_added']);
				$points=number_format(html($row2['points']),2);
				$waiting_time=html($row2['waiting_time']);
				$surgery_unit=html($row2['surgery_name']);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("waiting time for patients allocation to $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("waiting time for patients between $from_date and $to_date");}
					echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=pwt_count></th>
					<th class=pwt_date>DATE</th><th class=pwt_name>PATIENT NAME</th><th class=pwt_pnum>PATIENT NUMBER</th>
					<th class=pwt_sname>SURGICAL UNIT</th><th class=pwt_time>WAITING TIME</th><th class=pwt_points>POINTS</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$i</td><td>$when_added</td><td>$patient_name</td><td>$patient_number</td><td>$surgery_unit</td><td>$waiting_time</td>
				<td>$points</td></tr>";
			}
			echo "</tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";}
		exit;
	}
	//get patients who left
	elseif(!$exit_flag and $report_type==11){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select concat(a.first_name,' ',a.middle_name,' ',a.last_name) as patient_name, a.patient_number, 
					b.time_allocated, b.discharge_time,c.surgery_name, timediff(b.discharge_time, b.time_allocated) as waiting_time,
					timestampdiff(minute, b.time_allocated ,b.discharge_time) * points_per_min as points,
					a.mobile_phone, a.biz_phone
				from patient_allocations as b join patient_details_a as a on  b.pid=a.pid and date(b.time_allocated) >=:from_date 
					and date(b.time_allocated) <=:to_date $suregry
				left join surgery_names as c on c.surgery_id=b.surgery_id 
				where date(b.discharge_time) > '0000-00-00'  order by b.id
				";
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			foreach($s2 as $row2 ){
				$patient_name=html($row2['patient_name']);
				$patient_number=html($row2['patient_number']);
				$time_in=html($row2['time_allocated']);
				$time_out=html($row2['discharge_time']);
				$points=number_format(html($row2['points']),2);
				$waiting_time=html($row2['waiting_time']);
				$surgery_unit=html($row2['surgery_name']);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("patients who left before treatment from $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("patients who left before treatment between $from_date and $to_date");}
					echo "<table class=normal_table><caption>$caption</caption><thead><tr><th class=pl_count></th>
					<th class=pl_name>PATIENT NAME</th><th class=pl_pnum>PATIENT NUMBER</th>
					<th class=pl_sname>SURGICAL UNIT</th><th class=pl_time>TIME ARRIVED</th><th class=pl_time>TIME LEFT</th>
					<th class=pl_wtime>WAITING TIME</th><th class=pl_points>POINTS</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$i</td><td>$patient_name</td><td>$patient_number</td><td>$surgery_unit</td>
				<td>$time_in</td><td>$time_out</td><td>$waiting_time</td><td>$points</td></tr>";
			}
			echo "</tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients who left before treatment for the selected search criteria</div>";}
		exit;
	}	
	//get number of patients seen
	elseif(!$exit_flag and $report_type==10){
			$sql2=$error2=$s2='';$placeholders2=array();
			$from_date=html("$_POST[from_date]");
			$to_date=html("$_POST[to_date]");
			$suregry='';
			if($_POST['surgery_unit']!='all'){
				$suregry= " and b.surgery_id=:surgery_id ";
				$placeholders2['surgery_id']=$encrypt->decrypt("$_POST[surgery_unit]");
			}
			
			$sql2="select count(b.pid),c.surgery_name
					from patient_allocations as b left join surgery_names as c on c.surgery_id=b.surgery_id 
					and date(b.time_allocated) >=:from_date and date(b.time_allocated) <=:to_date $suregry
					where date(b.discharge_time) > '0000-00-00' and patient_left=0 group by b.surgery_id
				";
			$placeholders2[':from_date']=$from_date;
			$placeholders2[':to_date']=$to_date;
			$error2="Error: Unable to get waiting time for patients for date range ";
			
		$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
		$i=$total=0;
		if($s2->rowCount() > 0){ 
			$total=0;
			foreach($s2 as $row2 ){
				$patients_seen=number_format(html($row2[0]));
				$surgery_unit=html($row2['surgery_name']);
				$total=$total + html($row2[0]);
				if($i==0){
					if($_POST['surgery_unit']!='all'){$caption=strtoupper("patients seen from $surgery_unit between $from_date and $to_date");}
					elseif($from_date!='' and $to_date!=''){$caption=strtoupper("patients seen between $from_date and $to_date");}
					echo "<table class=half_width><caption>$caption</caption><thead><tr>
					<th class=ps_name>SURGICAL UNIT</th><th class=ps_seen>NUMBER SEEN</th></tr></thead><tbody>";
				}
				$i++;
				echo "<tr><td>$surgery_unit</td><td>$patients_seen</td></tr>";
			}
			echo "<tr class=total_background><td>TOTAL</td><td>".number_format($total)."</td></tr></tbody></table><br>";
		}
		else{ echo "<div class='error_response'>There were no patients seen for the selected search criteria</div>";}
		exit;
	}	
}

?>


<form class='' action='' method="POST"  name="" id="">
	<div class='grid-15'>
		<?php $token = form_token(); $_SESSION['token_sr1'] = "$token";  ?>
		<input type="hidden" name="token_sr1"  value="<?php echo $_SESSION['token_sr1']; ?>" />
		<label for="" class="label">Search by</label>
	</div>
	<div class='grid-20'>
		<?php
			$sql=$error=$s='';$placeholders=array();
			$sql="select a.id, a.name from sub_menus a, sub_privileges b where b.user_id=:user_id and b.parent_menu_id=104
				and b.sub_menu_id=a.id";
			$error="Unable to get surgery report options";
			$placeholders[':user_id']=$_SESSION['id'];
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			echo "<select class=' ' name=surgery_report><option></option>";
			if($s->rowCount() > 0){
				
				foreach($s as $row){
					$name=html($row['name']);
					$id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$id'>$name</option>";
				}			
									
			}
			else{//check if this is a role
				$sql=$error=$s='';$placeholders=array();
				$sql="select a.id, a.name from sub_menus a, role_sub_privileges b , user_roles c where c.user_id=:user_id and 
				c.role_id=b.role_id and b.parent_menu_id=104 and and b.sub_menu_id=a.id";
				$error="Unable to iget surgery report options by role";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$name=html($row['name']);
					$id=$encrypt->encrypt(html($row['id']));
					echo "<option value='$id'>$name</option>";
				}			
				
			}		
			echo "</select>";
		?>
	</div>
	<div class=clear></div><br>
		<div class='grid-15 label'>Select Surgery unit</div>
		<div class='grid-25 '><?php
			$sql=$error=$s='';$placeholders=array();
			$sql="select surgery_id, surgery_name from surgery_names order by surgery_name";
			$error="Unable to get surgery units";
			$s = 	select_sql($sql, $placeholders, $error, $pdo);
			echo "<select class=' ' name=surgery_unit><option value='all' >All Surgery Units</option>";
				foreach($s as $row){
					$name=html($row['surgery_name']);
					$id=$encrypt->encrypt(html("$row[surgery_id]"));
					echo "<option value='$id'>$name</option>";
				}			
				echo "</select>";					
		?>
		</div>
	
	<div class=clear></div><br>
		<div class='grid-15 label'>Between this date</div>
		<div class='grid-10'><input type=text name=from_date class=date_picker /></div>
		<div class='grid-10 label'>And this date</div>
		<div class='grid-10'><input type=text name=to_date class=date_picker /></div>
		<div class='grid-35 show_spin'><input class='find_pt1' type=submit value="Submit"  /></div>
	
	
</form>	 
</div>

