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

    public function __construct($id)
    {
        $this->loadState();
    }

    public function login($user, $password)
    {
        $client = new SoapClient("https://certification.dlna.org/services/drm.asmx?wsdl");
        $result = $client->DrmLogIn($this->userId, $password);
        return $result;
    }

    private function loadState()
    {
        if(file_exists)
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
        $status = array(
            'userId' => $this->userId,
            'sessionId' => $this->sessionId
        );
        file_put_contents(DRM_SESSIONS, json_encode($status));
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