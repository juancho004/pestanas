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

			jQuery( "#nombre-pestana" ).focus();


			//validar si ingresa formato HTML
			jQuery('.formato-html').change(function(){
				var id_name 	= 'panel-data-html-prestana';
				var id_parent 	= 'panel-nombre-prestana';
				var isHtml 		= jQuery(this).val();

				if( isHtml == '1' ){
					methods._panelInsertHtml(id_name);
				} else {
					jQuery('#panel-fan-prestana').remove();
					jQuery('#panel-imagen-prestana').remove();
					jQuery('#'+id_name).remove();
					methods._panelFan(id_name,id_parent);
					methods._panelImagen(id_parent);

				}

			});

			jQuery( ".input-group-addon" ).delegate( ".validate-fan", "click", function() {
				var id_parent 	= 'panel-nombre-prestana';
				var name_panel	= 'panel-imagen-prestana-fan';
				var isFan 		= jQuery(this).val();

				if( isFan == '1' ){
					methods._panelImagenFan(id_parent,name_panel);
					jQuery('#panel-imagen-prestana').remove();
				} else {
					jQuery('#'+name_panel).remove();
					jQuery('#panel-app-id-prestana').remove();
					methods._panelImagen(id_parent);
				}

			});


		},
		_panelInsertHtml : function(id_name){

			//Eliminar panel que no se usan
			jQuery('#panel-fan-prestana').remove();
			jQuery('#panel-imagen-prestana').remove();
			jQuery('#panel-data-html-prestana').remove();
			jQuery('#panel-app-id-prestana').remove();
			jQuery('#panel-imagen-prestana-fan').remove();

			var content_html = 	'<div id="'+id_name+'" class="list-group" onmouseover="overgroup(this.id)" onmouseout="outgroup(this.id)" >'+
								'<div class="list-group-item">'+
                                '<p><h3>Ingrese codigo HTML&nbsp;&nbsp;<span class="glyphicon glyphicon-list-alt"></span></h3></p>'+
                                '<div class="input-group">'+
                                '<textarea id="html-editor" class="form-control" style="width: 700px" name="htmlText" ></textarea>'+
                                '</div>'+
                            	'</div>';
			
            var paretn_tab	= jQuery('#panel-html-prestana').parent().attr('id');
			jQuery('#'+paretn_tab).append(content_html);

			tinymce.init({
				selector: "textarea#html-editor",
				paste_data_images: true,
				theme: "modern",
				height: 300,
				plugins: [
					"advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
					"searchreplace wordcount visualblocks visualchars code insertdatetime media nonbreaking",
					"save table contextmenu directionality emoticons template paste textcolor"
				],
				content_css: "css/content.css",
				toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons", 
				style_formats: [
					{title: 'Bold text', inline: 'b'},
					{title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
					{title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
					{title: 'Example 1', inline: 'span', classes: 'example1'},
					{title: 'Example 2', inline: 'span', classes: 'example2'},
					{title: 'Table styles'},
					{title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
				]
			 }); 


		},
		_panelFan : function(id_name,id_parent){


			//Eliminar panel que no se usan
			jQuery('#panel-fan-prestana').remove();
			jQuery('#panel-imagen-prestana').remove();
			jQuery('#panel-data-html-prestana').remove();
			jQuery('#panel-app-id-prestana').remove();


			var content_html 	= 	'<div id="panel-fan-prestana" class="list-group" onmouseover="overgroup(this.id)" onmouseout="outgroup(this.id)" >'+
									'<div class="list-group-item">'+
									'<h3>Verificar si es FAN ó NO</h3>'+
									'<div class="input-group">'+
									'<div class="col-lg-3">'+
									'<div class="input-group">'+
									'<span class="input-group-addon">'+
									'<input id="es-fan" class="validate-fan" type="radio" name="isfan" value="1">'+
									'</span>'+
									'<input type="text" class="form-control" value="Si" readonly>'+
									'</div>'+
									'</div>'+
									'<div class="col-lg-3">'+
									'<div class="input-group">'+
									'<span class="input-group-addon">'+
									'<input id="no-fan" class="validate-fan" type="radio" name="isfan" value="0" checked >'+
									'</span>'+
									'<input type="text" class="form-control" value="No" readonly>'+
									'</div>'+
									'</div>'+
									'</div>'+
									'</div>'+
									'</div>';

			var paretn_tab	= jQuery('#'+id_parent).parent().attr('id');
			jQuery('#'+paretn_tab).append(content_html);
			methods.init();

		},
		_panelImagen : function(id_parent){

			jQuery('#panel-imagen-prestana').remove();

			var content_html 	= 	'<div id="panel-imagen-prestana" class="list-group" onmouseover="overgroup(this.id)" onmouseout="outgroup(this.id)" >'+
									'<div class="list-group-item actives">'+
									'<h3>Cargar imagen de la pestaña</h3>'+
									'<div class="input-group">'+
									'<span class="btn btn-default btn-file">'+
									'<span class="glyphicon glyphicon-folder-open"></span>'+
									'&nbsp;&nbsp;&nbsp;Buscar imagen <input id="uploadr-imagen-pestana"  name="uploadr_imagen_pestana" type="file">'+
									'</span>'+
									'</div>'+
									'</div>'+
									'</div>';

			var paretn_tab	= jQuery('#'+id_parent).parent().attr('id');
			jQuery('#'+paretn_tab).append(content_html);

		},
		_panelImagenFan : function(id_parent,name_panel){


			//Eliminar panel que no se usan
			jQuery('#'+name_panel).remove();
			jQuery('#panel-imagen-prestana').remove();
			jQuery('#panel-data-html-prestana').remove();
			jQuery('#panel-app-id-prestana').remove();

			var content_html 	= 	'<div id="'+name_panel+'" class="list-group" onmouseover="overgroup(this.id)" onmouseout="outgroup(this.id)" >'+
									'<div class="row list-group-item actives">'+
									
									'<div class="col-lg-6 col-sm-6">'+
									'<h3>Cargar imagen Si es Fan</h3>'+
									'<div class="input-group">'+
									'<span class="btn btn-default btn-file">'+
									'<span class="glyphicon glyphicon-folder-open"></span>'+
									'&nbsp;&nbsp;&nbsp;Buscar imagen <input id="uploadr-imagen-pestana"  name="uploadr_imagen_pestana_fan" type="file">'+
									'</span>'+
									'</div>'+
									'</div>'+

									'<div class="col-lg-6 col-sm-6">'+
									'<h3>Cargar imagen si No es Fan</h3>'+
									'<div class="input-group">'+
									'<span class="btn btn-default btn-file">'+
									'<span class="glyphicon glyphicon-folder-open"></span>'+
									'&nbsp;&nbsp;&nbsp;Buscar imagen <input id="uploadr-imagen-pestana"  name="uploadr_imagen_pestana_no_fan" type="file" checked>'+
									'</span>'+
									'</div>'+
									'</div>'+

									'</div>'+
									'</div>'+
									'<div id="panel-app-id-prestana" class="list-group" onmouseover="overgroup(this.id)" onmouseout="outgroup(this.id)">'+
									'<div class="list-group-item actives">'+
									'<h3>Ingresa ID de la aplicacion</h3>'+
									'<div class="input-group">'+
									'<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span></span>'+
									'<input id="id-app-pestana" name="id_app_pestana" type="text" class="form-control" placeholder="ID de la aplicacion" >'+
									'</div>'+
									'</div>'+
									'</div>';

			var paretn_tab	= jQuery('#'+id_parent).parent().attr('id');
			jQuery('#'+paretn_tab).append(content_html);


		}


	};

	$.fn.createpestana = function( method ) {  
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));  
		} else if ( typeof method === 'object' || ! method ){
			return methods.init.apply( this, arguments );
		} else {  
			jQuery.error( 'Este método ' +  method + ' no existe en jQuery.'+method+'' );  
		}
	};

})( jQuery );
