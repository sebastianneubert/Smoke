<?php
/**
 * Created by PhpStorm.
 * User: nils.langner
 * Date: 31.03.17
 * Time: 20:40
 */

namespace whm\Smoke\Extensions\SmokeHttpClient;


interface CacheAware
{
    public function enableCache();

    public function disableCache();
}