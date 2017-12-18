<?php

namespace whm\Smoke\Rules\Html;

use phm\HttpWebdriverClient\Http\Client\HeadlessChrome\HeadlessChromeResponse;
use phm\HttpWebdriverClient\Http\Response\ResourcesAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\StandardRule;

/**
 * This rule checks if a scanned resource has 4xx or 5xx links
 */
class BrokenLinkRule extends StandardRule
{
    protected $contentTypes = ['text/html'];

    private $excludedFiles = [];

    public function init($excludedFiles = array())
    {
        foreach ($excludedFiles as $fileName) {
            $this->excludedFiles[] = $fileName['filename'];
        }
    }

    public function doValidation(ResponseInterface $response)
    {

        if ($response instanceof ResourcesAwareResponse) {
            $resources = $response->getResources();

            $errorList = [];

            foreach ($resources as $resource) {
                if ($resource['http_status'] >= 400) {
                    $excluded = false;
                    foreach ($this->excludedFiles as $excludedFile) {
                        if (strpos($resource['name'], $excludedFile) !== false) {
                            $excluded = true;
                        }
                    }

                    if (!$excluded) {
                        $errorList[] = $resource;
                    }
                }
            }

            if (count($errorList) > 0) {
                $count = count($errorList);
                $msg = 'Found ' . $count . ' resource(s) with status code 4xx or 5xx. <ul>';
                foreach ($errorList as $error) {
                    $msg .= '<li>' . $error['name'] . ' (http status: ' . $error['http_status'] . ')</li>';
                }
                $msg .= '</ul>';
                return new CheckResult(CheckResult::STATUS_FAILURE, $msg, $count);
            } else {
                return new CheckResult(CheckResult::STATUS_SUCCESS, 'No resources with status 4xx or 5xx found.', 0);
            }
        } else {
            return new CheckResult(CheckResult::STATUS_SKIPPED, 'Expected ResourcesAwareResponse. Skipped.');
        }
    }
}
