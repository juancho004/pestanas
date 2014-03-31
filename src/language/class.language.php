<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Clase de Lenguajes
 */

class classLanguage
{
	protected $app; 
    protected $prefix;

	function __construct($app,$prefix){
	  $this->app = $app;
	  $this->prefix = $prefix;
	  //$this->_loadLanguage('es-ES');
	  $this->_getLanguage('es-ES');
	}

	/**
	* Recibe nombre de archivo INI de lenguajes
	* @var String
	* @return define nombre de variables INI y si valor
	*/
	public function _loadLanguage($code)
	{
        $table_p_language	    = $this->prefix.'admin_language';
       //se cambia el campo enable a 0 para todos los lenguajes
       $update=$this->app['dbs']['mysql_silex']->executeQuery("Update {$table_p_language} set enable=0");
       //se estable el lenguaje indicado como default
       $update=$this->app['dbs']['mysql_silex']->update($table_p_language, array('enable' =>1), array('code' => $code));                          
	}//end function

	public function _getLanguage()
	{

        $table_p_property 		= $this->prefix.'admin_language_property';
		$table_p_content 		= $this->prefix.'admin_language_content';
        $table_p_language	    = $this->prefix.'admin_language';


        //Se eliminan palabras del lenguaje anterior
        $query	= "SELECT apc.id AS id, app.value AS value, apc.value AS label
				FROM {$table_p_property} app
				INNER JOIN {$table_p_content} apc ON apc.id = app.id_admin_language_content
				INNER JOIN {$table_p_language} apl ON apl.id = app.id_admin_language
				WHERE apl.enable=1";
        
		$words= $this->app['dbs']['mysql_silex']->fetchAll($query);
        
		//se definen palabras del lenguaje establecido
		foreach ($words as $key => $value) {
		    @define(trim($value['label']),trim($value['value']));
		}
       }
}//end class


?>