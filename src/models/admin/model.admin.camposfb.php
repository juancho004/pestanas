<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Campos FB
 */
class modelCamposFB {
    
	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct(){}


    /**
	* Obtiene el listado de Menus
	*
	* @var object
	* @var String
	* @return array
	*/
	public function _getCampos($app,$prefix)
	{
	   
		$query  = 'SELECT id,name,label,block FROM `'.$prefix.'user_form_type`';
        $list 	= $app['dbs']['mysql_silex']->fetchAll($query);
    	return $list;
	}


 /**
	* Obtener Vista de Creacion de Campos
	*
	* @var object
	* @var string
	* @return object
	*/
	public function _getTableCreateCampo($app,$prefix)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Crear Campo Formulario FB";
        $html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="create-campofb">';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Name:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="name" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Name" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Label:</strong></span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="label" value="" />';
					$html.= '<a class="info-param-tooltip" data-content="Label" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
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
	* Obtener Vista de Edicion de Campos
	*
	* @var object
	* @var string 
	* @var int
	* @return object
	*/
	public function _getTableEditCampo($app,$prefix,$idcampo)
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Editar Campo Formulario FB";
      
        $query 			= 'SELECT * FROM `'.$prefix.'user_form_type` WHERE id ='.$idcampo;
		$params 		= $app['dbs']['mysql_silex']->fetchAssoc($query);
	

		$html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="edit-campofb">';
		$html.= "<input type='hidden' name='id' value='$idcampo'>";
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Nombre:</strong> </span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="name" value="'.$params['name'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Nombre" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
					$html.= '<div id="facebook-admin-box class="facebook-admin-box input-prepend ">';
					$html.= '<span class="add-on facebook-admin-title span3"><strong>Label:</strong> </span>';
					$html.= '<input type="text" class="span4 facebook-admin-input" name="label" value="'.$params['label'].'" />';
					$html.= '<a class="info-param-tooltip" data-content="Label" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
					$html.= '</div>';
		$html.= '<legend></legend>';
		$html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
		$html.= '</fieldset>';
		$html.= '</form>';
       
		$optionmenu->form = $html;
		return $optionmenu;
	}




/*
*Funcion para eliminar un campo del formulario de FB*/
/**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 public function _deleteCampo($app,$id,$prefix,$logError)
 {
   try {
   	 /*Se elimina el item de configuracion, tabla admin_params_conf*/
     $del=$app['dbs']['mysql_silex']->delete($prefix.'user_form_type', array('id' => $id));
     $logError->addLine('Admin Campos FB',"{user_form_type}","Campo Eliminado ".$id, $_SESSION["uid"]);
	
   }
   catch(Exception $exception) 
   { 
     return null;
   }  
    return $del;
 }//fin de la funcion deleteCampo



 /*Funcion que realiza el bloqueo de Campo, cambiando su status a 1*/
 /**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 function _blockCampo($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'user_form_type', array('block' =>1), array('id' => $id));                     
   $logError->addLine('Admin Campos FB',"{user_form_type}","Campo Bloqueado ".$id, $_SESSION["uid"]);
   return $update;
 }//fin de la funcion blockCampo

  /*Funcion que realiza el desbloqueo de un Campo, cambiando su status a 0*/
 /**
	* 
	*
	* @var object
	* @var int
	* @var string
	* @var object
	* @return boolean
	*/
 function _activateCampo($app,$id,$prefix,$logError)
 {
   $update=$app['dbs']['mysql_silex']->update($prefix.'user_form_type', array('block' =>0), array('id' => $id));                     
   $logError->addLine('Admin Campos FB',"{user_form_type}","Campo Desbloqueado ".$id, $_SESSION["uid"]);
   return $update;
 }//fin de la funcion blockCampo


}
?>
