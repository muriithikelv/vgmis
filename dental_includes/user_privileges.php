<?php
/*if(!isset($_SESSION))
{
session_start();
}*/
if(!userIsLoggedIn() or !userHasRole($pdo,44)){exit;}
echo "<div class='grid_12 page_heading'>USER PRIVILEGES</div>";


?>
<div class=grid-container>
<?php 
//insert or update user
if(isset($_POST['token_privilege1']) and $_POST['token_privilege1']!='' 	and $_POST['token_privilege1']==$_SESSION['token_privilege1']){
	if($_POST['current_users']!=''){
		$_SESSION['user_set_privilege']=$user_id=$encrypt->decrypt($_POST['current_users']);
		echo "<div class=grid-15><label class=label>Assign Privileges </label></div>";
		echo "<div class=grid-15><select class=privilege_type><option></option>
															<option value=role>By Role</option>
															<option value=individual>Individually</option></select></div>";
		echo "<div class=clear></div><br>";
		
		//get user names 
		$sql=$error=$s='';$placeholders=array();
		$sql = "select user_name,first_name,middle_name,last_name from users where id= :user_id ";
		$placeholders[':user_id'] = $user_id;
		$error = "Unable to get user names for privileges";
		$s = 	select_sql($sql, $placeholders, $error, $pdo);
		foreach($s as $row){$user_names=html("$row[first_name] $row[middle_name] $row[last_name]");}
		$token = form_token(); $_SESSION['token_privilege2'] = "$token";  
		
		echo "<div id=role_privileges><div class=role_priv_check></div>";		
		echo "<fieldset><legend>Role privileges for user $user_names</legend><br>"; ?>
		<form action="" method="POST"  name="" id="" class='patient_form'>
			<input type="hidden" name="token_privilege2"  value="<?php echo $_SESSION['token_privilege2']; ?>" />	
			<input type="hidden" name="ninye_role"  value="<?php echo $_SESSION['token_privilege2']; ?>" />	
		<?php
		//get list of roles
		$sql=$error1=$s='';$placeholders=array();
		$sql="select role_id from user_roles where user_id=:user_id";
		$error="Unable to get user roles";
		$placeholders[':user_id']=$user_id	;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$user_roles_array=array();
		foreach($s as $row){
			$user_roles_array[]=html($row['role_id']);
		}

		
		$sql=$error1=$s='';$placeholders=array();
		$sql="select id,name,description from roles";
		$error="Unable to get list of roles";
		$s = select_sql($sql, $placeholders, $error, $pdo);
		$count=1;
		echo "<div class='grid-100 '>";
		echo "<table class='normal_table'><caption>Privilege Roles</caption><thead>
		              <tr><th class=role_name>Name</th><th class=role_desc>Description</th><th class=role_grant>Allowed</th></tr></thead><tbody>";
		foreach($s as $row){
				$name=html($row['name']);
				$description=html($row['description']);
				$val=$encrypt->encrypt(html($row['id']));
				$checked='';
				if (in_array($row['id'], $user_roles_array)) {$checked = " checked ";}
				echo "<tr><td>$name</td><td>$description</td><td><input type=radio name=roles[] value='$val' $checked  /></td></tr>";
				$count++;
		}
		echo "</tbody></table>";
		echo "</div>";
		echo "<br><input type=submit class=''  value=Submit /></form>";
		echo "</fieldset>";
		echo "</div>";//end id role_privileges		
		
		echo "<div id=individual_privileges><div class=role_priv_check></div>";		
		echo "<fieldset><legend>User privileges for $user_names</legend><br>"; ?>
		<form action="" method="POST"  name="" id="" class='patient_form'>
			<input type="hidden" name="token_privilege2"  value="<?php echo $_SESSION['token_privilege2']; ?>" />	
			<input type="hidden" name="ninye_privilege"  value="<?php echo $_SESSION['token_privilege2']; ?>" />	
		<?php
		//get list of privileges
		$sql=$error1=$s='';$placeholders=array();
		$sql="select menu_id from privileges where user_id=:user_id";
		$error="Unable to get user privileges";
		$placeholders[':user_id']=$user_id	;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$user_privileges_array=array();
		foreach($s as $row){
			$user_privileges_array[]=html($row['menu_id']);
		}
		
		//get list of sub menu privileges on same page
		$sql=$error1=$s='';$placeholders=array();
		$sql="select sub_menu_id from sub_privileges where user_id=:user_id";
		$error="Unable to get user sub privileges";
		$placeholders[':user_id']=$user_id	;
		$s = select_sql($sql, $placeholders, $error, $pdo);	
		$user_sub_privileges_array=array();
		foreach($s as $row){
			$user_sub_privileges_array[]=html($row['sub_menu_id']);
		}

		//check if the menu has children
		function check_sub_menus($menu_id, $pdo){
			$sql=$error1=$s='';$placeholders=array();
			$sql="select id,name from menus where parent_id=:parent_id";
			$error="Unable to get actions for privileges";
			$placeholders[':parent_id']=$menu_id;	
			$s = select_sql($sql, $placeholders, $error, $pdo);
			if($s->rowCount() > 0){return "has_sub_menus";	}
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
		//$dont_check_sub_menu=false;
		function get_sub_menus($menu_id, $pdo,$padd_left, $encrypt,$user_privileges_array, $sql_var, $user_sub_privileges_array){
			
			$sql=$error1=$s='';$placeholders=array();
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
						$sql_var="select id,name from menus where parent_id=:parent_id order by arrangement_order";
						get_sub_menus($row['id'], $pdo, 'padd_left',$encrypt,$user_privileges_array, $sub_menu, $user_sub_privileges_array);
					}
					elseif($sub_menu == 'has_sub_menus_on_same_page'){
						
						echo "<tr><td class='$padd_left'>$action</td><td></td></tr>";
						$sql_var="select id,name from sub_menus where parent_menu_id=:parent_id order by arrangement_order";
						get_sub_menus($row['id'], $pdo, 'padd_left',$encrypt,$user_privileges_array,$sub_menu, $user_sub_privileges_array);
					}
					elseif($sub_menu == 'no_sub_menus'){
						
						$val=$encrypt->encrypt(html($row['id']));
						$checked='';
						if (in_array($row['id'], $user_privileges_array)) {$checked = " checked ";}
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
			//$sql_var = "select id,name from menus where parent_id=:parent_id";
			get_sub_menus($row['id'], $pdo, '',$encrypt,$user_privileges_array, $sub_menu, $user_sub_privileges_array);
			echo "</tbody></table>";
			echo "</div>";
			$count++;
			if($count > 3){
				$count=1;
				echo "<div class=clear></div><br>";
//				continue;
			}
		}
		echo "<div class=clear></div><br>";
		
		//now show extras
		$sql=$error1=$s='';$placeholders=array();
		$sql="select id,name,parent_id from menus where level=1 and id=110 "; //110 is for extras
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
				$sql2="select id,name,description from menus where level=2 and parent_id=110 order by arrangement_order"; //110 is for extras
				$error2="Unable to get list of actions";
				$s2 = select_sql($sql2, $placeholders2, $error2, $pdo);
				foreach($s2 as $row2){
					$description=html($row2['description']);
					$action2=html($row2['name']);
					$val=$encrypt->encrypt(html($row2['id']));
					$checked='';
					if (in_array($row2['id'], $user_privileges_array)) {$checked = " checked ";}
					echo "<tr><td>$action2</td><td>$description</td><td><input type=checkbox name=privileges[] value='$val' $checked  /></td></tr>";
				}
				
				echo "</tbody></table>";
				echo "</div>";
				$count++;
			}
		}
		
		echo "<br><input type=submit class=''  value=Submit /></form>";
		echo "</fieldset>";
		echo "</div>";//end individual privileges
	}	
 exit; } ?>
	<!--this is for selcting action to perform-->
	<?php if(isset($_SESSION['result_class']) and isset($_SESSION['result_message']) and $_SESSION['result_message']!='' and 
		$_SESSION['result_class']!=''){
				echo "<div class='feedback $_SESSION[result_class]'>$_SESSION[result_message]</div>";
				$_SESSION['result_class']=$_SESSION['result_message']='';	
		}
	?>
			<fieldset><legend>Select User</legend>
	<form action="" method="POST" enctype="" name="" id="">

	<div class='grid-100 grid-parent '>
			
	
					<?php $token = form_token(); $_SESSION['token_privilege1'] = "$token";  ?>
	<input type="hidden" name="token_privilege1"  value="<?php echo $_SESSION['token_privilege1']; ?>" />
		

	
		<div class='grid-15 alpha'><label for="" class="label">Select User</label></div>
		<div class='grid-45 omega'><select class=input_in_table_cell name=current_users><option></option>
			<?php
				$sql=$error=$s='';$placeholders=array();
				$sql = "select id,first_name, middle_name, last_name  from users order by first_name";
				$error = "Unable to list users";
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				foreach($s as $row){
					$name=html("$row[first_name] $row[middle_name] $row[last_name]" );
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