<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Language
 */
class modelLanguage {
    
    protected $app;
    protected $prefix;
	/**
	* 
	*
	* @var
	* @return null
	*/
	public function __construct($app,$prefix){
        $this->app=$app; 
		$this->prefix=$prefix;
	}


    /**
	* Valida si la opcion pertenece al menu de language
	*
	* @var integer
	* @return array
	*/
	public function _validateOption($id)
	{
	   
		$query="SELECT * FROM  `".$this->prefix."admin_menu` WHERE parent_id=13 and link LIKE  '%$id%'";
        $menu=$this->app['dbs']['mysql_silex']->fetchAssoc($query);
    	return $menu;
	}



        /**
	* Retorna el listado de lenguajes registrados
	*
	* @return array
	*/
	public function _getLanguages()
	{
	  $html='';	
	   $query="SELECT * FROM  `".$this->prefix."admin_language`";
       $languages=$this->app['dbs']['mysql_silex']->fetchAll($query);

        $html.='
            <div class="ttabbable tabs-left">
              <ul class="nav nav-tabs">';
       $select="<select name='language' class='setlanguage' id='lanselec' onchange='changeLanguage(this.value)'>";       
       foreach ($languages as $key => $value) {
            if($value["enable"]=='1') {
               $select .= "<option value=".$value["code"]." selected>".$value["title"]."</option>";
               $html.='<li class="alert-success"><span class="span" onclick="deleteLanguage(\''.$value['id'].'\')"><i class="icon-remove"></i></span><a id="'.$value['id'].'" class="language" data-toggle="tab">'.$value['title'].'</a></li>';
             }
            else{
              $select .= "<option value=".$value["code"].">".$value["title"]."</option>";  
              $html.='<li><span class="span" onclick="deleteLanguage(\''.$value['id'].'\')"><i class="icon-remove"></i></span><a id="'.$value['id'].'" class="language" data-toggle="tab">'.$value['title'].'</a></li>';
            }
        } 
        $select.='</select><br/><br/>';      
        $html.='</ul></div><div class="tab-content">
                 <div id="words">';
        $html.='</div></div>'; 
        $select.=$html;
          return $select;
	}

	   
     /**
	* Save new Language
	* [LDONIS]
	* @var array
	* @var object
	* @return boolean
	*/
	public function _saveLanguage($dataForm,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_language';

		foreach ($dataForm as $key => $value) {
			$language[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     
       
		try {
           
            $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('code' =>$language['code'] ,'title'=>$language['Title'],'date_register'=>date("Y-m-d H:i:s"),'id_param_conf'=>'7'));  
			$logError->addLine('Admin','sql', "Create new language  ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveLanguage



     /**
	* Delete Language
	* [LDONIS]
	* @var integer
	* @var object
	* @return boolean
	*/
	public function _deleteLanguage($id,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_language';
		$nametbcont	= $this->prefix.'admin_language_content';
		$nametbpro	= $this->prefix.'admin_language_property';

		
		try {
           
	        $content=$this->app['dbs']['mysql_silex']->fetchAll("SELECT * FROM {$nametbcont} where id_admin_language= ?",array($id));
	        
	        //se eliminan los registros de las palabras en la tabla admin_language_property
	        foreach ($content as $key => $value) {
	        	$delete=$this->app['dbs']['mysql_silex']->delete($nametbpro, array('id_admin_language_content' => $value['id']));  
	            $logError->addLine('Admin','sql', "Delete word $nametbpro} ", (int)$_SESSION["uid"]);
	        }
	        
	        //se eliminan los registros de las palabras en la tabla admin_language_content
	        $delete=$this->app['dbs']['mysql_silex']->delete($nametbcont, array('id_admin_language' => $id));  
	        $logError->addLine('Admin','sql', "Delete word {$nametbcont} ", (int)$_SESSION["uid"]); 
			
	        //se elimina el lenguage
	        $delete=$this->app['dbs']['mysql_silex']->delete($nametb, array('id' => $id));  
	        $logError->addLine('Admin','sql', "Delete language {$nametb} ", (int)$_SESSION["uid"]); 

			return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _deleteLanguage


 /**
	* Save new word
	* [LDONIS]
	* @var array
	* @var object
	* @return boolean
	*/
	public function _saveWord($dataForm,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_language_content';
		$nametbpro=$this->prefix.'admin_language_property';

		foreach ($dataForm as $key => $value) {
			$word[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     

       
		try {
           
            //se guarda en la tabla admin_language_content
            $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('value' => strtoupper($word['label']) ,'label'=>strtoupper($word['label']),'tooltip'=>strtoupper($word['label']),'id_admin_language'=>$word['idlanguage']));  
            $id=$this->app['dbs']['mysql_silex']->lastInsertId();

            $logError->addLine('Admin','sql', "Create new Word Table Content{$nametb} ", (int)$_SESSION["uid"]);
            
            //Se guarda el valor del campo de lenguaje creado en la tabla admin_language_property
            $insertpro=$this->app['dbs']['mysql_silex']->insert($nametbpro,array('value' =>$word['value'] ,'date_register'=>date("Y-m-d H:i:s"),'id_admin_language'=>$word['idlanguage'],'id_admin_language_content'=>$id));  			
            

			$logError->addLine('Admin','sql', "Create new Word Table Property {$nametbpro} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {
			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveWord


/**
	* Edit Word
	* [LDONIS]
	* @var array
	* @var object
	* @return boolean
	*/
	public function _editWord($dataForm,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_language_content';
		$nametbpro=$this->prefix.'admin_language_property';


		foreach ($dataForm['form'] as $key => $value) {
			$word[$value['name']] = utf8_decode(htmlentities(addslashes( $value['value'] ) ) );
		}     
 
		try {
           
           
            //Se actualiza el valor del campo de lenguaje creado en la tabla admin_language_property
            $update = $this->app['dbs']['mysql_silex']->update($nametb,array('value' => strtoupper($word['label']) ,'label'=>strtoupper($word['label']),'tooltip'=>strtoupper($word['label'])),array('id_admin_language'=>$word['idlanguage'],'id'=>$word['id']));    			
            
            //Se actualiza el valor del campo de lenguaje creado en la tabla admin_language_property
            $update=$this->app['dbs']['mysql_silex']->update($nametbpro,array('value' =>$word['value'] ,'date_register'=>date("Y-m-d H:i:s")),array('id_admin_language'=>$word['idlanguage'],'id_admin_language_content'=>$word['id']));  			
            

			$logError->addLine('Admin','sql', "Edit Word Table Property {$nametbpro} ", (int)$_SESSION["uid"]);
			return TRUE;

		} catch (Exception $e) {
			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _editWord


  /**
	* Delete Word
	* [LDONIS]
	* @var integer
	* @var object
	* @return boolean
	*/
	public function _deleteWord($id,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_language_content';
		$nametbpro=$this->prefix.'admin_language_property';

      
		try {
                
            //se elimina en la tabla admin_language_content
            $deletepro=$this->app['dbs']['mysql_silex']->delete($nametbpro, array('id_admin_language_content' => $id));  
            $logError->addLine('Admin','sql', "delete Word Table Property{$nametbpro} ", (int)$_SESSION["uid"]);
            
            //Se elimina el valor del campo de lenguaje creado en la tabla admin_language_property
             $delete=$this->app['dbs']['mysql_silex']->delete($nametb, array('id' => $id));  			
			 $logError->addLine('Admin','sql', "delete Word Table Content {$nametb} ", (int)$_SESSION["uid"]);
			 return TRUE;

		} catch (Exception $e) {
			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _deleteWord


     /**
	* Return a list of the language words
	*
	* @var integer
	* @return string(json)
	*/
	public function _getWords($id_language)
	{
	   
        
        $table_p_property 		= $this->prefix.'admin_language_property';
		$table_p_content 		= $this->prefix.'admin_language_content';
	
		$response 				= new stdClass();
		$query 					= "SELECT apc.id as id, app.value as value, apc.value as label FROM
									{$table_p_property} app 
									INNER JOIN
									{$table_p_content} apc
									ON apc.id = app.id_admin_language_content
									WHERE app.id_admin_language= $id_language";

		$words       		= $this->app['dbs']['mysql_silex']->fetchAll($query);

			   $html='<div id="tabla" class="table_users">';
			   $html.='<table class="table table-hover table-striped">';
			   $html.='<th class="header">Label</th>';
			   $html.='<th class="header">Value</th>';    
			   $html.='<th class="header" style="width:28%;">Opciones</th>'; 

			   foreach ($words as $key) {
				     $html.='<tr>';
				     $html.='<td>'.$key['label'].'</td>';
				     $html.='<td>'.$key['value'].'</td>';
				     $html.='<td> <span class="btn btn-danger btn-small" onclick="delWord('.$key['id'].')" title="Eliminar"><i class="icon-remove"></i></span>';                    
			   		 $html.='<span class="btn btn-success btn-small" onclick="javascript:EditWord('.$key['id'].','."'".$key['label']."'".','."'".$key['value']."'".')" title="Editar"><i class="icon-edit"></i></span></td>';                                      
			   		 $html.='</tr>';
			   }
			   $html.='</table>';
			   $html.='</div>';
			   $html.='<input type="button" data-toggle="modal" class="btn btn-primary" href="#newWord" value="New Word">';
		       $html.=' <div id="newWord" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		            <div class="modal-header">
		   			  <h3>New Word</h3>
		  			</div>
		            <div class="modal-body">
		              <form id="word_params"> 
		                   <input type="hidden" name="idlanguage" value="'.$id_language.'"/>
		                    <div class="facebook-admin-box input-prepend ">
		                      <span class="add-on facebook-admin-title span3"><strong>Label</strong></span><input type="text" name="label" class="span4 facebook-admin-input"/>
		                    </div>
		                    <div class="facebook-admin-box input-prepend ">
		                      <span class="add-on facebook-admin-title span3"><strong>Value</strong></span><input type="text" name="value" class="span4 facebook-admin-input"/>
		                    </div>
		                    <div class="modal-footer">
		                      <button class="btn" data-dismiss="modal">Close</button>
		                      <input type="button" data-dismiss="modal" class="btn btn-primary" onclick="saveWord();" value="Save"></button>
		                    </div>
		              </form>
		            </div>
		           
		          </div>';
		       $html.=' <div id="editWord" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		            <div class="modal-header">
		   			  <h3>Edit Word</h3>
		  			</div>
		            <div class="modal-body">
		              <form id="edit_word_params"> 
		                   <input type="hidden" name="idlanguage" value="'.$id_language.'"/>
		                   <input type="hidden" name="id" id="id" />
		                    <div class="facebook-admin-box input-prepend ">
		                      <span class="add-on facebook-admin-title span3"><strong>Label</strong></span><input type="text" name="label" id="label" class="span4 facebook-admin-input"/>
		                    </div>
		                    <div class="facebook-admin-box input-prepend ">
		                      <span class="add-on facebook-admin-title span3"><strong>Value</strong></span><input type="text" name="value" id="value" class="span4 facebook-admin-input"/>
		                    </div>
		                    <div class="modal-footer">
		                      <button class="btn" data-dismiss="modal">Close</button>
		                      <input type="button" data-dismiss="modal" class="btn btn-primary" onclick="editWord();" value="Save"></button>
		                    </div>
		              </form>
		            </div>
		           
		          </div>';
			   $var=array('tabla'=>$html);
			   return json_encode($var);
			    

  }//fin de la funcion getWords

 

 /**
	* Obtener Vista de Creacion de Campos
	*
	* @return object
	*/
	public function _getTableCreateLanguage()
	{
		$optionmenu 			= new stdClass();
		$optionmenu->title_form = "Create Language";
        $html = '<form id="form-params" class="row-fluid ">';
		$html.= '<input type="hidden" name="task" value="create-language">';
		$html.= '<fieldset>';
		$html.= '<legend></legend>';      
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
		$html.= '<span class="add-on facebook-admin-title span3"><strong>Code:</strong></span>';
		$html.= '<input type="text" class="span4 facebook-admin-input" name="code" value="" />';
		$html.= '<a class="info-param-tooltip" data-content="Code" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
		$html.= '</div>';
		$html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
		$html.= '<span class="add-on facebook-admin-title span3"><strong>Title:</strong></span>';
		$html.= '<input type="text" class="span4 facebook-admin-input" name="Title" value="" />';
		$html.= '<a class="info-param-tooltip" data-content="Title" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
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


}
?>
