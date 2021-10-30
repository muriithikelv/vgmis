<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,43)){exit;}
echo "<div class='grid_12 page_heading'>PRIVILEGE ROLES</div>";


?>
<div class=grid-container>
<div class='feedback '></div>
<?php 
//insert or update user
if(isset($_POST['token_role1']) and $_POST['token_role1']!='' 	and $_POST['token_role1']==$_SESSION['token_role1']){
	if($_POST['action_type']=='edit_role' and (!isset($_POST['current_roles']) or $_POST['current_roles']=='')){
		echo "<div class='error_response'>No role selected for editing</div>";
		exit;
	}
	if($_POST['action_type']=='add_role'){
		echo "<fieldset><legend>New role privileges</legend><br>"; 
		$role_privileges_array=array();
		$user_sub_privileges_array=array();
		$_SESSION['role_id']=$role_id=$role_name=$description='';
	}
	elseif($_POST['action_type']=='edit_role'){
		$_SESSION['role_id']=$role_id=$encrypt->decrypt($_POST['current_roles']);
		//get role name 
		$sql=$error=$s='';$placeholders=array();
		$sql = "select name,description from roles where id= :role_id ";
		$placeholders[':role_id'] = $role_id;
		$error = "Unable to get role names for privileges";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){
			$role_name=html("$row[name]");
			$description=html("$row[description]");
		}
			
		echo "<fieldset><legend>Role Privileges</legend><br>"; 
	
		//get user profile
		$sql=$error1=$s='';$placeholders=array();
		$sql="select * from role_privileges where role_id=:id";
		$error="Unable to get role details";
		$placeholders[':id']=$encrypt->decrypt("$_POST[current_roles]");	
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$role_privileges_array=array();
		foreach($s as $row){$role_privileges_array[]=html($row['menu_id']);}

		//get list of sub menu privileges on same page
		$sql=$error1=$s='';$placeholders=array();
		$sql="select sub_menu_id from role_sub_privileges where role_id=:id";
		$error="Unable to get user sub privileges";
		$placeholders[':id']=$encrypt->decrypt("$_POST[current_roles]");	
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$user_sub_privileges_array=array();
		foreach($s as $row){
			$user_sub_privileges_array[]=html($row['sub_menu_id']);
		}		
	}	
	
	echo "<div class=grid-100 >";?>
		<form action="" method="POST"  name="" id="" class='patient_form'>
			<br>
			<?php 	$token = form_token(); $_SESSION['token_role2'] = "$token";  ?>
			<input type="hidden" name="token_role2"  value="<?php echo $_SESSION['token_role2']; ?>" />	
			<div class=grid-10><label class=label>Role Name</label></div>
			<div class=grid-15><input type=text name=role_name value='<?php echo "$role_name"?>' /></div>
			<div class='prefix-5 grid-10'><label class=label>Description</label></div>
			<div class=grid-45><input  type=text name=description value='<?php echo "$description"?>' /></div>
			<div class=clear></div></br>
		<?php	
		//check if the menu has children
		function check_sub_menus($menu_id, $pdo){
			$sql=$error1=$s='';$placeholders=array();
			$sql="select id,name from menus where parent_id=:parent_id";
			$error="Unable to get actions for privileges";
			$placeholders[':parent_id']=$menu_id;	
			$s = select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){return "has_sub_menus";	}
			//else{ return "no_sub_menus";}
			else{ 
				//check if the menu has sub menus that will be in sub menus page
				$sql=$error1=$s='';$placeholders=array();
				$sql="select id,name from sub_menus where parent_menu_id=:parent_id";
				$error="Unable to get same page sub menus for privileges";
				$placeholders[':parent_id']=$menu_id;	
				$s = select_sql($sql, $placeholders, $error, $pdo);
				if($s->rowCount() > 0){return "has_sub_menus_on_same_page";	}
				else{return "no_sub_menus";}
			}
		}		
		
		//get list of actions and display
		$sub_menu = 'no_sub_menus';
		function get_sub_menus($menu_id, $pdo,$padd_left, $encrypt,$role_privileges_array, $sql_var, $user_sub_privileges_array){
			$sql=$error1=$s='';$placeholders=array();
			//$sql="select id,name from menus where parent_id=:parent_id";
			if($sql_var=='has_sub_menus_on_same_page'){
				$dont_check_sub_menu=true;
				$sql="select id,name from sub_menus where parent_menu_id=:parent_id order by arrangement_order";
			}
			else{
				$dont_check_sub_menu=false;
				$sql="select id,name from menus where parent_id=:parent_id";
			}
			$error="Unable to get sub_menus for privileges";
			$placeholders[':parent_id']=$menu_id;	
			$s = select_sql($sql, $placeholders, $error, $pdo);
				foreach($s as $row){
					$action=html($row['name']);
					if(!$dont_check_sub_menu){$sub_menu = check_sub_menus($row['id'], $pdo);}
					elseif($dont_check_sub_menu){//show sub menus on same page
						$val=$encrypt->encrypt(html("$row[id]#$menu_id"));
						$checked='';
						if (in_array($row['id'], $user_sub_privileges_array)) {$checked = " checked ";}
						echo "<tr><td class='$padd_left'>$action</td><td><input type=checkbox name=sub_privileges[] value='$val' $checked  /></td></tr>";
						//$dont_check_sub_menu=true;
						continue;
					}
					if($sub_menu == 'has_sub_menus'){
						echo "<tr><td class='$padd_left'>$action</td><td></td></tr>";
						get_sub_menus($row['id'], $pdo, 'padd_left',$encrypt,$role_privileges_array,$sub_menu, $user_sub_privileges_array);
					}
					elseif($sub_menu == 'has_sub_menus_on_same_page'){
						echo "<tr><td class='$padd_left'>$action</td><td></td></tr>";
						get_sub_menus($row['id'], $pdo, 'padd_left',$encrypt,$role_privileges_array,$sub_menu, $user_sub_privileges_array);
					}
					elseif($sub_menu == 'no_sub_menus'){
						$val=$encrypt->encrypt(html($row['id']));
						$checked='';
						if (in_array($row['id'], $role_privileges_array)) {$checked = " checked ";}
						echo "<tr><td class='$padd_left'>$action</td><td><input type=checkbox name=privileges[] value='$val' $checked  /></td></tr>";
					}				
				}

		}
		
		$sql=$error1=$s='';$placeholders=array();
		$sql="select id,name,parent_id from menus where level=1 and id!=110"; //110 is for extras
		$error="Unable to get list of actions";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$count=1;
		foreach($s as $row){
			
			$action=html($row['name']);
			echo "<div class='grid-25 suffix-5'>";
			echo "<table class='normal_table'><caption>$action</caption><thead>
				  <tr><th class=priv_name>Privilege</th><th class=priv_grant>Allowed</th></tr></thead><tbody>";
			get_sub_menus($row['id'], $pdo, '',$encrypt,$role_privileges_array, $sub_menu, $user_sub_privileges_array);
			echo "</tbody></table>";
			echo "</div>";
			$count++;
			if($count > 3){
				$count=1;
				echo "<div class=clear></div><br>";
			//	continue;
			}	
		}
		echo "<div class=clear></div><br>";
		
				//now show extras
		$sql=$error1=$s='';$placeholders=array();
		$sql="select id,name,parent_id from menus where level=1 and id=110"; //110 is for extras
		$error="Unable to get list of actions";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$count=1;
		if($s->rowCount() > 0){
			foreach($s as $row){
				$action=html($row['name']);
				if($count==1){
					echo "<div class='grid-100'>";
					echo "<table class='normal_table'><caption>$action</caption><thead>
						<tr><th class=priv_name1>Privilege</th><th class=priv_desc1>Description</th>
						<th class=priv_grant1>Allowed</th></tr></thead><tbody>";
				}
				//now get sub privileges under extras
				$sql2=$error2=$s2='';$placeholders2=array();
				$sql2="select id,name,description from menus where level=2 and parent_id=110"; //110 is for extras
				$error2="Unable to get list of actions";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){
					$description=html($row2['description']);
					$action2=html($row2['name']);
					$val=$encrypt->encrypt(html($row2['id']));
					$checked='';
					if (in_array($row2['id'], $role_privileges_array)) {$checked = " checked ";}
					echo "<tr><td>$action2</td><td>$description</td><td><input type=checkbox name=privileges[] value='$val' $checked  /></td></tr>";
				}
				
				echo "</tbody></table>";
				echo "</div>";
				$count++;
			}
		}
		
		echo "<br><input type=submit class=''  value=Submit /></form>";
		echo "</fieldset>";	
	echo "</div>";
	 exit; } ?>
	<!--this is for selcting action to perform-->
	<?php if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
	?>
			<fieldset><legend>Select Action</legend>
	<form action="" method="POST" enctype="" name="" id="">

	
			
	<div class='grid-15'>
					<?php $token = form_token(); $_SESSION['token_role1'] = "$token";  ?>
	<input type="hidden" name="token_role1"  value="<?php echo $_SESSION['token_role1']; ?>" />
		
	<label for="" class="label">Select Action</label></div>
	<div class='grid-25'><select class='input_in_table_cell add_user_action' name=action_type><option></option>
						<option value='add_role'>Add New Role</option>
						<option value='edit_role'>Edit Role Privileges</option>
						</select></div>
	<div class=clear></div>
	<br>
	<div class='grid-100 grid-parent select_user'>
		<div class='grid-15 alpha'><label for="" class="label">Select Role</label></div>
		<div class='grid-45 omega'><select class=input_in_table_cell name=current_roles><option></option>
			<?php
				$sql=$error=$s='';$placeholders=array();
				$sql = "select id,name  from roles order by name";
				$error = "Unable to list roles";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				foreach($s as $row){
					$name=html("$row[name]" );
					$val=$encrypt->encrypt(html($row['id']));							
					echo "<option value='$val'>$name</option>";
				}
			
			?>					
			</select/>
		</div>
		<div class=clear></div>
	</div>
	<div class='grid-25 prefix-15'>	<br><input type="submit"  value="Submit"/></form></div>
<div class=clear></div>
			
		</fieldset>	
</div>