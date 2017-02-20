<?php

namespace whm\Smoke\Extensions\CookieMaker;

use whm\Smoke\Http\Session;
use whm\Smoke\Scanner\SessionContainer;

class CookieMakerExtension
{
    private $executable = 'CookieMaker';

    private $sessionContainer;

    public function init(array $sessions, $executable = null, $cacheDir = '/tmp/cookieMaker', $webdriverHost = 'webdriver')
    {
        if ($executable) {
            $this->executable = $executable;
        }

        $this->sessionContainer = new SessionContainer();

        foreach ($sessions as $sessionName => $session) {
            $command = $this->executable . " '" . json_encode($session) . "' '" . $cacheDir . "' '" . $webdriverHost . "'";

            exec($command, $output, $return);

            $cookies = json_decode($output[0]);

            $session = new Session();

            foreach ($cookies as $key => $value) {
                $session->addCookie($key, $value);
            }

            $this->sessionContainer->addSession($sessionName, $session);
        }
    }

    /**
     * @Event("ResponseRetriever.setSessionContainer.before")
     */
    public function addSessions(SessionContainer $sessionContainer)
    {
        $sessionContainer->addContainer($this->sessionContainer);
    }
}
