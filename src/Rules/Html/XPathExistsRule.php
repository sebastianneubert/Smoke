<?php

namespace whm\Smoke\Rules\Html;

use phm\HttpWebdriverClient\Http\Response\DomAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if xpath is found in a html document.
 */
class XPathExistsRule extends StandardRule
{
    // protected $contentTypes = ['text/html'];
    private $xPaths;

    /**
     * @var boolean
     */
    private $useDom = true;

    public function init(array $xPaths, $useDom = true)
    {
        $this->xPaths = $xPaths;
        $this->useDom = $useDom;
    }

    public function doValidation(ResponseInterface $response)
    {
        $domDocument = new \DOMDocument();

        // @todo this could be part of an abstract class
        if ($this->useDom) {
            $content = (string)$response->getBody();
        } else {
            if ($response instanceof DomAwareResponse) {
                $content = $response->getHtmlBody();
            } else {
                $content = (string)$response->getBody();
            }
        }

        @$domDocument->loadHTML($content);

        $domXPath = new \DOMXPath($domDocument);

        foreach ($this->xPaths as $xpath) {
            $count = $domXPath->query($xpath['pattern'])->length;

            if ($xpath['relation'] === 'equals') {
                $result = $count === (int)$xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were exact ' . $xpath['value'] . ' occurencies.';
            } elseif ($xpath['relation'] === 'less than') {
                $result = $count < (int)$xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were less than ' . $xpath['value'] . '.';
            } elseif ($xpath['relation'] === 'greater than') {
                $result = $count > (int)$xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were more than ' . $xpath['value'] . '.';
            } else {
                throw new \RuntimeException('Relation not defined. Given "' . $xpath['relation'] . '" expected [equals, greater than, less than]');
            }

            $this->assert($result, $message);
        }
    }
}
