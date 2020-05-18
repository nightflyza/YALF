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

    /**
     * Name of module which will be used as main route
     *
     * @var string
     */
    protected $indexModule = 'index';

    /**
     * Some paths, routes etc
     */
    const YALF_CONF_PATH = 'config/yalf.ini';
    const LIBS_PATH = 'api/libs/';
    const MODULE_CODE_NAME = 'index.php';
    const ROUTE_MODULE_LOAD = 'module';

    public function __construct() {
        $this->loadConfig();
        $this->setOptions();
        $this->switchIndexModule();
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
     * Checks is module path valid and loadable?
     * 
     * @param string $moduleName
     * 
     * @return bool
     */
    protected function isModuleValid($moduleName) {
        $result = false;
        $moduleName = preg_replace('/\0/s', '', $moduleName);
        $moduleName = preg_replace("#[^a-z0-9A-Z]#Uis", '', $moduleName);
        if (!empty($moduleName)) {
            //no module dir
            if (file_exists(MODULES_PATH . $moduleName)) {
                //no module codepart
                if (file_exists(MODULES_PATH . $moduleName . '/' . self::MODULE_CODE_NAME)) {
                    $result = true;
                }
            }
        }

        return($result);
    }

    /**
     * Preprocess some options an sets internal props for further usage
     * 
     * @return void
     */
    protected function setOptions() {
        //library layers preloading
        if (!empty($this->config)) {
            foreach ($this->config as $eachOption => $eachValue) {
                if (ispos($eachOption, 'LAYER_')) {
                    if (!empty($eachValue)) {
                        $requirements = explode(',', $eachValue);
                        if (!empty($requirements)) {
                            foreach ($requirements as $io => $eachLib) {
                                $libPath = self::LIBS_PATH . 'api.' . $eachLib . '.php';
                                if (!file_exists($libPath)) {
                                    die('Library ' . $libPath . ' required for loading of feature layer ' . $eachOption . ' is not exists!');
                                } else {
                                    $this->loadLibs[$libPath] = $eachOption;
                                }
                            }
                        }
                    }
                }
            }
        }

        //initial module preloading
        if (isset($this->config['INDEX_MODULE'])) {
            if (!empty($this->config['INDEX_MODULE'])) {
                $this->indexModule = $this->config['INDEX_MODULE'];
                if (!$this->isModuleValid($this->indexModule)) {
                    die('Module code ' . MODULES_PATH . $this->indexModule . '/' . self::MODULE_CODE_NAME . ' set in INDEX_MODULE is not exists!');
                }
            }
        }
    }

    /**
     * Switches index module if its required
     * 
     * @return void
     */
    protected function switchIndexModule() {
        if (isset($_GET[self::ROUTE_MODULE_LOAD])) {
            $moduleName = $_GET[self::ROUTE_MODULE_LOAD];
            $moduleName = preg_replace('/\0/s', '', $moduleName);
            $moduleName = preg_replace("#[^a-z0-9A-Z]#Uis", '', $moduleName);
            if ($this->isModuleValid($_GET[self::ROUTE_MODULE_LOAD])) {
                $this->indexModule = $moduleName;
            } else {
                die('No module ' . $moduleName . ' exists');
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

    /**
     * Returns full path of index module aka main route
     * 
     * @return string
     */
    public function getIndexModulePath() {
        return(MODULES_PATH . $this->indexModule . '/' . self::MODULE_CODE_NAME);
    }

}
