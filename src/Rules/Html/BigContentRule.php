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
class BigContentRule extends StandardRule
{
    private $maxSize;

    protected $contentTypes = array('text/html');

    public function init($maxSize = 400)
    {
        $this->maxSize = $maxSize;
    }

    protected function doValidation(ResponseInterface $response)
    {
        $totalSize = 0;

        if ($response instanceof ResourcesAwareResponse) {
            foreach ($response->getResources() as $resource) {
                $resourceSize = round($resource['transferSize'] / 1000);
                $totalSize += $resourceSize;
            }
        }

        if ($totalSize > $this->maxSize) {
            $message = 'The total size of the file (and all assets) was ' . $totalSize . ' KB (max: ' . $this->maxSize . ' KB).';
            $result = new CheckResult(CheckResult::STATUS_FAILURE, $message, $totalSize);
            $result->addAttribute(new Attribute('resources', $response->getResources(), true));
            return $result;
        } else {
            $message = 'The total size of the file (and all assets) was ' . $totalSize . ' KB (max: ' . $this->maxSize . ' KB).';
            $result = new CheckResult(CheckResult::STATUS_SUCCESS, $message, $totalSize);
            $result->addAttribute(new Attribute('resources', $response->getResources(), true));
            return $result;
        }
    }
}
