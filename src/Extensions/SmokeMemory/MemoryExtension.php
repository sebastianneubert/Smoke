<?php

namespace whm\Smoke\Extensions\SmokeMemory;

use phm\HttpWebdriverClient\Http\Response\UriAwareResponse;
use Symfony\Component\Yaml\Yaml;
use whm\Html\Uri;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\CrawlingRetriever;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;

class MemoryExtension
{
    private $memoryFile;

    private $memory = array();
    private $oldMemories = array();

    public function init($memoryFile)
    {
        $this->memoryFile = $memoryFile;

        if (file_exists($memoryFile)) {
            $this->oldMemories = Yaml::parse(file_get_contents($memoryFile));
        }
    }

    /**
     * @var CheckResult[] $results
     *
     * @Event("Scanner.Scan.Validate")
     */
    public function process($results, UriAwareResponse $response)
    {
        foreach ($results as $result) {
            /** @var UriAwareResponse $response */
            if ($result->getStatus() == CheckResult::STATUS_FAILURE) {
                $this->memory[] = (string)$response->getUri();
            }
        }
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        if ((reset($this->memory)) !== false) {
            $yaml = Yaml::dump($this->memory);
            file_put_contents($this->memoryFile, $yaml);
        }
    }


    /**
     * @Event("Scanner.Init.ResponseRetriever")
     */
    public function initRetriever(Retriever $responseRetriever)
    {
        if ($responseRetriever instanceof CrawlingRetriever) {
            foreach ($this->oldMemories as $memory) {
                $responseRetriever->addPage(new Uri($memory));
            }
        } else {
            throw new \RuntimeException('The memory extension can only be used with a crawling retriever.');
        }
    }
}
