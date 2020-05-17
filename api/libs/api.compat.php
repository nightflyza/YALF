<?php

/**
 * Some legacy workaround here
 */
if (!function_exists('__')) {

    /**
     * Dummy i18n function
     * 
     * @param string $str
     * @return string
     */
    function __($str) {
        return($str);
    }

}

if (!function_exists('show_window')) {

    /**
     * Replace for system content output
     * 
     * @param string $title
     * @param string $data
     * @param string $align
     */
    function show_window($title, $data, $align = "left") {
        $result = '';
        if (!empty($title)) {
            $result = $title . "\n";
        }
        $result .= $data . "\n";
        print($result);
    }

}


if (!function_exists('curdatetime')) {

    /**
     * Returns current date and time in mysql DATETIME view
     * 
     * @return string
     */
    function curdatetime() {
        $currenttime = date("Y-m-d H:i:s");
        return($currenttime);
    }

}

if (!function_exists('rcms_redirect')) {

    /**
     * Shows redirection javascript. 
     * 
     * @param string $url
     * @param bool $header
     */
    function rcms_redirect($url, $header = false) {
        if ($header) {
            @header('Location: ' . $url);
        } else {
            echo '<script language="javascript">document.location.href="' . $url . '";</script>';
        }
    }

}


if (!function_exists('ispos')) {

    /**
     * Checks for substring in string
     * 
     * @param string $string
     * @param string $search
     * @return bool
     */
    function ispos($string, $search) {
        if (strpos($string, $search) === false) {
            return(false);
        } else {
            return(true);
        }
    }

}

/**
 * Advanced php5 scandir analog wit some filters
 * 
 * @param string $directory Directory to scan
 * @param string $exp  Filter expression - like *.ini or *.dat
 * @param string $type Filter type - all or dir
 * @param bool $do_not_filter
 * 
 * @return array
 */
function rcms_scandir($directory, $exp = '', $type = 'all', $do_not_filter = false) {
    $dir = $ndir = array();
    if (!empty($exp)) {
        $exp = '/^' . str_replace('*', '(.*)', str_replace('.', '\\.', $exp)) . '$/';
    }
    if (!empty($type) && $type !== 'all') {
        $func = 'is_' . $type;
    }
    if (is_dir($directory)) {
        $fh = opendir($directory);
        while (false !== ($filename = readdir($fh))) {
            if (substr($filename, 0, 1) != '.' || $do_not_filter) {
                if ((empty($type) || $type == 'all' || $func($directory . '/' . $filename)) && (empty($exp) || preg_match($exp, $filename))) {
                    $dir[] = $filename;
                }
            }
        }
        closedir($fh);
        natsort($dir);
    }
    return $dir;
}

/**
 * Parses standard INI-file structure and returns this as key=>value array
 * 
 * @param string $filename Existing file name
 * @param bool $blocks Section parsing flag
 * 
 * @return array
 */
function rcms_parse_ini_file($filename, $blocks = false) {
    $array1 = file($filename);
    $section = '';
    foreach ($array1 as $filedata) {
        $dataline = trim($filedata);
        $firstchar = substr($dataline, 0, 1);
        if ($firstchar != ';' && !empty($dataline)) {
            if ($blocks && $firstchar == '[' && substr($dataline, -1, 1) == ']') {
                $section = strtolower(substr($dataline, 1, -1));
            } else {
                $delimiter = strpos($dataline, '=');
                if ($delimiter > 0) {
                    preg_match("/^[\s]*(.*?)[\s]*[=][\s]*(\"|)(.*?)(\"|)[\s]*$/", $dataline, $matches);
                    $key = $matches[1];
                    $value = $matches[3];

                    if ($blocks) {
                        if (!empty($section)) {
                            $array2[$section][$key] = stripcslashes($value);
                        }
                    } else {
                        $array2[$key] = stripcslashes($value);
                    }
                } else {
                    if ($blocks) {
                        if (!empty($section)) {
                            $array2[$section][trim($dataline)] = '';
                        }
                    } else {
                        $array2[trim($dataline)] = '';
                    }
                }
            }
        }
    }
    return (!empty($array2)) ? $array2 : false;
}
