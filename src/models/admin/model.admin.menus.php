<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Menus
 */
class modelMenus {
    
	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct(){}


    /**
	* Obtiene el listado de Menus
	* @var object
	* @var string
	* @return array
	*/
	public function _getItems($app,$prefix)
	{
	   
		$query  = 'SELECT id,label FROM `'.$prefix.'admin_params_conf`';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
    	return $list;
	}


    /**
	* Obtiene la lista de roles que se pueden asociar a un usuario
	*
	* @var object
	* @var string
	* @return string
	*/
  private function _getRoles($app,$prefix)
	{
		$count=0; 
		$query  = 'SELECT * FROM `'.$prefix.'admin_roles`';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query); 
		$select="<select name='roluser' id='rol'>";
    	$select .= "<option value=-1>Seleccione un rol</option>";
    	foreach ($list as $key) {
          if($count==0)
           $select .= "<option value=".$key["rol_id"]." selected>".$key["name"]."</option>";
    	  else
    	    $select .= "<option value=".$key["rol_id"].">".$key["name"]."</option>";	
    	 $count+=1;
    	}
    	$html=$select;
    	$html.='</select>';
    	return $html;
	} 

/**
	* Obtiene el listado de Menus
	*
	* @var object
	* @var string
	* @return array
	*/
	public function _getMenus($app,$prefix)
	{
	   
		$query  = 'SELECT id,title,published,r.name FROM `'.$prefix.'admin_menu` m, `'.$prefix.'admin_roles` r where parent_id=0 and m.level=r.rol_id';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
    	return $list;
	}

/*Funcion que realiza el bloqueo de Menu, cambiando su published a 0*/
 /**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 function _blockMenu($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'admin_menu', array('published' =>0), array('id' => $id));                     
   $logError->addLine('Admin Menus',"{admin_users}","Menu Despublicado ".$id, $_SESSION["uid"]);
   return $update;
 }//fin de la funcion blockMenu

/*Funcion que realiza la activacion de menus, cambiando su published a 1*/
 /**
	* 
	*
	* @var array
	* @var int
	* @var string
	* @return boolean
	*/
function _activateMenu($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'admin_menu', array('published' =>1), array('id' => $id));
   $logError->addLine('Admin Menus',"{admin_menu}","Menu Publicado ".$id, $_SESSION["uid"]);
   return $update;
 }//fin de la funcion activeMenu


/**
	* Obtener Vista de Creacion de Menus
	*
	* @var object
	* @var string
	* @return object
	*/
	public function _getTableCreateMenu($app,$prefix)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Crear Menu";
        $html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="create-menu">';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Title:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="title" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Title" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Link:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="link" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Link" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Asociar a:</strong></span>';
					$menu=$this->_getRoles($app,$prefix);
				    $html.= $menu;
					$html.= '<a class="info-param-tooltip" data-content="Menu" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<fieldset>';
		//$campos=$this->_get_fieldsMenu($app,$prefix,$idmenu);
		//$html.=$campos;
		$html.='</fieldset>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';
       
		$optionmenu->form = $html;
		return $optionmenu;
	}


/**
	* Obtener Vista de Edicion de Menus
	*
	* @var object
	* @var string
	* @var int
	* @return object
	*/
	public function _getTableEditMenu($app,$prefix,$idmenu)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Editar Menu";
      
        $query 			= 'SELECT * FROM `'.$prefix.'admin_menu` WHERE id ='.$idmenu;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);
	

		$html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="edit-menu">';
		$html.= "<input type='hidden' name='id' value='$idmenu'>";
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
		$html.= '<span class="add-on facebook-admin-title span3"><strong>Titulo:</strong></span>';
		$html.= '<input type="text" class="span4 facebook-admin-input" name="title" value="'.$params['title'].'" />';
		$html.= '<a class="info-param-tooltip" data-content="Titulo" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
		$html.= '</div>';
		$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
		$html.= '<span class="add-on facebook-admin-title span3"><strong>Asociar a:</strong></span>';
		$menu=$this->_getRoles($app,$prefix);
		$html.= $menu;
		$html.= '<a class="info-param-tooltip" data-content="Menu" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
		$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';

		$optionmenu->form = $html;
		return $optionmenu;
	}


   
/*Funcion para eliminar un Menu*/
/**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 public function _deleteMenu($app,$id,$prefix,$logError)
 {
   try {
   	
      
     //se eliminan los items asociados a un menu principal
   	$query="SELECT * FROM  `".$prefix."admin_menu` WHERE  parent_id =$id";
    $menus=$app['dbs']['mysql_silex']->fetchAll($query); 
    foreach ($menus as $key => $value) {
      $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_menu', array('id' => $value['id'])); 
      $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_params_conf', array('name' =>$value['title'])); 
      $logError->addLine('Admin Items',"{admin_params_conf}","Item del Menu Eliminado ".$id, $_SESSION["uid"]);
    }
     $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_menu', array('id' => $id));
     $logError->addLine('Admin Items',"{admin_menu}","Menu Eliminado ".$id, $_SESSION["uid"]);
	 



   }
   catch(Exception $exception) 
   { 
     return null;
   }  
    return $del;
 }//fin de la funcion deleteMenu





/*Funcion para eliminar un Item de configuracion*/
/**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 public function _deleteItem($app,$id,$prefix,$logError)
 {
   try {
   	 /*Se elimina el item de configuracion, tabla admin_params_conf*/
     $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_params_conf', array('id' => $id));
     $logError->addLine('Admin Items',"{admin_params_conf}","Item Eliminado ".$id, $_SESSION["uid"]);
     
     /*Se elimina el menu que hace referencia al item, tabla admin_menu*/
     $query="SELECT * FROM  `".$prefix."admin_menu` WHERE  link LIKE  '%$id%'";
     $menu=$app['dbs']['mysql_silex']->fetchAssoc($query);
     $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_menu', array('id' => $menu['id']));
     $logError->addLine('Admin Items',"{admin_menu}","Menu Eliminado ".$menu['id'], $_SESSION["uid"]);
	
   }
   catch(Exception $exception) 
   { 
     return null;
   }  
    return $del;
 }//fin de la funcion deleteItem


 	
	/**
	* Obtener Vista de Creacion de Items
	*
	* @var object
	* @var string
	* @return object
	*/
	public function _getTableCreateItem($app,$prefix)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Crear Item";
        $html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="create-item">';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Title:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="title" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Title" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Description:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="Description" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Description" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Asociar a:</strong></span>';
					$menu=$this->_getmenu($app,$prefix);
				    $html.= $menu;
					$html.= '<a class="info-param-tooltip" data-content="Menu" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<fieldset>';
		//$campos=$this->_get_fieldsMenu($app,$prefix,$idmenu);
		//$html.=$campos;
		$html.='</fieldset>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';
       
		$optionmenu->form = $html;
		return $optionmenu;
	}

/**
	* Obtener Vista de Edicion de Items
	*
	* @var object
	* @var string
	* @var int
	* @return object
	*/
	public function _getTableEditItem($app,$prefix,$idmenu)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Editar Item";
      
        $query 			= 'SELECT * FROM `'.$prefix.'admin_params_conf` WHERE id ='.$idmenu;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);
	

		$html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="edit-item">';
		$html.= "<input type='hidden' name='id' value='$idmenu'>";
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Nombre:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="name" value="'.$params['name'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Nombre" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Label:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="label" value="'.$params['label'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Label" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<fieldset>';
		$campos=$this->_get_fieldsMenu($app,$prefix,$idmenu);
		$html.=$campos;
		$html.='</fieldset>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';
        $html.=' <div id="newField" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
   			  <h3>Nuevo Campo</h3>
  			</div>
            <div class="modal-body">
              <form id="field_params"> 
                   <input type="hidden" name="idmenu" value="'.$idmenu.'"/>
                   <div class="facebook-admin-box input-prepend ">
                     <span class="add-on facebook-admin-title span3"><strong>Value</strong></span><input type="text" name="value" class="span4 facebook-admin-input"/>
                   </div>
                   <div class="facebook-admin-box input-prepend ">
                     <span class="add-on facebook-admin-title span3"><strong>Label</strong></span><input type="text" name="label"class="span4 facebook-admin-input"/>
                   </div>
                   <div class="facebook-admin-box input-prepend ">
                     <span class="add-on facebook-admin-title span3"><strong>Tooltip</strong></span><input type="text" name="tooltip"class="span4 facebook-admin-input"/>
                   </div>
                    <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">Cerrar</button>
                    <input type="button" data-dismiss="modal" class="btn btn-primary" onclick="new_field()" value="Guardar"></button>
            </div>
              </form>
            </div>
           
          </div>';
		$optionmenu->form = $html;
		return $optionmenu;
	}

	 /**
	* Obtiene la lista de los menu's raiz
	*
	* @var object
	* @var string
	* @return integer
	*/
	private function _getmenu($app,$prefix)
	{
		$query  = 'SELECT * FROM `'.$prefix.'admin_menu` where parent_id=0';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query); 
		$select="<select name='menuroot' id='rol'>";
    	$select .= "<option value=-1>Seleccione un Menu</option>";
    	foreach ($list as $key) {
          $select .= "<option value=".$key["id"].">".$key["title"]."</option>";
    	}
    	$html=$select;
    	$html.='</select>';
    	return $html;
	}




/**
	* Obtener Lista de Campos de un menu de configuracion
	*
	* @var object
	* @var string
	* @var int
	* @return object
	*/
function _get_fieldsMenu($app,$prefix,$idmenu)
{

   $query 	= 'SELECT * FROM `'.$prefix.'admin_params_content` WHERE id_admin_params_conf ='.$idmenu;
   $params 	= $app['dbs']['mysql_silex']->fetchAll($query);
   $html='';
   $html.='<div id="tabla" class="table_menus">'; 
   $html.='<h4>Configuración <small>Campos</small></h4> <input type="button" data-toggle="modal" class="btn btn-primary" href="#newField" value="Agregar Campo"></input><br/><br/>'; 
   $html.='<table class="table table-hover table-striped">';
   $html.='<th>ID</th>';
   $html.='<th>Value</th>';
   $html.='<th>Label</th>';      
   $html.='<th>Tooltip</th>';
   $html.='<th>Opciones</th>'; 
   foreach ($params as $key => $value) {
     $html.='<tr>';
     $html.='<td>'.$value['id'].'</td>';
     $html.='<td>'.$value['value'].'</td>';
     $html.='<td>'.$value['label'].'</td>';
     $html.='<td>'.$value['tooltip'].'</td>';
     $html.='<td class="text-center"><span class="btn btn-danger btn-small" id="'.$value['id'].'"  onClick="delete_field(this.id)"  title="Eliminar"><i class="icon-remove"></i></span></td>';                          
     $html.='</tr>';

   }
  $html.='</table>';
  $html.='</div>';
  
   return $html;
}//fin de la funcion get_fieldsMenu

/*Funcion para eliminar un campo de configuracion*/
/**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 public function _deleteField($app,$id,$prefix,$logError)
 {
 
   try {
     $del=$app['dbs']['mysql_silex']->delete($prefix.'admin_params_content', array('id' => $id));
     $logError->addLine('Admin Items',"{admin_params_content}","Eliminacion de campo de item de configuracion $id", (int)$_SESSION["uid"]);
   }
   catch(Exception $exception) 
   { 
     return null;
   }  
    return $del;
 }//fin de la funcion deleteField

 /*Funcion para agregar un campo al menu de configuracion*/
/**
	* 
	*
	* @var object
	* @var strin
	* @var object
	* @return boolean
	*/
 public function _addField($app,$prefix,$logError)
 {
           $nametb=$prefix."admin_params_content";
           $dataForm 	= $_POST['form'];
           foreach ($dataForm as $key => $value) {
			  $field[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		   }
    
            /*Valida que los campos no esten vacios*/
        foreach ($field as $key => $value) {
		#true = vacio o null
		if ( empty( $value ) ){
		      return FALSE;
		  }
	    } 

         try {

            $query 			= "INSERT INTO {$nametb} (value,label,tooltip,id_admin_params_conf) ";
			$query 			.= 'VALUES( "'.$field['value'].'", "'.$field['label'].'","'.$field['tooltip'].'",'.$field['idmenu'].')';
			$app['dbs']['mysql_silex']->executeQuery($query);
			$logError->addLine('Admin Items','sql', "Se agrega campo a item de configuracion {$field['idmenu']} {$nametb} ", (int)$_SESSION["uid"]);
            return true;
          }
          catch (Exception $e) {

			$logError->addLine('Admin Items',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

 }//fin de la funcion addField

}
?>
