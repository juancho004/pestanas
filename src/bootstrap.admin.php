<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;
use Silex\Provider\FormServiceProvider;


$app        = new Application();
$confp      = new Lib('conn');
$phpMailer  = new PHPMailer();
$admin      = new modelAdministrator();
$modelusers = new modelUsers();
$modelmenus = new modelMenus();
$modelFB    =new modelCamposFB();
$prefix=$confp->_v('PREFIX');
$smtp=$confp->_v('SMTP');
$models     = new modelAplication($phpMailer, $app,$smtp,$prefix);
$language   = new modelLanguage($app,$prefix);
$google     = new modelAnalytics($app,$prefix);
$install    = new modelInstall($app,$prefix);

$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

//Registro de la base de datos
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options'  => array(
      'mysql_silex' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAME'),
          'user'          => $confp->_v('USER'),
          'password'      => $confp->_v('PASSWORD'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
   ),   
));

$app->register(new Silex\Provider\ModelsServiceProvider(), array(
  'models.path' => __DIR__ . '/models/'
));

$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(PATH_TEMPLATES_ADMIN),
    
      //'twig.options' => array('cache' => PATH_CACHE.'/twig'), 
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
// add custom globals, filters, tags, ...
    return $twig;
}));
#classe error,
$languages   = new classLanguage($app,$prefix);
$templates  = new modelTemplatesManager( $app, $prefix );
$template=$templates->_getTemplateActive(); 
$logError   = new logError($app,$confp->_v('PREFIX'));
return $app;
