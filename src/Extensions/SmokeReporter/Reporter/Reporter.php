<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use whm\Smoke\Rules\CheckResult;

interface Reporter
{
    /**
     * This function will be called after an url has been validated.
     *
     * @param CheckResult[] $results the result of the validation
     */
    public function processResults($results);

    /**
     * This function is called after the last url has been validated.
     */
    public function finish();
}
