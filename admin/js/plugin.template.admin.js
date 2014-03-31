/*! Pluguin Template manager V.1.0
* Jcbarreno
*/

(function($){

var methods = {

		init : function(options){
			

			//valores default
			var settings 	= {}
			jQuery.extend(settings, options);
			var startbutton = jQuery(this).attr('id');

			jQuery('.dir-content').click(function() {
				var dir_name = jQuery(this).attr('id');
				/*Reinicar carpetas activas a inactivas*/
				jQuery('#main-sub-dir li ul').removeClass('active-dir').addClass('close-dir').hide('slow');
				jQuery('ul#main-sub-dir li.dir-content > i').removeClass('icon-folder-open').addClass('icon-folder-close');

				var dir_name = jQuery(this).attr('id');
				/*desbloquear listado de directorio seleccionado*/
				jQuery('#'+dir_name+' ul').removeClass('close-dir').addClass('active-dir').show('slow');
				jQuery('#'+dir_name+' > i').removeClass('icon-folder-close').addClass('icon-folder-open');

			});

		}

	};

	$.fn.templatemanager = function( method ) {  
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.'+method+'' );  
		}
	};

})( jQuery );

function changeTemplate(id)
{

   jQuery.ajax({
         url: basepath+"/index.php/changeTemplate",
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