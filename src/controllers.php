<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#Home
/**
* Funcion principal para recoleccion de datos de facebook
*
* @var array, instancia de la app
* @var object, instancia BUSI
* @var object, facebook
* @var object, instancia directa para consumo de variables del archivo .INI
* @var mix resto de instancias a utilizar
* @return null
*/
$app->match('/', function () use ($app, $login) {
	
	$islogin = $login->_validateSessionActive();
	
	if( !$islogin->redirect ):
		#no se ha iniciado sesion
		return new Response(
			$app['twig']->render( 'login.twig', array() )
		);
	else:
		#sesion correcta
		return new Response(
			$app['twig']->render( 'home.twig', array( 'TITLE_PAGE' => "Administrador De Pestañas") )
		);
	endif;

})->method('GET|POST');

$app->match('/login', function () use ($app, $login, $prefix) {

	$validate 	= $login->_validateUser($_POST);
	$login 		= (!$validate)? 'error':'success';
	$message 	= (!$validate)? 'El usuario y/o contraseña no son válidos ':'';

	$jsondata['login'] 		= $login;
	$jsondata['message'] 	= $message;
	return json_encode($jsondata);

})->method('GET|POST');

#Se obtienen terminos y condiciones
$app->match('/terminos', function () use ($app,$models,$prefix) {
  
  $terms=$models->_getTermsAndConditions();
  $terminos=htmlspecialchars_decode($terms);
  return new Response(
						$app['twig']->render( 'terminos.twig', array( 'TITLE_PAGE' => "Terminos y Condiciones","terminos" => $terminos) )
					);

})->method('GET|POST');


/**
	Pestañas
*/
$app->match('/savetab', function () use ($app, $login, $prefix, $tab ) {

_pre($_POST);
exit;
	$islogin = $login->_validateSessionActive();

	if( !$islogin->redirect ):
		#no se ha iniciado sesion
		return new Response(
			$app['twig']->render( 'login.twig', array( 'TITLE_PAGE' => "Administrador De Pestañas" ) )
		);
	else:

		#registrar pestaña normal
		if( $_POST['ishtml'] == '0' ):

			$tab = $tab->_saveTab($_POST,0);
			if($tab->status):
				return $app->redirect($app['url_generator']->generate('pestanas'));
			endif;

		endif;

	endif;
})->bind('savetab')->method('GET|POST');
