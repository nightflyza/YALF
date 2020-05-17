<?php

/**
 * rcms-like commons consts defines
 */
define('CONFIG_PATH', 'config/');

/**
 * Including all needed APIs and Libs
 */
include('api/libs/api.compat.php');
include('api/libs/api.yalfcore.php');

$yalfCore = new YALFCore();
$yalfLibs = $yalfCore->getLibs();
if (!empty($yalfLibs)) {
    foreach ($yalfLibs as $eachLibPath => $eachYalfLayer) {
        include ($eachLibPath);
    }
}

