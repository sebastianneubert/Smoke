<?php

namespace whm\Smoke\Rules\Security;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if a https request contains any insecure includes via http.
 */
class PasswordSecureTransferRule extends StandardRule
{
    protected $contentTypes = array('text/html');

    private $knownIdentifier = array();

    protected function doValidation(ResponseInterface $response)
    {
        $crawler = new Crawler((string)$response->getBody());
        $actionNodes = $crawler->filterXPath('//form[//input[@type="password"]]');

        $url = (string)$response->getUri();

        foreach ($actionNodes as $node) {
            $action = $node->getAttribute('action');

            if (strpos($action, 'https://') === 0) {
                continue;
            }

            $fullPath = $node->tagName;
            $parent = $node->parentNode;

            while ($parent = $parent->parentNode) {
                if (property_exists($parent, 'tagName')) {
                    $fullPath = $parent->tagName . '/' . $fullPath;
                } else {
                    break;
                }
            }

            if (in_array($fullPath, $this->knownIdentifier, true)) {
                continue;
            }

            $this->knownIdentifier[] = $fullPath;

            $this->assert(strpos($url, 'https://') !== false, 'Password is transferred insecure using HTTP.');
        }
    }
}
