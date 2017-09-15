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

class KoalamonSummaryReporter implements Reporter
{
    private $failed = 0;
    private $success = 0;

    /**
     * @var Configuration
     */
    private $config;
    private $system;
    private $identifier;
    private $tool = 'smoke';
    private $server;
    private $url;

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

    public function init($apiKey, Configuration $_configuration, OutputInterface $_output, $server = 'https://webhook.koalamon.com', $system = '', $identifier = '', $tool = '', $url ='')
    {
        $httpClient = new \GuzzleHttp\Client();

        $this->reporter = new KoalaReporter('', $apiKey, $httpClient, $server);

        $this->config = $_configuration;

        $this->system = $system;
        $this->identifier = $identifier;

        if ($tool) {
            $this->tool = $tool;
        }

        $this->leankoalaExtension = $_configuration->getExtension('Leankoala');

        $this->url = $url;
        $this->server = $server;
        $this->output = $_output;

        var_dump($this->url);
    }

    /**
     * @param CheckResult[] $results
     */
    public function processResults($results)
    {
        foreach ($results as $result) {
            if($result->getStatus() == CheckResult::STATUS_SUCCESS) {
                $this->success++;
            } else {
                $this->failed++;
            }
        }
    }

    public function finish()
    {
        $message = 'Checks: ';
        $message .= $this->success . ' succeeded, ';
        $message .= $this->failed . ' failed. ';

        if ($this->failed > 0) {
            $status = Event::STATUS_FAILURE;
        } else {
            $status = Event::STATUS_SUCCESS;
        }

        $this->send($this->identifier, $this->system, $message, $status, $this->failed, $this->tool, $this->system, [], $this->url);
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
    private function send($identifier, $system, $message, $status, $value, $tool, $component, $attributes = [], $url = "")
    {
        if ($status !== CheckResult::STATUS_NONE) {
            $event = new Event($identifier, $system, $status, $tool, $message, $value, $url, $component);
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
