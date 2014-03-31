<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/**
Administracion de Menus
**/

$app->match('/Menus', function () use ($app, $admin,$prefix,$modelmenus) {     
	
         $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$menus = $modelmenus->_getMenus($app,$prefix);
                  
                    $table_menus=Menus($menus);
                    $table_menus=json_decode($table_menus,true);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Menus", "MENU" => $menu, "FORM" => $table_menus['tabla'], "TITLE_FORM" => 'Menus') )
					);
				}
	
})->method('GET|POST');

$app->match('/create_menu', function () use ($app, $admin,$prefix,$modelmenus) {     
            $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelmenus->_getTableCreateMenu($app,$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');



$app->match('/block_menu', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     

        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelmenus->_blockMenu($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Menu Despublicado Correctamente';
            		  
				}
	
})->method('GET|POST');

$app->match('/activate_menu', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     
     
        
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelmenus->_activateMenu($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Menu Publicado Correctamente';
            		  
				}
	
})->method('GET|POST');

$app->match('/delete_menu', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     
     

        $id=$_POST['id'];   
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
				   	$delete = $modelmenus->_deleteMenu($app,$id,$prefix,$logError);
					 if($delete!=NULL)
                	   return 'Menu Eliminado Correctamente';
              		else
                	  return 'Error al Eliminar el Menu';
					
				}
	
})->method('GET|POST');


$app->match('/Edit_Menu-{id}', function ($id) use ($app, $admin,$prefix,$modelmenus) {     

		$url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelmenus->_getTableEditMenu($app,$prefix,$id);
					
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');


/**
Administracion de Items de configuracion
*/

$app->match('/Items', function () use ($app, $admin,$prefix,$modelmenus) {     
	
         $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$menus = $modelmenus->_getItems($app,$prefix);
                    $table_menus=Items($menus);
                    $table_menus=json_decode($table_menus,true);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Items", "MENU" => $menu, "FORM" => $table_menus['tabla'], "TITLE_FORM" => 'Items') )
					);
				}
	
})->method('GET|POST');


$app->match('/Create-Item', function () use ($app, $admin,$prefix,$modelmenus) {     
            $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelmenus->_getTableCreateItem($app,$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/Edit_Item-{id}', function ($id) use ($app, $admin,$prefix,$modelmenus) {     

		$url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelmenus->_getTableEditItem($app,$prefix,$id);
					
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/delete_item', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     
     

        $id=$_POST['id'];   
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
				   	$delete = $modelmenus->_deleteItem($app,$id,$prefix,$logError);
					 if($delete!=NULL)
                	   return 'Item Eliminado Correctamente';
              		else
                	  return 'Error al Eliminar el Item';
					
				}
	
})->method('GET|POST');


$app->match('/delete_field', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     
     
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
				   	$delete = $modelmenus->_deleteField($app,$id,$prefix,$logError);
					 if($delete!=NULL)
                	   return 'Campo Eliminado Correctamente';
              		else
                	  return 'Error al Eliminar el Campo';
					
				}
	
})->method('GET|POST');


$app->match('/new_field', function () use ($app, $admin,$prefix,$modelmenus,$logError) {     
			
		    	$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
				   	$add = $modelmenus->_addField($app,$prefix,$logError);
					   if($add){
			              return "Campo agregado correctamente";
		               } else {
			              return "Error al agregar el campo";
		               }
		
				}
	
})->method('GET|POST');

?>