<?php

namespace whm\Smoke\Scanner;

use whm\Smoke\Http\Response;

class Result
{
    private $type;
    private $messages = array();
    private $response;
    private $parent;
    private $url;
    private $duration;

    private $value;
    private $attributes = array();

    public function __construct($uri, $type, Response $response, $parent, $duration)
    {
        $this->type = $type;
        $this->url = $uri;
        $this->response = $response;
        $this->parent = $parent;
        $this->duration = $duration;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function isFailure()
    {
        return $this->type === Scanner::ERROR;
    }

    public function isSuccess()
    {
        return $this->type === Scanner::PASSED;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}
