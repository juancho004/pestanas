<?php
/*
 +------------------------------------------------------------------+
 | NinjaFirewall   (c)2012-2013 NinTechNet                          |
 |                 <contact@ninjafirewall.com>                      |
 +------------------------------------------------------------------+
 | REVISION:       2013-08-18 22:43:35                              |
 +------------------------------------------------------------------+
*/
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) { die('Forbidden'); }


$exist=strpos($_SERVER['PHP_SELF'],'login.php');


if($exist)
  $path=str_replace('/admin/ninjafirewall/login.php','',$_SERVER['SCRIPT_FILENAME']);
if(strpos($_SERVER['PHP_SELF'],'index.php'))	
    $path=str_replace('/admin/ninjafirewall/index.php','',$_SERVER['SCRIPT_FILENAME']);
if(strpos($_SERVER['PHP_SELF'],'install'))
    $path=str_replace('/admin/ninjafirewall/install/index.php','',$_SERVER['SCRIPT_FILENAME']);
if(strpos($_SERVER['PHP_SELF'],'web'))
    $path=str_replace('/web/index.php','',$_SERVER['SCRIPT_FILENAME']);
if(strpos($_SERVER['PHP_SELF'],'admin'))
    $path=str_replace('/admin/index.php','',$_SERVER['SCRIPT_FILENAME']);

require($path.'/config/clib.php');


$confp      = new Lib('conn');

// Run the installer to configure the following variables :
$db_name = $confp->_v('DBNAME');
$db_user = $confp->_v('USER');
$db_pass = $confp->_v('PASSWORD');
$db_port = $confp->_v('PORTNIN');
$db_ip = $confp->_v('HOST');
$db_prefix = $confp->_v('PREFIX');

// This can be setup from your admin console (Account/Options menu) :
@date_default_timezone_set('UTC');
?>