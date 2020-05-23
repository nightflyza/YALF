<?php

/**
 * Including all needed APIs and Libs
 */
require_once('api/libs/api.compat.php');
require_once('api/libs/api.yalfcore.php');

$yalfCore = new YALFCore();
$yalfLibs = $yalfCore->getLibs();
if (!empty($yalfLibs)) {
    foreach ($yalfLibs as $eachLibPath => $eachYalfLayer) {
        require_once($eachLibPath);
    }
}

