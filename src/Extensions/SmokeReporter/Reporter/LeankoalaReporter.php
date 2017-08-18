<?php

namespace whm\Smoke\Extensions\SmokeReporter\Reporter;

use Koalamon\Client\Reporter\Event;
use Koalamon\Client\Reporter\Event\Attribute;
use Koalamon\Client\Reporter\Event\Processor\MongoDBProcessor;
use Koalamon\Client\Reporter\KoalamonException;
use Koalamon\Client\Reporter\Reporter as KoalaReporter;
use Symfony\Component\Console\Output\OutputInterface;
use whm\Smoke\Config\Configuration;
use whm\Smoke\Extensions\Leankoala\LeankoalaExtension;
use whm\Smoke\Extensions\SmokeResponseRetriever\Retriever\Retriever;
use whm\Smoke\Rules\CheckResult;
use whm\Smoke\Scanner\Result;

/**
 * Class XUnitReporter.
 */
class LeankoalaReporter implements Reporter
{
    /**
     * @var Result[]
     */
    private $results = [];

    /**
     * @var Configuration
     */
    private $config;
    private $system;
    private $collect;
    private $identifier;
    private $systemUseRetriever;
    private $tool = 'smoke';
    private $groupBy;
    private $server;
    private $addComingFrom;
    private $useUrlAsIdentifier;

    /**
     * @var KoalaReporter
     */
    private $reporter;

    /**
     * @var Retriever
     */
    private $retriever;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var LeankoalaExtension
     */
    private $leankoalaExtension;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';

    public function init($apiKey, Configuration $_configuration, OutputInterface $_output, $server = 'https://webhook.koalamon.com', $system = '', $identifier = '', $tool = '', $collect = true, $systemUseRetriever = false, $groupBy = false, $addComingFrom = true, $useMongo = true, $useUrlAsIdentifier = false)
    {
        $httpClient = new \GuzzleHttp\Client();

        $this->reporter = new KoalaReporter('', $apiKey, $httpClient, $server);

        if ($useMongo) {
            $this->reporter->setEventProcessor(MongoDBProcessor::createByEnvironmentVars('leankoala'));
        }

        $this->config = $_configuration;
        $this->systemUseRetriever = $systemUseRetriever;

        $this->system = $system;
        $this->collect = $collect;
        $this->identifier = $identifier;
        $this->useUrlAsIdentifier = $useUrlAsIdentifier;
        $this->groupBy = $groupBy;

        $this->addComingFrom = $addComingFrom;

        if ($tool) {
            $this->tool = $tool;
        }

        $this->leankoalaExtension = $_configuration->getExtension('Leankoala');

        $this->server = $server;
        $this->output = $_output;
    }

    public function setResponseRetriever(Retriever $retriever)
    {
        $this->retriever = $retriever;
    }

    public function processResults($results)
    {
        $this->results[] = $results;
    }

    public function finish()
    {
        if ($this->collect) {
            $this->sendCollectedResults();
        } else {
            $this->sendSingleResults();
        }
    }

    private function getComponent($ruleName)
    {
        $ruleArray = explode('_', $ruleName);
        $component = array_pop($ruleArray);

        return $component;
    }

    private function sendCollectedResults()
    {
        $checks = [];

        foreach ($this->results as $results) {
            foreach ($results as $result) {
                /* @var CheckResult $result */
                $tool = 'Smoke' . $result->getRuleName();
                $checks[$tool][] = $result;
            }
        }

        foreach ($checks as $toolName => $results) {
            $attributes = array();

            if (count($results) === 0) {
                continue;
            }

            $message = 'The smoke test for #system_name# failed (Rule: ' . $toolName . ').<ul>';
            $status = Event::STATUS_SUCCESS;
            $failureCount = 0;
            $identifier = $toolName . '_' . $this->system;

            foreach ($results as $result) {
                /** @var CheckResult $result */
                if ($result->getStatus() === CheckResult::STATUS_FAILURE) {
                    $comingFrom = '';
                    if ($this->addComingFrom && $this->retriever->getComingFrom($result->getResponse()->getUri())) {
                        $comingFrom = ', coming from: ' . $this->retriever->getComingFrom($result->getResponse()->getUri());
                    }
                    $message .= '<li>' . $result->getMessage() . ' (url: ' . (string)$result->getResponse()->getUri() . $comingFrom . ')</li>';
                    ++$failureCount;
                }
            }
            if ($failureCount > 0) {
                $status = Event::STATUS_FAILURE;
                $message .= '</ul>';
                $firstResult = array_pop($results);
                $attributes[] = new Attribute('html-content', (string)$firstResult->getResponse()->getBody(), true);
            } else {
                $message = 'All checks for system "#system_name#" succeeded [SmokeBasic:' . $toolName . '].';
            }

            $this->send($identifier, $this->system, $message, $status, $failureCount, $this->tool, $this->system, $attributes);
        }
    }

    private function sendSingleResults()
    {
        foreach ($this->results as $results) {
            foreach ($results as $result) {
                /* @var CheckResult $result */

                $identifier = '_' . $this->getIdentifier($result);
                $tool = $this->getPrefix($result->getRuleName());

                $component = $this->getComponent($result->getRuleName());
                $system = $this->leankoalaExtension->getSystem($component);

                $attributes = array();
                if ($result->getStatus() == CheckResult::STATUS_FAILURE) {
                    $body = (string)$result->getResponse()->getBody();
                    if ($body == "") {
                        $attributes[] = new Attribute('html content', '<empty>');
                    } else {
                        $attributes[] = new Attribute('html content', $body, true);
                    }
                    $attributes[] = new Attribute('http header', json_encode($result->getResponse()->getHeaders()), true);
                    $attributes[] = new Attribute('http status code', $result->getResponse()->getStatusCode());
                }

                $checkResultAttributes = $result->getAttributes();
                foreach ($checkResultAttributes as $checkResultAttribute) {
                    $attributes[] = new Attribute($checkResultAttribute->getKey(), $checkResultAttribute->getValue(), $checkResultAttribute->isIsStorable());
                }

                if ($this->system) {
                    $currentSystem = $this->system;
                } else {
                    $currentSystem = $system;
                }

                $this->send(
                    $identifier,
                    $currentSystem,
                    $result->getMessage() . ' (url: ' . (string)$result->getResponse()->getUri() . ')',
                    $result->getStatus(),
                    $result->getValue(),
                    $tool,
                    $component,
                    $attributes
                );
            }
        }
    }

    private function getIdentifier(CheckResult $result)
    {
        if ($this->useUrlAsIdentifier) {
            $suffix = '_' . md5((string)$result->getResponse()->getUri());
        } else {
            $suffix = '';
        }

        return $this->tool . '_' . $result->getRuleName() . $suffix;
    }

    private function getPrefix($string)
    {
        return substr($string, 0, strpos($string, '_'));
    }

    /**
     * @param $identifier
     * @param $system
     * @param $message
     * @param $status
     * @param $value
     * @param $tool
     * @param $component
     * @param Attribute[] $attributes
     */
    private function send($identifier, $system, $message, $status, $value, $tool, $component, $attributes = [])
    {
        if ($status !== CheckResult::STATUS_NONE) {
            $event = new Event($identifier, $system, $status, $tool, $message, $value, '', $component);
            $event->addAttribute(new Attribute('_config', json_encode($this->config->getConfigArray()), true));
            foreach ($attributes as $attribute) {
                $event->addAttribute($attribute);
            }

            try {
                $this->reporter->sendEvent($event);
            } catch (KoalamonException $e) {
                $this->output->writeln("\n  <error> Error sending result to leankoala. </error>");
                $this->output->writeln('   Url: ' . $e->getUrl());
                $this->output->writeln('   Payload: ' . $e->getPayload());
                $this->output->writeln("");
            } catch (\Exception $e) {
                $this->output->writeln($e->getMessage());
            }
        }
    }
}
