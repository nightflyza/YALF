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
        global $lang;
        if (isset($lang['def'][$str])) {
            $str = $lang['def'][$str];
        }
        return($str);
    }

}

if (!function_exists('cfr')) {

    /**
     * dummy rights system replacement
     */
    function cfr($right) {
        return(true);
    }

}

/**
 * Dummy rcms localisation function
 * 
 * @param string $str
 * 
 * @return string
 */
function rcms_date_localise($str) {
    return($str);
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

if (!function_exists('vf')) {

    /**
     * Returns cutted down data entry 
     *  Available modes:
     *  1 - digits, letters
     *  2 - only letters
     *  3 - only digits
     *  4 - digits, letters, "-", "_", "."
     *  5 - current lang alphabet + digits + punctuation
     *  default - filter only blacklist chars
     *
     * @param string $data
     * @param int $mode
     * 
     * @return string
     */
    function vf($data, $mode = 0) {
        switch ($mode) {
            case 1:
                return preg_replace("#[^a-z0-9A-Z]#Uis", '', $data); // digits, letters
                break;
            case 2:
                return preg_replace("#[^a-zA-Z]#Uis", '', $data); // letters
                break;
            case 3:
                return preg_replace("#[^0-9]#Uis", '', $data); // digits
                break;
            case 4:
                return preg_replace("#[^a-z0-9A-Z\-_\.]#Uis", '', $data); // digits, letters, "-", "_", "."
                break;
            case 5:
                return preg_replace("#[^ [:punct:]" . ('a-zA-Z') . "0-9]#Uis", '', $data); // current lang alphabet + digits + punctuation
                break;
            default:
                return preg_replace("#[~@\+\?\%\/\;=\*\>\<\"\'\-]#Uis", '', $data); // black list anyway
                break;
        }
    }

}

/**
 * Fast debug text data output
 * 
 * @param string $data
 */
function deb($data) {
    show_window('DEBUG', $data);
}

/**
 * Fast debug output of array
 * 
 * @param string $data
 */
function debarr($data) {
    $result = print_r($data, true);
    $result = '<pre>' . $result . '</pre>';
    show_window('DEBUG', $result);
}

/**
 * Returns current date and time in mysql DATETIME view
 * 
 * @return string
 */
function curdatetime() {
    $currenttime = date("Y-m-d H:i:s");
    return($currenttime);
}

/**
 * returns current time in mysql DATETIME view
 * 
 * @return string
 */
function curtime() {
    $currenttime = date("H:i:s");
    return($currenttime);
}

/**
 * Returns current date in mysql DATETIME view
 * 
 * @return string
 */
function curdate() {
    $currentdate = date("Y-m-d");
    return($currentdate);
}

/**
 * Returns current year-month in mysql DATETIME view
 * 
 * @return string
 */
function curmonth() {
    $currentmonth = date("Y-m");
    return($currentmonth);
}

/**
 * Returns previous year-month in mysql DATETIME view
 * 
 * @return string
 */
function prevmonth() {
    $result = date("Y-m", strtotime("-1 months"));
    return ($result);
}

/**
 * Returns current year as just Y
 * 
 * @return string
 */
function curyear() {
    $currentyear = date("Y");
    return($currentyear);
}

/**
 * Returns all months with names in two digit notation
 * 
 * @param string $number
 * @return array/string
 */
function months_array($number = null) {
    $months = array(
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    );
    if (empty($number)) {
        return $months;
    } else {
        return $months[$number];
    }
}

/**
 * Retuns all months with names without begin zeros
 * 
 * @return array
 */
function months_array_wz() {
    $months = array(
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December');
    return($months);
}

/**
 * Returns visual bar with count/total proportional size
 * 
 * @param float $count
 * @param float $total
 * @return string
 */
function web_bar($count, $total) {
    $barurl = 'skins/bar.png';
    if ($total != 0) {
        $width = ($count / $total) * 100;
    } else {
        $width = 0;
    }

    $code = wf_img_sized($barurl, '', $width . '%', '14');
    return($code);
}

/**
 * Calculates percent value
 * 
 * @param float $sum
 * @param float $percent
 * 
 * @return float
 */
function zb_Percent($sum, $percent) {
    // и не надо ржать, я реально не могу запомнить чего куда делить и умножать
    $result = $percent / 100 * $sum;
    return ($result);
}

/**
 * Counts percentage between two values
 * 
 * @param float $valueTotal
 * @param float $value
 * 
 * @return float
 */
function zb_PercentValue($valueTotal, $value) {
    $result = 0;
    if ($valueTotal != 0) {
        $result = round((($value * 100) / $valueTotal), 2);
    }
    return ($result);
}
