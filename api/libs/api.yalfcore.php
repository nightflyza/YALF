<?php

class YALFCore {

    /**
     * Contains raw YALF primary config as key=>value
     *
     * @var array
     */
    protected $config = array();

    /**
     * Contains names of libs to load as path=>layer
     *
     * @var array
     */
    protected $loadLibs = array();

    const YALF_CONF_PATH = 'config/yalf.ini';
    const LIBS_PATH = 'api/libs/';

    public function __construct() {
        $this->loadConfig();
        $this->setOptions();
    }

    /**
     * Loads framework primary config into protected property for further usage
     * 
     * @return void
     */
    protected function loadConfig() {
        $this->config = rcms_parse_ini_file(self::YALF_CONF_PATH);
    }

    /**
     * Preprocess some options
     * 
     * @return void
     */
    protected function setOptions() {
        if (!empty($this->config)) {
            foreach ($this->config as $eachOption => $eachValue) {
                if (ispos($eachOption, 'LAYER_')) {
                    if (!empty($eachValue)) {
                        $requirements = explode(',', $eachValue);
                        if (!empty($requirements)) {
                            foreach ($requirements as $io => $eachLib) {
                                $this->loadLibs[self::LIBS_PATH . 'api.' . $eachLib . '.php'] = $eachOption;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns array of libs required for loading layers
     * 
     * @return array
     */
    public function getLibs() {
        return($this->loadLibs);
    }

}
