<?php

namespace whm\Smoke\Extensions\SmokeFilter;

use phm\HttpWebdriverClient\Http\Response\UriAwareResponse;
use phmLabs\Components\Annovent\Event\Event;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Yaml\EnvAwareYaml;

/**
 * Class FilterExtension.
 *
 * @example for filter file
 *
 * filters:
 *   _HttpHeaderSuccessStatus:
 *     - http://www.wunderweib.de/tag/
 *     - http://www.amilio.de/old-but-mandatory-file/
 *     - http://www.amilio.de/images/(.*)
 *
 * exclusive:
 *   _HttpHeaderSuccessStatus:
 *     - http://www.wunderweib.de/tag/
 *     - http://www.amilio.de/old-but-mandatory-file/
 */
class FilterExtension
{
    /**
     * @var Retriever
     */
    private $retriever;

    private $filters = array();
    private $exclusives = array();

    private $currentModus = self::MODUS_FILTER;

    const MODUS_FILTER = 'filter';
    const MODUS_EXCLUSIVE = 'exclusive';

    public function init($filters = array(), $filterFile = '', $exclusive = array())
    {
        if (count($exclusive) > 0 && (count($filters) > 0 || $filterFile !== '')) {
            throw new \RuntimeException("It's not possible to define filter lists and an exclusive list at the same time [Extension: " . get_class($this) . '].');
        }

        if ($filterFile !== '') {
            if (!file_exists($filterFile)) {
                throw new \RuntimeException('Filter file not found: ' . $filterFile);
            }

            $filterElements = EnvAwareYaml::parse(file_get_contents($filterFile));

            foreach ($filterElements['filters'] as $rule => $uris) {
                foreach ($uris as $uri) {
                    $this->filters[] = array('rule' => $rule, 'uri' => $uri);
                }
            }
        } elseif (!is_null($filters)) {
            foreach ($filters as $rule => $filteredUrls) {
                if (!is_null($filteredUrls)) {
                    foreach ($filteredUrls as $uri) {
                        $this->filters[] = array('rule' => $rule, 'uri' => $uri);
                    }
                }
            }
        }

        if (count($exclusive) > 0) {
            foreach ($exclusive as $rule => $urls) {
                if (is_array($urls)) {
                    foreach ($urls as $url) {
                        $uri = new Uri($url);
                        $this->exclusives[$rule][] = (string)$uri;
                    }
                }
            }
            $this->currentModus = self::MODUS_EXCLUSIVE;
        }
    }

    /**
     * @Event("Scanner.Init.ResponseRetriever")
     */
    public function getResponseRetriever(Retriever $responseRetriever)
    {
        $this->retriever = $responseRetriever;
    }

    /**
     * @Event("Scanner.CheckResponse.isFiltered")
     */
    public function isFiltered(Event $event, $ruleName, UriAwareResponse $response)
    {
        $uri = (string)$this->retriever->getOriginUri($response->getUri());

        if ($this->currentModus === self::MODUS_FILTER) {
            $isFiltered = $this->isFilteredByFilter($ruleName, $uri);
        } else {
            $isFiltered = $this->isFilteredByExclusives($ruleName, $uri);
        }

        if ($isFiltered) {
            $event->setProcessed();
            return true;
        } else {
            return false;
        }
    }

    private function isFilteredByFilter($ruleName, $uri)
    {
        foreach ($this->filters as $filter) {
            if ($ruleName === $filter['rule'] && 0 < preg_match('$' . $filter['uri'] . '$', $uri)) {
                return true;
            }
        }


        return false;
    }

    private function isFilteredByExclusives($ruleName, $uri)
    {
        if (array_key_exists($ruleName, $this->exclusives)) {
            if (is_array($this->exclusives[$ruleName])) {
                if (in_array($uri, $this->exclusives[$ruleName], true)) {
                    return false;
                }
            }
        }

        return true;
    }
}
