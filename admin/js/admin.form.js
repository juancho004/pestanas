/*! Pluguin  Admin form v1
*	Pluguin para registrar datos de formulario
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

			var id 	= jQuery(this).attr('id');
			jQuery('#inputUser').focus();

			jQuery(this).submit(function() {
				jQuery('#alert-box').remove();
				
				var data_form 	= jQuery(this).serializeArray();//Obtiene valores de los campos del formulario

				//Ajax de validacion de campos
				jQuery.ajax({
					url: basepath+'/index.php/adminform',
					type: 'POST',
					async: true,
					data: {
						action: settings.action,
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
											'<button data-dismiss="alert" class="close"  type="button">×</button>'+
											'<center><h4>Registro correcto!</h4></strong>Los cambios se realizaron de forma carrecta.</div>';

							jQuery('#'+id).prepend(msn_error);


						} else {
							//Despliegue de alerta de error
							var msn_error = '<div id="alert-box" class="alert alert-error" style="left: 20%; position: absolute; width: 60%;  z-index: 12; ">'+
											'<button data-dismiss="alert" class="close" type="button">×</button>'+
											'<center><h4>Error en el registro!</h4></strong>No fue posible realizar los cambios.</div>';

							jQuery('#'+id).prepend(msn_error);

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

	$.fn.adminform = function( method ) {  
		if ( methods[method] ) {
			//return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );  
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.estiloPropio' );  
		}
	};

})( jQuery );


jQuery(function() {
                	$("form#formUpload").submit(function(){

					var formData = new FormData($(this)[0]);

					 jQuery.ajax({
					        url: basepath+'/index.php/uploadBackground',
					        type: 'POST',
					        data: formData,
					        async: false,
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
									jQuery('#formUpload').prepend(msn_error);
							} else {
									//Despliegue de alerta de error
									var msn_error = '<div id="alert-box" class="alert alert-error" style="left: 20%; position: absolute; width: 60%;  z-index: 12; ">'+
													'<button data-dismiss="alert" class="close" type="button">×</button>'+
													'<center><h4>Error en el registro!</h4></strong>No fue posible realizar los cambios.</div>';
									jQuery('#formUpload').prepend(msn_error);
								}
							},
							complete: function(xhr, textStatus){
								//elimina box de loader al finalizar Ajax
								jQuery('.box-loader-img').remove();
							},  
					        cache: false,
					        contentType: false,
					        processData: false
					    });
					    return false;
					});
				});


