<?php

namespace whm\Smoke\Http;

use Ivory\HttpAdapter\Message\Request;
use whm\Html\Uri;

class Response extends \Ivory\HttpAdapter\Message\Response
{
    private $contents;

    public function getStatus()
    {
        return $this->getStatusCode();
    }

    public function getContentType()
    {
        $exploded = explode(';', $this->hasHeader('Content-Type') ? $this->getHeader('Content-Type')[0] : null);

        return array_shift($exploded);
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        if ($this->getParameters('request') !== null) {
            if (array_key_exists('request', $this->getParameters('request'))) {
                if ($this->getParameters('request')['request']->getParameters() !== null) {
                    if (array_key_exists('parent_request', $this->getParameters('request')['request']->getParameters())) {
                        return $this->getParameters('request')['request']->getParameters()['parent_request']->getUri();
                    }
                }
            }
        }

        return $this->getParameter('request')->getUri();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->getParameter('request');
    }

    public function getDuration()
    {
        return $this->getParameter('duration');
    }

    public function getBody()
    {
        if (!$this->contents) {
            $contents = parent::getBody()->getContents();

            if (false !== $content = @gzdecode($contents)) {
                $contents = $content;
            }

            $this->contents = $contents;
        }

        return $this->contents;
    }
}
