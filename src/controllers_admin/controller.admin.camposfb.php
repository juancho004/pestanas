<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->match('/Campos_FB', function () use ($app, $admin,$prefix,$modelFB) {     
	      $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';

				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$menus = $modelFB->_getCampos($app,$prefix);
                    $table_menus=Campos($menus);
                    $table_menus=json_decode($table_menus,true);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Campos FB", "MENU" => $menu, "FORM" => $table_menus['tabla'], "TITLE_FORM" => 'Campos FB') )
					);
				}
	
})->method('GET|POST');


$app->match('/Create-Campo-FB', function () use ($app, $admin,$prefix,$modelFB) {     
            $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelFB->_getTableCreateCampo($app,$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/Edit_CampoFB-{id}', function ($id) use ($app, $admin,$prefix,$modelFB) {     

		$url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelFB->_getTableEditCampo($app,$prefix,$id);
					
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/delete_campofb', function () use ($app, $admin,$prefix,$modelFB,$logError) {     
     

        $id=$_POST['id'];   
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
				   	$delete = $modelFB->_deleteCampo($app,$id,$prefix,$logError);
					 if($delete!=NULL)
                	   return 'Campo Eliminado Correctamente';
              		else
                	  return 'Error al Eliminar el Campo';
					
				}
	
})->method('GET|POST');

$app->match('/block_campofb', function () use ($app, $admin,$prefix,$modelFB,$logError) {     
     
        
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelFB->_blockCampo($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Campo Bloqueado Correctamente';
            		  
				}
	
})->method('GET|POST');


$app->match('/activate_campofb', function () use ($app, $admin,$prefix,$modelFB,$logError) {     
     
        
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelFB->_activateCampo($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Campo Desbloqueado Correctamente';
            		  
				}
	
})->method('GET|POST');

?>