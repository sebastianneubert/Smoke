<?php

namespace whm\Smoke\Http\HttpClient;

interface HttpClient
{
    public function request(array $uris);
}