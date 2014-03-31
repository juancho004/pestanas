<?php
header('p3p: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__DIR__));
define('PATH_ROOT_WEB', dirname(__FILE__));
define('PATH_CACHE', PATH_ROOT.'/cache');
define('PATH_CONFIG', PATH_ROOT.'/config');
define('PATH_LOG', PATH_ROOT.'/logs');
define('PATH_SRC', PATH_ROOT.'/src');
define('PATH_TEMPLATES', PATH_ROOT.'/templates');
define('PATH_VENDOR', PATH_ROOT.'/vendor');
define('PATH_WEB', PATH_ROOT.'/web');
define('PATH_TEMPLATES_WEB', PATH_WEB.'/templates');
require_once PATH_VENDOR.'/autoload.php';
require_once PATH_CONFIG.'/clib.php';
require_once PATH_SRC.'/language/class.language.php';
require_once PATH_SRC.'/models/model.aplication.php';
require_once PATH_SRC.'/phpmailer/class.phpmailer.php';
require_once PATH_SRC.'/models/admin/model.templates.manager.php';
require_once PATH_SRC.'/models/model.login.php';
require_once PATH_SRC.'/libws/Services.php';
require_once PATH_SRC.'/images/image.php';
require_once PATH_SRC.'/models/model.tab.php';
require_once PATH_SRC.'/busireporter/class.busireporter.php';
require_once PATH_SRC.'/logerror/class.log.php';
require_once PATH_SRC.'/analytics/model.analytics.php';
require_once PATH_SRC.'/facebook/facebook.api.php';
$app = require PATH_SRC.'/bootstrap.php';
require PATH_SRC.'/controllers.php';
$app['debug'] = false;
$app->run();
