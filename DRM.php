<?php

/**
 * DRM short summary.
 *
 * DRM description.
 *
 * @version 1.0
 * @author jeremy
 */
class DRM
{
    public $userId = false;
    public $sessionId = false;

    private $lock = false;
    private $client = false;

    public function __construct()
    {
        $this->loadState();
        $this->client = new SoapClient("https://certification.dlna.org/services/drm.asmx?wsdl");
    }

    public function login($username, $password)
    {
        if(false != $username && "" != $username &&
           false != $password && "" != $password)
        {
            $result = $this->client->DrmLogIn(array(
                'emailAddress' => $username,
                'pword' => $password));
            if(isset($result->DrmLogInResult))
            {
                $drmInfo = json_decode($result->DrmLogInResult);
                if(isset($drmInfo->userID) && '' != $drmInfo->userID &&
                   isset($drmInfo->sessionID) && '' != $drmInfo->sessionID)
                {
                    $this->userId = $drmInfo->userID;
                    $this->sessionId = $drmInfo->sessionID;
                    $this->lock();
                    $this->saveState();
                    $this->unlock();

                    return true;
                }
            }
        }

        $this->logout();
        return false;
    }

    public function logout()
    {
        $this->lock();

        if(file_exists(DRM_SESSIONS)) {
            unlink(DRM_SESSIONS);
        }
        $this->userId = false;
        $this->sessionId = false;

        $this->unlock();
    }

    public function info()
    {
        $result = $this->client->DRMSessionCheck(array(
            'userID' => $this->userId,
            'sessionID' => $this->sessionId));

        $info = array(
            'userId' => $this->userId,
            'sessionId' => $this->sessionId
        );

        if(isset($result->DRMSessionCheckResult))
        {
            $sessionInfo = json_decode($result->DRMSessionCheckResult);
            $info['message'] = isset($sessionInfo->message) ? $sessionInfo->message : 'error';
            $info['profile'] = isset($sessionInfo->profile) ? $sessionInfo->message : false;
        }

        return $info;
    }

    private function loadState()
    {
        if(file_exists(DRM_SESSIONS))
        {
            $drm = json_decode(file_get_contents(DRM_SESSIONS), true);
            if(array_key_exists('userId', $drm)) {
                $this->userId = $drm['userId'];
            }
            if(array_key_exists('sessionId', $drm)) {
                $this->sessionId = $drm['sessionId'];
            }
        }
    }

    private function saveState()
    {
        file_put_contents(DRM_SESSIONS, json_encode($this->info()));
    }

    private function lock()
    {
        $this->lock = fopen(DRM_SESSIONS.'.lock', "w");
        return flock($this->lock, LOCK_EX); // acquire an exclusive lock
    }

    private function unlock()
    {
        flock($this->lock, LOCK_UN);    // release the lock
        fclose($this->lock);

        $this->lock = false;
        unlink(DRM_SESSIONS.'.lock');
    }

}