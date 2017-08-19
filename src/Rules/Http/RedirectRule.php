<?php

namespace whm\Smoke\Rules\Http;

use GuzzleHttp\Psr7\Request;
use phm\HttpWebdriverClient\Http\Client\Guzzle\GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use whm\Html\Uri;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

/**
 * This rule can validate if a http request takes longer than a given max duration.
 * A website that is slower than one second is considered as slow.
 */
class RedirectRule implements Rule
{
    private $urls;

    public function init($redirectedUrls)
    {
        $this->urls = $redirectedUrls;
    }

    public function validate(ResponseInterface $response)
    {
        $client = new GuzzleClient();
        $errors = [];

        foreach ($this->urls as $url) {
            $uri = new Uri($url['url']);

            $urlResponses = $client->sendRequests([new Request('GET', $uri)]);
            $effectiveUrl = (string)$urlResponses[0]->getEffectiveUri();

            if ($effectiveUrl != (string)$response->getUri()) {
                $errors[] = 'The url "' . $url['url'] . '" gets redirected to "' . $effectiveUrl . '".';
            }
        }

        if (count($errors) > 0) {
            $message = 'Not all given urls do redirect to "' . (string)$response->getUri() . '"".<ul>';
            foreach ($errors as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= "</ul>";

            return new CheckResult(CheckResult::STATUS_FAILURE, $message);
        } else {
            return new CheckResult(CheckResult::STATUS_SUCCESS, 'All given urls redirect to ' . (string)$response->getUri());
        }
    }
}
