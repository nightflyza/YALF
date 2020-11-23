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
                return($result);
            }

        }

        $userManager = new UserManager();

        //User deletion
        if (ubRouting::checkGet($userManager::ROUTE_DELETE)) {
            $userManager->deleteUser(ubRouting::get($userManager::ROUTE_DELETE));
            ubRouting::nav($userManager::URL_ME);
        }

        show_window(__('Available users'), $userManager->renderUsersList());
    } else {
        show_error(__('Permission denied'));
    }
} else {
    show_error(__('Authorization engine disabled'));
}