<?php

class YALFCore {

    /**
     * Contains raw YALF primary config as key=>value
     *
     * @var array
     */
    protected $config = array();

    const YALF_CONF_PATH = 'config/yalf.ini';

    public function __construct() {
        $this->loadConfig();
    }

    /**
     * Loads framework primary config into protected property for further usage
     * 
     * @return void
     */
    protected function loadConfig() {
        $this->config = rcms_parse_ini_file(self::YALF_CONF_PATH);
    }
    
    

}
