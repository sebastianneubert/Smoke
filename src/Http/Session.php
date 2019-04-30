<?php

namespace whm\Smoke\Http;

class Session
{
    private $cookies = array();

    public function addCookie($key, $value)
    {
        $this->cookies[$key] = $value;
    }

    public function getCookies()
    {
        return $this->cookies;
    }
}
