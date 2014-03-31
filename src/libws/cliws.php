<?php

// Include the NuSOAP class file:
require_once('class.nusoap.php');
require_once('class.xml.php');
require_once('class.blowfish.php');

class libws extends blowfish {
	private $ws;
	private $url;
	private $origin;
	private $client;
	private $destiny;
	private $server;
	private $bridge;
	private $params;
	private $unique;
	private $namespace;
	private $object;
	private $type;
	
	public function __construct($url = '') {
		if (is_array($url)) {
			$this->params = $this->bridge = $url;
			$url = $url[0];
		}
		
		if (strpos($url, '://') === false) {
			$url = (!empty($url)) ? $url : 'libws';
			
			if (strpos($url, ':') !== false) {
				$ini_bridge_part = explode(':', $url);
				
				$url = $ini_bridge_part[0];
				$ini_bridge = $ini_bridge_part[1];
			} else {
				$ini_bridge = $url;
			}

			$ini_bridge = strtoupper($ini_bridge);
			$ini_file_path = dirname(__FILE__) . '/';

			foreach (ws(' ./ ../', false, 'rtrim') as $path) {
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
  
		foreach (ws('?wsdl mysql:// oracle:// php:// facebook:// email://') as $row) {
			if (!is_array($url) && strpos($url, $row) !== false) {
				$this->type = preg_replace('/[^a-z]/', '', $row);
				break;
			}
		}

		$this->url = $url;
		$this->origin = true;
		$this->unique = true;

		return true;
	}

	public function __ws_construct($app, $object, $namespace = '') {
		$this->server = new nusoap_server();
		$this->namespace = (!empty($namespace)) ? $namespace : $this->url;
		$this->object = $object;

		$this->server->configureWSDL($app, $namespace);
		$this->server->wsdl->schemaTargetNamespace = $namespace;

		return;
	}

	public function __ws_method($method, $input, $output) {
		if (!function_exists($method)) {
			$format = 'function %s(%s) { return %s::%s(%s); }';
			$assign = "%s::__combine('%s', '%s', true);";

			$arg = '';
			if (count($input)) {
				$arg = array_keys($input);

				eval(sprintf($assign, $this->object, $method, implode(' ', $arg)));

				$arg = '$' . implode(', $', $arg);
			}

			eval(sprintf($format, $method, $arg, $this->object, $method, $arg));
		}

		$this->server->register($method, $input, $output, $this->namespace . $this->namespace . '/' . $method);
	}

	public function __ws_service() {
		$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : implode("\r\n", file('php://input'));

        $this->server->service($HTTP_RAW_POST_DATA);
	}

	public function __ws_object() {
		return $this->server;
	}

	public function _v($v) {
		return $this->_param_replace('#' . $v);
	}

	private function _filter($response) {
		$a = ws();

		if (!is_array($response)) {
			$response = array($response);
		}

		foreach ($response as $i => $row) {
			$a[$i] = is_array($row) ? $this->_filter($row) : str_replace(ws('&lt; &gt;'), ws('< >'), utf8_encode($row));
		}
		
		return $a;
	}
	
	private function _param_replace($arg) {
		$arg = (is_object($arg)) ? (array) $arg : $arg;

		if (is_array($arg)) {
			foreach ($arg as $i => $row) {
				$arg[$i] = $this->_param_replace($row);
			}

			return $arg;
		}

		return (strpos($arg, '#') !== false) ? preg_replace('/\#([A-Z\_]+)/e', '(isset($this->params["$1"])) ? @$this->params["$1"] : "$1"', $arg) : $arg;
	}
	
	private function _build($ary, $s = true) {
		$query = '';
		foreach ($ary as $i => $row) {
			if (is_array($row)) {
				$i = $row[0];
				$row = $row[1];
			}

			$query .= ((!empty($query)) ? '&' : '') . $i . '=' . urlencode($row);
		}

		return ($s ? '?' : '') . $query;
	}
	
	private function _format($data) {
		if (is_array($data) && isset($data['response'])) {
			$data = $data['response'];
		}
		
		preg_match_all('#([a-z0-9\.]+)\=(.*?)\n#i', $data, $parts);
		
		$details = 'identitydetails.attribute.name';
		$details2 = 'userdetails.attribute.name';
		$values = 'identitydetails.attribute.value';
		$values2 = 'userdetails.attribute.value';
		$attr = 'identitydetails.attribute';

		$open = false;
		$response = ws();
		foreach ($parts[1] as $i => $name) {
			$value = $parts[2][$i];

			switch ($name) {
				case $attr:
					break;
				case $details:
				case $details2:
					if ($open) {
						$response[$open] = '';
						$open = false;
					}

					if (!$open) {
						$open = str_replace(ws('. -'), '_', strtolower($value));
						continue;
					}
					break;
				case $values:
				case $values2:
					if ($open) {
						$response[$open] = $value;
						$open = false;
					}
					break;
				default:
					$name = str_replace(ws('. -'), '_', strtolower($name));
					$response[$name] = $value;
					break;
			}
		}

		return $response;
	}

	private function _format_users($data) {
		if (isset($data['response'])) {
			$data = $data['response'];
		}

		preg_match_all('#([a-z0-9\.]+)\=(.*?)\n#i', $data, $parts);

		return $parts[2];
	}

	public function __enrichment($override = false) {
		static $number;

		$f = 'HTTP_X_NOKIA_MSISDN';
		if ($override !== false) {
			$_SERVER[$f] = $override;
		}

		if (!isset($number)) {
			$number = (isset($_SERVER[$f]) && !empty($_SERVER[$f])) ? $_SERVER[$f] : '';
		}

		preg_match('/(\d{3})(\d+)/i', $number, $part);
		unset($part[0]);

		foreach (ws('1 2') as $i) {
			if (!isset($part[$i])) $part[$i] = '';
		}

		return (object) array_combine(ws('area number'), $part);
	}
	
	public function __claro_is($country, $phone, $by_name = false) {
		$is = $this->IsClaro_Phone(array(
			'user' => '#SMS_USER',
			'pass' => '#SMS_PASS',
			'area' => $country,
			'phone' => $phone
		));
		#$client = new SoapClient("http://wsenviosms.ispdes.local/Send_SMS/Service1.asmx?wsdl");
		#$is = $client->IsClaro_Phone(array('user'=>'#SMS_USER','pass'=>'#SMS_PASS','area'=>$country,'phone'=>$phone));
		#_pre($client);exit;
		#$is->IsClaro_PhoneResult = 1;
		if (isset($is->IsClaro_PhoneResult)) {
			$response = (int) $is->IsClaro_PhoneResult;
				
			if ($by_name !== false) {
				switch ($response) {
					case -1: $response 	= 'ESPE'; break;
					case -104: $response 	= 'ESPE'; break;
					case 1: $response 	= 'PREP'; break;
					case 2: $response 	= 'HIBR'; break;
					case 3: $response 	= 'POST'; break;
				}
			}
			return $response;
		}
		
		return false;
	}

	public function __claro_sms($country, $phone, $message) {
		if ($this->__claro_is($country, $phone)) {
			$sms = $this->Send_SMS(array(
				'user' => '#SMS_USER',
				'pass' => '#SMS_PASS',
				'to_phone' => $country.$phone,
				'text' => $message
			));
			
			return true;
		}
		
		return false;
	}

  
   /**/
	public function __sso_create($email, $password, $fn, $sn) {
		$token = $this->authenticate(array(
			'username' => '#SSO_USER',
			'password' => '#SSO_PASS')
		);

		if (!count($token)) {
			return array('timeout' => true);
		}

		$is_created = false;

		if (isset($token->token_id)) {
			$user = $this->read(array(
				'name' => $email,
				'admin' => $token->token_id)
			);

			if (!isset($user->user_id)) {
				$cn = $fn . ((!empty($fn) && !empty($sn)) ? ' ' : '') . $sn;
		
				$query = array(
					array('identity_name', $email),
					array('identity_attribute_names', 'userpassword'),
					array('identity_attribute_values_userpassword', $password),
					array('identity_attribute_names', 'givenname'),
					array('identity_attribute_values_givenname', $fn),
					array('identity_attribute_names', 'sn'),
					array('identity_attribute_values_sn', $sn),
					array('identity_attribute_names', 'cn'),
					array('identity_attribute_values_cn', $cn),
					array('identity_attribute_names', 'mail'),
					array('identity_attributes_values_mail', $email),
					array('identity_attribute_names', 'inetuserstatus'),
					array('identity_attributes_values_inetuserstatus', 'Active'),
					
					array('identity_realm', '/'),
					array('identity_type', 'user'),
					array('inetuserstatus', 'Active'),
					array('admin', $token->token_id)
				);
				$create = $this->create($query);
				
				$user = $this->read(array(
					'name' => $email,
					'admin' => $token->token_id)
				);

				if (isset($user->user_id)) {
					$this->update(array(
						'identity_name' => $email,
						'identity_attribute_names' => 'mail',
						'identity_attribute_values_mail' => $email,
						'admin' => $token->token_id)
					);
					$user->mail = $email;
					
					$is_created = $user;
				}
			}
			
			$out = $this->logout(array(
				'subjectid' => $token->token_id)
			);
		}
		
		return $is_created;
	}
	
	public function __sso_read($username) {
		$token = $this->authenticate(array(
			'username' => '#SSO_USER',
			'password' => '#SSO_PASS')
		);

		if (!count($token)) {
			return array('timeout' => true);
		}
		
		if (isset($token->token_id)) {
			$user = $this->read(array(
				'name' => $username,
				'admin' => $token->token_id)
			);
			
			$out = $this->logout(array(
				'subjectid' => $token->token_id)
			);
			
			if (isset($user->user_id)) {
				return $user;
			}
		}
		
		return false;
	}
	
	public function __sso_update($username, $name, $value) {
		$token = $this->authenticate(array(
			'username' => '#SSO_USER',
			'password' => '#SSO_PASS')
		);

		if (!count($token)) {
			return array('timeout' => true);
		}
		
		if (isset($token->token_id)) {
			$user = $this->update(array(
				'identity_name' => $username,
				'identity_attribute_names' => $name,
				'identity_attribute_values_' . $name => $value,
				'admin' => $token->token_id)
			);
			
			$out = $this->logout(array(
				'subjectid' => $token->token_id)
			);
			
			return true;
		}
		
		return false;
	}


	
	public function __sso_delete($username) {
		$token = $this->authenticate(array(
			'username' => '#SSO_USER',
			'password' => '#SSO_PASS')
		);

		if (!count($token)) {
			return array('timeout' => true);
		}
		
		if (isset($token->token_id)) {
			$user = $this->delete(array(
				'identity_name' => $username,
				'admin' => $token->token_id)
			);
			
			$out = $this->logout(array(
				'subjectid' => $token->token_id)
			);
			
			return true;
		}
		
		return false;
	}

	public function __sso_search($criteria) {
		$token = $this->authenticate(array(
			'username' => '#SSO_USER',
			'password' => '#SSO_PASS')
		);

		if (!count($token)) {
			return array('timeout' => true);
		}
		
		if (isset($token->token_id)) {
			$user = $this->search(array(
				'name' => $criteria,
				'admin' => $token->token_id)
			);
			
			$out = $this->logout(array(
				'subjectid' => $token->token_id)
			);

			return $user;
		}
		
		return false;
	}
	
	public function _() {
		$this->origin = false;
		$this->unique = false;
		$method = $_REQUEST['_method'];

		unset($_REQUEST['_method']);
		unset($_REQUEST['_chain']);
		unset($_REQUEST['_unique']);

		echo @$this->$method($_REQUEST);
		exit;
	}
	
	public function auth($username, $password, $type = 'basic') {
		return $this->client->setCredentials($username, $password, $type);
	}
	
	public function __call($method, $arg) {
		if (empty($this->url)) {
			error_log('libws: No url is configured.');
			return;
		}

		if (!is_array($arg)) {
			$arg = array($arg);
		}

		if (count($arg) == 1 && isset($arg[0]) && is_array($arg[0])) {
			$arg = $arg[0];
		}

		if (strpos($this->destiny, 'facebook') !== false) {
			$add = array(
				'APPID' => '#APPID',
				'APPSECRET' => '#APPSECRET'
			);
			$arg = array_merge($add, $arg);
		}

		if (isset($arg) && is_array($arg)) {
			$arg = $this->_param_replace($arg);
		} else {
			$arg_cp = $arg;
			$_arg = isset($arg[0]) ? ws($arg[0]) : ws();

			$arg = ws();
			foreach ($_arg as $v) {
				if (isset($_REQUEST[$v])) $arg[$v] = $_REQUEST[$v];
			}

			$arg = (!$arg) ? $arg_cp : $arg;
		}

		$_bridge = $this->bridge;
		$count_bridge = count($_bridge);
		$_url = $this->url;
		$response = null;

		switch ($this->type) {
			case 'wsdl':
				$this->client = new nusoap_client($this->url, true);

				if ($error = $this->client->getError()) {
					echo 'Client error: ' . $error;
					exit;
				}

				$response = $this->client->call($method, $arg);
				
				// Check if there were any call errors, and if so, return error messages.
				if ($error = $this->client->getError()) {
					$response = $this->client->response;
					$response = xml2array(substr($response, strpos($response, '<?xml')));
					
					if (isset($response['soap:Envelope']['soap:Body']['soap:Fault']['faultstring'])) {
						$fault_string = $response['soap:Envelope']['soap:Body']['soap:Fault']['faultstring'];
						
						$response = explode("\n", $fault_string);
						$response = $response[0];
					} else {
						$response = $error;
					}
					
					$response = array(
						'error' => true,
						'message' => $response
					);
				}
				
				$response = json_decode(json_encode($this->_filter($response)));
				break;
			case 'mysql':
				if (isset($arg['_mysql'])) {
					$this->params['_MYSQL'] = $arg['_mysql'];
					unset($arg['_mysql']);
				}

				$connect = (isset($this->params['_MYSQL']) && $this->params['_MYSQL']) ? $this->params['_MYSQL'] : '';

				if (empty($arg)) {
					return false;
				}

				global $db;

				require_once('class.mysql.php');
				$db = new database($connect);

				if (empty($db->message)) {
					switch ($method) {
						case 'sql_field':
						case 'sql_build':
							break;
						default:
							if (count($arg) > 1) {
								$sql = array_shift($arg);
								$arg = sql_filter($sql, $arg);
							}
							break;
					}

					$response = (@function_exists($method)) ? false : array('error' => true, 'message' => $method . ' is undefined');

					if ($response === false) {
						switch ($method) {
							case 'sql_field':
							case 'sql_build':
								extract($arg, EXTR_PREFIX_ALL, 'sf');

								$arg_v = '';
								foreach ($arg as $i => $row) {
									$arg_v .= (($arg_v) ? ', ' : '') . '$sf_' . $i;
								}

								eval('$response = $method(' . $arg_v . ');');
								break;
							default:
								$response = $method($arg);
								break;
						}
					}
				}/* else {
					$response = array('url' => $_url, 'error' => 500, 'message' => $db->message);
				}*/

				if (!empty($db->message)) {
					$response = $db->message;
				}
				break;
			case 'oracle':
				if (isset($arg['_oracle'])) {
					$this->params['_ORACLE'] = $arg['_oracle'];
					unset($arg['_oracle']);
				}

				$connect = (isset($this->params['_ORACLE']) && $this->params['_ORACLE']) ? $this->params['_ORACLE'] : '';

				if (empty($arg)) {
					return false;
				}

				global $db;

				require_once('class.oracle.php');
				$db = new database($connect);

				if (empty($db->message)) {
					switch ($method) {
						case 'sql_field':
						case 'sql_build':
							break;
						default:
							if (count($arg) > 1) {
								$sql = array_shift($arg);
								$arg = sql_filter($sql, $arg);
							}
							break;
					}

					//$response = (@function_exists($method)) ? $method($arg) : array('error' => true, 'message' => $method . ' is undefined');
					$response = (@function_exists($method)) ? false : array('error' => true, 'message' => $method . ' is undefined');

					if ($response === false) {
						switch ($method) {
							case 'sql_field':
							case 'sql_build':
								extract($arg, EXTR_PREFIX_ALL, 'sf');

								$arg_v = '';
								foreach ($arg as $i => $row) {
									$arg_v .= (($arg_v) ? ', ' : '') . '$sf_' . $i;
								}

								eval('$response = $method(' . $arg_v . ');');
								break;
							default:
								$response = $method($arg);
								break;
						}
					}
				}

				if (!isset($response['error']) && is_array($response)) {
					if (isset($response[0]) && is_array($response[0])) {
						foreach ($response as $i => $row) {
							if (is_array($row)) {
								$response[$i] = array_change_key_case($row, CASE_LOWER);
							}
						}
					} else {
						$response = array_change_key_case($response, CASE_LOWER);
					}
				}

				if (!empty($db->message)) {
					$response = $db->message;
				}
				break;
			case 'php':
				if (isset($arg['_php'])) {
					unset($arg['_php']);
				}

				$print = ws();
				switch ($method) {
					case 'tail':
					case 'cat':
						if (!@is_readable($arg[0])) {
							$response = 'Can not read file: ' . $arg[0];
						}
						break;
					case 'ping':
						$arg[1] = '-c' . ((isset($arg[1])) ? $arg[1] : 3);
						break;
				}

				switch ($method) {
					case 'tail':
					case 'cat':
					case 'ping':
						if ($response === null) {
							exec($method . ' ' . implode(' ', $arg), $print);
							$response = implode("\r\n", $print);
						}
						break;
					case 'exec':
						if ($response === null) {
							$method(implode(' ', $arg), $print);
							$response = implode("\r\n", $print);
						}
						break;
					default:
						ob_start();

						if (@function_exists($method) || $method == 'eval') {
							eval(($method == 'eval') ? $arg[0] : 'echo @$method(' . (count($arg) ? "'" . implode("', '", $arg) . "'" : '') . ');');

							$_arg = error_get_last();
						} else {
							$_arg = array('message' => 'PHP Fatal error: Call to undefined function ' . $method . '()');
						}

						$response = (null === $_arg) ? ob_get_contents() : array('url' => $_url . $method, 'error' => 500, 'message' => $_arg['message']);

						ob_end_clean();
						break;
				}
				break;
			case 'facebook':
				if (isset($arg['_facebook'])) {
					unset($arg['_facebook']);
				}

				//header('Content-type: text/html; charset=utf-8');
				require_once('class.facebook.php');

				$facebook = new Facebook(array(
					'appId'  => $arg['APPID'],
					'secret' => $arg['APPSECRET'])
				);
				unset($arg['APPID'], $arg['APPSECRET']);

				try {
					$page = array_shift($arg);
					$page = (is_string($page)) ? '/' . $page : $page;
					
					$req = (isset($arg[0]) && is_string($arg[0])) ? array_shift($arg) : '';
					$req = (empty($req)) ? 'get' : $req;

					$arg = (isset($arg[0])) ? $arg[0] : $arg;

					$response = (!empty($page)) ? (count($arg) ? $facebook->$method($page, $req, $arg) : $facebook->$method($page, $req)) : $facebook->$method();
				} catch (FacebookApiException $e) {
					$response = array(
						'url' => $_url,
						'error' => 500,
						'message' => trim(str_replace('OAuthException: ', '', $e))
					);

					error_log($e);
				}

				unset($facebook);
				break;
			case 'email':
				require_once('class.emailer.php');

				$emailer = new emailer();

				$response = $arg;
				break;
			default:
				$send_var = ws('sso mysql oracle php facebook email');
				$send = new stdClass;

				if ($count_bridge == 1 && $_bridge[0] === $_url) {
					$count_bridge--;
					array_shift($_bridge);
				}

				foreach ($send_var as $row) {
					$val = '_' . strtoupper($row);
					$send->$row = (isset($this->params[$val]) && $this->params[$val]) ? $this->params[$val] : false;

					if (!$count_bridge && ($send->$row || isset($arg['_' . $row]))) {
						$this->type = $row;
					}
				}

				switch ($this->type) {
					case 'sso':
						$this->origin = false;

						$_url .= $method;
						unset($arg['_sso']);
						break;
					default:
						foreach ($send_var as $row) {
							if (isset($send->$row) && !empty($send->$row)) {
								$arg['_' . $row] = $send->$row;
							}
						}

						$arg['_method'] = $method;
						$arg['_unique'] = (!$this->unique) ? $this->unique : 1;
						
						if (isset($_bridge) && count($_bridge)) {
							array_shift($_bridge);
							$arg['_chain'] = implode('|', $_bridge);
						}
						break;
				}

				$_arg = $arg;
				$arg = ($this->type == 'sso') ? $this->_build($arg, false) : __encode($arg);

				$socket = @curl_init();
				@curl_setopt($socket, CURLOPT_URL, $_url);
				@curl_setopt($socket, CURLOPT_VERBOSE, 0);
				@curl_setopt($socket, CURLOPT_HEADER, 0);
				@curl_setopt($socket, CURLOPT_RETURNTRANSFER, 1);
				@curl_setopt($socket, CURLOPT_POST, 1);
				@curl_setopt($socket, CURLOPT_POSTFIELDS, $arg);
				@curl_setopt($socket, CURLOPT_SSL_VERIFYPEER, 0);
				@curl_setopt($socket, CURLOPT_SSL_VERIFYHOST, 1);

				$response = @curl_exec($socket);

				$_curl = new stdClass;
				$_curl->err = @curl_errno($socket);
				$_curl->msg = @curl_error($socket);
				$_curl->inf = (object) @curl_getinfo($socket);
				@curl_close($socket);

				switch ($_curl->err) {
					/**
					If the request has no errors.
					*/
					case 0:
						switch ($this->type) {
							/**
							SSO type
							*/
							case 'sso':
								if (preg_match('#<body>(.*?)</body>#i', $response, $part)) {
									preg_match('#<p><b>description</b>(.*?)</p>#i', $part[1], $status);
									
									$response = array(
										'url' => $_url,
										'error' => $_curl->inf->http_code,
										'message' => trim($status[1])
									);
								} else {
									switch($method) {
										case 'search':
											preg_match_all('/string\=(.*?)\n/i', $response, $response_all);
											$response = $response_all[1];
											break;
										default:
											$response = $this->_format($response);
											break;
									}
								}
								break;
							/**
							Any other type
							*/
							default:
								$_json = json_decode($response);

								if ($_json === null) {
									$response = trim($response);
									$response = (!empty($response)) ? $response : $_curl->inf;

									$_json = $response;
								}
								
								$response = $_json;
								break;
						}
						break;
					/**
					Some error was generated after the request.
					*/
					default:
						$response = array(
							'url' => $_url,
							'error' => 500,
							'message' => $_curl->msg
						);
						break;
				}

				break;
		}

		if (!$this->origin || $this->unique) {
			$response = json_encode($response);
		}

		if (($this->type == 'sso' && $this->unique) || ($this->type != 'sso' && $this->unique)) {
			$response = json_decode($response);
		}

		if (is_array($response) && isset($response[0]) && is_string($response[0]) && strpos($response[0], '<?xml') !== false) {
			$response = array_change_key_case_recursive(xml2array($response[0]));

			$response = json_decode(json_encode($response));
		}

		return $response;
	}

/**
	Functions RBT
*/
/*=============
	Funciones RBT
==============*/
	public function __is_login_joomla( $iduser , $autoredirect=false , $redirect='index.php' , $title='', $message='' )
	{

		switch ($iduser) {
			case '0':
				if ( $autoredirect ) {
					
					$app 	= JFactory::getApplication();
					$link 	= $redirect;
					$msg 	= ( $message == '' || null )? 'Debes de estar registrado':$message;
					
					$app->redirect( $link, $msg, $title );	

				} else {
					return false;
				}
			break;
			
			default:
				return true;
			break;
		}

	}
	public function __exist_phone_rbt( $phone )
	{
		$status;
		//valida telefono este registrado en rbt
		$is_rbt = $this->login(array(
			'event' => array(
				'portalAccount'    => '#PORTALACCOUNT',
				'portalPwd'        => '#PORTALPWD',
				'portalType'       => '1',
				'phoneNumber'      => $phone,
				'pwd'			   => '1'
		)
		));

		switch ($is_rbt->returnCode) {
			case '301003'://Incorrect password
				$status = 1;
			break;
			case '301001'://The subscriber does not exist
				$status = 2;
			break;

			default:
			break;
		}
		$response = array( "returnCode" => $is_rbt->returnCode , "status" => $status );
		$r = (object)$response;
		return $r;
	}
	
	public function __getPwd( $phone )
	{
		//envia password de registro rbt
		$pw = $this->getPwd(array(
				'event' => array(
					'portalAccount'    => '#PORTALACCOUNT',
					'portalPwd'        => '#PORTALPWD',
					'portalType'        => '1',
					'phoneNumber'      	=> $phone
				)
		));
		
		$r = ( $pw->returnCode == '000000' )? '1': ( ( $pw->returnCode == '301028')? 2:0);
		return $r;
	}

	public function __login_register( $phone , $code )
	{
		$login = $this->login(array(
			'event' => array(
				'portalAccount'    => '#PORTALACCOUNT',
				'portalPwd'        => '#PORTALPWD',
				'portalType'       => '1',
				'phoneNumber'      => $phone,
				'pwd'			   => $code
			)
		));
		return $login->returnCode;
	}
	
	public function __orderTone( $phone, $idcontent ){

		$response = $this->orderTone(array(
			'event' => array(
				"portalAccount"  => "#PORTALACCOUNT",
				"portalPwd"      => "#PORTALPWD",
				"portalType"     => "1",
				"moduleCode"     => null,
				"role"           => "1",
				"roleCode"       => $phone,
				"resourceType"   => "1",
				"rankType"       => null,
				"phoneNumber"    => $phone,
				"resourceID"     => $idcontent,
				"resourceCode"   => $idcontent,
				"discount"       => null,
				"productID"      => "2",
				"passID"         => null,
				"toneValidDay"   => null,
				"merchantID"     => null
			)
		));
		$r = (object)array( "returnCode" => $response->returnCode , "transactionID" => $response->transactionID , "phone" => $phone , "idmedia" => $idcontent );
		return $r;
		
	}

	public function __setTonebuyrbt( $phone, $idcontent )
	{
		$response = $this->setTone(array(
		'event' => array(
			"portalAccount"  	  => "#PORTALACCOUNT",
			"portalPwd"      	  => "#PORTALPWD",
			"portalType"          => "1",
			"moduleCode"          => null,
			"role"                => "1",
			"roleCode"            => $phone,
			"calledUserID"        => $phone,
			"calledUserType"      => "1",
			"callerNumber"        => null,
			"description"         => null,
			"endTime"             => null,
			"loopType"            => "2",
			"moodModeID"          => null,
			"overlayFlag"         => null,
			"phoneState"          => null,
			"resourceType"        => "1",
			"setMode"             => "1",
			"setType"             => null,
			"startTime"           => null,
			"timeType"            => "2",
			"toneBoxID"           => $idcontent,
			"toneType"            => null
		)
		));	
	}

	public function __querySetting($phone)
	{
		$response = $this->querySetting(array(
			'event' => array(
				"portalAccount" 	  => "#PORTALACCOUNT",
				"portalPwd"      	  => "#PORTALPWD",
				"portalType"          => "1",
				"calledUserID"        => $phone,
				"calledUserType"      => "1",
				"phoneState"          => null,
				"setMode"             => null,
				"setType"             => null,
				"timeType"            => null,
				"toneType"            => null
			)
		));	
	}

	public function __addGroup($phoneuse , $idgcode=null , $name , $desc ){
	
		$response = $this->addGroup(array(
			'event' => array(
				'portalAccount'		=> 'admin',
				'portalPwd'			=> 'admin',
				'portalType'		=> '1',
				'moduleCode'		=> null,
				'role'	 			=> '1',
				'roleCode'			=> $phoneuse,
				'phoneNumber'		=> $phoneuse,
				'groupCode' 		=> $idgcode,
				'groupName' 		=> $name,
				'description' 		=> $desc
				)
		));

		$r = array( 'groupID' => $response->groupID , 'returnCode' => $response->returnCode );
		return (object)$r;
		 		
	}

	public function __queryGroup( $phone , $groupid=null , $type=false )
	{

		$response = $this->queryGroup(array(
			'event' => array(
				'portalAccount' => 'admin',
				'portalPwd' 	=> 'admin',
				'portalType' 	=> '1',
				'startRecordNum'=> null,
				'endRecordNum' 	=> null,
				'queryType' 	=> '2',
				'phoneNumber' 	=> $phone,
			)
		));

		if ( !$type ) {

			$count = COUNT($response->groupInfos);

			for ( $i=0; $i<$count; $i++) {
				
				if ( $response->groupInfos[$i]->groupID == $groupid ) {
					$gcode = $response->groupInfos[$i]->groupCode;
				}
			}
			return $gcode;
		}

		return $response;

	}

	public function __delGroup( $phone , $groupid , $groupcode )
	{
		$response = $this->delGroup(array(
			'event' => array(
				'portalAccount' => 'admin',
				'portalPwd' 	=> 'admin',
				'portalType' 	=> '1',
				'moduleCode'	=> null,
				'role'	 		=> '1',
				'roleCode' 		=> $phone,
				'phoneNumber'	=> $phone,
				'groupID'	 	=> $groupid,
				'groupCode' 	=> $groupcode,
			)
		));

		return $response->returnCode;
	}

	public function __editGroupMember( $phone , $groupid , $groupcode , $name , $desc )
	{

		$response = $this->editGroup(array(
			'event' => array(
				'portalAccount'		=> 'admin',
				'portalPwd'			=> 'admin',
				'portalType'		=> '1',
				'moduleCode'		=> null,
				'role'	 			=> '1',
				'roleCode'			=> $phone,
				'phoneNumber'		=> $phone,
				'groupID'			=> $groupid,
				'groupCode' 		=> $groupcode,
				'groupName' 		=> $name,
				'description' 		=> $desc
			)
		));

		return $response->returnCode;
	}

	public function __addGroupMember( $p )
	{

		$response 	= $this->addGroupMember(array(
			'event' => array(
				'portalAccount'	=> 'admin',
				'portalPwd'		=> 'admin',
				'portalType'	=> '1',
				'moduleCode'	=> '000000',
				'role'	 		=> '1',
				'roleCode'		=> $p->phone,
				'phoneNumber'	=> $p->phone,
				'groupID'       => $p->groupid,
				'groupCode'     => $p->groupcode,
				'memberNumber'  => $p->fphone,
				'memberName'    => $p->fname,
				'memberDetails' => $p->fdesc
			)
		));
		return $response->returnCode;
	}

	public function __delGroupMember( $phone , $groupid , $memberphone ){

		$response 	= $this->delGroupMember(array(
				'event' => array(
					'portalAccount'	=> 'admin',
					'portalPwd'		=> 'admin',
					'portalType'	=> '1',
					'moduleCode'	=> '000000',
					'role'	 		=> '0',
					'roleCode'		=> '0',
					'phoneNumber'	=> $phone,
					'groupID'       => $groupid,
					'groupCode'     => null,
					'memberNumber'  => array( "0" => $memberphone )
				)
		));
		return $response->returnCode;		
	}


	public function __queryInboxTone($phone)
	{
		$response = $this->queryInboxTone(array(
			'event' => array(
				"portalAccount"		=> "admin",
				"portalPwd"			=> "admin",
				"portalType"		=> "1",
				"startRecordNum"	=> null,
				"endRecordNum"		=> null,
				"queryType"			=> '2',
				'resourceType'		=> '1',
				'phoneNumber'		=> $phone,
				'languageID'		=> null,
				'merchantID'		=> null
			)
		));
		return $response->toneInfos;

	}

	public function __addToneBox( $phone , $toneid , $groupname='default')
	{
		$response = $this->addToneBox(array(
			'event' => array(
				"portalAccount"     =>'admin',
				"portalPwd"         =>'admin',
				"portalType"        =>'1',
				"moduleCode"        =>null,
				"role"              =>'1',
				"roleCode"          =>$phone,
				"objectRole"        =>NULL,
				"objectCode"        =>NULL,
				"name"              =>$groupname,
				"toneBoxCode"       =>null,
				"type"              =>'1',
				"feeType"           =>null,
				"canSplit"          =>null,
				"canUpdate"         =>null,
				"price"             =>null,
				"toneValidDay"      =>null,
				"description"       =>null,
				"toneID"            =>array(0=>$toneid), 
				"toneCode"          =>null,
				"toneType"          =>array(0=>'1'),
				"toneOffsets"       =>null,
				"toneEndOffsets"    =>null,
				"toneFileNames"     =>null,
				"relativeTime"      =>null,
				"enabledDate"       =>null,
				"loopToneType"      =>null,
				"priceGroupID"      =>null,
				"catalogID"         =>null,
				"languageID"        =>null,
				"uploadType"        =>null,
				"isMixMusicBox"     =>null
				)
		));

	$r = (object)array( "returnCode" => $response->returnCode , "toneBoxID" => $response->toneBoxID );	
	return $r;
	}

	public function __editSetting( $phone , $settingid , $settype , $toneBoxID , $timeType , $callerNumber , $startTime , $endTime , $paramsbt )
	{

		$ta 			= $paramsbt->toneAddress;
		$sn 			= $paramsbt->singerName;
		$tn 			= $paramsbt->toneName;
		$price 			= $paramsbt->price;
		$downtime 		= $paramsbt->downtime;
		$expirestime	= $paramsbt->expirestime;
		
		$response = $this->setTone(array(
			'event' => array(
				"portalAccount"	=> "admin",
				"portalPwd"		=> "admin",
				"portalType"	=> "1",
				"moduleCode"	=> '000000',
				"role"			=> "1",
				"roleCode"		=> $phone,
				"calledUserID"	=> $phone,
				"calledUserType"=> "1",
				"settingID"		=> $settingid,
				"setType"		=> $settype, //4=INVIDUAL, 3=GRUPO, 2=DEFAULT, 1=SISTEMTONE
				"callerNumber"	=> $callerNumber,
				"resourceType"	=> "1",
				"toneBoxID"		=> $toneBoxID,
				"timeType"		=> $timeType,
				"startTime"		=> $startTime, 
				"endTime"		=> $endTime,
				"loopType"		=> "2"
			)
		));
		
		$r = (object)array(
				"returnCode" 	=> $response->returnCode ,
				"settingID" 	=> $response->settingID ,
				"toneboxid" 	=> $toneBoxID ,
				"stime" 		=> $startTime ,
				"etime" 		=> $endTime ,
				"timetype" 		=> $timeType ,
				"settype" 		=> $settype ,
				"toneaddress" 	=> $ta,
				"singername" 	=> $sn,
				"tonename"		=> $tn,
				"price"			=> $price,
				"callernumber"	=> $callerNumber,
				"downtime"		=> $downtime,
				"expirestime"	=> $expirestime
			);
		return $r;
	}	

	public function __setTone( $phone , $callernumber , $addtonebox , $time , $segment , $typeconf ,  $paramsbt , $groupCode )
	{

		$toneboxid  	= $addtonebox->toneBoxID;
		$ta 			= $paramsbt->toneAddress;
		$sn 			= $paramsbt->singerName;
		$tn 			= $paramsbt->toneName;
		$downtime 		= $paramsbt->downtime;
		$expirestime 	= $paramsbt->expirestime;
		$price 			= $paramsbt->price;
		$stime 			= $time->stime;
		$etime 			= $time->etime;
		$timetype 		= $segment;
		$settype 		= $typeconf;
		$callernumber 	= ($settype == '3' )? $groupCode : $callernumber;


		if( $typeconf == 2 ) {

			$response = $this->__setToneBt( $phone , $toneboxid );

		} else { 


			$response = $this->setTone(array(
				'event' => array(
					"portalAccount"       => 'admin',
					"portalPwd"           => 'admin',
					"portalType"          => '1',
					"moduleCode"          => null,
					"role"                => '1',
					"roleCode"            => $phone,
					"calledUserType"      => '1', //1 persona individual, 2 grupo usuario
					"calledUserID"        => $phone,
					"toneBoxID"           => $toneboxid,
					"resourceType"        => '1',
					"setType"             => $settype, //4=INVIDUAL, 3=GRUPO, 2=DEFAULT, 1=SISTEMTONE
					"callerNumber"        => $callernumber,// IDGROUP || PHONE',
					"loopType"            => '2',
					"timeType"            => $timetype,
					"startTime"           => $stime,
					"endTime"             => $etime,
					"toneType"            => null,
					"setMode"             => null,
					"overlayFlag"         => null,
					"description"         => null,
					"moodModeID"          => null,
					"phoneState"          => null
				)
			));
		}
		$r = (object)array(
				"returnCode" 	=> $response->returnCode ,
				"settingID" 	=> $response->settingID ,
				"toneboxid" 	=> $toneboxid ,
				"stime" 		=> $stime ,
				"etime" 		=> $etime ,
				"timetype" 		=> $timetype ,
				"settype" 		=> $settype ,
				"toneaddress" 	=> $ta,
				"singername" 	=> $sn,
				"tonename"		=> $tn,
				"price"			=> $price,
				"callernumber"	=> $callernumber,
				"downtime"		=> $downtime,
				"expirestime"	=> $expirestime
			);
		return $r;
	}

	public function __setToneBt( $phone , $toneboxid )
	{
		$response = $this->setTone(array(
			'event' => array(
				"portalAccount"  	  => "#PORTALACCOUNT",
				"portalPwd"      	  => "#PORTALPWD",
				"portalType"          => "1",
				"moduleCode"          => '000000',
				"role"                => "1",
				"roleCode"            => $phone,
				"calledUserID"        => $phone,
				"calledUserType"      => "1",
				"callerNumber"        => null,
				"description"         => null,
				"endTime"             => null,
				"loopType"            => "1",
				"moodModeID"          => null,
				"overlayFlag"         => null,
				"phoneState"          => null,
				"resourceType"        => "1",
				"setMode"             => "1",
				"setType"             => null,
				"startTime"           => null,
				"timeType"            => "2",
				"toneBoxID"           => $toneboxid,
				"toneType"            => null
			)
		));

		$r = (object)array( "returnCode" => $response->returnCode , "settingID" => $response->settingID );
		return $r;
	}

	public function __delSetting( $settingid , $phoneuse ){

		$response = $this->delSetting(array(
			'event' => array(
				"portalAccount"       => 'admin',
				"portalPwd"           => 'admin',
				"portalType"          => '1',
				"moduleCode"          => null,
				"role"                => '1',
				"roleCode"            => $phoneuse,
				"calledUserID"        => $phoneuse,
				"calledUserType"      => '1', //1 persona individual, 2 grupo usuario
				"settingID"           => $settingid
			)
		));

		return ( $response->returnCode == '000000')? true:false;
	}

	public function __delInboxTone( $personid , $phone, $merchantid='' )
	{

		$response = $this->delInboxTone(array(
			'event' => array(
				'portalAccount'    	=> 'admin',
				'portalPwd'        	=> 'admin',
				'portalType'       	=> '1',
				'moduleCode'		=> '000000',
				'role'				=> '1',
				'roleCode'			=> $phone,
				'phoneNumber'		=> $phone,
				'personId'			=> $personid,
				'merchantID'		=> $merchantid
			)
		));
		return $response->returnCode;
	}

	public function __getValidateCode( $phone ){

		$response = $this->getValidateCode(array(
			'event' => array(
				'portalAccount'    => 'admin',
				'portalPwd'        => 'admin',
				'portalType'       => '1',
				'moduleCode'		=> '000000',
				'role'	 			=> '3',
				'roleCode' 		=> $phone,
				'phoneNumber'      => $phone
			)
		));

		switch ($response->returnCode) {
			
			case '000000':
				$return = 1; #Success
			break;

			case '309123': #The times of registering the service in a day by the subscriber reaches its upper limit.
				$return = 2;
			break;
			
			case '309116':
				$return = 3; #The subscriber type and access type are not set.
			break;

			case '301008': #The state of the subscriber is incorrect, and the subscriber cannot obtain the authentication code. 
				$return = 4;
			break;

			default:
				$return = 0;
			break;
		}
		return $return;
	}

	public function __subscribe( $phone , $validatecode ){
	
		$response = $this->subscribe(array(
			'event' => array(
				'portalAccount'    	=> 'admin',
				'portalPwd'        	=> 'admin',
				'portalType'       	=> '1',
				'role'				=> '1',
				'roleCode'			=> $phone,
				'phoneNumber'      	=> $phone,
				'validateCode'		=> $validatecode
			)
		));
		#$response->returnCode = '301005';
		switch( $response->returnCode ){
			case '000000':
				$cod = array( 'code' => '1' , 'message' => 'Tu número a sido agregado con exito' );
			break;
			
			case '100003':
				#Creating or deleting a subscriber succeeds. 
				$cod = array( 'code' => '2' , 'message' => 'The operation times out' );
			break;
			
			case '100001':
				$cod = array( 'code' => '3' , 'message' => 'Ocurrion un error, intent mas tarde' );
			break;
			
			case '301012':
				$cod = array( 'code' => '4' , 'message' => 'No se a generado código de validación' );
			break;
			
			case '309123':
				$cod = array( 'code' => '5' , 'message' => 'Se ha excedido el número de suscripciones por día' );
			break;
			
			case '301005':
				$cod = array( 'code' => '6' , 'message' => 'Codigo incorrecto' );
			break;
		}
		
		return (object)$cod;
	}

	public function __activateAndPause( $phone , $type )
	{
		$response = $this->activateAndPause(array(
			'event' => array(
				'portalAccount'	=> 'admin',
				'portalPwd'     => 'admin',
				'portalType'    => '1',
				'moduleCode'	=> '000000',
				'role'	 		=> '3',
				'roleCode' 		=> $phone,
				'type'			=> $type, #1=activated, 2=suspended
				'phoneNumber'   => $phone
			)
		));
		return $response->returnCode;
	}

	public function __unSubscribe( $phone ){

		$response = $this->unSubscribe(array(
				'event' => array(
					'portalAccount'    	=> 'admin',
					'portalPwd'        	=> 'admin',
					'portalType'       	=> '1',
					'moduleCode'		=> '000000',
					'role'				=> '1',
					'roleCode'			=> $phone,
					'phoneNumber'      	=> $phone,
					'serviceFlag'		=> '2'
				)
		));
		return $response->returnCode;	
	}

}
/**
	General Functions
*/
//
// General functions
//
#validate login & phone selected
function _islogin($userid,$vns=false) #$vnr validate phone selected
{
	$is_login;
	$session 	= JFactory::getSession();
	$option 	= (int)$vns;
	$ws 		= new libws('ws');
	$usephoneid = $session->get('phoneuseid');

	$is_login = $ws->__is_login_joomla( $userid , true ,'index.php' ,'','Debes de estar registrado para continuar');

	if ( $is_login ) {

		switch ($option) {

			case '1':

				if( !$usephoneid ) {
					$title	= '';
					$app 	= JFactory::getApplication();
					$link 	= 'index.php?option=com_phonebook';
					$msg 	= 'Tienes que tener un numero seleccionado';
					$app->redirect( $link, $msg, $title );
				} else {
					return $is_login;
				}

			break;
			
			default:
				return $is_login;
			break;

		}

	} else {

		return $is_login;

	}
}





 function __iSvalidPos($pos,$pin){

		$res = $this->isValidPos(array(
			'pos' => $pos,
			'pin' => $pin
		));
		return $res;
	}



function _servicesisactive()
{
	#_pre($userid);
	#exit;
}

function _decript($value){

	$valor = explode( 'rbt',_hex2bin($value) );
	$decript = $valor[1];
	return $decript;

}

function _hex2bin( $h ){

	if (!is_string($h)) return null;
	$r='';
	for ($a=0; $a<strlen($h); $a+=2) {
		$r.=chr(hexdec($h{$a}.$h{($a+1)}));
	}

	return $r;
}

function ws($a = '', $d = false, $del = 'trim') {
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

function array_change_key_case_recursive($input, $case = null) {
	if (!is_array($input)) {
		trigger_error("Invalid input array '{$input}'",E_USER_NOTICE); exit;
	}

	// CASE_UPPER|CASE_LOWER
	if (null === $case) {
		$case = CASE_LOWER;
	}

	if (!in_array($case, array(CASE_UPPER, CASE_LOWER))) {
		trigger_error("Case parameter '{$case}' is invalid.", E_USER_NOTICE); exit;
	}

	$input = array_change_key_case($input, $case);
	foreach ($input as $key => $array) {
		if (is_array($array)) {
			$input[$key] = array_change_key_case_recursive($array, $case);
		}
	}

	return $input;
}

function hex2ascd($str) {
	$str2 = '';
	for ($n = 0, $end = strlen($str); $n < $end; $n += 2) {
		$str2 .=  pack('C', hexdec(substr($str, $n, 2)));
	}
	
	return $str2;
}

function encoded($str) {
	return bin2hex(base64_encode($str));
}

function decoded($str) {
	return base64_decode(hex2ascd($str));
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

function __encode($arg) {
	foreach ($arg as $i => $row) {
		$_i = encoded($i);
		$arg[$_i] = encoded(json_encode($row));
		unset($arg[$i]);
	}

	return $arg;
}

function __decode($arg) {
	foreach ($arg as $i => $row) {
		$_i = decoded($i);
		$arg[$_i] = json_decode(decoded($row));
		unset($arg[$i]);
	}
	
	return $arg;
}

function _image_exists($url)
{
	if(getimagesize($url)){
		return 1;
	} else {
		return 0;
	}
}

function _detecting_browser() {

	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	return ( preg_match('/MSIE/i',$u_agent) == 1 )? true:false;
}

///funciones compra de recargas y paquetes costa rica-------------------


	


/**
	SOA
*/

function _hook($name, $args = array(), $arr = false) {
	switch ($name) {
		case 'isset':
			eval('$a = ' . $name . '($args' . ((is_array($args)) ? '[0]' . $args[1] : '') . ');');
			return $a;
			break;
		case 'in_array':
			if (is_array($args[1])) {
				if (_hook('isset', array($args[1][0], $args[1][1]))) {
					eval('$a = ' . $name . '($args[0], $args[1][0]' . $args[1][1] . ');');
				}
			} else {
				eval('$a = ' . $name . '($args[0], $args[1]);');
			}
			
			return (isset($a)) ? $a : false;
			break;
	}
	
	$f = 'call_user_func' . ((!$arr) ? '_array' : '');
	return $f($name, $args);
}

function _prefix($prefix, $arr) {
	$prefix = ($prefix != '') ? $prefix . '_' : '';
	
	$a = ws();
	foreach ($arr as $k => $v) {
		$a[$prefix . $k] = $v;
	}
	return $a;
}

function db_escape_mimic($inp) {
	if (is_array($inp)) {
		return array_map(__METHOD__, $inp);
	}

	if (!empty($inp) && is_string($inp)) {
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
	}

	return $inp;
}

function _htmlencode($str, $multibyte = false) {
	$result = trim(htmlentities(str_replace(array("\r\n", "\r", '\xFF'), array("\n", "\n", ' '), $str)));
	
	if ($multibyte) {
		$result = preg_replace('#&amp;(\#\d+;)#', '&\1', $result);
	}
	$result = preg_replace('#&amp;((.*?);)#', '&\1', $result);
	
	return $result;
}

function _set_var(&$result, $var, $type, $multibyte = false, $regex = '') {
	settype($var, $type);
	$result = $var;

	if ($type == 'string') {
		$result = _htmlencode($result, $multibyte);
	}
}

//
// Get value of request var
//
function v($var_name, $default, $multibyte = false, $regex = '') {
	if (preg_match('/^(files)(\:?(.*?))?$/i', $var_name, $files_data)) {
		switch ($files_data[1]) {
			case 'files':
				$var_name = (isset($files_data[3]) && !empty($files_data[3])) ? $files_data[3] : $files_data[1];
				
				$_REQUEST[$var_name] = isset($_FILES[$var_name]) ? $_FILES[$var_name] : $default;
				break;
		}
	}
	
	if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name]))) {
		return (is_array($default)) ? array() : $default;
	}
	
	$var = $_REQUEST[$var_name];
	
	if (!is_array($default)) {
		$type = gettype($default);
		$var = ($var);
	} else {
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
	}
	
	if (is_array($var)) {
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v) {
			if (is_array($v)) {
				foreach ($v as $_k => $_v) {
					_set_var($k, $k, $key_type);
					_set_var($_k, $_k, $key_type);
					_set_var($var[$k][$_k], $_v, $type, $multibyte);
				}
			} else {
				_set_var($k, $k, $key_type);
				_set_var($var[$k], $v, $type, $multibyte);
			}
		}
	} else {
		_set_var($var, $var, $type, $multibyte);
	}
	
	return $var;
}


########>
/*
function __($url = '') {
	if (!isset($_REQUEST)) {
		exit;
	}

	$_REQUEST = __decode($_REQUEST);
	if (!isset($_REQUEST['_method']) || !isset($_REQUEST['_chain'])) {
		exit;
	}

	return npi(explode('|', $_REQUEST['_chain']))->_();
}
*/

function __($url = '') {
	if (!isset($_REQUEST)) {
		exit;
	}

	$_REQUEST = __decode($_REQUEST);
	if (!isset($_REQUEST['_method']) || !isset($_REQUEST['_chain'])) {
		exit;
	}
	
	$ws = new libws(explode('|', $_REQUEST['_chain']));
	return $ws->_();
}



function npi($url = '') {
	return new libws($url);
}






?>