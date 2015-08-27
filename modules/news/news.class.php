<?php
//News
//Author: Gage LeBlanc
//Dist: Cheesecake CMS

$newsFunc = new news($this->settings, $this->dbc, $this->layout, $this->core, $this->parser, $this->vars);

if(isset($_GET['action'])){
	if(!isset($_GET['mode'])){
		if($_GET['action'] == 'news' && !isset($_GET['post'])){
			if(!isset($this->vars['newsInstalled'])){
				$newsFunc->newsInstall();
			} else {
				$newsFunc->newsView("newsPage");
				$newsFunc->newsAdminBar();
			}
		}
		if($_GET['action'] == 'postNews'){
			$newsFunc->postNews();
			$newsFunc->newsAdminBar();
		}
		if($_GET['action'] == 'newsFB'){
			$newsFunc->ogstat();
			$newsFunc->newsAdminBar();
		}
		if($_GET['action'] == 'news' && isset($_GET['post'])){
			$newsFunc->newsView("perma");
			$newsFunc->newsAdminBar();
		}
	}
	if(isset($_GET['mode'])){
		if($_GET['mode'] == 'admin'){
			$newsFunc->newsPostAdmin();
			$newsFunc->newsAdminBar();
		}
		if($_GET['mode'] == "delete"){
			$newsFunc->newsDeletePost();
			$newsFunc->newsAdminBar();
		}
		if($_GET['mode'] == "hide"){
			$newsFunc->newsHideAdmin();
			$newsFunc->newsAdminBar();
		}
		if($_GET['mode'] == "unhide"){
			$newsFunc->newsUnHideAdmin();
			$newsFunc->newsAdminBar();
		}
	}
}


class news {
	public function __construct($settings, $dbc, $layout, $core, $parser, $cms_vars){
		$this->settings = $settings;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;
		$this->vars = $cms_vars;

	}
	public function newsAdminBar(){
		echo sprintf($this->layout['adminBar'], '/news/mode/admin', 'News');
	}
	public function newsInstall(){
		if(!isset($this->vars['newsInstalled'])){
			//print_r($this->vars);
			$sqlfile = 'include/scripts/news/sql/initialize.sql';
			$sql = file_get_contents($sqlfile);
			mysqli_multi_query($this->dbc, $sql);
			$query = "SELECT `modifiers` FROM `settings`";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$mods = $row['modifiers'];
			$mods = $mods.';varSet.newsInstalled:true';
			$query = "UPDATE `settings` SET `modifiers` = '$mods'";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">News installed, <a href="/news">Refresh</a></div>';
		}
	}
	public function newsView($display){
		if($display == "homePage"){
			$query = "SELECT news.*, users.* FROM news JOIN users ON users.uid = news.user AND `hidden` = 0 ORDER BY news.id DESC LIMIT 5";
			$data = mysqli_query($this->dbc, $query);
			$ct = mysqli_num_rows($data);
			if($ct == 0){
				echo '<div class="shadowbar">No news posts to display</div>';
			}
			while($row = mysqli_fetch_array($data)){
				$parsed = $this->parser->parse($row['body']);
				$sig = $this->parser->parse($row['sig']);
				$permalink = "/news/post/".$row['id'];
				echo '<div class="shadowbar"><a class="Link LButton" href="'.$permalink.'">Permalink</a></div>';
				echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
			}
		} else {
			if($display == "newsPage"){
				echo '
				<script type="text/javascript">
					$(document).ready(function() {
						document.title = "'.$this->settings['site_name'].' - News";
					});
				</script>
				';
				if(!isset($_GET['p'])){
					$p = 0;
				} else {
					$secureP = preg_replace("/[^0-9]/", "", $_GET['p']);
					$p = mysqli_real_escape_string($this->dbc, $secureP);
				}
				$query = "SELECT news.*, users.* FROM news JOIN users ON users.uid = news.user AND `hidden` = 0 ORDER BY news.id DESC LIMIT $p,5";
				$data = mysqli_query($this->dbc, $query);
				$ct = mysqli_num_rows($data);
				if($ct == 0){
					echo '<div class="shadowbar">No news posts to display</div>';
				}
				while($row = mysqli_fetch_array($data)){
					$parsed = $this->parser->parse($row['body']);
					$sig = $this->parser->parse($row['sig']);
					$permalink = "/news/post/".$row['id'];
					echo '<div class="shadowbar"><a class="Link LButton" href="'.$permalink.'">Permalink</a></div>';
					echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
				}
				if($ct > 0){
					echo '<div class="shadowbar"><a class="Link LButton" href="/news/p/'.($p - 5).'">Previous</a><a class="Link LButton" href="/news/p/'.($p + 5).'">Next</a></div>';
				}
			} else {
				if($display == "perma"){
					$secureP = preg_replace("/[^0-9]/", "", $_GET['post']);
					$p = mysqli_real_escape_string($this->dbc, $secureP);
					$query = "SELECT news.*, users.* FROM news JOIN users ON users.uid = news.user AND `hidden` = 0 WHERE id = '$p'";
					$data = mysqli_query($this->dbc, $query);
					$ct = mysqli_num_rows($data);
					if($ct == 0){
						echo '<div class="shadowbar">No news posts to display</div>';
					}
					$row = mysqli_fetch_array($data);
					$parsed = $this->parser->parse($row['body']);
					$sig = $this->parser->parse($row['sig']);
					$permalink = "/news/post/".$row['id'];
					echo '<div class="shadowbar"><a class="Link LButton" href="/newsFB/p/'.$row['id'].'">FB Post Generator</a><a class="Link LButton" href="'.$permalink.'">Permalink</a></div>';
					echo '
					<script type="text/javascript">
						$(document).ready(function() {
							document.title = "'.$this->settings['site_name'].' - '.$row['title'].'";
						});
					</script>
					';
					/*
					echo '<meta itemprop="fb" property="og:title" content="'.$row['title'].'" />';
					echo '<meta itemprop="fb" property="og:url" content="http://'.$settings['b_url'].$permalink.'" />';
					echo '<meta itemprop="fb" property="og:description" content="'.$parsed.'" />';
					echo '<meta itemprop="fb" property="og:image" content="none" />';
					*/
					echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
				}
			}
		}
	}
	public function postNews(){
		echo'<div class="shadowbar">';
		if($this->core->verify("news.*") || $this->core->verify("news.write")){
			$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$username = $row['uid'];
			if (isset($_POST['submit'])) {
				$post = mysqli_real_escape_string($this->dbc, trim($_POST['blogPost']));
				$display = mysqli_real_escape_string($this->dbc, trim($_POST['display']));
				$title = mysqli_real_escape_string($this->dbc, trim($_POST['title']));

				if (!empty($post) && !empty($title)) {

					$query = "INSERT INTO news (`title`, `body`, `display`, `user`, `date`) VALUES ('$title', '$post', '$display', '$username', NOW() )";
					mysqli_query($this->dbc, $query);

					echo '<div class="shadowbar">Your post has been successfully added. Would you like to <a href="/news">view all of the news posts</a>?</div>';

					exit();
				}
				else {
					echo '<div class="shadowbar">You must enter information into all of the fields.</p>';
				}
			}
			print($this->layout['newsPostFormat']);
			echo'</div>';
		}
	}
	public function newsPostAdmin(){
		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		if(!$core->verify("news.*")){
			exit();
		}

		$query = "SELECT news.*, users.* FROM news JOIN users ON users.uid = news.user ORDER BY news.id DESC ";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['body']);
			echo sprintf($this->layout['adminBlogPostLayout'], $parsed, $row['id'], 'news', 'delete', $row['id'], $row['hidden'], 'newa', $row['id'], 'news', $row['id'], $row['username'], $row['adminlevel']);
		}
		echo '</div>';
	}
	public function newsHideAdmin(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("news.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "UPDATE news SET `hidden`='1' WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a href="/news/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo sprintf($this->layout['adminNewsDeleteLayout'], 'hide', $_GET['del']);
	}
	public function newsUnHideAdmin(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("news.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "UPDATE news SET `hidden`='0' WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully unhidden. Would you like to <a href="/news/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo sprintf($this->layout['adminNewsDeleteLayout'], 'unhide', $_GET['del']);
	}
	public function newsDeletePost(){
		$this->core->isLoggedIn();
		if(!$this->core->verify("news.*")){
			exit();
		}
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "DELETE FROM news WHERE id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully deleted. Would you like to <a href="/news/mode/admin">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}

		$id = mysqli_real_escape_string($this->dbc, trim($_GET['del']));
		echo sprintf($this->layout['adminNewsDeleteLayout'], 'delete', $id);
	}
	public function ogstat(){
		$postid = mysqli_real_escape_string($this->dbc, $_GET['p']);
		if(!file_exists("ogPost/")){
			mkdir("ogPost/");
		}
		$query = "SELECT * FROM news WHERE id = '$postid'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$t = $row['title'];
		$p = $this->parser->parse($row['body']);
		$l = 'http://'.$this->settings['b_url'].'/news/post/'.$row['id'];
		$s = $this->settings['site_name'];
		$file = "ogPost/news".$postid.'.html';
		$infLink = 'http://'.$this->settings['b_url'].'/ogPost/news'.$postid.'.html';
		$p = strip_tags($p);
		if(file_exists($file)){
			echo '<div class="shadowbar">Facebook Link: http://'.$this->settings['b_url'].'/ogPost/news'.$postid.'.html</div>';
		} else {
			$OGFile =
(
<<<EOD
<html>
<head>
<title>$postid</title>
<meta property="og:url" content="$infLink"/>
<meta property="og:title" content="$t"/>
<meta property="og:site_name" content="$s"/>
<meta property="og:description" content="$p"/>
<script>
window.location = "$l";
</script>
</head>
</html>
EOD
);
			$path = 'ogPost/news'.$postid.'.html';
			file_put_contents($path, $OGFile);
			echo '<div class="shadowbar">Facebook Link: http://'.$this->settings['b_url'].'/ogPost/news'.$postid.'.html</div>';
		}
	}
}
?>
