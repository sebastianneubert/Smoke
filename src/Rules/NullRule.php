<?php

namespace whm\Smoke\Rules;

use Psr\Http\Message\ResponseInterface;
use whm\Smoke\Http\Response;

class NullRule implements Rule
{
    public function validate(ResponseInterface $response)
    {
        // this rule can be used to override standard rules in config
    }
}
