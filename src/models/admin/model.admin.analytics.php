<?php 
/**
 * Factory .
 *
 * @author LDONIS <ldonis.tpp@gmail.com>
 * @version 1.0
 * @package Modelo Analytics
 */
class modelAnalytics {
    
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
    *Funcion que devuelve el listado de categorias de analytics
    *@return string (html)
	*/
	public function _getCategories($id=null)
	{
      $query  = 'SELECT * FROM `'.$this->prefix.'analytics_category`';
      $list 	= $this->app['dbs']['mysql_silex']->fetchAll($query); 
		  $select="<select name='category' id='category'>";
    	$select .= "<option value=-1>Seleccione una Categoria</option>";
    	foreach ($list as $key) {
          if($id!=null)
            {
              if($key['id']==$id)
                $select .= "<option value=".$key["id"]." selected>".$key["name"]."</option>";
    	        else
                $select .= "<option value=".$key["id"].">".$key["name"]."</option>";  
            }
           else
            $select .= "<option value=".$key["id"].">".$key["name"]."</option>";  
      }
    	$html=$select;
    	$html.='</select>';
    	return $html;
	}


    /**
    *Funcion que devuelve el listado de categorias de analytics
    *@return string (html)
    */
    public function _getAnalytics($id=null)
    {
        $query  = 'SELECT * FROM `'.$this->prefix.'admin_analytics`';
        $list   = $this->app['dbs']['mysql_silex']->fetchAll($query); 
        $select="<select name='goal' id='goal'>";
        $select .= "<option value=-1>Seleccione un Goal</option>";
        foreach ($list as $key) {
          if($id!=null)
            {
              if($key['id']==$id) 
                 $select .= "<option value=".$key["id"]." selected>".$key["name"]."</option>";
              else
                $select .= "<option value=".$key["id"].">".$key["name"]."</option>";
            }
            else
             $select .= "<option value=".$key["id"].">".$key["name"]."</option>";     
        }
        $html=$select;
        $html.='</select>';
        return $html;
    } 


    /**
  * ADD new Category
  * [LDONIS]
  * @var array
  * @var object
  * @return boolean
  */
  public function _addCategory($form, $logError)
  { 
    @session_start();
    $nametb=$this->prefix."analytics_category";

    if(!empty($form['name']) && $form['goal']!=-1) {                           
             /*Se crea la nueva funcion */
        $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('name' =>$form['name']));  
        $logError->addLine('Admin','sql', "Create new Category  ", (int)$_SESSION["uid"]);
        return true;
    }
    else
     return false; 
  }// fin de la funcion _addFunction


  /**
  * Delete Category
  * [LDONIS]
  * @var integer id_category
  * @var object
  * @return boolean
  */
  public function _deleteCategory($id,$logError)
  { 
        @session_start();
    $nametb = $this->prefix.'analytics_category';
    

    try {
             
             $this->app['dbs']['mysql_silex']->delete( $nametb , array('id' =>$id) ); 
             $logError->addLine('Analytics',"sql","Category deleted ".$id, $_SESSION["uid"]);
 
       return TRUE;

    } catch (Exception $e) {

      $logError->addLine("Admin","sql", $e->getMessage(), $_SESSION["uid"]);
      return FALSE;
    }

  }// fin de la funcion _deleteFunction



    /**
  * Update Category
  * [LDONIS]
  * @var array
  * @var object
  * @return boolean
  */
  public function _editCategory($form, $logError)
  { 
    @session_start();
    $nametb=$this->prefix."analytics_category";

    if(!empty($form['name'])) {                           
             /*Se actualiza la categoria */
        $insert = $this->app['dbs']['mysql_silex']->update($nametb,array('name' =>$form['name']),array('id'=>$form['id']));  
        $logError->addLine('Admin','sql', "Update Category  ", (int)$_SESSION["uid"]);
        return true;
    }
    else
     return false; 
  }// fin de la funcion _editFunction




 /**
    *Funcion que devuelve el listado de categorias de analytics 
    *@return string (html)
    */
    public function _getCategory()
    {
        $query          = "SELECT * FROM ".$this->prefix."analytics_category";
        $list       = $this->app['dbs']['mysql_silex']->fetchAll($query);

  
               $html='<div id="tabla" class="table_menu">';
               $html.= '<div><input type="button" class="btn btn-primary" value="Add Category" href="#Category" data-toggle="modal" /></div><br/>';
               $html.='<table class="table table-hover table-striped table-bordered">';
               $html.='<th>Category</th>';
               $html.='<th>Opciones</th>'; 

               foreach ($list as $key) {
                       $html.='<tr>';
                       $html.='<td>'.$key['name'].'</td>';
                       $html.='<td> <span class="btn btn-danger btn-small"  onclick="javascript:delCategory('.$key['id'].')" title="Eliminar"><i class="icon-remove"></i></span>';
                       $html.='<a class="btn btn-success btn-small" onclick="javascript:EditCategory('.$key['id'].','."'".$key['name']."'".')" title="Editar"><i class="icon-edit"></i></a></td>';                                      
                       $html.='</tr>';  
               }

      
            $html.='</table>';
            $html.='</div>'; 
            $html.= ' <div id="Category" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 class="text-success" id="Titulo">Category</h3>
                        </div>
                        <form method="POST" action="ListCategories">
                        <div id="modal-body" class="modal-body">
                        <input id="task" name="task" type="hidden" value="create-category"/>
                        <input id="id" name="id" type="hidden"/>
                        <strong> Name: </strong> <input type="text" name="name" id="name"/>
                        </div>
                        <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <input type="submit" class="btn btn-primary" id="save" value="Save"></button>
                        </form>
                        </div>
                        </div>'; 
             
        return $html;

    }




  /**
  * Edit Function
  * [LDONIS]
  * @var array
  * @var object
  * @return boolean
  */
  public function _editFunction($form, $logError)
  { 
    @session_start();
    $nametb=$this->prefix."admin_analytics_functions";

    if(!empty($form['name']) && $form['goal']!=-1) {  

        $values=array();    

             /*Se actualiza la funcion */
        $insert = $this->app['dbs']['mysql_silex']->update($nametb,array('name' =>$form['name'] ,'id_admin_analytics'=>$form['goal']),array("id"=>$form['id']));  
        $logError->addLine('Admin','sql', "Update Function  ", (int)$_SESSION["uid"]);
  
      
       foreach ($form as $key => $value) {
                if(!(strpos($key,"campo")===false))
                  {
                   $values[]=$value;
                  }
       }

       /*Se obtienen los valores de la funcion*/
       $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_functions where id ='.$form['id'];
       $function   = $this->app['dbs']['mysql_silex']->fetchAssoc($query);


        $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_property where id_admin_function ='.$form['id']." and id_admin_analytics=".$function['id_admin_analytics'];
        $params   = $this->app['dbs']['mysql_silex']->fetchAll($query);
      
         

  if ( count($params) == 0 ) {
   
                
      $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_content where id_admin_analytics ='.$function['id_admin_analytics'];
      $content   = $this->app['dbs']['mysql_silex']->fetchAll($query);
      $parameters=array();
        


        foreach ($content as $row) {

             $query         = 'SELECT value FROM `'.$this->prefix.'admin_analytics_property` WHERE `id_admin_analytics`= '.$function['id_admin_analytics'].' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
             $value       = $this->app['dbs']['mysql_silex']->fetchAssoc($query);
         
             if ($row['name']!='structure')
                    $parameters[]=$row['id']; 
                 
          }

          $val=0;
          foreach ($parameters as $id_content) {
              $insert = $this->app['dbs']['mysql_silex']->insert($this->prefix.'admin_analytics_property',array('value' =>$values[$val],'date_register'=>date("Y-m-d H:i:s"),'id_admin_analytics'=>$function['id_admin_analytics'],'id_admin_analytics_content'=>$id_content,'id_admin_function'=>$form['id']));  
               $logError->addLine('Admin','sql', "Insert value for field ".$id_content, (int)$_SESSION["uid"]); 
             $val++; 
          }
      
     }
     else {
      $val=0; 
      foreach ($params as $field) {
          $this->app['dbs']['mysql_silex']->update($this->prefix.'admin_analytics_property' ,array('value' =>$values[$val], 'date_register'=> date("Y-m-d H:i:s")), array('id_admin_analytics_content' => $field['id_admin_analytics_content'],'id_admin_analytics'=>$function['id_admin_analytics'],'id_admin_function'=>$form['id']));
          $logError->addLine('Admin','sql', "Update value for field ".$field[0], (int)$_SESSION["uid"]);
          $val++;    
      }
      return true;                                                  
     }
   } 
    else
     return false;
  }// fin de la funcion _editFunction




 /**
  * Delete Function
  * [LDONIS]
  * @var integer id_function
  * @var object
  * @return boolean
  */
  public function _deleteFunction($id,$logError)
  { 
        @session_start();
    $nametb = $this->prefix.'admin_analytics_functions';
    

    try {
             
             $this->app['dbs']['mysql_silex']->delete( $nametb , array('id' =>$id) ); 
             $logError->addLine('Analytics Functions',"sql","Function deleted ".$id, $_SESSION["uid"]);
 
       return TRUE;

    } catch (Exception $e) {

      $logError->addLine("Admin","sql", $e->getMessage(), $_SESSION["uid"]);
      return FALSE;
    }

  }// fin de la funcion _deleteFunction



       /**
  * ADD new Function
  * [LDONIS]
  * @var array
  * @var object
  * @return boolean
  */
  public function _addFunction($form, $logError)
  { 
    @session_start();
    $nametb=$this->prefix."admin_analytics_functions";

    if(!empty($form['name']) && $form['goal']!=-1) {                           
             /*Se crea la nueva funcion */
        $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('name' =>$form['name'] ,'id_admin_analytics'=>$form['goal']));  
        $logError->addLine('Admin','sql', "Create new Function  ", (int)$_SESSION["uid"]);
        return true;
    }
    else
     return false; 
  }// fin de la funcion _addFunction



   public function getParamsFunction($id)
   {
    $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_content where id_admin_analytics ='.$id;
    $params   = $this->app['dbs']['mysql_silex']->fetchAll($query);
    $html = "";
    $parameters=array();
    $id=1;

    foreach ($params as $row) {

             $query         = 'SELECT value FROM `'.$this->prefix.'admin_analytics_property` WHERE `id_admin_analytics`= '.$id.' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
             $values        = $this->app['dbs']['mysql_silex']->fetchAssoc($query);
         
             if ($row['name']=='structure')
                    $structure=$values['value']; 
             else
              {    
                    
                     $html.="<strong>".$row['name'].":</strong><input type=text name=campo".$id."><br/>";
                     $id++;
              }     

     }
     
     return $html;

   }//fin de la funcion


    /**
    *Funcion que devuelve el listado de Funciones de analytics 
    *@return string (html)
    */
    public function _getFunctions()
    {
        $query          = "SELECT f.id id_function, f.name function, a.id id, a.name, a.description FROM ".$this->prefix."admin_analytics_functions f, ".$this->prefix."admin_analytics a where a.id=f.id_admin_analytics";
        $list       = $this->app['dbs']['mysql_silex']->fetchAll($query);

 
               $html='<div id="tabla" class="table_menu">';
               $html.= '<div><input type="button" class="btn btn-primary" value="Add Function" href="#Function" data-toggle="modal" /></div><br/>';
               $html.='<table class="table table-hover table-striped table-bordered">';
               $html.='<th>Funcion</th>';
               $html.='<th>Goal</th>';
               $html.='<th>Descripcion</th>';
               $html.='<th>Opciones</th>'; 

               foreach ($list as $key) {
                       $param=$this->getParamsFunction($key['id']);
                       $html.='<tr>';
                       $html.='<td>'.$key['function'].'</td>';
                       $html.='<td>'.$key['name'].'</td>';
                       $html.='<td>'.$key['description'].'</td>';
                       $html.='<td> <span class="btn btn-danger btn-small"  onclick="javascript:delFunction('.$key['id_function'].')" title="Eliminar"><i class="icon-remove"></i></span>
                                      <span class="btn btn-info btn-small" onclick="javascript:view('.$key['id'].','."'".$key['function']."'".');" title="Ver"><i class="icon-eye-open"></i></span> 
                                      <a class="btn btn-success btn-small" onclick="javascript:EditFunction('.$key['id_function'].','."'".$key['function']."','".$param."'".');" title="Editar"><i class="icon-edit"></i></a></td>';                                      
                       $html.='</tr>';  
               }

            $html.='</table>';
            $html.='</div>'; 
            $html.= '<!-- Modal -->
                        <div id="Functionv" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 class="text-success" id="myModalLabel">New Function</h3>
                        </div>
                        <form method="POST" action="ListFunctions">
                        <div id="body" class="modal-body">
                        </div>
                        <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        </form>
                        </div>
                        </div>';
             $html.= ' <div id="Function" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 class="text-success" id="Titulo">Function</h3>
                        </div>
                        <form method="POST" action="ListFunctions">
                        <div id="modal-body" class="modal-body">
                        <input id="task" name="task" type="hidden" value="create-function"/>
                        <input id="id" name="id" type="hidden"/>
                        <strong> Name: </strong> <input type="text" name="name" id="name"/><br/><strong>Goal:</strong>'.$this->_getAnalytics().'
                        <br/>
                        <div id="params"></div>
                        </div>
                        <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <input type="submit" class="btn btn-primary" id="save" value="Save"></button>
                        </form>
                        </div>
                        </div>'; 
             

        return $html;

    }


   
    /**
    *Funcion que devuelve el listado de Goals 
    *@return string (html)
    */
    public function _getObjectives()
    {
        $query 			= "SELECT a.id, a.name, a.description, c.name AS category
                           FROM ".$this->prefix."admin_analytics a, ".$this->prefix."analytics_category c
                           WHERE a.id_analytics_category = c.id
                           ORDER BY a.id_analytics_category";
		$list	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);


 
	      $html='<div id="tabla" class="table_menu">';
			   $html.='<table class="table table-hover table-striped table-bordered">';
              $temp='';
        
			   foreach ($list as $key) {
			   	  
				     
				     if($key['category']!=$temp)
				      {	
				      //$url=$this->app['url_generator']->generate('EditObjective',array('id'=>$key['id']));	
				       $html.='<tr class="info">'; 	
				       $html.='<td colspan=2><strong class="text-success">'.$key['category'].'</strong></td>'; 
				       $html.='</tr>';
				       $html.='<tr>';
				       $html.='<td>'.$key['name'].'</td>';
					     $html.='<td> <span class="btn btn-danger btn-small"  onclick="javascript:delObjective('.$key['id'].')" title="Eliminar"><i class="icon-remove"></i></span>
					                  <span class="btn btn-info btn-small" onclick="javascript:ViewObjective('.$key['id'].','."'".$key['name']."'".');" title="Ver"><i class="icon-eye-open"></i></span> 
					                  <a class="btn btn-success btn-small" href="./EditObjective'.$key['id'].'" title="Editar"><i class="icon-edit"></i></a></td>';                                      
				       $temp=$key['category'];
				      }
              else
              {
                         //$url=$this->app['url_generator']->generate('EditObjective',array('id'=>$key['id']));
               $html.='<tr>';	
					     $html.='<td>'.$key['name'].'</td>';
					     $html.='<td> <span class="btn btn-danger btn-small"  onclick="javascript:delObjective('.$key['id'].')" title="Eliminar"><i class="icon-remove"></i></span>
					                  <span class="btn btn-info btn-small" onclick="javascript:ViewObjective('.$key['id'].','."'".$key['name']."'".');" title="Ver"><i class="icon-eye-open"></i></span>                   
					                  <a class="btn btn-success btn-small" href="./EditObjective'.$key['id'].'" title="Editar"><i class="icon-edit"></i></a></td>';                    
			   		  }
			   		  $html.='</tr>';
			   }

      			   $html.='</table>';
      			   $html.='</div>'; 
      			   $html.= '<!-- Modal -->
      					    <div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      					    <div class="modal-header">
      					    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      					    <h3 class="text-success" id="myModalLabel">Modal header</h3>
      					    </div>
      					    <div id="modal" class="modal-body">
      					    <p>One fine body…</p>
      					    </div>
      					    <div class="modal-footer">
      					    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
      					    </div>
      					    </div>'; 

		return $html;

    }


	   
     /**
	* Save new Objective
	* [LDONIS]
	* @var array
	* @var object
	* @return boolean
	*/
	public function _saveObjective($dataForm,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_analytics';
		$nametbcont=$this->prefix.'admin_analytics_content';
		$nametbprop=$this->prefix.'admin_analytics_property';
    $parameters=array();

		foreach ($dataForm as $key => $value) {
			$objective[$value['name']] = utf8_decode(htmlentities($value['value']) );
		}     
  
		try {
           
           /*Se valida que seleccione una categoria para el goal*/
              if($objective['category']=='-1')
              	return false;

            /*Se validad que la cantidad de parametros en la estructura sea igual a la cantidad definida*/
                $cantpar=0;
                $cantstr=substr_count($objective['structure'],'{{var}}');
                foreach ($objective as $key => $value) {
                if(!(strpos($key,"campo")===false))
                  {
                   $parameters[]=$value;
                   $cantpar++;
                  }
                }

               if($cantpar!=$cantstr)
                   return false; 


            /*Se crea el nuevo goal */
            $insert = $this->app['dbs']['mysql_silex']->insert($nametb,array('name' =>$objective['Name'] ,'description'=>$objective['description'],'id_analytics_category'=>$objective['category'],'date_register'=>date("Y-m-d H:i:s")));  
      			$id=$this->app['dbs']['mysql_silex']->lastInsertId();
      			$logError->addLine('Admin','sql', "Create new Goal  ", (int)$_SESSION["uid"]);
      			

			     /*Se crea el atributo Structure para el goal*/
	         $insert = $this->app['dbs']['mysql_silex']->insert($nametbcont,array('name' =>'structure','label'=>'structure','id_admin_analytics'=>$id));  
			     $id_attribute=$this->app['dbs']['mysql_silex']->lastInsertId();
			

            /*Se almacena el valor de la estructura del goal*/
            $insert = $this->app['dbs']['mysql_silex']->insert($nametbprop,array('value' =>$objective['structure'],'date_register'=>date("Y-m-d H:i:s"),'id_admin_analytics'=>$id,'id_admin_analytics_content'=>$id_attribute,'id_admin_function'=>0));  
           
            /*Se crean los parametros del goal*/
            foreach ($parameters as $value) {
        	     $insert = $this->app['dbs']['mysql_silex']->insert($nametbcont,array('name' =>$value,'label'=>$value,'id_admin_analytics'=>$id));  
            }

			      return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _saveObjective



	 /**
	* Edit Objective
	* [LDONIS]
	* @var array
	* @var object
	* @return boolean
	*/
	public function _editObjective($dataForm,$logError)
	{ 

		@session_start();
		$nametb	= $this->prefix.'admin_analytics';
		$nametbcont=$this->prefix.'admin_analytics_content';
		$nametbprop=$this->prefix.'admin_analytics_property';
    $parameters=array();

		foreach ($dataForm as $key => $value) {
			$objective[$value['name']] = utf8_decode(htmlentities($value['value']) );
		}     
  
		try {

           
          /*Se valida que seleccionen una categoria*/ 
          if($objective['category']=='-1')
            	return false;

          /*Se valida que la cantidad de para*/  
           $cantstr=substr_count($objective['structure'],'{{var}}');

            $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_content where id_admin_analytics ='.$objective['id'];
            $params   = $this->app['dbs']['mysql_silex']->fetchAll($query);


            foreach ($params as $row) {
                  $query         = 'SELECT value FROM `'.$this->prefix.'admin_analytics_property` WHERE `id_admin_analytics`= '.$objective['id'].' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
                  $values        = $this->app['dbs']['mysql_silex']->fetchAssoc($query);
             
                 if ($row['name']=='structure')
                        $structure=$values['value']; 
                 else  
                       $parameters[]=$row['name'];  
            }

            if(count($parameters)!=$cantstr)
                 return false; 
            

            /*Se actualiza el goal */
            $update = $this->app['dbs']['mysql_silex']->update($nametb,array('name' =>$objective['Name'] ,'description'=>$objective['description'],'id_analytics_category'=>$objective['category'],'date_register'=>date("Y-m-d H:i:s")),array('id'=>$objective['id']));  

			
			     /*Se actualiza el atributo Structure para el goal*/
		        $query 		= 'SELECT * FROM '.$nametbcont.' where name like "%structure%" and id_admin_analytics='.$objective['id'];
		        $parameter	 	= $this->app['dbs']['mysql_silex']->fetchAssoc($query);


	        $update = $this->app['dbs']['mysql_silex']->update($nametbprop,array('value' =>$objective['structure'],'date_register'=>date("Y-m-d H:i:s")),array('id_admin_analytics'=>$objective['id'],'id_admin_analytics_content'=>$parameter['id']));  
			$logError->addLine('Admin','sql', "Edit Goal  ", (int)$_SESSION["uid"]);

           return TRUE;

		} catch (Exception $e) {

			$logError->addLine('Admin',"{$nametb}", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;

		}

	}// fin de la funcion _editObjective


    
	public function _getTableCreateObjective()
    {
           
   
        $objective             = new stdClass();
        $objective->title_form = "Create Goal";
        $html = '<form id="form-params" class="row-fluid ">';
        $html.= '<input type="hidden" name="task" value="create-objective">';
        $html.= '<div id="vals"></div>';
        $html.= '<fieldset>';
        $html.= '<legend></legend>';     
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Name:</strong></span>';
        $html.= '<input type="text" class="span4 facebook-admin-input" name="Name" value="" />';
        $html.= '<a class="info-param-tooltip" data-content="Name" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Campos:</strong></span>';
        $html.= '<input type="text" class="span4 facebook-admin-input" name="Campos" onkeypress="return justNumbers(event)" id="campos" value="" />';
        $html.= '<input type="button" class="btn btn-primary" value="crear" href="#newField" data-toggle="modal" />';
        $html.= '<a class="info-param-tooltip" data-content="Campos" title="" href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Descripcion:</strong></span>';
        $html.= '<input type="text" class="span4 facebook-admin-input" name="description" value="" />';
        $html.= '<a class="info-param-tooltip" data-content="Descripcion" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Categoria:</strong></span>';
        $html.=$this->_getCategories();
        $html.= '<a class="info-param-tooltip" data-content="Categoria" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Structure:</strong></span><br/>';
        $html.= '<textarea rows="25" style="width:100%" name="structure" id="structureT"></textarea>';
        $html.= '<a class="info-param-tooltip" data-content="Structure para los parame usar {{var}}" title="" href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<legend></legend>';
        $html.= '<fieldset>';
        $html.= '</fieldset>';
        $html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
        $html.= '</fieldset>';
        $html.= '</form>';
        $html.=' <div id="newField" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-header">
                         <h3>Campos</h3>
                      </div>
                    <div class="modal-body">
                      <form id="field_params">
                       <div id="fields"></div>
                            <div class="modal-footer">
                            <button class="btn" data-dismiss="modal">Close</button>
                            <input type="button" data-dismiss="modal" class="btn btn-primary" id="save" value="Save"></button>
                      </div>
                      </form>
                    </div>
                  
                  </div>';
             $objective->form = $html;
        return $objective;
               

   }//fin de la funcion

public function _getTableEditObjective($id)
    {
           
        $nametb1	= $this->prefix.'admin_analytics';
		    $nametb2	= $this->prefix.'admin_analytics_content';
		    $nametb3	= $this->prefix.'admin_analytics_property';

         $query 		= 'SELECT * FROM '.$this->prefix.'admin_analytics where id='.$id;
		     $goal	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);

        $query 		= 'SELECT * FROM '.$this->prefix.'admin_analytics_content where id_admin_analytics ='.$id;
		    $params	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);


		foreach ($params as $row) {

             $query 				= 'SELECT value FROM `'.$this->prefix.'admin_analytics_property` WHERE `id_admin_analytics`= '.$id.' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
			       $values 				= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
         
             if ($row['name']=='structure')
               	    $structure=$values['value']; 
             else  
                     @$tabla.='<tr><td>'.$row['name'].'</td><td><span class="btn btn-danger btn-small" onclick="javascript:DeleteField('.$row['id'].','."'".$id."'".');" title="Ver"><i class="icon-remove"></i></span></td></tr>';
		}


        $objective             = new stdClass();
        $objective->title_form = "Edit Goal";
        $html = '<form id="form-params" class="row-fluid ">';
        $html.= '<input type="hidden" name="task" value="edit-objective">';
        $html.= '<input type="hidden" name="id" value="'.$id.'">';
        $html.= '<div id="vals"></div>';
        $html.= '<fieldset>';
        $html.= '<legend></legend>';     
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Name:</strong></span>';
        $html.= '<input type="text" class="span4 facebook-admin-input" name="Name" value="'.$goal[0]['name'].'" />';
        $html.= '<a class="info-param-tooltip" data-content="Name" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Parametros:</strong></span>';
        $html.= '<input type="button" class="btn btn-primary" value="crear" href="#newField" data-toggle="modal" />';
        $html.= '</div>';
        $html.= '<div class="table_users"><table class="table table-striped table-bordered"><th>Parametro</th><th>Opciones</th>'.$tabla.'</table></div><br>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Descripcion:</strong></span>';
        $html.= '<input type="text" class="span4 facebook-admin-input" name="description" value="'.$goal[0]['description'].'" />';
        $html.= '<a class="info-param-tooltip" data-content="Descripcion" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Categoria:</strong></span>';
        $html.= $this->_getCategories($goal[0]['id_analytics_category']);
        $html.= '<a class="info-param-tooltip" data-content="Categoria" title=""  href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<div id="facebook-admin-box" class="facebook-admin-box input-prepend ">';
        $html.= '<span class="add-on facebook-admin-title span3"><strong>Structure:</strong></span><br/>';
        $html.= '<textarea rows="25" style="width:100%" name="structure" id="structureT">'.htmlspecialchars_decode($structure).'</textarea>';
        $html.= '<a class="info-param-tooltip" data-content="Structure para los parametros usar {{var}}" title="" href="#" data-original-title="¿Cómo configurar?"><i class="icon-info-sign"></i></a>';
        $html.= '</div>';
        $html.= '<legend></legend>';
        $html.= '<fieldset>';
        $html.= '</fieldset>';
        $html.= '<div class="controls"><button type="submit" class="btn btn btn-inverse"><i class="icon-ok-sign icon-white"></i> Guardar</button></div>';
        $html.= '</fieldset>';
        $html.= '</form>';
               $html.=' <div id="newField" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-header">
                         <h3>Campos</h3>
                      </div>
                    <div class="modal-body">
                      <form id="field_params">
                            <input type="hidden" name="id" value="'.$id.'"/>
                            <div class="control-group">
                            <label class="control-label" for="inputText">Name</label><input type="text"  id="inputText" name="new"/>
                            </div>
                            <div class="modal-footer">
                            <button class="btn" data-dismiss="modal">Close</button>
                            <input type="button" data-dismiss="modal" class="btn btn-primary" id="save" value="Save" onclick="saveField()" ></input>
                      </div>
                      </form>
                    </div>
                  
                  </div>';
             $objective->form = $html;
        return $objective;
               

   }//fin de la funcion



 /**
	* Delete Parameter
	* [LDONIS]
	* @var integer id_analytics
	* @var integer id_analytics_content
	* @var object
	* @return boolean
	*/
	public function _deleteField($id, $id_content, $logError)
	{ 
        @session_start();
		    $nametb	= $this->prefix.'admin_analytics_content';
		

		try {
             
             $this->app['dbs']['mysql_silex']->delete( $nametb , array('id' =>$id_content,'id_admin_analytics' => $id ) ); 
             $logError->addLine('Ojective Items',"sql","Objective Item deleted ".$id_content, $_SESSION["uid"]);
 
			 return TRUE;

		} catch (Exception $e) {

			$logError->addLine("Admin","sql", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;
		}

	}// fin de la funcion _deleteField


     /**
	* Delete Objective
	* [JMARTINEZ]
	* @var id
	* @var object
	* @return boolean
	*/
	public function _deleteObjective($id, $logError)
	{ 
        @session_start();
		$nametb1	= $this->prefix.'admin_analytics';
		$nametb2	= $this->prefix.'admin_analytics_content';
		$nametb3	= $this->prefix.'admin_analytics_property';
		
		try {
             
             $query    = 'SELECT * FROM '.$this->prefix.'admin_analytics_functions where id_admin_analytics ='.$id;
             $function   = $this->app['dbs']['mysql_silex']->fetchAll($query);
              
              if($function)
              {
                  return false;
              }
              else
              {
                 $this->app['dbs']['mysql_silex']->delete( $nametb3 , array('id_admin_analytics' => $id ) ); 
                 $logError->addLine('Ojective Items Value',"sql","Objective Items values deleted ".$id, $_SESSION["uid"]);

                 $this->app['dbs']['mysql_silex']->delete( $nametb2 , array('id_admin_analytics' => $id) ); 
                 $logError->addLine('Ojective Items',"sql","Objective Items deleted ".$id, $_SESSION["uid"]);

                 $this->app['dbs']['mysql_silex']->delete( $nametb1 , array('id' => $id) ); 
                 $logError->addLine('Ojective Name',"sql","Objective name deleted ".$id, $_SESSION["uid"]); 
                 return TRUE;
              }
           
		} catch (Exception $e) {

			$logError->addLine("Admin","sql", $e->getMessage(), $_SESSION["uid"]);
			return FALSE;
		}

	}// fin de la funcion _deleteObjective

	 /**
	* Get Objective Fields
	* [JMARTINEZ]
	* @var id
	* @return json
	*/
	public function _getObjectiveParams($id, $logError)
	{ 
        @session_start();
		$nametb1	= $this->prefix.'admin_analytics';
		$nametb2	= $this->prefix.'admin_analytics_content';
		$nametb3	= $this->prefix.'admin_analytics_property';
		$patern = "/{{var}}/";

    $query 		= 'SELECT * FROM '.$this->prefix.'admin_analytics_content where id_admin_analytics ='.$id;
		$params	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);
		$html = "<form id='paramsForm' name='paramsForm' method='post'>";

		foreach ($params as $row) {

             $query 				= 'SELECT value FROM `'.$this->prefix.'admin_analytics_property` WHERE `id_admin_analytics`= '.$id.' AND `id_admin_analytics_content` = '.$row['id'].' ORDER BY date_register DESC LIMIT 1';
			 $values 				= $this->app['dbs']['mysql_silex']->fetchAssoc($query);
         
             if ($row['name']=='structure')
               	    $structure=$values['value']; 
             else
              {    
                     $structure = preg_replace($patern, $row['name'], $structure, 1); 
                     @$tabla.='<tr><td>'.$row['name'].'</td></tr>';
              }     

		}


		$html.='<h4>Estructura</h3>';
        $html.='<p>'.$structure.'</p><br/>';
        $html.='<h4>Parametros</h2>';
        $html.='<table class="table table-striped">';
        $html.=$tabla;
		$html.='</table>';
        $html.="</form>";

        return $html;

    }// fin de la funcion _getObjectiveParams

     /**
	* Insert Objective Fields values
	* [JMARTINEZ]
	* @var id
	* @var form 
	* @return json
	*/
	public function _insertParamsValues($id, $form, $logError)
	{ 
        @session_start();
		
		$query 		= 'SELECT * FROM '.$this->prefix.'admin_analytics_property where id_admin_analytics ='.$id;
		$params	 	= $this->app['dbs']['mysql_silex']->fetchAll($query);

		if ( count($params) == 0 ) {
			foreach ($form as $field) {
				$this->app['dbs']['mysql_silex']->insert($this->prefix.'admin_analytics_property' ,array('value' =>$field[1],'date_register'=>date("Y-m-d H:i:s"),'id_admin_analytics'=> $id ,'id_admin_analytics_content' =>$field[0] ));
			    $logError->addLine('Admin','sql', "Set value for field ".$field[0], (int)$_SESSION["uid"]);
			}
			return true;	
		} else {
			foreach ($form as $field) {
			$this->app['dbs']['mysql_silex']->update($this->prefix.'admin_analytics_property' ,array('value' =>$field[1], 'date_register'=> date("Y-m-d H:i:s")), array('id_admin_analytics_content' => $field[0] ));
			$logError->addLine('Admin','sql', "Update value for field ".$field[0], (int)$_SESSION["uid"]);
			}
			return true;
		}
		
  }// fin de la funcion _getObjectiveParams

   /**
	* generate Analitics Function
	* [JMARTINEZ]
	* @var name
	* @return string
	*/
	public function _generateAnaliticsFunction($name)
	{ 

        $query 		= 'SELECT structure, id FROM '.$this->prefix.'admin_analytics where name ="'.$name.'"';
		$structure 	= $this->app['dbs']['mysql_silex']->fetchAssoc($query);


        $patern = "/{{var}}/";
        
        $query 		= 'SELECT value FROM '.$this->prefix.'admin_analytics_property where id_admin_analytics = '.$structure['id'];
		$values 	= $this->app['dbs']['mysql_silex']->fetchAll($query);


        $function = $structure['structure'];
        
        foreach ($values as $field ) {
            $function = preg_replace($patern, $field['value'], $function, 1); 	
        } 
        
        return $function;
        	
  }// fin de la funcion _getObjectiveParams
 

     /**
	* Create new parameter from a Goal structure
	* [LDONIS]
	* @var array 
	* @return boolean
	*/
	public function _saveField($form, $logError)
	{ 
        @session_start();
       
       foreach ($form as $key => $value) {
			$field[$value['name']] = utf8_decode(htmlentities($value['value'] ) );
		}     
    

      if(empty($field['new']))
          return false; 

     
		  $insert= $this->app['dbs']['mysql_silex']->insert($this->prefix.'admin_analytics_content' ,array('name' =>$field['new'],'label'=>$field['new'],'id_admin_analytics'=> $field['id'] ));
	    $logError->addLine('Admin','sql', "Add new field ".$field['new'], (int)$_SESSION["uid"]);

        if($insert)
	       return true;
	    else
	       return false;	
		
  }// fin de la funcion _getObjectiveParams

}
?>
