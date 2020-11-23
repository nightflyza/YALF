<?php

global $system;
if ($system->getAuthEnabled()) {
    if (cfr('ROOT')) {

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

        //User editing
        if (ubRouting::checkPost($userManager::PROUTE_DOEDIT)) {
            $saveResult = $userManager->saveUser();
            if (empty($saveResult)) {
                ubRouting::nav($userManager::URL_ME . '&' . $userManager::ROUTE_EDIT . '=' . ubRouting::post($userManager::PROUTE_DOEDIT));
            } else {
                show_error($saveResult);
            }
        }

        if (!ubRouting::checkGet($userManager::ROUTE_EDIT) AND ! ubRouting::checkGet($userManager::ROUTE_EDIT)) {
            //rendering existing users list
            show_window(__('Available users'), $userManager->renderUsersList());
        } else {
            //rendering user data edit interface
            if (ubRouting::checkGet($userManager::ROUTE_EDIT)) {
                show_window(__('Edit user') . ' ' . ubRouting::get($userManager::ROUTE_EDIT), $userManager->renderEditForm(ubRouting::get($userManager::ROUTE_EDIT)));
                show_window('', wf_BackLink($userManager::URL_ME));
            }
        }
    } else {
        show_error(__('Permission denied'));
    }
} else {
    show_error(__('Authorization engine disabled'));
}