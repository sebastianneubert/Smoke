<?php

namespace whm\Smoke\Rules\Seo;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use whm\Html\Uri;
use whm\Smoke\Rules\Attribute;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

class GoogleMobileFriendlyRule implements Rule
{
    const ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v3beta1/mobileReady?url=#url#&strategy=mobile';

    private function getEndpoint(UriInterface $uri)
    {
        return str_replace('#url#', urlencode((string)$uri), self::ENDPOINT);
    }

    public function validate(ResponseInterface $response)
    {
        $uri = $response->getUri();

        if (Uri::isBasicAuth($uri)) {
            $result = new CheckResult(CheckResult::STATUS_SKIPPED, 'Google mobile friendliness cannot be checked for an URL with http basic auth.');
            return $result;
        }

        $endpoint = $this->getEndpoint($uri);

        $result = json_decode(file_get_contents($endpoint, false,
            stream_context_create(
                array(
                    'http' => array(
                        'ignore_errors' => true,
                    ),
                )
            )));

        if (property_exists($result, 'error')) {
            $checkResult = new CheckResult(CheckResult::STATUS_FAILURE, 'Google mobile friendly test was not passed. Error "' . $result->error->message . '"');
            $checkResult->addAttribute(new Attribute('Google response', json_encode($result), true));
            return $checkResult;
        }

        $passResult = $result->ruleGroups->USABILITY;

        if (!$passResult->pass) {
            $result = new CheckResult(CheckResult::STATUS_FAILURE, 'Google mobile friendly test was not passed. Score ' . $passResult->score . '/100.', (int)$passResult->score);

        } else {
            $result = new CheckResult(CheckResult::STATUS_SUCCESS, 'Google mobile friendly test passed. Score ' . $passResult->score . '/100.', (int)$passResult->score);
        }

        $result->addAttribute(new Attribute('google_result', json_encode($result), true));
        return $result;
    }
}