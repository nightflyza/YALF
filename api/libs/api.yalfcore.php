<?php

class YALFCore {

    /**
     * Contains raw YALF primary config as key=>value
     *
     * @var array
     */
    protected $config = array();

    /**
     * Contains names of libs to load
     *
     * @var array
     */
    protected $loadLibs = array();

    const YALF_CONF_PATH = 'config/yalf.ini';

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
        if (isset($this->config['LOAD_LIBS'])) {
            if (!empty($this->config['LOAD_LIBS'])) {
                $libsTmp = explode(',', $this->config['LOAD_LIBS']);
                if (!empty($libsTmp)) {
                    foreach ($libsTmp as $io => $name) {
                        $this->loadLibs[$name] = $io;
                    }
                }
            }
        }
    }

}
