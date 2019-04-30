<?php

namespace whm\Smoke\Rules\Html;

use Psr\Http\Message\ResponseInterface;
use whm\Html\Document;
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

    abstract protected function getFilesToCount(Document $document, ResponseInterface $response);

    protected function doValidation(ResponseInterface $response)
    {
        $document = new Document((string)$response->getBody());
        $files = $this->getFilesToCount($document, $response);

        $this->assert(count($files) <= $this->maxCount, sprintf($this->errorMessage, count($files)));
    }
}
