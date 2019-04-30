<?php

namespace whm\Smoke\Scanner;

use whm\Smoke\Http\Session;

class SessionContainer
{
    private $sessions = array();

    public function addSession($key, Session $session)
    {
        $this->sessions[$key] = $session;
    }

    /**
     * @param $key
     *
     * @return Session
     */
    public function getSession($key)
    {
        return $this->sessions[$key];
    }

    public function hasSession($key)
    {
        return array_key_exists($key, $this->sessions);
    }

    public function getSessions()
    {
        return $this->sessions;
    }

    public function addContainer(SessionContainer $sessionContainer)
    {
        foreach ($sessionContainer->getSessions() as $sessionName => $session) {
            $this->addSession($sessionName, $session);
        }
    }
}
