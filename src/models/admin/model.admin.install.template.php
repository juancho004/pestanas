<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Install
 */
class modelInstall {
    
    protected $app;
    protected $prefix;
    protected $pathtemplate;
	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct($app,$prefix){
        $this->app=$app; 
		$this->prefix=$prefix;
		$this->pathtemplate 		= PATH_WEB.'/templates';
	}


 
/**
	* Instalar template, copia archivos a carpeta de proyecto
	*@var array
	*@var object
	* @return boolean
	*/
 public function _install($file,$logError)
 {
   $installer=new stdClass(); 

   if(strrpos($file["fichero"]["type"],"zip")===false)
   {
   	  $installer->status=false;
      $installer->message="Seleccione un archivo .zip";
      return $installer;
   }

   $file=$file["fichero"]["tmp_name"];
   $nametb=$this->prefix.'template';	 
   $files = @zip_open($file);
	if ($files) {
	  while ($cursor =@zip_read($files)) {
	  	   $name=@zip_entry_name($cursor);
	  	   $file_names[]=$name;
	       if(!is_dir($name)){ 
	        if(strpos($name,".xml"))
	          {
	             if (@zip_entry_open($files, $cursor, "r")) {
		       	      $install=@zip_entry_read($cursor, @zip_entry_filesize($cursor));
		       	      @zip_entry_close($cursor);
	             }
 	          }
	        } 
	  }
	   @zip_close($files);
	 }

        if($install!=null){ 
		     $template =new stdClass();
		     $parser = @xml_parser_create();
		     @xml_parse_into_struct($parser, $install, $tags, $index);
		     @xml_parser_free($parser);
		 
		    foreach ($tags as $key => $value) {
		     	$value['tag']=strtoupper($value['tag']); 
		       
		        switch ($value['tag']) {
		        	case 'NAME':
		        	      $template->name=$value['value'];
		        		break;
		        	case 'FOLDER':
		        		 $folders[]=$value['value'];
		        		break;
		        	case 'FILENAME':
		        		 $archivos[]=$value['value'];
		        		break;
		        }    	    
		        
		     }
		  }
		  else
		   {
             $installer->status=false;
             $installer->message="No existe archivo de instalación";
		     return $installer; 
		   }    
           

      if(!(is_dir($this->pathtemplate.'/template_'.$template->name))){
		     $template->folders=$folders;
		     $template->files=$archivos;
		     $files_install=array_merge($template->files,$template->folders); 
		     /*Se valida que los archivos especificados en el archivo de instalacion existan en el zip*/ 
		     $exist=true;
		     foreach ($files_install as $key => $value) {
		     	 $find=false;
		       	 foreach ($file_names as $keyi => $valuei) {
		       	 	if(strrpos($valuei,'.xml')===false){
		       	 		if(!(strrpos($valuei,$value)===false))
		       	 		     $find=true; 
		       	 	       }
		       	 }
		       	 if(!$find)
		       	 	$exist=false;
		       }  

		   
             if($template->name==null)
             { 
                $installer->status=false;
                $installer->message="No está definido el nombre para el template en el archivo de instalación";
                return $installer; 
             }

		    if($exist)
		    {
		    	@mkdir($this->pathtemplate.'/template_'.$template->name,0777);
		    	/*Se copia el contenido del zip en la carpeta creada*/
		    	/* Descomprimir zip */
			     $zip=new ZipArchive;
			     if ($zip->open($file) === TRUE) {
			     	if(@$zip->extractTo($this->pathtemplate.'/template_'.$template->name)===TRUE)
			     	  {
                        $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('name' =>'template_'.$template->name,'title'=>'template_'.$template->name ,'register_date'=>date("Y-m-d H:i:s"),'state'=>'0'));  
			            $logError->addLine('Admin','sql', "Installation of a new Template", (int)$_SESSION["uid"]);
			     	  	if($insert) {
			     	  		$installer->status=true ;
                            $installer->message="Instalación realizada correctamente";
			     	      }
			     	  }
			     	else{
                      $installer->status=false;
                      $installer->message="No se pudo realizar la instalación";
			     	}
			     		
			     	 $zip->close();
			     	 @chmod($this->pathtemplate.'/template_'.$template->name, 0755);
			     }
			     
		    }
		    else {
		     $installer->status=false;
             $installer->message="Los archivos no concuerdan con los especificados en el archivo de instalación";
            }
        }
        else
        {
        	 $installer->status=false;
             $installer->message="El template ya está instalado";
        }	

       return $installer; 
 }
  

 /**
	* Obtener vista para instalacion de template
	* @return object (HTML)
	*/
	public function _installTemplate()
	{
		$form= new stdClass();
		$form->title_form 	= "Install Template";
	    $html='<form action="'.$this->app['url_generator']->generate('template', array( 'option' => 'installTemplate' ) ).'" method="POST" enctype="multipart/form-data" <br />';
        $html.='<p>Seleccione el template (.ZIP) a instalar<br/>';
        $html.='<input type="file" name="fichero" /><br/><br/>';
        $html.='<input type="submit" class="btn btn-inverse" value="Instalar" /></p><br/>'; 	
	   $form->form=$html;
     return $form;
	}

}
?>
