<?php

/**
 * Gravatar API
 */

/**
 * Get gravatar URL by some email
 * 
 * @param string $email user email
 * @param bool $secure use HTTPS for API interraction?
 * @param string $avatarService gravatar|libravatar|etc
 * 
 * @return string
 */
function gravatar_GetUrl($email, $secure = false, $avatarService = '') {
    $hash = strtolower($email);
    $hash = md5($hash);
    $proto = ($secure) ? 'https' : 'http';

    switch ($avatarService) {
        case 'gravatar':
            $baseUrl = 'gravatar.com/avatar/';
            break;
        case 'libravatar':
            $baseUrl = 'seccdn.libravatar.org/avatar/';
            break;
        default:
            $baseUrl = 'seccdn.libravatar.org/avatar/';
            break;
    }


    $result = $proto . '://' . $baseUrl . $hash;
    return ($result);
}

/**
 * Function that returns avatar code by user email
 * 
 * @global object $ubillingConfig
 * @param string $email  user email
 * @param int $size   user avatar size
 * @param string $class custom image class
 * @param title $title custom image title
 * 
 * @return string
 */
function gravatar_GetAvatar($email, $size = '64', $class = '', $title = '') {
    global $ubillingConfig;
    $cachePath = DATA_PATH . 'avatars/';
    $gravatarOption = $ubillingConfig->getAlterParam('GRAVATAR_DEFAULT');
    $gravatarCacheTime = $ubillingConfig->getAlterParam('GRAVATAR_CACHETIME');
    $avatarService = $ubillingConfig->getAlterParam('GRAVATAR_SERVICE');
    $getsize = ($size) ? '&s=' . $size : '';
    //option not set
    if (!$gravatarOption) {
        $gravatarOption = 'monsterid';
    }

    $useSSL = ($gravatarCacheTime) ? false : true; //avoid mixed content issues on disabled caching cases
    $url = gravatar_GetUrl($email, $useSSL, $avatarService);
    $fullUrl = $url . '?d=' . $gravatarOption . $getsize;

    //avatar caching to local FS.
    if ($gravatarCacheTime) {
        $cacheTime = time() - ($gravatarCacheTime * 86400); //Expire time. Option in days.
        $avatarHash = md5($fullUrl) . '.jpg';
        $fullCachedPath = $cachePath . $avatarHash;
        $updateCache = true;
        if (file_exists($fullCachedPath)) {
            $updateCache = false;
            if ((filemtime($fullCachedPath) > $cacheTime)) {
                $updateCache = false;
            } else {
                $updateCache = true;
            }
        } else {
            $updateCache = true;
        }

        if ($updateCache) {
            $gravatarApi = new OmaeUrl($fullUrl);
            $remoteAvatar = $gravatarApi->response();
            if (!empty($remoteAvatar)) {
                file_put_contents($fullCachedPath, $remoteAvatar);
            }
        }

        $fullUrl = $fullCachedPath;
    }

    $result = wf_tag('img', false, $class, 'src="' . $fullUrl . '" alt="avatar" title="' . $title . '"');
    return ($result);
}

/**
 * Get framework user email
 * 
 * @param string $username rcms user login
 * 
 * @return string
 */
function gravatar_GetUserEmail($username) {
    $storePath = DATA_PATH . "users/";
    if (file_exists($storePath . $username)) {
        $userContent = file_get_contents($storePath . $username);
        $userData = unserialize($userContent);
        $result = $userData['email'];
    } else {
        $result = '';
    }
    return ($result);
}

/**
 * Shows avatar for some framework user - use only this in production!
 * 
 * @param string $username rcms user login
 * @param int    $size - size of returning avatar
 * @param string $class - class of image body
 * @param string $title - title of avatar image
 * 
 * @return string
 */
function gravatar_ShowAdminAvatar($username, $size = '', $class = '', $title = '') {
    $adminEmail = gravatar_GetUserEmail($username);
    if ($adminEmail) {
        $result = gravatar_GetAvatar($adminEmail, $size, $class, $title);
    } else {
        $result = wf_img('skins/admava.png', $title);
    }
    return ($result);
}
