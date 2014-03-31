<?php
/*============================================================================================================*/
/* FUNCIONES */
 /*Funcion que genera el paginador, dependiendo de la cantidad de registros y paginas*/
function get_paginador($pages,$page){

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

/**
 * @var array
 * @return string
*/
/*Funcion que genera la tabla del listado de usuarios*/
function users($list)
{
   $html="<input type='button' value='Crear Usuario' class='btn btn-primary' onclick='create_user()'>";
   $html.='<br/><br/><br/>';   
   $html.='<div id="tabla" class="table_users">';
   $html.='<table class="table table-hover table-striped">';
   $html.='<th class="header">ID</th>';
   $html.='<th class="header">Nombre</th>';    
   $html.='<th class="header">Usertype</th>';     
   $html.='<th class="header">Mail</th>';   
   $html.='<th class="header" style="width:28%;">Opciones</th>'; 

   foreach ($list as $key) {
     

     $html.='<tr>';
     $html.='<td>'.$key['id'].'</td>';
     $html.='<td>'.$key['name'].'</td>';
     $html.='<td>'.$key['rol'].'</td>';
     $html.='<td>'.$key['mail'].'</td>';
     $html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_user(this.id)" title="Editar"><i class="icon-edit"></i></span>
      <span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_user(this.id)" title="Eliminar"><i class="icon-remove"></i></span>';
    if($key['block']=='1') 
       $html.=' <span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_user(this.id)" title="Bloquear"><i class="icon-ban-circle"></i></span>';
     else 
       $html.=' <span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="active_user(this.id)" title="Activar"><i class="icon-ok-circle"></i></span></td>';
                                
    $html.='</tr>';
   }
   $html.='</table>';
   $html.='</div>';

   $var=array('tabla'=>$html);
   return json_encode($var);

}//fin de la funcion users




/**
 * @var array
 * @return string
*/
/*Funcion que genera la tabla del listado de menus*/
function Items($list)
{

   $html="<input type='button' value='Crear Item' class='btn btn-primary' onClick='create_menu()'>";
   $html.='<br/><br/><br/>';   
   $html.='<div id="tabla" class="table_menus">'; 
   $html.='<table class="table table-hover table-striped">';
   $html.='<th class="header">ID</th>';
   $html.='<th class="header">LABEL</th>';    
   $html.='<th class="header">Opciones</th>'; 
   foreach ($list as $key) {
     

     $html.='<tr>';
     $html.='<td>'.$key['id'].'</td>';
     $html.='<td>'.$key['label'].'</td>';
     $html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_item(this.id)" title="Editar"><i class="icon-edit"></i></span>
      <span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_item(this.id)" title="Eliminar"><i class="icon-remove"></i></span>';
                            
    $html.='</tr>';
   }
   $html.='</table>';
   $html.='</div>';

   $var=array('tabla'=>$html);
   return json_encode($var);

}//fin de la funcion items


/**
 * @var array
 * @return string
*/
/*Funcion que genera la tabla del listado de menus*/
function Menus($list)
{

   $html="<a class='btn btn-primary' href='./create_menu'>Crear Menu</a>";
   $html.='<br/><br/><br/>';   
   $html.='<div id="tabla" class="table_menus">'; 
   $html.='<table class="table table-hover table-striped">';
   $html.='<th class="header">ID</th>';
   $html.='<th class="header">TITLE</th>';  
   $html.='<th class="header">ROL</th>';    
   $html.='<th class="header">Opciones</th>'; 
   foreach ($list as $key) {
     

     $html.='<tr>';
     $html.='<td>'.$key['id'].'</td>';
     $html.='<td>'.$key['title'].'</td>';
      $html.='<td>'.$key['name'].'</td>';
     $html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_menu(this.id)" title="Editar"><i class="icon-edit"></i></span>
      <span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_menu(this.id)" title="Eliminar"><i class="icon-remove"></i></span>';
    if($key['published']=='1') 
       $html.=' <span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_menu(this.id)" title="Despublicar"><i class="icon-eye-close"></i></span>';
     else 
       $html.=' <span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="active_menu(this.id)" title="Publicar"><i class="icon-eye-open"></i></span></td>';                        
    $html.='</tr>';
   }
   $html.='</table>';
   $html.='</div>';

   $var=array('tabla'=>$html);
   return json_encode($var);
}
//fin de la funcion Menus 


/**
 * @var array
 * @return string
*/
/*Funcion que genera la tabla del listado de campos del formulario de FB*/
function Campos($list)
{

   $html="<input type='button' value='Crear Campo' class='btn btn-primary' onClick='create_campofb()'>";
   $html.='<br/><br/><br/>';   
   $html.='<div id="tabla" class="table_menus">'; 
   $html.='<table class="table table-hover table-striped">';
   $html.='<th class="header">ID</th>';
   $html.='<th class="header">NAME</th>';
   $html.='<th class="header">LABEL</th>';    
   $html.='<th class="header">Opciones</th>'; 
   foreach ($list as $key) {
     

     $html.='<tr>';
     $html.='<td>'.$key['id'].'</td>';
     $html.='<td>'.$key['name'].'</td>';
     $html.='<td>'.$key['label'].'</td>';
     $html.='<td> <span class="btn btn-info btn-small" id="'.$key['id'].'" onClick="editar_campo(this.id)" title="Editar"><i class="icon-edit"></i></span>
      <span class="btn btn-danger btn-small" id="'.$key['id'].'" onClick="eliminar_campo(this.id)" title="Eliminar"><i class="icon-remove"></i></span>';
    if($key['block']=='0') 
       $html.=' <span class="btn btn-warning btn-small" id="'.$key['id'].'" onClick="bloquear_campo(this.id)" title="Bloquear"><i class="icon-ban-circle"></i></span>';
     else 
       $html.=' <span class="btn btn-success btn-small" id="'.$key['id'].'" onClick="active_campo(this.id)" title="Activar"><i class="icon-ok-circle"></i></span></td>';                    
    $html.='</tr>';
   }
   $html.='</table>';
   $html.='</div>';

   $var=array('tabla'=>$html);
   return json_encode($var);

}//fin de la funcion items

/*Funcion que realiza el export de datos a excel*/
function exportar()
 {
   $filename ="reporte.xlsx";
   header("Content-type:   application/x-msexcel; charset=utf-8");
   header('Content-Disposition: attachment; filename='.$filename);

 }//fin de la funcion exportar

//=========================================================================
?>