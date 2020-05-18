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

function show_error($data) {
    return show_window('Error', $data);
}

function show_warning($data) {
    return show_window('Warning', $data);
}

function show_success($data) {
    return show_window('Success', $data);
}

function show_info($data) {
    return show_window('Info', $data);
}
