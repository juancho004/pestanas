<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Modelo login
 */
class modelLogin extends modelAplication{

	protected $mail;
	protected $app;
	protected $prefix;
	protected $logError;

	/**
	* Recibe instancia de phpMailer
	*
	* @var Instancia phpMailer
	* @return null
	*/
	public function __construct($app, $prefix, $logError )
	{
		#instancia phpMailer
		$this->app 		= $app;
		$this->prefix 	= $prefix;
		$this->logError	= $logError;
	}

	public function _validateUser( $params )
	{
		$params 	= $this->_sanitizeVar($params,true);
		$password 	= md5($params->pass);
		$table 		= "{$this->prefix}admin_users";
		$query 		= "SELECT id, usertype FROM {$table} WHERE name = '{$params->name}' AND password = '{$password}'";
		$user  		= $this->app['dbs']['mysql_silex']->fetchAssoc($query);

		if( !empty($user['id']) ):

			$this->logError->addLine('Admin','Login Web', 'Inicio de sesión Web correcto', $user['id'] );
			#inicia sesión
			@session_name("login_usuario");
			@session_start();

			#registrar inicio de sesion
			$_SESSION["authenticated_user"]	= TRUE; #asignar que el usuario se autentico
			$_SESSION["lastaccess_user"]	= date("Y-n-j H:i:s"); #definir la fecha y hora de inicio de sesión en formato aaaa-mm-dd hh:mm:ss
			$_SESSION["id_user"]			= (int)$user['id']; #asigna a session ID de usuario registrado
			$_SESSION["role_user"]			= (int)$user['usertype']; #asigna a session Tipo de usuario registrado
			return TRUE;

		endif;

		session_destroy();
		return FALSE;
	}

	/**
	* Valida que la session de login continua activa
	*
	* @var boolean
	* @return object
	*/
	public function _validateSessionActive()
	{
		#inicia sesión
		@session_name("login_usuario");
		@session_start();
		$response 			= new stdClass();
		$response->redirect = FALSE;

		#validar que el usuario esta logueado
		if ( !(@$_SESSION["authenticated_user"]) ) {

			#el usuario NO inicio sesion
			$response->redirect = FALSE;
			$response->url 		= 'index.php/login';

		} else {
			#el usuario inicio sesion
			$fechaGuardada 			= $_SESSION["lastaccess_user"];
			$ahora 					= date("Y-n-j H:i:s");
			$tiempo_transcurrido 	= (strtotime($ahora)-strtotime($fechaGuardada));

			#comparar el tiempo transcurrido 
			if($tiempo_transcurrido >= 600) {

				#si el tiempo es mayo del indicado como tiempo de vida de la session
				session_destroy(); #destruir la sesión y se redirecciona a lagin
				$response->redirect = FALSE;
				$response->url 		= 'index.php/login';
				#sino, se actualiza la fecha de la session

			}else {

				#actualizar tiempo de session
				$_SESSION["lastaccess_user"] = $ahora;
				$response->redirect 	= TRUE;
				$response->url 			= 'index.php/home';

			}
		}
		return $response;
	}


}
?>