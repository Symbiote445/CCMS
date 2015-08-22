<?php
//Page Generation
class pageGeneration {
	public function __construct($settings, $version, $dbc, $layout, $core, $parser, $admin){
		$this->settings = $settings;
		$this->version = $version;
		$this->dbc = $dbc;
		$this->layout = $layout;
		$this->core = $core;
		$this->parser = $parser;
		$this->admin = $admin;

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
		$this->core->loadModule("nav");
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
		$this->core->loadModule("initialLoad");
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
		$this->core->loadModule("initialLoad");
		$this->core->notifBar();
		echo sprintf($this->layout['footer'], $this->settings['b_url'], $this->settings['site_name'], $this->version['core']);
	}
}
?>
