<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\Provider\FormServiceProvider;
use Doctrine\DBAL\Schema\Table;

#se genera archivo htaccess si no existe, en caso de existir se sobreescribe 
$app->match('/htaccess', function () use ($app, $admin,$models,$prefix) {
     $url 	= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] );
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$admin->_gen_htaccess();
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$description_menu = $admin->_getDescriptionMenuActive($app,$prefix);

					return new Response(
						$app['twig']->render( 'home-admin.twig', array( 'TITLE_PAGE' => "ADMINISTRATOR", "MENU" => $menu, "TAB_DESCRIPTION" => $description_menu ) )
					);
				}
})->method('GET|POST');

$app->match('/inicio', function () use ($app, $admin,$models,$prefix) {
    $remove=$models->removeDirectory(PATH_ROOT.'/install');
     if($remove)
	   return $app->redirect('./login');
	})->method('GET|POST'); 

   #Opciones 
$app->match('/option-{id}', function ($id) use ($app, $admin,$language,$prefix,$templates,$install) {
               $url 	= str_replace('/index.php', '', $_SERVER['SCRIPT_NAME'] );
		      $redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
						switch ($id) {
							case 6:
						         $form_table = $admin->_getTableTermsAndConditions($app,$id,$prefix); 
						        $template='params-admin.twig';
								$params = array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form );
							break;
							case 7:
								 $form_table->form=$language->_getLanguages();
			                     $form_table->title_form='Languages';
			                     $template='language-admin.twig';
								 $params = array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ); 
								break;
							case 8:#admin template
								$form_table = $templates->_getTemplateList();

								if( !$form_table ):
									$form_table->list 	= 'No se encontraron plantillas';
									$form_table->title 	= 'Listado de Plantillas';
								endif;
								$template='params-admin.twig';
								$params = array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->list, "TITLE_FORM" => $form_table->title );
							break;

							case 9: #install template
								//echo 'Install Template';
								$form_install=$install->_installTemplate();
								$template='params-admin.twig';
								$params = array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_install->form, "TITLE_FORM" => $form_install->title_form );
							break;
							default:
								$form_table = $admin->_getTableParams($app,$id,$prefix);
								$template='params-admin.twig';
								$params = array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form );
							break;
						}	
						  
						return new Response(
						$app['twig']->render($template,$params)
					);
				}
	
	})->method('GET|POST');



	#Admin
	$app->match('/', function () use ($app, $admin,$prefix) {
     
		$url 	= str_replace('/index.php', '', $_SERVER['PHP_SELF'] );
		$var 	= ( count($_GET) > 0 )? $_GET:$_POST;
		$option = ( empty($var['op']) )?'default':$var['op'];

		

		switch ($option) {
			
			case 'login':
				$redirect = $admin->_validateSessionActive();
				if ( $redirect->redirect ){
					return $app->redirect( $url.$redirect->url );
				}
			break;

			case 'administrator':
				$redirect = $admin->_validateSessionActive();
				if ( $redirect->redirect ){
					return $app->redirect( $url.$redirect->url );	
				} else {

                    $menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$description_menu = $admin->_getDescriptionMenuActive($app,$prefix);

					return new Response(
						$app['twig']->render( 'home-admin.twig', array( 'TITLE_PAGE' => "ADMINISTRATOR", "MENU" => $menu, "TAB_DESCRIPTION" => $description_menu ) )
					);
				}
			break;

			
			case 'terminos':

				$redirect = $admin->_validateSessionActive();
				if ( $redirect->redirect ){
					return $app->redirect( $url.$redirect->url );	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$var['id'] = ( @$var['id'] === NULL )? 0:$var['id'];
					$form_table = $admin->_getTableTermsAndConditions($app,(int)$var['id'],$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
				
			break;

			case 'backgorund':

				$redirect = $admin->_validateSessionActive();
				if ( $redirect->redirect ){
					return $app->redirect( $url.$redirect->url );	
				} else {
					$menu 	= $admin->_getMenuAdministrator($app,$prefix);
					$form_table = $admin->_getBackground($app,(int)$var['id'],$prefix);
					return new Response(
						$app['twig']->render( 'params-admin.twig', array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $form_table->form, "TITLE_FORM" => $form_table->title_form ) )
					);
				}
				
			break;

			default:
				if ( decode($option) == 'sessionclose' ){
					#inicia sesión
					@session_name("loginUsuario");
					@session_start();
					@session_destroy();
					return $app->redirect($url.'index.php/login');
				} else {
					return $app->redirect($url);
				}
			break;
		}
		exit;
	})->method('GET|POST');

	#validar credenciales de usuario
	$app->match('/login', function () use ($app, $admin, $logError,$prefix ) {
		$var 	= ( count($_POST) > 0 )? $_POST:$_GET;
		$option = ( empty($var['task']) )? 'default':$var['task'];

		switch ($option) {

			case 'login':

				$validate_params 	= $admin->_validateParams( $var['dataForm'], $logError, $app,$prefix);
				$jsondata['login'] 	= $validate_params;
				return json_encode($jsondata);
				exit;

			break;
			
			default:

				return new Response(
					$app['twig']->render( 'login.php', array( 'TITLE_PAGE'	=> "ADMINISTRATOR DE APLICAIONES" ) )
				);

			break;

		}
	})->method('GET|POST');

	#Formularios administrativos
	$app->match('/adminform', function () use ($app, $admin, $logError, $prefix,$models,$language) {
    
		$dataForm 	= $_POST['dataForm']; 

        /*Se valida el task recibido al momento de guardar*/

        switch ( $dataForm[0]['value']) {
                      	case '6':
                      		  $nameFunction = "_saveAdminFormTerms";
                      		break;
                      	case 'create-user':
                      		  $nameFunction = "_saveAdminUsers";
                      		break;
                        case 'edit-user':
                      		  $nameFunction = "_saveEditUsers";
                      		break;
                        case 'edit-item':
                      		  $nameFunction = "_saveEditItem";
                      	break;	
                      	case 'create-item':
                      		  $nameFunction = "_saveAdminItem";
                      	break;	
                      	case 'create-menu':
                      		  $nameFunction = "_saveAdminMenu";
                      	break;
                      	case 'edit-menu':
                      		  $nameFunction = "_saveEditMenu";
                      	break;	
                      	case 'create-campofb':
                      		  $nameFunction = "_saveAdminCampoFB";
                      	break;
                      	case 'edit-campofb':
                      		  $nameFunction = "_saveAdminEditCampoFB";
                      	break;
                      	case 'create-language':
                            
                      	break;		
                      	default:
                      		 $nameFunction = "_saveAdminForm";
                      		break;
            }              
           
		if( $admin->$nameFunction( $app, $dataForm, $logError,$prefix,$models) ){
			$jsondata['register'] = 'success';
		} else {
			$jsondata['register'] = 'false';
		}
		return json_encode($jsondata);
		exit;

	})->method('GET|POST');


#UpLoad Images

	$app->post('/uploadBackground', function () use ($app, $admin, $logError,$prefix) {
    

      	if($_FILES["background"]['name']){

				$archivo=$_FILES["background"]['tmp_name'];
				$nombre=$_FILES['background']['name'];
				$tipo_archivo = $_FILES["background"]['type'];
                $valInput				= $_SERVER["PHP_SELF"]."/web/img/background/";
					   
			   	                                          
				                                                                      
				@copy($archivo,PATH_WEB.'/img/background/'.$nombre);
				@chmod(PATH_WEB.'/img/background/'.$nombre,0777);
                
                if( $admin->_saveLoginBackground( $app, $nombre, $logError,$prefix) ){
					$jsondata['register'] = 'success';
				} else {
					$jsondata['register'] = 'false';
				}    
        
        }else{
		
				$jsondata['register'] = 'false';
		}
        return json_encode($jsondata);
	})->method('GET|POST');
?>