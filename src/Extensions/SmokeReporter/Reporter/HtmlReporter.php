<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;


class HtmlReporter implements Reporter
{
    private $results = [];
    private $successes = [];
    private $failures = [];
    private $unknowns = [];


    /**
     * @var Configuration
     */
    private $templateDir;
    private $templateFile;
    private $resultFile;

    /**
     * @var OutputInterface
     */
    private $output;

    public function init(OutputInterface $_output, $resultFile, $templateDir = null, $templateFile = 'index.html.twig')
    {
        $this->resultFile = $resultFile;

        if ($templateDir === null) {
            $this->templateDir = __DIR__ . '/templates';
        } else {
            $this->templateDir = $templateDir;
        }

        $this->templateFile = $templateFile;
        $this->output = $_output;
    }

    /**
     * @param CheckResult[] $results
     */
    public function processResults($results)
    {
        foreach ($results as $result) {
            $temp = [
                'url' => $result->getResponse()->getUri(),
                'rule' => $result->getRuleName(),
                'message' => $result->getMessage(),
                'status' => $result->getStatus(),
            ];

            switch ($result->getStatus()) {
                case CheckResult::STATUS_SUCCESS:
                    $this->successes[] = $temp;
                    break;

                case CheckResult::STATUS_FAILURE:
                    $this->failures[] = $temp;
                break;
                default:
                    $this->unknowns[] = $temp;
            }

            $this->results[] = $temp;
        }
    }

    public function finish()
    {
        $loader = new \Twig_Loader_Filesystem($this->templateDir);
        $twig = new \Twig_Environment($loader);

        $html = $twig->render($this->templateFile, [
            'results' => $this->results,
            'successes' => $this->successes,
            'failures' => $this->failures,
            'unknowns' => $this->unknowns,
            'success' => count($this->successes),
            'failure' => count($this->failures),
            'unknown' => count($this->unknowns),
            'total' => count($this->results),
        ]);

        if (!file_exists(dirname($this->resultFile))) {
            mkdir(dirname($this->resultFile));
        }

        if (!file_put_contents($this->resultFile, $html)) {
            $this->output->writeln("<error>HTML Reporter extension: Could not write result file to " . $this->resultFile ."</error>");
        } else {
            $this->output->writeln("<info>HTML Reporter extension:</info> Result file written to " . $this->resultFile);
        }
    }

}
