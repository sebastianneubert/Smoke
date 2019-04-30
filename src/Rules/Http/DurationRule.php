<?php

namespace whm\Smoke\Rules\Http;

use phm\HttpWebdriverClient\Http\Response\DurationAwareResponse;
use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

/**
 * This rule can validate if a http request takes longer than a given max duration.
 * A website that is slower than one second is considered as slow.
 */
class DurationRule implements Rule
{
    private $maxDuration;

    /**
     * @param int $maxDuration The maximum duration a http call is allowed to take (time to first byte)
     */
    public function init($maxDuration = 1000)
    {
        $this->maxDuration = $maxDuration;
    }

    public function validate(ResponseInterface $response)
    {
        if ($response instanceof DurationAwareResponse) {
            if ($response->getDuration() > $this->maxDuration) {
                return new CheckResult(
                    CheckResult::STATUS_FAILURE,
                    'The http request took ' . (int)$response->getDuration() . ' milliseconds (limit was ' . $this->maxDuration . 'ms).',
                    (int)$response->getDuration());
            }

            return new CheckResult(
                CheckResult::STATUS_SUCCESS,
                'The http request took ' . (int)$response->getDuration() . ' milliseconds (limit was ' . $this->maxDuration . 'ms).',
                (int)$response->getDuration());
        }
    }
}
