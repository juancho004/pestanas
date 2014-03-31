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
            
    
           jQuery("#campos").change(function(){
            	methods._createFields(this.value); 
            	return false;
			});
          
           jQuery('#save').click(function()
           {
             methods._addFields(); 
             //return false;
           });

            jQuery(this).submit(function() {
            	methods._saveObjective(this); 
            	return false;
           	});
	
		},
		_saveObjective: function(form) 
		{
            var id 	= jQuery(form).attr('id');

				//jQuery('#alert-box').remove();
				
				var data_form 	= jQuery(form).serializeArray();//Obtiene valores de los campos del formulario
				jQuery.ajax({
					url: basepath+'/index.php/saveObjective',
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
											'<button data-dismiss="alert" class="close" type="button">×</button>'+
											'<center><h4>Registro correcto!</h4></strong>Los cambios se realizaron de forma carrecta.</div>';
                            
                            jQuery("input[name=Name]").val("");
                            jQuery("input[name=Campos]").val("");
                            jQuery("#structureT").val("");
                            jQuery("#fields").html("");
                            
                            jQuery('#'+id).prepend(msn_error);


						} else {
							//Despliegue de alerta de error
							var msn_error = '<div id="alert-box" class="alert alert-error" style="left: 20%; position: absolute; width: 60%;  z-index: 12; ">'+
											'<button data-dismiss="alert" class="close" " type="button">×</button>'+
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
		_createFields: function(field){
			var cant=parseInt(field);
			var campo='';
			for (var i = 0; i < cant; i++) {
				    campo+='<div class="facebook-admin-box input-prepend ">';
		            campo+='<span class="add-on facebook-admin-title span3"><strong>Name</strong></span><input type="text" name="campo'+i+'" class="span4 facebook-admin-input"/>';
		            campo+='</div>';
			 };
			 $('#fields').html(campo);
		},
		_addFields: function(field){
			var data_form   = jQuery('#field_params').serialize();
            var data=data_form.split('&');
            for (var i = 0; i < data.length; i++) {
            	var value=data[i].split('=');
                jQuery('#vals').append('<input type="hidden" name="'+value[0]+'" value="'+value[1]+'"/>'); 
            }
          var form =jQuery('#vals').html();
		}
	   	

	};//end methods

	$.fn.adminanalytics = function( method ) {  
		if ( methods[method] ) {
			//return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );  
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.estiloPropio' );  
		}
	};

})( jQuery );

var idSEtValues = 0;

function justNumbers(e)
{
var keynum = window.event ? window.event.keyCode : e.which;
if ((keynum == 8) || (keynum == 46))
return true;
 
return /\d/.test(String.fromCharCode(keynum));
}


/* Category*/
function delCategory(id) {
  jQuery.ajax({
         url: basepath+"/index.php/deleteCategory",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
        if(json.register=="success")
            alert('Categoria Eliminada');  
        else
            alert('No se pudo eliminar la categoria, ya que esta asociada a uno o mas goals');          
           location.reload();
            },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
  });

}


/* Category*/
function EditCategory(id,name) {
       jQuery("#task").val('edit-category');
       jQuery("#id").val(id); 
       jQuery("#name").val(name);         
       jQuery('#Category').modal('toggle'); 

}


function DeleteField(id_content,id) {
	jQuery.ajax({
         url: basepath+"/index.php/DeleteField",
         type: 'post',
         data: { id_content:id_content,id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
               if(json.register=="success")
                 alert('Campo Eliminado');  
               else
                 alert('No se pudo eliminar el campo, ya que esta asociado a una o mas funciones');    
               location.reload();	
            },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	});

}

function delObjective(id) {
	jQuery.ajax({
         url: basepath+"/index.php/deleteObjective",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
               if(json.register=="success")
                  alert('Goal Eliminado');  
              else
                  alert('No se pudo eliminar el goal, ya que esta asociada a una o mas funciones');    
               location.reload();  	
            },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	});

}


function saveField()
{
  var data_form   = jQuery('#field_params').serializeArray();
  
  jQuery.ajax({
         url: basepath+"/index.php/saveField",
         type: 'post',
         data: { data_form:data_form },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
           if(json.register='success')
            {  
               location.reload();
            }  
           else {
               alert('No se pudo crear el parametro');
               location.reload();
            }  	
            },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	});

}




function view(id,funct) {
	  jQuery.ajax({
         url: basepath+"/index.php/getParams",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
           var body =String(json.params);
           jQuery('#body').html(body);
		       jQuery("#myModalLabel").html(funct);
           jQuery('#Functionv').modal('toggle');  
           },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	 });
}



function ViewObjective(id,funct) {
	  jQuery.ajax({
         url: basepath+"/index.php/getParams",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
        var body =String(json.params);
           jQuery('#modal').html(body);
		        jQuery("#myModalLabel").html(funct);
           jQuery('#myModal').modal('toggle');  
           },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	 });
}



function delFunction(id) {
  jQuery.ajax({
         url: basepath+"/index.php/deleteFunction",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
         if(json.register=="success")
            alert('Funcion Eliminada');  
        else
            alert('No se pudo eliminar la funcion');    
               location.reload();
            },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
  });

}



function EditFunction(id,funct,params) {
       jQuery("#task").val('edit-function');
       jQuery("#name").val(funct);
       jQuery("#params").html(params);
       jQuery("#id").val(id);                 
       jQuery('#Function').modal('toggle');
    }

function EditObjective(id,funct) {
	  jQuery.ajax({
         url: basepath+"/index.php/EditObjective",
         type: 'post',
         data: { id:id },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
            jQuery("#modal-body").html(json.params);
		        jQuery("#myModalLabel").html(funct);
		        idSEtValues = id;
           jQuery('#myModal').modal('toggle');  
           },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	 });
}

function saveValuesParams(){
	var form =jQuery("#paramsForm").serialize();
	jQuery.ajax({
         url: basepath+"/index.php/saveValuesParams",
         type: 'post',
         data: { id:idSEtValues, form:form },
         dataType: 'json',
         beforeSend: function(xhr){
         //agragar loader mientras se ejecuta el Ajax
         jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%; z-index: 9999;"><img src="'+basepath+'/img/loading.gif" /></div>');
      },
      success: function(json) {
      	       if (json.status)
           	   		jQuery('#myModal').modal('toggle');  
           },
     complete: function(xhr, textStatus){
              jQuery('.box-loader-img').remove();
     }
         
	 });
}

