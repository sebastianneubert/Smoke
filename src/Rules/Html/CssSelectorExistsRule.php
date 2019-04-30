<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\CssSelector\CssSelectorConverter;
use whm\Smoke\Rules\StandardRule;
use whm\Smoke\Rules\ValidationFailedException;

/**
 * This rule checks if xpath is found in a html document.
 */
class CssSelectorExistsRule extends StandardRule
{
    protected $contentTypes = ['text/html'];

    private $cssSelectors;

    public function init(array $cssSelectors)
    {
        $this->cssSelectors = $cssSelectors;
    }

    public function doValidation(ResponseInterface $response)
    {
        $content = (string)$response->getBody();

        $domDocument = new \DOMDocument();
        @$domDocument->loadHTML($content);

        $domXPath = new \DOMXPath($domDocument);

        $error = false;
        $snotFoundSelectors = array();

        $converter = new CssSelectorConverter();

        foreach ($this->cssSelectors as $selector) {

            try {
                $selectorAsXPath = $converter->toXPath($selector['pattern']);
            } catch (\Exception $e) {
                throw new ValidationFailedException('Invalid css selector (' . $selector['pattern'] . ').');
            }

            $count = $domXPath->query($selectorAsXPath)->length;

            if ($count === 0) {
                $error = true;
                $snotFoundSelectors[] = $selector['pattern'];
            }
        }

        if ($error === true) {
            $allNotFoundSelectors = implode('", "', $snotFoundSelectors);

            throw new ValidationFailedException('CSS Selector "' . $allNotFoundSelectors . '" not found in DOM.');
        }
    }
}
