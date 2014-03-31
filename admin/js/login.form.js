/*! Pluguin  login v1
*	Login de usuarios
*/

(function($){

	var methods = {

		init : function(options){
			//valores default
			var settings = {
				error_message : 'Usuario o contraseña incorrecto.'//Mensaje de alerta
			}
			jQuery.extend(settings, options);

			var id 	= jQuery(this).attr('id');
			jQuery('#inputUser').focus();

			jQuery(this).submit(function() {
				
				jQuery('#'+id+' input').removeClass('empty_param');//credenciales incorrectas
				jQuery('#'+id+' .control-group .input-controls .help-inline').remove();

				var data_form 	= jQuery(this).serializeArray();//Obtiene valores de los campos del formulario

				//Ajax de validacion de campos
				jQuery.ajax({
					url: basepath+'/index.php/login',
					type: 'POST',
					async: true,
					data: {
						task: 'login',
						dataForm: data_form 
					},
					dataType: 'json',
					beforeSend: function(xhr){
						//agragar loader mientras se ejecuta el Ajax
						jQuery('body').prepend('<div class="box-loader-img" style="position:absolute;left: 50%;top: 50%;"><img src="'+basepath+'/img/loading.gif" /></div>');
					},
					success: function(json){
						if(json.login == 'error'){
							jQuery('#'+id+' input').addClass('empty_param');//credenciales incorrectas
							//jQuery('#'+id+' .control-group .input-controls').append( '<span class="help-inline"><i class="icon-asterisk"></i></span>');

							//Despliegue de alerta de error
							var msn_error = '<div class="alert alert-error" style="left: 20%; position: absolute; width: 60%;">'+
											'<button data-dismiss="alert" class="close" type="button">×</button>'+
											'<center><h4>Error en el registro!</h4></strong>Es posible que el usuario y/o la contraseña sean incorrectos, por favor intenta de nuevo.</div>';

							jQuery('body').prepend(msn_error);
						}

						if(json.login == 'success'){
							window.location.href = basepath+'/index.php/?op=administrator';
						}
					},
					complete: function(xhr, textStatus){
						//elimina box de loader al finalizar Ajax
						jQuery('.box-loader-img').remove();
					}
				});
				return false; 
			});

		}
	};

	$.fn.loginform = function( method ) {  
		if ( methods[method] ) {
			//return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );  
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.estiloPropio' );  
		}
	};

})( jQuery );
