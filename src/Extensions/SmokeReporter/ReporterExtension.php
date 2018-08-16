<?php

namespace whm\Smoke\Extensions\SmokeReporter;

use PhmLabs\Components\Init\Init;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeReporter\Reporter\ExceptionAwareReporter;
use whm\Smoke\Extensions\SmokeReporter\Reporter\Reporter;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;

class ReporterExtension
{
    /**
     * @var Reporter[]
     */
    private $reporters = array();
    private $output;

    /**
     * @var Configuration
     */
    private $config;

    public function init(OutputInterface $_output, Configuration $_configuration)
    {
        $this->output = $_output;
        $this->config = $_configuration;

        if ($_configuration->hasSection('reporter')) {
            $this->reporters = Init::initializeAll($_configuration->getSection('reporter'));
        }
    }

    /**
     * @Event("Scanner.Init.ResponseRetriever")
     */
    public function getResponseRetriever(Retriever $responseRetriever)
    {
        foreach ($this->reporters as $reporter) {
            if (method_exists($reporter, 'setResponseRetriever')) {
                $reporter->setResponseRetriever($responseRetriever);
            }
        }
    }

    /**
     * @var CheckResult[]
     *
     * @Event("Scanner.Scan.Validate")
     */
    public function process($results, ResponseInterface $response)
    {
        foreach ($this->reporters as $reporter) {
            $reporter->processResults($results, $response);
        }
    }

    /**
     * @Event("Scanner.Scan.Finish")
     */
    public function finish()
    {
        foreach ($this->reporters as $reporter) {
            $reporter->finish();
        }
    }

    /**
     * @Event("Scanner.Scan.Exceptions.Handle")
     */
    public function handleExceptions($occuredExceptions)
    {
        foreach ($this->reporters as $reporter) {
            if ($reporter instanceof ExceptionAwareReporter) {
                $reporter->handleExceptions($occuredExceptions);
            }
        }
    }
}
