<?php


class FacebookServiceProvider {

	protected $params_facebook;
	protected $user_profile;
	protected $appid;
	protected $secretid;
	protected $scope;
	protected $urlapp;
	protected $facebook;
	protected $user_facebook;
	protected $app;
	protected $logError;
	protected $prefix;

	public function __construct($app,$prefix,$logError)
	{
		require_once("class.credentials.facebook.php");
		require_once("facebook.php");

		$this->logError 		= $logError;
		$this->prefix 			= $prefix;
		$this->app 				= $app;
		$credentials_facebook 	= new CredentialsFacebook($app,$prefix);
		$this->params_facebook 		= $credentials_facebook->_getParamsFacebook();
		#credenciles facebook
		$this->appid 			= @$this->params_facebook->appid;
		$this->secretid 		= @$this->params_facebook->secretid;
		$this->scope 			= @$this->params_facebook->scope;
		$this->urlapp 			= @$this->params_facebook->urlapp;
		return true;
	}


	/**
	* inicializar conexion con facebook
	* @return object
	*/
	public function _initFacebook()
	{
		
		$facebook = new Facebook(array(
			'appId' 				=> $this->appid,
			'secret' 				=> $this->secretid,
			'fileUpload' 			=> false,
			'cookie' 				=> true,
			'allowSignedRequest' 	=> false
		));
		$facebook->CURL_OPTS['CURLOPT_CONNECTTIMEOUT'] = 30; 
		
		$user 	= $facebook->getUser();

		if ($user) {
			try {
				$this->user_profile = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			}
		}


		if ($user) {
				$logoutUrl = $facebook->getLogoutUrl();
			}else {
				$login_url = $facebook->getLoginUrl( array(
					'scope' => $this->params_facebook->scope
				)); 
			}


		if (!$user) {
			echo "<script type='text/javascript'>top.location.href ='$login_url';</script>";
			exit;
		}

		echo '<script type="text/javascript"> if(top.location==self.location)top.location="'.$this->params_facebook->urlapp.'";</script>';

		$this->facebook 		= $facebook;
		$this->user_facebook 	= $user;

	}

	/**
	* Obtener instancia de facebook
	* @return object
	*/
	public function _getInstanceOfFacebook()
	{
		return $this->facebook;
	}

	/**
	* Obtener informacion de perfil
	* @return object
	*/
	public function _getUserProfileFacebook()
	{
		return $this->user_profile;
	}

	/**
	* Obtener id perfil facebook
	* @return object
	*/
	public function _getUserIdFacebook()
	{
		return $this->user_facebook;
	}

	/**
	* FQL valida si es fan o no de una FanPage
	* @return object
	*/
	public function _isFanOnFanPage()
	{
		$page_id 		= ( empty( $this->params_facebook->fanpage ) )?0:$this->params_facebook->fanpage;
		$fql_query 		= "SELECT uid FROM page_fan WHERE uid = '{$this->user_facebook}' AND page_id = {$page_id}";
		$fql 			= $this->facebook->api(array('method' => 'fql.query', 'query' => $fql_query));
		$isFacebookFan 	= ( count($fql) > 0 )? TRUE:FALSE;

		return $isFacebookFan;
	}

	/**
	* Listado de amigos facebook
	* @return object
	*/
	public function _getListFriendsFacebook()
	{

		$list_friend	= new stdClass();
		$fql_query 		= "SELECT uid2 FROM friend WHERE uid1 = '{$this->user_facebook}' ";
		$fql 			= $this->facebook->api(array('method' => 'fql.query', 'query' => $fql_query));

		foreach ($fql as $key => $value) {
			$list_friend->list_friend[] = $value['uid2'];	
		}
		return $list_friend;
	}

	/**
	* Hacer publicacion en muro
	* @return object
	*/
	public function _publishCommentFacebook( $message = 'No Message' )
	{

		$response 		= new stdClass();
		try {
			$comment 			= $this->facebook->api("/me/feed", 'POST', array('message' => $message ) );
			$response->id_post 	= $comment['id'];
		} catch (FacebookApiException $e) {
			$response->error = $e->getMessage();
			$this->_registerErrorFacebook( $response->error );
		}

		return $response;
	}	

	/**
	* Registra error de facebook en el log de errores, recibe mensaje de error
	* @var string 
	* @return object
	*/
	public function _registerErrorFacebook($message = 'No Message')
	{
		$this->logError->addLine('Aplicatin','Facebook', $message, $this->user_facebook );
		return true;
	}

	/**
	* Registra usuario facebook en tabla de facebook
	* Esta opcion requiere del scope email,user_birthday
	* @return object
	*/
	public function _registerUserFacebook()
	{
		$response 	= new stdClass();
		$tab 		= "{$this->prefix}user_facebook";
		$params 	= array(
						"id_facebook" 	=> @$this->user_profile['id'],
						"first_name" 	=> @$this->user_profile['first_name'],
						"last_name" 	=> @$this->user_profile['last_name'],
						"birthday" 		=> @$this->user_profile['birthday'],
						"gender" 		=> @$this->user_profile['gender'],
						"mail" 			=> @$this->user_profile['email'],
						#"mail2" 		=> @$this->user_profile['email2'],
						"date_register" => date("Y-m-d H:i:s")
					);

		foreach ($params as $key => $value) {
			if( empty($value) ){
				$response->message 	= 'Error, validating parameters.';
				$response->status 	= false; 
				return $response;
			}
		}

		$query 		= "SELECT COUNT(id_facebook) as exist FROM {$tab} WHERE id_facebook = {$this->user_profile['id']} ";
		$exist_user = $this->app['db']->fetchAssoc($query);
		$response->id_user = $this->user_profile['id'];

		switch ($exist_user['exist']) {
			case '0':
				$register = $this->app['db']->insert($tab,$params);
				$response->message = 'New registered user.';
				$response->status 	= true;
			break;
			
			default:
				$response->message = 'Existin user.';
				$response->status 	= false;
			break;
		}
		return $response;
	}

		

}

?>
