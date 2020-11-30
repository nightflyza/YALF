<?php

if (cfr('TESTING')) {
    show_window('Just a test module', 'subj');
    
    deb(wf_DatePicker('yep'));
} else {
    show_error(__('Access denied'));
}