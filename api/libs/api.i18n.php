<?php

/**
 * Loads current YALF language
 */
$yalfLanguagePath = $yalfCore->getLangPath();
if (file_exists($yalfLanguagePath)) {
    $allLangFiles = rcms_scandir($yalfLanguagePath, '*.php');
    if (!empty($allLangFiles)) {
        foreach ($allLangFiles as $locPreloadIndex => $locPreloadName) {
            require_once ($yalfLanguagePath . $locPreloadName);
        }
    }
}