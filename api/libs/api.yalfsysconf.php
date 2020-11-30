<?php

/**
 * System configuration editor class
 */
class YalfSysConf {

    /**
     * Contains configs array editable from web as filePath=>just name
     *
     * @var array
     */
    protected $editableConfigs = array();

    /**
     * Message helper system object placeholder
     *
     * @var object
     */
    protected $messages = '';

    /**
     * Some URLs, routes.. etc
     */
    const URL_ME = '?module=sysconf';
    const ROUTE_EDIT = 'editconfig';
    const PROUTE_FILEPATH = 'editfilepath';
    const PROUTE_FILECONTENT = 'editfilecontent';

    public function __construct() {
        $this->initMessages();
        $this->loadEditableConfigs();
    }

    /**
     * Inits system messages helper for further usage
     * 
     * @return void
     */
    protected function initMessages() {
        $this->messages = new UbillingMessageHelper();
    }

    /**
     * Loads config files editable from web
     * 
     * @return void
     */
    protected function loadEditableConfigs() {
        global $system;
        $rawConf = parse_ini_file($system::YALF_CONF_PATH);
        if (!empty($rawConf)) {
            if (isset($rawConf['YALF_EDITABLE_CONFIGS'])) {
                if (!empty($rawConf['YALF_EDITABLE_CONFIGS'])) {
                    $rawOption = explode(',', $rawConf['YALF_EDITABLE_CONFIGS']);
                    if (!empty($rawOption)) {
                        foreach ($rawOption as $index => $eachConfig) {
                            $this->editableConfigs[$eachConfig] = basename($eachConfig);
                        }
                    }
                }
            }
        }
    }

    /**
     * Renders module controls
     * 
     * @return string
     */
    public function rendedControls() {
        $result = '';
        if (!empty($this->editableConfigs)) {
            foreach ($this->editableConfigs as $eachPath => $eachName) {
                $encPath = base64_encode($eachPath);
                $result .= wf_Link(self::URL_ME . '&' . self::ROUTE_EDIT . '=' . $encPath, web_edit_icon() . ' ' . $eachName, false, 'ubButton');
            }
        }
        return($result);
    }

    /**
     * Returns simple text editing form
     * 
     * @param string $path
     * @param string $content
     * 
     * @return string
     */
    protected function fileEditorForm($path, $content) {
        $result = '';
        $content = htmlentities($content, ENT_COMPAT, "UTF-8");

        $inputs = wf_HiddenInput(self::PROUTE_FILEPATH, $path);
        $inputs .= wf_tag('textarea', false, 'fileeditorarea', 'name="' . self::PROUTE_FILECONTENT . '" cols="145" rows="30"');
        $inputs .= $content;
        $inputs .= wf_tag('textarea', true);
        $inputs .= wf_tag('br');
        $inputs .= wf_Submit(__('Save'));
        $result .= wf_Form('', 'POST', $inputs, 'glamour');
        $result.= wf_delimiter();
        $result.= wf_BackLink(self::URL_ME);
        return ($result);
    }

    /**
     * Catches editing request and render edit area if required
     * 
     * @return string
     */
    public function renderFileEditor() {
        $result = '';
        if (ubRouting::checkGet(self::ROUTE_EDIT)) {
            $fileToEdit = base64_decode(ubRouting::get(self::ROUTE_EDIT));
            if (file_exists($fileToEdit)) {
                if (is_readable($fileToEdit)) {
                    if (is_writable($fileToEdit)) {
                        $fileContent = file_get_contents($fileToEdit);
                        $result .= $this->fileEditorForm($fileToEdit, $fileContent);
                    } else {
                        $result .= $this->messages->getStyledMessage(__('File is not writable') . ': ' . $fileToEdit, 'error');
                    }
                } else {
                    $result .= $this->messages->getStyledMessage(__('Cant read file') . ': ' . $fileToEdit, 'error');
                }
            } else {
                $result .= $this->messages->getStyledMessage(__('File not exists') . ': ' . $fileToEdit, 'error');
            }
        }
        return($result);
    }

    /**
     * Saves editing file if its exists/readable/writable on receiving expected POST variables
     * 
     * @return void/string on error
     */
    public function saveFile() {
        $result = '';
        if (ubRouting::checkPost(array(self::PROUTE_FILECONTENT, self::PROUTE_FILEPATH))) {
            $fileToEdit = ubRouting::post(self::PROUTE_FILEPATH);
            if (file_exists($fileToEdit)) {
                if (is_readable($fileToEdit)) {
                    if (is_writable($fileToEdit)) {
                        $fileContent = ubRouting::post(self::PROUTE_FILECONTENT);
                        if (ispos($fileContent, "\r\n")) {
                            //cleanup to unix EOL
                            $fileContent = str_replace("\r\n", "\n", $fileContent);
                        }
                        file_put_contents($fileToEdit, $fileContent);
                    } else {
                        $result .= __('File is not writable') . ': ' . $fileToEdit;
                    }
                } else {
                    $result .= __('Cant read file') . ': ' . $fileToEdit;
                }
            } else {
                $result .= __('File not exists') . ': ' . $fileToEdit;
            }
        }
        return($result);
    }

}
