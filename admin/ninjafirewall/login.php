<?php
/*
 +------------------------------------------------------------------+
 | NinjaFirewall   (c)2012-2013 NinTechNet                          |
 |                 <contact@ninjafirewall.com>                      |
 |                                                                  |
 | EDITION :       Free Edition                                     |
 |                                                                  |
 | REVISION:       2013-08-18 15:26:01                              |
 +------------------------------------------------------------------+
 | This program is free software: you can redistribute it and/or    |
 | modify it under the terms of the GNU General Public License as   |
 | published by the Free Software Foundation, either version 3 of   |
 | the License, or (at your option) any later version.              |
 |                                                                  |
 | This program is distributed in the hope that it will be useful,  |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of   |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    |
 | GNU General Public License for more details.                     |
 +------------------------------------------------------------------+
*/

if (file_exists('install/') ) {
	echo '<html><head><title>NinjaFirewall</title><link href="static/styles.css" rel="stylesheet" type="text/css"><link rel="Shortcut Icon" type="image/gif" href="static/favicon.ico"></head><body><br><br><br><br><br><br><center class=smallblack><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;warning : the following directory has been found&nbsp;: <a href="install/" style="border-bottom:1px dotted #ffd821;">'.dirname(__FILE__).'/install/</a><p><li>If you want to install or upgrade NinjaFirewall, please click on the above link and follow the indicated steps.</li><br><li>If you already have installed NinjaFirewall, please <u>delete</u> the above directory and then reload this page<br>in order to access the administration console.</li></center></body></html>';
   exit;
}

session_start();

require(dirname(__FILE__) . '/config.php');

if ( (! $db_name) || (! $db_ip) || (! $db_port) || (! $db_user) || (! $db_pass) ) {
   if ( file_exists('install.php') ) {
      header('Location: install.php');
      exit;
   } else {
      echo 'Error : please edit the "config.php" file with your database credentials';
      exit;
   }
}

$dbh = new mysqli($db_ip, $db_user, $db_pass, $db_name, $db_port);
if (mysqli_connect_error() ) {
	die('error (line ' . __LINE__ . ') : mysqli_connect_error');
}

$max_time = 5;
$dbqa = $dbh->query('SELECT * FROM `' . $db_prefix . 'nf_banned`');
while ( $dbbanned  = $dbqa->fetch_object() ) {
   if ( ($dbbanned->ip == $_SERVER['REMOTE_ADDR']) &&
        ($dbbanned->attempt > 2) &&
        ( (time() - $dbbanned->time) < $max_time * 60) ) {
      login_page( 2, ($dbbanned->time + $max_time * 60) - time() );
      exit;
   }
   if ( (time() - $dbbanned->time) > $max_time * 60)
      $dbh->query('DELETE FROM `' . $db_name . '`.`' . $db_prefix .'nf_banned` WHERE `' . $db_prefix .'nf_banned`.`ip` = "' . $dbbanned->ip . '"');
}

$dbqh = $dbh->query('SELECT * FROM `' . $db_prefix . 'nf_admin`');
if (! $dbadmin = $dbqh->fetch_object() ) {
   die('error (line ' . __LINE__ . ') : fetch_object error');
}
if ( ($dbadmin->login_ssl) && ($_SERVER['SERVER_PORT'] != 443) ) {
   header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] );
   exit;
}

if ( (! isset($_POST['user'])) || (! isset($_POST['pass'])) )  { login_page(0,0); }
/*
if ( (! preg_match('/^[a-z0-9]{6,12}$/D',  $_POST['user']) ) ||
     (! preg_match('/^[a-z0-9]{6,12}$/iD', $_POST['pass'])) ) {
	log_ip();
	login_page(1,0);
}*/

if ( ($_POST['user'] === $dbadmin->name) && ( sha1($_POST['pass']) === $dbadmin->password) ) {
   $adm_log = fopen ('logs/admin.log', 'a');
   @fwrite ($adm_log, date('[d/M/Y H:i:s O] ') . '[' . $dbadmin->name . '] ' .
      '[' . $_SERVER['REMOTE_ADDR'] . '] ' . '[OK] ' .  "\n" );

   if ($dbadmin->login_alert) {
      $subject = '[NinjaFirewall] Admin console login';
      $message = "Someone just logged into your NinjaFirewall admin interface:\n\n".
                 "- IP   : ". $_SERVER['REMOTE_ADDR'] . "\n" .
                 "- Date : ". date('F j, Y @ g:i a') . "\n" .
                 "- URL  : http://". $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "\n";

		$headers = 'From: "'. $dbadmin->email .'" <'. $dbadmin->email .'>' . "\r\n";
		mail($dbadmin->email, $subject, $message, $headers, '-f'. $dbadmin->email);
   }

   $_SESSION['nf_adm'] = $dbadmin->name;
   $_SESSION['timeout'] = time();
   $_SESSION['token'] = $token = sha1(time());
	session_write_close();
   header('Location: index.php?token='. $token);

	exit;
}

log_ip();
login_page(1,0);

exit;

/********************************************************************/
function log_ip() {

	global $dbh;
	global $db_name;
	global $db_prefix;
	global $dbadmin;

   $dbqa = $dbh->query('SELECT * FROM `' . $db_name . '`.`' . $db_prefix . 'nf_banned` WHERE `' . $db_prefix . 'nf_banned`.`ip` = "' .  $_SERVER['REMOTE_ADDR'] . '"');
   if ( $dbbanned = $dbqa->fetch_object() ) {
		$dbh->query('UPDATE `' . $db_name . '`.`' . $db_prefix . 'nf_banned` SET `attempt` = "' . ($dbbanned->attempt + 1 ) .
            '" WHERE `' . $db_prefix . 'nf_banned`.`ip` = "' . $_SERVER['REMOTE_ADDR'] . '"');

      if ($dbbanned->attempt == 2) {
         $subject = '[NinjaFirewall] Admin failed login attempts';
         $message = "NinjaFirewall has blocked 3 failed login attempts to your admin console:\n\n".
                    "- Date : ". date('F j, Y @ g:i a') . "\n" .
						  "- IP   : ". $_SERVER['REMOTE_ADDR'] . "\n" .
						  "- URL  : http://". $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "\n\n" .
                    "This IP was banned for 5 minutes.\n";

			$headers = 'From: "'. $dbadmin->email .'" <'. $dbadmin->email .'>' . "\r\n";
			mail($dbadmin->email, $subject, $message, $headers, '-f'. $dbadmin->email);
      }

   } else {
      $dbh->query('INSERT INTO `' . $db_name . '`.`' . $db_prefix .'nf_banned` (`ip`, `time`, `attempt` ) VALUES ("' .
            $_SERVER['REMOTE_ADDR'] . '", "'. time() . '", "1")');
   }

}
/********************************************************************/
function login_page($err, $time) {

   global $dbadmin;
   global $max_time;
   echo '<html><head><title>NinjaFirewall : Admin login</title><link href="static/styles.css" rel="stylesheet" type="text/css"><link rel="Shortcut Icon" type="image/gif" href="static/favicon.ico"></head><body ';

   if ($err < 2) { echo 'onload="document.wl.user.focus();"'; }
   echo '><br><br><br><br>';

   if ( $_SERVER['QUERY_STRING'] == 'logout' ) {
      echo '<center class=tinyred>Your session has been closed.</center>';
   } elseif ( $_SERVER['QUERY_STRING'] == 'expired' ) {
      echo '<center class=tinyred>Your session has expired.</center>';
   } elseif ( $err == 2) {
      echo '<center><table border=0 class=tinyblack cellpadding=10 style="border:1px #FFD821 solid;"><tr><td align=center class=tinyred><img src="static/icon_error.png" border=0 width=16 height=16>&nbsp;You have been banned for '. $max_time .' minutes !<p>Only '. $time .' seconds left....</td></tr></table><br><br><br><br><br><font class=tinygrey>&copy; 2012-'. date('Y') .' <a style="border-bottom:dotted 1px #FDCD25;color:#999999;" href="http://nintechnet.com/" target="_blank" title="The Ninja Technologies Network">NinTechNet</a><br>The Ninja Technologies Network</font></center></body></html>';
      exit;
   } elseif ( $err == 1 ) {
      echo '<center class=tinyred><img src="static/icon_error.png" border=0 width=16 height=16>&nbsp;Wrong username or password.</center>';
	}
   echo '<p><form method=post action=login.php name=wl><center class=tinyblack><fieldset style="width:300px;border:1px solid #ffd821;"><legend><b>&nbsp;Admin login&nbsp;</b></legend><table width=100% align=center border=0 class=tinyblack cellpadding=10 style="border:none;"><tr><td align=right width=50%>Username : </td><td width=50%><input id=username class=input type=text size=15 name=user maxlength=30></td></tr><tr><td align=right width=50%>Password : </td><td width=50%><input id=password class=input type=password size=15 name=pass maxlength=30></td></tr><tr><td width=50%>&nbsp;</td><td align=center width=50%><input class=button type=submit value=Login></td></tr></table></fieldset></center></form><p><center class=tinygrey>&copy; 2012-'. date('Y') .' <a style="border-bottom:dotted 1px #FDCD25;color:#999999;" href="http://nintechnet.com/" target="_blank" title="The Ninja Technologies Network">NinTechNet</a><br>The Ninja Technologies Network</center></body></html>';

   if ( $err == 1 ) {
      $adm_log = fopen ('logs/admin.log', 'a');
      @fwrite ($adm_log, date('[d/M/Y H:i:s O] ') . '[' . $dbadmin->name . '] ' .
         '[' . $_SERVER['REMOTE_ADDR'] . '] ' . '[FAILED]' . "\n" );
   }

   exit;
}
/********************************************************************/
// EOF
?>
