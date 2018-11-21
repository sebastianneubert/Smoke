<?php

namespace whm\Smoke\Rules;

class Attribute
{
    const KEY_DETAIL_URL = '_detail_url';

    const KEY_GENERAL_TIMEOUT = 'timeout';

    private $key;
    private $value;
    private $isStorable;

    public function __construct($key, $value, $isStorable = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->isStorable = $isStorable;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isIsStorable()
    {
        return $this->isStorable;
    }
}
