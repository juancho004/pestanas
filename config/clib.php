<?php 

class Lib{
	
	private $url;
	private $bridge;
	private $params;

	public function __construct($url)
	{
		if (is_array($url)) {
			$this->params = $this->bridge = $url;
			$url = $url[0];
		}

		
		if (strpos($url, '://') === false) {
			$url = (!empty($url)) ? $url : 'libs';
			
			if (strpos($url, ':') !== false) {
				$ini_bridge_part = explode(':', $url);
				
				$url = $ini_bridge_part[0];
				$ini_bridge = $ini_bridge_part[1];
				
			} else {
				$ini_bridge = $url;
			}

			$ini_bridge = strtoupper($ini_bridge);
			$ini_file_path = dirname(__FILE__) . '/';

 			foreach (w(' ./ ../', false, 'rtrim') as $path) {
				$ini_file = $path . 'ini.' . $url . '.php';

				if (!empty($path)) {
					$ini_file = $ini_file_path . $ini_file;
				}

				if (@file_exists($ini_file)) {
					$this->params = parse_ini_file($ini_file);
					break;
				}
			}
			
			if (!isset($this->params[$ini_bridge])) {
				return false;
			}
			
			$this->bridge = $this->params[$ini_bridge];
			unset($this->params[$ini_bridge]);
			
			
			$url = $this->bridge[0];
			$this->destiny = end($this->bridge);
			reset($this->bridge);
			
		}

		$this->url = $url;
		$this->origin = true;
		$this->unique = true;
		return true;
	}
	public function _v($v)
	{
		return $this->_param_replace('#' . $v);
	}
	private function _param_replace($arg)
	{
		$arg = (is_object($arg)) ? (array) $arg : $arg;

		if (is_array($arg)) {
			foreach ($arg as $i => $row) {
				$arg[$i] = $this->_param_replace($row);
			}

			return $arg;
		}

		return (strpos($arg, '#') !== false) ? @preg_replace('/\#([A-Z\_]+)/e', '(isset($this->params["$1"])) ? @$this->params["$1"] : "$1"', $arg) : $arg;
	}


}

	function w($a = '', $d = false, $del = 'trim')
	{
		if (empty($a) || !is_string($a)) return array();
		
		$e = explode(' ', $del($a));
		if ($d !== false) {
			foreach ($e as $i => $v) {
				$e[$v] = $d;
				unset($e[$i]);
			}
		}
		
		return $e;

	}

	if (!function_exists('_pre')) {
		function _pre($a, $t = false , $d = false ) {

			if( $t === true ){
				echo '<pre>';
					var_dump($a);
				echo '</pre>';
			}else{
				echo '<pre>';
					print_r($a);
				echo '</pre>';
			}
			
			if ($d === true) {
				exit;
			}
		}
	}
	/**
		encode-decode
	*/
	if (!function_exists('encode')) {
		function encode($str)
		{
			return bin2hex(base64_encode($str));
		}
	}
	if (!function_exists('decode')) {
		function decode($str)
		{
		return base64_decode(hex2asc($str));
		}
	}
	if (!function_exists('hex2asc')) {
		function hex2asc($str) {
			$str2 = '';
			for ($n = 0, $end = strlen($str); $n < $end; $n += 2) {
				$str2 .=  pack('C', hexdec(substr($str, $n, 2)));
			}
			return $str2;
		}
	}

?>