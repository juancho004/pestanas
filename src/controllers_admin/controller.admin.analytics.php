<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->match('/ListObjectives', function () use ($app, $admin,$prefix,$google) {     

          $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
                    
					$form_table = $google->_getObjectives();
					return new Response(
						$app['twig']->render( 'analytics-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table, "TITLE_FORM" => 'Goals' ) )
					);
				}
	
})->method('GET|POST');

$app->match('/CreateObjective', function () use ($app, $admin,$prefix,$google) {     
           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					
					$form_table = $google->_getTableCreateObjective();
					return new Response(
						$app['twig']->render( 'analytics-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');


$app->match('/saveObjective', function () use ($app, $admin,$prefix,$google,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$dataForm 	= $_POST['dataForm']; 

					if($dataForm[0]['value']=='create-objective')
						$save=$google->_saveObjective($dataForm,$logError);
					else
					   $save=$google->_editObjective($dataForm,$logError);	

					if($save) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


$app->match('/saveField', function () use ($app, $admin,$prefix,$google,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$form 	= $_POST['data_form']; 
					if($google->_saveField($form,$logError)) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


$app->match('/DeleteField', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// DELETE GOAL
					$id 	= $_POST['id'];
					$id_content=$_POST['id_content'];
					
					if($google->_deleteField($id,$id_content,$logError)) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');

$app->match('/deleteObjective', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// DELETE GOAL
					$id 	= $_POST['id'];
					
					if($google->_deleteObjective($id ,$logError)) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


$app->match('/EditObjective{id}', function ($id) use ($app, $admin,$prefix,$google) {     
           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					
					$form_table = $google->_getTableEditObjective($id);
					return new Response(
						$app['twig']->render( 'analytics-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');

$app->match('/getParams', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// get Params
					$id 	= $_POST['id'];
					$params = $google->_getObjectiveParams($id, $logError);
                    $data = array( 'params' => $params );

					return json_encode($data);
					exit;

				}
	
})->method('GET|POST');

$app->match('/saveValuesParams', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// get Params
					$id 	= $_POST['id'];
				    $form = explode("&", $_POST['form']);
                    
                    for ($i=0; $i< count($form); $i++) { 
	                	$form[$i] =  explode("=", $form[$i]);
                    }
				    
				    //
				    $params = $google->_insertParamsValues($id, $form, $logError);
                    
                    if($params) 
                    	$data = array( 'status' => true );
                    else 
                    	$data = array( 'status' => false );
					return json_encode($data);
					exit;

				}
	
})->method('GET|POST');


/*Categorias*/

$app->match('/ListCategories', function () use ($app, $admin,$prefix,$google,$logError) {

          $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
                    
 
                    if(!empty($_POST)){

                    	switch ($_POST['task']) {
                    		case 'create-category':
                    			  $google->_addCategory($_POST,$logError);
                    			break;
                    	    case 'edit-category':
                    			  $google->_editCategory($_POST,$logError);
                    			break;
                    	}
                    	
                    	return $app->redirect('./ListCategories');
                    }        

					$form_table = $google->_getCategory();
					return new Response(
						$app['twig']->render( 'analytics-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table, "TITLE_FORM" => 'Categories' ) )
					);
				}
	
})->method('GET|POST');


/*Funciones*/

$app->match('/ListFunctions', function () use ($app, $admin,$prefix,$google,$logError) {

          $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
                    
 
                    if(!empty($_POST)){

                    	switch ($_POST['task']) {
                    		case 'create-function':
                    			  $google->_addFunction($_POST,$logError);
                    			break;
                    		
                    		case 'edit-function':
                    		     $google->_editFunction($_POST,$logError); 
                    			break;
                    	}
                    	
                    	return $app->redirect('./ListFunctions');
                    }        

					$form_table = $google->_getFunctions();
					return new Response(
						$app['twig']->render( 'analytics-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table, "TITLE_FORM" => 'Functions' ) )
					);
				}
	
})->method('GET|POST');


$app->match('/deleteFunction', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// DELETE GOAL
					$id 	= $_POST['id'];
					
					if($google->_deleteFunction($id ,$logError)) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');

$app->match('/deleteCategory', function () use ($app, $admin,$prefix,$google,$logError) {     

                $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					
					// DELETE GOAL
					$id 	= $_POST['id'];
					
					if($google->_deleteCategory($id ,$logError)) 
					   {
                           $jsondata['register'] = 'success';
                       }
                    else
                        {	
						   $jsondata['register'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


?>
