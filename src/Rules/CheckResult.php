<?php

namespace whm\Smoke\Rules;

use phm\HttpWebdriverClient\Http\Response\UriAwareResponse;
use Psr\Http\Message\ResponseInterface;

class CheckResult
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_NONE = 'none';

    const HINT_KEY = '__hint';

    private $status;
    private $value;
    private $message;
    private $attributes = array();
    private $ruleName;
    private $url;

    private $tool;

    /**
     * @var UriAwareResponse
     */
    private $response;

    /**
     * Result constructor.
     *
     * @param $status
     * @param $value
     * @param $message
     */
    public function __construct($status, $message = '', $value = null, $url = null)
    {
        $this->status = $status;
        $this->value = $value;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;
    }

    public function setHint($message)
    {
        $this->addAttribute(new Attribute(self::HINT_KEY, $message, false));
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getTool()
    {
        return $this->tool;
    }

    /**
     * @param string $tool
     */
    public function setTool($tool)
    {
        $this->tool = $tool;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return UriAwareResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getRuleName()
    {
        return $this->ruleName;
    }

    /**
     * @param mixed $ruleName
     */
    public function setRuleName($ruleName)
    {
        $this->ruleName = $ruleName;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
