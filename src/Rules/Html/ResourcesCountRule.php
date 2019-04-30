<?php

namespace whm\Smoke\Rules\Html;

use phm\HttpWebdriverClient\Http\Response\ResourcesAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Attribute;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule calculates the size of a html document. If the document is bigger than a given value
 * the test will fail.
 */
class ResourcesCountRule extends StandardRule
{
    private $maxElements;

    protected $contentTypes = array('text/html');

    public function init($maxElements = 200)
    {
        $this->maxElements = (int)$maxElements;
    }

    protected function doValidation(ResponseInterface $response)
    {
        if ($response instanceof ResourcesAwareResponse) {
            $resourceCount = count($response->getResources());
            if ($resourceCount > $this->maxElements) {
                $message = 'Too many resources were loaded. ' . $resourceCount . ' resources loaded, maximum was ' . $this->maxElements . '.';
                $result = new CheckResult(CheckResult::STATUS_FAILURE, $message, $resourceCount);
                $result->addAttribute(new Attribute('resources', $response->getResources(), true));
                return $result;
            } else {
                $message = $resourceCount . ' resources were loaded.';
                return new CheckResult(CheckResult::STATUS_SUCCESS, $message, $resourceCount);
            }
        } else {
            throw new \RuntimeException('Unable to analyze resources count. Please use another response retriever.');
        }
    }
}
