<?php
date_default_timezone_set("America/Guatemala");
/**
* @version 1.0
* @package logError
*/
class logError{

	protected $existTab;
	protected $app;
	protected $dbname;
	protected $path;
	protected $time_log;

	function __construct($app,$prefix,$type=0)
	{
		#formato de fecha para guardar log
		$this->tablename 	= $prefix."error_log";
		$this->time_log 	= $type; #0=forday,1=for week
		$this->path 		= str_replace('index.php','',$_SERVER['DOCUMENT_ROOT'].str_replace( 'index.php/', '', $_SERVER['SCRIPT_NAME']).'log/');
		$this->app 			= $app;
		$this->existTab 	= $this->_validateExistTabLog();
		return true;
	}
	/**
	* Valida que la tabla de log exista o la crea
	*
	* @var 
	* @return null
	*/
	private function _validateExistTabLog ()
	{
		$query 		= "SHOW TABLES like '{$this->tablename}'";
		$existTab 	= $this->app['dbs']['mysql_silex']->fetchAll($query);

		if ( count($existTab) < 1 ){
			#no exist
			$query = "CREATE TABLE IF NOT EXISTS `{$this->tablename}` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`type_error` varchar(45) COLLATE utf8_spanish2_ci NOT NULL,
					`action` varchar(45) COLLATE utf8_spanish2_ci NOT NULL,
					`description_error` mediumtext COLLATE utf8_spanish2_ci NOT NULL,
					`data_user` longtext COLLATE utf8_spanish2_ci NOT NULL,
					`log_date` datetime NOT NULL,
					`id_user` int(11) NOT NULL,
					PRIMARY KEY (`id`,`id_user`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci AUTO_INCREMENT=1 ;";
			$this->app['dbs']['mysql_silex']->executeQuery($query);
			$this->addLine('sql','create_table', 'init log table', 0);
		}

	}
	/**
	* Recibe Mensaje de error y estructura la nueva linea de archivo log y para DB
	*
	* @var Type String 
	* @var Action String 
	* @var Message String 
	* @var User Id Int 
	* @return null
	*/
	public function addLine ( $type, $action, $message, $uid = 1 )
	{
		$error_logic 			= error_get_last();
		$log 					= new stdClass();
		$log->type_error		= utf8_decode( $type );
		$log->action 			= utf8_decode( $action );
		$log->id_user 			= (int)$uid;
		$log->description_error = addslashes( htmlspecialchars( strip_tags($message) ) );
		$log->data_user			= (object)array( 
									'date'		=> date('r'), 				'host' 		=> $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'],   
									'client'	=> $_SERVER['REMOTE_ADDR'],	'useragent' => $_SERVER['HTTP_USER_AGENT'],
									'message'	=> str_replace("'", "", $error_logic['message']),
									'file' 		=> "PHP ".addslashes( htmlspecialchars( strip_tags($error_logic['file'])))." on line ".$error_logic['line']
									);
		$this->_save($log);
	}
	/**
	* Agraga mensaje de error en archivo de archivo log y DB
	*
	* @var Object 
	* @return null
	*/
	private function _save ($line)
	{	
		#guardar en archivo .log
		$sufix_time 		= ( $this->time_log == 0 )? date("Y-m-d"):$this->_numberOfWeek();
		$line->duser 		= $line->data_user;
		$line->data_user 	= json_encode($line->data_user)."\n";
		$line->log_date		= date('Y-m-d H:m:s');

		
		$register_log = 	"[ate: ".date('r')."] [type: ".$line->type_error."] [action: ".$line->action." ]".
							"[pid: ".getmypid()."] [client: ".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT']."] ".
							"[user: ".$line->id_user."] [ip: ".$line->duser->client."] [useragent: ".$line->duser->useragent."] ".
							"[message: ".$line->description_error."]"."\n";
		
		@error_log($register_log, 3, $this->path."log_".$sufix_time.".log");

		#guardar en DB
		$query = "INSERT INTO {$this->tablename} ( type_error, action, description_error, data_user, log_date, id_user ) ";
		$query.= "VALUES ( '{$line->type_error}', '{$line->action}', '{$line->description_error}', '{$line->data_user}', '{$line->log_date}', '{$line->id_user}' )";
		$this->app['dbs']['mysql_silex']->executeQuery($query);

	}
	/**
	* Obtener numero de semana del mes actual
	*
	* @var 
	* @return Nombre para guardar archivo log por semana
	*/
	private function _numberOfWeek ()
	{
		$response		= '';
		$year			= date("Y");
		$month			= date("m");
		$day			= date("d");
		$date 			= mktime ($hour, $minutes, $seconds, $month, 1, $year);
		$numberOfWeek 	= (int)ceil (($day + (date ("w", $date)-1)) / 7);
		return $numberOfWeek.'_'.date("Y-m");

	}
} 

?>

