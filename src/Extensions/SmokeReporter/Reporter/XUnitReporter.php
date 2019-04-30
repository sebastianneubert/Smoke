<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use phmLabs\XUnitReport\Elements\Failure;
use phmLabs\XUnitReport\Elements\TestCase;
use phmLabs\XUnitReport\XUnitReport;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\CrawlingRetriever;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class XUnitReporter implements Reporter
{
    private $filename = null;

    /**
     * @var Result[]
     */
    private $results = array();

    private $output = null;

    private $config;

    /**
     * @var Retriever
     */
    protected $retriever;

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    public function init($filename, Configuration $_configuration, OutputInterface $_output)
    {
        $this->filename = $filename;
        $this->config = $_configuration;
        $this->output = $_output;

        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename));
        }
    }

    public function processResult(Result $result)
    {
        $this->results[] = $result;
    }

    public function finish()
    {
        $failures = 0;

        if ($this->retriever instanceof CrawlingRetriever) {
            $startPage = (string)$this->retriever->getStartPage();
        } else {
            $startPage = '';
        }

        $xUnitReport = new XUnitReport($startPage);

        foreach ($this->results as $result) {
            $testCase = new TestCase(
                $result->getUrl(),
                $result->getUrl(),
                $result->getDuration()
            );

            if ($result->isFailure()) {
                ++$failures;

                foreach ($result->getMessages() as $ruleName => $message) {
                    $testCase->addFailure(new Failure($ruleName, $message));

                    if ($this->retriever instanceof CrawlingRetriever) {
                        $stackTrace = $result->getUrl() . ' coming from ' . (string)$this->retriever->getComingFrom($result->getUrl()) . PHP_EOL;
                        $stackTrace .= '    - ' . $message . " [rule: $ruleName]";
                        $testCase->setSystemOut($stackTrace);
                    }
                }
            }

            $xUnitReport->addTestCase($testCase);
        }

        file_put_contents($this->filename, $xUnitReport->toXml());

        $this->output->writeln('    <info>Writing XUnit Output to file:</info> ' . $this->filename);
    }

    public function processResults($results)
    {
        // TODO: Implement processResults() method.
    }
}
