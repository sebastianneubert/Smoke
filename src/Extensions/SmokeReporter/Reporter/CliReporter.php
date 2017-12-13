<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;

abstract class CliReporter implements Reporter
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Retriever
     */
    protected $retriever;

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    protected function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function renderFailure(CheckResult $result)
    {
        $this->output->writeln('   <error> ' . (string) $result->getResponse()->getUri() . ' </error> coming from ' . (string) $this->retriever->getComingFrom($result->getResponse()->getUri()));
        $this->output->writeln('    - ' . $result->getMessage() . ' [rule: ' . $result->getRuleName() . ']');
        $this->output->writeln('');
    }

    protected function renderSuccess(CheckResult $result)
    {
        $this->output->writeln('   <info> ' . (string) $result->getResponse()->getUri() . ' </info> all tests passed');
    }

    protected function renderSkipped(CheckResult $result)
    {
        $this->output->writeln('   <comment> ' . (string) $result->getResponse()->getUri() . ' </comment>test skipped');
        $this->output->writeln('    - ' . $result->getMessage() . ' [rule: ' . $result->getRuleName() . ']');
    }
}
