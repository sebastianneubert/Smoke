<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use phm\HttpWebdriverClient\Http\Request\DeviceAwareRequest;
use phm\HttpWebdriverClient\Http\Request\ViewportAwareRequest;
use phm\HttpWebdriverClient\Http\Response\RequestAwareResponse;
use Psr\Http\Message\ResponseInterface;
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

    private function getRequestString(ResponseInterface $response)
    {
        if ($response instanceof RequestAwareResponse) {
            $request = $response->getRequest();
            if ($request instanceof DeviceAwareRequest) {
                return ' (Device: ' . $request->getDevice()->getName() . ')';
            } else if ($request instanceof ViewportAwareRequest) {
                $viewport = $request->getViewport();
                return ' (Viewport: width: ' . $viewport->getWidth() . ', height: ' . $viewport->getHeight() . ')';
            }
        }

        return '';
    }

    protected function renderFailure(CheckResult $result)
    {
        $this->output->writeln('   <error> ' . (string)$result->getResponse()->getUri() . $this->getRequestString($result->getResponse()) . ' </error> coming from ' . (string)$this->retriever->getComingFrom($result->getResponse()->getUri()));
        $this->output->writeln('    - ' . $result->getMessage() . ' [rule: ' . $result->getRuleName() . ']');
        $this->output->writeln('');
    }

    protected function renderSuccess(CheckResult $result)
    {
        $this->output->writeln('   <info> ' . (string)$result->getResponse()->getUri() . $this->getRequestString($result->getResponse()) . ' </info> all tests passed');
    }

    protected function renderSkipped(CheckResult $result)
    {
        $this->output->writeln('   <comment> ' . (string)$result->getResponse()->getUri() . $this->getRequestString($result->getResponse()) . ' </comment>test skipped');
        $this->output->writeln('    - ' . $result->getMessage() . ' [rule: ' . $result->getRuleName() . ']');
    }
}
