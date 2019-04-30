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
class BigFilesRule extends StandardRule
{
    private $maxElementSize;

    protected $contentTypes = array('text/html');

    public function init($maxSize = 400)
    {
        $this->maxElementSize = $maxSize;
    }

    protected function doValidation(ResponseInterface $response)
    {
        $bigFiles = [];

        if ($response instanceof ResourcesAwareResponse) {
            foreach ($response->getResources() as $resource) {
                $resourceSize = round($resource['transferSize'] / 1000);

                if ($resourceSize > $this->maxElementSize) {
                    $bigFiles[] = ['name' => $resource['name'], 'size' => $resourceSize];
                }
            }
        }

        if (count($bigFiles) > 0) {
            $message = "Some files were found that are too big (max: " . $this->maxElementSize . " KB):<ul>";
            foreach ($bigFiles as $bigFile) {
                $message .= '<li>File: ' . $bigFile['name'] . ', Size: ' . $bigFile['size'] . ' KB</li>';
            }
            $message .= "</ul>";
            $result = new CheckResult(CheckResult::STATUS_FAILURE, $message, count($bigFiles));
            $result->addAttribute(new Attribute('resources', $response->getResources(), true));
            return $result;
        }
    }
}
