<?php

/*
 * This rule will find external ressources on a https transfered page that are insecure (http).
 *
 * @author Nils Langner <nils.langner@phmlabs.com>
 * @inspiredBy Christian Haller
 */

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

/**
 * This rule checks if a https document uses http (insecure) ressources.
 */
class InsecureContentRule implements Rule
{
    private $excludedFiles = [];

    public function init($excludedFiles = [])
    {
        foreach ($excludedFiles as $excludedFile) {
            $this->excludedFiles[] = $excludedFile['file'];
        }
    }

    public function validate(ResponseInterface $response)
    {
        $uri = $response->getUri();

        if ('https' !== $uri->getScheme()) {
            return;
        }

        $htmlDocument = new Document((string)$response->getBody());

        $resources = $htmlDocument->getDependencies($uri, false);

        $unsecures = array();

        foreach ($resources as $resource) {
            if ($resource->getScheme() && 'https' !== $resource->getScheme()) {
                $excluded = false;
                foreach ($this->excludedFiles as $excludedFile) {
                    if (preg_match('*' . $excludedFile . '*', (string)$resource)) {
                        $excluded = true;
                        break;
                    }
                }
                if (!$excluded) {
                    $unsecures[] = $resource;
                }
            }
        }

        if (count($unsecures) > 0) {
            $message = 'At least one dependency was found on a secure url, that was transfered insecure.<ul>';
            foreach ($unsecures as $unsecure) {
                $message .= '<li>' . (string)$unsecure . '</li>';
            }
            $message .= '</ul>';
            return new CheckResult(CheckResult::STATUS_FAILURE, $message, count($unsecures));
        } else {
            return new CheckResult(CheckResult::STATUS_SUCCESS, 'No http element on that https url found.', 0);
        }
    }
}
