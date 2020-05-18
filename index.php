<?php

error_reporting(E_ALL);

/**
 * rcms-like commons consts defines
 */
define('CONFIG_PATH', 'config/');
define('MODULES_PATH', 'modules/general/');

include ('api/autoloader.php');
include ($yalfCore->getIndexModulePath());

//web based renderer template load
if ($yalfCore->getRenderer() == 'WEB') {
    require_once($yalfCore->getSkinPath() . $yalfCore::SKIN_TEMPLATE_NAME);
}