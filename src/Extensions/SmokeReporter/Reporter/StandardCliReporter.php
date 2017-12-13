<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Rules\Rule;

class StandardCliReporter extends CliReporter
{
    /**
     * @var CheckResult[]
     */
    private $results = array();
    private $orderBy;

    /**
     * @var Rule[]
     */
    private $rules = array();
    private $maxResults;

    public function init(OutputInterface $_output, Configuration $_configuration, $orderBy = 'url', $maxResults = 0)
    {
        $this->setOutputInterface($_output);

        $this->orderBy = $orderBy;
        $this->rules = $_configuration->getRules();

        if ($maxResults === 0) {
            $this->maxResults = 10000000;
        } else {
            $this->maxResults = $maxResults;
        }
    }

    /**
     * @param \whm\Smoke\Rules\CheckResult[] $results
     */
    public function processResults($results)
    {
        if (count($results) === 0) {
            return;
        }

        $failures = false;

        $processedResults = [];

        foreach ($results as $result) {
            if ($result->getStatus() === CheckResult::STATUS_FAILURE) {
                $processedResults[] = $result;
                $failures = true;
            }
        }
        if ($failures) {
            $this->results[] = $processedResults;
        } else {
            $this->results[] = [array_pop($results)];
        }
    }

    public function finish()
    {
        if ($this->orderBy === 'url') {
            $this->renderUrlOutput();
        } elseif ($this->orderBy === 'rule') {
            $this->renderRuleOutput();
        }
        $this->output->writeln('');
    }

    private function getFailedUrls($ruleKey)
    {
        $failedUrls = array();
        $count = 0;
        foreach ($this->results as $results) {
            foreach ($results as $key => $result) {
                /** @var CheckResult $result */
                if ($result->getStatus() === CheckResult::STATUS_FAILURE) {
                    if ($ruleKey === $key) {
                        $failedUrls[] = (string) $result->getResponse()->getUri() . ' - ' . $result->getMessage();
                        ++$count;
                    }
                    if ($count > $this->maxResults) {
                        $failedUrls[] = '... only the first ' . $this->maxResults . ' elements are shown.';
                        break;
                    }
                }
            }
        }

        return $failedUrls;
    }

    private function renderRuleOutput()
    {
        $this->output->writeln("\n\n <comment>Rules and Violations:</comment> \n");

        foreach ($this->rules as $ruleKey => $rule) {
            $info = Init::getInitInformationByClass($rule);
            $failedUrls = $this->getFailedUrls($ruleKey);

            if (count($failedUrls) > 0) {
                $this->output->writeln('  <error> ' . get_class($rule) . ' </error>');
            } else {
                $this->output->writeln('  <info> ' . get_class($rule) . ' </info>');
            }

            $this->output->writeln('   ' . str_replace("\n", "\n   ", $info['documentation']) . "\n");

            foreach ($failedUrls as $failedUrl) {
                $this->output->writeln('   - ' . $failedUrl);
            }

            $this->output->writeln('');
        }
    }

    private function renderUrlOutput()
    {
        $this->output->writeln("\n\n <comment>Passed tests:</comment> \n");

        foreach ($this->results as $results) {
            foreach ($results as $result) {
                if ($result->getStatus() === CheckResult::STATUS_SUCCESS) {
                    $this->renderSuccess($result);
                }
            }
        }

        $this->output->writeln("\n <comment>Failed tests:</comment> \n");

        foreach ($this->results as $results) {
            foreach ($results as $result) {
                if ($result->getStatus() === CheckResult::STATUS_FAILURE) {
                    $this->renderFailure($result);
                }
            }
        }

        foreach ($this->results as $results) {
            foreach ($results as $result) {
                if ($result->getStatus() === CheckResult::STATUS_SKIPPED) {
                    $this->renderSkipped($result);
                }
            }
        }
    }
}
