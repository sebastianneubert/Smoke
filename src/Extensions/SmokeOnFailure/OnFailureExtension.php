<?php

namespace whm\Smoke\Extensions\SmokeOnFailure;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Rules\CheckResult;

class OnFailureExtension
{
    private $callback;

    public function init($command)
    {
        $this->callback = function () use ($command) {
            return eval($command);
        };
    }

    /**
     * @param CheckResult[] $results
     * @Event("Scanner.Scan.Validate")
     */
    public function process($results, ResponseInterface $response)
    {
        foreach ($results as $result) {
            if ($result->getStatus() == CheckResult::STATUS_FAILURE) {
                $callback = $this->callback;
                $callback();
            }
        }
    }
}
