<?php  
	include_once  '../dental_includes/magicquotes.inc.php'; 
	include_once   '../dental_includes/db.inc.php'; 
	include_once '../dental_includes/dbsession.php';
	$session = new dbSession($pdo);
	//if(isset($_SESSION['LAST_ACTIVITY'])){echo "<br>xx $_SESSION[LAST_ACTIVITY]"; }
	//	  require_once    '../dental_includes/MySqlSession.php';
  //new MySqlSession($pdo);
//	include_once   '../dental_includes/DatabaseSession.class.php';
	include_once   '../dental_includes/access.inc.php';
	include_once   '../dental_includes/encryption.php';
	include_once    '../dental_includes/helpers.inc.php';
	include_once    '../dental_includes/phpmailer/class.phpmailer.php';
	include_once     '../dental_includes/fpdf/fpdf.php';
	$encrypt = new Encryption();
	
	/*//will enable append mail to sent items
	class PHPMailer_mine extends PHPMailer {
		public function get_mail_string() {
			return $this->MIMEHeader.$this->MIMEBody;
		}
	}*/
	$mail = new PHPMailer_mine();
	
/*	if (isset($_SESSION['LAST_ACTIVITY']) and (time() - $_SESSION['LAST_ACTIVITY'] > 900) and $_GET['id']!='log-out') {
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
	}*/
	
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp	
	//echo "40".$_SESSION['id']
	//remove any tokens for invoice editing
	check_invoice_edit_tokens($pdo);
	
//this is for password change from login page
if( isset($_POST['token_pc_2']) and isset($_SESSION['token_pc_2']) and $_SESSION['token_pc_2']==$_POST['token_pc_2']){
			$_SESSION['token_pc_2']=$error_message='';
			$exit_flag=false;
			if(!$exit_flag and $_POST['new_password1']==''){
				$exit_flag=true;
				$error_message=" New password was not specified. ";
			}
			if(!$exit_flag and $_POST['new_password2']!=$_POST['new_password1']){
				$exit_flag=true;
				$error_message=" New passwords do not macth. ";
			}
			
			
				if(!$exit_flag){
					//check password criteria
					$pwd = $_POST['new_password1'];

					$result=check_password_complexity($pdo,"$pwd", $_SESSION['temp_user_id'], $salt);
					if(!$result){
						$exit_flag=true;
						$error_message="$_SESSION[password_message]";
					}
					elseif($result){
						$login_error_message="<div class=success_response>Your password has changed.<br>
						Please login with your new credentials</div>";
					}
					

				}
			
			if(isset($error_message) and $error_message!=''){
				$password_change_error="<div class=error_response>$error_message</div>";
				$change_password=true;
			}
		
}	
	
	//process login
	if(isset($_SESSION['token']) and isset($_POST['username']) and isset($_POST['password']) and $_POST['token']!=''){
	if($_SESSION['token']==$_POST['token']){
	$password = hash_hmac('sha1', $_POST['password'], $salt);
	$sql=$error=$s='';$placeholders=array();
	$sql = "select id,user_name,status,first_name,middle_name,last_name,user_type,reset_password,user_type,
			datediff(curdate(),date_of_last_password_change) as password_age from users where password = :password and user_name = :username ";
	$placeholders[':username'] = $_POST['username'];
	$placeholders[':password'] = "$password";
	$error = "Unable to login user";
	$s = 	select_sql($sql, $placeholders, $error, $pdo);
	if($s->rowCount()==0){
		$login_error_message="<div class=error_response>Incorrect Username/Password</div>";
	}	
	elseif($s->rowCount()>0){
		foreach ($s as $row)
		{		
			if($row['status']=='locked'){
				$login_error_message="<div class=error_response>Your account is locked</div>";
			}
			elseif($row['password_age'] > 90){
				$change_password=true;
				$_SESSION['temp_user_id']=html($row['id']);
			}
			elseif($row['status']=='active'){
				session_regenerate_id();
				$_SESSION['user_name']=html("$row[user_name]");
				$_SESSION['id']=html($row['id']);
				$_SESSION['is_user_doctor']=html($row['user_type']);
				$_SESSION['logged_in_user_names']=ucfirst(html("$row[first_name] $row[middle_name] $row[last_name]"));
				$_SESSION['logged_in_user_reset_passwrd_status']=html("$row[reset_password]");
				$_SESSION['logged_in_user_type']=html("$row[user_type]");					
				//call needed functions
				get_patient_types($pdo);
				get_covered_company($pdo);
				get_cities($pdo);
				get_relationships($pdo);
				get_referee($pdo);
				show_teeth();
				get_xray_types($pdo);
				check_if_procedure_exists($pdo);
				record_login($pdo);
				//get_smtp_details($pdo);
				//check if the guy is a doctor
				$_SESSION['select_surgery']='';
				if($_SESSION['is_user_doctor']==1){$_SESSION['select_surgery']='no';}
				$_SESSION['LAST_ACTIVITY2'] = time();
				
				//delete payment tokens older than todays
				$sql=$error=$s='';$placeholders=array();
				$sql="delete from check_token where datediff(now(), when_added) > 2";
				$error="Unable to get rid of tokens";
				$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			
				//$login_error_message="<div class=error_response>No activity within 15 minutes please log in again</div>";
				
				//get_user_notifications($pdo);
				//print_r($_SESSION['patient_type_array']);
			//	echo "session id is ".$_SESSION['id'];
			}
		}
		
	}//correct password	
	}//end token
}

//this is for selecting a surgery
if(isset($_SESSION['token_sr']) and isset($_POST['token_sr']) and $_POST['token_sr']==$_SESSION['token_sr'] and isset($_POST['surgery_name'])
	and $_POST['surgery_name']!=''){
			//remove previous user surgery logins
			$sql=$error=$s='';$placeholders=array();
			$sql="delete from surgery_logins where user_id=:user_id";
			$error="Unable to login to surgery";
			$placeholders[':user_id']=$_SESSION['id'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			
			$sql=$error=$s='';$placeholders=array();
			$sql="insert into surgery_logins set user_id=:user_id, surgery_id=:surgery_id";
			$error="Unable to login to surgery";
			$placeholders[':surgery_id']=$encrypt->decrypt($_POST['surgery_name']);
			$placeholders[':user_id']=$_SESSION['id'];
			$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
			if($s){$_SESSION['select_surgery']='';}
			
}

if(isset($_GET['log_out']) or (isset($_GET['id']) and $_GET['id']=='log-out')){
	//session_start();
	//remove previous user surgery logins
	$sql=$error=$s='';$placeholders=array();
	$sql="delete from surgery_logins where user_id=:user_id";
	$error="Unable to login to surgery";
	$placeholders[':user_id']=$_SESSION['id'];
	$s = 	insert_sql($sql, $placeholders, $error, $pdo);	
	
	record_logout($pdo);
	session_destroy();
    header('Location:  . ');
    exit();
}

	?>
	
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1"/>
<title>Cuspid Dental</title>
<script type="text/javascript"> </script>
<link rel="stylesheet" type="text/css" media="screen" href="dental_css/reset.css" />
<link rel="stylesheet" type="text/css" media="screen" href="dental_css/text.css" />

<link rel="stylesheet" type="text/css" media="screen" href="dental_css/unsemantic-grid-responsive.css" />
<link rel="stylesheet" type="text/css" media="screen" href="dental_css/jquery-ui-1.9.2.custom.min.css" /><!--
<link rel="stylesheet" type="text/css" media="all" href="dental_css/style.css" />-->
<link rel="stylesheet" type="text/css" media="screen" href="dental_css/hide.css" /> <!--
<link rel="stylesheet" type="text/css" media="all" href="dental_css/menu/menu/css/style.css" />-->
<link rel="stylesheet" type="text/css" media="screen" href="dental_css/style1.css" />
<link rel="stylesheet" type="text/css" media="print" href="dental_css/printment.css" />
<script type="text/javascript" src="dental_b/jquery-1.8.3.js"></script>
<script type="text/javascript" src="dental_b/jquery-ui-1.9.2.custom.min.js"></script>
<!--
<script type="text/javascript" src="dental_b/jquery.chromatable.js"></script>-->
<script type="text/javascript" src="dental_b/jquery.fixedheadertable.min.js"></script>
<script type="text/javascript" src="dental_b/bloody_tabs.js"></script>
<script type="text/javascript" src="dental_b/menu.js"></script>
<script type="text/javascript" src="dental_b/jquery.printElement.min.js"></script>
<script type="text/javascript" src="dental_b/jquery.idle.min.js"></script>

</head>
<body>
<noscript>
	<div class="grid-container"><div class="grid-100 no_js_text">JavaScript is disabled! 
	Please enable JavaScript in your web browser to use this site!</div></div>
 
	<style type="text/css">
		.mainsss , .main_content{ display:none; }
		.show_height{min-height: 100%;}
	</style>
</noscript>



<?php  //$_SESSION['id']=1; 

//print_pdf($dompdf);
	
		//change password if need be
		if(isset($change_password) and $change_password){  ?>
		<div class='grid-container'>
			
			<div class="grid-100 push_down center_from_top push_down reset_password_at_login">
			<div class='feedback hide_element'></div>
			<div class="grid-100 label">Your password has expired and needs to be reset<br><br></div>
			<?php
				if(isset($password_change_error) and $password_change_error!=''){
					echo "$password_change_error";
				}
			?>
			<div class='grid-50 alpha'>
				
					<form class='' action="" method="post" name="" id="">
						<div class='grid-40 '><label for="user" class="label">New Password</label></div>
						<div class='grid-40 '><input type=password name=new_password1 /></div> <!-- drug -->
						<div class=clear></div><br>
						<div class='grid-40 '><label for="user" class="label">Re-type New Password</label></div>
						<div class='grid-40 '><input type=password name=new_password2 /></div> <!-- drug -->
						<div class=clear></div><br>
						
						<?php $token = form_token(); $_SESSION['token_pc_2'] = "$token";  ?>
					<input type="hidden" name="token_pc_2"  value="<?php echo $_SESSION['token_pc_2']; ?>" />
						<div class='prefix-40 grid-15'><input type="submit"  value="Submit"/></div>
						</form>
			</div>
			<div class='grid-50 omega label'>	
				New password must:<br>
				Be at least 8 characters long<br>
				Include at least one number [0-9]<br>
				Include at least one lower case letter [a-z]]<br>
				Include at least one upper case letter [A-Z]]<br>
				include at least one special character  e.g. !# <br>
			</div>
			<div class=clear></div>
			</div>
		</div>
	<?php
	}
	
	//login
	elseif(!isset($_SESSION['id']) ){ 
echo "<div   class='grid-container' main_content>"; //main_content ?>
 
	
	<div class='grid-100 grid-parent'>
		<div class="prefix-33 grid-33 suffix-33 no_padding" id="login2">
			<?php
				if(isset($login_error_message)){
					echo "$login_error_message";
					$login_error_message='';
				}
			?>
		</div>
		<div class=clear></div>
		<?php
			if(!isset($_GET['log_out']) or (!isset($_GET['id']) or $_GET['id']!='log-out')){
			?>	<script type="text/javascript">
							if (localStorage.time_out != ''){
								document.getElementById("login2").innerHTML= localStorage.time_out;
								//empty localstorage for logout
								localStorage.time_out='';

							}
							
				</script> 
			<?php	
			}
		?>

		<div class="prefix-33 grid-33 suffix-33" id="login">
				
				
				<div class="grid-100"><font color="green"><h2>Cuspid Dental</h2> </font>
				</div>
					
				<div class="grid-100"><form action="?" method="POST">
				<?php $token = sha1(uniqid(rand(), TRUE)); $_SESSION['token'] = "$token"; $_SESSION['token_time'] = time(); ?>
				<input type="hidden" name="token" id="token" value="<?php echo $_SESSION['token']; ?>" />
				<label for="username" class="login_label">Username</label></div>
				<div class="grid-80" ><input class="login_text" type="text" name="username" id="username" /></div>
				<div class=clear></div>
				<div class="password grid-100"><label for="passowrd"  class="login_label">Password</label></div>
				<div class="grid-80"><input class="login_text" type="password" name="password" id="password" /></div>
				<div class=clear></div>
				<div class="submit grid-100"><input class="button_submit" type="submit" name="login" value="Sign In"/>
				</form><br><br>
				</div>
			
		</div><!-- login form -->
	</div>

	<?php 

echo "</div>";//this is for main_content	
	
	}
	

	//ask the docotr to select his surgery
	//if($_SESSION['is_user_doctor']==1){$_SESSION['select_surgery']='no';}
	elseif(isset($_SESSION['id']) and $_SESSION['is_user_doctor']==1 and  $_SESSION['select_surgery']=='no'){ 
		echo "<div   class=grid-container main_content>";?>
		<div class='grid-100 grid-parent'>
			<div class="prefix-33 grid-33 suffix-33 no_padding" id="login2"></div>
			<div class=clear></div>
			<div class="prefix-33 grid-33 suffix-33" id="login">
				<div class="grid-100"><form action="?" method="POST">
				<?php $token = sha1(uniqid(rand(), TRUE)); $_SESSION['token_sr'] = "$token"; $_SESSION['token_time'] = time(); ?>
				<input type="hidden" name="token_sr" id="token_sr" value="<?php echo $_SESSION['token_sr']; ?>" />
				<label for="username" class="login_label">Select the surgery your working from</label></div>
				<div class="grid-80" ><select name=surgery_name>
					<?php 
						$val=$encrypt->encrypt('0');
						echo "<option value='$val'>None</option>";
						$sql=$error=$s='';$placeholders=array();
						$sql = "select surgery_id,surgery_name from surgery_names order by surgery_name";
						$error = "Unable to get surgery names";
						$s = 	select_sql($sql, $placeholders, $error, $pdo);	
						foreach($s as $row){
							$name=html($row['surgery_name']);
							$val=$encrypt->encrypt(html($row['surgery_id']));
							echo "<option value='$val'>$name</option>";
						}					
					?>
					</select>
				</div>
				<div class=clear></div>
				<div class="submit grid-100"><input class="button_submit" type="submit" name="login" value="Submit"/>
				</form><br><br>
				</div>
			</div><!-- login form -->
		</div>

		<?php 
		echo "</div>";//this is for main_content	
	}	
	//do below only if the user is logged in
	//elseif(isset($_SESSION['id']) and $_SESSION['id']!='')
	elseif(isset($_SESSION['id']) and $_SESSION['select_surgery']!='no'){ ?>  
	
		<!-- this displays the menu bar after a user has logged in -->
<div class="mainsss">
		<div class="clear"></div>
		 <div  class="main_menu"> 
			<div class="grid-container menus"> 
			<?php
				//get menus for this user
				//$uid=$_SESSION[id];

				?>

				<ul id="nav1"><!--top level menu list <ul id="nav1">-->

				<?php
				//get menus for all menus that will have tabs e.g. suppliers=1
				$placeholders=array();
				$sql="select a.id, a.name, a.tab_name from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id and a.level = 1 and (b.menu_id=1 ) ";//1 is patients
				$error="Unable to select menu";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				//check in roles if individual privileges does not procude anything
				
				if($s->rowCount() == 0){
					$sql="select a.id, a.name, a.tab_name from menus a, user_roles c, role_privileges b where c.user_id=:user_id and  c.role_id=b.role_id and
					a.id=b.menu_id and a.level = 1 and (b.menu_id=1 ) ";//1 is patients
					$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				}
					foreach ($s as $row)
					{
						$menu_id=html("$row[tab_name]");
						$menu_name=html("$row[name]");
						echo 	"<li><a class='tab_link level0'   href='?id=$row[tab_name]'>$menu_name</a></li>";	
											
							
					}
				
				//this is for menu items that will not need tabs e.g. reports
				$placeholders=array();
				$sql="select a.id, a.name, a.tab_name  from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id and a.level = 1 and b.menu_id!=1 and b.menu_id!=110 order by arrangement_order";//1 is for patients menu
				$error="Unable to select menu";
				$placeholders[':user_id']=$_SESSION['id'];
				$s = 	select_sql($sql, $placeholders, $error, $pdo);
				//check in roles if individual privileges does not procude anything
				if($s->rowCount() == 0){
					$sql="select a.id, a.name, a.tab_name from menus a, user_roles c, role_privileges b where c.user_id=:user_id and  c.role_id=b.role_id and
					a.id=b.menu_id and a.level = 1 and b.menu_id!=1 order by arrangement_order ";//1 is patients
					$s = 	select_sql($sql, $placeholders, $error, $pdo);	
				}			
					foreach ($s as $row)
					{
						//if($row['name'] != 'Admin'){$admin_class='';}
						//else{$admin_class=' admin_class ';}
						//echo "<li class=''><a class='level0  ' href=?id=$row[tab_name]>";htmlout("$row[name]");echo"</a>";
						//if($row['name'] != 'Admin'){
								//show level 2 menu
									$placeholders=array();
									$sql="select a.id, a.name, a.tab_name, a.classes  from menus a, privileges b where 
										b.user_id=:user_id and a.id=b.menu_id and a.parent_id =$row[id] and a.level = 2  and a.id!=109
										order by arrangement_order";
									$error="Unable to select menu";
									$placeholders[':user_id']=$_SESSION['id'];
									$s2 = 	select_sql($sql, $placeholders, $error, $pdo);
									//check in roles if individual privileges does not procude anything
									if($s2->rowCount() == 0){
										$sql="select a.id, a.name, a.tab_name, a.classes from menus a, user_roles c, role_privileges 
											b where c.user_id=:user_id and c.role_id=b.role_id and a.id!=109 and 
											a.id=b.menu_id and a.parent_id =$row[id] and a.level = 2 order by arrangement_order";//1 is patients
										$s2 = 	select_sql($sql, $placeholders, $error, $pdo);
									}
									//show mega drop down for long admin privileges
									if( $s2->rowCount() > 10){
										echo "<li class='admin_class'><a class='level0  ' href=?id=$row[tab_name]>";htmlout("$row[name]");echo"</a>";
										$var = $s2->rowCount();
										//$var = $s2->rowCount() / 10;
										//$mod = $s2->rowCount() % 10;
										/*$mod1 = 4 % 10;
										$mod2 = 10 % 10;
										$mod3 = 0 % 10;
										/*if($var >= 1 and $var < 2){ $drop_down_class=' dropdown_2columns ';$drop_col_width=' grid-50';}
										elseif($var >= 2 and $var < 3){ $drop_down_class=' dropdown_3columns ';$drop_col_width=' grid-33';}
										elseif($var >= 3 and $var < 4){ $drop_down_class=' dropdown_4columns ';$drop_col_width=' grid-25';}
										elseif($var >= 4 and $var < 5){ $drop_down_class=' dropdown_5columns ';$drop_col_width=' grid-20';}*/
										if($var >= 1 and $var < 19){ $drop_down_class=' dropdown_2columns ';$drop_col_width=' grid-50';}
										elseif($var >= 19 and $var < 28){ $drop_down_class=' dropdown_3columns ';$drop_col_width=' grid-33';}
										elseif($var >= 28 and $var < 37){ $drop_down_class=' dropdown_4columns ';$drop_col_width=' grid-25';}
										elseif($var >= 37 and $var < 46){ $drop_down_class=' dropdown_5columns ';$drop_col_width=' grid-20';}
										echo "<div  class=$drop_down_class  id=dropdown_2columns>";
											//	echo "$mod1 -- $mod2 -- $mod3 --  $mod -- $var -- $drop_down_class -- $drop_col_width";
												//echo "<ul>";//<!--level II menu list <ul id=nav2>-->
												$class='';
												$i=1;
												
												$open_div=true;
												foreach ($s2 as $row2)
												{
													//echo "<br>$row2[name]";
													if(($i % 11) != 0){
														if($open_div){echo "<div class=$drop_col_width>";$open_div=false;}
													}
													else{
														echo "</div><div class=$drop_col_width>";
														$i++;
													}													
														echo "<div class='grid-100 li_div'><a  class='level2 $row2[classes]' href=?id=$row2[tab_name]>";htmlout("$row2[name]");echo"</a>";
														
																$placeholders3=array();
																$sql="select a.id, a.name, a.tab_name from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id  and a.parent_id =$row2[id] and a.level = 3  order by arrangement_order";
																$error="Unable to select menu";									
																$placeholders3[':user_id']=$_SESSION['id'];
																$s3 = 	select_sql($sql, $placeholders3, $error, $pdo);		
																//check in roles if individual privileges does not procude anything
																if($s3->rowCount() == 0){
																	$sql="select a.id, a.name, a.tab_name from menus a, user_roles c, role_privileges b where c.user_id=:user_id and c.role_id=b.role_id and
																	a.id=b.menu_id  and a.parent_id =$row2[id] and a.level = 3  order by arrangement_order";//1 is patients
																	$s3 = 	select_sql($sql, $placeholders3, $error, $pdo);	
																}									
																if($s3->rowCount() > 0){
																	echo "<div class=li_div2>";//<!--level III menu list-->
																	foreach ($s3 as $row3)
																	{
																		//echo "<li><a href=?id=$row3[tab_name]>";htmlout("$row3[name]");echo"</a></li>";
																		echo "<div class='grid-100 li_div3'><a href=?id=$row3[tab_name]>";htmlout("$row3[name]");echo"</a></div>";
																	}
																	echo "</div>";//end Level III list													
																}
														echo "</div>";//end li_div
													//}
													/*else{
														echo "</div><div class=$drop_col_width>";
													}*/

													$i++;
													
												}
													echo "</div>";//end last col_1										
										echo "</div>";										
									}
									//show normal drop down for other items
									else{
										echo "<li class=''><a class='level0  ' href=?id=$row[tab_name]>";htmlout("$row[name]");echo"</a>";
						
										echo "<ul id=nav2>";//<!--level II menu list <ul id=nav2>-->
										$class='';
										foreach ($s2 as $row2)
										{
											echo "<li><a  class='level2 $row2[classes]' href=?id=$row2[tab_name]>";htmlout("$row2[name]");echo"</a>";
									
												$placeholders3=array();
												$sql="select a.id, a.name, a.tab_name from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id  and a.parent_id =$row2[id] and a.level = 3  order by arrangement_order";
												$error="Unable to select menu";									
												$placeholders3[':user_id']=$_SESSION['id'];
												$s3 = 	select_sql($sql, $placeholders3, $error, $pdo);		
												//check in roles if individual privileges does not procude anything
												if($s3->rowCount() == 0){
													$sql="select a.id, a.name, a.tab_name from menus a, user_roles c, role_privileges b where c.user_id=:user_id and c.role_id=b.role_id and
													a.id=b.menu_id  and a.parent_id =$row2[id] and a.level = 3  order by arrangement_order";//1 is patients
													$s3 = 	select_sql($sql, $placeholders3, $error, $pdo);	
												}									
												echo "<ul>";//<!--level III menu list-->
												foreach ($s3 as $row3)
												{
													echo "<li><a href=?id=$row3[tab_name]>";htmlout("$row3[name]");echo"</a></li>";
												}
												echo "</ul>";//end Level III list
											echo"</li>";//end level II list item
										}
										echo "</ul>";//end level II list
									}//end else
							
							echo "</li> ";//end level I list item
						//}
						/*elseif($row['name']=='Admin'){
							//show level 2 menu
							$placeholders=array();
							$sql="select a.id, a.name, a.tab_name, a.classes  from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id and a.parent_id =$row[id] and a.level = 2 order by arrangement_order";
							$error="Unable to select menu";
							$placeholders[':user_id']=$_SESSION['id'];
							$s2 = 	select_sql($sql, $placeholders, $error, $pdo);
							//check in roles if individual privileges does not procude anything
							if($s2->rowCount() == 0){
								$sql="select a.id, a.name, a.tab_name, a.classes from menus a, user_roles c, role_privileges b where c.user_id=:user_id and c.role_id=b.role_id and
								a.id=b.menu_id and a.parent_id =$row[id] and a.level = 2 order by arrangement_order";//1 is patients
								$s2 = 	select_sql($sql, $placeholders, $error, $pdo);
							}
							if{$s2->

							echo "</ul>"
							echo "<div  class='dropdown_2columns'>";
									echo "<ul id=nav2>";//<!--level II menu list <ul id=nav2>-->
									$class='';
									$i=0
									foreach ($s2 as $row2)
									{
										if($i < 9){
											if($i==0){echo "<div class=col_1>";}
											echo "<li><a  class='level2 $row2[classes]' href=?id=$row2[tab_name]>";htmlout("$row2[name]");echo"</a></li>";
										}
										if($i >=9 {
											if($i==0){echo "</div><div class=col_1>";}
											echo "<li><a  class='level2 $row2[classes]' href=?id=$row2[tab_name]>";htmlout("$row2[name]");echo"</a></li>";
										}
										$i++;
									}
										echo "</div>";//end last col_1										
							echo "</div>";	
							echo "</li>";

							
						}
					//echo "</div>";*/
					}
			
			?>
			<li><a href=?id=log-out>Log Out <?php htmlout("$_SESSION[user_name]"); ?></a></li>
			</ul><!-- end nav1 -->
			</div>
		 </div>
		 </div>

			

	
	<?php 
echo "<div class=clear></div><div   class='grid-container main_content'>"; 
?>
	
<?php
if(isset($_GET['id'])){
echo "<div class='grid-100 get_width'></div>
	<div class='grid-100 div_shower'></div>
	<div class='grid-100 div_shower2 procedure_container2 '></div>
	";
if($_GET['id']=='insurance-company'){if(userHasRole($pdo,9)){include_once     '../dental_includes/insurance_company.php';}
														elseif(!userHasRole($pdo,9)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='employer'){if(userHasRole($pdo,10)){include_once     '../dental_includes/employer.php';}
														elseif(!userHasRole($pdo,10)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='patient-referrer'){if(userHasRole($pdo,11)){include_once     '../dental_includes/patient_referrer.php';}
														elseif(!userHasRole($pdo,11)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														}														  
if($_GET['id']=='patient-relationships'){if(userHasRole($pdo,21)){include_once     '../dental_includes/patient_relationships.php';}
														elseif(!userHasRole($pdo,21)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='procedures'){if(userHasRole($pdo,23)){include_once     '../dental_includes/procedures.php';}
														elseif(!userHasRole($pdo,23)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='x-rays'){if(userHasRole($pdo,24)){include_once     '../dental_includes/xray_types.php';}
														elseif(!userHasRole($pdo,24)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='add-user'){if(userHasRole($pdo,25)){include_once     '../dental_includes/add_user.php';}
														elseif(!userHasRole($pdo,25)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='xray-referrer'){if(userHasRole($pdo,27)){include_once     '../dental_includes/add_xray_referrer.php';}
														elseif(!userHasRole($pdo,27)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='lab-technician'){if(userHasRole($pdo,26)){include_once     '../dental_includes/add_lab_technician.php';}
														elseif(!userHasRole($pdo,26)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='lab-prescription-form'){if(userHasRole($pdo,29)){include_once     '../dental_includes/lab_prescription_form.php';}
														elseif(!userHasRole($pdo,29)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='work-due-out'){if(userHasRole($pdo,30)){include_once     '../dental_includes/work_due_out.php';}
														elseif(!userHasRole($pdo,30)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='work-due-in'){if(userHasRole($pdo,31)){include_once     '../dental_includes/work_due_in.php';}
														elseif(!userHasRole($pdo,31)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='lab-payments'){if(userHasRole($pdo,33)){include_once     '../dental_includes/lab_payments.php';}
														elseif(!userHasRole($pdo,33)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='receive-trays'){if(userHasRole($pdo,35)){include_once     '../dental_includes/receive_lab_trays.php';}
														elseif(!userHasRole($pdo,35)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='finished-patient-work'){if(userHasRole($pdo,36)){include_once     '../dental_includes/finished_patient_work.php';}
														elseif(!userHasRole($pdo,36)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='user-privileges'){if(userHasRole($pdo,44)){include_once     '../dental_includes/user_privileges.php';}
														elseif(!userHasRole($pdo,44)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='roles'){if(userHasRole($pdo,43)){include_once     '../dental_includes/roles.php';}
														elseif(!userHasRole($pdo,43)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='surgery-units'){if(userHasRole($pdo,39)){include_once     '../dental_includes/surgery_units.php';}
														elseif(!userHasRole($pdo,39)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }  
if($_GET['id']=='book-appointment'){if(userHasRole($pdo,45)){include_once     '../dental_includes/book_appointment.php';}
														elseif(!userHasRole($pdo,45)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }			
if($_GET['id']=='working-hours'){if(userHasRole($pdo,46)){include_once     '../dental_includes/working_hours.php';}
														elseif(!userHasRole($pdo,46)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															
if($_GET['id']=='loyalty-points'){if(userHasRole($pdo,42)){include_once     '../dental_includes/loyalty_points.php';}
														elseif(!userHasRole($pdo,42)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }				
if($_GET['id']=='allocate-patients'){if(userHasRole($pdo,48)){include_once     '../dental_includes/allocate_patients.php';}
														elseif(!userHasRole($pdo,48)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='insurance-payments'){if(userHasRole($pdo,49)){include_once     '../dental_includes/insurance_payment.php';}
														elseif(!userHasRole($pdo,49)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='self-payments'){if(userHasRole($pdo,50)){include_once     '../dental_includes/self_payments.php';}
														elseif(!userHasRole($pdo,50)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='edit-treatment-plan'){if(userHasRole($pdo,51)){include_once     '../dental_includes/edit_treatment_plan.php';}
														elseif(!userHasRole($pdo,51)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }			
if($_GET['id']=='waiver-approval'){if(userHasRole($pdo,52)){include_once     '../dental_includes/waiver_approval.php';}
														elseif(!userHasRole($pdo,52)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='xray-referal'){if(userHasRole($pdo,54)){include_once     '../dental_includes/xray_referal.php';}
														elseif(!userHasRole($pdo,54)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='invoice-authorisation'){if(userHasRole($pdo,57)){include_once     '../dental_includes/invoice_authorisation.php';}
														elseif(!userHasRole($pdo,57)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='edit-invoice'){if(userHasRole($pdo,60)){include_once     '../dental_includes/edit_invoice.php';}
														elseif(!userHasRole($pdo,60)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='prescription-drugs'){if(userHasRole($pdo,61)){include_once     '../dental_includes/prescription_drug.php';}
														elseif(!userHasRole($pdo,61)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='invoice-dispatch'){if(userHasRole($pdo,58)){include_once     '../dental_includes/invoice_dispatch.php';}
														elseif(!userHasRole($pdo,58)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='edit-dispatch'){if(userHasRole($pdo,59)){include_once     '../dental_includes/edit_dispatch.php';}
														elseif(!userHasRole($pdo,59)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='patient-swap'){if(userHasRole($pdo,41)){include_once     '../dental_includes/patient_swap.php';}
														elseif(!userHasRole($pdo,41)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='expense-type'){if(userHasRole($pdo,63)){include_once     '../dental_includes/expense_type.php';}
														elseif(!userHasRole($pdo,63)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='record-expense'){if(userHasRole($pdo,64)){include_once     '../dental_includes/record_expense.php';}
														elseif(!userHasRole($pdo,64)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='dispatch-report'){if(userHasRole($pdo,65)){include_once     '../dental_includes/dispatch_report.php';}
														elseif(!userHasRole($pdo,65)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='invoice-authorisations'){if(userHasRole($pdo,66)){include_once     '../dental_includes/invoice_authorisations_report.php';}
														elseif(!userHasRole($pdo,66)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='cadcam-type'){if(userHasRole($pdo,68)){include_once     '../dental_includes/cadcam_type.php';}
														elseif(!userHasRole($pdo,68)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='cadcam-stock'){if(userHasRole($pdo,69)){include_once     '../dental_includes/cadcam_stock.php';}
														elseif(!userHasRole($pdo,69)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='appointments-report'){if(userHasRole($pdo,70)){include_once     '../dental_includes/appointments_report.php';}
														elseif(!userHasRole($pdo,70)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='login-times-report'){if(userHasRole($pdo,71)){include_once     '../dental_includes/login_time_report.php';}
														elseif(!userHasRole($pdo,71)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='patient-payments'){if(userHasRole($pdo,72)){include_once     '../dental_includes/patient_payments.php';}
														elseif(!userHasRole($pdo,72)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='swapped-patients'){if(userHasRole($pdo,73)){include_once     '../dental_includes/swapped_patients_report.php';}
														elseif(!userHasRole($pdo,73)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='cadcam-referal'){if(userHasRole($pdo,55)){include_once     '../dental_includes/cadcam_referal.php';}
														elseif(!userHasRole($pdo,55)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='cadcam-referrers'){if(userHasRole($pdo,76)){include_once     '../dental_includes/cadcam_referrers.php';}
														elseif(!userHasRole($pdo,76)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='income-report'){if(userHasRole($pdo,77)){include_once     '../dental_includes/income_report.php';}
														elseif(!userHasRole($pdo,77)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='invoice-payments'){if(userHasRole($pdo,79)){include_once     '../dental_includes/invoice_payment_report.php';}
														elseif(!userHasRole($pdo,79)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='edit-appointments'){if(userHasRole($pdo,80)){include_once     '../dental_includes/edit_appointments.php';}
														elseif(!userHasRole($pdo,80)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='referred-xrays'){if(userHasRole($pdo,83)){include_once     '../dental_includes/referred_xrays_report.php';}
														elseif(!userHasRole($pdo,83)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='referred-cadcam'){if(userHasRole($pdo,84)){include_once     '../dental_includes/referred_cadcam_report.php';}
														elseif(!userHasRole($pdo,84)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='cash-balance'){if(userHasRole($pdo,85)){include_once     '../dental_includes/cash_balance_report.php';}
														elseif(!userHasRole($pdo,85)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='backup-database'){if(userHasRole($pdo,86)){include_once     '../dental_includes/backup_database.php';}
														elseif(!userHasRole($pdo,86)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='doctor-commisions'){if(userHasRole($pdo,87)){include_once     '../dental_includes/doctor_commissions.php';}
														elseif(!userHasRole($pdo,87)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='treatment-rate'){if(userHasRole($pdo,88)){include_once     '../dental_includes/treatment_rate.php';}
														elseif(!userHasRole($pdo,88)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='treatment-done'){if(userHasRole($pdo,89)){include_once     '../dental_includes/treatment_done_report.php';}
														elseif(!userHasRole($pdo,89)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='patient-lists'){if(userHasRole($pdo,90)){include_once     '../dental_includes/patient_list.php';}
														elseif(!userHasRole($pdo,90)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='reprint-prescriptions'){if(userHasRole($pdo,92)){include_once     '../dental_includes/reprint_prescriptions.php';}
														elseif(!userHasRole($pdo,92)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='drugs-sold'){if(userHasRole($pdo,93)){include_once     '../dental_includes/drugs_sold.php';}
														elseif(!userHasRole($pdo,93)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='expenses-report'){if(userHasRole($pdo,94)){include_once     '../dental_includes/expenses_report.php';}
														elseif(!userHasRole($pdo,94)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='follow-ups'){if(userHasRole($pdo,95)){include_once     '../dental_includes/follow_ups.php';}
														elseif(!userHasRole($pdo,95)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='cash-pledges'){if(userHasRole($pdo,96)){include_once     '../dental_includes/cash_pledges.php';}
														elseif(!userHasRole($pdo,96)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='delete-payment'){if(userHasRole($pdo,98)){include_once     '../dental_includes/delete_payment.php';}
														elseif(!userHasRole($pdo,98)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='delete-invoice'){if(userHasRole($pdo,99)){include_once     '../dental_includes/delete_invoice.php';}
														elseif(!userHasRole($pdo,99)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='delete-appointment'){if(userHasRole($pdo,101)){include_once     '../dental_includes/delete_appointments.php';}
														elseif(!userHasRole($pdo,101)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }			
if($_GET['id']=='delete-expense'){if(userHasRole($pdo,100)){include_once     '../dental_includes/delete_expenses.php';}
														elseif(!userHasRole($pdo,100)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='deleted-payments'){if(userHasRole($pdo,102)){include_once     '../dental_includes/deleted_payments.php';}
														elseif(!userHasRole($pdo,102)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='deleted-invoices'){if(userHasRole($pdo,103)){include_once     '../dental_includes/deleted_invoices.php';}
														elseif(!userHasRole($pdo,103)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='surgery-reports'){if(userHasRole($pdo,104)){include_once     '../dental_includes/surgery_reports.php';}
														elseif(!userHasRole($pdo,104)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='procedure-count'){if(userHasRole($pdo,105)){include_once     '../dental_includes/procedure_count_report.php';}
														elseif(!userHasRole($pdo,105)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='visa-banks'){if(userHasRole($pdo,106)){include_once     '../dental_includes/visa_bank_types.php';}
														elseif(!userHasRole($pdo,106)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }
if($_GET['id']=='reminders'){if(userHasRole($pdo,40)){include_once     '../dental_includes/reminders.php';}
														elseif(!userHasRole($pdo,40)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='password-change'){if(userHasRole($pdo,38)){include_once     '../dental_includes/password_change.php';}
														elseif(!userHasRole($pdo,38)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='patient_notes'){if(userHasRole($pdo,107)){include_once     '../dental_includes/patient_notes.php';}
														elseif(!userHasRole($pdo,107)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }		
if($_GET['id']=='invoice-search'){if(userHasRole($pdo,108)){include_once     '../dental_includes/invoice_search.php';}
														elseif(!userHasRole($pdo,108)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
if($_GET['id']=='partially-authorised'){if(userHasRole($pdo,112)){include_once     '../dental_includes/partially_authorised.php';}
														elseif(!userHasRole($pdo,112)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='email-credentials'){if(userHasRole($pdo,114)){include_once     '../dental_includes/email_credentials.php';}
														elseif(!userHasRole($pdo,114)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='prescription_stock_in'){if(userHasRole($pdo,115)){include_once     '../dental_includes/prescription_stock_in.php';}
														elseif(!userHasRole($pdo,115)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='patient-searches'){if(userHasRole($pdo,117)){include_once     '../dental_includes/patient_searches_report.php';}
														elseif(!userHasRole($pdo,117)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='unauthorise-invoice'){if(userHasRole($pdo,118)){include_once     '../dental_includes/unauthorise_invoice.php';}
														elseif(!userHasRole($pdo,118)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='tplan-visits'){if(userHasRole($pdo,120)){include_once     '../dental_includes/tplan_visits.php';}
														elseif(!userHasRole($pdo,120)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='expedite-reasons'){if(userHasRole($pdo,121)){include_once     '../dental_includes/expedite_reasons.php';}
														elseif(!userHasRole($pdo,121)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }	
if($_GET['id']=='shcool-holiday-appt'){if(userHasRole($pdo,122)){include_once     '../dental_includes/school_holiday_appointment.php';}
														elseif(!userHasRole($pdo,122)){?><div class="grid-100 no_js_text">ERROR: ACCESS DENIED!</div><?php }
														  }															  
												//echo "</div>";//container														
elseif($_GET['id']=='patient'){//$_SESSION['tab_id']=$_GET['jid'];//include_once     '../inventory_includes/tabs.php';
	//echo "<div class=clear></div><div   class='grid_12 main_content'>";echo "ddd";
	//show level 2 menus for supplier tabs
		$placeholders2s=array();
		$sql2s=$s2s=$error2s='';
		$sql2s="SELECT a.id, a.name,a.tab_name from menus a, privileges b where b.user_id=:user_id and a.id=b.menu_id and 
		a.parent_id = (select id from menus where 	tab_name =:parent_tab_name) and a.level = 2 order by a.arrangement_order";
		$placeholders2s[':user_id']=$_SESSION['id'];
		$placeholders2s[':parent_tab_name']=$_GET['id'];
		$error2s="Unable to select menu";
		$s2s = 	select_sql($sql2s, $placeholders2s, $error2s, $pdo);
		//check in roles if individual privileges does not procude anything
		if($s2s->rowCount() == 0){
			$placeholders2s=array();
			$sql2s=$s2s=$error2s='';
			$sql2s="SELECT a.id, a.name, a.tab_name, a.classes from menus a, user_roles c, role_privileges b where c.user_id=:user_id 
					and c.role_id=b.role_id and	a.id=b.menu_id and a.parent_id =(select id from menus where tab_name =:parent_tab_name)
					and a.level = 2 order by arrangement_order";//1 is patients
			$placeholders2s[':user_id']=$_SESSION['id'];
			$placeholders2s[':parent_tab_name']=$_GET['id'];
			$s2s = 	select_sql($sql2s, $placeholders2s, $error2s, $pdo);
		}
?>

<div  id="tabs" >
	<ul>

		<?php
		//this will pass variables from ajax tabs back to the same ajax tabs
		//this is for patient contacts
		/*if(isset($_SESSION['token_a1_patinet']) and isset($_POST['token_a1_patinet']) and $_POST['token_a1_patinet']==$_SESSION['token_a1_patinet']){
			//echo "<br>$_POST is ".print_r($_POST)."<br>".print_r($_FILES['image_upload']);
			//echo "count is ".count($_FILES['image_upload']);
			//echo "<br>name is ".$_FILES["image_upload"]["name"];
			//echo "<br>session is ".$_SESSION['photo_path'];exit;
			if(isset($_FILES["image_upload"])){
				if($_FILES["image_upload"]["name"]!=''){
					$upload=upload_photo($_FILES['image_upload']);
					$_POST['upload_status']= "$upload";
				}
				elseif($_FILES["image_upload"]["name"]==''){
					$upload="GOODsplitter$_SESSION[photo_path]";
					$_POST['upload_status']= "$upload";
				}
			}
			$_SESSION['post']=array();
			$_SESSION['post']=$_POST;
		}
		//this is for patient searches
		if(isset($_POST['token_search_patient']) and $_POST['token_search_patient']!=''){
			$_SESSION['post']=array();
			$_SESSION['post']=$_POST;
		}*/	
		$tabs_div=array();
		foreach ($s2s as $row2s)
		{
			$menu_name=html("$row2s[name]");
			$row2s['tab_name']=html($row2s['tab_name']);
			
			//echo 	"<li ><a class='tab_link tab_button' href='#test'>$menu_name</a></li>";
				echo 	"<li ><a class='tab_link tab_button' href='$row2s[tab_name]/'>$menu_name</a></li>";
			
			//$tabs_div[]=$row2s['tab_name'];
			//echo 	"<li><a href='#$row2s[id]'>$menu_name</a></li>";
			//$tabs_div[]=$row2s['id'];
		}	

			

		//PRINT_R($tabs_div);
		?>
	</ul>

	<?php
	/*foreach ($tabs_div as $div){
		//patient contacts
		if($div == 'patient-contacts'){if(userHasRole($pdo,12)){echo "<div  class='$div' id='$div'>"; include_once 	"$div/index.php"; echo "</div>";}
												elseif(!userHasRole($pdo,11)){?><div id=patient-contacts class="grid_12 alpha omega no_js_text">ERROR: ACCESS DENIED!</div><?php }
											}	
		//dental information
		elseif($div == 'dental-information'){if(userHasRole($pdo,13)){echo "<div class='$div' id='$div'>"; include_once 	"$div/index.php"; echo "</div>";}
												elseif(!userHasRole($pdo,12)){?><div id=dental-information class="grid_12 no_js_text">ERROR: ACCESS DENIED!</div><?php }
												}
		//diseases
		elseif($div == 'diseases'){if(userHasRole($pdo,16)){echo "<div class='$div' id='$div'>"; include_once 	"$div/index.php"; echo "</div>";}
												elseif(!userHasRole($pdo,12)){?><div id=diseases class="grid_12 no_js_text">ERROR: ACCESS DENIED!</div><?php }
												}
		//compeltion
		elseif($div == 'completion'){if(userHasRole($pdo,17)){echo "<div class='$div' id='$div'>"; include_once 	"$div/index.php"; echo "</div>";}
												elseif(!userHasRole($pdo,12)){?><div id=completion class="grid_12 no_js_text">ERROR: ACCESS DENIED!</div><?php }
												}
		//medical information
		elseif($div == 'medical-information'){if(userHasRole($pdo,13)){echo "<div class='$div' id='$div'>"; include_once 	"$div/index.php"; echo "</div>";}
												elseif(!userHasRole($pdo,12)){?><div id=medical-information class="grid_12 no_js_text">ERROR: ACCESS DENIED!</div><?php }
												}												
	}*/?>

</div>
<?php
//echo "</div>";//for grid_12	
}

}//end if(isset($_GET['jid'])
else{get_user_notifications($pdo);
if(userHasRole($pdo,57)){invoices_pending_authorisation($pdo);}
}
echo "</div>";




	}  

?>
<!--
<div id="footer_container">
		<div id="wrapper" class="container_12">
			<div class="grid_4 "><a class="password_link footer_text" href=http://www.atkenya.com/terms target=_blank>Terms and Conditions</a></div>
			<div class="grid_4 prefix_4 copyright">ATKENYA &copy; <?php echo date('Y'); ?></div>
	</div><!-- end wrapper 
</div>-->
<div id=dialogs>
	<input type=hidden id=tab_number value='0' />
<div class=message></div></div>
<div class='grid-100 div_shower31a'></div>	
<div class="grid-container" id="show_all_dialogs"></div>
<div  id="print_all_dialogs"></div>
<div  class="grid-100 get_width">&nbsp;</div>


</body>
</html>