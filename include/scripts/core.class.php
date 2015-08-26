<?php
/*
CheesecakeCore
*/

//error_reporting(E_ALL);
if(!defined("CCore")){
	die("Access Denied.");
}
class admin {
	public function __construct($settings, $version, $dbc, $layout, $core, $parser, $cms_vars){
		$this->settings = $settings;
		$this->version = $version;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;
		$this->vars = $cms_vars;

	}
	public function array2php($arr, $arrName){
		$out = '<?php $'.$arrName.' = array(';
		foreach( $arr as $k => $v ){
			if( is_bool($v) ){
				$v = ( $v ) ? 'true' : 'false';
			}
			else{
				$v = "\"".$v."\"";
			}
			$out .= " \"".$k."\" => ".$v.",";
		}
		$out = rtrim($out, ",");
		$out .= ' ); ?>';

		return $out;
	}
	public function counter(){
		$this->core->isLoggedIn();
		$query = "SELECT * FROM `views`";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$C = count($row);
		$uC = array_unique($row);
		$uCL = count($uC);
		echo '
		<div class="shadowbar">
		<table class="table">
		<thead><th>View Counter</th></thead>
		<tr><td>View Count:</td><td>'.$C.'</td></tr>
		<tr><td>Unique View Count:</td><td>'.$uCL.'</td></tr>
		</table>
		</div>
		';
	}
	public function groupEdit(){
		if(!$this->core->verify("core.*")){
			die('Insignificant Permission');
		}
		if(isset($_POST['submit'])){
			$groupID = mysqli_real_escape_string($this->dbc, trim($_POST['gID']));
			$perms = mysqli_real_escape_string($this->dbc, trim($_POST['perms']));
			$gName = mysqli_real_escape_string($this->dbc, trim($_POST['gName']));
			$query = "UPDATE `groups` SET `groupName` = '$gName', `groupPerms` = '$perms' WHERE `groupID` = '$groupID' ";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">Group Updated</div>';
			exit();
		}
		$gID = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
		$secureGroup = preg_replace("/[^0-9]/", "", $gID);
		$query = "SELECT * FROM `groups` WHERE `groupID` = '$secureGroup' ";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		echo sprintf($this->layout['groupEditLayout'], $row['groupName'], $row['groupPerms'], $secureGroup);
	}
	public function groups(){
		if(!$this->core->verify("core.*")){
			die('Insignificant Permission');
		}
		if(isset($_POST['submit'])){
		$gName = mysqli_real_escape_string($this->dbc, trim($_POST['groupName']));
		$gPerms = mysqli_real_escape_string($this->dbc, trim($_POST['groupPerms']));
		$query = "INSERT INTO groups (`groupName`, `groupPerms`) VALUES ('$gName', '$gPerms')";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar">Group Added</div>';
		}
		if(isset($_GET['mode'])){
		if($_GET['mode'] == 'deleteGroup'){
		$gID = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
		$query = "DELETE FROM groups WHERE groupID = '$gID' ";
		$data = mysqli_query($this->dbc, $query);
		}
		}
		print ($this->layout['addGroup']);
		$query = "SELECT * FROM groups";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)){
			echo sprintf($this->layout['userGroupsAdmin'], $row['groupName'], $row['groupPerms'], $row['groupID'], $row['groupID'], $row['groupID']);
		}
	}
	public function delu(){
		if(!$this->core->verify("core.*")){
			exit();
		}
		$this->core->isLoggedIn();
		if (isset($_POST['submit'])) {
			$userid = mysqli_real_escape_string($this->dbc, trim($_POST['userid']));
			if (!empty($userid)) {
				$query = "DELETE FROM users WHERE uid = $userid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>User has been successfully deleted. Would you like to <a href="/acp/mode/users">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<div class="shadowbar"><p class="error">You must enter information into all of the fields.</p></div>';
			}
		}

		if($_GET['del'] == $_SESSION['uid']){
			die('<div class="shadowbar">Cannot delete yourself.</div>');
		}
		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/acp/mode/deleteuser">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>
			<input type="hidden" name="userid" value="'.$_GET['del'].'">';
		echo 'User ID: ' . $_GET['del'] . '<br /> <br />';
		echo'</fieldset>
		<input type="submit" value="Delete User" name="submit" />    <a class="button" href="/acp">Cancel</a>
	</form>
	</div>';
	}
	public function eur(){
		$this->core->isLoggedIn();
		if (isset($_POST['submit'])) {
			$user = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['user'])));
			$perm = mysqli_real_escape_string($this->dbc, trim($_POST['perm']));
			if (!empty($perm) && !empty($user)) {
				$query = "UPDATE `users` SET `adminlevel` = '$perm' WHERE `uid` = '$user'";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>User has been successfully edited. Would you like to <a href="/acp">return to the ACP</a>?</p></a>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}

		}
		if(!$this->core->verify("core.*")){
			exit();

		}
		echo'<div class="shadowbar"><table class="table">
		<tr>
		<th>Perms:</th>
		<th>Description of Permission:</th>
		</tr>
		<tr>
		<td>4</td>
		<td>Global Admin.</td>
		</tr>
		<tr>
		<td>2</td>
		<td>Moderator</td>
		</tr>
		</table>
		</div>
	';




		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/acp/mode/editperms">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
			<label type="hidden" for="perm">Permission:</label><br />
			<input type="rank" name="perm"><br /><br />
			<input type="hidden" name="user" value="'. $_GET['r'] .'">
		</fieldset>
		<input type="submit" value="Save User" name="submit" />
	</form>
	</div>';
	}
	public function eug(){
		$this->core->isLoggedIn();
		if (isset($_POST['submit'])) {
			$user = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['user'])));
			$perm = mysqli_real_escape_string($this->dbc, trim($_POST['perm']));
			if (!empty($perm) && !empty($user)) {
				$query = "UPDATE `users` SET `group` = '$perm' WHERE `uid` = '$user'";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>User has been successfully edited. Would you like to <a href="/acp">return to the ACP</a>?</p></a>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}

		}
		if(!$this->core->verify("core.*")){
			exit();

		}
		echo'<div class="shadowbar"><form method="post" action="/acp/mode/editgroup">
		<fieldset>
			<label type="hidden" for="perm">Group:</label><br />';
		echo'<select id="perm" name="perm">';
		$query = "SELECT * FROM groups";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			echo '<option value="'.$row['groupID'].'">'.$row['groupName'].'</option>';
		}
		echo'</select><br /><br />
			<input type="hidden" name="user" value="'. $_GET['r'] .'">
		</fieldset>
		<input type="submit" value="Save User" name="submit" />
	</form>
	</div>';
	}
	public function usr() {
		echo '<div class="shadowbar">';
		if(!$this->core->verify("core.*")){
			exit();
		}


		// Connect to the database

		// Grab the profile data from the database
		$query = "SELECT * FROM users ORDER BY uid DESC";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			echo sprintf($this->layout['adminUserLayout'], $row['username'], $row['uid'], $row['uid'], $row['activated'], $row['hash'], $row['group'], $row['uid'], $row['uid'], $row['uid']);
		}
		echo '</div>';
	}
	public function uploadModule(){
		if(!$this->core->verify("core.*")){
			die('<div class="shadowbar">You don\'t have significant privilege</div>');
		}
		if(isset($_POST['submit'])){
			$zip = new ZipArchive;
			$f = $zip->open($_FILES['module']['tmp_name']);
			if($f === true){
				$zip->extractTo('include/scripts/');
				$zip->close();
				$modInfoPath = explode(".", $_FILES["module"]["name"])[0];
				$modInfo = file_get_contents('include/scripts/'.$modInfoPath.'/info.json');
				$modInfo = json_decode($modInfo, true);
				$mName = $modInfo['mName'];
				$mFile = $modInfo['mFile'];
				$mNav = $modInfo['mNav'];
				$mClass = $modInfo['mClass'];
				$mStats = $modInfo['mStats'];
				$mAdmin = $modInfo['mAdmin'];
				$mPerms = $modInfo['mPerms'];
				$mHref = $modInfo['mHref'];
				$mSidebar = $modInfo['mSidebar'];
				$mSBD = $modInfo['mSBD'];
				$mACP = $modInfo['mACP'];
				$mLink = str_replace(" ", "", $mName);
				$moduleFile = file_get_contents("modules.php");
				$moduleFile = str_replace("?>", "", $moduleFile);
				$moduleFile .= "\r\n" . '$modules["'.$mName.'"] = array( "description"=> "'.$mName.'","link"=> "'.$mFile.'","enabled"=> "1","nav"=>"'.$mNav.'","admin"=>"'.$mAdmin.'","perms"=>"'.$mPerms.'","class"=>"'.$mClass.'","stats"=>"'.$mStats.'","href"=>"'.$mHref.'","sidebar"=>"'.$mSidebar.'","sidebarDesc"=>"'.$mSBD.'","acp"=>"'.$mACP.'"); ' . "\r\n?>";
				$moduleFilePath = "include/scripts/".$mFile;
				file_put_contents("modules.php", $moduleFile);
				echo '<div class="shadowbar">Module uploaded and activated successfully.</div>';
			}
		}	else {
			echo '
			<div class="shadowbar">
				<h3>Upload Module</h3>
				<form action="/acp/mode/uploadMod" enctype="multipart/form-data" method="post">
				  <input type="file" name="module" />
				  <input type="submit" name="submit" value="Submit" />
				</form>
			</div>
			';
		}
	}
	public function addModule(){
		if(!$this->core->verify("core.*")){
			die('<div class="shadowbar">You don\'t have significant privilege</div>');
		}
		if($this->settings['dev'] == '1'){
		if(isset($_POST['submit'])){
			$mName = $_POST['mName'];
			$mFile = $_POST['mFile'];
			$mLink = str_replace(" ", "", $mName);
			$moduleFile = file_get_contents("modules.php");
			$moduleFile = str_replace("?>", "", $moduleFile);
			$moduleFile .= "\r\n" . '$modules["'.$mName.'"] = array( "description"=> "'.$mName.'","link"=> "'.$mFile.'","enabled"=> "1","admin"=>"","class"=>"","stats"=>"false","href"=>"/'.$mLink.'","sidebar"=>"/'.$mLink.'","sidebarDesc"=>"'.$mName.'","acp"=>""); ' . "\r\n?>";
			$moduleFilePath = "include/scripts/".$mFile;
			file_put_contents("modules.php", $moduleFile);
			file_put_contents($moduleFilePath, $this->layout['moduleTemplate']);
		}
		print($this->layout['moduleAddForm']);
	} else {
		echo '<div class="shadowbar">Please enable developer mode.</div>';
	}
	}
	public function editModule(){
		if(!$this->core->verify("core.*")){
			die('<div class="shadowbar">You don\'t have significant privilege</div>');
		}
		if($this->settings['dev'] == '1'){
			require("modules.php");
				if(isset($_POST['submit'])){
					$module = $_POST['modules'];
					$file = $_POST['file'];
					file_put_contents($file, $module);
					echo '<div class="shadowbar">Module updated</div>';
				}
				if(isset($_POST['fileName'])){
					$mFile = file_get_contents($_POST['moduleFile']);
					$mFileName = $_POST['moduleFile'];
					$mFile = htmlspecialchars($mFile);
				}
				echo '<div class="shadowbar">
				<form action="/acp/mode/editModule" method="post">
				<select name="moduleFile">';
				foreach($modules as $name => $module) if ($module['enabled']) {
					echo '<option value="include/scripts/'.$module['link'].'">'.$module['link'].'</option>';
				}
				echo '</select><input type="submit" name="fileName" value="Edit File" class="Link LButton" /></div>';
		if(isset($mFile)){
				echo '
					<div class="shadowbar">
						<form method="post" action="/acp/mode/editModule">
						<fieldset>
						<legend>Module</legend>
						<div class="input-group" style="width:100%">
						<input type="hidden" value="'.$mFileName.'" name="file" />
						<textarea style="width:100%" placeholder="Module" name="modules" id="codeEdit" rows="50">'.$mFile.'</textarea><br />
						</div>
						</fieldset>
						<input class="Link LButton" type="submit" value="Submit Edits" name="submit" />
					</form>
				</div>
				';
		}
		} else {
			echo '<div class="shadowbar">Please enable developer mode.</div>';
		}
	}
	public function viewError(){
		if(!isset($f)){
			$f = 0;
		}
		$query = "SELECT * FROM `err` ORDER BY `id` DESC LIMIT 0,10";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar"><table class="table"><thead><th>Errors</th></thead><tbody>';
		while($row = mysqli_fetch_array($data)){
			echo '<tr><td>';
			echo '<b><span style="color:yellow;">'.$this->core->FriendlyErrorType($row['errno']).'</span></b> <span style="color:red;">'.$row['errstr'].'</span> <span style="color:white;">in</span> <span style="color:yellow;">'.$row['errfile'].'</span> <span style="color:white;">on line</span> <span style="color:red;">'.$row['errline'].'</span>';
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
	public function acp(){
		if(!$this->core->verify("core.*") || $this->core->verify("core.mod")){
			die("Insufficient Permissions");
		}
		echo '<div class="shadowbar">
		<a class="Link LButton" href="/acp">Admin </a><a class="Link LButton" href="/acp/mode/errors">Errors </a><a class="Link LButton" href="/acp/mode/users">Users </a><a class="Link LButton" href="/acp/mode/groups">Groups </a><a class="Link LButton" href="/acp/mode/banlist">Banned Users</a><a class="Link LButton" href="/acp/mode/Settings">Settings </a><a class="Link LButton" href="/acp/mode/stats">Record Stats</a><a class="Link LButton" href="/acp/mode/counter">View Counter</a>';
		if($this->settings['dev'] == '1'){
			echo '<a class="Link LButton" href="/acp/mode/addmodule">Add Module</a><a class="Link LButton" href="/acp/mode/editModule">Edit Module</a><a class="Link LButton" href="/acp/mode/modules">Module Settings </a><a class="Link LButton" href="/acp/mode/uploadMod">Upload Module </a><a class="Link LButton" href="/acp/mode/layout">Advanced Layout Editor</a>';
		}
		echo '</div>
';

		if(isset($_GET['mode'])){
			if($_GET['mode'] == 'users'){
				$this->usr();
			}
			if($_GET['mode'] == 'errors'){
				$this->viewError();
			}
			if($_GET['mode'] == 'uploadMod'){
				$this->uploadModule();
			}
			if($_GET['mode'] == 'counter'){
				$this->counter();
			}
			if($_GET['mode'] == 'deleteuser'){
				$this->delu();
			}
			if($_GET['mode'] == 'groups'){
				$this->groups();
			}
			if($_GET['mode'] == 'deleteGroup'){
				$this->groups();
			}
			if($_GET['mode'] == 'editGroupInfo'){
				$this->groupEdit();
			}
			if($_GET['mode'] == 'editperms'){
				$this->eur();
			}
			if($_GET['mode'] == 'editgroup'){
				$this->eug();
			}
			if($_GET['mode'] == 'addmodule'){
				$this->addModule();
			}
			if($_GET['mode'] == 'editModule'){
				$this->editModule();
			}
			if($_GET['mode'] == 'banaccount'){
				if(isset($_POST['submit'])){
					$this->core->securityAgent("banacc");
				} else {
				echo '
				<div class="shadowbar">
				<form action="/acp/mode/banaccount" method="post">
					<input type="text" name="res"  placeholder="Ban Reason"/>
					<input type="hidden" name="user" value="'.$_GET['u'].'" />
					<input type="submit" name="submit" value="Ban User" class="Link LButton" />
				</form>
				</div>
				';
			}
			}
			if($_GET['mode'] == 'banip'){
				if(isset($_POST['submit'])){
					$this->core->securityAgent("banip");
				} else {
				echo '
				<div class="shadowbar">
				<form action="/acp/mode/banip" method="post">
					<input type="text" name="res"  placeholder="Ban Reason"/>
					<input type="hidden" name="user" value="'.$_GET['u'].'" />
					<input type="submit" name="submit" value="Ban User" class="Link LButton" />
				</form>
				</div>
				';
				}

			}
			if($_GET['mode'] == 'unban'){
				$bID = mysqli_real_escape_string($this->dbc, trim($_GET['b']));
				$query = "DELETE FROM bans WHERE bID = '$bID'";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar">User Unbanned</div>';
			}
			if($_GET['mode'] == 'banlist'){
				echo '<div class="shadowbar">
				<h3>Account Bans</h3>
					<table class="table">
					<thead>
					<th>User</th>
					<th>Ban Reason</th>
					<th>Options</th>
					</thead>
					<tbody>
				';
				$query = "SELECT users.*, bans.* FROM `bans` JOIN users on users.username = bans.user";
				$data = mysqli_query($this->dbc, $query);
				while($row = mysqli_fetch_array($data)){
					echo '
					<tr><td>'.$row['username'].'</td><td>'.$row['reason'].'</td><td><a href="/acp/mode/unban/b/'.$row['bID'].'">Unban User</a></td></tr>
					';
				}
				if(mysqli_num_rows($data) < 1){
					echo '<tr><td>No user accounts banned.</td></tr>';
				}
				echo '</tbody>
					</table></div>';
				echo '<div class="shadowbar">
				<h3>IP Bans</h3>
					<table class="table">
					<thead>
					<th>User</th>
					<th>Ban Reason</th>
					<th>Options</th>
					</thead>
					<tbody>
				';
				$query = "SELECT users.*, bans.* FROM `bans` JOIN users on users.ip = bans.user";
				$data = mysqli_query($this->dbc, $query);
				while($row = mysqli_fetch_array($data)){
					echo '
					<tr><td>'.$row['username'].' IP: '.$row['user'].'</td><td>'.$row['reason'].'</td><td><a href="/acp/mode/unban/b/'.$row['bID'].'">Unban User</a></td></tr>
					';
				}
				if(mysqli_num_rows($data) < 1){
					echo '<tr><td>No users banned by IP.</td></tr>';
				}
				echo '</tbody>
					</table></div>';
			}
			if($_GET['mode'] == 'layout'){
				if($this->settings['dev'] == '1'){
				if(isset($_POST['submit'])){
					$layoutSettings = $_POST['layout'];
					file_put_contents("include/scripts/layout.php", $layoutSettings);
					echo '<div class="shadowbar">Layout file updated</div>';
				}
				$layoutFile = file_get_contents("include/scripts/layout.php");
				$layoutFile = htmlspecialchars($layoutFile);
echo (
<<<EOD
<div class="shadowbar">
		<form method="post" action="/acp/mode/layout">
		<fieldset>
		<legend>Advanced Layout Editor</legend>
		<div class="input-group" style="width:100%;">
		<textarea id="codeEdit" rows=50 placeholder="Layout File" name="layout" style="width:100%;">$layoutFile</textarea><br />
		</div>
		</fieldset>
		<input class="Link LButton" type="submit" value="Submit Edits" name="submit" />
	</form>
	</div>
EOD
);
				} else {
					echo '<div class="shadowbar">Please enable developer mode.</div>';
				}
			}
			if($_GET['mode'] == 'modules'){
				if($this->settings['dev'] == '1'){
				if(isset($_POST['submit'])){
					$moduleSettings = $_POST['modules'];
					file_put_contents("modules.php", $moduleSettings);
					echo '<div class="shadowbar">Module settings updated</div>';
				}
				$moduleFile = file_get_contents("modules.php");
				echo '
<div class="shadowbar">
		<form method="post" action="/acp/mode/modules">
		<fieldset>
		<legend>Module Settings</legend>
		<div class="input-group" style="width:100%">
		<textarea style="width:100%" placeholder="Module Settings" name="modules" id="codeEdit" rows="50">'.$moduleFile.'</textarea><br />
		</div>
		</fieldset>
		<input class="Link LButton" type="submit" value="Submit Edits" name="submit" />
	</form>
</div>
				';
				} else {
					echo '<div class="shadowbar">Please enable developer mode.</div>';
				}
			}
			if(($_GET['mode'] === 'Settings')){
				if(isset($_POST['submit'])){
					$mySettingsFile = 'include/scripts/settings.php';
					$dev = $_POST['dev'];
					$home = $_POST['homepage'];
					$name = $_POST['name'];
					$burl = $_POST['url'];
					$bemail = $_POST['email'];
					$about = $_POST['about'];
					$signup = $_POST['signup'];
					$style = $_POST['style'];
					$db = $this->settings['db'];
					$pass = $this->settings['db_password'];
					$user = $this->settings['db_user'];
					$about = str_replace("'", "", $about);
					$about = str_replace('"', "", $about);
					$sidebarDisp = $_POST['sideDisp'];

					$newSettings = array (         // the default settings array
					'home_display'=>''.$home.'',
					'style'=>''.$style.'',
					'dev'=>''.$dev.'',
					'db_host'=>'localhost',
					'db_user'=>''.$user.'',
					'db_password'=>''.$pass.'',
					'db'=>''.$db.'',
					'login_enabled'=>true,
					'signup_enabled'=>''.$signup.'',
					'site_name'=>''.$name.'',
					'b_url'=>''.$burl.'',
					'b_email'=>''.$bemail.'',
					'board_enabled'=>false,
					'about' => "".$about."",
					'sidebarDisp' => "".$sidebarDisp.""
					);
					$end = '<?php $dbc=mysqli_connect($settings[\'db_host\'],$settings[\'db_user\'],$settings[\'db_password\'],$settings[\'db\']); ?>';
					file_put_contents($mySettingsFile, $this->array2php($newSettings, "settings"));
					file_put_contents($mySettingsFile, $end, FILE_APPEND | LOCK_EX );

					echo '<div class="shadowbar"><div class="alert alert-success">Settings Edited</div></div>';
				}

				echo'<div class="shadowbar"><div class="alert alert-info">Please refer to the documentation <a href="http://cheesecakecms.org/pages/cheesecake-cms-documentation-1">Here</a> for settings</div>
		<form method="post" action="/acp/mode/Settings">
		<fieldset>
		<legend>Settings</legend>
		<div class="input-group">
		<span class="input-group-addon">Home Page Display</span>
		<input class="form-control" type="text" name="homepage" value="'.$this->settings['home_display'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Website Style</span>
		<input class="form-control" type="text" name="style" value="'.$this->settings['style'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Developer Mode</span>
		<input class="form-control" type="text"  name="dev" value="'.$this->settings['dev'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">DB Host</span>
		<input class="form-control" type="text" name="dbhost" value="'.$this->settings['db_host'].'" disabled />
		</div>
		<div class="input-group">
		<span class="input-group-addon">DB User</span>
		<input class="form-control" type="text" name="dbuser" value="'.$this->settings['db_user'].'" disabled />
		</div>
		<div class="input-group">
		<span class="input-group-addon">DB Password</span>
		<input class="form-control" type="password" name="dbpass" value="passworddbdb" disabled />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Database</span>
		<input class="form-control" type="text"  name="db" value="'.$this->settings['db'].'" disabled />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Site Name</span>
		<input class="form-control" type="text" name="name" value="'.$this->settings['site_name'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Site URL</span>
		<input class="form-control" type="text" name="url" value="'.$this->settings['b_url'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Site Email</span>
		<input class="form-control" type="text" name="email" value="'.$this->settings['b_email'].'" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Sidebar Display (True/False no caps)</span>
		<input class="form-control" type="text" name="sideDisp" value="'.$this->settings['sidebarDisp'].'" />
		</div>
		<div class="input-group">
		<textarea rows="8" placeholder="about" name="about" id="about">'.$this->settings['about'].'</textarea><br />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Signup Settings</span>
		<select name="signup" >
		<option value="true">Enabled</option>
		<option value="false">Disabled</option>>
		</select>
		</div>


		</fieldset>
		<input class="Link LButton" type="submit" value="Submit Edits" name="submit" />
	</form>
	</div>';


			}
			if($_GET['mode'] == 'stats'){
				$this->stats();
			}
		}

		if(!isset($_GET['mode'])){
			echo '
<div class="shadowbar">
<table class="table">
<thead>
<th>Setting</th>
<th>Value</th>
</thead>
<tbody>
<tr>
<td>
Home Page Display
</td>
<td>
'.$this->settings['home_display'].'
</td>
</tr>
<tr>
<td>
Database Host
</td>
<td>
'.$this->settings['db_host'].'
</td>
</tr>
<tr>
<td>
Database
</td>
<td>
'.$this->settings['db'].'
</td>
</tr>
<tr>
<td>
CMS Variables
</td>
<td>';
foreach($this->vars as $var => $value){
	echo $var . ': ' . $value . '<br />';
}
echo '
</td>
</tr>

</tbody>
</table>


</div>

';
echo'
<div class="shadowbar">
<table class="table table-bordered">
<thead>
<th>Stats Files</th>
</thead>';
foreach (glob('include/*.dat') as $stats) {
	$stats = preg_replace("/(include\/)/", "", $stats);
	echo '<tr><td><a href="//'.$this->settings['b_url'].'/include/'.$stats.'">'.$stats.'</a></td></tr>';
}
echo '</table></div>
';
		}
	}
	public function stats() {
		$query = "SELECT * FROM users";
		$data = mysqli_query($this->dbc, $query);
		$ucount = mysqli_num_rows($data);
		$day = date("j");
		$month = date("M");
		$year = date("Y");
		$filename = $day . $month . $year . '.dat';
		$str = "Users: $ucount \r\n";
		file_put_contents("include/".$filename, $str);
		echo '<div class="shadowbar">Stats file can be found at '. $this->settings['b_url'] . '/include/' . $filename.'</div>';
		echo '<div class="shadowbar">Core stats finished...</div>';
		require("modules.php");
		foreach($modules as $name => $module) if ($module['enabled'] && $module['stats'] == 'true') {
			$module['class']::stats();
		}
	}
}


class core {
	public function __construct($settings, $version, $dbc, $layout, $parser, $modules, $cms_vars, $pageFuncs){
		$this->settings = $settings;
		$this->version = $version;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->parser = $parser;
		$this->modules = $modules;
		$this->vars = $cms_vars;
		$this->pageGen = $pageFuncs;

	}
	/*
	public function errHandlr(){
		$errstrArr = error_get_last();
		$errno = $errstrArr['type'];
		$errstr = $errstrArr['message'];
		$errfile = $errstrArr['file'];
		$errline = $errstrArr['line'];
		$query = "INSERT INTO `err` (`errno`, `errstr`, `errfile`, `errline`) VALUES ('$errno', '$errstr', '$errfile', '$errline')";
		mysqli_query($this->dbc, $query);
		//die("<b>There was an error. Check the database.</b>");
		print_r($errstrArr);
	}
	*/
	public function fatalErrHandlr(){
		$errstrArr = error_get_last();
		if($errstrArr['type'] > 0){
		$errno = mysqli_real_escape_string($this->dbc, trim($errstrArr['type']));
		$errstr = mysqli_real_escape_string($this->dbc, trim($errstrArr['message']));
		$errfile = mysqli_real_escape_string($this->dbc, trim($errstrArr['file']));
		$errline = mysqli_real_escape_string($this->dbc, trim($errstrArr['line']));
		$query = "INSERT INTO `err` (`errno`, `errstr`, `errfile`, `errline`) VALUES ('$errno', '$errstr', '$errfile', '$errline')";
		mysqli_query($this->dbc, $query);
		//var_dump(mysqli_error($this->dbc));
		echo("<b>There was an error. Check the database.</b>");
		//return true;
	}
	}
	public function FriendlyErrorType($type){
        switch($type){
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_CORE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_CORE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
            }
        return $type;
    }
		/*
	public function GenerationModifiers(&$settings, &$modules, &$cms_vars){      //<-- changed
		    $query = "SELECT `modifiers` FROM `settings`";
		    $data = mysqli_query($this->dbc, $query);
		    $row = mysqli_fetch_array($data);
		    $modifiers = $row['modifiers'];
		    $modifiers = explode(";", $modifiers);
		    foreach($modifiers as $modifier){
		        $mod = explode(".", $modifier);
		        $control = $mod[0];
		        $setting = $mod[1];
		        switch($control){
		            case "moduleOff":
		                $modules[$setting]['enabled'] = 0;
		                break;
								case "moduleOn":
										$modules[$setting]['enabled'] = 1;
										break;
		            case "settingsChange":
		                $s = explode(":", $setting);
		                $toChange = $s[0];
		                $changeTo = $s[1];
		                $settings[$toChange] = $changeTo;      //<-- changed
		                break;
								case "varSet":
										$s = explode(":", $setting);
										//print_r($cms_vars);
										$cms_vars[$s[0]] = $s[1];
										//print_r($cms_vars);
										break;
		        }
		    }
		}
		*/
	public function notifBar(){
		if(isset($_SESSION['uid'])){
			echo '</div><div class="col-3"><div class="shadowbar">';
			print_r($this->vars);
			if(isset($_GET['action']) && ($_GET['action'] == 'markasread')){
				$query = "UPDATE notifications SET `read` = '1' WHERE `user` = ".$_SESSION['uid']." ";
				$data = mysqli_query($this->dbc, $query);
				echo '<div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>Marked as read</div>';
				}
			if(isset($_GET['action']) && ($_GET['action'] == 'markasunread')){
				$query = "UPDATE notifications SET `read` = '0' WHERE `user` = ".$_SESSION['uid']." ";
				$data = mysqli_query($this->dbc, $query);
				echo '<div class="alert alert-success"><span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>Marked as unread</div>';
				}
			print($this->layout['sidebarNotif']);
				$query = "SELECT * FROM notifications WHERE `user` = '" .$_SESSION['uid']. "' AND `read` = 0";
				$data = mysqli_query($this->dbc, $query);
				if(mysqli_num_rows($data) > 0){
				echo '<a href="/markasread">Mark all as read</a><br />';
				echo '<ul class="list-group">';
				while($row = mysqli_fetch_array($data)){
					echo '
					<li class="list-group-item"><a href="'.$row['link'].'">'.$row['description'].'</a></li>
					';
				}
				echo '</ul>';
				} else {
				echo 'No new notifications.';
				}

			echo'</div><div role="tabpanel" class="tab-pane" id="read">';
				$query = "SELECT * FROM notifications WHERE `user` = '" .$_SESSION['uid']. "' AND `read`= 1";
				$data = mysqli_query($this->dbc, $query);
				if(mysqli_num_rows($data) > 0){
				echo '<a href="/markasunread">Mark all as unread</a>';
				echo '<ul class="list-group">';
				while($row = mysqli_fetch_array($data)){
					echo '
					<li class="list-group-item"><a href="'.$row['link'].'">'.$row['description'].'</a></li>
					';
				}
				echo '</ul>';
				} else {
				echo 'No new notifications.';
				}
			print($this->layout['notifEnd']);
		}
	}
	public function sidebar() {
			//print($this->layout['sidebarBegin']);
				// Generate the navigation menu
				if (isset($_SESSION['uid'])) {
				$query = "SELECT * FROM users WHERE `uid` = ".$_SESSION['uid']."";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$uid = $_SESSION['uid'];
				echo sprintf($this->layout['sidebar-core'], $row['username'], $row['username']);
					$this->pageGen->loadModule("sidebar", $this->modules);
					if($this->verify("core.*")){
						echo sprintf($this->layout['sidebarLink'], "/acp", "Admin Panel");
						}
					if($this->verify("core.*") || $this->verify("core.mod")){
						$this->pageGen->loadModule("acp", $this->modules);
					}
					//echo '</div>';
				}
}
	public function onlineList(){
		if(isset($_SESSION['uid'])){
			$time = time();
			$query = "UPDATE users SET `active` = '$time' WHERE `uid` = ".$_SESSION['uid']."";
			mysqli_query($this->dbc, $query);
			}
			print($this->layout['onlineUsersPanel']);
			$query = "SELECT * FROM users";
			$data = mysqli_query($this->dbc, $query);
			while ($row = mysqli_fetch_array($data)){
			if(time() - 300 < $row['active']){
			echo '<a href="/ucp/uid/'.$row['uid'].'">'.$row['username'].'</a>, ';
			}
			}
			print($this->layout['onlineUsersEnd']);
	}

	public function checkLogin(){
		if(!isset($_SESSION['uid']) && isset($_COOKIE['ID'])){
			$UID = $_COOKIE['ID'];
			$query = "SELECT * FROM `loggedin` WHERE `uid` = '$UID'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$time = $row['time'];
			if(time() - 86400 < $time){
			if($row['uid'] == $_COOKIE['ID'] && $row['hash'] == $_COOKIE['HASH'] && $row['ip'] == $_COOKIE['IP']){
			$query = "SELECT username, ip, hash FROM users WHERE uid = '" . $_COOKIE['ID'] . "'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			if($row['ip'] == $_COOKIE['IP']){
			if($row['hash'] == $_COOKIE['HASH']){
			$_SESSION['uid'] = $_COOKIE['ID'];
			$_SESSION['username'] = $row['username'];
			}
			}
		}
		}
		}
	}
	public function Version($local, $remote){
		$remoteVersion=trim(file_get_contents($remote));
		return version_compare($local, $remoteVersion, 'ge');
	}
	public function verify($permName){
		if(!empty($permName)){
			if(isset($_SESSION['uid'])){
				$query = "SELECT `group` FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$groupID = $row['group'];
				$query = "SELECT groupPerms FROM groups WHERE groupID = '$groupID'";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$perms = $row['groupPerms'];
				$perms = explode(";", $perms);
				if(in_array($permName, $perms)){
					return true;
				} else {
					return false;
				}
			}
		} else {
			return true;
		}
	}

	public function ifModule($moduleName){

		include("modules.php");
		if($modules[$moduleName]['enabled'] === '1'){
			return true;
		} else {
			return false;
		}
	}
	public function logout(){
		session_destroy();
		unset($_COOKIE['ID']);
		setcookie('ID', "", time()-86400, "/");
		setcookie('IP', "", time()-86400, "/");
		setcookie('HASH', "", time()-86400, "/");
		//setcookie('user_id', "", time()-10, "/");
		echo '
			<script>

			function Redirect()
		{
			window.location="index.php";
		}
			</script>
		<script>Redirect();</script>';
		}
	public function isLoggedIn(){
		echo '<div class="shadowbar">';
		if (!isset($_SESSION['uid'])) {
			echo '<p class="login">Please <a href="/login">log in</a> to access this page.</p>';
			exit();
		}
		else {
			echo('<p class="login">You are logged in as ' . $_SESSION['username'] . '. <a href="/logout">Log out</a>.</p>');
		}
		echo '</div>';
	}
	public function deactivateAndReset(){
	$user = $_SESSION['uid'];
	$query = "UPDATE users SET activated = '0', passwordReset = '1' WHERE uid = '$user' ";
	mysqli_query($this->dbc, $query);
	echo '<div class="shadowbar">Your password has ben set the the "reset" state and your account deactivated. Once you have re-activated your account you will have to reset your password. (Check your email and be sure to check spam)</div>';
	$query = "SELECT email, hash FROM users WHERE uid = '$user' ";
	$data = mysqli_query($this->dbc, $query);
	$row = mysqli_fetch_array($data);
	$burl = $this->settings['b_url'];
	$hash = $row['hash'];
	$email = $row['email'];
	$site = $this->settings['site_name'];
					$to      = $email; // Send email to our user
					$subject = $this->settings['site_name']; // Give the email a subject
					$message = '
			You have submitted a password reset request at '.$this->settings['site_name'].'
			Your account has been deactivated and you will have to reset your password upon re-activation.
			Please click this link to activate your account:
			http://'.$burl.'/verifyaccount/hash/'.$hash.'
			If you did not request this, please click here:
			http://'.$burl.'/verifyaccount/hash/'.$hash.'/fraud/true
			'; // Our message above including the link
					$headers = 'From:'.$site.'' . "\r\n"; // Set from headers
					mail($to, $subject, $message, $headers); // Send our email
	}
	public function addNotification($U, $L, $D){
	$user = $U;
	$link = $L;
	$description = $D;
	$query = "INSERT INTO notifications (`user`, `description`, `link`) VALUES ('$user', '$description', '$link')";
	mysqli_query($this->dbc, $query);
	}
	public function securityAgent($opt, $u = null){
		$O = $opt;
		if($O == 'check'){
			$IP = $_SERVER['REMOTE_ADDR'];
			$query = "SELECT * FROM `bans` WHERE `user` = '$IP' ";
			$data = mysqli_query($this->dbc, $query);
			$c = mysqli_num_rows($data);
			$row = mysqli_fetch_array($data);
			if($c > 0){
				echo '<div class="shadowbar">You have been banned from this website for reason: '.$row['reason'].'.</div>';
				die(sprintf($this->layout['footer'], $this->settings['b_url'], $this->settings['site_name'], $version['core']));
			}
		}
		if($O == 'checkacc'){
			$query = "SELECT * FROM `bans` WHERE `user` = '$u' ";
			$data = mysqli_query($this->dbc, $query);
			$c = mysqli_num_rows($data);
			$row = mysqli_fetch_array($data);
			if($c > 0){
				echo 'banned';
				exit();
			}
		}
		if($O == 'banip'){
			$reason = mysqli_real_escape_string($this->dbc, trim($_POST['res']));
			$user = mysqli_real_escape_string($this->dbc, trim($_POST['user']));
			$query = "SELECT ip FROM users WHERE uid = '$user' ";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$user = $row['ip'];
			$query = "INSERT INTO `bans` (`user`, `reason`) VALUES ('$user', '$reason')";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">User banned.</div>';
		}
		if($O == 'banacc'){
			$reason = mysqli_real_escape_string($this->dbc, trim($_POST['res']));
			$user = mysqli_real_escape_string($this->dbc, trim($_POST['user']));
			$query = "SELECT username FROM users WHERE uid = '$user' ";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$user = $row['username'];
			$query = "INSERT INTO `bans` (`user`, `reason`) VALUES ('$user', '$reason')";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">User banned.</div>';
		}
	}
	public function login() {
		if(!isset($_SESSION['uid'])){
			if(isset($_POST['submit'])){
				$username = mysqli_real_escape_string($this->dbc, trim($_POST['email']));
				$password = mysqli_real_escape_string($this->dbc, trim($_POST['password']));
				if(!empty($username) && !empty($password)){
					$query = "SELECT uid, email, username, password, hash FROM users WHERE email = '$username' AND password = SHA('$password') AND activated = '1'";
					$data = mysqli_query($this->dbc, $query);
					if((mysqli_num_rows($data) === 1)){
						$row = mysqli_fetch_array($data);
						$this->securityAgent("checkacc", $row['username']);
						$_SESSION['uid'] = $row['uid'];
						$_SESSION['username'] = $row['username'];
						//$_SERVER['REMOTE_ADDR'] = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"];
						$ip = $_SERVER['REMOTE_ADDR'];
						$user = $row['uid'];
						$query = "UPDATE users SET ip = '$ip' WHERE uid = '$user' ";
						mysqli_query($this->dbc, $query);
						setcookie("ID", $row['uid'], time()+3600*24);
						setcookie("IP", $ip, time()+3600*24);
						setcookie("HASH", $row['hash'], time()+3600*24);
						$hash = $row['hash'];
						$uid = $row['uid'];
						$time = time();
						$query = "INSERT INTO `loggedin` (`hash`, `ip`, `uid`, `time`) VALUES ('$hash', '$ip', '$uid', '$time')";
						mysqli_query($this->dbc, $query);
						$query = "UPDATE `users` SET `ip`='$ip' WHERE `uid` = '$user'";
						mysqli_query($this->dbc, $query);
						echo '
						<script type="text/javascript">
						function Redirect()
						{
							window.location="/index.php";
						}

						setTimeout("Redirect()", 1);
						</script>
						';
						exit();
					} else {
						//$error = '<div class="shadowbar">It seems we have run into a problem... Either your username or password are incorrect or you haven\'t activated your account yet.</div>' ;
						//return $error;
						echo '
						<script type="text/javascript">
						function Redirect()
						{
							window.location="/login";
						}

						document.write("You will be redirected to main page in 10 sec.");
						setTimeout("Redirect()", 10000);
						</script>
						';
					//echo($err);
					exit();
					}
				} else {
					//$error = '<div class="shadowbar">You must enter both your username AND password.</div>';
					//return $error;
						echo '
						<script type="text/javascript">
						function Redirect()
						{
							window.location="/login";
						}

						document.write("You will be redirected to main page in 10 sec.");
						setTimeout("Redirect()", 10000);
						</script>
						';
					echo json_encode($err);
					exit();
				}
			}
		} else {
			echo '{"result":"success"}';
			exit();
		}
		return $error;
	}
	public function addcomment(){
		if(isset($_POST['submit'])){
			$comment = mysqli_real_escape_string($this->dbc, trim($_POST['comment']));
			$user = mysqli_real_escape_string($this->dbc, trim($_POST['user']));
			$module = mysqli_real_escape_string($this->dbc, trim($_POST['module']));
			$id = mysqli_real_escape_string($this->dbc, trim($_POST['id']));
			$query = "INSERT INTO `comments` (`body`, `user`, `module`, `id`) VALUES ('$comment', '$user', '$module', '$id')";
			$data = mysqli_query($this->dbc, $query);
				echo 'success';
				exit();
			}
	}
	public function editprofile(){
		$this->isLoggedIn();
		echo '<div class="shadowbar">';
		if (isset($_POST['submit'])) {
			// Grab the profile data from the POST
			$filename = mysqli_real_escape_string($this->dbc, trim($_FILES["new_picture"]["name"]));
			$email = mysqli_real_escape_string($this->dbc, trim($_POST["email"]));
			$sig = mysqli_real_escape_string($this->dbc, trim($_POST["sig"]));
			if (!empty($_FILES["new_picture"]["name"])) {
				$query = "SELECT * FROM users WHERE uid = ".$_SESSION['uid']."";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$pnum = mysqli_num_rows($data);
				$pnumu = ($row['uid'] + 1);
				$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
				$temp = explode(".", $_FILES["new_picture"]["name"]);
				$extension = end($temp);
				$pnumname = 'ccp'.$pnumu.'.'.$extension;
				$user = $_SESSION['uid'];
				if ((($_FILES["new_picture"]["type"] == "image/gif")
							|| ($_FILES["new_picture"]["type"] == "image/jpeg")
							|| ($_FILES["new_picture"]["type"] == "image/jpg")
							|| ($_FILES["new_picture"]["type"] == "image/pjpeg")
							|| ($_FILES["new_picture"]["type"] == "image/x-png")
							|| ($_FILES["new_picture"]["type"] == "image/png"))
						&& ($_FILES["new_picture"]["size"] < 5000000)
						&& in_array($extension, $allowedExts)
						&& isset($_FILES['new_picture']['type'])) {
						$query = "UPDATE users SET `picture` = '$pnumname' WHERE uid = '".$_SESSION['uid']."'";
						mysqli_query($this->dbc, $query);
						move_uploaded_file($_FILES["new_picture"]["tmp_name"],
						"include/images/profile/" . $pnumname);
				} else {
					echo 'Error: Invalid File.';
				}
				if(!empty($email)){
					$query = "UPDATE users SET `email` = '$email', `sig` = '$sig' WHERE uid = '".$_SESSION['uid']."'";
					mysqli_query($this->dbc, $query);
					echo'Profile Updated';
					exit();
				}
			} else {
				if(!empty($email)){
					$query = "UPDATE users SET `email` = '$email', `sig` = '$sig' WHERE uid = '".$_SESSION['uid']."'";
					mysqli_query($this->dbc, $query);
					echo'Profile Updated';
					exit();
				}
			}
		} // End of check for form submission
		else {  // Grab the profile data from the database
			$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			if ($row != NULL) {
				if(!isset($row[''])){

				}
				$email = $row['email'];
				$old_picture = $row['picture'];
				$sig = $row['sig'];
			}
			else {
				echo '<p class="error">There was a problem accessing your profile.</p>';
			}
		}




		echo'<form enctype="multipart/form-data" method="post" action="/editprofile">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Personal Information</legend>
		<label for="new_picture">Picture:</label>';
		if (!empty($old_picture)) {
			echo '<img style="max-height:120px;" class="profile" src="/include/images/profile/' . $old_picture . '" alt="Profile Picture" /><br /><br />';
		}
		echo'<input type="file" id="new_picture" name="new_picture" />
	<label for="email">E-Mail:</label>
	<input type="text" id="email" name="email" value="'.$email.'"/><br>
	<label for="sig">Signature:</label><br>
	<textarea style="width:100%;" rows="6" id="editor" placeholder="Signature..." name="sig">'.$sig.'</textarea>
		</fieldset>
		<input type="submit" value="Save Profile" name="submit" /> <a class="button" href="/ucp">Cancel</a>
	</form>
	</div>';
	}
	public function ucp(){
		$this->isLoggedIn();
		if (!isset($_GET['uid'])) {
			$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		}
		else {
			$secureUser = preg_replace("/[^0-9]/", "", $_GET['uid']);
			$suser = mysqli_real_escape_string($this->dbc, $secureUser);
			$query = "SELECT * FROM users WHERE uid = '" .$suser. "'";
		}
		$data = mysqli_query($this->dbc, $query);

		if (mysqli_num_rows($data) == 1) {
			$row = mysqli_fetch_array($data);
			echo '<div class="shadowbar">';
			echo '<table class="table">
				  <th>User Info</th>';
			echo '<tr><td>Username:</td><td>' . $row['username'] . '</td></tr>';
			$uGID = $row['group'];
			$q = "SELECT * FROM `groups` WHERE `groupID` = '$uGID'";
			$d = mysqli_query($this->dbc, $q);
			$r = mysqli_fetch_array($d);
			echo '<tr><td>User Group:</td><td>'.$r['groupName'].'</td></tr>';
			echo '<tr><td>Email:</td><td>' . $row['email'] . '</td></tr>';
			echo '<tr><td>Rep:</td><td>' . $row['rep'] . '</td></tr>';
			echo '<tr><td>Picture:</td><td><img style="max-height:100px;" class="img-square" src="/include/images/profile/' . $row['picture'] .
			'" alt="Profile Picture" /></td></tr>';
			echo '<tr><td>Signature:</td><td>'.$row['sig'].'</td></tr>';
			echo '</table>';
			if (!isset($_GET['uid']) || ($_SESSION['uid'] == $_GET['uid'])) {
				echo '<p><a class="Link LButton" href="/editprofile">Edit</a><a class="Link LButton" href="/passwordReset">Reset Password</a></p>';
			}
		}
		else {
			echo '<p class="error">There was a problem accessing your profile.</p>';
		}
		$this->sidebar();
		echo'</div>';

	}
	public function activate() {
		if(isset($_POST['submit'])) {
		$password1 = mysqli_real_escape_string($this->dbc, trim($_POST['password1']));
		$password2 = mysqli_real_escape_string($this->dbc, trim($_POST['password2']));
		$hash = mysqli_real_escape_string($this->dbc, trim($_POST['hash']));
		if($password1 == $password2){
		$query = "UPDATE users SET `activated` = '1', `passwordReset` = '0', `password` = SHA('$password1') WHERE hash = '$hash' ";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar">User successfully activated! You can now login!</div>';
		}
		}
		if(isset($_GET['hash'])){
		$secureHash = $_GET['hash'];
		$hash = mysqli_real_escape_string($this->dbc, $secureHash);
		// Grab the profile data from the database
		$query = "SELECT passwordReset FROM users WHERE hash = '$hash' ";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		if($row['passwordReset'] == '0') {
		$query = "UPDATE users SET `activated` = '1' WHERE hash = '$hash' ";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar">User successfully activated! You can now login!</div>';
		exit();
		}
		if($row['passwordReset'] == '1') {
		echo '
		<div class="shadowbar">
				<form method="post" action="/verifyaccount">
				<fieldset>
				<legend>Reset Password</legend>
				<div class="input-group">
				<span class="input-group-addon">Password</span>
				<input class="form-control" type="password" id="password" name="password1" />
				</div>
				<div class="input-group">
				<span class="input-group-addon">Retype Password</span>
				<input class="form-control" type="password" id="password" name="password2" />
				<input class="form-control" type="hidden" id="hash" name="hash" value="'.$_GET['hash'].'" />
				</div>
				</fieldset>
				<input class="Link LButton" type="submit" value="Reset" name="submit" />
			</form>
		</div>
		';
		}
		}
		if(isset($_GET['fraud'])){
		if($_GET['fraud'] == 'true') {
		$query = "UPDATE users SET `activated` = '1', `passwordReset` = '0' WHERE hash = '$hash' ";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar">User successfully activated! You can now login!</div>';
		}
		}

	}
	public function viewConvo(){
		echo '<div class="shadowbar">';
		$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['username'];
		$id = $row['uid'];
		$query = "SELECT * FROM convo WHERE convo.sent_to = '$username' OR convo.sent_by = '$id'";
		$data = mysqli_query($this->dbc, $query);
		echo '<table class="table">';
		echo '<thead>';
		echo '<th>Message Title</th>';
		echo '</thead>';
		echo '<tbody>';
		while ($row = mysqli_fetch_array($data)) {
			$query2 = "SELECT * FROM messages WHERE convo = ".$row['id']."";
			$count = mysqli_query($this->dbc, $query2);
			$rc = mysqli_fetch_array($count);
			if(!empty($row['title'])) {
				echo'<tr>';
				echo'<td>';
					echo'<a class="nav" href="/viewmessage/m/'.$row['id'].'">' .$row['title']. '   <span class="badge">' . mysqli_num_rows($count) . ' Messages</span></a>';
				echo'</td>';
			}

		}
		echo '</tbody></table></div>';
	}
	public function viewMessage(){
		echo '<div class="shadowbar">';
		$secureMessage = preg_replace("/[^0-9]/", "", $_GET['m']);
		$message = mysqli_real_escape_string($this->dbc, $secureMessage);
		$query = "SELECT convo.*, messages.*, users.* FROM messages JOIN users ON users.uid = messages.user JOIN convo ON convo.id = messages.convo AND messages.convo = $message";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$replyTitle = $row['title'];
				echo '<a class="Link LButton" href="/replymessage/m/'.$message.'">Reply</a>';
			$parsed = $this->parser->parse($row['content']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
		}
		echo '</div>';
	}
	public function sendMessage(){
		$this->isLoggedIn();
		echo '<div class="shadowbar">';
		//Grab the profile data from the database
		$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['uid'];
		if (isset($_POST['submit'])) {
			// Grab the profile data from the POST
			$message = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['message'])));
			$title = mysqli_real_escape_string($this->dbc, trim($_POST['title']));
			$sent_to = mysqli_real_escape_string($this->dbc, trim($_POST['sent_to']));
			// Update the post data in the database
			if (!empty($message) && !empty($title) && !empty($sent_to)) {
				$query = "INSERT INTO convo (`sent_by`, `sent_to`, `title`) VALUES ('$username', '$sent_to', '$title')";
				mysqli_query($this->dbc, $query);
				$query = "SELECT * FROM convo WHERE sent_by = '$username' AND title = '$title' AND sent_to = '$sent_to' ORDER BY id DESC";
				$cquery = mysqli_query($this->dbc, $query);
				$convo = mysqli_fetch_array($cquery);
				$convo_id = $convo['id'];
				$query = "INSERT INTO messages (`user`, `convo`, `content`, `date`) VALUES ('$username', '$convo_id', '$message', NOW())";
				mysqli_query($this->dbc, $query);
				echo '<p>Your message has been successfully sent. Would you like to <a href="index.php?action=messages">view all of your messages</a>?</p>';
				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		} // End of check for form submission
		echo'<form enctype="multipart/form-data" method="post" action="/sendmessage">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Send Message:</legend>
			<label type="hidden" for="title">Title:</label><br />
			<input type="text" name="title"><br />
			<label type="hidden" for="title">To:</label><br />
			<input type="text" name="sent_to"><br />
			<label type="hidden" for="message">Post Content:</label><br />
		<textarea rows="4"  name="message" id="message" cols="50"></textarea><br />
		</fieldset>
		<input type="submit" value="Send" name="submit" />
	</form>
	</div>';
	}
	public function invalidAction(){
	die('<div class="shadowbar">Invalid Argument.</div>');
	}
	public function sendMessageReply(){
		$this->isLoggedIn();
		// Grab the profile data from the database
		$query = "SELECT uid FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['uid'];
		if (isset($_POST['submit'])) {
			// Grab the profile data from the POST
			$reply = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['reply'])));
			$secureReply = preg_replace("/[^0-9]/", "", $_POST['replyid']);
			$replyid = mysqli_real_escape_string($this->dbc, $secureReply);
			// Update the post data in the database
			if (!empty($reply)) {
				// Only set the picture column if there is a new picture
				$query = "INSERT INTO messages (`user`, `convo`, `content`, `date`) VALUES ('$username', '$replyid', '$reply', NOW())";
				mysqli_query($this->dbc, $query) or die(mysqli_error($this->dbc));
				// Confirm success with the user
				echo '<div class="shadowbar"><p>You have replied successfully. Would you like to <a href="index.php?action=messages">view all of your messages</a>?</p></div>';
				exit();
			}
			else {
				echo '<div class="shadowbar"><p class="error">You must enter information into all of the fields.</p></div>';
			}
		} // End of check for form submission
		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/replymessage">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Reply:</legend>
		<input type="hidden" name="replyid" value="'.$_GET['m'].'">
		<textarea rows="4"  name="reply" id="reply" cols="50"></textarea><br />
		</fieldset>
		<input type="submit" value="Send" name="submit" />
	</form>
	</div>';
	}
	public function signup(){
		if(($this->settings['signup_enabled'] === 'false')){
			die('<div class="alert alert-warning"><strong>Registration Disabled.</strong></div>');
		}
		if(!isset($_SESSION['uid'])){
		if (isset($_POST['submit'])) {
			$username = mysqli_real_escape_string($this->dbc, trim($_POST['username']));
			$password1 = mysqli_real_escape_string($this->dbc, trim($_POST['password1']));
			$password2 = mysqli_real_escape_string($this->dbc, trim($_POST['password2']));
			$email = mysqli_real_escape_string($this->dbc, trim($_POST['email']));
			if (!empty($username) && !empty($password1) && !empty($password2) && !empty($email) && ($password1 == $password2)) {
				$hash = md5(rand(0,1000));
				$bemail = $this->settings['b_email'];
				$burl = $this->settings['b_url'];
				$query = "SELECT * FROM users WHERE email = '$email' AND `username` = '$username'";
				$data = mysqli_query($this->dbc, $query);
				if (mysqli_num_rows($data) == 0) {
					$to      = $email; // Send email to our user
					$subject = $this->settings['site_name']; // Give the email a subject
					$message = sprintf($this->layout['signupEmail'], $this->settings['site_name'], $username, $password1, $burl, $hash);
					$headers = 'From:'.$bemail.'' . "\r\n"; // Set from headers
					mail($to, $subject, $message, $headers); // Send our email
					$uip = $_SERVER['REMOTE_ADDR'];
					$query = "INSERT INTO users (username, password, email, hash, ip, picture) VALUES ('$username', SHA('$password1'), '$email', '$hash', '$uip', 'nopic.png')";
					mysqli_query($this->dbc, $query);
					echo '<div class="shadowbar">Your new account has been successfully created. You now need to verify your account. You signed up with this email: ' .$email . '. Please check your spam folder as there\'s a chance that the email could have ended up in there.</div>';

					exit();
				}
				else {
					echo '<div class="shadowbar"><div class="alert alert-warning"><strong>An account already exists for this username. Try another.</strong></div></div>';
					$username = "";
				}
			}
			else {
				echo '<div class="shadowbar"><div class="alert alert-warning"><strong>You must enter all data, including your desired password twice.</strong></div></div>';
			}

		}
		} else {
			echo "<div class='shadowbar'>You're Logged In!</div>";
			exit();
		}

		echo'<div class="shadowbar"><div class="alert alert-info"><strong>Please enter your username and desired password to sign up.</strong></div>
		<form method="post" action="/signup">
		<fieldset>
		<legend>Registration Info</legend>
		<div class="input-group">
		<span class="input-group-addon">Username</span>
		<input class="form-control" type="text" id="username" name="username"  />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Email</span>
		<input class="form-control" type="email" id="email" name="email" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Password</span>
		<input class="form-control" type="password" id="password" name="password1" />
		</div>
		<div class="input-group">
		<span class="input-group-addon">Retype Password</span>
		<input class="form-control" type="password" id="password" name="password2" />
		</div>
		<input type="hidden" id="perm" name="perm" value="U"/>
		<input type="hidden" id="usergroup" name="usergroup" value="User"/>
		</fieldset>
		<input class="Link LButton" type="submit" value="Sign Up" name="submit" />
	</form>
	</div>';
	}
	public function counter(){
		if(!isset($_SESSION['uid'])){
			$u = $_SERVER['REMOTE_ADDR'];
			$q = "INSERT INTO `views` (`count`) VALUES ('$u')";
			mysqli_query($this->dbc, $q);
		} else {
			return false;
		}
	}
	public function repUp($u, $m){
		$rU = ($u+(5*$m));
		return $rU;
	}
	public function repDown($u, $m){
		$rU = ($u-(5*$m));
		return $rU;
	}


}

?>
