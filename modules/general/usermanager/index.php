<?php

global $system;
if ($system->getAuthEnabled()) {
    if (cfr('ROOT')) {

        /**
         * Basic user management interface
         */
        class UserManager {

            /**
             * YalfCore system object placeholder
             *
             * @var object
             */
            protected $system = '';

            /**
             * System messages helper instance
             *
             * @var object
             */
            protected $messages = '';

            /**
             * Some static routes etc
             */
            const URL_ME = '?module=usermanager';
            const ROUTE_DELETE = 'deleteuser';

            /**
             * New user parameters here
             */
            const PROUTE_DOREGISTER = 'registernewuserplease';
            const PROUTE_DOEDIT = 'editthisuser';
            const PROUTE_USERNAME = 'username';
            const PROUTE_PASSWORD = 'password';
            const PROUTE_PASSWORDCONFIRM = 'confirmation';
            const PROUTE_NICKNAME = 'nickname';
            const PROUTE_EMAIL = 'email';

            /**
             * Creates new user manager instance
             */
            public function __construct() {
                $this->initMessages();
                $this->initSystemCore();
            }

            /**
             * Inits current system core instance for further usage
             * 
             * @global object $system
             * 
             * @return void
             */
            protected function initSystemCore() {
                global $system;
                $this->system = $system;
            }

            /**
             * Inits system messages helper for further usage
             * 
             * @return
             */
            protected function initMessages() {
                $this->messages = new UbillingMessageHelper();
            }

            /**
             * Deletes existing user
             * 
             * @param string $userName
             * 
             * @return void
             */
            public function deleteUser($userName) {
                if (file_exists(USERS_PATH . $userName)) {
                    unlink(USERS_PATH . $userName);
                    log_register('USER DELETE {' . $userName . '}');
                }
            }

            /**
             * Renders list of available users with some controls
             * 
             * @return string
             */
            public function renderUsersList() {
                $result = '';
                $allUsers = rcms_scandir(USERS_PATH);
                if (!empty($allUsers)) {

                    $cells = wf_TableCell(__('User'));
                    $cells .= wf_TableCell(__('Actions'));
                    $rows = wf_TableRow($cells, 'row1');
                    foreach ($allUsers as $index => $eachUser) {
                        $cells = wf_TableCell($eachUser);
                        $actControls = '';
                        $actControls = wf_JSAlert(self::URL_ME . '&' . self::ROUTE_DELETE . '=' . $eachUser, web_delete_icon(), $this->messages->getDeleteAlert());
                        $cells .= wf_TableCell($actControls);
                        $rows .= wf_TableRow($cells, 'row5');
                    }

                    $result .= wf_TableBody($rows, '100%', 0, 'sortable');
                } else {
                    $result .= $this->messages->getStyledMessage(__('Nothing to show'), 'warning');
                }

                $result .= wf_delimiter();
                $result .= wf_modalAuto(web_add_icon() . ' ' . __('Register new user'), __('Register new user'), $this->renderRegisterForm(), 'ubButton');
                return($result);
            }

            /**
             * Renders new user registration form
             * 
             * @return string
             */
            protected function renderRegisterForm() {
                $result = '';

                $inputs = wf_HiddenInput(self::PROUTE_DOREGISTER, 'true');
                $inputs .= wf_TextInput(self::PROUTE_USERNAME, __('Login'), '', true, 20, 'alphanumeric');
                $inputs .= wf_PasswordInput(self::PROUTE_PASSWORD, __('Password'), '', true, 20);
                $inputs .= wf_PasswordInput(self::PROUTE_PASSWORDCONFIRM, __('Password confirmation'), '', true, 20);
                $inputs .= wf_TextInput(self::PROUTE_NICKNAME, __('NickName'), '', true, 20, 'alphanumeric');
                $inputs .= wf_TextInput(self::PROUTE_EMAIL, __('Email'), '', true, 20);
                $inputs .= wf_Submit(__('Create'));

                $result .= wf_Form('', 'POST', $inputs, 'glamour');
                return($result);
            }

            /**
             * Registers new user
             * 
             * @return void/string on error
             */
            public function createUser() {
                $result = '';

                //all of this props are required for normal registration
                $requiredParams = array(
                    self::PROUTE_USERNAME,
                    self::PROUTE_PASSWORD,
                    self::PROUTE_PASSWORDCONFIRM,
                    self::PROUTE_NICKNAME,
                    self::PROUTE_NICKNAME,
                    self::PROUTE_EMAIL
                );

                if (ubRouting::checkPost($requiredParams)) {
                    $newLogin = ubRouting::post(self::PROUTE_USERNAME, 'vf');
                    $newPasword = ubRouting::post(self::PROUTE_PASSWORD);
                    $confirmation = ubRouting::post(self::PROUTE_PASSWORDCONFIRM);
                    $newNickName = ubRouting::post(self::PROUTE_NICKNAME, 'mres');
                    $newEmail = ubRouting::post(self::PROUTE_EMAIL, 'mres');
                    $newUserRights = '';

                    if (!empty($newLogin)) {
                        $userDataPath = USERS_PATH . $newLogin;
                        if (!file_exists($userDataPath)) {
                            if ($newPasword == $confirmation) {
                                if (!empty($newEmail)) {
                                    if (!empty($newNickName)) {
                                        $newUserData = array(
                                            'admin' => $newUserRights,
                                            'password' => md5($newPasword),
                                            'nickname' => $newNickName,
                                            'username' => $newLogin,
                                            'email' => $newEmail,
                                            'hideemail' => '1',
                                            'tz' => '2'
                                        );

                                        $saveUserData = serialize($newUserData);

                                        file_put_contents($userDataPath, $saveUserData);
                                        log_register('USER REGISTER {' . $newLogin . '}');
                                    } else {
                                        $result .= __('Empty NickName');
                                    }
                                } else {
                                    $result .= __('Empty email');
                                }
                            } else {
                                $result .= __('Passwords did not match');
                            }
                        } else {
                            $result .= __('User already exists');
                        }
                    } else {
                        $result .= __('Empty login');
                    }
                }
                return($result);
            }

        }

        $userManager = new UserManager();

        //User deletion
        if (ubRouting::checkGet($userManager::ROUTE_DELETE)) {
            $userManager->deleteUser(ubRouting::get($userManager::ROUTE_DELETE));
            ubRouting::nav($userManager::URL_ME);
        }

        //User creation
        if (ubRouting::checkPost($userManager::PROUTE_DOREGISTER)) {
            $registerResult = $userManager->createUser();
            if (empty($registerResult)) {
                ubRouting::nav($userManager::URL_ME);
            } else {
                show_error($registerResult);
            }
        }
        
        show_window(__('Available users'), $userManager->renderUsersList());
    } else {
        show_error(__('Permission denied'));
    }
} else {
    show_error(__('Authorization engine disabled'));
}