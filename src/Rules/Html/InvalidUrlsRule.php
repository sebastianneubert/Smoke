<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule find invalid URLs which are hyperlinked in a given site.
 */
class InvalidUrlsRule extends StandardRule
{
    protected $contentTypes = array('text/html');

    /**
     * {@inheritdoc}
     */
    protected function doValidation(ResponseInterface $response)
    {
        $document = new Document((string)$response->getBody(), false);
        $urls = $document->getDependencies($response->getUri());
        $invalidUrls = array();

        foreach ($urls as $url) {
            if (function_exists('idn_to_ascii')) {
                $idnUrl = $url->getScheme() . '://' . idn_to_ascii($url->getHost()) . $url->getPath();
            } else {
                $idnUrl = $url->getScheme() . '://' . $url->getHost() . $url->getPath();
            }

            if (!filter_var($idnUrl, FILTER_VALIDATE_URL)) {
                $invalidUrls[] = (string)$url;
            }
        }

        $this->assert(count($invalidUrls) === 0, 'Invalid urls found (' . implode(', ', $invalidUrls) . ').');
    }
}
