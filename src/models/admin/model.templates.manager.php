<?php 
/**
 * @author Jcbarreno <jcbarreno.tpp@gmail.com>
 * @version 1.0
 * @package Modelo administrador de templates
 */
class modelTemplatesManager {

	protected $app;
	protected $prefix;
	protected $pathtemplate;

	public function __construct( $app, $prefix ){
		$this->app 					= $app;
		$this->prefix 				= $prefix;
		$this->pathtemplate 		= PATH_WEB.'/templates';
	}

	/**
	* Obtener listado de templates
	* @return string (html)
	*/
	public function _getTemplateList()
	{
	   $query="SELECT * FROM  `".$this->prefix."template` ";
       $template=$this->app['dbs']['mysql_silex']->fetchAll($query);
        $select="<select name='template' class='setlanguage' id='selecTemp' onchange='changeTemplate(this.value)'>";       
       	$list = '<table class="table table-striped">';
		$list.= '<thead><tr><th>#</th><th>Plantilla</th></tr></thead>';
		$list.= '<tbody>';
        $i 						= 1;
        $class='';

		foreach ($template as $key => $dir) {
      

			
			if( is_dir($this->pathtemplate.'/'.$dir['name']) ):
                $carpeta = @scandir($this->pathtemplate.'/'.$dir['name']);
				if(count($carpeta)>2):		
						if($dir['state']=='1') 
		               {
						 $class='class="alert-success"'; 
					     $selected="selected";
					   }
					   else {
					     $class='';
					     $selected='';
					   }
						$list.= '<tr><td>'.$i.'</td>';
						$list.= '<td><a '.$class.'href="'.$this->app['url_generator']->generate('template', array( 'option' => 'source', 'tpl' => $dir['name'] ) ).'" >'.$dir['name'].'</a></td></tr>';
						$i++;
						$select .= "<option value=".$dir["id"]." ".$selected.">".$dir["name"]."</option>";
		        endif;
		    endif;
		}
      

       $select.='</select><br/><br/>'; 
       $select.=$list;
       $list_template->list 	= $select;
	   $list_template->title 	= "Listado de Plantillas";

		return $list_template;

       
       $list.= '</tbody>';
	   $list.= '</table>';
         /*Lesctura de listado desde un directorio*/
		/*return $this->_readTemplateDirectory();*/
	}

	/**
	* Listar Contentenido del directorio de Templates
	* @return object
	*/
	public function _readTemplateDirectory()
	{

		$list_template 			= new stdClass();
		$directory_templates 	= opendir($this->pathtemplate) or false;
		$i 						= 1;

		if( !$directory_templates ):
			return false;
		endif;

		$list = '<table class="table table-striped">';
		$list.= '<thead><tr><th>#</th><th>Plantilla</th></tr></thead>';
		$list.= '<tbody>';

		while($template_name = @readdir($directory_templates)) {
	
			if ($template_name == '.' || $template_name == '..'):
				continue;
			endif;

			if( is_dir($this->pathtemplate.'/'.$template_name) ):
				$list.= '<tr><td>'.$i.'</td>';
				$list.= '<td><a href="'.$this->app['url_generator']->generate('template', array( 'option' => 'source', 'tpl' => $template_name ) ).'" >'.$template_name.'</a></td></tr>';
				$i++;
			endif;

		}

		$list.= '</tbody>';
		$list.= '</table>';
		closedir($directory_templates);

		$list_template->list 	= $list;
		$list_template->title 	= "Listado de Plantillas";

		return $list_template;

	}

	/**
	* Lista el contenido del template seleccionado
	* @return object
	*/
	public function _getContentTemplate($params)
	{

		$tpl 					= $params['tpl'];
		$path_root 				= $this->pathtemplate.'/'.$tpl.'/';
		$directory_template 	= opendir($this->pathtemplate.'/'.$tpl) or false;

		#obtener directorios y archivos		
		while($template_name = @readdir($directory_template)) {
			if ($template_name == '.' || $template_name == '..'):
				continue;
			endif;

			if( is_dir($template_name) ){
				$sub_dir_name[] = array('dir_name' => $template_name );
			} else {
				$file_name[] 	= array('file_name' => $template_name );
			}
		}

		#instancia del plugin Template Manager
		$initpluginmanager = '<script type="text/javascript">';
		$initpluginmanager.= 'jQuery(document).ready(function(){ ';
		$initpluginmanager.= 'jQuery("#template-manager").templatemanager();';
        $initpluginmanager.= '});';
        $initpluginmanager.= '</script>';
		
		#generar listado de contenidos del tamplate seleccionado
		$menu_template = $initpluginmanager;
		$menu_template.= '<ul id="template-manager" class="nav nav-list" >';
		$menu_template.= '<li class="nav nav-list" ><a href="#"><i class="icon-home icon"></i>'.$tpl.'</a></li>';
		$menu_template.= '<li><ul id="main-sub-dir">';
			
			#desplegar directorios
			foreach ($sub_dir_name as $key => $dir_name ) {
				if( $dir_name['dir_name'] == 'img' ){
					continue;
				}
				$menu_template.= '<li id="dir-'.$dir_name['dir_name'].'" class="dir-content">';
				$menu_template.= '<i class="icon-folder-close"></i>'.$dir_name['dir_name'];
				$menu_template.= '<ul class="close-dir"  >';

				#desplegar contenido de los directorios
				$content_sub_dir 	= opendir($this->pathtemplate.'/'.$tpl.'/'.$dir_name['dir_name'].'/') or false;

					#crear lista de contenido interno Nivel #1
					while($sub_content_name = @readdir($content_sub_dir)) {

						if ($sub_content_name == '.' || $sub_content_name == '..'):
							continue;
						endif;

						$path_file = $this->pathtemplate.'/'.$tpl.'/'.$dir_name['dir_name'].'/'.$sub_content_name;
						if( is_file($path_file) ){
							$menu_template.= '<li><i class="icon-file icon"></i><a href="'.$this->app['url_generator']->generate('template', array('option' => 'content', 'url_file' => encode($path_file) ) ).'" >'.$sub_content_name.'</a></li>';
						}

					}



				$menu_template.= '</ul>';
				$menu_template.= '</li>';
			}
		$menu_template.= '</ul></li>';
		
		#listado de archivos #Nivel 0
		$menu_template.= '<li><ul>';
		 if(count(@$file_name)>0) {
			foreach (@$file_name as $key => $name ) {
				$menu_template.= '<li><i class="icon-file icon"></i><a href="'.$this->app['url_generator']->generate('template', array('option' => 'content', 'url_file' => encode($path_root.$name['file_name']) ) ).'" >'.$name['file_name'].'</a></li>';
			}
	     }		
		$menu_template.= '</ul></li>';
		$menu_template.= '</ul>';

		return $menu_template;

	}


	/**
	* Listar Contentenido del Template seleccionado
	* @var string (nombre del template)
	* @return object
	*/
	public function _redTemplateContent($params)
	{
		$tpl 		= $params['tpl'];
		$dir_css 	= PATH_WEB.'/templates/'.$tpl.'/css/';	
		$list 		= '<ul>';
		foreach(glob($dir_css .'*.css') as $archivo) {
			$list.= '<li> <a href="'.$this->app['url_generator']->generate('template', array('option' => 'content', 'url_file' => encode($archivo) ) ).'" >'.$archivo.'</a></li>';
		}
		$list.= '</ul>';
		
		return $list;
		
	}

	/**
	* Leer y guarda contenido del fichero solicitado del Template
	* Nota: El directorio debera de tener permisos 755 y los archivos 666 \m/
	* @var string
	* @return object
	*/
	public function _editFile($nombre, $tipo, $texto="", $tamanio="")
	{
		$tipo = strtolower($tipo);
		$permiso = array('read'=>'r','replease'=>'w+','save'=>'a+', 'delete'=>'0');
		
		if($permiso[$tipo] != '0'){
			
			if($permiso[$tipo] == 'r'){
				#read
				$read 	= @file_get_contents(decode($nombre));
				$return = '<div>';
				$return.= '<form action="'.$this->app['url_generator']->generate('template', array( 'option' => 'savetemplate' ) ).'" name="templatesave" method="post" >';
				$return.= '<button type="submit" class="btn btn btn-success"><i class="icon-edit icon-white"></i> Guardar</button>';
                $return.= '<button type="button" class="btn btn btn-danger" onclick="javascript:window.history.back();"><i class="icon-edit icon-white"></i> Regresar</button>';
				$return.= '<textarea name="infotemplate" class="editbox" >'.$read.'</textarea>';
				$return.= '<input type="hidden" name="url_file" value="'.$nombre.'" />';
				$return.= '</form>';
				$return.= '<div>';
				return $return;
			} else {
				#save
				$fp = fopen(decode($nombre),$permiso[$tipo]);
				$read = fwrite($fp, $texto);
				fclose($fp);
				return $this->_editFile($nombre, 'read');
			}
		} else {
			$read = unlink(decode($nombre));
			return $read;
		}

	}



/**
	* Cambia el template por default del sitio
	*[LDONIS]
	* @var int
	*/
	public function _changeTemplate($id)
	{
        $table_template	    = $this->prefix.'template';
       //se cambia el campo state a 0 para todos los templates
       $update=$this->app['dbs']['mysql_silex']->executeQuery("Update {$table_template} set state=0");
       //se estable el template indicado como default
       $update=$this->app['dbs']['mysql_silex']->update($table_template, array('state' =>1), array('id' => $id));                          
	}//end function



 /**
	* Obtener template activo
	* @return string
	*/
	public function _getTemplateActive()
	{
	   $query="SELECT * FROM  `".$this->prefix."template` where state=1";
       $template=$this->app['dbs']['mysql_silex']->fetchAssoc($query);
       $path=$template['name'];
       return $path;
	}

}