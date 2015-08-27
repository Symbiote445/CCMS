<?php
//Page Generation
class pageGeneration {
	public function __construct($settings, $dbc, $layout, $parser, $modules, $cms_vars){
		$this->settings = $settings;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->parser = $parser;
		$this->modules = $modules;
		$this->vars = $cms_vars;
		$this->pageGen = $this;
		self::GenerationModifiers($this->settings, $this->modules, $this->vars);
		$core = new core($this->settings, $this->dbc, $this->layout, $this->parser, $this->modules, $this->vars, $this->pageGen);
		$this->core = $core;
		register_shutdown_function(array($core, 'fatalErrHandlr'));
		$admin = new admin($this->settings, $this->dbc, $this->layout, $this->core, $this->parser, $this->vars);
		$this->admin = $admin;
		$this->version = $this->vars['ver'];

	}
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
	public function loadModule($operator, &$modules){
		if(isset($operator)){
			$option = $operator;
			if($option === 'nav'){
				foreach($modules as $name => $module) if ($module['enabled']) {
					if($module['nav'] == 1){
						echo '<li><a href="'.$module['href'].'">'.$module['description'].'</a></li>';
					}
				}
			}
			if($option === 'initialLoad'){
				$settings = $this->settings;
				$version = $this->version;
				$dbc = $this->dbc;
				$layout = $this->layout;
				$core = $this->core;
				$parser = $this->parser;
				foreach($modules as $name => $module) if ($module['enabled']) {
					$dir = strtolower($module['description']);
					require_once('include/scripts/'.$dir.'/'.$module['link']);
				}
			}
			if($option === 'editModule'){
				foreach($modules as $name => $module) if ($module['enabled']) {
					return '<option value="include/scripts/'.$module['link'].'">'.$module['link'].'</option>';
				}
			}
			if($option === 'sidebar'){
				foreach($modules as $name => $module) if ($module['enabled'] && ($module['admin'] == '0')) {
					echo '<li class="navList-item"><a class="btn btn-default width100" href="'.$module['sidebar'].'">'.$module['sidebarDesc'].'</a></li>';
				}
			}
			if($option === 'acp'){
				foreach($modules as $name => $module) if ($module['enabled'] && ($module['admin'] == '1')) {
					if($this->core->verify("core.*") || $this->core->verify($module['perms']))
					echo '<li class="navList-item"><a class="btn btn-default width100" href="'.$module['acp'].'">'.$module['sidebarDesc'].'</a></li>';
				}
			}
		}
	}
	public function Generate(){
		if(isset($_GET['action'])){
			if($_GET['action'] === 'logout'){
				$this->core->logout();
			}
			if($_GET['action'] == 'doLogin'){
				$this->core->login();
				exit();
			}
			if($_GET['action'] == 'postComment'){
				$this->core->addcomment();
				exit();
			}
	}
		$this->parser->SetSmileyURL("http://".$this->settings['b_url']."/include/images/smileys");
		$this->core->checkLogin();
		echo sprintf($this->layout['header-begin'], $this->settings['site_name'], $this->settings['style'], $this->settings['style'], $this->settings['style'], $this->settings['style'], $this->settings['site_name']);
		$this->loadModule("nav", $this->modules);
		print($this->layout['header-end']);
		$this->core->counter();
		print($this->layout['donate-begin']);
		print($this->layout['div-end']);
		if($this->settings['sidebarDisp'] == "true"){
		if(!isset($_SESSION['uid'])){
			print($this->layout['userbarLoggedOut']);
		} else {
			print($this->layout['userbarLoggedIn']);
		}
		} else {
			echo '<div class="col-6">';
		}
		//$this->core->sidebar();
		$this->core->securityAgent("check");
			if(!isset($_GET['action'])){
				if($this->settings['home_display'] == 'none' || $this->settings['home_display'] == 'about'){
					echo '<div class="shadowbar">';
					$parsed = $this->parser->parse($this->settings['about']);
					print($parsed);
					echo '</div>';
				}
			}
		$this->loadModule("initialLoad", $this->modules);
		if(isset($_GET['action'])){
			if($_GET['action'] == 'login'){
				if(isset($error)){
					echo $error;
				}
				print($this->layout['login']);
			}
			if($_GET['action'] === 'signup'){
				$this->core->signup();
			}
			if($_GET['action'] === 'verifyaccount'){
				$this->core->activate();
			}
			if($_GET['action'] === 'acp'){
				$this->admin->acp();
			}
			if($_GET['action'] === 'ucp'){
				$this->core->ucp();
			}
			if($_GET['action'] === 'editprofile'){
				$this->core->editprofile();
			}
			if($_GET['action'] == "messages"){
			$this->core->viewConvo();
			}
			if($_GET['action'] == "viewmessage"){
			$this->core->viewMessage();
			}
			if($_GET['action'] == "sendmessage"){
			$this->core->sendMessage();
			}
			if($_GET['action'] == "replymessage"){
			$this->core->sendMessageReply();
			}
			if($_GET['action'] == "passwordReset"){
			$this->core->deactivateAndReset();
			}
		}
		$this->core->onlineList();
		$this->loadModule("initialLoad", $this->modules);
		$this->core->notifBar();
		echo sprintf($this->layout['footer'], $this->settings['b_url'], $this->settings['site_name'], $this->version);
	}
}
?>
