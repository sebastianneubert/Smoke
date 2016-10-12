<?php

namespace whm\Smoke\Cli\Command;

use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\Event\Subscriber\RedirectSubscriber;
use Ivory\HttpAdapter\Event\Subscriber\RetrySubscriber;
use Ivory\HttpAdapter\EventDispatcherHttpAdapter;
use phmLabs\Components\Annovent\Dispatcher;
use PhmLabs\Components\Init\Init;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use whm\Crawler\Http\RequestFactory;
use whm\Smoke\Http\MessageFactory;
use whm\Smoke\Scanner\Scanner;
use whm\Smoke\Yaml\EnvAwareYaml;

class SmokeCommand extends Command
{
    protected $output;
    protected $eventDispatcher;
    protected $config;

    protected function init(InputInterface $input, OutputInterface $output, $url = null)
    {
        if ($input->hasOption('bootstrap') && !is_null($input->getOption('bootstrap'))) {
            include $input->getOption('bootstrap');
        }

        $this->output = $output;
        $this->eventDispatcher = new Dispatcher();

        Init::registerGlobalParameter('_eventDispatcher', $this->eventDispatcher);
        Init::registerGlobalParameter('_output', $output);

        $this->writeSmokeCredentials($url);
    }

    /**
     * This function creates the credentials header for the command line.
     *
     * @param null $url
     */
    protected function writeSmokeCredentials($url = null)
    {
        $this->output->writeln("\n Smoke " . SMOKE_VERSION . " by Nils Langner\n");

        if ($url) {
            $this->output->writeln(' <info>Scanning ' . $url . "</info>\n");
        }
    }

    /**
     * This function return a http client.
     *
     * @throws \Ivory\HttpAdapter\HttpAdapterException
     *
     * @return \Ivory\HttpAdapter\HttpAdapterInterface
     */
    protected function getHttpClient()
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new RedirectSubscriber());
        $eventDispatcher->addSubscriber(new RetrySubscriber());

        // $guessedAdapter = HttpAdapterFactory::guess();
        /** @var \Ivory\HttpAdapter\Guzzle6HttpAdapter $guessedAdapter */
        $guessedAdapter = new CurlHttpAdapter();

        RequestFactory::addStandardHeader('Accept-Encoding', 'gzip');
        RequestFactory::addStandardHeader('Connection', 'keep-alive');

        $adapter = new EventDispatcherHttpAdapter($guessedAdapter, $eventDispatcher);
        $adapter->getConfiguration()->setTimeout(30);
        //$adapter->getConfiguration()->setUserAgent('versioneye-php');
        $adapter->getConfiguration()->setMessageFactory(new MessageFactory());

        return $adapter;
    }

    protected function scan()
    {
        $scanner = new Scanner($this->config->getRules(),
            $this->getHttpClient(),
            $this->eventDispatcher,
            $this->config->getExtension('_ResponseRetriever')->getRetriever());

        $scanner->scan();

        return $scanner->getStatus();
    }

    /**
     * Returns an array representing the configuration.
     *
     * @param $configFile
     *
     * @return array
     */
    protected function getConfigArray($configFile, $mandatory = false)
    {
        $configArray = array();

        if ($configFile) {
            if (strpos($configFile, 'http://') === 0 || strpos($configFile, 'https://') === 0) {
                $fileContent = (string) $this->getHttpClient()->get($configFile)->getBody();
            } else {
                if (file_exists($configFile)) {
                    $fileContent = file_get_contents($configFile);
                } else {
                    throw new \RuntimeException("Config file was not found ('" . $configFile . "').");
                }
            }
            $configArray = EnvAwareYaml::parse($fileContent);
        } else {
            if ($mandatory) {
                throw new \RuntimeException('Config file was not defined.');
            }
        }

        return $configArray;
    }
}
