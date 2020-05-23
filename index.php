<?php

error_reporting(E_ALL);
/**
 * Default headers
 */
header('Last-Modified: ' . gmdate('r'));
header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

/**
 * Page generation time counters begins
 */
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];
$query_counter = 0;
/**
 * rcms-like commons consts defines
 */
define('CONFIG_PATH', 'config/');
define('MODULES_PATH', 'modules/general/');

require_once('api/autoloader.php');
require_once($yalfCore->getIndexModulePath());

//web based renderer template load
if ($yalfCore->getRenderer() == 'WEB') {
    require_once($yalfCore->getSkinPath() . $yalfCore::SKIN_TEMPLATE_NAME);
}