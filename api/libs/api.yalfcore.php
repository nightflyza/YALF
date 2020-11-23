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
     * Contains modules preloaded from general modules directory
     *
     * @var array
     */
    protected $modules = array();

    /**
     * Contains all rights injected with startup modules initialization
     *
     * @var array
     */
    protected $rights_database = array();

    /**
     * Is now some user logged in flag
     *
     * @var bool
     */
    protected $loggedIn = false;

    /**
     * Have current user root rights?
     *
     * @var bool
     */
    protected $root = false;

    /**
     * This array contain data from user's profile
     *
     * @var array
     */
    protected $user = array();

    /**
     * Contains current user rights
     *
     * @var array
     */
    protected $rights = array();

    /**
     * Some mystic output buffer. Used in i18n, users auth etc.
     *
     * @var array
     */
    protected $results = array();

    /**
     * Name of default auth cookie. May be configurable in future.
     *
     * @var string
     */
    protected $cookie_user = 'reloadcms_user';

    /**
     * System athorization enable flag
     *
     * @var bool
     */
    protected $authEnabled = false;

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
        $this->performUserAuth();
        $this->initializeUser();
        $this->initializeModules();
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
            //already preloaded from filesystem
            if (isset($this->modules['main'][$moduleName])) {
                //no module dir
                if (file_exists(MODULES_PATH . $moduleName)) {
                    //check for module codepart
                    if (file_exists(MODULES_PATH . $moduleName . '/' . self::MODULE_CODE_NAME)) {
                        //check for module definition
                        if (file_exists(MODULES_PATH . $moduleName . '/' . self::MODULE_DEFINITION)) {
                            $result = true;
                        }
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

        //system auth enabled
        if (isset($this->config['YALF_AUTH_ENABLED'])) {
            if ($this->config['YALF_AUTH_ENABLED']) {
                $this->authEnabled = true;
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
        $forceLoginForm = false;
        if (!$this->loggedIn) {
            $forceLoginForm = true;
        }
        if (!$forceLoginForm) {
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
        } else {
            //force login form switch
            if ($this->isModuleValid('loginform')) {
                $this->indexModule = 'loginform';
            } else {
                die('No module loginform exists');
            }
        }
    }

    /**
     * Loads some module by its name
     * 
     * @return void
     */
    public function loadCurrentModule() {
        require_once ($this->getIndexModulePath());
    }

    /**
     * Preloads all general modules from general modules directory
     * 
     * @return void
     */
    protected function initializeModules() {
        $disabledModules = array();
        //some modules may be disabled
        if (isset($this->config['YALF_DISABLED_MODULES'])) {
            if (!empty($this->config['YALF_DISABLED_MODULES'])) {
                $disabledModules = explode(',', $this->config['YALF_DISABLED_MODULES']);
                $disabledModules = array_flip($disabledModules);
            }
        }

        $allModules = scandir(MODULES_PATH);
        foreach ($allModules as $module) {
            if (!isset($disabledModules[$module])) {
                if (is_readable(MODULES_PATH . $module . '/' . self::MODULE_DEFINITION)) {
                    include_once(MODULES_PATH . $module . '/' . self::MODULE_DEFINITION);
                }
            }
        }
        // Register modules rights in main database
        foreach ($this->modules as $type => $modules) {
            foreach ($modules as $module => $moduledata) {
                foreach ($moduledata['rights'] as $right => $desc) {
                    $this->rights_database[$right] = $desc;
                }
            }
        }
    }

    /**
     * Registers module as preloaded
     * 
     * @param string $module
     * @param string $type
     * @param string $title
     * @param string $copyright
     * @param array $rights
     * 
     * @return void
     */
    protected function registerModule($module, $type, $title, $copyright = '', $rights = array()) {
        $this->modules[$type][$module]['title'] = $title;
        $this->modules[$type][$module]['copyright'] = $copyright;
        $this->modules[$type][$module]['rights'] = $rights;
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
                        $renderMenuEntry = true;
                        $icon = (!empty($each['ICON'])) ? $each['ICON'] : self::DEFAULT_ICON;
                        $icon = self::MENU_ICONS_PATH . $icon;
                        $name = __($each['NAME']);
                        $actClass = ($this->getCurrentModuleName() == $section) ? 'active' : '';
                        if (isset($each['NEED_RIGHT'])) {
                            if (!empty($each['NEED_RIGHT'])) {
                                $renderMenuEntry = $this->checkForRight($each['NEED_RIGHT']);
                            }
                        }
                        if ($renderMenuEntry) {
                            $result .= wf_tag('li', false, $actClass) . wf_Link($each['URL'], wf_img($icon) . ' ' . $name, false) . wf_tag('li', true);
                        }
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

    /**
     * Returns some user data as array
     * 
     * @param string $username
     * 
     * @return array/bool
     */
    public function getUserData($username) {
        $result = @unserialize(@file_get_contents(USERS_PATH . basename($username)));
        if (empty($result)) {
            return (false);
        } else {
            return $result;
        }
    }

    /**
     * Inits user and sets some cookies if its ok
     * 
     * @param bool $skipcheck Use this parameter to skip userdata checks
     * 
     * @return bool
     */
    protected function initializeUser($skipcheck = false) {
        //Inits default guest user
        $this->user = array('nickname' => __('Guest'), 'username' => 'guest', 'admin' => '', 'tz' => (int) @$this->config['default_tz'], 'accesslevel' => 0);
        $this->initialiseAccess($this->user['admin']);

        if (@$this->config['YALF_AUTH_ENABLED']) {
            // If user cookie is not present we exiting without error
            if (empty($_COOKIE[$this->cookie_user])) {
                $this->loggedIn = false;
                return (true);
            }

            // So we have a cookie, let's extract data from it
            $cookie_data = explode(':', $_COOKIE[$this->cookie_user], 2);
            if (!$skipcheck) {

                // If this cookie is invalid - we exiting destroying cookie and exiting with error
                if (sizeof($cookie_data) != 2) {
                    setcookie($this->cookie_user, null, time() - 3600);
                    return(false);
                }
                // Now we must validate user's data
                if (!$this->checkUserData($cookie_data[0], $cookie_data[1], 'user_init', true, $this->user)) {
                    setcookie($this->cookie_user, null, time() - 3600);
                    $this->loggedIn = false;
                    return(false);
                }
            }

            $userdata = $this->getUserData($cookie_data[0]);
            //failed to load user profile
            if ($userdata == false) {
                setcookie($this->cookie_user, null, time() - 3600);
                $this->loggedIn = false;
                return (false);
            }

            $this->user = $userdata;
            $this->loggedIn = true;

            // Initialise access levels
            $this->initialiseAccess($this->user['admin']);

            // Secure the nickname
            $this->user['nickname'] = htmlspecialchars($this->user['nickname']);
        } else {
            //All users around is logged in and have root rights
            $this->loggedIn = true;
            $this->root = true;
        }
    }

    /**
     * Performs user ath/deauth if required
     * 
     * @return void
     */
    protected function performUserAuth() {
        if ($this->config['YALF_AUTH_ENABLED']) {
            if (!empty($_POST['login_form'])) {
                $this->logInUser(@$_POST['username'], @$_POST['password'], !empty($_POST['remember']) ? true : false);
            }
            //default POST logout
            if (!empty($_POST['logout_form'])) {
                $this->logOutUser();
                rcms_redirect('index.php', true);
            }
            //additional get-request user auto logout sub
            if (!empty($_GET['idleTimerAutoLogout'])) {
                $this->logOutUser();
                rcms_redirect('index.php', true);
            }

            //normal get-request user logout
            if (!empty($_GET['forceLogout'])) {
                $this->logOutUser();
                rcms_redirect('index.php', true);
            }
        }
    }

    /**
     * Parses some rights string into protected rights property
     * 
     * @param string $rights
     * 
     * @return bool
     */
    protected function initialiseAccess($rights) {
        if ($rights !== '*') {
            preg_match_all('/\|(.*?)\|/', $rights, $rights_r);
            foreach ($rights_r[1] as $right) {
                $this->rights[$right] = (empty($this->rights_database[$right])) ? ' ' : $this->rights_database[$right];
            }
        } else {
            $this->root = true;
        }
        return (true);
    }

    /**
     * This function log out user from system and destroys his cookie.
     * 
     * @return bool
     */
    protected function logOutUser() {
        setcookie($this->cookie_user, '', time() - 3600);
        $_COOKIE[$this->cookie_user] = '';
        $this->initializeUser(false);
        return (true);
    }

    /**
     * This function check user's data and logs in him.
     * 
     * @param string $username
     * @param string $password
     * @param bool $remember
     * 
     * @return bool
     */
    protected function logInUser($username, $password, $remember) {
        $username = basename($username);
        if ($username == 'guest') {
            return false;
        }

        if (!$this->loggedIn AND $this->checkUserData($username, $password, 'user_login', false, $userdata)) {
            // OK... Let's allow user to log in :)
            setcookie($this->cookie_user, $username . ':' . $userdata['password'], ($remember) ? time() + 3600 * 24 * 365 : null);
            $_COOKIE[$this->cookie_user] = $username . ':' . $userdata['password'];
            $this->initializeUser(true);
            return (true);
        } else {
            return (false);
        }
    }

    /**
     * Returns logged in state
     * 
     * @return bool
     */
    public function getLoggedInState() {
        return($this->loggedIn);
    }

    /**
     * Returns system athorization flag state
     * 
     * @return bool
     */
    public function getAuthEnabled() {
        return($this->authEnabled);
    }

    /**
     * This function check user's data and validate his profile file.
     * 
     * @param string $username
     * @param string $password
     * @param string $report_to
     * @param boolean $hash
     * @param link $userdata
     * 
     * @return bool
     */
    protected function checkUserData($username, $password, $report_to, $hash, &$userdata) {
        if (preg_replace("/[\d\w]+/i", "", $username) != "") {
            $this->results[$report_to] = __('Invalid username');
            return false;
        }
        // If login is not exists - we exiting with error
        if (!is_file(USERS_PATH . $username)) {
            $this->results[$report_to] = __('There are no user with this username');
            return false;
        }
        // So all is ok. Let's load userdata
        $result = $this->getUserData($username);
        // If userdata is invalid we must exit with error
        if (empty($result))
            return false;
        // If password is invalid - exit with error
        if ((!$hash && md5($password) !== $result['password']) || ($hash && $password !== $result['password'])) {
            $this->results[$report_to] = __('Invalid password');
            return false;
        }
        // If user is blocked - exit with error
        if (@$result['blocked']) {
            $this->results[$report_to] = __('This account has been blocked by administrator');
            return false;
        }
        $userdata = $result;
        return true;
    }

    /**
     * Public getter for currently logged in user login
     * 
     * @return string
     */
    public function getLoggedInUsername() {
        return($this->user['username']);
    }

    /**
     * TODO: This piece of shit must be reviewed and rewritten
     * to many existing code use $system->getRightsForUser and $system->checkForRight('ONLINE') or something like
     */

    /**
     * Check if user have specified right
     * 
     * @param string $right
     * @param string $username
     * 
     * @return bool
     */
    public function checkForRight($right = '-any-', $username = '') {
        if (empty($username)) {
            $rights = &$this->rights;
            $root = &$this->root;
        } else {
            if (!$this->getRightsForUser($username, $rights, $root)) {
                return false;
            }
        }

        return $root OR ( $right == '-any-' && !empty($rights)) OR ! empty($rights[$right]);
    }

    /**
     * 
     * @param string $username
     * @param pointer $rights
     * @param pointer $root
     * 
     * @return bool
     */
    protected function getRightsForUser($username, &$rights, &$root) {
        if (!($userdata = $this->getUserData($username))) {
            return false;
        }

        $rights = array();
        $root = false;
        if ($userdata['admin'] !== '*') {
            preg_match_all('/\|(.*?)\|/', $userdata['admin'], $rights_r);
            foreach ($rights_r[1] as $right) {
                $rights[$right] = (empty($this->rights_database[$right])) ? ' ' : $this->rights_database[$right];
            }
        } else {
            $root = true;
        }

        return true;
    }

}
