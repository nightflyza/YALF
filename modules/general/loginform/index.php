<?php

if (!LOGGED_IN) {
    require_once ('api/libs/api.yalfloginform.php');
    $loginForm = new LoginForm();
    show_window('', $loginForm->render());
} else {
    //basic logout control
    show_window('', wf_Link('?forceLogout=true', __('Log out')));
}

