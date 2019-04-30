<?php

namespace whm\Smoke\Rules;

use Psr\Http\Message\ResponseInterface;

interface Rule
{
    public function validate(ResponseInterface $response);
}
