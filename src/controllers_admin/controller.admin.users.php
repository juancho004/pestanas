<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->match('/Users', function () use ($app, $admin,$prefix,$modelusers) {     
	
          $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$users = $modelusers->_getUsers($app,$prefix);
                    $table_users=users($users);
                    $table_users=json_decode($table_users,true);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Usuarios", "MENU" => $menu, "FORM" => $table_users['tabla'], "TITLE_FORM" => 'Users') )
					);
				}
	
})->method('GET|POST');


$app->match('/Create-Users-{id}', function ($id) use ($app, $admin,$prefix,$modelusers) {     
           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelusers->_getTableCreateUser($app,$id,$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/Edit_User-{id}', function ($id) use ($app, $admin,$prefix,$modelusers) {     

		    $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $modelusers->_getTableEditUser($app,$prefix,$id);
					
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/delete_user', function () use ($app, $admin,$prefix,$modelusers,$logError) {     
     

        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$delete = $modelusers->_deleteUser($app,$id,$prefix,$logError);
					 if($delete!=NULL)
                	   return 'Usuario Eliminado Correctamente';
              		else
                	  return 'Error al Eliminar el Usuario';
					
				}
	
})->method('GET|POST');


$app->match('/block_user', function () use ($app, $admin,$prefix,$modelusers,$logError) {     
     
        
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelusers->_blockUser($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Usuario Bloqueado Correctamente';
            		  
				}
	
})->method('GET|POST');

$app->match('/activate_user', function () use ($app, $admin,$prefix,$modelusers,$logError) {     
     
        
        $id=$_POST['id'];   
		

				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
				   	$block = $modelusers->_activateUser($app,$id,$prefix,$logError);
					   if($block==1)
              			return 'Usuario Activado Correctamente';
            		  
				}
	
})->method('GET|POST');


?>