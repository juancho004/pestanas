<?php
/*
 +------------------------------------------------------------------+
 | NinjaFirewall   (c)2012-2013 NinTechNet                          |
 |                 <contact@ninjafirewall.com>                      |
 |                                                                  |
 | EDITION :       Free Edition                                     |
 +------------------------------------------------------------------+
*/
define('VERSION', '1.2.1');
/*
 +------------------------------------------------------------------+
 | REVISION:       2013-08-18 15:27:05                              |
 +------------------------------------------------------------------+
 |                                                                  |
 | This program is free software: you can redistribute it and/or    |
 | modify it under the terms of the GNU General Public License as   |
 | published by the Free Software Foundation, either version 3 of   |
 | the License, or (at your option) any later version.              |
 |                                                                  |
 | This program is distributed in the hope that it will be useful,  |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of   |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the    |
 | GNU General Public License for more details.                     |
 |                                                                  |
 +------------------------------------------------------------------+
*/
if (file_exists('install/') ) {
	 echo '<html><head><title>NinjaFirewall</title><link href="static/styles.css" rel="stylesheet" type="text/css"><link rel="Shortcut Icon" type="image/gif" href="static/favicon.ico"></head><body><br><br><br><br><br><br><center class=smallblack><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;warning : the following directory has been found&nbsp;: <a href="install/" style="border-bottom:1px dotted #ffd821;">'.dirname(__FILE__).'/install/</a><p><li>If you want to install or upgrade NinjaFirewall, please click on the above link and follow the indicated steps.</li><br><li>If you already have installed NinjaFirewall, please <u>delete the above directory</u> and then reload this page<br>in order to access the administration console.</li></center></body></html>';
   exit;
}

if (PHP_OS !== 'Linux') {
	echo 'Error : NinjaFirewall runs only on Linux, not on ['. PHP_OS .'] !';
	exit;
}
if (version_compare(PHP_VERSION, '5.1.0', '<')) {
	echo 'Error : NinjaFirewall requires PHP 5.1.x, not PHP '. floatval(phpversion()) .' !';
	exit;
}

session_start();

if ( $_SERVER['QUERY_STRING'] == 'logout') {
	session_destroy();
   header('Location: login.php?logout');
   exit;
}

if (! isset($_SESSION['timeout']) || (! isset($_SESSION['nf_adm'])) ||
	(! isset($_SESSION['token']))  || (! isset($_REQUEST['token'])) ) {
	if (! isset($_SESSION['first_run']) ) { session_destroy(); }
   header('Location: login.php');
   exit;
}

if ($_SESSION['token'] != $_REQUEST['token']) {
	session_destroy();
   header('Location: login.php');
   exit;
}

if ( ($_SESSION['timeout'] + 1800) < time() ) {
	session_destroy();
   header('Location: login.php?expired');
   exit;
}

require(dirname(__FILE__) . '/config.php');

if ( (!$db_name) || (!$db_ip) || (!$db_port) || (!$db_user) || (!$db_pass) ) {
	session_destroy();
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
   session_destroy();
   die('error (line ' . __LINE__ . ') : mysql_connect');
}
$db_port = $db_user = $db_pass = '';


$dbqh = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_admin`');

if (! $dbadmin  = $dbqh->fetch_object() ) {
	session_destroy();
   die('error (line ' . __LINE__ . ') : fetch_object error');
}
if ( ($dbadmin->login_ssl) && ($_SERVER['SERVER_PORT'] != 443) ) {
   header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] );
   exit;
}

if ($_SESSION['nf_adm'] != $dbadmin->name){
	session_destroy();
   header('Location: login.php');
   exit;
}

$_SESSION['timeout'] = time();

if ( (isset($_REQUEST['mid'])) && (preg_match('/^[1-39][0-3]$/D', $_REQUEST['mid'])) ) {
   $mid = $_REQUEST['mid'];
} else {
   $mid = 10;
   $act = 0;
}

if ( (isset($_REQUEST['act'])) && (preg_match('/^[0-9]$/D', $_REQUEST['act'])) ) {
   $act = $_REQUEST['act'];
} else {
   $act = 0;
}

if ( (isset($_REQUEST['itm']))  && (preg_match('/^[0-9]$/D', $_REQUEST['itm'])) ) {
   $itm = $_REQUEST['itm'];
} else {
   $itm = 0;
}
if ( (isset($_REQUEST['xtr']))  && (preg_match('/^[0-9]{7}$/D', $_REQUEST['xtr'])) ) {
   $xtr = $_REQUEST['xtr'];
} else {
   $xtr = '\d{7}';
}

define('ADMIN_LOG_FILE', 'logs/admin.log');
if (! defined('LOG_FILE')) { define('LOG_FILE', 'logs/firewall_' . date('Y-m') . '.log'); }


if (! isset($_SESSION['ver']) ) {
	$_SESSION['vapp'] = $_SESSION['vrules'] = $_SESSION['ver'] = 0;
}

$dbqh = $dbh->query('SELECT * FROM `'. $db_prefix .'nf`');
if (! $dbnf  = $dbqh->fetch_object() ) {
   die('error (line ' . __LINE__ . ') : fetch_object error');
}

if (@strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === FALSE) {
	define('OPA','opacity:0.6');
	define('OPA_OUT','this.style.opacity=0.6');
	define('OPA_OVER','this.style.opacity=1');
} else {
	define('OPA','filter:alpha(opacity=60)');
	define('OPA_OUT','this.filters.alpha.opacity=60');
	define('OPA_OVER','this.filters.alpha.opacity=100');
}

if ($mid == 90) {
	raw_admin_log();
} elseif ($mid == 91) {
   flush_admin_log();
   raw_admin_log();
} elseif ($mid == 22) {
	menu_account_update();
} elseif ($mid == 30) {
   if ($act == 1) menu_firewall_options_save();
   else menu_firewall_options();
} elseif ($mid == 31) {
   if ($act == 1) menu_firewall_policies_save();
   else menu_firewall_policies(0);
} elseif ($mid == 32) {
   if ( ($act == 1) && ($itm > 0) ) {
      define('NF_NODBG',true);
      if ($itm == 7) {
         if (! $fh = fopen(LOG_FILE, 'r') ) {
            echo '<br><br><br><center><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;unable to open ' . LOG_FILE . ' !</center><br></td>';
            exit;
         }
         echo '<textarea style="width:100%;
height:299px;border:none;font-family:\'Courier New\',Courier,monospace;font-size:12px;padding:4px;" wrap="off">';
         while (! feof($fh) ) {
            echo str_replace('<', '&lt;', fgets($fh));
			}
         echo '</textarea>';
         fclose($fh);
         exit;
      } elseif ( ($itm == 8) && ($xtr) ) {
         $what = '.+';
      } elseif ($itm < 6) {
         $what = $itm;
      } else {
         $what = '.+';
		}
     if (! $fh = fopen(LOG_FILE, 'r') ) {
         echo '<br><br><br><center><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;unable to open '. LOG_FILE .' !</center><br></td>';
         exit;
      }
      $total = 0;
      $res = '';
      while (! feof($fh) ) {
         $line = fgets($fh);
         if ( preg_match('/^\[(.+?)(?:\s.\d{4})?\]\s+.+?\[(#' . $xtr . '(?:-\d+)?)\]\s+\[(' . $what .')\]\s+\[([\d.]+?)\]\s+\[.+?\]\s+\[(.+?)\]\s+\[(.+?)\]\s+\[(.+?)\]\s+\[(.+)\]$/', $line, $match ) ) {
				list( $match2_a, $match2_b ) = explode( '-', $match[2] . '-' );
				if ( $match2_b ) {
					$match[2] = $match2_a . '  ' . str_pad( $match2_b, 4 , ' ', STR_PAD_LEFT);
				} else {
					$match[2] = $match2_a . '     -';
				}
            if (! $total) {
               $intro = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DATE&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INCIDENT&nbsp;&nbsp;RULE&nbsp;&nbsp;';
               if ($itm == 6) { $intro .= 'LEVEL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; }
               $intro .= '&nbsp;&nbsp;&nbsp;IP&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;REQUEST' . "\n\n";
				}
            $total++;
            $levels = array('', 'medium', 'high', 'critical', 'error', 'upload', 'info');
				if ( preg_match('/\D/', $match[1] ) ) {
					$res .= $match[1] . '  ' . $match[2] ;
				} else {
					$res .= date( 'd/M/y H:i:s', $match[1] ) . '  ' . $match[2] ;
				}
            if ($itm == 6) {
					$res .= '  ' . str_pad( $levels[$match[3]], 8 , ' ', STR_PAD_RIGHT );
				}
            $res .= '  ' . str_pad( $match[4], 15, ' ', STR_PAD_RIGHT);
            $res .= '  ' . $match[5] . ' ' . $match[6] . ' - ' . $match[7] . ' - [' . $match[8] . "]\n";
         }
      }
      fclose($fh);
      if (! $total) {
         echo '<br><br><br><center><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;no activities match your criteria !</center><br></td>';
      } else {
			$log_stat = stat( LOG_FILE );
         echo '<textarea style="width:100%;height:299px;border:none;font-family:\'Courier New\',Courier,monospace;font-size:12px;padding:4px;" wrap="off">' . $intro . htmlentities( $res ) . '</textarea><br /><center><span style="color:#666666;font-style:italic;font-size:10px;">The log is rotated monthly - Current size: ' . number_format( $log_stat['size'] ) .' bytes</span></center>';
		}
      exit;
   }
   menu_firewall_log();
} elseif ($mid == 33) {
	menu_firewall_editor();
} elseif ($mid == 11) {
   menu_summary_stats($xtr);
} elseif ($mid == 20) {
	menu_account_license();
} elseif ($mid == 21) {
   if ($act == 1) {
		menu_account_options_save();
   } else {
		menu_account_options(0);
	}
} else {
   menu_summary_overview();
}

exit;

/********************************************************************/
function menu_summary_overview() {

   html_header(1);

   global $dbnf;
   global $dbh;
   global $db_name;
   global $db_prefix;

   $dbqh = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_options`');
   if (! $dboptions  = $dbqh->fetch_object() ) {
      die('error (line ' . __LINE__ . ') : fetch_object error');
	}

   echo '<br><fieldset><legend>&nbsp;<a href="javascript:toogle(\'cinfo\');" class="links" title="Click to show/hide informations"><b>Info</b></a>&nbsp;</legend>
      <table id=cinfo width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';

   if ( (isset($_COOKIE['cinfo'])) && ($_COOKIE['cinfo'])==1 ) {
      echo ' style="display:none;"';
	}
   echo '>';

   $on = 0;
   if (! $dboptions->enabled ) {
      echo '<tr valign=top>
       <td valign=top width=45%>Firewall</td>
       <td width=10% align=center><img src="static/icon_error.png" border=0 width=16 height=16 title="Warning !"></td>
       <td width=45%><font color=red>Protection is disabled!</font><br><a class=links href="?mid=30&token='.$_REQUEST['token'].'"class=links style="border-bottom:1px dotted #FFCC25;">Click here</a> to enable the firewall protection for your site.</td>
      </tr>';
   } else {
      $on++;
      echo '<tr valign=top>
      <td width=45%>Firewall</td>
      <td width=10% align=center><img src="static/icon_ok.png" border=0 width=16 height=16></td>
      <td width=45%>Enabled</td>
     </tr>';
   }

   if (preg_match('/apache/i', PHP_SAPI) ) {
		$sapi = '.htaccess';
	} else {
		$sapi = 'php.ini';
	}

   if ( dirname(__FILE__) .'/firewall.php' === ini_get('auto_prepend_file') ) {
      $on++;
      echo '<tr valign=top>
      <td width=45%>PHP hook</td>
      <td width=10% align=center><img src="static/icon_ok.png" border=0 width=16 height=16></td>
      <td width=45%>Enabled</td>
     </tr>';
	} else {
      echo '<tr valign=top>
       <td valign=top width=45%>PHP hook</td>
       <td width=10% align=center><img src="static/icon_error.png" border=0 width=16 height=16 title="Warning !"></td>
       <td width=45%><font color=red>Protection is disabled!</font><br>Please ensure that you followed the instructions to setup the firewall using the <b>'.$sapi.'</b> file.</td>
      </tr>';
   }

   echo '<tr>
       <td valign=top width=45%>PHP SAPI</td>
       <td width=10% align=center>&nbsp;</td>
       <td width=45%>' . strtoupper(PHP_SAPI) . '</td>
      </tr>';

	if ($_SESSION['ver'] < 1) {
		if (! preg_match('/^\d\d\.\d\d\.\d\d$/', $dbnf->version_rules) ) { $dbnf->version_rules = '00.00.00';	}

		$tmp = '';
		if (function_exists('curl_init') ) {
			$ch = curl_init();
			curl_setopt ($ch, CURLOPT_URL, 'http://ninjafirewall.com/version.php?edn=0&nf=1');
			curl_setopt($ch, CURLOPT_USERAGENT, 'NinjaFirewall/' . VERSION . '_' . $dbnf->version_rules);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
			$tmp = @curl_exec($ch);
			curl_close($ch);
		}

		if ( strpos($tmp, '::') !== false ) {
			list($null, $res_status, $res_vapp, $res_vrules) = explode('::', $tmp);
			if ($null == 'NF1') {
				if (! preg_match('/^\d{1,2}\.\d\.\d$/', $res_vapp) ) {
					$_SESSION['vapp'] = VERSION;
				} else {
					$_SESSION['vapp'] = $res_vapp;
				}
				if (! preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $res_vrules) ) {
					$_SESSION['vrules'] = $dbnf->version_rules;
				} else {
					$_SESSION['vrules'] = $res_vrules;
				}
				$_SESSION['ver'] = 1;
			}
		}
	}

	echo '<tr>
       <td valign=top width=45%>License Type</td>
       <td width=10% align=center>&nbsp;</td>
       <td width=45%><b>Free Edition</b> (<a class=links style="border-bottom:1px dotted #FFCC25;" href="?mid=20&token='.$_REQUEST['token'].'" title="Upgrade to NinjaFirewall Pro Edition">upgrade to Pro Edition&nbsp;!</a>)</td>
       </tr>

       <tr>
		 <td valign=top width=45%>Engine version</td>
       <td width=10% align=center>';

	if ($_SESSION['ver'] == 0) {
		echo '<img src="static/icon_warn.png" border=0 width=16 height=16>
		</td>
      <td width=45%>Unable to retrieve update informations from NinjaFirewall server&nbsp;!</td>
      </tr>';

   } else {

		if ( isset($_SESSION['first_run']) ) { $_SESSION['vrules'] = $dbnf->version_rules;}

		if ( version_compare( VERSION, $_SESSION['vapp'], '<' ) ) {
			echo '<img src="static/icon_warn.png" border=0 width=16 height=16 title="Update available !">
		   </td>
         <td width=45%>v'.VERSION.' : <font color=red>a newer version ('.$_SESSION['vapp'].') is available&nbsp;!</font>
         <br>Please <a class=links style="border-bottom:1px dotted #FFCC25;" title="Click to access NinjaFirewall.com and download the new version." href="http://ninjafirewall.com/download.html">download it</a> from NinjaFirewall.com website.</td></tr>';
         $_SESSION['jswarn'] = 1;
		} else {
			echo '<img src="static/icon_ok.png" border=0 width=16 height=16>
			</td>
         <td width=45%>v'.VERSION.'</td>
         </tr><tr>
          <td valign=top width=45%>Rules version</td>
          <td width=10% align=center>';

			if ( version_compare( $dbnf->version_rules, $_SESSION['vrules'], '<' ) ) {
				echo '<img src="static/icon_warn.png" border=0 width=16 height=16 title="Update available !">
		      </td>
            <td width=45%>v' . $dbnf->version_rules . ' : <font color=red>new security rules (v' . $_SESSION['vrules'] . ') are available&nbsp;!</font><br>Click <a class=links style="border-bottom:1px dotted #FFCC25;" title="Click to access the update menu" href="?mid=22&token='.$_REQUEST['token'].'">here to update.</a></td></tr>';
			} else {
				echo '<img src="static/icon_ok.png" border=0 width=16 height=16>
			   </td>
            <td width=45%>v' .$dbnf->version_rules. '</td>
            </tr>';
			}
		}
	}

   if ($dboptions->debug) {
      echo '<tr valign=top>
    <td width=45%>Debugging</td>
    <td width=10% align=center><img src="static/icon_warn.png" border=0 width=16 height=16></td>
    <td width=45%>NinjaFirewall is running in <i>Debug Mode</i> - do not forget to <a class=links style="border-bottom:1px dotted #FFCC25;" title="Click to access the options menu" href="?mid=30&token='.$_REQUEST['token'].'">disable it</a> before going live</td>
   </tr>';
   }

   if ($fh = @fopen(ADMIN_LOG_FILE, 'r' ) ) {
      $tot_line = 0;
      $login_array = array();
      while (! feof($fh) ) {
         $line = fgets($fh);
         if ( preg_match('/^\[([^\s]+)\s+([^\s]+).+?\]\s+\[(.+?)\]\s+\[(.+?)\]\s+\[OK/', $line, $match) ) {
            array_push($login_array,  $match[3] . '::' . $match[4] . '::' . $match[1] . '::' . $match[2]);
            $tot_line++;
         }
      }
      fclose($fh);
      if ($tot_line > 1) {
         array_pop($login_array);
         list($who, $ip, $date, $hour) = explode('::', array_pop($login_array));
         echo '<tr valign=top><td width=45%>Last Login</td><td width=10% align=center>&nbsp;</td><td width=45%>' .
               $who . ' ['.$ip.'] on '. str_replace('/', '-', $date) .' at '.$hour.' - <a class=links style="border-bottom:1px dotted #FFCC25;" title="Click to view the admin log" href="javascript:popup(\'?mid=90&token='.$_REQUEST['token'].'\',640,480,0)">View admin log</a></td></tr>';
      }
   }

	if (! file_exists(ADMIN_LOG_FILE) ) {
      if ($fh = @fopen(ADMIN_LOG_FILE, 'w') ){
			fclose($fh);
			@chmod(ADMIN_LOG_FILE, 0666);
		}
   }
	if ( (! file_exists(ADMIN_LOG_FILE)) || (! is_writable(ADMIN_LOG_FILE)) ) {
		echo '<tr valign=top>
    <td width=45%>Admin Logfile</td>
    <td width=10% align=center><img src="static/icon_error.png" border=0 width=16 height=16 title="Warning !"></td>
    <td width=45%><font color=red>warning : ['. ADMIN_LOG_FILE.'] is not writable! Please chmod it to 0666 (rw-rw-rw-) and chmod the "logs/" directory to 0777 (rwxrwxrwx).</font></td>
    </tr>';
	}

   if (! file_exists(LOG_FILE) ) {
      $fh = fopen(LOG_FILE, 'w');
      fclose($fh);
      @chmod(LOG_FILE, 0666);
   }
   if (! is_writable(LOG_FILE) ) {
         echo '<tr valign=top>
    <td width=45%>Firewall Logfile</td>
    <td width=10% align=center><img src="static/icon_error.png" border=0 width=16 height=16 title="Warning !"></td>
    <td width=45%><font color=red>warning : ['. LOG_FILE .'] is not writable! Please chmod it to 0666 (rw-rw-rw-) and chmod the "logs/" directory to 0777 (rwxrwxrwx).</font></td>
    </tr>';
   }
   echo '</table>';

   if ($on != 2) {
      echo '<p><center><font color=red>Warning, you are at risk! Your site is not protected as long as the above problems aren\'t solved.</font></center>';
   }
   echo '</fieldset>';

   html_footer();
   exit;

}
/********************************************************************/
function menu_summary_stats($xtr) {

   html_header(0);

   $stat_log = LOG_FILE;
   if (preg_match('/^(\d{4})\d(\d\d)$/', $xtr, $match) ) {
      if (file_exists( dirname(__FILE__) . '/logs/firewall_'. $match[1] .'-'. $match[2] .'.log') ) {
			$stat_log =  dirname(__FILE__) . '/logs/firewall_'. $match[1] .'-'. $match[2] .'.log';
		}
	}

   if (! file_exists($stat_log) ) {
      echo '<br><table align=center width=80% cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;you do not have any log yet&nbsp;!</td></tr></table><p>';
      menu_summary_stats_combo($xtr);
      html_footer();
      exit;
   }

   $critical = $high = $medium = $slow = $benchmark =
   $tot_bench = $speed = $upload = $error = $banned_ip = 0;
   $fast = 1000;
   if ($fh = @fopen($stat_log, 'r') ) {
      while (! feof($fh) ) {
         $line = fgets($fh);
         if (preg_match('/^\[.+?(?:\s.\d{4})?\]\s+\[(.+?)\]\s+(?:\[.+?\]\s+){2}\[([1-6])\]/', $line, $match) ) {
            if ($match[2] == 1) {
					$medium++;
				} elseif ($match[2] == 2) {
					$high++;
				} elseif ($match[2] == 3) {
					$critical++;
				} elseif ($match[2] == 4) {
					$error++;
				} elseif ($match[2] == 5) {
					$upload++;
				} elseif ($match[2] == 6) {
					if (strpos($line, 'Banning IP') !== false) {
						$banned_ip++;
					}
				}
            if ($match[1] > $slow) {
               $slow = $match[1];
				}
            if ($match[1] < $fast) {
               $fast = $match[1];
				}
            $speed += $match[1];
            $tot_bench++;
         }
      }
      fclose($fh);
   } else {
      echo '<br><table align=center width=80% cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_warn.png" border=0 width=16 height=16 title="Warning !">&nbsp;<font color=red>unable to open ['. $stat_log .']!</font></td></tr></table><p>';
      menu_summary_stats_combo($xtr);
      html_footer();
      exit;
   }

   $total = $critical + $high + $medium;
   if ($total == 1) {$fast = $slow;}

	if ( (! $total) || (! $tot_bench) ) {
      echo '<br><table align=center width=80% cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;you do not have any log yet&nbsp;!</td></tr></table><p>';
      menu_summary_stats_combo($xtr);
      html_footer();
      exit;
   }

	$coef = 100 / $total;
	$critical = round($critical * $coef, 2);
	$high = round($high * $coef, 2);
	$medium = round($medium * $coef, 2);
	$speed = round($speed / $tot_bench, 4);

   $ret = menu_summary_stats_combo($xtr);
   if (! $ret) {
		$ret = date("M. Y");
	}

   echo '<br>
<fieldset><legend>&nbsp;<a href="javascript:toogle(\'cfirewall\');" class="links" title="Click to show/hide informations"><b>Firewall</b></a>&nbsp;</legend>
 <table id=cfirewall width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';
   if ( (isset($_COOKIE['cfirewall'])) && ($_COOKIE['cfirewall'])==1) echo ' style="display:none;"';
   echo '>
<tr>
 <td width=50%>Statistics period</td><td width=45% align=center>'. $ret .'</td><td width=5%>&nbsp;</td>
</tr>
<tr>
 <td width=50%>Total blocked hacking attempts</td><td width=45% align=center>'. $total .'</td><td width=5%>&nbsp;</td>
</tr>
<tr>
 <td width=50%>Hacking attempts severity</td><td width=45% align=center class=tinyblack>
Critical : '. $critical .'%<br>
  <table bgcolor="#c0c0c0" border="0" cellpadding="0" cellspacing="0" height="14" width="202" align="center" style="height:14px;">
   <tr>
    <td width="' . round($critical) .'%" background="static/bar-critical.png"></td><td width="'. round(100-$critical) .'%"></td>
   </tr>
  </table>
  <br>
High : '. $high .'%<br>
  <table bgcolor="#c0c0c0" border="0" cellpadding="0" cellspacing="0" height="14" width="202" align="center" style="height:14px;">
   <tr>
    <td width="' . round($high) .'%" background="static/bar-high.png"></td><td width="'. round(100-$high) .'%"></td>
   </tr>
  </table>
<br>
Medium : '. $medium .'%<br>
  <table bgcolor="#c0c0c0" border="0" cellpadding="0" cellspacing="0" height="14" width="202" align="center" style="height:14px;">
   <tr>
    <td width="'. round($medium) .'%" background="static/bar-medium.png"></td><td width="'. round(100-$medium) .'%"></td>
   </tr>
  </table>
 </td><td width=5%><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Statistics are taken from the current month firewall log.<br>Logs are rotated on the first day of the month. You can<br>view the logs by clicking on the <b>[Firewall / Logs]</b> menu.\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
</tr>
<tr>
 <td width=50%>Total uploaded files</td><td width=45% align=center>'. $upload .'</td><td width=5%>&nbsp;</td>
</tr>
<tr>
 <td width=50%>Total errors</td><td width=45% align=center>';
   if ($error) {
      echo '<font color=red>'. $error .'</font>';
   } else {
      echo $error;
	}
   echo '</td><td width=5%>&nbsp;</td>
</tr>
<tr>
 <td width=50%>Banned IPs</td><td width=45% align=center>'. $banned_ip .'</td><td width=5%>&nbsp;</td>
</tr>
</table>
</fieldset>
<p>
<fieldset><legend>&nbsp;<a href="javascript:toogle(\'cbenchmarks\');" class="links" title="Click to show/hide informations"><b>Benchmarks</b></a>&nbsp;</legend>
 <table id=cbenchmarks width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';

   if ( (isset($_COOKIE['cbenchmarks'])) && ($_COOKIE['cbenchmarks'])==1) echo ' style="display:none;"';

   echo '>
  <tr><td width=50%>Average time per request</td><td width=45% align=center>'. $speed .'s</td><td width=5%>&nbsp;</td></tr>
  <tr><td width=50%>Fastest request</td><td width=45% align=center>'. round($fast, 4) .'s</td><td width=5%><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Benchmarks show the time <b>NinjaFirewall</b> took, in seconds, to proceed<br>each request it has blocked. \',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td></tr>
  <tr><td width=50%>Slowest request</td><td width=45% align=center>'. round($slow, 4) .'s</td><td width=5%>&nbsp;</td></tr>
 </table>
</fieldset>';

   html_footer();

}
/********************************************************************/
function menu_summary_stats_combo($xtr) {

	$ret = '';
	$avail_logs = array();
   if (is_dir( dirname(__FILE__) . '/logs') ) {
      if ($dh = opendir( dirname(__FILE__) . '/logs' )) {
         while ( ($file = readdir($dh) ) !== false) {
            if (preg_match( '/^firewall_(\d{4})-(\d\d)\.log$/', $file, $match ) ) {
               $month = date('F', mktime(0, 0, 0, $match[2], 1, 2000) );
               $avail_logs[$match[1] . '0' . $match[2]] = $month . ' ' . $match[1];
            }
         }
      closedir($dh);
      } else { return; }
   } else {	return; }

   if ( count($avail_logs) < 2 ) { return; }

   krsort($avail_logs);
	echo '<br><center><form>
   <select class=input name=xtr onChange="document.location.href=\'?mid=11&token='. $_REQUEST['token'] .'&xtr=\'+this.value;">
   <option>Select monthly stats to view...</option>';
   foreach ($avail_logs as $dig => $txt) {
      echo '<option value="' . $dig . '"';
      if ($xtr == $dig) {
         echo ' selected';
         $ret = $txt;
      }
      echo '>' . $txt . '</option>';
   }
   echo '</select></form></center>';
   return $ret;

}
/********************************************************************/
function menu_account_license() {

   html_header(0);

   echo '<br>
<fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'license\');" class="links" title="Click to show/hide informations">Current License</a>&nbsp;</b></legend>
 <table id=license width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';
   if ( (isset($_COOKIE['license'])) && ($_COOKIE['license'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
  <tr><td valign=top width=45%>License Number</td><td width=10% align=center>&nbsp;</td><td width=45% align=center>N/A</td></tr>
  <tr><td valign=top width=45%>License Type</td><td width=10% align=center>&nbsp;</td><td width=45% align=center><b>Free Edition</b></td></tr>
  <tr><td valign=top width=45%>License Expiration date</td><td width=10% align=center>&nbsp;</td><td width=45% align=center>N/A</td></tr>
 </table>
</fieldset>
<br>
<br>
<fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'renewal\');" class="links" title="Click to show/hide informations">Upgrade</a>&nbsp;</b></legend>
  <table id=renewal width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';
   if ( (isset($_COOKIE['renewal'])) && ($_COOKIE['renewal'])==1)
      echo ' style="display:none;"';

   echo '>
   <tr>
   <td valign=middle width=45%>Need more power and options&nbsp;? Better security&nbsp;? Full support and one-click security rules updates&nbsp;?<p><a href="http://ninjafirewall.com/order.html" class=links style="border-bottom:1px dotted #FDCD25;" target=_blank>Click here to upgrade to the <b>Professional Edition</b>&nbsp;!</a></td>
    <td width=10% align=center>&nbsp;</td>
    <td width=45% align=center><a href="http://ninjafirewall.com/order.html" target=_blank title="Click here to upgrade to the Professional Edition"><img src="static/logopro.png" width=60 height=60 border=0></a></td>
   </tr>
   </table>
 </fieldset>';

   html_footer();

}
/********************************************************************/
function menu_account_options($err_msg) {

   html_header(0);

   global $dbadmin;

   if ($err_msg) {
      echo '<br><table align=center width=80% cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_warn.png" border=0 width=16 height=16 title="Warning !"><br><font color=red>'.$err_msg.'</font></td></tr></table>';
   } elseif ($GLOBALS['itm']) {
      echo '<br><table align=center width=300 cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_ok.png" border=0 width=16 height=16>&nbsp;configuration was saved !</td></tr></table>';
   }
   echo '<br>
<form method=post>
 <input type=hidden name=mid value=' . $GLOBALS['mid'] .'>
 <input type=hidden name=act value=1>
 <fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'cpass\');" class="links" title="Click to show/hide informations">Change login password</a>&nbsp;</b></legend>
 <table width=100% id=cpass class=smallblack';

   if ( (isset($_COOKIE['cpass'])) && ($_COOKIE['cpass'])==1) {
      echo ' style="display:none;"';
	}

   echo '><tr><td width=55%>
  Password length must be from 6 to 12 alphanumeric characters (a-z, 0-9). Uppercase and lowercase characters are accepted.
  </td><td width=45% align=right>
  Old password : <input type=password class=input size=13 maxlength=30 name=oldpass>
  <p>
  New password : <input type=password class=input size=13 maxlength=30 name=newpass1>
  <p>
  Verify new password : <input type=password class=input size=13 maxlength=30 name=newpass2>
 </td></tr></table>
 </fieldset>
 <p>
 <fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'ccontact\');" class="links" title="Click to show/hide informations">Contact</a>&nbsp;</b></legend>
  <table width=100% id=ccontact class=smallblack';

   if ( (isset($_COOKIE['ccontact'])) && ($_COOKIE['ccontact'])==1) {
      echo ' style="display:none;"';
	}

   echo '><tr><td width=55%>
   Enter your email address where NinjaFirewall will send you alerts and notifications
  </td><td width=45% align=right>
  Email : <input type=text class=input size=25 value="' . $dbadmin->email . '" name="cmail">
 </td></tr></table>
 </fieldset>
  <p>';

	$zonelist = array('UTC', 'Africa/Abidjan', 'Africa/Accra', 'Africa/Addis_Ababa', 'Africa/Algiers', 'Africa/Asmara', 'Africa/Asmera', 'Africa/Bamako', 'Africa/Bangui', 'Africa/Banjul', 'Africa/Bissau', 'Africa/Blantyre', 'Africa/Brazzaville', 'Africa/Bujumbura', 'Africa/Cairo', 'Africa/Casablanca', 'Africa/Ceuta', 'Africa/Conakry', 'Africa/Dakar', 'Africa/Dar_es_Salaam', 'Africa/Djibouti', 'Africa/Douala', 'Africa/El_Aaiun', 'Africa/Freetown', 'Africa/Gaborone', 'Africa/Harare', 'Africa/Johannesburg', 'Africa/Kampala', 'Africa/Khartoum', 'Africa/Kigali', 'Africa/Kinshasa', 'Africa/Lagos', 'Africa/Libreville', 'Africa/Lome', 'Africa/Luanda', 'Africa/Lubumbashi', 'Africa/Lusaka', 'Africa/Malabo', 'Africa/Maputo', 'Africa/Maseru', 'Africa/Mbabane', 'Africa/Mogadishu', 'Africa/Monrovia', 'Africa/Nairobi', 'Africa/Ndjamena', 'Africa/Niamey', 'Africa/Nouakchott', 'Africa/Ouagadougou', 'Africa/Porto-Novo', 'Africa/Sao_Tome', 'Africa/Timbuktu', 'Africa/Tripoli', 'Africa/Tunis', 'Africa/Windhoek', 'America/Adak', 'America/Anchorage', 'America/Anguilla', 'America/Antigua', 'America/Araguaina', 'America/Argentina/Buenos_Aires', 'America/Argentina/Catamarca', 'America/Argentina/ComodRivadavia', 'America/Argentina/Cordoba', 'America/Argentina/Jujuy', 'America/Argentina/La_Rioja', 'America/Argentina/Mendoza', 'America/Argentina/Rio_Gallegos', 'America/Argentina/Salta', 'America/Argentina/San_Juan', 'America/Argentina/San_Luis', 'America/Argentina/Tucuman', 'America/Argentina/Ushuaia', 'America/Aruba', 'America/Asuncion', 'America/Atikokan', 'America/Atka', 'America/Bahia', 'America/Barbados', 'America/Belem', 'America/Belize', 'America/Blanc-Sablon', 'America/Boa_Vista', 'America/Bogota', 'America/Boise', 'America/Buenos_Aires', 'America/Cambridge_Bay', 'America/Campo_Grande', 'America/Cancun', 'America/Caracas', 'America/Catamarca', 'America/Cayenne', 'America/Cayman', 'America/Chicago', 'America/Chihuahua', 'America/Coral_Harbour', 'America/Cordoba', 'America/Costa_Rica', 'America/Cuiaba', 'America/Curacao', 'America/Danmarkshavn', 'America/Dawson', 'America/Dawson_Creek', 'America/Denver', 'America/Detroit', 'America/Dominica', 'America/Edmonton', 'America/Eirunepe', 'America/El_Salvador', 'America/Ensenada', 'America/Fort_Wayne', 'America/Fortaleza', 'America/Glace_Bay', 'America/Godthab', 'America/Goose_Bay', 'America/Grand_Turk', 'America/Grenada', 'America/Guadeloupe', 'America/Guatemala', 'America/Guayaquil', 'America/Guyana', 'America/Halifax', 'America/Havana', 'America/Hermosillo', 'America/Indiana/Indianapolis', 'America/Indiana/Knox', 'America/Indiana/Marengo', 'America/Indiana/Petersburg', 'America/Indiana/Tell_City', 'America/Indiana/Vevay', 'America/Indiana/Vincennes', 'America/Indiana/Winamac', 'America/Indianapolis', 'America/Inuvik', 'America/Iqaluit', 'America/Jamaica', 'America/Jujuy', 'America/Juneau', 'America/Kentucky/Louisville', 'America/Kentucky/Monticello', 'America/Knox_IN', 'America/La_Paz', 'America/Lima', 'America/Los_Angeles', 'America/Louisville', 'America/Maceio', 'America/Managua', 'America/Manaus', 'America/Marigot', 'America/Martinique', 'America/Matamoros', 'America/Mazatlan', 'America/Mendoza', 'America/Menominee', 'America/Merida', 'America/Mexico_City', 'America/Miquelon', 'America/Moncton', 'America/Monterrey', 'America/Montevideo', 'America/Montreal', 'America/Montserrat', 'America/Nassau', 'America/New_York', 'America/Nipigon', 'America/Nome', 'America/Noronha', 'America/North_Dakota/Center', 'America/North_Dakota/New_Salem', 'America/Ojinaga', 'America/Panama', 'America/Pangnirtung', 'America/Paramaribo', 'America/Phoenix', 'America/Port-au-Prince', 'America/Port_of_Spain', 'America/Porto_Acre', 'America/Porto_Velho', 'America/Puerto_Rico', 'America/Rainy_River', 'America/Rankin_Inlet', 'America/Recife', 'America/Regina', 'America/Resolute', 'America/Rio_Branco', 'America/Rosario', 'America/Santa_Isabel', 'America/Santarem', 'America/Santiago', 'America/Santo_Domingo', 'America/Sao_Paulo', 'America/Scoresbysund', 'America/Shiprock', 'America/St_Barthelemy', 'America/St_Johns', 'America/St_Kitts', 'America/St_Lucia', 'America/St_Thomas', 'America/St_Vincent', 'America/Swift_Current', 'America/Tegucigalpa', 'America/Thule', 'America/Thunder_Bay', 'America/Tijuana', 'America/Toronto', 'America/Tortola', 'America/Vancouver', 'America/Virgin', 'America/Whitehorse', 'America/Winnipeg', 'America/Yakutat', 'America/Yellowknife', 'Arctic/Longyearbyen', 'Asia/Aden', 'Asia/Almaty', 'Asia/Amman', 'Asia/Anadyr', 'Asia/Aqtau', 'Asia/Aqtobe', 'Asia/Ashgabat', 'Asia/Ashkhabad', 'Asia/Baghdad', 'Asia/Bahrain', 'Asia/Baku', 'Asia/Bangkok', 'Asia/Beirut', 'Asia/Bishkek', 'Asia/Brunei', 'Asia/Calcutta', 'Asia/Choibalsan', 'Asia/Chongqing', 'Asia/Chungking', 'Asia/Colombo', 'Asia/Dacca', 'Asia/Damascus', 'Asia/Dhaka', 'Asia/Dili', 'Asia/Dubai', 'Asia/Dushanbe', 'Asia/Gaza', 'Asia/Harbin', 'Asia/Ho_Chi_Minh', 'Asia/Hong_Kong', 'Asia/Hovd', 'Asia/Irkutsk', 'Asia/Istanbul', 'Asia/Jakarta', 'Asia/Jayapura', 'Asia/Jerusalem', 'Asia/Kabul', 'Asia/Kamchatka', 'Asia/Karachi', 'Asia/Kashgar', 'Asia/Kathmandu', 'Asia/Katmandu', 'Asia/Kolkata', 'Asia/Krasnoyarsk', 'Asia/Kuala_Lumpur', 'Asia/Kuching', 'Asia/Kuwait', 'Asia/Macao', 'Asia/Macau', 'Asia/Magadan', 'Asia/Makassar', 'Asia/Manila', 'Asia/Muscat', 'Asia/Nicosia', 'Asia/Novokuznetsk', 'Asia/Novosibirsk', 'Asia/Omsk', 'Asia/Oral', 'Asia/Phnom_Penh', 'Asia/Pontianak', 'Asia/Pyongyang', 'Asia/Qatar', 'Asia/Qyzylorda', 'Asia/Rangoon', 'Asia/Riyadh', 'Asia/Saigon', 'Asia/Sakhalin', 'Asia/Samarkand', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Asia/Taipei', 'Asia/Tashkent', 'Asia/Tbilisi', 'Asia/Tehran', 'Asia/Tel_Aviv', 'Asia/Thimbu', 'Asia/Thimphu', 'Asia/Tokyo', 'Asia/Ujung_Pandang', 'Asia/Ulaanbaatar', 'Asia/Ulan_Bator', 'Asia/Urumqi', 'Asia/Vientiane', 'Asia/Vladivostok', 'Asia/Yakutsk', 'Asia/Yekaterinburg', 'Asia/Yerevan', 'Atlantic/Azores', 'Atlantic/Bermuda', 'Atlantic/Canary', 'Atlantic/Cape_Verde', 'Atlantic/Faeroe', 'Atlantic/Faroe', 'Atlantic/Jan_Mayen', 'Atlantic/Madeira', 'Atlantic/Reykjavik', 'Atlantic/South_Georgia', 'Atlantic/St_Helena', 'Atlantic/Stanley', 'Australia/ACT', 'Australia/Adelaide', 'Australia/Brisbane', 'Australia/Broken_Hill', 'Australia/Canberra', 'Australia/Currie', 'Australia/Darwin', 'Australia/Eucla', 'Australia/Hobart', 'Australia/LHI', 'Australia/Lindeman', 'Australia/Lord_Howe', 'Australia/Melbourne', 'Australia/NSW', 'Australia/North', 'Australia/Perth', 'Australia/Queensland', 'Australia/South', 'Australia/Sydney', 'Australia/Tasmania', 'Australia/Victoria', 'Australia/West', 'Australia/Yancowinna', 'Europe/Amsterdam', 'Europe/Andorra', 'Europe/Athens', 'Europe/Belfast', 'Europe/Belgrade', 'Europe/Berlin', 'Europe/Bratislava', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest', 'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar', 'Europe/Guernsey', 'Europe/Helsinki', 'Europe/Isle_of_Man', 'Europe/Istanbul', 'Europe/Jersey', 'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/Ljubljana', 'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid', 'Europe/Malta', 'Europe/Mariehamn', 'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Nicosia', 'Europe/Oslo', 'Europe/Paris', 'Europe/Podgorica', 'Europe/Prague', 'Europe/Riga', 'Europe/Rome', 'Europe/Samara', 'Europe/San_Marino', 'Europe/Sarajevo', 'Europe/Simferopol', 'Europe/Skopje', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane', 'Europe/Tiraspol', 'Europe/Uzhgorod', 'Europe/Vaduz', 'Europe/Vatican', 'Europe/Vienna', 'Europe/Vilnius', 'Europe/Volgograd', 'Europe/Warsaw', 'Europe/Zagreb', 'Europe/Zaporozhye', 'Europe/Zurich', 'Indian/Antananarivo', 'Indian/Chagos', 'Indian/Christmas', 'Indian/Cocos', 'Indian/Comoro', 'Indian/Kerguelen', 'Indian/Mahe', 'Indian/Maldives', 'Indian/Mauritius', 'Indian/Mayotte', 'Indian/Reunion', 'Pacific/Apia', 'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Easter', 'Pacific/Efate', 'Pacific/Enderbury', 'Pacific/Fakaofo', 'Pacific/Fiji', 'Pacific/Funafuti', 'Pacific/Galapagos', 'Pacific/Gambier', 'Pacific/Guadalcanal', 'Pacific/Guam', 'Pacific/Honolulu', 'Pacific/Johnston', 'Pacific/Kiritimati', 'Pacific/Kosrae', 'Pacific/Kwajalein', 'Pacific/Majuro', 'Pacific/Marquesas', 'Pacific/Midway', 'Pacific/Nauru', 'Pacific/Niue', 'Pacific/Norfolk', 'Pacific/Noumea', 'Pacific/Pago_Pago', 'Pacific/Palau', 'Pacific/Pitcairn', 'Pacific/Ponape', 'Pacific/Port_Moresby', 'Pacific/Rarotonga', 'Pacific/Saipan', 'Pacific/Samoa', 'Pacific/Tahiti', 'Pacific/Tarawa', 'Pacific/Tongatapu', 'Pacific/Truk', 'Pacific/Wake', 'Pacific/Wallis', 'Pacific/Yap');

	$current_tz = date_default_timezone_get();

	echo '<p>
 <fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'ctz\');" class="links" title="Click to show/hide informations">Timezone</a>&nbsp;</b></legend>
  <table width=100% id=ctz class=smallblack';
   if ( (isset($_COOKIE['ctz'])) && ($_COOKIE['ctz'])==1) {
      echo ' style="display:none;"';
	}
   echo '><tr><td width=55%>Display dates and times using the following timezone</td><td width=45% align=right><select name=tz class=input>';
	foreach ($zonelist as $tz_place) {
		echo '<option value ="'. $tz_place .'"';
		if ($current_tz == $tz_place) { echo ' selected'; }
		@date_default_timezone_set($tz_place);
		echo '>'. $tz_place .' (' .date('O'). ')</option>';
	}
	echo '</select></td></tr>
	</table>
 </fieldset>
  <p>

 <fieldset><legend><b>&nbsp;<a href="javascript:toogle(\'calert\');" class="links" title="Click to show/hide informations">Login security</a>&nbsp;</b></legend>
  <table width=100% id=calert class=smallblack';

   if ( (isset($_COOKIE['calert'])) && ($_COOKIE['calert'])==1) {
      echo ' style="display:none;"';
   }

   echo '><tr><td width=55%><br>
  Send me an email whenever someone logs in to my NinjaFirewall admin account<br>&nbsp;
  </td><td width=45% align=right>
  <input type=checkbox name="lgalert"';
   if ($dbadmin->login_alert) {
      echo ' checked>';
   } else {
      echo '>';
	}

   echo '</td></tr><tr><td width=55% style="border-top:1px dotted #666666;"><br>
  Always log in using a secure connection (HTTPS)<br>&nbsp;
  </td><td width=45% align=right style="border-top:1px dotted #666666;">
  <input type=checkbox name="lgssl"';
   if ($dbadmin->login_ssl) {
      echo ' checked>';
   } else {
      echo '>';
	}

   echo '</td></tr></table></fieldset>
 <br><br>
 <center><input type=submit class=button value="Apply Changes"></center>
</form>';

   html_footer();
   exit;

}
/********************************************************************/
function menu_account_options_save() {

   global $dbh;
   global $db_name;
   global $dbadmin;
   global $db_prefix;

	$error = '';
   $oldpass = $_POST['oldpass'];
   $newpass1 = $_POST['newpass1'];
   $newpass2 = $_POST['newpass2'];

   if ( ($oldpass) || ($newpass2) || ($newpass1) ) {
		if ( (! $oldpass ) || (! $newpass2 ) || (! $newpass1 ) ) {
			$error.= "- if you want to change your login password, you must enter your current password and twice your new password. Otherwise leave those fields blank.<br>";
		} else if ( $newpass2 !== $newpass1 ) {
			$error.= "- the 2 new password fields do not match<br>";
		} else if ( $newpass2 === $newpass1 ) {
			$encoded = sha1 ($oldpass);
			if ( $encoded !== $dbadmin->password ) {
				$error.= "- old password is not correct<br>";
			}	else if ( preg_match ('/\s/', $newpass1) ) {
				$error.= "- the new password must not contain any space character<br>";
			} else if (! preg_match('/^[a-zA-Z0-9]{6,12}$/', $newpass1) ) {
				$error.= "- the new password length must be from 6 to 12 alphanumeric characters";
			} else {
				$password = sha1($newpass1);
			}
		}
	}

   if (! preg_match('/^[a-z0-9\.\-_]+\@[a-z0-9\.\-_]+\.[a-z]{2,4}$/i', $_POST['cmail']) ) {
      $error.= "- email address for contact / alerts is not correct<br>";
	}

	$tz = @$_POST['tz'];
	if (! @date_default_timezone_set($tz)) {
		$tz = 'UTC';
	}
	if (! is_writable( dirname(__FILE__) . '/config.php' ) ) {
		$error.= '- [config.php] is not writable, your timezone cannot be saved&nbsp;!';
	} else {
		if ( $tmp_array = file( dirname(__FILE__) . '/config.php', FILE_SKIP_EMPTY_LINES) ) {
			$fh = fopen( dirname(__FILE__) . '/config.php', 'w');
			foreach ( $tmp_array as $line ) {
				if (preg_match('/^\@date_default_timezone_set/', $line) ) {
					@fwrite( $fh, '@date_default_timezone_set(\'' . $tz . "');\n" );
				}else {
					@fwrite( $fh, $line );
				}
			}
			fclose( $fh );
		}
	}

	if ( (isset($_POST['lgalert'])) && ($_POST['lgalert']  == 'on') ) {
		$login_alert = 1;
	} else {
		$login_alert = 0;
	}
	if ( (isset($_POST['lgssl'])) && ($_POST['lgssl']  == 'on') ) {
		$login_ssl = 1;
	} else {
		$login_ssl = 0;
	}

   if ( (isset($error)) && ($error) ) {
      menu_account_options($error);
      exit;
   }

   $sql = "UPDATE `$db_name`.`". $db_prefix ."nf_admin` SET ";
   if ( (isset($password)) && ($password) ) { $sql.= "`password` = '$password',"; }
   $sql.="`email` = '".$_POST['cmail']."', `login_alert` = '$login_alert',`login_ssl` = '$login_ssl' WHERE `". $db_prefix ."nf_admin`.`t_id` = 'admin'";

   $dbh->query($sql);

   header('Location: index.php?mid=21&itm=1&token=' . $_REQUEST['token']);
   exit;

}
/********************************************************************/
function menu_account_update() {

	global $dbnf;

	html_header(0);

	echo '<script>function updnff(){alert("Error : this option is not available in the Free Edition of NinjaFirewall.\nUpgrade to the Pro Edition to benefit from great additional features and free security updates.");return false;}function updeng(){alert("Error : please download the new engine version from NinjaFirewall.com first.");return false;}</script>';

	$free = '<p><img src=\\\'static/iconpro.png\\\' width=16 height=16>&nbsp;<i class=smallred>this option is available in the Pro version only</i>';

	echo '<br>
<fieldset><legend>&nbsp;<a href="javascript:toogle(\'cengine\');" class="links" title="Click to show/hide informations"><b>Engine</b></a>&nbsp;</legend>
  <table id=cengine border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cengine'])) && ($_COOKIE['cengine'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr>
    <td width=45%>Your current engine version : '. VERSION. '<br></td>
    <td width=45% align=right>';
	if (! preg_match('/^\d{1,2}\.\d\.\d$/', $_SESSION['vapp']) ) {
		$_SESSION['vapp'] = VERSION;
	}

	if ( version_compare( VERSION, $_SESSION['vapp'], '<' ) ) {
		echo '<font color=red>A newer version is available&nbsp;: v'. $_SESSION['vapp']. '</font><br>Please <a target=_blank href="http://ninjafirewall.com/download.html" class=links style="border-bottom:1px dotted #ffd821;">download it from NinjaFirewall.com</a>';
		$text = 'A newer version [v'. $_SESSION['vapp'] .'] is available for download.<br>Keep your <b>NinjaFirewall</b> up to date for best security.';
	} else {
		echo '<font color=green>Up to date !</font>';
		$text = 'Your <b>NinjaFirewall</b> engine is up to date.';
	}

   echo '</td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\''.$text.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
  </table>
 </fieldset>';

	echo'<br>
 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'crules\');" class="links" title="Click to show/hide informations"><b>Security Rules</b></a>&nbsp;</legend>
  <table id=crules border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['crules'])) && ($_COOKIE['crules'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr>
    <td width=45%>Your current rules version : '. $dbnf->version_rules. '<br></td>
    <td width=45% align=right>';
	if (! preg_match('/^\d{2}\.\d{2}\.\d{2}$/', $_SESSION['vrules']) ) {
		$_SESSION['vrules'] = $dbnf->version_rules;
	}
    if ( $_SESSION['vrules'] == $dbnf->version_rules ) {
		echo '<font color=green>Up to date !</font>';
		$text = 'Your <b>NinjaFirewall</b> security rules are up to date.';
	} else {
		echo '<font color=red>New security rules are available&nbsp;: v'. $_SESSION['vrules']. '</font>
		<p>
		<input type=button class=button value="Download and update rules" onclick="';
		if ( version_compare( VERSION, $_SESSION['vapp'], '<' ) ) {
			echo 'updeng()";>';
		} else {
			echo 'updnff()";>';
		}
		$text = 'New security rules are available for download.<br>Clicking on this button will automatically download<br>and install them, without you having to do anything.<p>Keep your <b>NinjaFirewall</b> up to date for best security!<p><img src=\\\'static/iconpro.png\\\' width=16 height=16>&nbsp;<i class=smallred>this option is available in the Pro version only</i>';
	}
   echo '</td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\''. $text .'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

  </table>
 </fieldset>';

	html_footer();

}
/********************************************************************/
function menu_firewall_options() {

   html_header(0);

   global $dbh;
   global $db_prefix;

   $dbqh = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_options`');
   if (! $dboptions  = $dbqh->fetch_object() ) {
      die('error (line ' . __LINE__ . ') : mysql_fetch_object error');
	}

	if ($GLOBALS['itm']) {
      echo '<br><table align=center width=300 cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_ok.png" border=0 width=16 height=16>&nbsp;configuration was saved !</td></tr></table>';
	}

	$free = '<p><img src=\\\'static/iconpro.png\\\' width=16 height=16>&nbsp;<i class=smallred>this option can be changed in the Pro version only</i>';

   echo '<br>
<form method=post>
<input type=hidden name=mid value='. $GLOBALS['mid'] . '>
<input type=hidden name=act value=1>
 <fieldset>
   <legend>&nbsp;<a href="javascript:toogle(\'coptions\');" class="links" title="Click to show/hide informations"><b>Options</b></a>&nbsp;</legend>
  <table id=coptions border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';

   if ( (isset($_COOKIE['coptions'])) && ($_COOKIE['coptions'])==1) { echo ' style="display:none;"';}

   echo '>
   <tr>
    <td width=45%>Firewall protection<br>&nbsp;</td>
    <td width=45% align=right>';
   if (! $dboptions->enabled) echo '<img src="static/icon_error.png" border=0 width=16 height=16 title="Warning, you are at risk : your firewall is disabled !">&nbsp;';
   echo '<select class=input name=protection style=\'width:180px;\'>';
   echo '<option value="on"';
   if ($dboptions->enabled) echo ' selected';
   echo ' style="color:green;">Enabled</option><option value="off"';
   if (! $dboptions->enabled) echo ' selected';
   echo ' style="color:red;">Disabled</option>';
   echo '</select><br>&nbsp;
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option allows you to disable <b>NinjaFirewall</b>.<br>Your site will remain unprotected until you enable it again.\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;"><br>HTTP error code to return<br>&nbsp;</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
    <select class=input style="width:180px;">
    <option disabled>400 Bad Request</option>
    <option selected>403 Forbidden</option>
    <option disabled>404 Not Found</option>
    <option disabled>406 Not Acceptable</option>
    <option disabled>500 Internal Server Error</option>
    <option disabled>503 Service Unavailable</option>
    </select><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><br><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'When blocking a dangerous request, <b>NinjaFirewall</b> will return<br>a HTTP error code. By default, it is 403 (Forbidden).<br>Use this menu if you want to change it.'. $free .'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>

  <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;"><br>Stealth mode<br>&nbsp;</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
    <select class=input style="width:180px;">
    <option disabled>Yes</option>
    <option selected>No</option>
    </select><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><br><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'<b>NinjaFirewall</b> will always return an error message including<br>an unique identifier to explain why the request was blocked.<br>That way, if a legitimate user was blocked by mistake, he would<br>know what happened and could even report the problem to you.<br>In <i>Stealth Mode</i> no message will be returned and there is<br>no way for the user to know why he was blocked. Note that this<br>option does not affect the previous one (HTTP error code).'. $free .'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Ban offending IP</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
    <select class=input name=blockip style="width:180px;">
    <option selected>Never ban IPs</option>
    <option disabled>Only if critical severity</option>
    <option disabled>If critical or high severity</option>
    <option disabled>Always ban offending IPs</option>
    </select>
   <p>
   Ban IPs for <input type=text class=input size=2 style="background-color:#D6CEC3;" disabled value="0">&nbsp;minutes
   <br>&nbsp;</td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'In addition to reject the request, <b>NinjaFirewall</b> can also ban<br>the offending IP depending on the level of the severity.<br>If you decide to ban IPs, use the submenu to select the time<br>that IPs will be banned (from 1 to max 99 minutes).'. $free .'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr>
    <td width=45% style="border-top:1px dotted #666666;"><br>Debug mode<br>&nbsp;</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>';
   if ($dboptions->debug) echo '<img src="static/icon_warn.png" border=0 width=16 height=16 title="Warning, Debug mode is enabled !">&nbsp;';
   echo '<select class=input name=debug style=\'width:180px;\'>';
   echo '<option value=0';
   if (! $dboptions->debug) echo ' selected';
   echo '>No (default)</option><option value=1';
   if ($dboptions->debug == 1) echo ' selected';
   echo '>Level 1 (HTML source)</option><option value=2';
   if ($dboptions->debug ==2) echo ' selected';
   echo '>Level 2 (screen)</option>';
   echo '</select><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><br><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'You can run <b>NinjaFirewall</b> in <i>Debug Mode</i> before enabling it on<br>a production server. Do not forget to disable it when going live.<br>Two different levels are available :<br><b>- Level 1 (HTML source) :</b> debugging lines are appended to your<br>HTML code. Use your browser `View Source` menu to see them.<br><b>- Level 2 (screen) :</b> debugging lines are output to the screen.<p>In either case, the text will be located at the bottom of the page.\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>
 </table>
 </fieldset>
 <br><center><input type=submit class=button value="Save changes"></center>
</form>';

   html_footer();

}
/********************************************************************/
function menu_firewall_options_save() {

   global $dbh;
   global $db_name;
   global $db_prefix;

	if ( $_POST['protection']  == 'off') {
		$enabled = 0;
	} else {
		$enabled = 1;
	}

   if ( $_POST['debug']  == 1 )     { $debug = '1'; }
   elseif ( $_POST['debug']  == 2 ) { $debug = '2'; }
   else { $debug = '0'; }

   $sql = "UPDATE `".$db_name."`.`". $db_prefix ."nf_options` SET `enabled` = '$enabled', `debug` = '$debug' WHERE `". $db_prefix ."nf_options`.`t_id` = 'options'";
   $dbh->query($sql);

   header('Location: index.php?mid=30&itm=1&token='. $_REQUEST['token']);
   exit;

}
/********************************************************************/
function menu_firewall_policies() {

	html_header(0);

   global $dbh;
   global $db_prefix;

	$free = '<p><img src=\\\'static/iconpro.png\\\' width=16 height=16>&nbsp;<i class=smallred>this option can be changed in the Pro version only</i>';

   $dbqh = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_options`');
   if (! $dboptions  = $dbqh->fetch_object() ) {
      die('error (line ' . __LINE__ . ') : fetch_object error');
	}

   if ($GLOBALS['itm']) {
      echo '<br><table align=center width=300 cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_ok.png" border=0 width=16 height=16>&nbsp;configuration was saved&nbsp;!</td></tr></table>';
	}

   echo '<script>function restore() {if (confirm("All fields will be restored to their default values.\nGo ahead ?")){return true;}else{return false;}}function vm(){if (document.fwrules.virtuemart.checked){if (!document.fwrules.joomla.checked){alert("Do not forget to select Joomla as well to protect your site.");document.fwrules.joomla.checked=true;}}}</script>
<br>
<form method=post name=fwrules><input type=hidden name=mid value='. $GLOBALS['mid'] . '><input type=hidden name=act value=1>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cupload\');" class="links" title="Click to show/hide informations"><b>File uploads</b></a>&nbsp;</legend>
  <table id=cupload border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cupload'])) && ($_COOKIE['cupload'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Whether or not to allow file uploads</td>
    <td width=45% align=right>
     <select class=input name=upload><option disabled>Allow</option><option selected>Disallow</option><option disabled>Block scripts, ELF and system files</option></select><p>Sanitise filenames&nbsp;<input type=checkbox disabled>
   </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Select whether you want to block file uploads or not.<br>You can <b>allow</b> or <b>disallow</b> uploads, or <b>block</b> scripts (PHP, CGI, Ruby,<br>bash), Linux binary files (ELF) and system files (.htaccess, .htpasswd<br>and php.ini).<p>If you allow uploads, you have the possibility to <b>sanitise</b> filenames&nbsp:<br>- any character that is not a letter [a-zA-Z], a digit [0-9], a dot [.],<br>a hyphen [-] or an underscore [_] will be removed from the filename.<br>- filenames will be truncated if they exceed 100 characters.<br>The name of each uploaded file will be written to the firewall log.<p>By default, uploads are not allowed.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
  </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cinstall\');" class="links" title="Click to show/hide informations"><b>Installation/Configuration</b></a>&nbsp;</legend>
  <table id=cinstall border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cinstall'])) && ($_COOKIE['cinstall'])==1) {
      echo ' style="display:none;"';
	}
	echo '>
   <tr style="color:#999999;">
    <td width=45%>Whether or not to block access to installation scripts</td>
    <td width=45% align=right>
     <input type=radio disabled>&nbsp;Block&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;Don\'t block<br>&nbsp;
   </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'<b>NinjaFirewall</b> can block the execution of most <b>installation scripts</b>.<br>If you wanted to install or upgrade a PHP application, a plugin or a module,<br>you would probably need to disable this option (or to turn NinjaFirewall off)<br>during its installation in order not to get blocked.<br>Installation scripts are not blocked by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>
   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Whether or not to block access to configuration scripts</td>
    <td width=45% align=right style="border-top:1px dotted #666666;">
     <input type=radio disabled>&nbsp;Block&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;Don\'t block<br>&nbsp;
   </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'<b>NinjaFirewall</b> can block the execution of most <b>configuration scripts</b>.<br>If you wanted to configure a PHP application, you would probably need<br>to disable this option (or to turn NinjaFirewall off) during its configuration<br>in order not to get blocked.<br>Configuration scripts are not blocked by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a><br>&nbsp;</td>
   </tr>
  </table>
 </fieldset>
 <br>
 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'capps\');" class="links" title="Click to show/hide informations"><b>Applications</b></a>&nbsp;</legend>
  <table id=capps border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['capps'])) && ($_COOKIE['capps'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr>
    <td width=45%>Application-specific protections</td>
    <td width=45%>
    <table width=100% class=smallblack>
     <tr>
      <td width=50% align=right>
       <label>OSCommerce <input type="checkbox" name="oscommerce"';
      if ( strpos($dboptions->application, 'oscommerce') !== false ) {
         echo ' checked';
		}
		echo '></label><br>
       <label>Magento <input type="checkbox" name="magento"';
       if ( strpos($dboptions->application, 'magento') !== false ) {
         echo ' checked';
		}
		echo '></label><br>
       <label>WordPress <input type="checkbox" name="wordpress"';
		if ( strpos($dboptions->application, 'wordpress') !== false ) {
         echo ' checked';
		}
		echo '></label><br>
       <label>CS-Cart <input type="checkbox" name="cscart"';
		if ( strpos($dboptions->application, 'cscart') !== false ) {
         echo ' checked';
		}
		echo '></label>
      </td>
      <td width=50% align=right>
       <label>PrestaShop <input type="checkbox" name="prestashop"';
		if ( strpos($dboptions->application, 'prestashop') !== false ) {
         echo ' checked';
		}
		echo '></label><br>
       <label>Drupal <input type="checkbox" name="drupal"';
       if ( strpos($dboptions->application, 'drupal') !== false ) {
         echo ' checked';
      }
		echo '></label><br>
       <label>Joomla <input type="checkbox" name="joomla"';
		if ( strpos($dboptions->application, 'joomla') !== false ) {
         echo ' checked';
		}
		echo '></label><br>
       <label>Virtuemart <input type="checkbox" name="virtuemart"';
		if ( strpos($dboptions->application, 'virtuemart') !== false ) {
			echo ' checked';
		}
		echo ' onChange="vm();"></label>
      </td>
     </tr>
    </table>
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'If you are using <b>NinjaFirewall</b> with one of those applications,<br>check its corresponding box so that some specific rules will be used<br>to protect it/them against known vulnerabilities.<br>Do not activate protection for applications you do not use as it could<br>have some bad side effects. Note that [OSCommerce] can be selected if<br>you are using [CRE Loaded], [Zen Cart] or any of its derivatives/forks.\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
  </table>
 </fieldset>
 <br>
 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'csql\');" class="links" title="Click to show/hide informations"><b>MySQL</b></a>&nbsp;</legend>
  <table id=csql border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';

   if ( (isset($_COOKIE['csql'])) && ($_COOKIE['csql'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Block localhost IP in GET/POST requests</td>
    <td width=45% align=right><br>
      <input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option will block any <b>GET</b> or <b>POST</b> request containing the <b>localhost IP</b>.<br>It can be useful to block SQL dumpers and various hacker\\\'s shell scripts<br>but it could block some installation or configuration programs (ie: Joomla,<br>Wordpress). It is disabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
     <td width=45% style="border-top:1px dotted #666666;">Escape single quote [\'], double quotes ["], backslash [\], line feed [\\n], carriage return [\\r] and backtick [`] to prevent SQL injections<br>
    </td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>&nbsp;
   Sanitise GET requests&nbsp;<input type=checkbox checked disabled><br>
   Sanitise POST requests&nbsp;<input type=checkbox disabled><br>
   Sanitise COOKIE requests&nbsp;<input type=checkbox checked disabled><br>
   Sanitise HTTP_USER_AGENT requests&nbsp;<input type=checkbox checked disabled><br>
   Sanitise HTTP_REFERER requests&nbsp;<input type=checkbox checked disabled><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option will not block but sanitise* users input by escaping those<br>characters that are often used to perform code or SQL injections.<br>This action will be performed when the filtering process is over,<br> right before <b>NinjaFirewall</b> gives the control back to your PHP script.<br>You can choose to sanitise <b>GET</b> requests, <b>POST</b> request, <b>COOKIE</b>,<br><b>HTTP_USER_AGENT</b> and <b>HTTP_REFERER</b>.<br>Note that if you enabled <b>POST</b> requests sanitising and you needed<br>to edit some texts or articles from your blog or e-commerce shop,<br>you could corrupt them with excessive backslashes.<p>*except leading quotes (single or double) in a GET/POST value,<br>line feeds and carriage returns in cookies, referrer and user-agent<br>variables which will always be blocked.'.$free.' \',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
  </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'ccookie\');" class="links" title="Click to show/hide informations"><b>Cookies</b></a>&nbsp;</legend>
  <table id=ccookie border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';

   if ( (isset($_COOKIE['ccookie'])) && ($_COOKIE['ccookie'])==1) {
      echo ' style="display:none;"';
	}

   echo '>
   <tr style="color:#999999;">
    <td width=45%>Scan cookies for code injection and cross-site scripting (XSS)</td>
    <td width=45% align=right><br>
      <input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'It is possible for an attacker to hide some nasty code inside <b>cookies</b><br>in order to hack into the site or its database.<br>This protection will block such attempts and is enabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Reject visitors without cookie(s)</td>
    <td width=45% style="border-top:1px dotted #666666;" align=right><br>
    <input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option will reject any visitor calling a PHP file if they do not have<br><b>one or more cookies</b> from your site.<br>It is turned off by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
  </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cbrowser\');" class="links" title="Click to show/hide informations"><b>Browsers</b></a>&nbsp;</legend>
  <table id=cbrowser border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cbrowser'])) && ($_COOKIE['cbrowser'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Scan HTTP_USER_AGENT field for code injection and cross-site scripting (XSS)</td>
    <td width=45% align=right>
    <input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'It is possible for an attacker to inject PHP or JavaScript code inside<br>the <b>HTTP_USER_AGENT</b> field (browser name) in order to hack into<br>a site or its database.<br>This protection will block such attempts and is enabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Scan HTTP_REFERER field for code injection and cross-site scripting (XSS)</td>
    <td width=45% align=right style="border-top:1px dotted #666666;">
    <input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'It is possible for an attacker to inject PHP or JavaScript code inside<br>the <b>HTTP_REFERER</b> field sent by the browser in order to hack into<br>a site or its database.<br>This protection will block such attempts and is enabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cssl\');" class="links" title="Click to show/hide informations"><b>HTTPS / SSL</b></a>&nbsp;</legend>
  <table id=cssl border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cssl'])) && ($_COOKIE['cssl'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Scan HTTPS/SSL traffic</td>
    <td width=45% align=right><br>
    <input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'By default, <b>HTTPS traffic</b> is scanned and protected just like<br>HTTP traffic is.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Force HTTPS/SSL connections</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
    <input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Use this option if you want to force secure connections by always<br>redirecting your visitors to the <b>HTTPS</b> section of your site. It is<br>disabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cwlist\');" class="links" title="Click to show/hide informations"><b>Whitelisted IPs</b></a>&nbsp;</legend>
  <table id=cwlist border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';

   if ( (isset($_COOKIE['cwlist'])) && ($_COOKIE['cwlist'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Do not filter requests coming from those IPs</td>
    <td width=45% align=center><br>
    <font class=tinygrey>IPv4 comma-separated list :</font><br>
    <input class=input type=text value="127.0.0.1, ' . $_SERVER['SERVER_ADDR'] .'" style="width:100%;color:#999999;" disabled><p>Include private IP address spaces&nbsp;<input type=checkbox disabled>
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This options lets you whitelist IPs that you do not want to filter<br>so that <b>NinjaFirewall</b> will never attempt to block them.<br>You can use it to whitelist IPs of payment notification sytems like<br>Paypal, search engine crawlers like Googlebot or even yourself.<br>The list must be comma-separated (,) and you can add either<br>a full IP or any part/substring of it :<br>- Full IP : <b>1.2.3.4</b> will only match IP <b>1.2.3.4</b><br>- Substring : <b>1.2.3.</b> : will match any IP starting with <b>1.2.3.</b>xxx<br>A substring must end with a \\\'.\\\' dot (eg \\\'1<font color=red><b>.</b></font>\\\', \\\'1.2<font color=red><b>.</b></font>\\\', \\\'1.2.3<font color=red><b>.</b></font>\\\') or it<br>will be ignored.<p><b>Include private IP address spaces</b> option will whitelist all<br>non-routable private IPs (10.0.0.0 to 10.255.255.255, 172.16.0.0<br>to 172.31.255.255 and 192.168.0.0 to 192.168.255.255).<br><p>Note: the firewall will look for both IPv4 and IPv4-mapped IPv6<br>notations (eg \\\'192.0.2.128\\\' will also match \\\'::ffff:192.0.2.128\\\').<br>IPv6 addresses and subnets are not supported.<p>By default, the whitelist includes the localhost (127.0.0.1) and<br> you server IP ('. $_SERVER['SERVER_ADDR'] .').'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cphp\');" class="links" title="Click to show/hide informations"><b>PHP</b></a>&nbsp;</legend>
  <table id=cphp border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';
   if ( (isset($_COOKIE['cphp'])) && ($_COOKIE['cphp'])==1) {
      echo ' style="display:none;"';
	}
   echo '>
   <tr style="color:#999999;">
    <td width=45%>Block PHP built-in wrappers<br>
    </td>
    <td width=45% align=right><br>&nbsp;
   <input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'PHP has several wrappers for use with the filesystem functions.<br>It is possible for an attacker to use them to bypass firewalls and<br>various IDS to exploit remote and local file inclusions.<br>This option lets you block any script attempting to pass a <b>php://</b><br>or a <b>data://</b> stream inside a GET or POST request, cookies, user<br>agent and referrer variables.<br>By default, PHP built-in wrappers are blocked.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Whether PHP errors should be hidden from the user or not</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br><input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;</td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option lets you <b>hide errors</b> returned by your scripts.<br>Such errors can leak sensitive informations which can be<br>exploited by hackers. By default, errors are not hidden.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Sanitise PHP_SELF, PATH_TRANSLATED and PATH_INFO</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br><input type=radio checked disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio disabled>&nbsp;No<br>&nbsp;</td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option can <b>sanitise</b> any dangerous characters found in<br> the <b>PHP_SELF</b>, <b>PATH_TRANSLATED</b> and the <b>PATH_INFO</b><br>server variables to prevent various XSS and database<br>injection attempts. Those characters are [&lt;\\\'&gt;`&#34; 0x0a 0x0d].<br>Characters found will be removed from the request.<br>By default, this option is enabled.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   </table>
 </fieldset>

 <br>

 <fieldset><legend>&nbsp;<a href="javascript:toogle(\'cadvanced\');" class="links" title="Click to show/hide informations"><b>Advanced Options</b></a>&nbsp;</legend>
<center><img src=static/icon_warn.png border=0 width=16 height=16 title="Warning !">&nbsp;<font color=red>All options below should not be modified unless you really know<br>what you are doing&nbsp;! <u>They may render your site inaccessible</u>&nbsp;!</font></center>
  <table id=cadvanced border=0 width=100% class=smallblack cellspacing=0 cellpadding=6';

   if ( (isset($_COOKIE['cadvanced'])) && ($_COOKIE['cadvanced'])==1) {
      echo ' style="display:none;"';
	}

   echo '>
   <tr style="color:#999999;">
    <td width=45%>Allowed HTTP methods</td>
    <td width=45%>
    <table width=100% class=smallblack>
     <tr style="color:#999999;">
      <td width=50% align=right>
       GET <input type="checkbox" checked disabled><br>
       POST <input type="checkbox" checked disabled><br>
       HEAD <input type="checkbox" checked disabled>
      </td>
      <td width=50% align=right>
       DELETE <input type="checkbox" disabled><br>
       TRACE <input type="checkbox" disabled><br>
       OPTIONS <input type="checkbox" disabled><br>
       CONNECT <input type="checkbox" disabled><br>
       PUT <input type="checkbox" disabled>
      </td>
     </tr>
    </table>
    </td>
    <td width=10% align=center><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Select which HTTP methods are allowed.<br>By default, <b>GET</b>, <b>POST</b> and <b>HEAD</b> are allowed, all other methods<br>will be rejected.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>

   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">HTTP hostname</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
    <select class=input><option selected>Mandatory</option><option disabled>Optional</option></select><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'If enabled, this option will reject any request using your server IP<br>instead of its domain name in the <b>Host</b> header of the HTTP request.<br>Unless you need to connect to your site using its IP address, (for<br>instance: http://'.$_SERVER["SERVER_ADDR"].'/index.php), enabling this option will block<br>a lot of hackers scanners because such applications scan IPs rather<br>than domain names.<br>This option is enabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
    </tr>

    <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Allowed ASCII characters</td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>
      ASCII control characters 1 to 8 and 14 to 31&nbsp;<input type=checkbox disabled><br>
      ASCII control characters 9 to 13&nbsp;<input type=checkbox checked disabled><br>
      ASCII printable characters 32 to 127&nbsp;<input type=checkbox checked disabled><br>
      Extended ASCII codes 128 to 255&nbsp;<input type=checkbox checked disabled><br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'<b>NinjaFirewall</b> can reject specific ASCII characters found in any HTTP<br>requests. They are split in 4 sections :<br>-<b>ASCII control characters (1 to 8 and 14 to 31)</b> : in most cases, those<br>characters are not needed and should not be allowed in any request.<br>-<b>ASCII control characters (9 to 13)</b> : those characters are enabled by<br>default because they are Tabs, Line/Form Feed and Carriage Return that<br>can be used in &amp;lt;textarea> fields like contact forms etc.<br>-<b>ASCII printable characters (32 to 127)</b> :  they are letters, numbers,<br>punctuation marks and <u>must be allowed</u>.<br>-<b>Extended ASCII codes (128 to 255)</b> : they are symbols, accentued<br>chars and most websites need them. They are enabled by default.<p>Note that the ASCII character 0x00 (NULL byte) will always be blocked<br>and that the line feed and carriage return will not be allowed in cookies,<br>referrer and user-agent variables.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Block the DOCUMENT_ROOT server variable in GET/POST requests<br>
    </td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>&nbsp;
   <input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option will <b>block</b> scripts attempting to pass the <b>DOCUMENT_ROOT</b><br>server variable in a GET or POST request. Hackers use shell scripts that<br>need to pass this value, but most legitimate programs do not. However,<br>some programs (ie: Joomla, Wordpress) could use this value while saving<br>their configuration and could get blocked too.<br>Its value on your system is : <b>' . getenv('DOCUMENT_ROOT') . '</b><br>By default, this option is disabled.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
   <tr style="color:#999999;">
    <td width=45% style="border-top:1px dotted #666666;">Block any POST request that does not have a Referrer header<br>
    </td>
    <td width=45% align=right style="border-top:1px dotted #666666;"><br>&nbsp;
   <input type=radio disabled>&nbsp;Yes&nbsp;&nbsp;&nbsp;<input type=radio checked disabled>&nbsp;No<br>&nbsp;
    </td>
    <td width=10% align=center style="border-top:1px dotted #666666;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'This option will block any <b>POST</b> request that <b>does not have a Referrer</b><br>header (HTTP_REFERER variable).<br>If you need external applications to post to your scripts (ie: Paypal IPN),<br>you are advised to either keep this option disabled or to add their IPs<br>to your whitelist otherwise they will be blocked.<br>Note that POST requests are not required to have a Referrer header and,<br>for that reason, this option is disabled by default.'.$free.'\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" width=16 height=16 border=0 style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a></td>
   </tr>
 </table>
 </fieldset>
 <br><center><input type=submit class=button value="Save changes" title="Save changes">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit class=button value="Restore default values" name=defval title="Restore all default values" onclick="return restore();"></center>
</form>';

   html_footer();

}
/********************************************************************/
function menu_firewall_policies_save() {

   global $dbh;
   global $db_name;
   global $db_prefix;

	$application = 'generic|option|';
   if (!isset($_POST['defval']) ){
		if ( isset($_POST['oscommerce']) ) { $application.= 'oscommerce|'; }
		if ( isset($_POST['magento']) )    { $application.= 'magento|';    }
		if ( isset($_POST['wordpress']) )  { $application.= 'wordpress|';  }
		if ( isset($_POST['cscart']) )    { $application.= 'cscart|';     }
		if ( isset($_POST['prestashop']) ) { $application.= 'prestashop|'; }
		if ( isset($_POST['drupal']) )     { $application.= 'drupal|';     }
		if ( isset($_POST['joomla']) )     { $application.= 'joomla|';     }
		if ( isset($_POST['virtuemart']) ) { $application.= 'virtuemart|'; }
	}
	$application = substr($application, 0, -1);

   $sql = "UPDATE `".$db_name."`.`". $db_prefix ."nf_options` SET `application` = '" .
			$application."' WHERE `". $db_prefix ."nf_options`.`t_id` = 'options'";
   $dbh->query($sql);

   header('Location: index.php?mid=31&itm=1&token='. $_REQUEST['token']);
   exit;

}
/********************************************************************/
function menu_firewall_editor() {

   html_header(0);

   global $dbh;
   global $db_prefix;
   global $db_name;

	$enabled_rules = $disabled_rules = 0;

	$dbq = $dbh->query('SELECT `id`, `who`, `why`, `enabled` FROM `' . $db_prefix . 'nf_rules`');
	while ( $dbrules = $dbq->fetch_object() ) {
		if ( $dbrules->id > 499 && $dbrules->id < 600 ) { continue; }
		if ($dbrules->enabled == 1) {
			$rules_array[$dbrules->id] = 1;
			$enabled_rules++;
		} else {
			$rules_array[$dbrules->id] = 0;
			$disabled_rules++;
		}
	}

	$err = $msg = '';
	if ( isset($_POST['sel_e_r']) ) {

		if ( $_POST['sel_e_r'] < 1 ) {
			$err = 'you did not select a rule to disable.';

		} else if ( ( $_POST['sel_e_r'] > 499 ) && ( $_POST['sel_e_r'] < 600 ) ) {
			$err = 'to change this rule, use the "Firewall / Rules" menu.';

		} else if (! isset( $rules_array[$_POST['sel_e_r']] ) ) {
			$err = 'this rule does not exist&nbsp;!';

		} else {
			$dbh->query( "UPDATE `" . $db_name . "`.`" . $db_prefix . "nf_rules` SET `enabled` = '0' WHERE `id` = '" . (int) $_POST['sel_e_r'] . "'" );
			$msg = 'rule ID ' . $_POST['sel_e_r'] . ' has been disabled.';
			$rules_array[$_POST['sel_e_r']] = 0;
			$disabled_rules++;
			$enabled_rules--;
		}

	} else if ( isset($_POST['sel_d_r']) ) {
		if ( $_POST['sel_d_r'] < 1 ) {
			$err = 'you did not select a rule to enable.';
		} else if ( ( $_POST['sel_d_r'] > 499 ) && ( $_POST['sel_d_r'] < 600 ) ) {
			$err = 'to change this rule, use the "Firewall / Rules" menu.';
		} else if (! isset( $rules_array[$_POST['sel_d_r']] ) ) {
			$err = 'this rule does not exist&nbsp;!';
		} else {
			$dbh->query( "UPDATE `" . $db_name . "`.`" . $db_prefix . "nf_rules` SET `enabled` = '1' WHERE `id` = '" . (int) $_POST['sel_d_r'] . "'" );
			$msg = 'rule ID ' . $_POST['sel_d_r'] . ' has been enabled.';
			$rules_array[$_POST['sel_d_r']] = 1;
			$enabled_rules++;
			$disabled_rules--;
		}
	}

	if ( $msg ) {
		echo '<br><table align=center width=300 cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_ok.png" border=0 width=16 height=16>&nbsp;' . $msg . '</td></tr></table>';
	} else if ( $err ) {
		echo '<br><table align=center width=300 cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_error.png" border=0 width=16 height=16>&nbsp;' . $err . '</td></tr></table>';
	}

   echo '<br><fieldset><legend>&nbsp;<a href="javascript:toogle(\'cedit\');" class="links" title="Click to show/hide informations"><b>Rules Editor</b></a>&nbsp;</legend>
   <table id="cedit" width="100%" class="smallblack" border="0" cellpadding="0" cellspacing="0"';
   if ( (isset($_COOKIE['cedit'])) && ($_COOKIE['cedit'])==1) {
      echo ' style="display:none;"';
	}
   echo '><tr>';

   echo'
	<td width="40%">Select the rule you want to disable</td>
   <td align="right" width="55%">
   <form method="post">
		<select name="sel_e_r" class="input" style="width:200px;">
			<option value="0">Total rules enabled: ' . $enabled_rules . '</option>';

	foreach ( $rules_array as $r_id => $tmp ) {
		if ( $tmp ) {
			echo '<option value="' . $r_id  . '">' . 'Rule ID : ' . $r_id . '</option>';
		}
	}

	echo '</select>&nbsp;&nbsp;&nbsp;<input type="submit" name="disable_r" value="Disable it" class="button" style="width:80px;">
	</form>
   </td>
    <td align="center" width="5%">&nbsp;</td>
	</tr>

	<tr>
	<td width="40%">&nbsp;</td>
   <td align="right" width="55%">&nbsp;</td>
   <td align="center" width="5%" valign="center"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'Besides the firewall policies, <b>NinjaFirewall</b> includes also a large set<br />of built-in rules used to protect your site against the most common<br />vulnerabilities and hacking attempts. They are always enabled and<br />you cannot edit them, but if you notice that your visitors are wrongly<br />blocked by some of those rules, you can disable them individually:<br />1) Check your firewall log and find the rule ID you want to disable in<br />the log RULE column.<br />2) Select its ID in the editor rules list and click [Disable it].\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',DELAY,300,FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/icon_info.png" style="opacity:0.6" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.6" border="0" height="16" width="16"></a></td>
   </tr>

	<tr>
	<td width="40%">Select the rule you want to enable</td>
   <td align="right" width="55%">
	<form method="post">
		<select name="sel_d_r" class="input" style="width:200px;">
			<option value="0">Total rules disabled: ' . $disabled_rules . '</option>';

	foreach ( $rules_array as $r_id => $tmp ) {
		if (! $tmp ) {
			echo '<option value="' . $r_id  . '">' . 'Rules ID : ' . $r_id . '</option>';
		}
	}

	echo '</select>&nbsp;&nbsp;&nbsp;<input type="submit" name="enable_r" value="Enable it" class="button" style="width:80px;">
	</form>
   </td>
    <td align="center" width="5%">&nbsp;</td>
	</tr></table>
</fieldset>';

	html_footer();

}

/********************************************************************/
function menu_firewall_log() {

   html_header(0);

   if (! file_exists(LOG_FILE) ) {
      echo '<br><table align=center width=80% cellpadding=6 style="border:1px solid #FDCD25;"><tr><td class=smallblack align=center><img src="static/icon_warn.png" border=0 width=16 height=16 title="Warning !">&nbsp;you do not have any log yet&nbsp;!</td></tr></table><p>';
      html_footer();
      exit;
   }

   echo '<script>
pic1= new Image(32,32);pic1.src="static/load.gif";
var http = getHTTPObject();
var ajaxRUL="index.php";
function ajax(itm, act){
   if (itm==0){document.getElementById("logoutput").innerHTML= "";return false;}
   var IDref=0;
   if (itm==8) {
      IDref=prompt("Please enter the incident number (7 digits) :", "");
      document.logform.selog.value=0;
      if ((!IDref) || (! IDref.match(/^\d{7}$/))) {
         alert("Sorry, incident number must only be 7 digits.\nAborting.");
         return false;
      }
   }
   document.getElementById("logoutput").innerHTML= "<br><br><br><center><img src=\'"+pic1.src+"\' height=32 width=32></center>";
   http.open("POST", ajaxRUL, true);
   http.onreadystatechange = ajaxRes;
   http.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
   http.send("mid=' . $GLOBALS['mid'] . '&act="+act+"&itm="+itm+"&xtr="+IDref+"&token='.$_REQUEST['token'].'");
}
function ajaxRes(){
   if (http.readyState == 4){
      document.getElementById("logoutput").innerHTML= http.responseText;
   }
}
function getHTTPObject(){
   var http;
   if(window.XMLHttpRequest){
      http = new XMLHttpRequest();
   } else if(window.ActiveXObject){
      http = new ActiveXObject("Microsoft.XMLHTTP");
   }
   return http;
}
</script>';

   echo '<br><fieldset><legend>&nbsp;<a href="javascript:toogle(\'clog\');" class="links" title="Click to show/hide informations"><b>Log</b></a>&nbsp;</legend>
   <table id=clog width=100% class=smallblack border=0 cellpadding=10 cellspacing=0';
   if ( (isset($_COOKIE['clog'])) && ($_COOKIE['clog'])==1) {
      echo ' style="display:none;"';
	}
   echo '><tr>';

   if (! $fh = @fopen(LOG_FILE, 'r') ) {
      echo '<td><center><img src="static/icon_warn.png" border=0 width=16 height=16 title="Warning !"><font color=red>&nbsp;unable to open logfile ['. LOG_FILE .'] !</font></center><br></td></tr></table></fieldset>';
      html_footer();
      exit;
   }
   fclose($fh);

   echo'<td><form name=logform><center>
Viewing [firewall_' . date('Y-m') . '.log] : <select class=input name=selog OnChange="return ajax(this.form.selog.value,1);">
<option value=0 selected>Select...</option>
<option value=3>Critical-scale attacks</option>
<option value=2>High-scale attacks</option>
<option value=1>Medium-scale attacks</option>
<option value=4>Errors</option>
<option value=5>Uploaded files</option>
<option value=6>All activities</option>
<option value=7>Raw log</option>
<option value=8>Search incident #ID...</option>
</select>
<br>&nbsp;
<div id=logoutput style="height:300px;border:1px solid #FDCD25;"><br><br><br><img src="static/icon_warn.png" border=0 width=16 height=16>&nbsp;use the above menu to select the log activities to display</div>
</center></form>
</td></tr></table>
</fieldset>';

	html_footer();

}
/********************************************************************/
function raw_admin_log() {

	define('NF_NODBG', true);

   echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>NinjaFirewall : admin raw log</title><link href="static/styles.css" rel="stylesheet" type="text/css"><script>function dellog(){if (confirm("Delete admin.log ?")){return true;}else{return false;}}</script></head><body bgcolor=white class=smallblack><fieldset>';

   if ( $fh = fopen(ADMIN_LOG_FILE, 'r') ) {
      $st = stat(ADMIN_LOG_FILE);
      echo '<legend><b>&nbsp;admin raw log ['. ADMIN_LOG_FILE .' - ' . $st['size'] . ' bytes]</b>&nbsp;</legend><pre>';

      if ($st['size'] < 5) {
         fclose($fh);
         echo '<center>Logfile is empty.</center></pre></fieldset><p></body></html>';
         exit;
      }
      while (! feof($fh) ) {
         $line = fgets($fh);
         if ( preg_match('/FAILED\]$/', $line, $match ) ) {
            echo '<font color=red>' . $line . '</font>';
         } else {
            echo $line;
         }
      }
      fclose($fh);
      echo '</pre></fieldset><p><center><form method=post onsubmit="return dellog();"><input type=hidden name=mid value=91><input type=submit class=input value="Erase log"></form><p></body></html>';
   } else {
      echo '<font color=red>Unable to open logfile ('. ADMIN_LOG_FILE .') !</font></fieldset><p></body></html>';
   }

   exit;

}
/********************************************************************/
function flush_admin_log() {

	global $dbadmin;

   if ($fh = fopen(ADMIN_LOG_FILE, 'w') ) {
		@fwrite($fh, date('[d/M/Y H:i:s O] ') . '[' . $dbadmin->name . '] ' .
      '[' . $_SERVER['REMOTE_ADDR'] . '] ' . '[OK] ' .  "\n" );
      fclose($fh);
	}
	@chmod(ADMIN_LOG_FILE, 0666);

}
/********************************************************************/
function html_header($js) {

   global $dbadmin;

   $menu = array(
      10 => 'Summary &gt; Overview',
      11 => 'Summary &gt; Statistics',
      20 => 'Account &gt; License',
      21 => 'Account &gt; Options',
      22 => 'Account &gt; Update',
      30 => 'Firewall &gt; Options',
      31 => 'Firewall &gt; Policies',
      33 => 'Firewall &gt; Rules Editor',
      32 => 'Firewall &gt; Log'
   );
   $m10 = $m11 = $m12 = $m20 = $m21 = $m22 = $m30 = $m31 = $m32 = $m33 = 'static/bullet_off.gif';

   if    ( $GLOBALS['mid'] == 10 ) $m10 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 11 ) $m11 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 20 ) $m20 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 21 ) $m21 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 22 ) $m22 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 30 ) $m30 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 31 ) $m31 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 32 ) $m32 = 'static/bullet_on.gif';
   elseif( $GLOBALS['mid'] == 33 ) $m33 = 'static/bullet_on.gif';

   echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <title>NinjaFirewall : '. $menu[$GLOBALS['mid']] .'</title>
   <link href="static/styles.css" rel="stylesheet" type="text/css">
   <link rel="Shortcut Icon" type="image/gif" href="static/favicon.ico">
   <script>function toogle(ID){if(document.getElementById(ID).style.display=="none"){document.getElementById(ID).style.display="";document.cookie=ID+"=; expires=Thu, 01-Jan-70 00:00:01 GMT;";}else{document.getElementById(ID).style.display="none";document.cookie=ID+"=1;";}}function disconnect(who){if (confirm("Close the session for ["+who+"] ?")){return true};return false;}';

   if ($js) {
		echo 'function popup(url,width,height,scroll_bar) {height=height+20;width=width+20;var str = "height=" + height + ",innerHeight=" + height;str += ",width=" + width + ",innerWidth=" + width;if (window.screen){var ah = screen.availHeight - 30;var aw = screen.availWidth -10;var xc = (aw - width) / 2;var yc = (ah - height) / 2;str += ",left=" + xc + ",screenX=" + xc;str += ",top=" + yc + ",screenY=" + yc;if (scroll_bar) {str += ",scrollbars=no";}else {str += ",scrollbars=yes";}str += ",status=no,location=no,resizable=yes";}win = open(url, "nfpop", str);setTimeout("win.window.focus()",1300);}';
	}
   echo '</script>
</head>
<body bgcolor=white class=smallblack>
<script src="static/javascript.js" type="text/javascript"></script>
<table style=\'border:0px solid #666666;\' width=100%>
   <tr>
      <td align=left width=250>
		<img src="static/logo.png" width=192 height=62>
      </td>
      <td>&nbsp;</td>
      <td align=right>
         <a href=\'?logout\' onclick="return disconnect(\''. $dbadmin->name .'\');"><img border=0 src="static/logout.png" width=45 height=45 title="Logout" alt="Logout" style="'.OPA.'" onmouseover="'.OPA_OVER.'" onmouseout="'.OPA_OUT.'"></a>
      </td>
   </tr>
</table>

<table border=0 width=100% cellpadding=0 cellspacing=0>
   <tr valign=top>
      <td width=150 align=left>
         <table border=0 width=150 height=400>
            <tr valign=top>
               <td class=tinyblack><br><br>
                  <center style="border:1px solid #FDCD25;">Summary</center><p>
                  <img src="'. $m10 .'" width=10 height=10>&nbsp;<a class=links href="?mid=10&token='.$_REQUEST['token'].'">Overview</a><p>
                  <img src="'. $m11 .'" width=10 height=10>&nbsp;<a class=links href="?mid=11&token='.$_REQUEST['token'].'">Statistics</a><p>
                  <center style="border:1px solid #FDCD25;">Account</center><p>
                  <img src="'. $m20 .'" width=10 height=10>&nbsp;<a class=links href="?mid=20&token='.$_REQUEST['token'].'">License</a><p>
                  <img src="'. $m21 .'" width=10 height=10>&nbsp;<a class=links href="?mid=21&token='.$_REQUEST['token'].'">Options</a><p>
                  <img src="'. $m22 .'" width=10 height=10>&nbsp;<a class=links href="?mid=22&token='.$_REQUEST['token'].'">Update</a><p>
                  <center style="border:1px solid #FDCD25;">Firewall</center><p>
                  <img src="'. $m30 .'" width=10 height=10>&nbsp;<a class=links href="?mid=30&token='.$_REQUEST['token'].'">Options</a><p>
                  <img src="'. $m31 .'" width=10 height=10>&nbsp;<a class=links href="?mid=31&token='.$_REQUEST['token'].'">Policies</a><p>
                  <img src="'. $m33 .'" width=10 height=10>&nbsp;<a class=links href="?mid=33&token='.$_REQUEST['token'].'">Rules Editor</a><p>
                  <img src="'. $m32 .'" width=10 height=10>&nbsp;<a class=links href="?mid=32&token='.$_REQUEST['token'].'">Log</a>
               </td>
            </tr>
            <tr>
					<td class=tinyblack style="text-align:center;"><a href="javascript:void(0);" style="cursor: help;" onmouseover="Tip(\'You are running the <b>Free Edition</b> of NinjaFirewall.<br>It is lacking a lot of security features and rules as well as options,<br>support and one-click updates. If you need more power and security<br>please upgrade to the <b>Professional Edition</b>&nbsp;!\',BGCOLOR,\'#FFFBCC\',BORDERCOLOR,\'#FFCC25\',FOLLOWMOUSE,false,ABOVE,true,SHADOW,true);"><img src="static/logofree.png" width=60 height=60 border=0></a></td>
				</tr>
			</table>
      </td>
      <td width=20>&nbsp;</td>
      <td>
         <table style="border:0px solid #666666;" width=100% cellpadding=6>
            <tr>
               <td class=smallblack><b>NinjaFirewall : '. $menu[$GLOBALS['mid']] .'</b><br>';

}
/********************************************************************/
function html_footer() {

   echo'   </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
<p><center class=tinygrey><a href="http://ninjamonitoring.com/" title="NinjaMonitoring : monitor your website for suspicious activities"><img src="static/p_icon_nm.png" height="21" width="21" border="0"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://ninjafirewall.com/" title="NinjaFirewall : advanced firewall software for all your PHP applications"><img src="static/p_icon_nf.png" height="21" width="21" border="0"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://ninjarecovery.com/" title="NinjaRecovery : incident response, malware removal &amp; hacking recovery" ><img src="static/p_icon_nr.png" height="21" width="21" border="0"></a><br />&copy; 2012-'. date('Y') .' NinTechNet<br /><a class=tinygrey style="border-bottom:1px dotted #ffd821;" href="http://nintechnet.com/" title="The Ninja Technologies Network">The Ninja Technologies Network</a></center>';
	if (isset($_SESSION['jswarn'])) {
		echo '<script>window.onload=alert("A newer version (v'. $_SESSION['vapp'] .') is available, please download it for free from NinjaFirewall.com website.")</script>';
		unset($_SESSION['jswarn']);
	}
	echo '</body>
</html>';
   exit;

}
/********************************************************************/
// EOF
?>