<?php
/*
 +------------------------------------------------------------------+
 | NinjaFirewall   (c)2012-2013 NinTechNet                          |
 |                 <contact@ninjafirewall.com>                      |
 |                                                                  |
 | EDITION :       Free Edition                                     |
 +------------------------------------------------------------------+
 | REVISION:       2013-08-18 15:28:13                              |
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

if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) { die('Forbidden'); }

if ( $_SERVER['SCRIPT_FILENAME'] == dirname(__FILE__) .'/install/test.php' ) {
	die('<html><script>function resok(){opener.document.getElementById("res").style.display="";opener.document.getElementById("testfw").style.display="none";}</script><body onload="resok();"><br><br><br><center><h1>It works :)</h1></center></body></html>');
}

require(dirname(__FILE__) . '/config.php');

define('NF_STARTTIME', microtime(true));

if ( (!$db_name) || (!$db_ip) || (!$db_port) || (!$db_user) || (!$db_pass) ) {
   echo 'NinjaFirewall : error, unable to get database credentials !';
   $db_err_msg = '[unable to get database credentials]';
	$log_db_err = 1;
}

@$dbh = new mysqli($db_ip, $db_user, $db_pass, $db_name, $db_port);
if (mysqli_connect_error() ) {
	echo 'NinjaFirewall : error, unable to connect to database !';
   $db_err_msg = '[unable to connect to database]';
	$log_db_err = 1;
}

if ( ( isset($log_db_err)) && ($log_db_err == 1) ) {
	$LOG_FILE = dirname(__FILE__) . '/logs/firewall_' . date('Y-m') . '.log';
   if ( $fh = fopen($LOG_FILE, 'a') ) {
      @fwrite($fh, date('[d/M/y H:i:s O] ') . '[' . benchmarks() . '] ' .
      '[' . $_SERVER['SERVER_NAME'] . '] ' . '[#0000000] ' .
      '[4] ' . '[' . $_SERVER['REMOTE_ADDR'] . '] ' .
      '[000] ' . '[' . $_SERVER['REQUEST_METHOD'] . '] ' .
      '[' . $_SERVER['SCRIPT_NAME'] . '] ' . '[NinjaFirewall] ' .
       $db_err_msg . "\n" );
      fclose($fh);
   }
   exit;
}

$db_ip = $db_port = $db_user = $db_pass = '';

$dbq = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_options`');
if (! $dboptions  = $dbq->fetch_object() ) {
	die('error (line ' . __LINE__ . ') : fetch_object error');
}

if ($dboptions->debug) {
   register_shutdown_function('debugfirewall', $dboptions->debug);
   define('STAG', '- ');
   define('ETAG', "\n");
   $nfdebug = STAG ."starting NinjaFirewall". ETAG . STAG ."hooked PHP script\t\t[----]   ". $_SERVER['SCRIPT_FILENAME'] . ETAG;
}

if (! $dboptions->enabled) {
   if ($dboptions->debug) { define('NFDEBUG', $nfdebug.= STAG ."protection is disabled\t[STOP]". ETAG . '::' . benchmarks() ); }
   $db_name = $db_prefix = '';
   @$dbh->close();
   return;
}

if ($dboptions->debug) { $nfdebug.= STAG ."checking user IP\t\t";}
if ( (preg_match('/^(?:::ffff:)?127\.0\.0\.1$/', $_SERVER['REMOTE_ADDR'])) || ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) ) {
   if ($dboptions->debug) { define('NFDEBUG', $nfdebug.= '[STOP]   '. $_SERVER['REMOTE_ADDR'] .' is whitelisted'. ETAG . '::' . benchmarks() ); }
   $db_name = $db_prefix = '';
   @$dbh->close();
   return;
}

if ($dboptions->debug) { $nfdebug.= '[----]   banning IP option is off'. ETAG; }
if ( ($_SERVER['SCRIPT_FILENAME'] == dirname(__FILE__) .'/index.php') || ($_SERVER['SCRIPT_FILENAME'] == dirname(__FILE__) .'/login.php') ) {
   if ($dboptions->debug) { define('NFDEBUG', $nfdebug.= STAG ."script is whitelisted\t\t[STOP]   ".$_SERVER['SCRIPT_NAME']. ETAG . '::' . benchmarks() ); }
   $db_name = $db_prefix = '';
   @$dbh->close();
   return;
}

if (preg_match('/^[\d.:]+$/', $_SERVER['HTTP_HOST'])) {
   if ($dboptions->debug) { $nfdebug.= STAG ."HTTP_HOST\t\t\t[FAIL]   HTTP_HOST is an IP (".$_SERVER['HTTP_HOST']  .')'. ETAG; }
   write2log('HTTP_HOST is an IP', $_SERVER['HTTP_HOST'], 1, 0);
   block();
}

if ( strpos('GET|POST|HEAD', $_SERVER['REQUEST_METHOD']) === false ) {
   if ($dboptions->debug) { $nfdebug.= STAG ."REQUEST_METHOD\t\t[FAIL]   ". $_SERVER['REQUEST_METHOD'] .' not allowed'. ETAG; }
   write2log('request method not allowed', $_SERVER['REQUEST_METHOD'], 2, 0);
   block();
}

check_request();

if ($dboptions->debug) { $nfdebug.= STAG ."checking uploads\t\t"; }

/* Cambio para permitir subir archivos al servidor LDONIS*/
session_start();
$upload=validateUpload(2000);
   if (!empty($_FILES) && $upload->enabled==0 && count(@$_SESSION)>1) {
     check_upload();
   } else {
    if ($dboptions->debug) { $nfdebug.= "[----]   no upload detected". ETAG; }
 }
/*Fin Cambios*/

$_GET = sanitise( $_GET, 1, 'GET');
$_POST = sanitise( $_POST, 1, 'POST');
$_COOKIE = sanitise( $_COOKIE, 1, 'COOKIE');
if (! empty($_SERVER['HTTP_USER_AGENT'])) {
	$_SERVER['HTTP_USER_AGENT'] = sanitise( $_SERVER['HTTP_USER_AGENT'], 1, 'HTTP_USER_AGENT');
}
if (! empty($_SERVER['HTTP_REFERER'])) {
	$_SERVER['HTTP_REFERER'] = sanitise( $_SERVER['HTTP_REFERER'], 1, 'HTTP_REFERER');
}
if (! empty($_SERVER['PATH_INFO'])) {
	$_SERVER['PATH_INFO'] = sanitise( $_SERVER['PATH_INFO'], 2, 'PATH_INFO');
}
if (! empty($_SERVER['PATH_TRANSLATED'])) {
	$_SERVER['PATH_TRANSLATED'] = sanitise( $_SERVER['PATH_TRANSLATED'], 2, 'PATH_TRANSLATED');
}
if (! empty($_SERVER['PHP_SELF'])) {
	$_SERVER['PHP_SELF'] = sanitise( $_SERVER['PHP_SELF'], 2, 'PHP_SELF');
}

if ( (! defined('NFDEBUG')) && ($nfdebug) ) { define('NFDEBUG',$nfdebug . '::' . benchmarks() ); }

$db_name = $db_prefix = $nfdebug = '';
@$dbh->close();
return;

/* ================================================================ */
function debugfirewall($debug) {

	if ( (defined('NF_NODBG')) || (! defined('NFDEBUG')) || (NFDEBUG == '') ) {
		return;
	}
	list($nfdebug, $bench) = explode('::', NFDEBUG . '::');

   if ($debug == 1) {
      echo "\n<!--\n". htmlentities( $nfdebug ) ."- stopping NinjaFirewall\n- processing time:\t\t$bench s\n-->"  ;
   } else {
		echo '<br><script>function onoff(){if(document.getElementById("tex").style.display=="none"){document.getElementById("tex").style.display="";document.getElementById("fie").style.background="#000000";document.cookie="tex=0; expires=Thu, 01-Jan-70 00:00:01 GMT;";}else{document.getElementById("tex").style.display="none";document.getElementById("fie").style.background="none";document.cookie="tex=1;";}}</script>'. "\n". '<center><fieldset id=fie style="width:85%;font-family:Verdana,Arial,sans-serif,Ubuntu;font-size:10px;background:';
		if ( (isset($_COOKIE['tex'])) && ($_COOKIE['tex'])==1) {echo 'none';} else {echo '#000000';}
		echo ';border:0px solid #000000;padding:0px;"><legend id=leg style="border:1px solid #ffd821;background:#ffd821;font-family:Verdana,Arial,sans-serif,Ubuntu;font-size:10px;"><a title=\'Click to mask/show the console\' href="javascript:onoff();" style="text-decoration: none;color:#000000;background:#ffd821;"><b>&nbsp;NinjaFirewall debug console&nbsp;</b></a></legend><textarea id=tex rows='. count(explode("\n", $nfdebug)) .' style="font-family:\'Courier New\',Courier,monospace,Verdana, Arial, sans-serif;font-size:12px;width:100%;border:none;padding:0px;background:#000000;color:#ffffff;line-height:14px;';
		if ( (isset($_COOKIE['tex'])) && ($_COOKIE['tex'])==1) {echo 'display:none;'; }
		echo '" wrap="off">'. htmlentities( $nfdebug ) ."- stopping NinjaFirewall\n- processing time\t\t$bench s</textarea></fieldset></center><br>";
   }
}

/* ================================================================ */
function validateUpload($id){
   global $db_name;
   global $dbh;
   global $db_prefix;
  $dbq = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_rules` where id='.$id);
  $dbrules = $dbq->fetch_object();
  return $dbrules; 
}



/* ================================================================ */
function check_request() {

   global $dboptions;
   global $db_name;
   global $dbh;
   global $nfdebug;
	$rules_count = 0;
	global $db_prefix;

   $dbq = $dbh->query('SELECT * FROM `'. $db_prefix .'nf_rules` WHERE `who` REGEXP "^('. $dboptions->application .')$" && `enabled` = "1"');
	while ($dbrules = $dbq->fetch_object() ) {
      $wherelist = explode('|', $dbrules->where);
      foreach ($wherelist as $where) {
			if ( ($where == 'POST') || ($where == 'GET') ) {
				foreach ($GLOBALS['_' . $where] as $reqkey => $reqvalue) {
               if ( is_array($reqvalue) ) {
                  $res = flatten( "\n", $reqvalue );
                  $reqvalue = $res;
                  $dbrules->what = '(?m:'. $dbrules->what .')';
               }
               if (! $reqvalue) {continue;}
               $rules_count++;
               if ( preg_match('`'.$dbrules->what.'`', $reqvalue) ) {
                  if ($dboptions->debug) { $nfdebug.= STAG ."checking request\t\t". '[FAIL]   '. $where .' : ' . $dbrules->why . ' (#'. $dbrules->id . ')' . ETAG; }
                  write2log($dbrules->why, $where . ':' . $reqkey . ' = ' . $reqvalue, $dbrules->level, $dbrules->id);
                  block();
               }
            }
				continue;
			}

			$sub_value = explode(':', $where);
			if ( (! empty($sub_value[1]) ) && ( @isset($GLOBALS['_' . $sub_value[0]] [$sub_value[1]]) ) ) {
				$rules_count++;
				if ( is_array($GLOBALS['_' . $sub_value[0]] [$sub_value[1]]) ) {
               $res = flatten( "\n", $GLOBALS['_' . $sub_value[0]] [$sub_value[1]] );
               $GLOBALS['_' . $sub_value[0]] [$sub_value[1]] = $res;
               $dbrules->what = '(?m:'. $dbrules->what .')';
            }
            if (! $GLOBALS['_' . $sub_value[0]] [$sub_value[1]]) {continue;}
				if ( preg_match('`'. $dbrules->what .'`', $GLOBALS['_' . $sub_value[0]] [$sub_value[1]]) ) {
					if ($dboptions->debug) { $nfdebug.= STAG ."checking request\t\t". '[FAIL]   '.$sub_value[0].':'.$sub_value[1].' : ' . $dbrules->why . ' (#'. $dbrules->id . ')' . ETAG; }
					write2log($dbrules->why, $sub_value[0].':'.$sub_value[1].' = ' . $GLOBALS['_' . $sub_value[0]] [$sub_value[1]], $dbrules->level, $dbrules->id);
					block();
				}

         } elseif ( isset($_SERVER[$where]) ) {
            $rules_count++;
				if ( preg_match('`'. $dbrules->what .'`', $_SERVER[$where]) ) {
               if ($dboptions->debug) { $nfdebug.= STAG ."checking request\t\t". '[FAIL]   ' . $where.' : ' . $dbrules->why . ' (#'. $dbrules->id . ')' . ETAG; }
               write2log($dbrules->why, $where . ':' . $_SERVER[$where], $dbrules->level, $dbrules->id);
               block();
            }
         }
      }
   }

   if ($dboptions->debug) { $nfdebug.= STAG ."checking request\t\t". '[PASS]   '. $rules_count . ' occurences checked' . ETAG; }

}
/* ================================================================ */
function flatten($glue, $pieces) {

   foreach ($pieces as $r_pieces) {
      if ( is_array($r_pieces)) {
         $ret[] = flatten($glue, $r_pieces);
      } else {
         $ret[] = $r_pieces;
      }
   }
   return implode($glue, $ret);
}
/* ================================================================ */
function sanitise($str, $how, $msg ) {

	global $dbh;
	global $dboptions;
	global $nfdebug;

	if (! isset($str) ) {
		return null;
	} else if (is_string($str) ) {
		if (get_magic_quotes_gpc() ) {$str = stripslashes($str);}

		if ($how == 1) {
			$str2 = $dbh->real_escape_string($str);
			$str2 = str_replace('`', '\`', $str2);
		} else {
			$str2 = str_replace(	array('\\', "'", '"', "\x0d", "\x0a", "\x00", "\x1a", '`', '<', '>'),
				array('\\\\', "\\'", '\\"', 'X', 'X', 'X', 'X', '\\`', '\\<', '\\>'),	$str);
		}
		if ($str2 != $str) {
			write2log('Sanitising user input', $msg . ': ' . $str, 6, 0);
			if ($dboptions->debug) { $nfdebug.= STAG . "sanitising $msg\t\t[WARN]   string: $str" . ETAG; }
		}
		return $str2;

	} else if (is_array($str) ) {
		foreach($str as $key => $value) {
			if (get_magic_quotes_gpc() ) {$key = stripslashes($key);}

			$key2 = str_replace(	array('\\', "'", '"', "\x0d", "\x0a", "\x00", "\x1a", '`', '<', '>'),
				array('\\\\', "\\'", '\\"', 'X', 'X', 'X', 'X', '&#96;', '&lt;', '&gt;'),	$key, $occ);
			if ($occ) {
				unset($str[$key]);
				write2log('Sanitising user input', $msg . ': ' . $key, 6, 0);
				if ($dboptions->debug) { $nfdebug.= STAG . "sanitising $msg\t\t[WARN]   string: $key" . ETAG; }
			}
			$str[$key2] = sanitise($value, $how, $msg);
		}
		return $str;
	}
}
/* ================================================================ */
function check_upload() {

   global $nfdebug;
   global $dboptions;
   $tmp = '';

   foreach ($_FILES as $file) {
     if (! $file['name']) { continue; }
     $tmp.= $file['name'] . ', ' . number_format($file['size']) . ' bytes ';
	}
   if ($tmp) {
		if ($dboptions->debug) { $nfdebug.= '[FAIL]   file upload attempt : '. $tmp . ETAG; }
		write2log('File upload attempt', rtrim($tmp, ' '), 2, 0);
		block();
	}

   if ($dboptions->debug) { $nfdebug.= '[----]   upload field is empty' . ETAG; }
}
/* ================================================================ */
function block() {

   global $nfdebug;
   global $rand_value;

   header('HTTP/1.1 403 Forbidden');
	header('Status: 403 Forbidden');
	echo '<html><head><title>403 Forbidden</title><style>.smallblack{font-family:Verdana,Arial,Helvetica,Ubuntu,"Bitstream Vera Sans",sans-serif;font-size:12px;line-height:16px;color:#000000;}.tinygrey{font-family:Verdana,Arial,Helvetica,Ubuntu, "Bitstream Vera Sans",sans-serif;font-size:10px;line-height:12px;color:#999999;}</style></head><body><br><br><br><br><br><table align=center style="border:1px solid #FDCD25;" cellspacing=0 cellpadding=6 class=smallblack><tr><td align=center><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA3XAAAN1wFCKJt4AAAAB3RJTUUH1goFFS4tIeiJwwAAAqNJREFUOMttk9trXFUUxn97nz0zZ+ZEEnNFwSAhVQoKEcFCqaJ9CQhBnxRC639QCvqgkJdQGoo3fBbxQmlLzIPoQ0SKEAQlITEEL6mkScUmJ2MymclJzlzOTM6cs3w4U3OhH+yXtdb3fWvtxVKcwPI4g20Z3nW6H30509HvgCLcc2tVr/hT4PPRU1f5i4dhZhzjfpz+Mpx/25fdJZE4kkPEIt7vEi68429+kr3x66ekjpFlCqvwef+8FH6OJPRFQl+q+9vy9eQtuXnjupQK6/IgLsW5aOeLJxeXx0kDGIC8y2ePvzb+HCanKa8BsHLnH+YXFjHG0NUmDL/0bOKm07r7/MRQ+O1bX0E0au59wKmegadHMI6hsgoa0FD11snn81iWxc52BglslAAxoIzuHjg9vPbhn88Yx+G9VC7dTbCW9KMTo9r+Jq7rorVmr2ghQRqlFQgQQiqb7mxvY8zkOjgHGxCuQgSoRCQob+O6LkopdosCByrJCUkd97HbecGYHDZ6D6qzYPf8L1ArF9nY2Eg62G0iYQRKJSM0SqBLGIeMUVkUFnBwF9QWpOxkM3GdZrMJQHhQBGkk5LAOB34yrg1GFA1SgCWg90Htg4InHjtcc19PDaVqrS0AKaAJommYsMFstpNBaCVM8l48C79MgbsFr5wBbbdm161/MhB5LOlmhfdjGx/TEkgBaVhx4c3LcOkK/PF3EjtqIFnKUcA13TXKcrDDD7QRYx2KfHMb3H+hUITJaYj0EQGHuF7gx443WNQATpGLjT2WeIQIKyl6fQT6eqGrE0ZeBcsGrITc8FjOVrhw/B6+J1OfYyr28MRHxEfiKhIHiFQQKSOxh1ef5zuZwX7AUyevsjLNUKqXMauL51WOtIAioB6V+K1ZYsIZZvFo/X+fTjL6xSvBJAAAAABJRU5ErkJggg==" border=0 width=16 height=16><p>Sorry <b>'. $_SERVER['REMOTE_ADDR'] .'</b>, your request cannot be proceeded.<br>For security reason it was blocked and logged.<p>If you think that this was a mistake, please contact<br>the webmaster and enclose the following incident ID&nbsp;:<p>[<b>#' . $rand_value . '</b>]<br>&nbsp;</td></tr></table><br><br><br><br><center class=tinygrey>&copy; 2012-'. date('Y') .' <a style="color:#999999;" href="http://nintechnet.com/" target="_blank" title="The Ninja Technologies Network">NinTechNet</a><br>The Ninja Technologies Network</center></body></html>';

   if ($nfdebug) {define('NFDEBUG', $nfdebug . '::' . benchmarks() );}

	@$dbh->close();
   exit;
}
/* ================================================================ */
function write2log( $loginfo, $logdata, $loglevel, $ruleid ) {

   global $dboptions;
   global $rand_value;
   global $nfdebug;

	if ( ($loglevel == 6) || ($loglevel == 5) ) {
		$rand_value = '0000000';
		$http_ret_code = '200 OK';
	} else {
		$rand_value = mt_rand(1000000, 9000000);
		$http_ret_code = '403 Forbidden';
	}

	$LOG_FILE = dirname(__FILE__) . '/logs/firewall_' . date('Y-m') . '.log';
	if (! $handle = fopen($LOG_FILE, 'a') ) {
		if ($dboptions->debug) { $nfdebug.= STAG .'unable to write to log'. "\t" . '[ERROR]  ' . $LOG_FILE . ETAG; }
		return;
	}

   if (strlen($logdata) > 100) { $logdata = substr($logdata, 0, 100) . '...'; }
	$res = '';
	$string = str_split($logdata);
	foreach ( $string as $char ) {
		if ( ord($char) < 32 ) {
			$res .= '%' . bin2hex($char);
		} else {
			$res .= $char;
		}
	}

   @fwrite($handle,
      '[' . time() . '] [' . benchmarks() . '] ' .
      '[' . $_SERVER['SERVER_NAME'] . '] ' . '[#' . $rand_value . '-' . $ruleid . '] ' .
      '[' . $loglevel . '] ' . '[' . $_SERVER['REMOTE_ADDR'] . '] ' .
      '[' . $http_ret_code . '] ' . '[' . $_SERVER['REQUEST_METHOD'] . '] ' .
      '[' . $_SERVER['SCRIPT_NAME'] . '] ' . '[' . $loginfo . '] ' .
      '[' . $res . ']' . "\n"
   );
   fclose($handle);
}
/* ================================================================ */
function benchmarks() {

   return round( (microtime(true) - NF_STARTTIME), 5);

}
/* ================================================================ */
// EOF
?>