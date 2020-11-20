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
     * Current skin name
     *
     * @var string
     */
    protected $skin = 'paper';

    /**
     * Default language name
     *
     * @var string
     */
    protected $language = 'english';

    /**
     * Application renderer type. Can be WEB/CLI at this moment
     *
     * @var string
     */
    protected $renderer = 'WEB';

    /**
     * Contains page title here
     *
     * @var string
     */
    protected $pageTitle = '';

    /**
     * Is global menu rendering enabled flag
     *
     * @var bool
     */
    protected $globalMenuEnabled = false;

    /**
     * Some paths, routes etc
     */
    const YALF_CONF_PATH = 'config/yalf.ini';
    const YALF_MENU_PATH = 'config/globalmenu.ini';
    const LIBS_PATH = 'api/libs/';
    const LANG_PATH = 'languages/';
    const MODULE_CODE_NAME = 'index.php';
    const MODULE_DEFINITION = 'module.php';
    const ROUTE_MODULE_LOAD = 'module';
    const SKINS_PATH = 'skins/';
    const MENU_ICONS_PATH = 'skins/menuicons/';
    const SKIN_TEMPLATE_NAME = 'template.html';

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
        $this->config = parse_ini_file(self::YALF_CONF_PATH);
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
                //check for module codepart
                if (file_exists(MODULES_PATH . $moduleName . '/' . self::MODULE_CODE_NAME)) {
                    //no module definition
                    if (file_exists(MODULES_PATH . $moduleName . '/' . self::MODULE_DEFINITION)) {
                        $result = true;
                    }
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

        //template selection
        if (isset($this->config['YALF_SKIN'])) {
            if (!empty($this->config['YALF_SKIN'])) {
                $this->skin = $this->config['YALF_SKIN'];
                if (!file_exists(self::SKINS_PATH . $this->skin . '/' . self::SKIN_TEMPLATE_NAME)) {
                    die('Template code not found ' . self::SKINS_PATH . $this->skin . '/' . self::SKIN_TEMPLATE_NAME . ' set in YALF_SKIN');
                }
            }
        }

        //locale selection
        if (isset($this->config['YALF_LANG'])) {
            if (!empty($this->config['YALF_LANG'])) {
                $this->language = $this->config['YALF_LANG'];
            }
        }

        //page title setup
        if (isset($this->config['YALF_TITLE'])) {
            if (!empty($this->config['YALF_TITLE'])) {
                $this->pageTitle = $this->config['YALF_TITLE'];
            }
        }

        //global menu rendering flag setup
        if (isset($this->config['YALF_MENU_ENABLED'])) {
            if ($this->config['YALF_MENU_ENABLED']) {
                $this->globalMenuEnabled = true;
            }
        }

        //renderer type detection
        if (isset($this->config['LAYER_CLIRENDER'])) {
            $this->renderer = 'CLI';
        }

        if (isset($this->config['LAYER_WEBRENDER'])) {
            $this->renderer = 'WEB';
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

    /**
     * Returns current module name
     * 
     * @return string
     */
    public function getCurrentModuleName() {
        return($this->indexModule);
    }

    /**
     * Returns current locale language full path
     * 
     * @return string
     */
    public function getLangPath() {
        return(self::LANG_PATH . $this->language . '/');
    }

    /**
     * Returns current locale ID as two-letters code
     * 
     * @return string
     */
    public function getCurLang() {
        return(substr($this->language, 0, '2'));
    }

    /**
     * Returns current skin path
     * 
     * @return string
     */
    public function getSkinPath() {
        return(self::SKINS_PATH . $this->skin . '/');
    }

    /**
     * Returns current application renderer type
     * 
     * @return string
     */
    public function getRenderer() {
        return($this->renderer);
    }

    /**
     * Returns current application page title
     * 
     * @return string
     */
    public function getPageTitle() {
        return($this->pageTitle);
    }

    /**
     * Sets current page title text
     * 
     * @param string $title
     * 
     * @return void
     */
    public function setPageTitle($title = '') {
        $this->pageTitle = $title;
    }

    /**
     * Returns ISP logo image code
     * 
     * @return string
     */
    public function renderLogo() {

        $result = '';
        if (isset($this->config['YALF_LOGO'])) {
            if ((!empty($this->config['YALF_APP'])) AND ( !empty($this->config['YALF_URL'])) AND ( (!empty($this->config['YALF_LOGO'])))) {
                $rawUrl = strtolower($this->config['YALF_URL']);
                if (stripos($rawUrl, 'http') === false) {
                    $rawUrl = 'http://' . $rawUrl;
                } else {
                    $rawUrl = $rawUrl;
                }
                $result = '<a href="' . $rawUrl . '" target="_BLANK"><img src="' . $this->config['YALF_LOGO'] . '" title="' . __($this->config['YALF_APP']) . '"></a>';
            }
        }
        return ($result);
    }

    /**
     * Renders application menu
     * 
     * @return string
     */
    public function renderMenu() {
        $result = '';
        if ($this->globalMenuEnabled) {
            if (file_exists(self::YALF_MENU_PATH)) {
                $rawData = parse_ini_file(self::YALF_MENU_PATH, true);
                if (!empty($rawData)) {
                    foreach ($rawData as $section => $each) {
                        $icon = (!empty($each['ICON'])) ? $each['ICON'] : self::DEFAULT_ICON;
                        $icon = self::MENU_ICONS_PATH . $icon;
                        $name = __($each['NAME']);
                        $actClass = ($this->getCurrentModuleName() == $section) ? 'active' : '';
                        $result .= wf_tag('li', false, $actClass) . wf_Link($each['URL'], wf_img($icon) . ' ' . $name, false) . wf_tag('li', true);
                    }
                }
            }
        }
        return($result);
    }

    /**
     * Returns global menu enable state flag
     * 
     * @return bool
     */
    public function getGlobalMenuFlag() {
        return($this->globalMenuEnabled);
    }

}
