<?php
	error_reporting(E_ALL); ini_set("display_errors", 1);
	define("CCore", true);
	session_start();
	//Load files...
	require_once('include/scripts/settings.php');
	require_once("modules.php");
	require('include/scripts/core.class.php');
	require('include/scripts/nbbc_main.php');
	$parser = new BBCode;
	require_once('include/scripts/layout.php');
	require_once('include/scripts/page.php');
	//Set Variables...
	global $dbc, $parser, $layout, $main, $settings, $core, $modules;
	/*
	$core = new core($settings, $version, $dbc, $layout, $parser, $modules);
	//set_error_handler(array($core, 'fatalErrHandlr'));
	//set_exception_handler(array($core, 'fatalErrHandlr'));
	register_shutdown_function(array($core, 'fatalErrHandlr'));
	$admin = new admin($settings, $version, $dbc, $layout, $core, $parser);
	*/
	$cms_vars = array();
	$page = new pageGeneration($settings, $dbc, $layout, $parser, $modules, $cms_vars);
	$page->Generate();
?>
