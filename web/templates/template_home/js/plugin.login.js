/*! Pluguin  Ruleta navidad V.1
*
*/

(function($){

	var methods = {

		init : function(options){

			var settings 	= {}
			var username;
			var password;
			var idform 		= jQuery(this).attr('id');
			
			jQuery.extend(settings, options);
			//var parentsatart	= jQuery(this).parent().attr('id');

			//Validar Usuario & Contraseña
			jQuery('#'+idform).submit(function( event ) {
				username = jQuery('#'+idform+ ' input:first').val();
				password = jQuery('#'+idform+ ' input:last').val();

				methods._validateUser(username,password);
				return false;
			});


		},
		_validateUser : function(username,password){
		
				jQuery.ajax({
					url: basepath+'/index.php/login',
					type: 'POST',
					async: true,
					data: {
						name: username,
						pass: password
					},
					dataType: 'json',
					beforeSend: function(xhr){
						jQuery.fancybox.showLoading();
					},
					success: function(json){
						if( json.login == 'error' ){
							jQuery.fancybox( '<div class="alert alert-danger"><h1>'+json.message+'</h1></div>' );
							return false;
						}
						top.location= basepath+'';
						

					},
					complete: function(xhr, textStatus){
						jQuery.fancybox.hideLoading();
					}
				});

		}

	};

	$.fn.loginform = function( method ) {  
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.'+method+'' );  
		}
	};

})( jQuery );
