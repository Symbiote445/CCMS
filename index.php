<?php
	error_reporting(E_ALL); ini_set('display_errors', 1);
	define("CCore", true);
	session_start();
	//Load files...
	require_once('include/scripts/settings.php');
	require_once('include/scripts/version.php');
	require('include/scripts/core.class.php');
	require('include/scripts/nbbc_main.php');
	$parser = new BBCode;
	require_once('include/scripts/layout.php');
	require_once('include/scripts/page.php');
	//Set Variables...
	global $dbc, $parser, $layout, $main, $settings, $core;
	$core = new core($settings, $version, $dbc, $layout, $parser);
	set_error_handler(array($core, "errHandlr"));
	$admin = new admin($settings, $version, $dbc, $layout, $core, $parser);
	$page = new pageGeneration($settings, $version, $dbc, $layout, $core, $parser, $admin);
	$page->Generate();
?>
