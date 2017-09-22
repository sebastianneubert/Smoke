<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;


class HtmlReporter implements Reporter
{
    private $results = [];
    private $success = 0;
    private $failure = 0;
    private $unknown = 0;


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
            switch ($result->getStatus()) {
                case CheckResult::STATUS_SUCCESS: $this->success++; break;
                case CheckResult::STATUS_FAILURE: $this->failure++; break;
                default:
                    $this->unknown++;
            }

            $this->results[] = [
                'url' => $result->getResponse()->getUri(),
                'rule' => $result->getRuleName(),
                'message' => $result->getMessage(),
                'status' => $result->getStatus(),
            ];
        }
    }

    public function finish()
    {
        $loader = new \Twig_Loader_Filesystem($this->templateDir);
        $twig = new \Twig_Environment($loader);

        $html = $twig->render($this->templateFile, [
            'results' => $this->results,
            'success' => $this->success,
            'failure' => $this->failure,
            'unknown' => $this->unknown,
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
