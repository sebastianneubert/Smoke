<?php
/**
 * Created by PhpStorm.
 * User: exthcont049
 * Date: 16.08.18
 * Time: 14:34
 */

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;


interface ExceptionAwareReporter
{
    /**
     * @param \Exception[] $exceptions
     * @return mixed
     */
    public function handleExceptions($exceptions);
}