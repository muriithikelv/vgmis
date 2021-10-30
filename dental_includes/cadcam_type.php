<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,68)){exit;}
echo "<div class='grid_12 page_heading'>CADCAM TYPES</div>"; 
if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
	$_SESSION['result_class']!=''){
		if($_SESSION['result_class']=='success_response'){
			echo "<div class=' $_SESSION[result_class]'>$_SESSION[result_message]</div>";
			$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
}
?>
<div class='feedback hide_element'></div>
<div class="grid-100 margin_top">
	<form class='patient_form2' action="" method="POST" enctype="" name="" id="">
		<?php $token = form_token(); $_SESSION['token_cs1'] = "$token";  ?>
		<input type="hidden" name="token_cs1"  value="<?php echo $_SESSION['token_cs1']; ?>" />
		<!-- manufacturer -->
		<fieldset><legend>Manufacturer</legend>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name, listed from cadcam_types where level = 1 order by name";
				$error2="Unable to get manufacturers for cadcam";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='prefix-15 grid-50'><label class=label>MANUFACTURER NAME</label></div><div class='grid-10'><label class=label>UNLIST</label></div>";
				foreach($s2 as $row2){
					$name=html($row2['name']);
					$var=$encrypt->encrypt($row2['id']);
					$checked='';
					if($row2['listed']==1){$checked=" checked ";}
					echo "<div class='prefix-15 grid-50'><input type=text name=old_manufacturer[] value=$name /></div>";
					echo "<div class='grid-10'><input type=checkbox name=unlist[] $checked value=$var /><input type=hidden name=old_manufacturer2[] value=$var /></div>";
					echo "<div class=clear></div>";
				}
				echo "<div class='grid-100 no_padding manuf_container'>";
					echo "<div class=grid-15><input type=button class='add_manufacturer button_style' value='Add Manufacturer' /></div>";
					echo "<div class=grid-50><input type=text name=new_manufacturer[]  /></div>";
				echo "</div>";
				
			
			?>
		</fieldset>
		
		<!-- size -->
		<fieldset><legend>Type</legend>
			<div class=grid-15><label class=label>Select Manufacturer</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where level = 1 and listed=0 order by name";
				$error2="Unable to get manufacturers for cadcam";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-50'><select name=manufacurer_l2 class=manufacurer_l2><option></option>";
					foreach($s2 as $row2){
						$name=html($row2['name']);
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
				echo "</select></div>";
				//current size
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Type</label></div>";
				echo "<div class=' no_padding current_size'></div>";
				//new size
				echo "<div class='grid-100 no_padding size_container'>";
					echo "<div class=grid-15><input type=button class='add_size button_style' value='Add Type' /></div>";
					echo "<div class=grid-50><input type=text name=new_size[]  /></div>";
				echo "</div>";
				
			
			?>
		</fieldset>	

		<!-- type -->
		<fieldset><legend>Size</legend>
			<div class=grid-15><label class=label>Select Manufacturer</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where level = 1 and listed=0 order by name";
				$error2="Unable to get manufacturers for cadcam";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-50'><select name=manufacurer_l3 class=manufacurer_l3><option></option>";
					foreach($s2 as $row2){
						$name=html($row2['name']);
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
				echo "</select></div>";
				//current size
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Type</label></div>";
				echo "<div class='grid-50 '><select class=size_l3 name=size_l3><option></option></select></div>";
				//current type
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Size</label></div>";
				echo "<div class=' no_padding current_type'></div>";
				//new type
				echo "<div class='grid-100 no_padding type_container'>";
					echo "<div class=grid-15><input type=button class='add_type button_style' value='Add Size' /></div>";
					echo "<div class=grid-50><input type=text name=new_type[]  /></div>";
				echo "</div>";
				
			
			?>
		</fieldset>		

		<!-- shade -->
		<fieldset><legend>Shade</legend>
			<div class=grid-15><label class=label>Select Manufacturer</label></div>
			<?php
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id, name from cadcam_types where level = 1 and listed=0 order by name";
				$error2="Unable to get manufacturers for cadcam";
				$s2 = 	select_sql($sql2, $placeholders2, $error2, $pdo);
				echo "<div class='grid-50'><select name=manufacurer_l4 class=manufacurer_l4><option></option>";
					foreach($s2 as $row2){
						$name=html($row2['name']);
						$var=$encrypt->encrypt($row2['id']);
						echo "<option value=$var>$name</option>";
					}
				echo "</select></div>";
				//current size
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Type</label></div>";
				echo "<div class='grid-50 '><select class=size_l4 name=size_l4><option></option></select></div>";
				//current type
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Size</label></div>";
				echo "<div class='grid-50 '><select class=type_l4 name=type_l4><option></option></select></div>";
				//current shade
				echo "<div class=clear></div>
					<div class=grid-15><label class=label>Current Shade</label></div>";
				echo "<div class=' no_padding current_shade'></div>";
				//new type
				echo "<div class='grid-100 no_padding shade_container'>";
					echo "<div class=grid-15><input type=button class='add_shade button_style' value='Add Shade' /></div>";
					echo "<div class=grid-50><input type=text name=new_shade[]  /></div>";
				echo "</div>";
				
			
			?>
		</fieldset>			
		<input type=submit value=Submit />
	</form>

</div>