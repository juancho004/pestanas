<?php 
/**
 * Factory .
 *
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Administrador
 */
class modelAdministrator {

	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct(){}

	/**
	* Recibe datos para acutenticacion de login
	*
	* @var array
	* @return String
	*/
	public function _validateParams( $data, $logError, $app,$prefix)
	{
		$empty_params 	= array();
		$params_form 	= array();
		#validando campos vacios
		foreach ($data as $key => $value) {
			#true = vacio o null
			if ( empty( $value['value'] ) ){
				$empty_params[] = $value['name'];
			}
			$params_form[$value['name']] = $value['value'];
		}

		if ( !empty($empty_params) ){
			return 'error';
		} else {
            $params_form['password']=md5($params_form['password']);
			#validar credencieales
				$exist 		= FALSE;
				$query 		= 'SELECT id, name, usertype FROM '.$prefix.'admin_users WHERE `name`= "'.addslashes( htmlspecialchars( strip_tags($params_form['user']))).'" AND `password` = "'.addslashes( strip_tags($params_form['password'])).'" AND block = 1 ';
				$result 	= $app['dbs']['mysql_silex']->fetchAssoc($query);
				if ( !empty($result) ){
					$exist = TRUE;
				}
             


			if( $exist === TRUE ){

				$logError->addLine('admin','login', 'Inicio de sesión correcto', $result['id'] );
				#inicia sesión
				@session_name("loginUsuario");
				@session_start();

				$_SESSION["authenticated"]	= TRUE; #asignar que el usuario se autentico
				$_SESSION["lastaccess"]		= date("Y-n-j H:i:s"); #definir la fecha y hora de inicio de sesión en formato aaaa-mm-dd hh:mm:ss
				$_SESSION["uid"]			= (int)$result['id']; #asigna a session ID de usuario registrado
				$_SESSION["role"]			= (int)$result['usertype']; #asigna a session ID de usuario registrado
				return 'success';
			} else {
				return 'error';
			}
		}
		exit;
	}

/**
	* Obtiene descripcion de menus activos para la app
	*
	* @var array
	* @return object
	*/
	public function _getDescriptionMenuActive($app,$prefix)
	{
		$query 		= 'SELECT title, description FROM '.$prefix.'admin_menu WHERE published = 1 AND parent_id = 1 ORDER BY 	ordering DESC';
		$list 		= $app['dbs']['mysql_silex']->fetchAll($query);

		$html = '<div class="page-header">';
		$html.= '<p>¿Qué tipo parametros puedo configurar?</p>';
		$html.= '<div class="bs-docs-example">';

		$html.= '<div class="tabbable tabs-left">';
		$html.= ' <ul class="nav nav-tabs">';
			foreach ($list as $key => $value) {
				$active = ( $key == 0 )? 'active':'';
				$html.= '<li class="'.$active.'"><a data-toggle="tab" href="#l'.$key.'">'.$value['title'].'</a></li>';
			}
		$html.= '</ul>';
		
		$html.= '<div class="tab-content">';
			foreach ($list as $key => $value) {
				$active = ( $key == 0 )? 'active':'';
				$html.= '<div id="l'.$key.'" class="tab-pane '.$active.'">';
				$html.= '<blockquote>';
				$html.= '<p>'.$value['description'].'</p>';
				$html.= '</blockquote>';
				$html.= '</div>';
			}
		#exit;
		$html.= '</div>';
		$html.= '</div>';
		$html.= '</div>';
		$html.= '</div>';

		return $html;

	}
     
     
/**
* Valida si el archivo htaccess de seguridad existe, sino lo crea
*
*
*/
public function _gen_htaccess()
 {

$htaccess_data = '#>NF1>>###############################################################
# .htaccess for NinjaFirewall (http://www.ninjafirewall.com/)
# Please keep this code on top of your .htaccess
######################################################################
# Some obvious restrictions :
<FilesMatch "(\.ini|\.htaccess|error_log)$">
  Order Allow,Deny
</FilesMatch>
# For Apache mod_php5 only : prepend our firewall :
<IfModule mod_php5.c>
  php_value auto_prepend_file ' .PATH_ROOT. '/admin/ninjafirewall/firewall.php
</IfModule>
# For PHP CGI only : must apply to all subdirectories as well :
<IfModule !mod_php5.c>
  SetEnv PHPRC ' .$_SERVER['DOCUMENT_ROOT']. '/php.ini
</IfModule>
######################################################################
# End of NinjaFirewall .htaccess configuration
#>>NF1>###############################################################
######################################################################
#### SETTING COOKIES PARAMETERS ####
Header edit Set-Cookie ^(.*)$ $1;HttpOnly;Secure
Header set Set-Cookie HttpOnly;Secure

Options -Indexes

<IfModule mod_rewrite.c>
 RewriteEngine On
### PREVENT DIRECT ACCESS TO SILEX DIRECTORIES #####
    RewriteRule templates/ web/ [R=400]
    RewriteRule config/ web/ [R=400]
    RewriteRule install/ web/ [R=400]
    RewriteRule logs/ web/ [R=400]
    RewriteRule language/ web/ [R=400]
    RewriteRule src/ web/ [R=400]
    RewriteRule vendor/ web/ [R=400]
### MAKING FRIENDLY URLS #####
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ web/index.php [QSA,L]
### PREVENTING HOT LINKING  #####
    RewriteCond %{HTTP_REFERER} !^$
    RewriteCond %{HTTP_REFERER} !^(http|https)://(www\.)?websiteguatemala.com/.*$ [NC]
    RewriteRule \.(gif|jpg|js)$ - [R=400]
</IfModule>

########## INICIO- Bloquear cualquier script que trate de establecer un valor
##mosConfig a través de una URL
RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|\%3D) [OR]
';

	   $nuevoarchivo = @fopen(PATH_ROOT.'/.htaccess', "w+");
       @fwrite($nuevoarchivo,"{$htaccess_data}");
	   @fclose($nuevoarchivo);


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
		@session_name("loginUsuario");
		@session_start();
		$response 			= new stdClass();
		$response->redirect = FALSE;

		#validar que el usuario esta logueado
		if ( !($_SESSION["authenticated"]) ) {

			$response->redirect = TRUE;
			$response->url 		= '/index.php/login';

		} else {
			
			$fechaGuardada 			= $_SESSION["lastaccess"];
			$ahora 					= date("Y-n-j H:i:s");
			$tiempo_transcurrido 	= (strtotime($ahora)-strtotime($fechaGuardada));

			#comparar el tiempo transcurrido 
			if($tiempo_transcurrido >= 600) {

				#si el tiempo es mayo del indicado como tiempo de vida de la session
				@session_destroy(); #destruir la sesión y se redirecciona a lagin
				$response->redirect = TRUE;
				$response->url 		= '/index.php/login';
				#sino, se actualiza la fecha de la session

			}else {

				#actualizar tiempo de session
				$_SESSION["lastaccess"] = $ahora;
				$response->redirect 	= FALSE;
				$response->url 			= '/index.php/?op=administrator';

			}
		}
		return $response;
	}

	/**
	* Obtener items de menu administrativos
	*
	* @var array
	* @return object
	*/
	public function _getMenuAdministrator($app,$prefix)
	{
     
		$port = ( $_SERVER['SERVER_PORT'] != 443 )? 'http://':'https://';
		$url = str_replace('/index.php', '', $port.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'] );
         

        $rol=$_SESSION["role"];
   

		$response 	= new stdClass();
		$query 		= 'SELECT * FROM '.$prefix.'admin_menu WHERE `parent_id`= 0 AND `published` = 1 AND `ordering` = 0 AND level='.$rol;
		$list 		= $app['dbs']['mysql_silex']->fetchAll($query);
	

		$menu = '<div class="navbar navbar-inverse">';
		$menu.= '<div class="navbar-inner">';
		$menu.= '<div class="container">';
		$menu.= '<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">';
		$menu.= '<span class="icon-bar"></span>';
		$menu.= '<span class="icon-bar"></span>';
		$menu.= '<span class="icon-bar"></span>';
		$menu.= '</a>';
		$menu.= '<a class="brand" href="#" onclick="javascript:window.location.href=\'../index.php/?op=administrator\'" >TPP</a>';
		
		$menu.= '<div class="nav-collapse collapse">';
		$menu.= '<ul class="nav">';
		/*Se modifico forma de obtener los items LDONIS*/
		foreach ($list as $key => $value) {
			 $menu.='	<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$value['title'].'<b class="caret"></b></a>
			            <ul class="dropdown-menu">';

            $query 		= 'SELECT * FROM '.$prefix.'admin_menu WHERE `parent_id`= '.$value['id'] .' AND `published` = 1 AND `ordering` = 0 AND level='.$rol;
		    $listh 		= $app['dbs']['mysql_silex']->fetchAll($query);      
		    foreach ($listh as $key => $valueh) {

			      if($valueh['id']=='4')
			        {
			         $menu.='<li class="dropdown-submenu"><a href=\''.$valueh['link'].'\'>'.$valueh['title'].'</a>';
                        $query 		= 'SELECT * FROM '.$prefix.'admin_menu WHERE `parent_id`= '.$valueh['id'] .' AND `published` = 1 AND `ordering` = 0 AND level='.$rol;
		    			$listhGA 		= $app['dbs']['mysql_silex']->fetchAll($query); 
		    			$menu.='<ul class="dropdown-menu">'; 
                        foreach ($listhGA as $key => $valuehGA) {
                         	$menu.='<li><a href=\''.$valuehGA['link'].'\'>'.$valuehGA['title'].'</a></li>'; 		     
                         } 
                        $menu.='</ul></li>';
                    }
                  else
                    $menu.='<li><a href=\''.$valueh['link'].'\'>'.$valueh['title'].'</a></li>'; 		     
		     }//fin del for menus hijos    
          $menu.= '</li></ul>';
		} // fin del for menu padre
        /*End*/
        if($rol==0)
         {	
        	$menu.='<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">htaccess<b class="caret"></b></a>';
			$menu.='<ul class="dropdown-menu">';
			$menu.='<li><a href="./htaccess">Generar</a></li></ul></li>';
		 }
		$menu.= '<li><a href="#" onclick="javascript:window.location.href=\'../index.php/?op='.encode('sessionclose').'\'" title="">Cerrar Sesión</a></li>';
		$menu.= '</ul>';
		$menu.= '</div>';
		$menu.= '</div>';
		$menu.= '</div>';
		$menu.= '</div>';
	
	    
		return $menu;
	}

	/**
	* Obtener Terminos y condiciones
	*
	* @var object
	* @var int
	* @return object
	*/
	public function _getTableTermsAndConditions($app, $idmenu,$prefix)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Términos y condiciónes";

		#obtener terminos y condiciones
		$query 			= 'SELECT value FROM `'.$prefix.'admin_termsandconditions` ORDER BY registger_date DESC';
		$terms 			= $app['dbs']['mysql_silex']->fetchAssoc($query);
		$terms 			= utf8_encode($terms['value']);

		$html = '<form id="form-params" class="row-fluid ">';
		//$html.= '<input type="hidden" name="task" value="terms">';
		$html.= '<input type="hidden" name="id_param" value="'.(int)$idmenu.'"/>';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';
		$html.= '<textarea id="terms-content" rows="25" style="width:100%" name="terms">'.$terms.'</textarea>';
		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;
		return $optionmenu;
	}

	/**
	* Obtener background aplicacion
	*
	* @var object
	* @var int
	* @return object
	*/
	public function _getBackground($app, $idmenu,$prefix)
	{
		$optionmenu 	= new stdClass();

		#obtener ID y LABEL de la opcion a configurar
		$query 			= 'SELECT id, label FROM `'.$prefix.'admin_params_conf` WHERE id = '.$idmenu;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);

		$optionmenu->title_form = $params['label'];

		#Obtener campos que le pertenecen a la opcion seleccionada
		$query 			= 'SELECT id, value, label, tooltip FROM `'.$prefix.'admin_params_content` WHERE `id_admin_params_conf` = '.(int)$params['id'];
		$content 		= $app['dbs']['mysql_silex']->fetchAll($query);
		$html = '<form id="formUpload" class="row-fluid " method="post" enctype="multipart/form-data" >';
		$html.= '<input type="hidden" name="id_param" value="'.(int)$params['id'].'"/>';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';
    
       
       
		foreach ($content as $key => $value) {


			$query 					= 'SELECT value FROM '.$prefix.'background_app WHERE id = 1';
			$valInput 				= $app['dbs']['mysql_silex']->fetchAssoc($query);
			$valInput				= str_replace('/admin/index.php/', "", $_SERVER["PHP_SELF"])."/web/img/background/".$valInput['value'];
	   
           
			//$valInput=$_SERVER['DOCUMENT_ROOT']."/web/img/background/".$valInput['value'];
			$file_name_fb_samall 	= explode(' ', $value['label']);
			$file_name_fb_samall 	= @$file_name_fb_samall[0].''.@$file_name_fb_samall[1];

            $html.= '<div id="facebook-admin-box-'.$key.'" class="facebook-admin-box input-prepend ">';
			$html.= '<span class="add-on facebook-admin-title span3"><strong>'.$value['label'].':</strong> <strong class="small-view" >'.$file_name_fb_samall.':</strong> </span><br><br>';
			$html .= '<div class="fileupload fileupload-new" data-provides="fileupload">
					  <div class="fileupload-preview thumbnail" style="width: 235px; height: 200px;">
							<img src="'.$valInput.'"/>
					  </div>
					  <div>
					  <span class="btn btn-file"><span class="fileupload-new">Seleccione Background</span><span class="fileupload-exists">Cambiar</span><input type="file" value="'.$valInput.'"  name="background" /></span>
					  <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Quitar</a>
					  <a class="info-param-tooltip" data-content="'.$value['tooltip'].'" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>
					  </div>
					  </div>';


			$html.= '</div>';
		}

		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;

		return $optionmenu;
	}



	/**
	* Obtener items de configuracion
	*
	* @var object
	* @var int
	* @var string
	* @return object
	*/
	public function _getTableParams($app, $idmenu,$prefix)
	{
		$optionmenu 	= new stdClass();

		#obtener ID y LABEL de la opcion a configurar
		$query 			= 'SELECT id, label FROM `'.$prefix.'admin_params_conf` WHERE id = '.$idmenu;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);

		$optionmenu->title_form = $params['label'];

		#Obtener campos que le pertenecen a la opcion seleccionada
		$query 			= 'SELECT id, value, label, tooltip FROM `'.$prefix.'admin_params_content` WHERE `id_admin_params_conf` = '.(int)$params['id'];
		$content 		= $app['dbs']['mysql_silex']->fetchAll($query);

       
         $html = '<form id="form-params" class="row-fluid ">';
			$html.= '<input type="hidden" name="id_param" value="'.(int)$params['id'].'"/>';
			$html.= '<fieldset>';
			$html.= '<legend></legend>'; 
		
		foreach ($content as $key => $value) {


			$query 					= 'SELECT value FROM `'.$prefix.'admin_params_property` WHERE `id_admin_params_conf`= '.$params['id'].' AND `id_admin_params_content` = '.$content[$key]['id'].' ORDER BY date_register DESC LIMIT 1';
			$valInput 				= $app['dbs']['mysql_silex']->fetchAssoc($query);
			$valInput				= $valInput['value'];
			$file_name_fb_samall 	= explode(' ', $value['label']);
			$file_name_fb_samall 	= @$file_name_fb_samall[0].''.@$file_name_fb_samall[1];
            
           

            switch ($file_name_fb_samall) {
            	case 'TERMS':
					$html.= '<textarea id="terms-content" rows="25" style="width:100%" name="terms">'.$valInput.'</textarea>';
            	break;
            	default:
            	    $html.= '<div id="facebook-admin-box-'.$key.'" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>'.$value['label'].':</strong> <strong class="small-view" >'.$file_name_fb_samall.':</strong> </span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" value="'.$valInput.'"  name="'.$value['value'].'" />';	
					$html.= '<a class="info-param-tooltip" data-content="'.$value['tooltip'].'" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
            	 break;


            }//fin del switch
      
		}//fin del foreach

		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;

		return $optionmenu;
	}



	/**
	* Guardar paramteros de administacion 
	*
	* @var object
	* @var array
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminFormTerms( $app, $dataForm, $logError,$prefix)
	{

		@session_start();
		$terms 	= array();
		$nametb	= $prefix.'admin_termsandconditions';

		foreach ($dataForm as $key => $value) {
			$terms[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}
        
   
		try {

			#ingresar registro en tabla de terminos
			$query 			= "INSERT INTO {$nametb} (value, registger_date ) ";
			$query 			.= 'VALUES( "'.$terms['terms'].'", "'.date("Y-m-d H:i:s").'" )';
			$app['dbs']['mysql_silex']->executeQuery($query);
			$logError->addLine('Admin','sql', "Cambio en parametros de configuracion {$nametb} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}


	/**
	* Guardar paramteros de administacion 
	*
	* @var object
	* @var array
	* @var string
	* @var string
	* @return array
	*/
	public function _saveAdminForm( $app, $dataForm, $logError,$prefix)
	{
		@session_start();
		$id_conf = $dataForm[0]['value'];


		#obtener nombre de configuracion afectada
		$query 			= 'SELECT name FROM `'.$prefix.'admin_params_conf` WHERE `id` = '.(int)$id_conf['id'];
		$name_conf 		= $app['dbs']['mysql_silex']->fetchAssoc($query);
		$name_conf		= $name_conf['name'];

		#registro de campos a ingresar 
		foreach ($dataForm as $key => $value) {
			if($value['name'] != 'id_param' ){
				$data[$value['name']] 	= utf8_decode(addslashes( htmlspecialchars( $value['value'] )));
				#$colum_tab[] 			= $value['name'];
			}
		}

		#limpia registro
		$query 	= 'DELETE FROM `'.$prefix.'admin_params_property` WHERE `id_admin_params_conf` = '.$id_conf;
		$app['dbs']['mysql_silex']->executeQuery($query);
		$logError->addLine('Admin','sql', "Limpiar parametros de configuracion {$name_conf}", $_SESSION["uid"]);
     

		foreach ($data as $key => $value) {
			#obtener id del label content
			$query 			= 'SELECT id FROM `'.$prefix.'admin_params_content` WHERE `value` = "'.$key.'"';
			$label_cont 	= $app['dbs']['mysql_silex']->fetchAssoc($query);

				
				try {

					#ingresar registro en tabla de property
					$query 			= 'INSERT INTO '.$prefix.'admin_params_property (value, date_register, id_admin_params_conf, id_admin_params_content ) ';
					$query 			.= 'VALUES( "'.trim($value).'", "'.date("Y-m-d h:i:m").'", '.trim($id_conf).', '.$label_cont['id'].' )';
					$app['dbs']['mysql_silex']->executeQuery($query);
					$response[] 	= TRUE;

				} catch (Exception $e) {

					$logError->addLine('admin',"{$name_conf}", $e->getMessage(), $_SESSION["uid"]);
					$response[] 	= FALSE;

				}
		}
		$result = array_unique($response);
		if( !$result ){
			return $result;
		} else {
			$logError->addLine('Admin','sql', "Cambio en parametros de configuracion {$name_conf} ", $_SESSION["uid"]);
			return $result;
		}
		
		

	}
	

   /**
	* Guardar Image de Background  [Jorge Martinez]
	*
	* @var object
	* @var string
	* @var object
	* @return bool
	*/
	public function _saveLoginBackground( $app, $imgName, $logError,$prefix )
	{
		@session_start();
		$nametb = $prefix."background_app";
         
		
		try {
			#insertar o actualizar background

			$sql = "select * from {$nametb}";
			$result = $app['dbs']['mysql_silex']->fetchAll($sql);

			if (count($result) == 0 ) {
				$query 			 = "INSERT INTO {$nametb} (value, registger_date ) ";
				$query 			.= 'VALUES( "'.$imgName.'", now() )';
				$app['dbs']['mysql_silex']->executeQuery($query);
				$logError->addLine('Admin','sql', "Cambio en parametros de configuracion {$nametb} ", (int)$_SESSION["uid"]);
			} else {
				$query 			 = "UPDATE {$nametb} SET value = '".$imgName."' , registger_date = now() ";
				$app['dbs']['mysql_silex']->executeQuery($query);
				$logError->addLine('Admin','sql', "Actualizacion en {$nametb} ", (int)$_SESSION["uid"]);
			}
			return TRUE;
		} catch (Exception $e) {
			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;
		}

	}



	/**
	* Valida la existencia de la tabla solicitada
	*
	* @var object
	* @var string
	* @return TRUE || FALSE
	*/
	private function _validateExistTable($app, $tabname)
	{
		$query 		= "SHOW TABLES like '{$tabname}'";
		$existTab 	= $app['dbs']['mysql_silex']->fetchAssoc($query);
		return ( $existTab === FALSE )? FALSE:TRUE;
	}

/*
--------------------------- FUNCIONES DE ADMINISTRACION DE USUARIOS   ------------------------------------------------------

*/
	/**
	* Guardar usuarios nuevos 
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminUsers( $app, $dataForm, $logError,$prefix,$models)
	{
		     
		@session_start();
		$user 	= array();
		$nametb	= $prefix.'admin_users';

		foreach ($dataForm as $key => $value) {
			$user[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}
    
       /*Validacion de los campos ingresados para el registro de los usuarios*/

        if(!$models->_validatemail($user['mailuser']))
        {
           return FALSE;
        
        }

        if($user['roluser']=='-1')
        	return FALSE; 

        /*Fin validaciones campos*/
       

		try {
           $user['passuser']=md5($user['passuser']);
			#guardar registro en la tabla admin_users
			$query 			= "INSERT INTO {$nametb} (name, date_register,block,password,usertype,mail ) ";
			$query 			.= 'VALUES( "'.$user['uname'].'", "'.date("Y-m-d H:i:s").'",1,"'.$user['passuser'].'",'.$user['roluser'].',"'.$user['mailuser'].'")';
			$app['dbs']['mysql_silex']->executeQuery($query);
			$logError->addLine('Admin','sql', "Cambio en parametros de configuracion {$nametb} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}//fin de la funcion _saveAdminUsers

	/**
	* Actualizar usuario 
	* [LDONIS]
	* @var obect
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveEditUsers( $app, $dataForm, $logError,$prefix,$models)
	{ 


		@session_start();
		$user 	= array();
		$nametb	= $prefix.'admin_users';

		foreach ($dataForm as $key => $value) {
			$user[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}
    

       /*Validacion de los campos ingresados para el registro de los usuarios*/

        foreach ($user as $key => $value) {
		#true = vacio o null
		if ( empty( $value ) ){
			return FALSE;
		  }
	    } 


        if(!$models->_validatemail($user['mail']))
        {
           return FALSE;
        
        }
        /*Fin validaciones campos*/
       

		try {
           $user['password']=md5($user['password']);
			#guardar registro en la tabla admin_users
            $update=$app['dbs']['mysql_silex']->update($nametb, array('name' => $user['name'],'block'=>'1','usertype'=>$user['roluser'],'password'=>$user['password'],'mail'=>$user['mail']), array('id' => $user['id']));
			
			$logError->addLine('Admin','sql', "Actualizacion de usuario {$user['id']} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveEditUsers

/*
-----------------------FUNCIONES DE ADMINISTRACION DE MENUS----------------------------------------------
*/
/**
	* Actualizar menu 
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminMenu( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'admin_menu';

		foreach ($dataForm as $key => $value) {
			$menu[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            /*Se crea el menu */
			$nametb=$prefix.'admin_menu';
			$query = "INSERT INTO {$nametb} (title,link,description,parent_id,published,ordering,access,level) ";
			$query .= 'VALUES( "'.$menu['title'].'","'.$menu['link'].'","",0,1,0,0,'.$menu['roluser'].')';
			
			$app['dbs']['mysql_silex']->executeQuery($query); 
			$logError->addLine('Admin','sql', "Creacion de menu ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveAdminMenu


/**
	* Actualizar menu 
	* [LDONIS]
	* @var array
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveEditMenu( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'admin_menu';

		foreach ($dataForm as $key => $value) {
			$menu[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            $update=$app['dbs']['mysql_silex']->update($nametb, array('title' => $menu['title'],'level'=>$menu['roluser']), array('id' => $menu['id']));
			   //se actualizan los items asociado con el level del menu
		   	$query="SELECT * FROM  `".$prefix."admin_menu` WHERE  parent_id =".$menu['id'];
		    $menus=$app['dbs']['mysql_silex']->fetchAll($query); 
		    foreach ($menus as $key => $value) {
		      $del=$app['dbs']['mysql_silex']->update($prefix.'admin_menu',array('level' => $menu['roluser']),array('id' => $value['id'])); 
		    }
			$logError->addLine('Admin','sql', "Actualizacion de Menu {$user['id']} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}//fin de la funcion _saveEditMenu



/*
--------------------------- FUNCIONES DE ADMINISTRACION DE ITEMS  ------------------------------------------------------

*/

 /**
	* Actualizar menu 
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveEditItem( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'admin_params_conf';

		foreach ($dataForm as $key => $value) {
			$menu[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            $update=$app['dbs']['mysql_silex']->update($nametb, array('name' => $menu['name'],'label'=>$menu['label']), array('id' => $menu['id']));
			
			$logError->addLine('Admin','sql', "Actualizacion de menu de configuracion {$user['id']} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveEditItem

	/**
	* Actualizar menu 
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminItem( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'admin_params_conf';

		foreach ($dataForm as $key => $value) {
			$menu[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            /*Se crea el parametro de configuracion*/
			$query = "INSERT INTO {$nametb} (name, label,date_register) ";
			$query .= 'VALUES( "'.$menu['title'].'","'.strtoupper($menu['title']).'", "'.date("Y-m-d H:i:s").'")';
			$app['dbs']['mysql_silex']->executeQuery($query);
			$id=$app['dbs']['mysql_silex']->lastInsertId();
			$logError->addLine('Admin','sql', "Creacion de item de configuracion ", (int)$_SESSION["uid"]);
                
            /*Se obtiene el level del menu al cual se asociara el item de configuracion*/
            $query  = 'SELECT level FROM `'.$prefix.'admin_menu` where id='.$menu['menuroot'];
            $list 	= $app['dbs']['mysql_silex']->fetchAll($query); 
            $level=$list[0]['level']; 


            /*Se crea el menu para poder acceder al parametro de configuracion creado en el paso anterior*/
			$nametb=$prefix.'admin_menu';
			$query = "INSERT INTO {$nametb} (title,link,description,parent_id,published,ordering,access,level) ";
			$query .= 'VALUES( "'.$menu['title'].'","option-'.$id.'","'.$menu['Description'].'",'.$menu['menuroot'].',1,0,0,'.$level.')';
			
			$app['dbs']['mysql_silex']->executeQuery($query); 
			$logError->addLine('Admin','sql', "Creacion de menu de configuracion ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveAdminItem

	/**
	* Crear campo formulario FB
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminCampoFB( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'user_form_type';

		foreach ($dataForm as $key => $value) {
			$campo[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            /*Se crea el parametro de configuracion*/
			$query = "INSERT INTO {$nametb} (name,label,date_register,block) ";
			$query .= 'VALUES( "'.$campo['name'].'","'.$campo['label'].'", "'.date("Y-m-d H:i:s").'",0)';
			$app['dbs']['mysql_silex']->executeQuery($query);
			$logError->addLine('Admin','sql', "Creacion de campo de formulario de FB ", (int)$_SESSION["uid"]);

			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveAdminCampoFB

/**
	* Actualizar menu 
	* [LDONIS]
	* @var object
	* @var array
	* @var object
	* @var string
	* @var object
	* @return array
	*/
	public function _saveAdminEditCampoFB( $app, $dataForm,$logError,$prefix,$models)
	{ 


		@session_start();
		$nametb	= $prefix.'user_form_type';

		foreach ($dataForm as $key => $value) {
			$campo[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

		try {
           
            $update=$app['dbs']['mysql_silex']->update($nametb, array('name' => $campo['name'],'label'=>$campo['label']), array('id' => $campo['id']));
			
			$logError->addLine('Admin','sql', "Actualizacion de campo de formulario FB {$campo['id']} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion user_saveAdminEditCampoFB
}


?>
