<?php
//Cheesecake Core Blog
$blog = new blog($this->settings, $this->version, $this->dbc, $this->layout, $this, $this->parser);
if(isset($_GET['action']) && ($_GET['action'] == "Blog")){
if(isset($_GET['action']) && !isset($_GET['mode'])){
if($_GET['action'] === "Blog"){
	if(!isset($this->vars['blogInstalled'])){
		$blog->blogInstall();
	} else {
		$blog->searchBar();
		$blog->viewBlog();
	}
}
$blog->blogAdminBar();
}
if(isset($_GET['mode'])){
if($_GET['mode'] == "postblog"){
$blog->postBlog();
}
if($_GET['mode'] == "admin"){
$blog->blogPostAdmin();
}
if($_GET['mode'] == "delete"){
$blog->blogDeletePost();
}
if($_GET['mode'] == "hide"){
$blog->blogHideAdmin();
}
if($_GET['mode'] == "archive"){
$blog->addArch();
}
if($_GET['mode'] == "unhide"){
$blog->blogUnHideAdmin();
}
$blog->blogAdminBar();
}
}

class blog {
	public function __construct($settings, $version, $dbc, $layout, $core, $parser){
		$this->settings = $settings;
		$this->version = $version;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;

	}
	public function blogInstall(){
		if(!isset($this->vars['blogInstalled'])){
			//print_r($this->vars);
			$sqlfile = 'include/scripts/blog/sql/initialize.sql';
			$sql = file_get_contents($sqlfile);
			mysqli_multi_query($this->dbc, $sql);
			$query = "SELECT `modifiers` FROM `settings`";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$mods = $row['modifiers'];
			$mods = $mods.';varSet.blogInstalled:true';
			$query = "UPDATE `settings` SET `modifiers` = '$mods'";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">Blog installed, <a href="/Blog">Refresh</a></div>';
		}
	}
	public function homepage(){
		$query = "SELECT blog.*, users.* FROM blog INNER JOIN users ON users.uid = blog.user ORDER BY blog.id DESC LIMIT 3";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar"><h3>Latest Blog Posts</h3><hr>';
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['content']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
		}
		echo '</div>';
	}
	public function searchBar(){
		if(isset($_POST['submit'])){
			$search = mysqli_real_escape_string($this->dbc, trim($_POST['search']));
			$query = "SELECT blog.*, users.* FROM blog INNER JOIN users ON users.uid = blog.user AND `title` LIKE '%$search%' OR `content` LIKE '%$search%' ";
			$data = mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar"><h3>Search results</h3>';
			while ($row = mysqli_fetch_array($data)){
			$parsed = $this->parser->parse($row['content']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
			}
			echo '</div>';

		}
	echo '
		<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/Blog">
				<fieldset>
				<legend>Search:</legend>
				<input type="text" name="search" /><br />
				</fieldset>
				<input class="Link LButton" type="submit" value="Search Blog" name="submit" />
			</form>
		</div>
	';

	}
	static function stats(){
		$query = "SELECT * FROM blog";
		$data = mysqli_query($this->dbc, $query);
		$bcount = mysqli_num_rows($data);
		$day = date("j");
		$month = date("M");
		$year = date("Y");
		$filename = $day . $month . $year . '.dat';
		$str = "Blogs: \r\n Blog Posts: $bcount \r\n";
		file_put_contents("include/".$filename, $str, FILE_APPEND);
		echo '<div class="shadowbar">Blog stats finished...</div>';
	}
	public function blogAdminBar(){
	if($this->core->verify("blog.*")){
	echo sprintf($this->layout['adminBar'], '/Blog/mode/admin', 'Blog');
	}
	}

	public function viewBlog(){
	echo '<div class="shadowbar"><h3>Archives</h3>';
	$query = "SELECT * FROM `archive` LIMIT 5";
	$data = mysqli_query($this->dbc, $query);
	while($row = mysqli_fetch_array($data)){
		echo '<a class="Link LButton" href="/Blog/archive/'.$row['arch'].'">'.$row['arch'].'</a>';
	}
	echo '<br /><a class="Link LButton" href="/Blog/mode/archive">Check archive and add as needed</a>';
	echo '</div>';
	if(isset($_GET['archive'])){
		$arch = mysqli_real_escape_string($this->dbc, trim($_GET['archive']));
		$query = "SELECT blog.*, users.* FROM blog JOIN users ON users.uid = blog.user AND blog.arch = '$arch' ORDER BY blog.id DESC";
		$data = mysqli_query($this->dbc, $query);
		$ct = mysqli_num_rows($data);
		if($ct == 0){
			echo '<div class="shadowbar">No blog posts to display.</div>';
		}
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['content']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
		}
	} else {
		$query = "SELECT blog.*, users.* FROM blog JOIN users ON users.uid = blog.user AND hidden = '0' ORDER BY blog.id DESC";
		$data = mysqli_query($this->dbc, $query);
		$ct = mysqli_num_rows($data);
		if($ct == 0){
			echo '<div class="shadowbar">No blog posts to display.</div>';
		}
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['content']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
		}
	}
	}

	public function postBlog(){
		echo'<div class="shadowbar">';
		if($this->core->verify("blog.*") || $this->core->verify("blog.write")){

			$mon = date("M");
			$Yr = date("Y");
			$date = $mon.'-'.$Yr;
			$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$username = $row['uid'];
			if (isset($_POST['submit'])) {
				$post1 = mysqli_real_escape_string($this->dbc, trim($_POST['post1']));
				$display = mysqli_real_escape_string($this->dbc, trim($_POST['display']));
				$title = mysqli_real_escape_string($this->dbc, trim($_POST['title']));

				if (!empty($post1) && !empty($title)) {

					$query = "INSERT INTO blog (`title`, `content`, `display`, `user`, `date`, `arch`) VALUES ('$title', '$post1', '$display', '$username', NOW(), '$date')";
					mysqli_query($this->dbc, $query);

					echo '<p>Your post has been successfully added. Would you like to <a href="/Blog">view all of the blog posts</a>?</p>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}
			print($this->layout['blogPostFormat']);
			echo'</div>';
		}
	}
	public function addArch(){
		$mon = date("M");
		$Yr = date("Y");
		$date = $mon.'-'.$Yr;
		$query = "SELECT * FROM `archive` WHERE `arch` = '$date'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$c = count($row);
		if($c <= 0){
			$query = "INSERT INTO `archive` (`arch`) VALUES ('$date')";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">Archiving complete</div>';
		} else {
		echo '<div class="shadowbar">Nothing to archive!</div>';
		}
	}
	public function blogPostAdmin(){
		$this->core->isLoggedIn();
		if($this->core->verify("blog.*")){
		echo '<div class="shadowbar">';
		$query = "SELECT blog.*, users.* FROM blog JOIN users ON users.uid = blog.user ORDER BY blog.id DESC ";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['content']);
			echo sprintf($this->layout['adminBlogPostLayout'], $parsed, $row['id'], 'Blog', 'delete', $row['id'], $row['display'], 'Blog', $row['id'], 'Blog', $row['id'], $row['username'], $row['adminlevel']);
		}
		echo '</div>';
		}
	}
	public function blogHideAdmin(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("blog.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "UPDATE blog SET `display`='0' WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a href="/Blog/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo sprintf($this->layout['adminDeleteLayout'], 'hide', $_GET['del']);
	}
	public function blogUnHideAdmin(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("blog.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "UPDATE blog SET `display`='1' WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully unhidden. Would you like to <a href="/Blog/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo sprintf($this->layout['adminDeleteLayout'], 'unhide', $_GET['del']);
	}
	public function blogDeletePost(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("blog.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "DELETE FROM blog WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully deleted. Would you like to <a href="/Blog/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}

		$id = mysqli_real_escape_string($this->dbc, trim($_GET['del']));
		echo sprintf($this->layout['adminDeleteLayout'], 'delete', $id);
	}
}
?>
