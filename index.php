<?php

error_reporting(E_ALL);

/**
 * rcms-like commons consts defines
 */
define('CONFIG_PATH', 'config/');
define('MODULES_PATH', 'modules/general/');

include ('api/autoloader.php');
include ($yalfCore->getIndexModulePath());