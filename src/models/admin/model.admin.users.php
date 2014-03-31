<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Users
 */
class modelUsers {
    
	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct(){}


    /**
	* Obtiene el listado de usuarios
	*
	* @var object
	* @var string
	* @return array
	*/
	public function _getUsers($app,$prefix)
	{
	
		$query  = 'SELECT user.id,user.name,role.name as rol,user.mail,user.block FROM `'.$prefix.'admin_users` user,'.'`'.$prefix.'admin_roles` role where user.usertype=role.rol_id';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
    	return $list;
	}


    
/*Funcion para eliminar usuarios*/
/**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 public function _deleteUser($app,$id,$prefix,$logError)
 {
   try {
     $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_users', array('id' => $id));
     $logError->addLine('Admin Users',"{admin_users}","Eliminacion de usuario ".$id, $_SESSION["uid"]);
   }
   catch(Exception $exception) 
   { 
     return null;
   }  
    return $del;
 }//fin de la funcion deleteUser


 /*Funcion que realiza el bloqueo de usuarios, cambiando su status a 0*/
 /**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 function _blockUser($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'admin_users', array('block' =>0), array('id' => $id));                     
   $logError->addLine('Admin Users',"{admin_users}","Bloqueo de Usuario ".$id, $_SESSION["uid"]);
   return $update;

 }//fin de la funcion blockUser

/*Funcion que realiza la activacion de usuarios, cambiando su status a 1*/
 /**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
function _activateUser($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'admin_users', array('block' =>1), array('id' => $id));
    $logError->addLine('Admin Users',"{admin_users}","Bloqueo de Usuario ".$id, $_SESSION["uid"]);
   return $update;
 }//fin de la funcion activeUser


	 /**
	* Obtiene la lista de roles que se pueden asociar a un usuario
	*
	* @var object
	* @var string
	* @return string (html)
	*/
	private function _getRoles($app,$prefix)
	{
		$query  = 'SELECT * FROM `'.$prefix.'admin_roles`';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query); 
		$select="<select name='roluser' id='rol'>";
    	$select .= "<option value=-1>Seleccione un Rol</option>";
    	foreach ($list as $key) {
          $select .= "<option value=".$key["rol_id"].">".$key["name"]."</option>";
    	}
    	$html=$select;
    	$html.='</select>';
    	return $html;
	}

	/**
	* Obtener Vista de Creacion de Usuarios
	*
	* @var object
	* @var int
	* @var string
	* @return object
	*/
	public function _getTableCreateUser($app, $idmenu,$prefix)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Crear Usuario";
      
        #obtener ID y LABEL de la opcion a configurar
		$query 			= 'SELECT id, label FROM `'.$prefix.'admin_params_conf` WHERE id = '.$idmenu;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);

		#Obtener campos que le pertenecen a la opcion seleccionada
		$query 			= 'SELECT id, value, label, tooltip FROM `'.$prefix.'admin_params_content` WHERE `id_admin_params_conf` = '.(int)$params['id'];
		$content 		= $app['dbs']['mysql_silex']->fetchAll($query);

		$html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="create-user">';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';

        foreach ($content as $key => $value) {


			$query 					= 'SELECT value FROM `'.$prefix.'admin_params_property` WHERE `id_admin_params_conf`= '.$params['id'].' AND `id_admin_params_content` = '.$content[$key]['id'].' ORDER BY date_register DESC LIMIT 1';
			$valInput 				= $app['dbs']['mysql_silex']->fetchAssoc($query);
			$valInput				= $valInput['value'];
			$file_name_fb_samall 	= explode(' ', $value['label']);
			$file_name_fb_samall 	= @$file_name_fb_samall[0].''.@$file_name_fb_samall[1];

            switch ($file_name_fb_samall) {
            	case 'USERTYPE':
            	    $roles=$this->_getRoles($app,$prefix);
					$input= $roles;
            	break;
            	case 'USERPASS':
            		 $input= '<input type="password" class="span4 facebook-admin-input" name="'.$value['value'].'" />';
            		break;
            	default:
            	   $input= '<input type="text" class="span4 facebook-admin-input" name="'.$value['value'].'" />';	
            	 break;


            }//fin del switch	
                    $html.= '<div id="facebook-admin-box-'.$key.'" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>'.$value['label'].':</strong> <strong class="small-view" >'.$file_name_fb_samall.':</strong> </span>';
					$html.= $input;
					$html.= '<a class="info-param-tooltip" data-content="'.$value['tooltip'].'" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';		
		}//fin del foreach

		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;
		return $optionmenu;
	}

/**
	* Obtener Vista de Edicion de Usuarios
	*
	* @var object
	* @var string
	* @var int
	* @return object
	*/
	public function _getTableEditUser($app,$prefix,$iduser)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Editar Usuario";
      
        $query 			= 'SELECT * FROM `'.$prefix.'admin_users` WHERE id ='.$iduser;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);
	
        $roles=$this->_getRoles($app,$prefix);

		$html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="edit-user">';
		$html.= "<input type='hidden' name='id' value='$iduser'>";
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Nombre:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="name" value="'.$params['name'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Nombre" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Rol:</strong></span>';
					$html.= $roles;
					$html.= '<a class="info-param-tooltip" data-content="Rol" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Email:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="mail" value="'.$params['mail'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Email" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Password:</strong></span>';
					$html.= '<input type="password" class="span4 facebook-admin-input" name="password" />';
					$html.= '<a class="info-param-tooltip" data-content="Password" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;
		return $optionmenu;
	}
}
?>
