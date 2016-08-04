<?php

namespace whm\Smoke\Rules\Html;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if xpath is found in a html document.
 */
class XPathExistsRule extends StandardRule
{
    protected $contentTypes = ['text/html'];

    private $xPaths;

    public function init(array $xPaths)
    {
        $this->xPaths = $xPaths;
    }

    public function doValidation(Response $response)
    {
        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML((string) $response->getBody());

        $domXPath = new \DOMXPath($domDocument);

        foreach ($this->xPaths as $xpath) {
            $count = $domXPath->query($xpath['pattern'])->length;

            if ($xpath['relation'] === 'equals') {
                $result = $count === $xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were exact ' . $xpath['value'] . ' occurencies.';
            } elseif ($xpath['relation'] === 'less than') {
                $result = $count < $xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were less than ' . $xpath['value'] . '.';
            } elseif ($xpath['relation'] === 'greater than') {
                $result = $count > $xpath['value'];
                $message = 'The xpath "' . $xpath['pattern'] . '" was found ' . $count . ' times. Expected were more than ' . $xpath['value'] . '.';
            } else {
                throw new \RuntimeException('Relation not defined. Given "' . $xpath['relation'] . '" expected [equals, greater than, less than]');
            }

            $this->assert($result, $message);
        }
    }
}
