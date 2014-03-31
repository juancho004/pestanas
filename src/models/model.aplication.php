<?php
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Maestro
 */
class modelAplication {

	protected $mail;
	protected $app;
	protected $smtp;
	protected $prefix;

	/**
	* Recibe instancia de phpMailer
	*
	* @var Instancia phpMailer
	* @return null
	*/
	public function __construct( $phpMailer, $app,$smtp,$prefix)
	{
		#instancia phpMailer
		$this->mail = $phpMailer;
		$this->app 	= $app;
		$this->smtp = $smtp;
		$this->prefix = $prefix;
	}
    //Function para remover un directorio
    public function removeDirectory($path)
	{
		    $path = @rtrim( @strval( $path ), '/' ) ;
		     
		    $d = @dir( $path );
		     
		    if( ! $d )
		        return false;
		     
		    while ( false !== ($current = $d->read()) )
		    {
		        if( $current === '.' || $current === '..')
		            continue;
		         
		        $file = $d->path . '/' . $current;
		         
		        if( @is_dir($file) )
		            $this->removeDirectory($file);
		         
		        if( @is_file($file) )
		            @unlink($file);
		    }
		     
		    @rmdir( $d->path );
		    $d->close();
		    return true;
	 }



	public function debug()
	{
	}

    
   
    /**
    * Funcion que devuelve los terminos y condiciones registrados en la DB
    * 
    *@var string (html)
    */
    public function _getTermsAndConditions()
	{
		$query 			= 'SELECT value FROM `'.$this->prefix.'admin_termsandconditions` ORDER BY registger_date DESC';
		$terms 			= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
		$terms 			= utf8_encode(html_entity_decode($terms['value']));

		return $terms;
	}

	/**
	* Recibe valores para sanitizar
	*
	* @var String || array || Object
	* @var Bool false valor por defecto, true=array,False=string
	* @return retorna valores sanitizados
	*/
	public function _sanitizeVar( $var, $type = false )
	{
		#type = true for array
		$sanitize = new stdClass();
		if ( $type ){

			foreach ($var as $key => $value) {
				$sanitize->$key = $this->_clearString( $value );
			}
			return $sanitize;
		} else {
			return  $this->_clearString( $var );
		}
	}

	
	/**
	* Recibe String para aliminar carcteres especiales
	*
	* @var String
	* @return retorna string libre de caracteres especiales
	*/
	private function _clearString( $string )
	{
		$string = strip_tags($string);
		$string = htmlspecialchars($string);
		$string = addslashes($string);
		#$string = quotemeta($string);
		return $string;
	}



	/**
	* Recibe parametros para armar el correo a enviar
	*
	* @var object
	* @return retorna objeto con estado  y mensaje de envio de mail
	*/
	public function _sendMail( $paramsMail )
	{

		$response = new stdClass();

		$this->mail->SMTPAuth   = false;                  
    	$this->mail->Host       = $this->smtp;    
    	$mail->Port       = 25;   
    	$this->mail->IsSendmail();
		$this->mail->From=$paramsMail->setFrom ;
    	$this->mail->FromName=$paramsMail->setFromName;
		$this->mail->AddAddress($paramsMail->addAddress, $paramsMail->nameAddress);
		$this->mail->Subject    = $paramsMail->subject;
		//$this->mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";#optional
		$this->mail->MsgHTML($paramsMail->body);
		
		$response->status 	= $this->mail->Send();
		$response->msnerror = $this->mail->ErrorInfo;
		return $response;
	}

	/**
	* Recibe fecha en cualquier formato
	*
	* @var string date()
	* @return retorna fecha en formato YY-MM-DD
	*/
	public function _dateFormat( $date )
	{
		return date_format( date_create($date), 'Y-m-d');
	}


	/**
	* Obtener datos de administrador
	*
	* @return object
	*/
	public function _getParamsUserAdmin()
	{
		$response 				= new stdClass();
		$query 					= 'SELECT mail, name FROM '.$this->prefix.'admin_users WHERE `id`= 1 AND `usertype`= 0';
		$useradmin       		= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
		$response->mail 		= $useradmin['mail'];
		$response->name 		= $useradmin['name'];
		return $response;
	} 

/**
Funcion que valida la estructura de un numero de telefono
 * @var string
 * @var integer
 * @return boolean
*/
public function _validatephone($phone,$length)
 {
   if (!(boolean)(preg_match('/^[0-9]{'.$length.','.$length.'}$/', $phone)))
       return false;
   else
      return true;    
 } 


/**
Funcion que valida que el correo tenga la estructura valida
 * @var string
 * @return boolean
*/
public function _validatemail($mail)
 {

   if (!(boolean)(preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9-.]+\.[A-Za-z]{2,4}$/',$mail)))
      return false;
   else
      return true;    
 } 


 /**
 Funcion que genera el paginador, dependiendo de la cantidad de registros y paginas
 * @var integer
 * @var integer
 * @return string(html)  
 *
 **/
public function get_paginador($pages,$page){

    $pag='<div class="pagination pagination-centered"><ul>';
    $pagesToShow=4;

            // Página anterior.
            if ($page>1) { 
                $pa=$page-1;
                $pag.="<li><a  title='Previous' onClick='paginacion($pa,this.title);'> < < Previous </a>"; 
              }
            
            $start = $page - $pagesToShow;

            if ($start <= 0){
                $start = 1;
            }

            $end = $page + $pagesToShow;

            if ($end >= $pages){
                $end = $pages;
            }

            if ($start > 0) {
                for ($i = 1; $i < 4 && $i < $start; ++$i) {
                    $li='<li>'; 
                    $pag.=$li."<a  title='page $i'  onClick='paginacion($i,this.title);'>$i</a></li>";
                }
            }

            if ($start > 2) { 
                    $pag.="<li><a>...</a></li>";
            }

            for ($i = $start; $i <= $end; ++$i) {
               if($i==$page) 
                  $li='<li class="active">';
               else 
                  $li='<li>'; 
               $pag.=$li."<a  title='page $i'  onClick='paginacion($i,this.title);'>$i</a></li>";
            }

            if ($end < $pages - 3) {
                 $pag.="<li><a>...</a></li>";
            }

            if ($end <= $pages - 1) {
                for ($i = max($pages- 2, $end + 1); $i <= $pages; ++$i) {
                    $li='<li>'; 
                    $pag.=$li."<a  title='page $i'  onClick='paginacion($i,this.title);'>$i</a></li>";
                }
            }
            // Siguiente página
            if ($page<$pages) { 
                $pa=$page+1; 
                $pag.="<li><a  title='Next'  onClick='paginacion($pa,this.title);'> Next >> </a>"; 
            }

        $pag.='</ul></div>';

    return $pag;
}//fin de la funcion get_paginador

}
