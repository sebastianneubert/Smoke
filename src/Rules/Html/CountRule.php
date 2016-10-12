<?php

namespace whm\Smoke\Rules\Html;

use whm\Html\Document;
use whm\Smoke\Http\Response;
use whm\Smoke\Rules\StandardRule;

abstract class CountRule extends StandardRule
{
    protected $maxCount;

    protected $contentTypes = array('text/html');

    protected $errorMessage;

    /**
     * @param int $maxCount The maximum number of css files that are allowed in one html document
     */
    public function init($maxCount)
    {
        $this->maxCount = $maxCount;
    }

    abstract protected function getFilesToCount(Document $document, Response $response);

    protected function doValidation(Response $response)
    {
        $document = new Document($response->getBody());
        $files = $this->getFilesToCount($document, $response);

        $this->assert(count($files) <= $this->maxCount, sprintf($this->errorMessage, count($files)));
    }
}
