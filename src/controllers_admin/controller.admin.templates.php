<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\Provider\FormServiceProvider;
use Doctrine\DBAL\Schema\Table;

$app->match('/template', function () use ($app, $prefix, $templates, $install, $admin,$logError) {
	$menu 	= $admin->_getMenuAdministrator($app,$prefix);
	$option = array_merge($_POST,$_GET);
    $tpl="Templates";
	switch ($option['option']) {

		case 'source':
			$params 		= array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $templates->_getContentTemplate($option), "TITLE_FORM" => $tpl );
			$template_name 	= 'params-admin.twig';
		break;

		case 'file':
			$params 		= array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $templates->_redTemplateContent( $option ), "TITLE_FORM" => $tpl );
			$template_name 	= 'params-admin.twig';
		break;

		case 'content':
			$params 		= array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $templates->_editFile( $option['url_file'], 'read' ), "TITLE_FORM" => $tpl );
			$template_name 	= 'params-admin.twig';
		break;

		case 'savetemplate':
			$params 		= array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $templates->_editFile( $option['url_file'], 'replease', $option['infotemplate'] ), "TITLE_FORM" => $tpl );
			$template_name 	= 'params-admin.twig';
		break;
		case 'installTemplate':
              $installer=$install->_install($_FILES,$logError);
              $params 		= array( 'TITLE_PAGE' => "Administrator", "MENU" => $menu, "FORM" => $installer->message, "TITLE_FORM" => 'Install Templates' );
			  $template_name 	= 'params-admin.twig';
		break;

		default:
		break;
	}

	return new Response(
		$app['twig']->render( $template_name, $params )
	);


	
	exit;

})->bind('template')->method('GET|POST');

$app->match('/changeTemplate', function () use ($app, $admin,$prefix,$templates,$logError) {   

           $url 	= str_replace('/admin/index.php', '', $_SERVER['SCRIPT_NAME'] ).'/admin';
		
				$redirect = $admin->_validateSessionActive();
				if ($redirect->redirect ){
					return $app->redirect( $url.$redirect->url);	
				} else {
					$id 	= $_POST['id']; 
					$templates->_changeTemplate($id);
					$logError->addLine('Admin','sql', "Change Template  ", (int)$_SESSION["uid"]);

				    $jsondata['change'] = 'true';
                    return json_encode($jsondata);
					exit;  	       
				}
	
})->method('GET|POST');


?>