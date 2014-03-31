<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Class Credentials Facebook
 */

class CredentialsFacebook {

	protected $app;
	protected $prefix;

	/**
	* Obtener credemciales de facebook en DB
	*
	* @var array
	* @return null
	*/
	public function __construct($app,$prefix)
	{
		$this->app 			= $app;
		$this->prefix 			= $prefix;
		return NULL;
	}
	
	/**
	* Obtener credenciales facebook de la DB
	*
	* @var Object or array()
	* @return TRUE
	*/
	public function _getParamsFacebook()
	{
		$table_p_property 		= $this->prefix.'admin_params_property';
		$table_p_content 		= $this->prefix.'admin_params_content';
		$response 				= new stdClass();
		$query 					= "SELECT app.value as value, apc.value as label FROM
									{$table_p_property} app 
									INNER JOIN
									{$table_p_content} apc
									ON apc.id = app.id_admin_params_content
									WHERE app.id_admin_params_conf= 10";
		$facebook       		= $this->app['dbs']['mysql_silex']->fetchAll($query);

		foreach ($facebook as $key => $value) {
			$response->{$value['label']} = $value['value'];
		}
		return $response;
	}
	
}

?>