<?php

if (!function_exists('show_window')) {

    /**
     * Replace for system content output for CLI
     * 
     * @param string $title
     * @param string $data
     * @param string $align
     */
    function show_window($title, $data, $align = 'left') {
        $result = '';
        if (!empty($title)) {
            $result = $title . "\n";
        }
        $result .= $data . "\n";
        print($result);
    }

}


if (!function_exists('show_error')) {

    /**
     * Shows default error notice
     * 
     * @param string $data
     */
    function show_error($data) {
        show_window('Error', $data);
    }

}