<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

class NullReporter implements Reporter
{
    public function processResults($results)
    {
    }

    public function finish()
    {
    }
}
