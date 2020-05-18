<?php



/**
 * Shows data in primary content container
 * 
 * @global string $ContentContainer
 * @param string $title
 * @param string $align
 * @param string $data
 */
function show_window($title, $data, $align = 'left') {
    global $contentContainer;
    $window_content = '
        <table width="100%" border="0">
        <tr>
        <td><h2>' . @$title . '</h2></td>
        </tr>
        <tr>
        <td valign="top">
        ' . @$data . '
        </td>
        </tr>
        </table>
        ';
    $contentContainer = $contentContainer . $window_content;
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