<?php

namespace whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Crawler\Filter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Crawler\Filter;
use whm\Smoke\Config\Configuration;

class BlackWhiteListFilter implements Filter
{
    private $blacklist = array();
    private $whitelist;

    public function isFiltered(UriInterface $uri, UriInterface $startPage)
    {
        foreach ($this->whitelist as $whitelist) {
            if (preg_match($whitelist, (string)$uri)) {
                foreach ($this->blacklist as $blacklist) {
                    if (preg_match($blacklist, (string)$uri)) {
                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }

    public function init(Configuration $_configuration)
    {
        if ($_configuration->hasSection('blacklist')) {
            $this->blacklist = $_configuration->getSection('blacklist');
        }

        if ($_configuration->hasSection('whitelist')) {
            $this->whitelist = $_configuration->getSection('whitelist');
        } else {
            $this->whitelist = array('/(.*)/');
        }
    }

    public function isResponseFiltered(ResponseInterface $response, UriInterface $startUri)
    {
        return false;
    }
}
