<?php

namespace whm\Smoke\Extensions\CookieMaker;

use Koalamon\CookieMakerHelper\CookieMaker;
use Koalamon\FallbackHelper\FallbackHelper;
use whm\Smoke\Http\Session;
use whm\Smoke\Scanner\SessionContainer;

class CookieMakerExtension
{
    private $executable = 'CookieMaker';

    private $sessionContainer;

    public function init(array $sessions, $executable = null)
    {
        if ($executable) {
            $this->executable = $executable;
        }

        $this->sessionContainer = new SessionContainer();

        foreach ($sessions as $sessionName => $session) {

            try {
                $cookieMaker = new CookieMaker($this->executable);
                $cookies = $cookieMaker->getCookies($session);
            } catch (\Exception $e) {
                $fallbackHelper = new FallbackHelper();
                if (!$fallbackHelper->isFallbackServer()) {
                    $fallbackHelper->doFallback($e->getMessage());
                    exit(1);
                }
            }

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
