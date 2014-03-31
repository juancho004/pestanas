<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->match('/getWords', function () use ($app, $admin,$prefix,$language) {     
	
          $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
				$redirect = $admin->_validateSessionActive();

				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
                    
					$words=$language->_getWords($_POST['id']);
                    return $words; 
				}
	
})->method('GET|POST');

$app->match('/create_language', function () use ($app, $admin,$prefix,$language) {     
           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					
					$form_table = $language->_getTableCreateLanguage();
					return new Response(
						$app['twig']->render( 'language-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
	
})->method('GET|POST');


$app->match('/saveLanguage', function () use ($app, $admin,$prefix,$language,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$dataForm 	= $_POST['dataForm']; 
					if($language->_saveLanguage($dataForm,$logError)) 
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



$app->match('/deleteLanguage', function () use ($app, $admin,$prefix,$language,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$id 	= $_POST['id']; 
					if($language->_deleteLanguage($id,$logError)) 
					   {
                           $jsondata['delete'] = 'success';
                       }
                    else
                        {	
						   $jsondata['delete'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');

$app->match('/changeLanguage', function () use ($app, $admin,$prefix,$languages,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$id 	= $_POST['id']; 
					$languages->_loadLanguage($id);
					$logError->addLine('Admin','sql', "Change Language  ", (int)$_SESSION["uid"]);

				    $jsondata['change'] = 'true';
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');

$app->match('/saveWord', function () use ($app, $admin,$prefix,$language,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$dataForm 	= $_POST['form'];
					if($language->_saveWord($dataForm,$logError)) 
					   {
                           $jsondata['save'] = 'success';
                       }
                    else
                        {	
						   $jsondata['save'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


$app->match('/deleteWord', function () use ($app, $admin,$prefix,$language,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$id 	= $_POST['id'];
					if($language->_deleteWord($id,$logError)) 
					   {
                           $jsondata['delete'] = 'success';
                       }
                    else
                        {	
						   $jsondata['delete'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


$app->match('/editWord', function () use ($app, $admin,$prefix,$language,$logError) {     

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					if($language->_editWord($_POST,$logError)) 
					   {
                           $jsondata['delete'] = 'success';
                       }
                    else
                        {	
						   $jsondata['delete'] = 'false';
                        }
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');

?>