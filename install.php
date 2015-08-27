<html>
<head>
  <title>CMS Installer</title>
  <link rel="stylesheet" type="text/css" href="/include/style/core/style.css">
  <link rel="stylesheet" type="text/css" href="/include/style/core/template.css">
</head>
<body>
<div class="row">
  <div class="col-2"></div>
  <div class="col-8">
<?php
error_reporting(E_ALL); ini_set("display_errors", 1);
define('CCore', true);
if(!isset($_GET['action'])){
  echo '<div class="shadowbar"><a class="Link LButton" href="/installer/step/1">Install the software</a><a class="Link LButton" href="/installer/upgrade">Upgrade current installation</a></div>';
}
//install script
require_once('include/scripts/core.class.php');
if(!isset($_GET['step'])){
  $_GET['step'] = 0;
}
$installer = new install($_GET['step']);
if(isset($_GET['action'])){
  if($_GET['action'] == "installer"){
    switch($_GET['step']){
      case 1:
        $installer->information();
        break;
      case 2:
        $installer->setVars();
        break;
    }
  }
  if($_GET['action'] == "upgrade"){
    $installer->upgrade();
  }
}
?>
</div>
</div>
</body>
</html>
