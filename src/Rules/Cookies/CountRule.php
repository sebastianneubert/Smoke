<?php

namespace whm\Smoke\Rules\Cookies;

use phm\HttpWebdriverClient\Http\Response\CookieAwareResponse;
use phm\HttpWebdriverClient\Http\Response\TimeoutAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\Attribute;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

/**
 * This rule can validate if a http request takes longer than a given max duration.
 * A website that is slower than one second is considered as slow.
 */
class CountRule implements Rule
{
    private $maxCookies;

    /**
     * @param int $maxDuration The maximum duration a http call is allowed to take (time to first byte)
     */
    public function init($maxElements = 20)
    {
        $this->maxCookies = $maxElements;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response instanceof CookieAwareResponse) {

            if (!$response instanceof TimeoutAwareResponse || !$response->isTimeout()) {

                $cookieCount = $response->getCookieCount();

                if ($cookieCount > $this->maxCookies) {
                    $result = new CheckResult(
                        CheckResult::STATUS_FAILURE,
                        $cookieCount . ' cookies were stored (limit was ' . $this->maxCookies . ').',
                        $cookieCount);
                } else {
                    $result = new CheckResult(
                        CheckResult::STATUS_SUCCESS,
                        $cookieCount . ' cookies were stored (limit was ' . $this->maxCookies . ').',
                        $cookieCount);
                }

                $cookies = $response->getCookies();

                $result->addAttribute(new Attribute(' cookies', json_encode($cookies), true));

                return $result;
            } else {
                $result = new CheckResult(
                    CheckResult::STATUS_SKIPPED,
                    'Skipped: Request timed out. Cannot read cookies.',
                    0);

                return $result;
            }
        }
    }
}
