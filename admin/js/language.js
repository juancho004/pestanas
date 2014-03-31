/*! Plugin  Language v1
*	Plugin para registrar y administrar informacion de los lenguajes
*/

(function($){

	var methods = {

		init : function(options){

				//ToolTip Information

			jQuery(".info-param-tooltip").mouseenter(function(){
				jQuery(this).popover('show')
			}).mouseleave(function(){
				jQuery(this).popover('destroy')
			});
			//valores default
			var settings = {
				action : ''//tipo de accion para adminostrar
			}
			jQuery.extend(settings, options);
            
          /*jQuery(".btn-danger").click(function(){
                alert('a'); 
          }); */

           jQuery(".language").click(function(){
            	id= $(this).attr("id");
				  jQuery.ajax({
		             url: basepath+"/index.php/getWords",
		             type: 'post',
		             data: {
		               id:id
		              },
		              dataType: 'json',
		          beforeSend: function(xhr){
		            //agragar loader mientras se ejecuta el Ajax
		            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
		          },
		          success: function(json) {
		            
		               jQuery('#words').html(json.tabla);

		             },
		          complete: function(xhr, textStatus){
		                  //elimina box de loader al finalizar Ajax
		                  jQuery('.box-loader-img').remove();
		                }
		             
      				}); 
			});
          
            jQuery(this).submit(function() {
            	methods._saveLanguage(this); 
            	return false;
           	});
	
		},
		_saveLanguage: function(form) 
		{
            var id 	= jQuery(form).attr('id');

				//jQuery('#alert-box').remove();
				
				var data_form 	= jQuery(form).serializeArray();//Obtiene valores de los campos del formulario

				jQuery.ajax({
					url: basepath+'/index.php/saveLanguage',
					type: 'POST',
					async: true,
					data: {
						dataForm: data_form 
					},
					dataType: 'json',
					beforeSend: function(xhr){
						//agragar loader mientras se ejecuta el Ajax
						jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%;"><img src="'+basepath+'/img/loading.gif" /></div>');
					},
					success: function(json){

						if( json.register == 'success' ){
							//Despliegue de alerta de error
							var msn_error = '<div id="alert-box" class="alert alert-success" style="left: 20%; position: absolute; width: 60%;  z-index: 12; ">'+
											'<button data-dismiss="alert" class="close"  onclick="location.href=\''+basepath+'/index.php/option-7\'" type="button">×</button>'+
											'<center><h4>Registro correcto!</h4></strong>Los cambios se realizaron de forma carrecta.</div>';

							jQuery('#'+id).prepend(msn_error);


						} else {
							//Despliegue de alerta de error
							var msn_error = '<div id="alert-box" class="alert alert-error" style="left: 20%; position: absolute; width: 60%;  z-index: 12; ">'+
											'<button data-dismiss="alert" class="close" onclick="location.href=\''+basepath+'/index.php/option-7\'" type="button">×</button>'+
											'<center><h4>Error en el registro!</h4></strong>No fue posible realizar los cambios.</div>';

							jQuery('#'+id).prepend(msn_error);

						}

					},
					complete: function(xhr, textStatus){
						//elimina box de loader al finalizar Ajax
						jQuery('.box-loader-img').remove();
					}
				});
		},
		_saveWord: function(){
			alert('b');
		}
	   	

	};//end methods

	$.fn.adminlanguage = function( method ) {  
		if ( methods[method] ) {
			//return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );  
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.estiloPropio' );  
		}
	};

})( jQuery );

function delWord(id)
{

   jQuery.ajax({
         url: basepath+"/index.php/deleteWord",
         type: 'post',
         data: {
           id:id
          },
          dataType: 'json',
      beforeSend: function(xhr){
        //agragar loader mientras se ejecuta el Ajax
        jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
           if(json.delete='success')
            {  
            	alert("palabra eliminada");
               location.reload();
            }  
           else {
           	 alert("error al eliminar la palabra");
             location.reload();
           }  	
         },
      complete: function(xhr, textStatus){
              //elimina box de loader al finalizar Ajax
              jQuery('.box-loader-img').remove();
            }
         
	 }); 

}

function saveWord()
{ 
         var data_form   = jQuery('#word_params').serializeArray();
	        jQuery.ajax({
		             url: basepath+"/index.php/saveWord",
		             type: 'post',
		             data: {
		               form:data_form
		              },
		              dataType: 'json',
		          beforeSend: function(xhr){
		            //agragar loader mientras se ejecuta el Ajax
		            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
		          },
		          success: function(json) {
                       if(json.save='success'){
                         alert("palabra creada");
                         location.reload();
                        }
                       else {
                       	 alert("error al crear la palabra");
                         location.reload(); 
                        } 	
		             },
		          complete: function(xhr, textStatus){
		                  //elimina box de loader al finalizar Ajax
		                  jQuery('.box-loader-img').remove();
		                }
		             
      			}); 
}

function editWord()
{ 
         var data_form   = jQuery('#edit_word_params').serializeArray();
	        jQuery.ajax({
		             url: basepath+"/index.php/editWord",
		             type: 'post',
		             data: {
		               form:data_form
		              },
		              dataType: 'json',
		          beforeSend: function(xhr){
		            //agragar loader mientras se ejecuta el Ajax
		            jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
		          },
		          success: function(json) {
                       if(json.save='success'){
                         alert("palabra actualizada");
                         location.reload();
                        }
                       else {
                       	 alert("error al actualizar la palabra");
                         location.reload(); 
                        } 	
		             },
		          complete: function(xhr, textStatus){
		                  //elimina box de loader al finalizar Ajax
		                  jQuery('.box-loader-img').remove();
		                }
		             
      			}); 
}

function EditWord(id,label,value) {
       jQuery("#label").val(label);
       jQuery("#value").val(value);
       jQuery("#id").val(id);                 
       jQuery('#editWord').modal('toggle');
    }


function deleteLanguage(id)
{

   jQuery.ajax({
         url: basepath+"/index.php/deleteLanguage",
         type: 'post',
         data: {
           id:id
          },
          dataType: 'json',
      beforeSend: function(xhr){
        //agragar loader mientras se ejecuta el Ajax
        jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
           if(json.delete='success')
            {  
            	alert("Lenguaje eliminado");
               location.reload();
            }  
           else {
           	 alert("error al eliminar el lenguaje");
             location.reload();
           }  	
         },
      complete: function(xhr, textStatus){
              //elimina box de loader al finalizar Ajax
              jQuery('.box-loader-img').remove();
            }
         
	}); 

}

function changeLanguage(id)
{

   jQuery.ajax({
         url: basepath+"/index.php/changeLanguage",
         type: 'post',
         data: {
           id:id
          },
          dataType: 'json',
      beforeSend: function(xhr){
        //agragar loader mientras se ejecuta el Ajax
        jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
           if(json.change='success')
            {  
            	//alert("Lenguaje cambiado");
               location.reload();
            }  
           else {
           	 //alert("error al cambiar el lenguaje");
             location.reload();
           }  	
         },
      complete: function(xhr, textStatus){
              //elimina box de loader al finalizar Ajax
              jQuery('.box-loader-img').remove();
            }
         
	}); 

}