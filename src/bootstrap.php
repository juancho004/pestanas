<?php
#error_reporting(E_ALL);
#ini_set('display_errors','On');
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Response;


$app        = new Application();
$confp      = new Lib('conn');
$phpMailer  = new PHPMailer();
$prefix     =$confp->_v('PREFIX');
$smtp       =$confp->_v('SMTP');
$service    =new Services();
$image      = new Image(); 

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


$models     = new modelAplication($phpMailer,$app,$smtp,$prefix);
$busi       = new BusiReporter($confp, $app, $phpMailer,$prefix);
$logError   = new logError( $app,$prefix);
$analytics     = new modelAnalytics( $app,$prefix);
$facebook   = new FacebookServiceProvider($app,$prefix,$logError);
$login      = new modelLogin($app, $prefix, $logError );
$tab        = new modelTab($phpMailer, $app, $prefix, $logError, $image );
/*Set Default Language*/
$language   = new classLanguage($app,$prefix);
/*Set Default Template*/
$templates  = new modelTemplatesManager( $app, $prefix );
$template=$templates->_getTemplateActive(); 


$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(PATH_TEMPLATES_WEB.'/'.$template.'/'),
      //'twig.options' => array('cache' => PATH_CACHE.'/twig'), 
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
// add custom globals, filters, tags, ...
    return $twig;
}));

/*Set default location to the sources*/
$path=str_replace($_SERVER['DOCUMENT_ROOT'], '', PATH_TEMPLATES_WEB);
$app['source']=$path.'/'.$template;

$app->error(function (\LogicException $e, $code) {
$error_logic = error_get_last();
$error_logic = "[date: ".date('r')."] [type: ".$error_logic['type']."] [pid: ".getmypid()."] [client: ".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT']."] PHP ".$error_logic['message']." ".$error_logic['file']." on line ".$error_logic['line']."\n";
error_log($error_logic, 3, PATH_ROOT."/logs/errors.log");
});

$app->error(function (\Exception $e, $code) use ($app) {

  switch ($code) {
  case 404:
    $message = 'Lo sentimos, la pagina No existe';
  break;

  default:
    $message = 'Lo sentimos, la pagina No existe';
  break;
  }
  echo $message;
  exit;
});

return $app;
