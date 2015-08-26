<?php
//Group Plugin
//Gage LeBlanc
//Created for
//Cheesecake CMS

class Groups {
  public function __construct($settings, $dbc, $layout, $core, $parser){
		$this->settings = $settings;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;

	}
  public function groupPerm($perm, $group){
    $p = mysqli_real_escape_string($this->dbc, trim($perm));
    $g = mysqli_real_escape_string($this->dbc, trim($group));
    $u = mysqli_real_escape_string($this->dbc, trim($_SESSION['uid']));
    if($this->inGroup($g)){
      //Get perms from database
      $query = "SELECT `gPerm` FROM `users` WHERE `uid` = '$u'";
      $data = mysqli_query($this->dbc, $query);
      $row = mysqli_fetch_array($data);
      $permArr = $row['gPerm'];
      //Decode JSON array from database
      $permArr = json_decode($permArr, true);
      $gPerms = explode(";", $permArr[$g]);
      if(in_array($p, $gPerms)){
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  public function inGroup($group){
    $g = mysqli_real_escape_string($this->dbc, trim($group));
    $u = mysqli_real_escape_string($this->dbc, trim($_SESSION['uid']));
    $query = "SELECT `gPerm` FROM `users` WHERE `uid` = '$u'";
    $data = mysqli_query($this->dbc, $query);
    $row = mysqli_fetch_array($data);
    $permArr = $row['gPerm'];
    $permArr = json_decode($permArr, true);
    $permArr = array_keys($permArr);
    if(in_array($g, $permArr)){
      return true;
    } else {
      return false;
    }
  }
  public function listUserGroups($keys=NULL){
    $u = mysqli_real_escape_string($this->dbc, trim($_SESSION['uid']));
    $query = "SELECT `gPerm` FROM `users` WHERE `uid` = '$u'";
    $data = mysqli_query($this->dbc, $query);
    $row = mysqli_fetch_array($data);
    if(isset($row['gPerm'])){
      $permArr = $row['gPerm'];
      $permArr = json_decode($permArr, true);
      if($keys){
        $permArr = array_keys($permArr);
        return $permArr;
      } else {
        return $permArr;
      }
    } else {
      return false;
    }
  }
  public function main(){
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    $query = "SELECT * FROM `fGroups` WHERE `gid` = '$g'";
    $data = mysqli_query($this->dbc, $query);
    $row = mysqli_fetch_array($data);
    echo sprintf($this->layout['fGroupDisplay'], $row['name'], $g, $g, $row['desc']);
    if(!$this->inGroup($g)){
      echo('<br /><br /><a class="Link LButton" href="/Group/g/'.$g.'/do/join">Join Group</a><br />');
    }
    print($this->layout['fGroupDisplayEnd']);
  }
  public function listGroupMembers(){
    echo('<div class="shadowbar">');
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    $query = "SELECT `users` FROM `fGroups` WHERE `gid` = '$g'";
    $data = mysqli_query($this->dbc, $query);
    $row = mysqli_fetch_array($data);
    $memArr = json_decode($row['users'], true);
    foreach($memArr as $member){
      $query = "SELECT * FROM `users` WHERE `uid` = '$member'";
      $data = mysqli_query($this->dbc, $query);
      $row = mysqli_fetch_array($data);
      $u = $row['username'];
      $id = $row['uid'];
      echo('<a href="/ucp/uid/'.$id.'">'.$u.'</a>');
      if($this->groupPerm("g.*", $g)){
        echo('<a href="/Group/g/'.$g.'/do/deleteuser/u/'.$id.'"> (Remove user)</a>');
        echo('<a href="/Group/g/'.$g.'/do/editperms/u/'.$id.'"> (Edit perms)</a><br />');
      } else {
        echo('<br />');
      }
    }
    echo('</div>');
  }
  public function delGroupUser(){
    $u = mysqli_real_escape_string($this->dbc, trim($_GET['u']));
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    if($this->inGroup($g)){
      if($this->groupPerm("g.del", $g) || $this->groupPerm("g.*", $g)){
        if($u == $_SESSION['uid']){
          die('<div class="shadowbar">You cannot delete yourself!</div>');
        }
        $query = "SELECT `users` FROM `fGroups` WHERE `gid` = '$g'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $userArr = json_decode($row['users'], true);
        if(array_search($u, $userArr) != false){
          $del = array_search($u, $userArr);
          if(array_key_exists($del, $userArr)){
            unset($userArr[$del]);
            $userArr = json_encode($userArr);
            $query = "UPDATE `fGroups` SET `users` = '$userArr' WHERE `gid` = '$g'";
            mysqli_query($this->dbc, $query);
            echo('<div class="shadowbar">User Deleted. <a href="/Group/g/'.$g.'/do/panel">Go back to the panel</a></div>');
          } else {
            print("none");
          }
        } else {
          print("none");
        }
      } else {
        die('<div class="shadowbar">You cannot do that.</div>');
      }
    }
  }
  public function userPermissionEditor(){
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    if(isset($_POST['submit'])){
      $u = mysqli_real_escape_string($this->dbc, trim($_POST['u']));
      $perms = mysqli_real_escape_string($this->dbc, trim($_POST['perms']));
      $query = "UPDATE `users` SET `gPerm` = '$perms' WHERE `uid` = '$u'";
      mysqli_query($this->dbc, $query);
      echo('<div class="shadowbar">User updated. <a href="/Group/g/'.$g.'/do/panel">Back to the panel</a></div>');
    } else {
      $u = mysqli_real_escape_string($this->dbc, trim($_GET['u']));
      $query = "SELECT `gPerm` FROM `users` WHERE `uid` = '$u'";
      $data = mysqli_query($this->dbc, $query);
      $row = mysqli_fetch_array($data);
      $permArr = $row['gPerm'];
      echo('
      <div class="shadowbar">
      <form action="/Group/g/'.$g.'/do/editperms" method="post">
      <textarea name="perms" cols="50" rows="4">'.$permArr.'</textarea>
      <input type="hidden" name="u" value="'.$_GET['u'].'" /><br />
      <input class="Link LButton" type="submit" value="Submit" name="submit" />   <a class="button" href="/Group/g/'.$g.'/do/panel">Cancel</a>
      </form>
      </div>
      ');
    }
  }
  public function addGroupUser(){
    $u = mysqli_real_escape_string($this->dbc, trim($_SESSION['uid']));
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    if($this->inGroup($g)){
      echo('<div class="shadowbar">You are already a member of this group.</div>');
    } else {
      $query = "SELECT `locked` FROM `fGroups` WHERE `gid` = '$g'";
      $data = mysqli_query($this->dbc, $query);
      $row = mysqli_fetch_array($data);
      if($row['locked']){
        echo('<div class="shadowbar">This group is not accepting members</div>');
      } else {
        $query = "SELECT `users` FROM `fGroups` WHERE `gid` = '$g'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $userArr = json_decode($row['users']);
        print_r($userArr);
        $query = "SELECT `gPerm` FROM `users` WHERE `uid` = '$u'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $permArr = $row['gPerm'];
        $permArr = json_decode($permArr, true);
        $permArr[$g] = "g.user";
        //print_r($permArr);
        $permArr = json_encode($permArr);
        //print_r($permArr);
        $query = "UPDATE `users` SET `gPerm` = '$permArr' WHERE `uid` = '$u'";
        mysqli_query($this->dbc, $query);
        $uc = count($userArr);
        $uak = $uc++;
        $userArr[$uak] = $u;
        $userArr = json_encode($userArr);
        /*
        if(is_array($userArr)){
          $userArr = array_push($userArr, $u);
          $userArr = json_encode($userArr);
        } else {
          $userArr = array();
          $userArr[0] = $u;
          $userArr = json_encode($userArr);
        }
        */
        print_r($userArr);
        $query = "UPDATE `fGroups` SET `users` = '$userArr' WHERE `gid` = '$g'";
        mysqli_query($this->dbc, $query);
        echo('<div class="shadowbar">You have joined this group. <a href="/Group/g/'.$g.'">Go back to the group</a></div>');
      }
    }
  }
  public function panel(){
    $g = mysqli_real_escape_string($this->dbc, trim($_GET['g']));
    echo('<div class="shadowbar"><a class="Link LButton" href="/Group/g/'.$g.'">Back to group</a></div>');
    if($_GET['do'] == 'panel'){
      if($this->groupPerm("g.admin", $g) || $this->groupPerm("g.*", $g)){
        echo sprintf($this->layout['fGroupPanel'], $g, $g, $g);
        $query = "SELECT `locked` FROM `fGroups` WHERE `gid` = '$g'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        if($row['locked']){
          echo('<div class="shadowbar"><h3>Locked settings</h3><a class="Link LButton" href="/Group/g/'.$g.'/do/unlockGroup">Unlock Group</a></div>');
        } else {
          echo('<div class="shadowbar"><h3>Locked settings</h3><a class="Link LButton" href="/Group/g/'.$g.'/do/lockGroup">Lock Group</a></div>');
        }
      }
    }
    if($_GET['do'] == 'changeDesc'){
      if($this->groupPerm("g.admin", $g) || $this->groupPerm("g.*", $g)){
        if(isset($_POST['submit'])){
          $desc = mysqli_real_escape_string($this->dbc, trim($_POST['description']));
          $query = "UPDATE `fGroups` SET `desc` = '$desc' WHERE `gid` = '$g'";
          mysqli_query($this->dbc, $query);
          die('<div class="shadowbar">Description Updated. <a href="/Group/g/'.$g.'/do/panel">Go back to the panel</a></div>');
        }
        $query = "SELECT `desc` FROM `fGroups` WHERE `gid` = '$g'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $desc = $row['desc'];
        echo sprintf($this->layout['fGroupChangeDesc'], $g, $desc);
      }
    }
    if($_GET['do'] == 'changeName'){
      if($this->groupPerm("g.admin", $g) || $this->groupPerm("g.*", $g)){
        if(isset($_POST['submit'])){
          $name = mysqli_real_escape_string($this->dbc, trim($_POST['name']));
          $query = "UPDATE `fGroups` SET `name` = '$name' WHERE `gid` = '$g'";
          mysqli_query($this->dbc, $query);
          die('<div class="shadowbar">Name Changed. <a href="/Group/g/1/do/panel">Go back to the panel</a></div>');
        }
        $query = "SELECT `name` FROM `fGroups` WHERE `gid` = '$g'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $name = $row['name'];
        echo sprintf($this->layout['fNameChangeDesc'], $g, $name);
      }
    }
    if($_GET['do'] == 'grpForumAdmin'){
      if($this->groupPerm("g.admin", $g) || $this->groupPerm("g.*", $g)){
        echo '<div class="shadowbar">';
    		$this->core->isLoggedIn();
    		$query = "SELECT posts.*, users.* FROM posts JOIN users ON users.uid = posts.user_id AND `category` = 'group-$g' ORDER BY posts.post_id DESC ";
    		$data = mysqli_query($this->dbc, $query);
    		while ($row = mysqli_fetch_array($data)) {
    			$parsed = $this->parser->parse($row['post']);
    			echo sprintf($this->layout['moderationPostLayout'], $row['title'], $row['post_id'], $parsed, $row['post_id'], "Group/g/$g/do/deletePost", $row['post_id'], $row['hidden'], "Group/g/$g/do/hidePost", $row['post_id'], "Group/g/$g/do/unhide", $row['post_id'], $row['username'], $row['group']);
    		}
    		echo '</div>';
      }
    }
    if($_GET['do'] == 'deletePost'){
      if($this->groupPerm("g.*", $g) || $this->groupPerm("g.mod", $g)){
      if (isset($_POST['submit'])) {
        $postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
        $query = "SELECT `category` FROM `posts` WHERE `post_id` = '$postid'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $grp = explode("-", $row['category']);
        $grp = end($grp);
        if(!$this->inGroup($grp)){
          die('<div class="shadowbar">invalid.</div>');
        }
        if (!empty($postid)) {
          $query = "SELECT `user_id` FROM posts WHERE post_id = $postid";
          $data = mysqli_query($this->dbc, $query);
          $row = mysqli_fetch_array($data);
          $uID = $row['user_id'];
          $query = "SELECT `rep` FROM users WHERE uid = $uID";
          $data = mysqli_query($this->dbc, $query);
          $row = mysqli_fetch_array($data);
          $rep = $row['rep'];
          $modRep = $this->core->repDown($rep, 0.99);
          $modRep = round($modRep);
          $query = "UPDATE `users` SET `rep` = '$modRep' WHERE `uid` = '$uID'";
          mysqli_query($this->dbc, $query);
          $query = "DELETE FROM posts WHERE post_id = $postid";
          mysqli_query($this->dbc, $query);
          echo '<div class="shadowbar"><p>Post has been successfully deleted. Would you like to <a href="/Group/g/'.$g.'/do/panel">go back to the admin panel</a>?</p></div>';

          exit();
        }
        else {
          echo '<p class="error">invalid</p>';
        }
        }
        echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/Group/g/'.$g.'/do/deletePost">
    		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
    		<fieldset>
    		<legend>Are you sure?</legend>';
    		echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
    		</fieldset>
    		<input type="submit" value="Delete Post" name="submit" />   <a class="button" href="/Group/g/'.$g.'/do/panel">Cancel</a>
    	</form>
    	</div>';
    }
    }
    if($_GET['do'] == 'hidePost'){
      $this->core->isLoggedIn();
  		if($this->groupPerm("g.*", $g) || $this->groupPerm("g.mod", $g)){
  			if (isset($_POST['submit'])) {
  				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
  				if (!empty($postid)) {

  					$query = "UPDATE posts SET `hidden` = '1' WHERE post_id = $postid AND `category` = 'group-$g'";
  					mysqli_query($this->dbc, $query);
  					echo '<div class="shadowbar"><p>Post has been successfully hidden. Would you like to <a class="Link LButton" href="/Group/g/'.$g.'/do/panel">go back to the admin panel</a>?</p></div>';

  					exit();
  				}
  				else {
  					echo '<p class="error">You must enter information into all of the fields.</p>';
  				}
  			}

  			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/Group/g/'.$g.'/do/hidePost">
  		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
  		<fieldset>
  		<legend>Are you sure?</legend>';
  			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
  		</fieldset>
  		<input type="submit" value="Hide Post" name="submit" />   <a class="button" href="/Group/g/'.$g.'/do/panel">Cancel</a>
  	</form>
  	</div>';

  }
    }
    if($_GET['do'] == 'unhide'){
      $this->core->isLoggedIn();
  		if($this->groupPerm("g.*", $g) || $this->groupPerm("g.mod", $g)){
  			if (isset($_POST['submit'])) {
  				$postid = mysqli_real_escape_string($this->dbc, trim($_POST['postid']));
  				if (!empty($postid)) {

  					$query = "UPDATE posts SET `hidden` = '0' WHERE post_id = $postid AND `category` = 'group-$g'";
  					mysqli_query($this->dbc, $query);
  					echo('<div class="shadowbar"><p>Post has been successfully unhidden. Would you like to <a class="Link LButton" href="/Group/g/'.$g.'/do/panel">go back to the Admin panel</a>?</p></div>');

  					exit();
  				}
  				else {
  					echo '<p class="error">You must enter information into all of the fields.</p>';
  				}
  			}


  			echo'<div class="shadowbar"><form enctype="multipart/form-data" method="post" action="/Group/g/'.$g.'/do/unhide">
  		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MM_MAXFILESIZE; ?>" />
  		<fieldset>
  		<legend>Are you sure?</legend>';
  			echo'<input type="hidden" name="postid" value="'.$_GET['del'].'">
  		</fieldset>
  		<input type="submit" value="Unhide Post" name="submit" />   <a class="button" href="/Group/g/'.$g.'/do/panel">Cancel</a>
  	</form>
  	</div>';

  		}
    }
    if($_GET['do'] == 'unlockGroup'){
      $query = "UPDATE `fGroups` SET `locked` = '0' WHERE `gid` = '$g'";
      mysqli_query($this->dbc, $query);
  		echo('<div class="shadowbar">Group has been successfully unlocked. Would you like to <a class="Link LButton" href="/Group/g/'.$g.'/do/panel">go back to the Admin panel</a>?</div>');
    }
    if($_GET['do'] == 'lockGroup'){
      $query = "UPDATE `fGroups` SET `locked` = '1' WHERE `gid` = '$g'";
      mysqli_query($this->dbc, $query);
  		echo('<div class="shadowbar">Group has been successfully locked. Would you like to <a class="Link LButton" href="/Group/g/'.$g.'/do/panel">go back to the Admin panel</a>?</div>');
    }
  }
  public function groupPost(){
		$this->core->isLoggedIn();
		echo '<div class="shadowbar">';
		if($this->core->verify("forum.*")  || $this->core->verify("forum.post")){
		//Grab the profile data from the database
		$username = $_SESSION['uid'];
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
      $cat = "group-$category";
      $groups = $this->listUserGroups(true);
			if(in_array($category, $groups)){
			// Update the post data in the database
			if (!empty($post1) && !empty($title)) {
				$query = "INSERT INTO posts (`user_id`, `date`, `title`, `post`, `category`) VALUES ('$username', NOW(), '$title', '$post1', '$cat')";
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
				echo '<p>Your post has been successfully added. Would you like to <a href="/viewcategory">view all of the posts</a>?</p>';
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
		echo'<form enctype="multipart/form-data" method="post" action="/groupPost">
		<fieldset>
		<legend>Post Here:</legend>
			<label type="hidden" for="title">Title:</label><br />
			<input type="text" name="title"><br /><br />';
		echo'<select id="category" name="category">';
    $groups = $this->listUserGroups(true);
		foreach($groups as $group) {
      $query = "SELECT `name` FROM `fGroups` WHERE `gID` = '$group'";
      $data = mysqli_query($this->dbc, $query);
      $row = mysqli_fetch_array($data);
			echo '<option value="'.$group.'">'.$row['name'].'</option>';
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
  public function groupList(){
		echo '<div class="shadowbar">';
		echo'
      <div id="transp" class="panel-body"><div role="tabpanel">

  <ul class="tabs" role="tablist">
    <li role="presentation" class="active"><a href="#groups" aria-controls="home" role="tab" data-toggle="tab">Group Forums</a></li>
    <li role="presentation"><a href="#list" aria-controls="profile" role="tab" data-toggle="tab">Group List</a></li>
  </ul>

  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="groups">';
	if(isset($_SESSION['uid'])){
			echo '<table class="table cgBox">';
			echo '<thead>';
			echo '<h3>Group Forums</h3>';
			echo '<th>Forum</th>';
			echo '<th>Latest Posts</th>';
			echo '</thead>';
			echo'<tbody>';
      $groups = $this->listUserGroups(true);
      foreach($groups as $group){
        $query = "SELECT * FROM `fGroups` WHERE `gid` = '$group'";
        $data = mysqli_query($this->dbc, $query);
        $row = mysqli_fetch_array($data);
        $query2 = "SELECT users.*, posts.* FROM posts JOIN users ON users.uid = posts.user_id AND category = 'group-$group' AND hidden = '0' ORDER BY post_id DESC";
				$count = mysqli_query($this->dbc, $query2);
				$rc = mysqli_fetch_array($count);
				echo'<tr>';
				echo'<td style="width:75%;"><a class="col-7" href="/f/group-'.$group.'">' .$row['name']. '<span class="badge">' . mysqli_num_rows($count) . ' Post(s)</span></a>';
				echo'<div class="col-6">'.$row['desc'].'</div>';
				echo'</td>';
				echo'<td style="width:25%;">';
				if((mysqli_num_rows($count) > 0)){
					echo'<a href="/post/'.$rc['postlink'].'">'.$rc['tag'].' '.$rc['title'].'</a><br>';
					echo'By: <a href="/ucp/'.$rc['uid'].'">' . $rc['username'] . '</a>';
				}
				echo'</td></tr>';
      }
		echo '</tbody></table></div>';
		echo '
		<div role="tabpanel" class="tab-pane" id="list">
		';
		echo '<table class="table cgBox">';
		echo '<thead>
		<th>Groups</th>
		</thead>
		<tbody>
		';
		$query = "SELECT * FROM `fGroups`";
		$data = mysqli_query($this->dbc, $query);
		while ($row = mysqli_fetch_array($data)) {
		echo '<tr><td><a href="/Group/'.$row['gid'].'">'.$row['name'].'</a></td></tr>';
		}
		echo '
		</tbody>
		</table>
		</div>';
		echo'
		</div>
		</div>
		</div>
		</div>
		';

	}
  }
}

$group = new Groups($this->settings, $this->dbc, $this->layout, $this, $this->parser);

if(isset($_GET['action'])){
  if($_GET['action'] == 'groupPost'){
    $group->groupPost();
  }
  if($_GET['action'] == 'forums'){
    $group->groupList();
  }
  if($_GET['action'] == 'Group' && !isset($_GET['do'])){
    $group->main();
  }
  if(isset($_GET['do'])){
    if($_GET['do'] == 'users'){
      $group->listGroupMembers();
    }
    if($_GET['do'] == 'deleteuser'){
      $group->delGroupUser();
    }
    if($_GET['do'] == 'join'){
      $group->addGroupUser();
    }
    if($_GET['do'] == 'editperms'){
      $group->userPermissionEditor();
    }
    if($_GET['do'] == 'panel' || $_GET['do'] == 'changeDesc' || $_GET['do'] == 'changeName' || $_GET['do'] == 'grpForumAdmin' || $_GET['do'] == 'deletePost' || $_GET['do'] == 'hidePost' || $_GET['do'] == 'unhide' || $_GET['do'] == 'unlockGroup' || $_GET['do'] == 'lockGroup'){
      $group->panel();
    }
  }
}
