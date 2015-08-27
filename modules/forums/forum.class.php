<?php
$forum = new Forums($this->settings, $this->dbc, $this->layout, $this->core, $this->parser, $this->vars);
if(isset($_GET['action'])){
	if (($_GET['action'] == 'forums')){
		if(!isset($this->vars['forumInstalled'])){
			//print_r($this->vars);
			$forum->forumInstall();
		} else {
			$forum->searchBar();
			$forum->category();
			$forum->forumAdminBar();
		}
	}
	if (($_GET['action'] == 'forumsFB')){
		$forum->ogstat();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'newforum'){
		$forum->newforum();
	}
	if($_GET['action'] == 'newcat'){
		$forum->newcat();
	}
	if (($_GET['action'] == 'f')){
		$forum->searchBar();
		$forum->viewforum();
		$forum->forumAdminBar();
	}
	if(($_GET['action'] == 'post')){
		$forum->searchBar();
		$forum->vpost();
	}
	if(($_GET['action'] == 'editpost')){
		$forum->editPost();
	}
	if(($_GET['action'] == 'posttopic')){
		$forum->upost();
		$forum->forumAdminBar();
	}
	if(($_GET['action'] == 'postreply')){
		$forum->postreply();
		$forum->forumAdminBar();
	}
	if(($_GET['action'] == 'reportpost')){
		$forum->rep();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'moderation'){
		$forum->forumModeration();
	}
	if($_GET['action'] == 'deletecat'){
		$forum->deletecat();
	}
	if($_GET['action'] == 'deleteforum'){
		$forum->deleteforum();
	}
	if($_GET['action'] == 'forumDeletePost'){
		$forum->delp();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'ForumHidePost'){
		$forum->hidep();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'ForumUnHidePost'){
		$forum->unhidep();
		$forum->forumAdminBar();
	} /*
	if($_GET['action'] == 'adminreply'){
		$forum->forumReplyAdmin();
	} */
	if($_GET['action'] == 'ForumHideReply'){
		$forum->hider();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'ForumUnHideReply'){
		$forum->unhider();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'forumDeleteReply'){
		$forum->delr();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'update'){
		$forum->update();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'postPoll'){
		$forum->postPoll();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'viewPoll'){
		$forum->vpoll();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'pollvote'){
		$forum->pollvote();
		$forum->forumAdminBar();
	}
	if($_GET['action'] == 'markAs'){
		$forum->markPost();
	}
	if($_GET['action'] == 'searchforums'){
		$forum->searchBar();
	}
} else {
	$forum->homepage();
}
class Forums{
	public function __construct($settings, $dbc, $layout, $core, $parser, $cms_vars){
		$this->settings = $settings;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;
		$this->vars = $cms_vars;

	}
	public function forumInstall(){
		if(!isset($this->vars['forumInstalled'])){
			//print_r($this->vars);
			$sqlfile = 'include/scripts/forums/sql/initialize.sql';
			$sql = file_get_contents($sqlfile);
			mysqli_multi_query($this->dbc, $sql);
			do {
			    mysqli_use_result($this->dbc);
			}while( mysqli_more_results($this->dbc) && mysqli_next_result($this->dbc) );
			$query = "SELECT `modifiers` FROM `settings`";
			$data = mysqli_query($this->dbc, $query) or die(mysqli_error($this->dbc));
			$row = mysqli_fetch_array($data) or die(mysqli_error($this->dbc));
			$mods = $row['modifiers'];
			$mods = $mods.';varSet.forumInstalled:true';
			$query = "UPDATE `settings` SET `modifiers` = '$mods'";
			mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">Forums installed, <a href="/forums">Refresh</a></div>';
		}
	}
	public function forumAdminBar(){
		if($this->core->verify("core.*") || $this->core->verify("forum.*") || $this->core->verify("forum.mod")){
		echo sprintf($this->layout['adminBar'], '/moderation', 'Forum');
	}
	}
	static function stats(){
		$query = "SELECT * FROM posts";
		$data = mysqli_query($this->dbc, $query);
		$pcount = mysqli_num_rows($data);
		$query = "SELECT * FROM reply";
		$data = mysqli_query($this->dbc, $query);
		$rcount = mysqli_num_rows($data);
		$day = date("j");
		$month = date("M");
		$year = date("Y");
		$filename = $day . $month . $year . '.dat';
		$str = "Forums: \r\n Posts: $pcount \r\n Replies: $rcount \r\n";
		file_put_contents("include/".$filename, $str, FILE_APPEND);
		echo '<div class="shadowbar">Forum stats finished...</div>';
	}
	public function searchBar(){
		if(isset($_POST['submit'])){
			$search = mysqli_real_escape_string($this->dbc, trim($_POST['search']));
			$tag = '['.$search.']';
			$query = "SELECT `title`, `postlink` FROM `posts` WHERE `tag` = '$tag' ";
			$data = mysqli_query($this->dbc, $query);
			echo '
			<div class="shadowbar">
			<table class="table">
			<th>Posts with this tag: '.$tag.'</th>
			';
			while ($row = mysqli_fetch_array($data)){
			echo '
			<tr><td><a href="/viewpost/'.$row['postlink'].'">'.$row['title'].'</a></td></tr>
			';
			}
			echo '
			</table>
			</div>
			';
		}
	echo '
		<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/searchforums">
				<fieldset>
				<legend>Search:</legend>
				<input type="text" name="search" /><br />
				</fieldset>
				<input class="Link LButton" type="submit" value="Search Forum Topics" name="submit" />
			</form>
		</div>
	';

	}
	public function newforum() {

		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		if (isset($_POST['submit'])) {
			$catt = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['catt'])));	$desc = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['desc'])));
			$cg = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['cg'])));
			if (!empty($catt)) {
				$query = "INSERT INTO categories (`name`, `desc`, `cg`) VALUES ('$catt', '$desc', '$cg')";
				mysqli_query($this->dbc, $query);
				echo '<p>Your forum has been successfully added. Would you like to go back to the <a href="/moderation">Admin Panel</a>?</p>';
				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
		echo'<form enctype="multipart/form-data" method="post" action="/newforum">
		<fieldset>
		<legend>Create Forum:</legend>
			<label type="hidden" for="catt">Forum name:</label><br />
			<input type="text" name="catt"><br /><br />
			<label type="hidden" for="desc">Description</label><br />
			<textarea rows="4"  name="desc" id="desc" style="width:100%"></textarea><br>
			<select name="cg">';

		$query = "SELECT * FROM category_groups";

		$data = mysqli_query($this->dbc, $query);

		while ($row = mysqli_fetch_array($data)) {

			echo '<option value="'.$row['cg_id'].'">'.$row['cg_name'].'</option>';

		}

		echo'</select>
		</fieldset>
		<input type="submit" value="Save Forum" name="submit" />
	</form>';
		echo '<table class="table">';
		echo '<thead><th>Forums</th><th>Options</th></thead>';
		$query = "SELECT * FROM categories ORDER BY cat_id ASC";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			echo '<tr>';
			echo '<td>'.$row['name'].'</td><td><a href="/deleteforum/f/'.$row['cat_id'].'">Delete Forum</a></td></tr>';
		}

		echo'</table>';
		echo'</div>';
	}
	}
	public function deletecat(){
		$this->core->isLoggedIn();
		if($this->core->verify("forum.*")){
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "DELETE FROM category_groups WHERE cg_id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Category has been successfully deleted. Would you like to <a href="/viewcategory">go back to the forums</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}



		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/deletecat">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
		echo'<input type="hidden" name="postid" value="'.$_GET['cat'].'">
		</fieldset>
		<input type="submit" value="Delete" name="submit" />   <a class="button" href="index.php">Cancel</a>
	</form>
	</div>';
	}
	}
	public function deleteforum(){
		$this->core->isLoggedIn();
		if($this->core->verify("forum.*")){
		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {

				$query = "DELETE FROM categories WHERE cat_id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Forum has been successfully deleted. Would you like to <a href="/viewcategory">go back to the forums</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/deleteforum">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
		echo'<input class="Link LButton" type="hidden" name="postid" value="'.$_GET['f'].'">
		</fieldset>
		<input type="submit" value="Delete" name="submit" />   <a class="button" href="index.php">Cancel</a>
	</form>
	</div>';
	}
	}
	public function newcat() {
		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		if (isset($_POST['submit'])) {
			$catt = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['catt'])));
			$perm = preg_replace("/[^0-9]/", "", $_POST['perm']);
			$securePerm = mysqli_real_escape_string($this->dbc, trim($perm));
			if (!empty($catt)) {
				$query = "INSERT INTO category_groups (`cg_name`, `perm`) VALUES ('$catt', '$securePerm')";
				mysqli_query($this->dbc, $query);
				echo '<p>Your category has been successfully added. Would you like to go back to the <a href="index.php?action=acp">Admin Panel</a>?</p>';
				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
		echo'<form enctype="multipart/form-data" method="post" action="/newcat">
		<fieldset>
		<legend>Create Category:</legend>
		  <div class="form-group">
			<label for="">Category Name</label>
			<input type="text" class="form-control" id="" name="catt" placeholder="Category Name">
		  </div>
		  <div class="form-group">
			<label for="">Permissions (Group ID)</label>
			<input type="text" class="form-control" id="" name="perm" placeholder="Perms">
		  </div>
		<input class="Link LButton" type="submit" value="Save Category" name="submit" />
	</form>';
		echo '<table class="table">';
		echo '<thead><th>Categories</th><th>Perms</th><th>Options</th></thead>';
		$query = "SELECT * FROM category_groups";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			echo '<tr>';
			echo '<td>'.$row['cg_name'].'</td><td>'.$row['perm'].'</td><td><a href="/deletecat/cat/'.$row['cg_id'].'">Delete Category</a></td></tr>';
		}

		echo'</table>';
		echo'</div>';
	}
	}
	public function category(){
		echo '<div class="shadowbar">';
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			echo '<a class="Link LButton" href="/newcat">New Category</a><a class="Link LButton" href="/posttopic">Post Topic</a><a class="Link LButton" href="/newforum">New Forum</a><a class="Link LButton" href="/postPoll">Post Poll</a>';
		}
		echo'
      <div id="transp" class="panel-body"><div role="tabpanel">

  <ul class="tabs" role="tablist">
    <li role="presentation" class="active"><a href="#categories" aria-controls="home" role="tab" data-toggle="tab">Forums</a></li>
    <li role="presentation"><a href="#polls" aria-controls="profile" role="tab" data-toggle="tab">Polls</a></li>
	<li role="presentation"><a href="#important" aria-controls="profile" role="tab" data-toggle="tab">Announcements/Important</a></li>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="categories">';
	if(isset($_SESSION['uid'])){
		$query = "SELECT `group` FROM `users` WHERE `uid` = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$uPerm = $row['group'];
	} else {
		$uPerm = 1;
	}
		$query = "SELECT * FROM category_groups WHERE `perm` <= '$uPerm'";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$category = $row['cg_id'];
			echo '<table class="table cgBox">';
			echo '<thead>';
			echo '<h3>'.$row['cg_name'].'</h3>';
			echo '<th>Forum</th>';
			echo '<th>Latest Posts</th>';
			echo '</thead>';
			echo'<tbody>';
			$query1 = "SELECT * FROM categories WHERE cg = ".$category." ORDER BY cat_id ASC";
			$data1 = mysqli_query($this->dbc, $query1);
			while ($row1 = mysqli_fetch_array($data1)) {
				$query2 = "SELECT users.*, posts.* FROM posts JOIN users ON users.uid = posts.user_id AND category = ".$row1['cat_id']." AND hidden = '0' ORDER BY post_id DESC";
				$count = mysqli_query($this->dbc, $query2);
				$rc = mysqli_fetch_array($count);
				echo'<tr>';
				echo'<td style="width:75%;"><a class="col-7" href="/f/'.$row1['cat_id'].'">' .$row1['name']. '<span class="badge">' . mysqli_num_rows($count) . ' Post(s)</span></a>';
				echo'<div class="col-6">'.$row1['desc'].'</div>';
				echo'</td>';
				echo'<td style="width:25%;">';
				if((mysqli_num_rows($count) > 0)){
					echo'<a href="/post/'.$rc['postlink'].'">'.$rc['tag'].' '.$rc['title'].'</a><br>';
					echo'By: <a href="/ucp/'.$rc['uid'].'">' . $rc['username'] . '</a>';
				}
				echo'</td></tr>';
			}
		}
		echo '</tbody></table></div>';
		echo '
		<div role="tabpanel" class="tab-pane" id="polls">
		';
		echo '<table class="table cgBox">';
		echo '<thead>
		<th>Poll</th>
		</thead>
		<tbody>
		';
		$query = "SELECT * FROM polls";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
		echo '<tr><td><a href="/viewPoll/'.$row['postlink'].'">'.$row['title'].'</a></td></tr>';
		}
		echo '
		</tbody>
		</table>
		</div>
		<div role="tabpanel" class="tab-pane" id="important">
		';
			$query = "SELECT `title`, `postlink`, `tag`, `post` FROM `posts` WHERE `tag` = '[!!]' ";
			$data = mysqli_query($this->dbc, $query);
			while ($row = mysqli_fetch_array($data)){
			$string = $row['post'];
			$truncated = (strlen($string) > 200) ? substr($string, 0, 200) . '...' : $string;
			$parsed = $this->parser->parse($truncated);
			echo '<div class="shadowbar">
			<table class="table cgBox">
			<tr>
			<td>
			<a class="col-7" href="/post/'.$row['postlink'].'">'.$row['tag'].' '.$row['title'].'</a>
			<div class="col-6">'.$parsed.'</div>
			</td>
			</tr>
			</table>
			</div>
			';
			}
		echo'
		</div>
		</div>
		</div>
		</div>
		</div>
		';

	}
	public function viewforum(){
		echo '<div class="shadowbar">';
		$cat = mysqli_real_escape_string($this->dbc, trim($_GET['cat']));
		$query = "SELECT posts.*, users.* FROM posts JOIN users ON users.uid = posts.user_id AND category = '".$cat."' AND hidden = '0' ORDER BY posts.post_id DESC";
		$data = mysqli_query($this->dbc, $query);
		echo '<table class="table cgBox">';
		echo '<thead>';
		echo '<th>Post Title</th>';
		echo '<th>Latest Posts</th>';
		echo '</thead>';
		echo '<tbody>';
		while ($row = mysqli_fetch_array($data)) {
			$query2 = "SELECT users.*, reply.* FROM reply JOIN users ON users.uid = reply.user_id AND post_id = ".$row['post_id']." AND hidden = '0' ";
			$count = mysqli_query($this->dbc, $query2);
			$rc = mysqli_fetch_array($count);
			if(!empty($row['title'])) {
				echo'<tr>';
				echo'<td>';
				if(($row['locked'] === '1')){
					echo'<a class="nav" href="/post/'.$row['postlink'].'"><img width="25px" height="25px" src="/include/images/lock.png" />'.$row['tag'].' '.$row['title']. '<span class="badge">' . mysqli_num_rows($count) . ' Replies</span></a>';
				} else {
					echo'<a class="nav" href="/post/'.$row['postlink'].'">'.$row['tag'].' '.$row['title']. '<span class="badge">' . mysqli_num_rows($count) . ' Replies</span></a>';
				}
				echo'</td>';
				echo'<td>';
				echo'Posted By:';
				echo '<a href="/ucp/'.$row['uid'].'">' . $row['username'] . '</a>';
				echo'</td>';
			}

		}
		echo '</tbody></table></div>';
	}
	public function vpoll() {

		$postid = mysqli_real_escape_string($this->dbc, $_GET['p']);
		$query = "SELECT `polls`.*, `users`.* FROM `polls` JOIN `users` ON `users`.`uid` = `polls`.`user_id` AND `polls`.`postlink` = '$postid' " ;
		$data = mysqli_query($this->dbc, $query) or die(mysqli_error($this->dbc));
		$row = mysqli_fetch_array($data);
			$replyTitle = $row['title'];
			$ID = $row['pid'];
			$parsed = $this->parser->parse($row['post']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $row['title'], $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
			echo '
			<div class="shadowbar">
			<h3>Poll Choices</h3>
			<ul class="navList">
			';
			$choices = explode(",", $row['choices']);
			foreach($choices as $choice){
			$choice = str_replace("'", "", $choice);
			$choiceO = str_replace(" ", "-", $choice);
			$choiceO = strtolower($choiceO);
			$query = "SELECT * FROM `votes` WHERE `poll` = '$ID' AND `choice` = '$choiceO'";
			$data = mysqli_query($this->dbc, $query);
			echo '<h4>'.$choice.': '.mysqli_num_rows($data). '</h4>';
			echo sprintf($this->layout['pollChoices'], $ID, $choiceO, $choice);
			}
			echo '
			</ul>
			</div>
			';


	}
	public function pollvote(){
	$this->core->isLoggedIn();
	$poll = mysqli_real_escape_string($this->dbc, $_GET['poll']);
	$vote = mysqli_real_escape_string($this->dbc, $_GET['choice']);
	$user = $_SESSION['uid'];
	$query = "SELECT `user`, `poll` FROM votes WHERE `user` = '$user' AND `poll` = '$poll' ";
	$data = mysqli_query($this->dbc, $query);
	$row = mysqli_fetch_array($data);
	if(empty($row)){
	$query = "INSERT INTO `votes` (`choice`, `user`, `poll`) VALUES ('$vote', '$user', '$poll') ";
	mysqli_query($this->dbc, $query);
	echo '<div class="shadowbar">Voted!</div>';
	} elseif(!empty($row)) {
	$query = "UPDATE `votes` SET `choice` = '$vote' WHERE `user` = '$user' AND `poll` = '$poll' ";
	mysqli_query($this->dbc, $query);
	echo '<div class="shadowbar">Your vote has been re-cast.</div>';
	}

	}
	public function homepage(){
	$query = "SELECT * FROM `fconf` ";
	$data = mysqli_query($this->dbc, $query);
	$fconf = mysqli_fetch_array($data);
	if($fconf['homeDisp'] == 'posts'){
	$query = "SELECT * FROM `posts` WHERE `hidden` = '0' ORDER BY `date` DESC LIMIT ".$fconf['homeNum']." ";
	$data = mysqli_query($this->dbc, $query);
	echo'
	<div class="shadowbar">
	<table class="table cgBox">
	<thead>
	<th>Latest Posts</th>
	</thead>
	';
	while($row = mysqli_fetch_array($data)){
	echo'<tr>';
	echo'<td><a class="nav" href="/post/'.$row['postlink'].'">'.$row['tag'].' '.$row['title']. '</a>';
	echo'</td>';
	echo'</tr>';
	}
	echo'
	</table>
	</div>
	';
	}
	if($fconf['homeDisp'] == 'polls'){
	$query = "SELECT * FROM `polls` ORDER BY `date` DESC LIMIT ".$fconf['homeNum']." ";
	$data = mysqli_query($this->dbc, $query);
	echo'
	<div class="shadowbar">
	<table class="table cgBox">
	<thead>
	<th>Latest Polls</th>
	</thead>
	';
	while($row = mysqli_fetch_array($data)){
	echo'<tr>';
	echo'<td><a class="nav" href="/viewPoll/p/'.$row['postlink'].'">'.$row['title'].'</a>';
	echo'</td>';
	echo'</tr>';
	}
	echo'
	</table>
	</div>
	';
	}
	if($fconf['homeDisp'] == 'both'){
	$query = "SELECT * FROM `posts` WHERE `hidden` = '0' ORDER BY `date` DESC LIMIT ".$fconf['homeNum']." ";
	$data = mysqli_query($this->dbc, $query);
	echo'
	<div class="shadowbar">
	<table class="table cgBox">
	<thead>
	<th>Latest Posts</th>
	</thead>
	';
	while($row = mysqli_fetch_array($data)){
	echo'<tr>';
	echo'<td><a class="nav" href="/post/'.$row['postlink'].'">'.$row['tag'].' '.$row['title']. '</a>';
	echo'</td>';
	echo'</tr>';
	}
	echo'
	</table>
	</div>
	';

	$query = "SELECT * FROM `polls` ORDER BY `date` DESC LIMIT ".$fconf['homeNum']." ";
	$data = mysqli_query($this->dbc, $query);
	echo'
	<div class="shadowbar">
	<table class="table cgBox">
	<thead>
	<th>Latest Polls</th>
	</thead>
	';
	while($row = mysqli_fetch_array($data)){
	echo'<tr>';
	echo'<td><a class="nav" href="/viewPoll/p/'.$row['postlink'].'">'.$row['title'].'</a>';
	echo'</td>';
	echo'</tr>';
	}
	echo'
	</table>
	</div>
	';
	}
	}
	public function editPost(){
		echo '<div class="shadowbar">';
		$postid = mysqli_real_escape_string($this->dbc, trim($_GET['post']));
		$query = "SELECT `user_id`, `post_id`, `post` FROM `posts` WHERE `postlink` = '$postid' ";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$pID = $row['post_id'];
		$uID = $row['user_id'];
		$post = $row['post'];
		if(isset($_POST['submit'])){
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['post']));
			$edit = mysqli_real_escape_string($this->dbc, trim($_POST['editor']));
			$uid = mysqli_real_escape_string($this->dbc, trim($_POST['user']));
			$pid = mysqli_real_escape_string($this->dbc, trim($_POST['pid']));
			$user = $_SESSION['uid'];
			if($uid != $_SESSION['uid']){
				echo 'You are not this user';
				exit();
			}
			$query = "SELECT `post_id` FROM `posts` WHERE `user_id` = '$user'  AND `post_id` = '$pid' ";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			if(count($row) < 1){
				echo 'This is not your post';
				exit();
			}
			$query = "UPDATE `posts` SET `post` = '$edit' WHERE `user_id` = '$uid' AND `post_id` = '$pid' ";
			$data = mysqli_query($this->dbc, $query);
			echo '<div class="shadowbar">Post Edited. <a href="/post/'.$postid.'">Go back to post</a>';
			exit();
			echo '</div>';
		}
		echo '
		<form action="/editpost/post/'.$postid.'" method="POST">
		<label for="edit">Edit post</label><br />
		<input type="hidden" name="post" value="'.$postid.'" />
		<input type="hidden" name="user" value="'.$uID.'" />
		<input type="hidden" name="pid" value="'.$pID.'" />
		<textarea rows="6" style="width:100%;" id="editor" name="editor">'.$post.'</textarea>
		<input type="submit" name="submit" value="Edit Post" />
		</form>
		';
		echo '</div>';
	}
	public function vpost() {
		echo '<div class="shadowbar">';
		if(isset($_GET['post'])){
		$postid = mysqli_real_escape_string($this->dbc, $_GET['post']);
		$permalink = "/post/".$postid;
		if(isset($_GET['mode']) && ($_GET['mode'] == 'lock')){
			if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
				$query = "UPDATE posts SET locked = 1, tag = '[Locked]' WHERE postlink = '$postid'";
				mysqli_query($this->dbc, $query);
				echo '<div class="alert alert-info"><strong>Post Locked</strong></div>';
			}
		} elseif(isset($_GET['mode']) && ($_GET['mode'] == 'unlock')){
			if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
				$query = "UPDATE posts SET locked = 0, tag = '' WHERE postlink = '$postid'";
				mysqli_query($this->dbc, $query);
				echo '<div class="alert alert-info"><strong>Post Unlocked</strong></div>';
			}
		}
		$query = "SELECT `posts`.*, `users`.* FROM `posts` JOIN `users` ON `users`.`uid` = `posts`.`user_id` AND `posts`.`postlink` = '$postid' WHERE `hidden` = '0' " ;
		$data = mysqli_query($this->dbc, $query) or die(mysqli_error($this->dbc));
		if(empty($data)){
		die("Invalid Action");
		}
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			echo '<a class="Link LButton" href="/post/post/'.$postid.'/mode/lock">Lock Post</a>';
			echo '<a class="Link LButton" href="/post/post/'.$postid.'/mode/unlock">Unlock Post</a>';
		}
			$row = mysqli_fetch_array($data);
		if(isset($_SESSION['uid'])){
			if($_SESSION['uid'] ==$row['user_id']){
				echo '<a class="Link LButton" href="/editpost/post/'.$postid.'">Edit Post</a>';
			}
		}
			$Title = $row['tag'].' '.$row['title'];
			$ID = $row['post_id'];
			echo '<a class="Link LButton" href="/forumsFB/post/'.$ID.'">FB Post Generator</a>';
			if(($row['locked'] != '1')){
				echo '<a class="Link LButton" href="/postreply/postid/'.$ID.'">Reply</a>';
			}
			$titler = $row['title'];
			$parsed = $this->parser->parse($row['post']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $Title, $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);

		//error_reporting(E_ALL);
		// Grab the profile data from the database
		$query = "SELECT reply.*, users.* FROM reply JOIN users ON users.uid = reply.user_id AND reply.post_id = $ID AND hidden = '0' ORDER BY reply.reply_id";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['reply']);
			$sig = $this->parser->parse($row['sig']);
			echo sprintf($this->layout['blogViewFormat'], $Title, $row['picture'], $row['uid'], $row['username'], date('M j Y g:i A', strtotime($row['date'])), $parsed, $sig);
		}

		echo '</div>';
	} else {
	echo '<div class="shadowbar">Invalid Query!</div>';
	}
	}

	public function rep() {
		$this->core->isLoggedIn();
		$secureCategory = preg_replace("/[^0-9]/", "", $_GET['pid']);
		$postid = mysqli_real_escape_string($this->dbc, $secureCategory);
		$query = "UPDATE posts SET reported = '1' WHERE post_id = '$postid'";
		$data = mysqli_query($this->dbc, $query);
		echo '<div class="shadowbar">
			<h3>Post Reported</h3>';
		echo 'Post Reported.';
		echo '</div>';
	}
	public function update(){
				$query = "SELECT `post_id`, `title` FROM `posts`";
				$data = mysqli_query($this->dbc, $query);
				while ($row = mysqli_fetch_array($data)){
				$postid = $row['post_id'];
				$title = $row['title'];
				$rawlink = $title.' '.$postid;
				//Lower case everything
				$postlink = strtolower($rawlink);
				//Make alphanumeric (removes all other characters)
				$postlink = preg_replace("/[^a-z0-9_\s-]/", "", $postlink);
				//Clean up multiple dashes or whitespaces
				$postlink = preg_replace("/[\s-]+/", " ", $postlink);
				//Convert whitespaces and underscore to dash
				$postlink = preg_replace("/[\s_]/", "-", $postlink);
				$query = "UPDATE `posts` SET `postlink` = '$postlink' WHERE `post_id` = '$postid' ";
				mysqli_query($this->dbc, $query);
				}
	}
	public function postPoll(){
		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		//Grab the profile data from the database
		$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['uid'];
		if (isset($_POST['submit'])) {
			// Grab the profile data from the POST
			$post1 = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['post1'])));
			$title = mysqli_real_escape_string($this->dbc, trim($_POST['title']));
			$choices = mysqli_real_escape_string($this->dbc, trim($_POST['choices']));

			// Update the post data in the database
			if (!empty($post1) && !empty($title)) {
				$query = "INSERT INTO polls (`date`, `title`, `post`, `choices`, `user_id`) VALUES (NOW(), '$title', '$post1', '$choices', '$username')";
				mysqli_query($this->dbc, $query);
				$query = "SELECT `pid` FROM `polls` WHERE `title` = '$title' ORDER BY `pid` DESC ";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$postid = $row['pid'];
				$rawlink = $title.' '.$postid;
				//Lower case everything
				$postlink = strtolower($rawlink);
				//Make alphanumeric (removes all other characters)
				$postlink = preg_replace("/[^a-z0-9_\s-]/", "", $postlink);
				//Clean up multiple dashes or whitespaces
				$postlink = preg_replace("/[\s-]+/", " ", $postlink);
				//Convert whitespaces and underscore to dash
				$postlink = preg_replace("/[\s_]/", "-", $postlink);
				$query = "UPDATE `polls` SET `postlink` = '$postlink' WHERE `pid` = '$postid' ";
				mysqli_query($this->dbc, $query);
				echo '<p>Your poll has been successfully added. Would you like to <a href="/viewcategory">view all of the polls</a>?</p>Link:'.$postlink;
				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		} // End of check for form submission
		echo'<form enctype="multipart/form-data" method="post" action="/postPoll">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Post Here:</legend>
			<label type="hidden" for="title">Title:</label><br />
			<input type="text" name="title"><br /><br />
		<label type="hidden" for="post1">Poll:</label><br />
		<script>edToolbar(\'bbcodeEditor\'); </script>
		<textarea class="ed" name="post1" id="bbcodeEditor" style="height:300px;width:100%;"></textarea><br />
		<label type="hidden" for="choices">Choices (separated by commas):</label><br />
		<input type="text" name="choices"><br />
		</fieldset>
		<input type="submit" value="Save Post" name="submit" />
	</form>
	</div>';
	}
	public function upost(){
		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		if($this->core->verify("forum.*")  || $this->core->verify("forum.post")){
		//Grab the profile data from the database
		$query = "SELECT * FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['uid'];
		if (isset($_POST['submit'])) {
		// Grab the profile data from the POST
			$uID = $_SESSION['uid'];
			$query = "SELECT `rep` FROM users WHERE uid = $uID";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$rep = $row['rep'];
			$modRep = $this->core->repUp($rep, 1.0);
			$modRep = round($modRep);
			$query = "UPDATE `users` SET `rep` = '$modRep' WHERE `uid` = '$uID'";
			mysqli_query($this->dbc, $query);
			$post1 = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['post1'])));
			$title = mysqli_real_escape_string($this->dbc, trim($_POST['title']));
			$category = mysqli_real_escape_string($this->dbc, trim($_POST['category']));
			$query = "SELECT `cg` FROM `categories` WHERE `cat_id` = '$category'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$cg = $row['cg'];
			$query = "SELECT `perm` FROM `category_groups` WHERE `cg_id` = '$category'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$perm = $row['perm'];
			$query = "SELECT `group` FROM `users` WHERE `uid` = '" . $_SESSION['uid'] . "'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$uPerm = $row['group'];
			if($perm <= $uPerm && is_int($category)){
			// Update the post data in the database
			if (!empty($post1) && !empty($title)) {
				$query = "INSERT INTO posts (`user_id`, `date`, `title`, `post`, `category`) VALUES ('$username', NOW(), '$title', '$post1', '$category')";
				mysqli_query($this->dbc, $query);
				$query = "SELECT `post_id` FROM `posts` WHERE `title` = '$title' ORDER BY `post_id` DESC ";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$postid = $row['post_id'];
				$rawlink = $title.' '.$postid;
				//Lower case everything
				$postlink = strtolower($rawlink);
				//Make alphanumeric (removes all other characters)
				$postlink = preg_replace("/[^a-z0-9_\s-]/", "", $postlink);
				//Clean up multiple dashes or whitespaces
				$postlink = preg_replace("/[\s-]+/", " ", $postlink);
				//Convert whitespaces and underscore to dash
				$postlink = preg_replace("/[\s_]/", "-", $postlink);
				$query = "UPDATE `posts` SET `postlink` = '$postlink' WHERE `post_id` = '$postid' ";
				mysqli_query($this->dbc, $query);
				echo '<p>Your post has been successfully added. Would you like to <a href="/viewcategory">view all of the posts</a>?</p>'.$postlink;
				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		} else {
			echo 'You do not have the permission required to post there.';
		}
		}

		// End of check for form submission
		echo'<form enctype="multipart/form-data" method="post" action="/posttopic">
		<fieldset>
		<legend>Post Here:</legend>
			<label type="hidden" for="title">Title:</label><br />
			<input type="text" name="title"><br /><br />';
		echo'<select id="category" name="category">';
		$query = "SELECT * FROM categories";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			echo '<option value="'.$row['cat_id'].'">'.$row['name'].'</option>';
		}
		echo'</select><br /><br />';
		echo'<label type="hidden" for="post1">Post Content:</label><br />
		<textarea class="ed" name="post1" id="editor" style="height:300px;width:100%;"></textarea><br />
		</fieldset>
		<input type="submit" value="Save Post" name="submit" />
	</form>
	</div>';
	} else {
		die("You cannot post.");
	}
	}
	public function delp(){
		$this->core->isLoggedIn();
if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){

		if (isset($_POST['submit'])) {
			$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
			if (!empty($postid)) {
				$query = "SELECT `user_id` FROM posts WHERE post_id = $postid";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$uID = $row['user_id'];
				$query = "SELECT `rep` FROM users WHERE uid = $uID";
				$data = mysqli_query($this->dbc, $query);
				$row = mysqli_fetch_array($data);
				$rep = $row['rep'];
				$modRep = $this->core->repDown($rep, 0.94);
				$modRep = round($modRep);
				$query = "UPDATE `users` SET `rep` = '$modRep' WHERE `uid` = '$uID'";
				mysqli_query($this->dbc, $query);
				$query = "DELETE FROM posts WHERE post_id = $postid";
				mysqli_query($this->dbc, $query);
				echo '<div class="shadowbar"><p>Post has been successfully deleted. Would you like to <a href="/moderation">go back to the admin panel</a>?</p></div>';

				exit();
			}
			else {
				echo '<p class="error">You must enter information into all of the fields.</p>';
			}
		}


		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/forumDeletePost">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
		echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Delete Post" name="submit" />   <a class="button" href="/moderation">Cancel</a>
	</form>
	</div>';

	}
	}
	public function hider(){
		$this->core->isLoggedIn();

		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			if (isset($_POST['submit'])) {
				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
				if (!empty($postid)) {

					$query = "UPDATE reply SET `hidden` = '1' WHERE reply_id = $postid";
					mysqli_query($this->dbc, $query);
					echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a href="/moderation">go back to replies</a>?</p></div>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}

			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/ForumHideReply/mode/hide">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Hide Post" name="submit" />   <a class="button" href="mcp.php">Cancel</a>
	</form>
	</div>';

		}
	}
	public function hidep(){
		$this->core->isLoggedIn();
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			if (isset($_POST['submit'])) {
				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
				if (!empty($postid)) {

					$query = "UPDATE posts SET `hidden` = '1' WHERE post_id = $postid";
					mysqli_query($this->dbc, $query);
					echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a class="Link LButton" href="/moderation">go back to the admin panel</a>?</p></div>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}

			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/ForumHidePost/mode/hide">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Hide Post" name="submit" />   <a class="button" href="mcp.php">Cancel</a>
	</form>
	</div>';

		}
	}
	public function unhidep(){
		$this->core->isLoggedIn();
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			if (isset($_POST['submit'])) {
				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
				if (!empty($postid)) {

					$query = "UPDATE posts SET `hidden` = '0' WHERE post_id = $postid";
					mysqli_query($this->dbc, $query);
					echo '<div class="shadowbar"><p>Post has been successfully unhidden. Would you like to <a class="Link LButton" href="/acp">go back to the Admin panel</a>?</p></div>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}


			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/ForumUnHidePost/mode/unhide">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Unhide Post" name="submit" />   <a class="button" href="mcp.php">Cancel</a>
	</form>
	</div>';

		}
	}
	public function unhider(){
		$this->core->isLoggedIn();
		//Check permissions
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			if (isset($_POST['submit'])) {
				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
				if (!empty($postid)) {
					//Unhide post in the database
					//Confirm success
					$query = "UPDATE reply SET `hidden` = '0' WHERE reply_id = $postid";
					mysqli_query($this->dbc, $query);
					echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a href="/moderation">go back to replies</a>?</p></div>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}


			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/ForumUnHideReply/mode/unhide">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Unhide Post" name="submit" />   <a class="button" href="mcp.php">Cancel</a>
	</form>
	</div>';

		}
	}
	public function delr(){
		$this->core->isLoggedIn();
		//Check permissions
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
			if (isset($_POST['submit'])) {
				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
				if (!empty($postid)) {

					$query = "DELETE FROM reply WHERE reply_id = $postid";
					mysqli_query($this->dbc, $query);
					//Confirm success
					echo '<div class="shadowbar"><p> has been successfully deleted. Would you like to <a href="/moderation">go back to replies</a>?</p></div>';

					exit();
				}
				else {
					echo '<p class="error">You must enter information into all of the fields.</p>';
				}
			}


			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/forumDeleteReply/mode/dr">
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
		<fieldset>
		<legend>Are you sure?</legend>';
			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
		</fieldset>
		<input type="submit" value="Delete Post" name="submit" />   <a class="button" href="/moderation">Cancel</a>
	</form>
	</div>';

		}
	}
	public function postreply(){
		$this->core->isLoggedIn();
		//Check permissions
		if($this->core->verify("forum.*") || $this->core->verify("forum.post"))
		if(isset($_GET['postid'])){
			$secureCategory = preg_replace("/[^0-9]/", "", $_GET['postid']);
			$postid = mysqli_real_escape_string($this->dbc, $secureCategory);
			$query = "SELECT locked FROM posts WHERE post_id = '$postid'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			if(($row['locked'] == '1')){
				echo '<div class="shadowbar"><h3>Reply</h3><p>Post is locked. Cannot reply.</p></div>';
				exit();
			}
			//Check to see if the post is from
			//A group or just a normal post, and
			//Check if the user is in the group
			$query = "SELECT `cat` FROM `posts` WHERE `post_id` = '$postid'";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			if(!is_int($row['cat'])){
				if(method_exists($this, 'inGroup')){
					$grp = explode("-", $row['cat']);
					$grp = end($grp);
					if(!inGroup($grp)){
						die('<div class="shadowbar">You are not in this group</div>');
					}
				}
			}
		}
		// Grab the profile data from the database
		$query = "SELECT uid FROM users WHERE uid = '" . $_SESSION['uid'] . "'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$username = $row['uid'];
		if (isset($_POST['submit'])) {
			// Grab the profile data from the POST
			$reply = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['reply'])));
			$secureCategory = preg_replace("/[^0-9]/", "", $_POST['replyid']);
			$replyid = mysqli_real_escape_string($this->dbc, $secureCategory);
			// Update the post data in the database
			$uID = $_SESSION['uid'];
			//Generate information on user rep
			//alter rep based on the post and
			//modifier for the forums. In this
			//case we will modify the rep by 0.4
			$query = "SELECT `rep` FROM users WHERE uid = $uID";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$rep = $row['rep'];
			$modRep = $this->core->repUp($rep, 0.4);
			$modRep = round($modRep);
			$query = "UPDATE `users` SET `rep` = '$modRep' WHERE `uid` = '$uID'";
			mysqli_query($this->dbc, $query);
			$query = "SELECT `user_id` FROM posts WHERE post_id = $replyid";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$uID = $row['user_id'];
			$query = "SELECT `rep` FROM users WHERE uid = $uID";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$rep = $row['rep'];
			$modRep = $this->core->repUp($rep, 0.8);
			$modRep = round($modRep);
			$query = "UPDATE `users` SET `rep` = '$modRep' WHERE `uid` = '$uID'";
			mysqli_query($this->dbc, $query);
			if (!empty($reply)) {
			$query = "SELECT `postlink` FROM `posts` WHERE `post_id` = '$replyid' ";
			$data = mysqli_query($this->dbc, $query);
			$row = mysqli_fetch_array($data);
			$post = $row['postlink'];
				$link = '/viewpost/post/'.$post;
				$description = 'Someone has replied to a post you are involved in';
				$infoquery = "SELECT DISTINCT `user_id` FROM reply WHERE `post_id` = '" .$replyid. "' AND `user_id` !='".$username."' ";
				$data = mysqli_query($this->dbc, $infoquery);
				while ($rows = mysqli_fetch_array($data)){
				$this->core->addNotification($rows['user_id'], $link, $description);
				}
				$link = '/viewpost/post/'.$post;
				$description = 'Someone has replied to your post';
				$infoquery = "SELECT DISTINCT `user_id` FROM posts WHERE `post_id` = '" .$replyid. "' ";
				$data = mysqli_query($this->dbc, $infoquery);
				$rows = mysqli_fetch_array($data);
				if($rows['user_id'] != $_SESSION['uid']){
				$this->core->addNotification($rows['user_id'], $link, $description);
				}

				//Add reply to the database
				$query = "INSERT INTO reply (`post_id`, `user_id`, `reply`, `date`) VALUES ('$replyid', '$username', '$reply', NOW())";
				mysqli_query($this->dbc, $query) or die(mysqli_error($this->dbc));
				//Confirm success with the user
				echo '<div class="shadowbar"><p>Your post has been successfully added. Would you like to <a href="/forums">view all of the posts</a>?</p></div>';
				exit();
			}
			else {
				echo '<div class="shadowbar"><p class="error">You must enter information into all of the fields.</p></div>';
			}
		} // End of check for form submission
		echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/postreply">
		<fieldset>
		<legend>Reply:</legend>
		<input type="hidden" name="replyid" value="'.$_GET['postid'].'">
		<textarea name="reply" id="editor" style="height:300px;width:100%;"></textarea><br />
		</fieldset>
		<input type="submit" value="Save Post" name="submit" />
	</form>
	</div>';
	}
	public function forumReplyAdmin(){
		echo '<div class="shadowbar">';
		$this->core->isLoggedIn();

		//Check Permissions
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){


		$query = "SELECT reply.*, users.* FROM reply JOIN users ON users.uid = reply.user_id ORDER BY reply.reply_id DESC ";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['reply']);
			echo sprintf($this->layout['adminReplyLayout'], 'N/A', $parsed, $row['reply_id'], 'forumDeleteReply', 'delete', $row['reply_id'], $row['hidden'], 'ForumHideReply', $row['reply_id'], 'ForumUnHideReply', $row['reply_id'], $row['username'], $row['group']);
		}
		echo '</div>';
	}
	}
	public function forumPostAdmin(){
		echo '<div class="shadowbar">';
		$this->core->isLoggedIn();
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
		$query = "SELECT posts.*, users.* FROM posts JOIN users ON users.uid = posts.user_id ORDER BY posts.post_id DESC ";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
			$parsed = $this->parser->parse($row['post']);
			echo sprintf($this->layout['moderationPostLayout'], $row['title'], $row['post_id'], $parsed, $row['post_id'], 'forumDeletePost', $row['post_id'], $row['hidden'], 'ForumHidePost', $row['post_id'], 'ForumUnHidePost', $row['post_id'], $row['username'], $row['group']);
		}
		echo '</div>';
	}
	}
	public function markPost(){
		//Check permissions
		if($this->core->verify("forum.*") || $this->core->verify("forum.mod")){
		if(isset($_POST['submit'])){
			$reply = mysqli_real_escape_string($this->dbc, strip_tags( trim($_POST['title'])));
			$secureCategory = preg_replace("/[^0-9]/", "", $_POST['id']);
			$replyid = mysqli_real_escape_string($this->dbc, $secureCategory);
			$tag = '['.$reply.']';
			//Update database info
			$query = "UPDATE `posts` SET `tag` = '$tag' WHERE `post_id` = '$replyid' ";
			$data = mysqli_query($this->dbc, $query);
			//Confirm success
			echo '<div class="shadowbar">Success!</div>';
			exit();
		}
		echo '
		<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/markAs">
				<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
				<fieldset>
				<legend>Mark As:</legend>
				<input type="hidden" name="id" value="'.$_GET['p'].'">
				<input type="text" name="title" /><br />
				</fieldset>
				<input type="submit" value="Save" name="submit" />
			</form>
		</div>
		';
	}
	}
	public function forumConf(){
	if(isset($_POST['submit'])){
		$homeDisp = mysqli_real_escape_string($this->dbc, trim($_POST['homeDisp']));
		$homeNum = mysqli_real_escape_string($this->dbc, trim($_POST['homeNum']));

		$query = "UPDATE `fconf` SET `homeDisp`='$homeDisp', `homeNum`='$homeNum' ";
		mysqli_query($this->dbc, $query);

		echo '<div class="shadowbar">Forum Module Configuration Updated.</div>';
	}
	$query = "SELECT * FROM `fconf` ";
	$data = mysqli_query($this->dbc, $query);
	$fconf = mysqli_fetch_array($data);
	echo '
<div class="shadowbar"><div class="alert alert-info">Please refer to the documentation <a href="http://cheesecakebb.org/index.php?action=pages&page=Settings">Here</a> for settings</div>
		<form method="post" action="/moderation">
		<fieldset>
		<legend>Settings</legend>
		<label>
		<span>Home Page Display</span>
		<input class="form-control" type="text" name="homeDisp" value="'.$fconf['homeDisp'].'" />
		</label>
		<label>
		<span>Number of Latest Post/Polls</span>
		<input class="form-control" type="text" name="homeNum" value="'.$fconf['homeNum'].'" />
		</label>
		</fieldset>
		<input class="Link LButton" type="submit" value="Submit Edits" name="submit" />
	</form>
	</div>
	';
	}
	public function forumModeration(){
		echo '<div class="shadowbar"> <div class="panel-body"><div role="tabpanel">

  <ul class="tabs" role="tablist">
    <li role="presentation"><a href="#posts" aria-controls="home" role="tab" data-toggle="tab">Posts</a></li>
    <li role="presentation"><a href="#replies" aria-controls="profile" role="tab" data-toggle="tab">Replies</a></li>
	<li role="presentation" class="active"><a href="#Conf" aria-controls="profile" role="tab" data-toggle="tab">Configuration</a></li>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane" id="posts">';
		$this->forumPostAdmin();
		echo '</div>  <div role="tabpanel" class="tab-pane" id="replies">';
		$this->forumReplyAdmin();
		echo '</div><div role="tabpanel" class="tab-pane active" id="Conf">';
		$this->forumConf();
		echo'</div></div></div></div></div>';
	}
	public function ogstat(){
		$postid = mysqli_real_escape_string($this->dbc, $_GET['post']);
		if(!file_exists("ogPost/")){
			mkdir("ogPost/");
		}
		$query = "SELECT * FROM posts WHERE post_id = '$postid'";
		$data = mysqli_query($this->dbc, $query);
		$row = mysqli_fetch_array($data);
		$t = $row['title'];
		$p = $this->parser->parse($row['post']);
		$p = strip_tags($p);
		$l = 'http://'.$this->settings['b_url'].'/post/'.$row['postlink'];
		$s = $this->settings['site_name'];
		$u = $row['user_id'];
		$picFormula = ($u + 1);
		$file = "ogPost/forum".$postid.'.html';
		$infLink = 'http://'.$this->settings['b_url'].'/ogPost/forum'.$postid.'.html';
		if(file_exists($file)){
			echo '<div class="shadowbar">Facebook Link: http://'.$this->settings['b_url'].'/ogPost/forum'.$postid.'.html</div>';
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
			$path = 'ogPost/forum'.$postid.'.html';
			file_put_contents($path, $OGFile);
			echo '<div class="shadowbar">Facebook Link: http://'.$this->settings['b_url'].'/ogPost/forum'.$postid.'.html</div>';
		}
	}
}
?>
