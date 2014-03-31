/*Funciones de Administracion de Lenguajes*/
function getWords(id)
 {
   jQuery.ajax({
              url: "./getWords",
              type: 'post',
              data: {
               id:id
              },
              dataType: 'json',
          success: function(json) {
             //draw_gen_pais(a);
            jQuery('#paginador').html(json.paginador);
            jQuery('#tabla').html(json.tabla);
          },
          error: function (error){
            alert(error)
          }
      }); 
 
 }//fin de la funcion paginacion

/*Funciones de Administracion de Menus*/
 function bloquear_menu(id)
 {
 
    jQuery.ajax({
      url: "./block_menu",
      type: 'post',
      data: { 
       id:id
      },
       beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
       success: function(result) {
         confirmacion(result,"./Menus");
        },
       complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
         }
    });     
      
 }//fin de la funcion bloquear_menu


 function active_menu(id)
 {
  
    jQuery.ajax({
      url: "./activate_menu",
      type: 'post',
      data: { 
       id:id
      },
      beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
      success: function(result) {
        confirmacion(result,"./Menus");
       },
      complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
    });     
   
 }//fin de la funcion active_menu

function eliminar_menu(id)
 {
var msg='¿Realmente desea eliminar este menu?'

$('#message').html("");

$('#message').append("<div id='dialog-message' title='Eliminar Menu' style='display:none'><p>"+msg+"</p></div>");

  $(function() {
     $( "#dialog-message" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
            url: "./delete_menu",
            type: 'post',
            data: { 
             id:id
            },
          beforeSend: function(xhr){
              //agragar loader mientras se ejecuta el Ajax
              jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
          success: function(result) {
            confirmacion(result,"./Menus");
          },
          complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
        });     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 }//fin de la funcion eliminar_menu


function editar_menu(id)
{
  location.href='./Edit_Menu-'+id;
}//fin de la funcion editar_user


/*Funciones Administracion de Items de Configuracion*/
function create_menu()
{
  location.href='./Create-Item';
}

function editar_item(id)
{
  location.href='./Edit_Item-'+id;
}//fin de la funcion editar_user

function delete_field(id)
 {
 
    jQuery.ajax({
      url: "./delete_field",
      type: 'post',
      data: { 
       id:id
      },
       beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
       success: function(result) {
         confirmacion(result,"");
        },
       complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
         }
    });     
      
 }//fin de la funcion delete_field

function new_field()
 {

  var data_form   = jQuery('#field_params').serializeArray();
    
    jQuery.ajax({
      url: "./new_field",
      type: 'post',
      data: { 
       form:data_form
      },
       beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
       success: function(result) {
          confirmacion(result,"");
        },
       complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
         }
    });     
      
 }//fin de la funcion delete_field

function eliminar_item(id)
 {
var msg='¿Realmente desea eliminar este menu?'

$('#message').html("");

$('#message').append("<div id='dialog-message' title='Eliminar Menu' style='display:none'><p>"+msg+"</p></div>");

  $(function() {
     $( "#dialog-message" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
            url: "./delete_item",
            type: 'post',
            data: { 
             id:id
            },
          beforeSend: function(xhr){
              //agragar loader mientras se ejecuta el Ajax
              jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
          success: function(result) {
            confirmacion(result,"./Items");
          },
          complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
        });     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 }//fin de la funcion eliminar_user

/*Funciones de Administracion de Campos de Formulario FB*/


function create_campofb()
{
  location.href='./Create-Campo-FB';   
}

function eliminar_campo(id)
 {
var msg='¿Realmente desea eliminar este campo?'

$('#message').html("");

$('#message').append("<div id='dialog-message' title='Eliminar Usuario' style='display:none'><p>"+msg+"</p></div>");

  $(function() {
     $( "#dialog-message" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
      url: "./delete_campofb",
      type: 'post',
      data: { 
       id:id
      },
      beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
    success: function(result) {
         confirmacion(result,"./Campos_FB");
       },
    complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
    });     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 }//fin de la funcion eliminar_campo

 function bloquear_campo(id)
 {
 
    jQuery.ajax({
      url: "./block_campofb",
      type: 'post',
      data: { 
       id:id
      },
       beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
       success: function(result) {
         confirmacion(result,"./Campos_FB");
        },
       complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
         }
    });     
      
 }//fin de la funcion bloquear_campofb


 function active_campo(id)
 {
  
    jQuery.ajax({
      url: "./activate_campofb",
      type: 'post',
      data: { 
       id:id
      },
      beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
      success: function(result) {
        confirmacion(result,"./Campos_FB");
       },
      complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
    });     
   
 }//fin de la funcion active_user


function editar_campo(id)
{
  location.href='./Edit_CampoFB-'+id;
}//fin de la funcion editar_campo



/*Funciones Administracion de Usuarios*/
function create_user()
{
  location.href='./Create-Users-3';   
}

function editar_user(id)
{
  location.href='./Edit_User-'+id;
}//fin de la funcion editar_user

function eliminar_user(id)
 {
var msg='¿Realmente desea eliminar este usuario?'

$('#message').html("");

$('#message').append("<div id='dialog-message' title='Eliminar Usuario' style='display:none'><p>"+msg+"</p></div>");

  $(function() {
     $( "#dialog-message" ).dialog({
       modal: true,
       buttons: {
        Ok: function() {
           $( this ).dialog( "close" );  
           jQuery.ajax({
		  url: "./delete_user",
		  type: 'post',
		  data: { 
		   id:id
		  },
      beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
		success: function(result) {
		     confirmacion(result,"./Users");
		   },
		complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
		});     
        },
       Cancel: function() {
          $( this ).dialog( "close" );
        } 
      }
    });
  });
 }//fin de la funcion eliminar_user

function bloquear_user(id)
 {
 
    jQuery.ajax({
		  url: "./block_user",
		  type: 'post',
		  data: { 
		   id:id
		  },
       beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
		   success: function(result) {
		     confirmacion(result,"./Users");
		    },
		   complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
         }
		});     
      
 }//fin de la funcion bloquear_user


function active_user(id)
 {
  
    jQuery.ajax({
		  url: "./activate_user",
		  type: 'post',
		  data: { 
		   id:id
		  },
      beforeSend: function(xhr){
            //agragar loader mientras se ejecuta el Ajax
            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
          },
		  success: function(result) {
		    confirmacion(result,"./Users");
		   },
		  complete: function(xhr, textStatus){
            //elimina box de loader al finalizar Ajax
            jQuery('.box-loader-img').remove();
          }
		});     
   
 }//fin de la funcion active_user



function informacion(msg)
{
$('#message').html("");
 $('#message').append("<div id='dialog-message' title='Información' style='display:none'><p>"+msg+"</p></div>");
 $(function() {
    $( "#dialog-message" ).dialog({
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  }); 
}//fin de la funcion informacion 

function confirmacion(msg,url)
{
$('#message').html("");
 $('#message').append("<div id='dialog-message' title='Confirmación' style='display:none'><p> <span class='ui-icon ui-icon-circle-check' style='float: left; margin: 0 7px 50px 0;'></span>"+msg+"</p></div>");
 $(function() {
    $( "#dialog-message" ).dialog({
      modal: true,
      buttons: {
        Ok: function() {
          $( this ).dialog( "close" );
          location.href=url;
        }
      }
    });
  }); 
}//fin de la funcion confirmacion 
    


